<?php

class CashierApp extends ApibaseApp
{
	var $_order_mod;
	var $_ordergoods_mod;
	var $_deposit_account_mod;
	var $_deposit_record_mod;
	var $_deposit_trade_mod;
	
	/* 构造函数 */
    function __construct()
    {
         $this->CashierApp();
    }

    function CashierApp()
    {
        parent::__construct();
		$this->_order_mod					= &m('order');
		$this->_ordergoods_mod 				= &m('ordergoods');
		$this->_deposit_account_mod			= &m('deposit_account');
		$this->_deposit_record_mod  		= &m('deposit_record');
		$this->_deposit_trade_mod 			= &m('deposit_trade');		
    }
	
	function check_valid()
	{
		$this->_checkUserAccess();
		
        /* 外部提供订单号 */
        $order_id = isset($this->PostData['order_id']) ? html_script($this->PostData['order_id']) : 0;
        if (!$order_id)
        {
            $this->json_fail('no_such_order');
            return;
        }
		
		$orderIds = explode(',', $order_id);
		if(!is_array($orderIds))
		{
			$this->json_fail('no_such_order');
            return;
		}
		
		$order_model =& m('order');
		$orderList  = $order_model->find("order_id " . db_create_in($orderIds) . " AND buyer_id=" .$this->PostData['user_id']);
        if (empty($orderList))
        {
            $this->json_fail('no_such_order');
            return;
		}
		
		$bizOrderId = array();
		foreach($orderList as $order_info)
		{
			$bizOrderId[] = $order_info['order_sn'];
		}
		
		$this->json_success(array(
			'merchantId' 	=> MERCHANTID, 
			'bizOrderId' 	=> implode(',', $bizOrderId),
			'bizIdentity'	=> TRADE_ORDER,
		));
	}
	
	/* 接收交易数据的网关 */
	function gateway()
	{
		$this->_checkUserAccess();
		// 商户号
		$merchantId 	= html_script(trim($this->PostData['merchantId']));
			
		// 商户订单号（合并付款时有多个）
		$bizOrderIdList	= explode(',', html_script(trim($this->PostData['bizOrderId'])));
		
		// 订单类型
		$bizIdentity	= html_script(trim($this->PostData['bizIdentity']));
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
				$buyer_id   	= intval($this->PostData['user_id']);
				
				if(in_array($bizIdentity, array(TRADE_ORDER))) 
				{
					$order_info 	= $this->_order_mod->get("order_sn='{$bizOrderId}' AND buyer_id=".$this->PostData['user_id']);
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
						'post'		 =>	$this->PostData,
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
					$appbuylog 		= $appbuylog_mod->get("user_id=" . $this->PostData['user_id'] . " AND orderId='{$bizOrderId}'");
					
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
			$this->json_fail($errorMsg);
			return;
		}
		
		sort($payNo, SORT_STRING);
		$orderId = implode(',', $payNo);
		
		list($errorMsg, $orderInfo) = $this->_deposit_trade_mod->_checkAndGetTradeInfo($orderId, $this->PostData['user_id']);
		if($errorMsg !== FALSE) {
			$this->json_fail($errorMsg);
			return;
		}
			
		$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->PostData['user_id']));
			
		$payment_model =& m('payment');
		list($all_payments, $cod_payments, $errorMsg) = $payment_model->getAvailablePayments($orderInfo, $this->PostData['user_id'], TRUE, TRUE);
		if($errorMsg !== FALSE) {
			$this->json_fail($errorMsg);
			return;
		}
		
		if(!empty($all_payments)){
			foreach($all_payments as $key=>$val){
				if(!in_array($key, array( 'wxminiprogram','deposit'))){//小程序只允许小程序支付和预存款支付
					unset($all_payments[$key]);
				}
			}
		}
		
		$this->json_success(array(
			'orderId'  => $orderId,
			'payments' => $all_payments,
			'orderInfo' => $orderInfo,
			'deposit_account' => $deposit_account
		));
	}
}

?>