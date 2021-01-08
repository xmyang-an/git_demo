<?php

/**
 *    余额支付
 *
 *    @author   MiMall
 *    @usage    none
 */
class DepositPayment extends BasePayment
{
	/* 余额支付网关 */
    var $_gateway   =   '';
    var $_code 		= 	'deposit';

    /**
     *    获取支付表单
     *
     *    @author    MiMall
     *    @param     array $orderInfo  待支付的订单信息，必须包含总费用及唯一外部交易号
     *    @return    array
     */
    function get_payform(&$orderInfo = array())
    {
		// 支付网关商户订单号
		$payTradeNo = $this->_get_trade_sn($orderInfo);
		// 给其他页面使用
		foreach($orderInfo['tradeList'] as $key => $val) {
			$orderInfo['tradeList'][$key]['payTradeNo'] = $payTradeNo;
		}
		
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) {
			$this->_handleOrderByBalancepay($orderInfo, $payTradeNo);
		}
		elseif(in_array($orderInfo['bizIdentity'], array(TRADE_BUYAPP))) {
			$this->_handleBuyappByBalancepay($orderInfo, $payTradeNo);
		}
		
		header('Location:index.php?app=paynotify&payTradeNo='.$payTradeNo);
		exit;
	}
	
	function GetPayform(&$orderInfo = array())
    {
		// 支付网关商户订单号
		$payTradeNo = $this->_get_trade_sn($orderInfo);
		// 给其他页面使用
		foreach($orderInfo['tradeList'] as $key => $val) {
			$orderInfo['tradeList'][$key]['payTradeNo'] = $payTradeNo;
		}
		
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) {
			$this->_handleOrderByBalancepay($orderInfo, $payTradeNo);
		}
		elseif(in_array($orderInfo['bizIdentity'], array(TRADE_BUYAPP))) {
			$this->_handleBuyappByBalancepay($orderInfo, $payTradeNo);
		}
	}
	
	/* 处理使用余额支付的购物订单 */
	function _handleOrderByBalancepay($orderInfo = array(), $payTradeNo = '')
	{
		$order_mod		= &m('order');
		$errorMsg 		= array();
		$ECBaseApp 		= new ECBaseApp();
		
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('outlay', 'buygoods');
		
		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$tradeNo 	= $tradeInfo['tradeNo'];
			$order_info = $order_mod->get("order_sn='{$tradeInfo['bizOrderId']}'");
			
			if (empty($order_info))
			{
				$errorMsg[$tradeInfo['bizOrderId']] = Lang::get('order_info_empty');
				continue;
			}
			
			$result 		= $depopay_type->respond_notify(array(
				'trade_info' => array('user_id' => $tradeInfo['buyer_id'], 'party_id' => $tradeInfo['seller_id'], 'amount' => $tradeInfo['amount']),
				'extra_info' => $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
				'post'		 =>	$_POST,
			));
				
			if(!$result)
			{
				$errorMsg[$tradeInfo['bizOrderId']] = $depopay_type->_get_errors();
				continue;
			}
					
			/* 短信和邮件提醒： 买家已付款通知卖家 */
			$ECBaseApp->sendMailMsgNotify($order_info, array(
					'key' => 'toseller_online_pay_success_notify'
				),
				array(
					'key' => 'pay', 
					'body' => sprintf(Lang::get('sms_pay'), $order_info['order_sn'], $order_info['buyer_name'])
				)
			);
		}
	}
	
	/* 处理余额购买应用的订单 */
	function _handleBuyappByBalancepay($orderInfo = array(), $payTradeNo = '')
	{
		$appbuylog_mod 	= &m('appbuylog');
		$errorMsg 		= array();
		
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('outlay', 'buyapp');
		
		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$tradeNo 	= $tradeInfo['tradeNo'];
			$order_info = $appbuylog_mod->get("orderId='{$tradeInfo['bizOrderId']}'");
		
			if (empty($order_info))
			{
				$errorMsg[$tradeInfo['bizOrderId']] = Lang::get('order_info_empty');
				continue;
			}
			
			$result 		= $depopay_type->respond_notify(array(
				'trade_info' => array('user_id' => $tradeInfo['buyer_id'], 'party_id' => 0, 'amount' => $tradeInfo['amount']),
				'extra_info' => $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
				//'post'		 =>	$_POST,
			));
					
			if(!$result)
			{
				$errorMsg[$tradeInfo['bizOrderId']] = $depopay_type->_get_errors();
				continue;
			}	
		}
	}
}

?>