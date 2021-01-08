<?php

class DepopayApp extends MemberbaseApp
{
	var $_order_mod;
	var $_ordergoods_mod;
	var $_deposit_account_mod;
	var $_deposit_record_mod;
	var $_deposit_trade_mod;
	
	/* 构造函数 */
    function __construct()
    {
         $this->DepopayApp();
    }

    function DepopayApp()
    {
        parent::__construct();
		$this->_order_mod					= &m('order');
		$this->_ordergoods_mod 				= &m('ordergoods');
		$this->_deposit_account_mod			= &m('deposit_account');
		$this->_deposit_record_mod  		= &m('deposit_record');
		$this->_deposit_trade_mod 			= &m('deposit_trade');		
    }
	
	/* 接收交易数据的网关 */
	function gateway()
	{
		// 商户号
		$merchantId 	= html_script(trim($_GET['merchantId']));
			
		// 商户订单号（合并付款时有多个）
		$bizOrderIdList	= explode(',', html_script(trim($_GET['bizOrderId'])));
		
		// 订单类型
		$bizIdentity	= html_script(trim($_GET['bizIdentity']));
		$bizIdentity 	= in_array($bizIdentity, array(TRADE_ORDER, TRADE_RECHARGE, TRADE_DRAW, TRADE_BUYAPP)) ? $bizIdentity : TRADE_ORDER;
			
		$payNo  	= array();
		$errorMsg 	= FALSE;
		$deposit_trade_mod = &m('deposit_trade');
		
		// 生成交易记录（合并付款时有多个交易记录被插入）
		foreach($bizOrderIdList as $bizOrderId)
		{
			// 此处不用判断此笔交易是否为当前用户发起的，到Pay页面才判断
			if($tradeInfo = $this->_deposit_trade_mod->get("merchantId='{$merchantId}' AND bizIdentity='{$bizIdentity}' AND bizOrderId='{$bizOrderId}'"))
			{
				$payNo[]	 = $tradeInfo['tradeNo'];
				$bizIdentity = $tradeInfo['bizIdentity'];
			} 
			else
			{
				// 交易号
				$tradeNo 		= $deposit_trade_mod->genTradeNo();
			
				// 付款者ID
				$buyer_id   	= intval($this->visitor->get('user_id'));
				
				if(in_array($bizIdentity, array(TRADE_ORDER))) 
				{
					$order_info 	= $this->_order_mod->get("order_sn='{$bizOrderId}' AND buyer_id=".$this->visitor->get('user_id'));
					if(!$order_info)
					{
						$errorMsg = Lang::get('no_such_order');
						break;
					}
					
					$amount 		= $order_info['order_amount'];
					$title 			= $this->_order_mod->getOrderSubjectByOrder($order_info['order_id']);
					
					/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
					$depopay_type    =&  dpt('outlay', 'buygoods');
					$result 		= $depopay_type->submit(array(
						'trade_info' => array('user_id' => $order_info['buyer_id'], 'party_id' => $order_info['seller_id'], 'amount' => $amount),
						'extra_info' => $order_info + array('tradeNo' => $tradeNo, 'bizOrderId' => $bizOrderId, 'bizIdentity' => $bizIdentity, 'title' => $title),
						'post'		 =>	$_POST,
					));
				
					if(!$result)
					{
						$errorMsg = $depopay_type->_get_errors();
						break;
					}
				}
				if(in_array($bizIdentity, array(TRADE_BUYAPP)))
				{
					/* 取出购买信息 */
					$appbuylog_mod 	= &m('appbuylog');
					$appbuylog 		= $appbuylog_mod->get("user_id=" . $this->visitor->get('user_id') . " AND orderId='{$bizOrderId}'");
					
					if(!$appbuylog)
					{
						$errorMsg = Lang::get('no_such_order');
						break;
					}
					
					$amount 		= $appbuylog['amount'];
					$title 			= sprintf(Lang::get('subject_for_payapp'), Lang::get($appbuylog['appid']), $appbuylog['period']);
					
					/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
					$depopay_type    =&  dpt('outlay', 'buyapp');
					$result 		= $depopay_type->submit(array(
						'trade_info' => array('user_id' => $appbuylog['user_id'], 'party_id' => 0, 'amount' => $amount),
						'extra_info' => $appbuylog + array('tradeNo' => $tradeNo, 'bizOrderId' => $bizOrderId, 'bizIdentity' => $bizIdentity, 'title' => $title),
						'post'		 =>	$_POST,
					));
					
					if(!$result)
					{
						$errorMsg = $depopay_type->_get_errors();
						break;
					}
				}
				
				$payNo[] = $tradeNo;
			}
		}
		
		if($errorMsg !== FALSE)
		{
			$this->show_warning($errorMsg);
			return;
		}
		
		sort($payNo, SORT_STRING);
		$orderId = implode(',', $payNo);

		// 交易有效期，交易订单不付款，一个小时候过期（预留，暂时没有用到）
		$timestamp = gmtime() + 3600;
		$url = site_url() . "/index.php?app=depopay&act=pay&orderId={$orderId}&timestamp={$timestamp}";
		
		header("Location:{$url}");

	}
	
    function pay()
    {
		$orderId		= html_script(trim($_GET['orderId']));
		
		if(!IS_POST)
		{
			list($errorMsg, $orderInfo) = $this->_deposit_trade_mod->_checkAndGetTradeInfo($orderId, $this->visitor->get('user_id'));
			if($errorMsg !== FALSE) {
				$this->show_warning($errorMsg);
				return;
			}
		
			/* 如果是充值订单的付款，则跳转到充值提交页面（暂不考虑合并付款的情况） */
			if(in_array($orderInfo['bizIdentity'], array(TRADE_RECHARGE))) {
				
				// 暂不考虑合并付款的情况
				$tradeInfo = current($orderInfo['tradeList']);
				
				$this->assign('payform', array(
					'gateway' 	=> 'index.php?app=deposit&act=recharge',
					'method'  	=> 'POST',
					'params' 	=> array('tradeNo' => $tradeInfo['tradeNo'])
				));
				$this->display('cashier.payform.html');
				exit;
			}
			
			/* 如果是购买APP应用服务的订单，并且支付金额为0，则跳转到应用市场购物车页面（暂不考虑合并付款的的情况） */
			if(in_array($orderInfo['bizIdentity'], array(TRADE_BUYAPP))) {
				
				// 暂不考虑合并付款的情况
				$tradeInfo = current($orderInfo['tradeList']);
				
				// 兼容处理
				if($orderInfo['amount'] == 0)
				{
					$appbuylog_mod 	= &m('appbuylog');
					$appbuylog 		= $appbuylog_mod->get(array(
						"conditions" 	=> "user_id=" . $this->visitor->get('user_id') . " AND orderId='{$tradeInfo['bizOrderId']}'",
						"fields"		=> "bid",
					));
					
					header('location:index.php?app=appmarket&act=cashier&id=' . $appbuylog['bid']);
					exit;
				}
			}
		
			$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
			
			$payment_model =& m('payment');
			list($all_payments, $cod_payments, $errorMsg) = $payment_model->getAvailablePayments($orderInfo, $this->visitor->get('user_id'), TRUE, TRUE);
			if($errorMsg !== FALSE) {
				$this->show_warning($errorMsg);
				return;
			}
			$this->assign('payments', $all_payments);
		
			/* 当前位置 */
        	$this->_curlocal(LANG::get('deposit'), 'index.php?app=deposit', LANG::get('deposit_cashier'));
			$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
			$this->assign('orderInfo', $orderInfo);
			$this->assign('deposit_account', $deposit_account);
			$this->assign('paynotice', sprintf(Lang::get('paynotice'), Conf::get('site_name')));
			$this->_config_seo('title', Lang::get('cashier') . ' - ' . Conf::get('site_title'));
			$this->display('depopay.index.html');
		}
		else
		{
			$payment_method = trim($_POST['payment_code']);
			
			list($payment_code) = $this->_deposit_trade_mod->getPaymentDetail($payment_method);
			
			// 如果是余额支付，验证支付密码
			if(in_array($payment_code, array('deposit'))) {
				$password 		= trim($_POST['password']);
				if(!$this->_deposit_account_mod->_check_account_password($password, $this->visitor->get('user_id'))){
					$this->show_warning('password_error');
            		return;
				}
			}
			
			list($errorMsg, $orderInfo) = $this->_deposit_trade_mod->_checkAndGetTradeInfo($orderId, $this->visitor->get('user_id'));
			if($errorMsg !== FALSE) {
				$this->show_warning($errorMsg);
				return;
			}
			
			$payment_model =& m('payment');
			
			/* 买家选择的支付方式数组（考虑使用支付宝直连网银支付的情况），更新到交易表 */
			list($payment_code, $payment_bank) = $this->_deposit_trade_mod->_updateTradePayment($orderInfo, $payment_method);
			
			/* 检查用户所使用的付款方式是否在允许的范围内 */
			list($all_payments, $cod_payments) = $payment_model->getAvailablePayments($orderInfo, $this->visitor->get('user_id'), TRUE, TRUE);
			if(!in_array($payment_code, $payment_model->getKeysOfPayments($all_payments)))
			{
				$this->show_warning('payment_not_available');
				return;
			}
			
			$payment_info = $all_payments[$payment_code];
			
			if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) {
				$isCod = strtoupper($payment_code) == 'COD';
				$this->_order_mod->_updateOrderPayment($orderInfo, $isCod ? $cod_payments : $payment_info, $isCod);
			}
		
			/* 生成支付URL或表单 */
			$payment    = $this->_get_payment($payment_code, $payment_info);
			$payment_form = $payment->get_payform($orderInfo);
			
			/* 通过其中一笔记录，获取商户交易号 */
			$getTradeInfo = end($orderInfo['tradeList']);
			$payTradeNo = $getTradeInfo['payTradeNo'];
				
			/* 跳转到真实收银台（货到付款和余额支付不会跳转） */
			$this->_config_seo('title', Lang::get('cashier'));
			$this->assign('payform', $payment_form);
			$this->assign('payment', $payment_info);
			$this->assign('payTradeNo', $payTradeNo);
			header('Content-Type:text/html;charset=' . CHARSET);
			$this->display('deposit.payform.html');
		}
    }
}

?>
