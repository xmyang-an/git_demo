<?php

/**
 *    货到付款支付方式
 *
 *    @author    MiMall
 *    @usage    none
 */
class CodPayment extends BasePayment
{
    var $_code = 'cod'; //唯一标识
	
	function get_payform(&$orderInfo = array())
    {
		// 支付网关商户订单号
		$payTradeNo = $this->_get_trade_sn($orderInfo);
		// 给其他页面使用
		foreach($orderInfo['tradeList'] as $key => $val) {
			$orderInfo['tradeList'][$key]['payTradeNo'] = $payTradeNo;
		}
		
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) {
			$this->_handleOrderByCodpay($orderInfo, $payTradeNo);
		}
		header('Location:index.php?app=paynotify&payTradeNo='.$payTradeNo);
		exit;
	}
	
	function _handleOrderByCodpay($orderInfo = array(), $payTradeNo = '')
	{	
		$ECBaseApp = new ECBaseApp();
		$order_mod 			= &m('order');
		$deposit_trade_mod 	= &m('deposit_trade');

		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$tradeNo = $tradeInfo['tradeNo'];
			
			$order_info = $order_mod->get("order_sn='{$tradeInfo['bizOrderId']}'");
			
			if (empty($order_info))
			{
				$errorMsg[$tradeInfo['bizOrderId']] = Lang::get('order_info_empty');
				continue;
			}
			
			/* 修改交易状态为提交 */
			$result = $deposit_trade_mod->edit("tradeNo='{$tradeNo}' AND status= 'PENDING'", array('status' => 'SUBMITTED'));
			
			if(!$result)
			{
				$error = current($deposit_trade_mod->get_error());
				$errorMsg[$tradeInfo['bizOrderId']] = $error['msg'];
				continue;
			}
			
			$order_mod->edit('order_sn='.$tradeInfo['bizOrderId']. ' AND status='.ORDER_PENDING, array('status' => ORDER_SUBMITTED));
															
			/* 邮件提醒：	订单已确认，等待安排发货 */
			$ECBaseApp->sendMailMsgNotify($order_info, array(
					'key' 		=> 'tobuyer_confirm_cod_order_notify',
					'touser' 	=> $order_info['buyer_id']
				)
			);
		}
	}
}

?>