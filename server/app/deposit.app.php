<?php

class DepositApp extends ApibaseApp
{
	var $_deposit_account_mod;
	var $_deposit_record_mod;
	var $_deposit_trade_mod;
	var $_order_mod;
	
	/* 构造函数 */
    function __construct()
    {
         $this->DepositApp();
    }

    function DepositApp()
    {
		$this->_deposit_account_mod			= &m('deposit_account');
		$this->_deposit_record_mod  		= &m('deposit_record');
		$this->_deposit_trade_mod 			= &m('deposit_trade');		
		$this->_order_mod                   = &m('order');
    }
	
	function check_password()
	{
		$this->_checkUserAccess();
		
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$password = html_script($this->PostData['password']);
		if(!$password){
			$this->json_fail('请输入支付密码');
			exit;
		}
		
		$account = $this->_deposit_account_mod->get('user_id='.$user_id.' AND password="'.md5($password).'"');
		if(empty($account)){
			$this->json_fail('支付密码不正确');
		}
		else{
			$this->json_success('check_access');
		}
	}
	
	//小程序支付获得支付参数
	function ajaxParameters()
	{
		$this->_checkUserAccess();
		
		$payTradeNo    	= isset($this->PostData['payTradeNo']) ? html_script(trim($this->PostData['payTradeNo'])) : 0;
		$code       	= html_script($this->PostData['code']);

        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->json_fail('order_info_empty');
            return;
        }
	
		$deposit_trade_mod 	= &m('deposit_trade');
		
		$orderInfo = $deposit_trade_mod->_getTradeInfoForNotify($payTradeNo);
		if (!$orderInfo)
        {
            /* 无效的通知请求 */
            $this->json_fail('order_info_empty');
            return;
        }
		
		if(!in_array($orderInfo['payment_code'], array('wxminiprogram'))){
			$this->json_fail('Hacking Attempt');
			return;
		}
		
		$payment_code = $orderInfo['payment_code'];

		$payment_model =& m('payment');
		$payment_info  = $payment_model->get("payment_code = '{$payment_code}'");
			
		$payment    = $this->_get_payment($payment_code, $payment_info);
		$jsApiParameters = $payment->getParameters($code, $orderInfo, $payTradeNo);	

		$this->json_success(ecm_json_decode($jsApiParameters,true));
	}
	
	function GetPayTradeNo()
	{
		$this->_checkUserAccess();
		
		$payment_method = trim($this->PostData['payment_method']);
		$tradeNo 	    = html_script(trim($this->PostData['tradeNo']));
		
		list($payment_code) = $this->_deposit_trade_mod->getPaymentDetail($payment_method);
		
		list($errorMsg, $orderInfo) = $this->_deposit_trade_mod->_checkAndGetTradeInfo($tradeNo, $this->PostData['user_id']);
		if($errorMsg !== FALSE) {
			$this->json_fail($errorMsg);
			return;
		}
		
		list($payment_code, $payment_bank) = $this->_deposit_trade_mod->_updateTradePayment($orderInfo, $payment_method);
			
		$payment_model =& m('payment');
			
        /* 检查用户所使用的付款方式是否在允许的范围内 */
		list($all_payments, $cod_payments) = $payment_model->getAvailablePayments($orderInfo, $this->PostData['user_id'], TRUE, TRUE);
		if(!in_array($payment_code, $payment_model->getKeysOfPayments($all_payments)))
		{
				$this->json_fail('payment_not_available');
				return;
		}
			
		$payment_info = $all_payments[$payment_code];
			
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) {
			$this->_order_mod->_updateOrderPayment($orderInfo, $payment_info, false);
		}
			
		$payment    = $this->_get_payment($payment_code, $payment_info);
		$payment_form = $payment->GetPayform($orderInfo);
		
		$getTradeInfo = end($orderInfo['tradeList']);
		$payTradeNo = $getTradeInfo['payTradeNo'];
		
		$this->json_success($payTradeNo);
	}
	
	function _getRechargeAvailablePayments()
	{
		$payment_model =& m('payment');
		$payments = $payment_model->get_enabled(0);
		$all_payments = array();
			
		foreach ($payments as $key => $payment)
		{
			if ($payment['is_online'])
			{
				// 充值操作不支持余额付款方式
				if(!in_array($payment['payment_code'], array('deposit'))) {
					$all_payments[] = $payment;
				}
			}
		}
		return $all_payments;
	}
	
	function paymentResult()
	{
		$this->_checkUserAccess();
		
		$payTradeNo    = isset($this->PostData['payTradeNo']) ? trim($this->PostData['payTradeNo']) : 0;
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->json_fail('forbidden');
            return;
        }

		$deposit_trade_mod 	= &m('deposit_trade');
		
		// 检索出最后支付的单纯充值或购物（或购买应用）订单，如果最后一笔是支付成功的，那么认为都是支付成功了
		$tradeInfo = $deposit_trade_mod->get(array(
			'conditions' => 'buyer_id='.$this->PostData['user_id']." AND payTradeNo='{$payTradeNo}'", 'order' => 'trade_id DESC'));
	
		if(empty($tradeInfo))
		{
			// 由于支付变更，通过商户交易号找不到对应的交易记录后，插入的资金退回记录
			$tradeInfo = $deposit_trade_mod->get(array(
				'conditions' => 'buyer_id='.$this->visitor->get('user_id')." AND tradeNo='{$payTradeNo}'", 'order' => 'trade_id DESC'));
				
			// 资金退回标记
			if($tradeInfo) $tradeInfo['RETURN_MONEY']	= TRUE;
		}
		
		if(empty($tradeInfo))
		{
			$this->json_fail('trade_info_empty');
			return;
		}
		
		if(in_array($tradeInfo['status'], array('PENDING', 'CLOSED'))){
			$this->json_fail('支付失败');
			return;
		}
		
		
		$this->json_success('','支付成功');
	}
	
	function recharge()
	{
		$this->_checkUserAccess();
		
		$tradeNo 	    = html_script(trim($this->PostData['tradeNo']));	
		if(!$tradeNo)
		{
			// 创建充值交易号
			$tradeNo 			= $this->_deposit_trade_mod->genTradeNo();
			$payment_method 	= html_script(trim($this->PostData['payment_method']));
			$money 				= html_script(trim($this->PostData['money']));
			
			/* 买家选择的支付方式数组（考虑使用支付宝直连网银支付的情况） */
			$payment_method = explode('|', $payment_method);
			list($payment_code, $payment_bank) = array($payment_method[0], $payment_method[1]);
		}
		// 如果是待付款的充值，再付款，则不需要再插入记录
		else
		{
			$tradeInfo = $this->_deposit_trade_mod->get("tradeNo='{$tradeNo}' AND buyer_id=" . $this->PostData['user_id']);
			if($tradeInfo)
			{
				$payment_code = $tradeInfo['payment_code'];
				$payment_bank = $tradeInfo['payment_bank'];
				$money = $tradeInfo['amount'];
			}
		}
		
		if(!$tradeInfo)
		{
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('income', 'recharge');
					
			/* 插入充值记录表，状态为：待付款 */
			$result 	= $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $this->PostData['user_id'], 'party_id' => 0, 'amount' => $money),
				'extra_info' =>  array('tradeNo' => $tradeNo, 'is_online' => 1),
				'post'		 =>	 $_POST + array('payment_code'=> $payment_code, 'payment_bank' => $payment_bank),
			));
				
			if(!$result)
			{
				$this->json_fail($depopay_type->_get_errors());
				return;
			}
		}
		
		$this->json_success(array('orderId' => $tradeNo));		
	}
}

?>