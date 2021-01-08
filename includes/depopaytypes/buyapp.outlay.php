<?php

/**
 *	  资金流向：支出
 *    支出类型：购买应用
 *
 *    @author   Mimall
 *    @usage    none
 */
class BuyappOutlay extends OutlayDepopay
{
    // 针对交易记录的交易分类，值有：购物：SHOPPING； 理财：FINANCE；缴费：CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat	   	= 'SHOPPING'; 
	
	// 针对财务明细的交易类型，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType 	= 'PAY';
	
	// 支付类型，值有：即时到帐：INSTANT；担保交易：SHIELD；货到付款：COD
	var $_payType   	= 'INSTANT';
	
	
	function submit($data)
	{
		/* 释放trade_info和extra_info和post三个变量 */
        extract($data);
		
		if($trade_info['amount'] <= 0) {
			$this->_errorCode[] = "10001";
			return FALSE;
		}
		
		$tradeNo = $extra_info['tradeNo'];
		
		if(!$this->_deposit_trade_mod->get("merchantId='".MERCHANTID."' AND tradeNo='{$tradeNo}'")) 
		{
			$time = gmtime();
			
			$data_trade = array(
				'tradeNo'		=> $tradeNo,
				'merchantId'	=> MERCHANTID,
				'bizOrderId'	=> $extra_info['bizOrderId'],
				'bizIdentity'	=> $extra_info['bizIdentity'],
				'buyer_id'		=> $trade_info['user_id'],
				'seller_id'		=> $trade_info['party_id'],
				'amount'		=> $trade_info['amount'],
				'status'		=> 'PENDING',
				'tradeCat'		=> $this->_tradeCat,
				'payType'		=> $this->_payType,
				'flow'     		=> $this->_flow_name,
				'title'			=> $extra_info['title'],
				'buyer_remark'	=> $post['remark'],
				'add_time'		=> gmtime(),
			);
			
			return $this->_deposit_trade_mod->add($data_trade);
		}
		
		return TRUE;
	}
	
	/* 响应通知 */
	function respond_notify($data)
	{
		/* 释放trade_info和extra_info和post三个变量 */
        extract($data);
        /* 处理交易基本信息 */
        $base_info = $this->_handle_trade_info($trade_info, $post);
		
        if (!$base_info)
        {
            /* 基本信息验证不通过 */
            return FALSE;
        }
		
		$time = gmtime();
		$tradeNo = $extra_info['tradeNo'];
		
		/* 修改交易状态为交易完成 */
		if(!$this->_update_trade_status($tradeNo, array('status'=> 'SUCCESS', 'pay_time' => $time, 'end_time' => $time))){
			$this->_errorCode[] = "50024";
			return FALSE;
		}
		
		/* 插入收支记录，并变更账户余额 */
		if(!$this->_insert_record_info($tradeNo, $trade_info, $extra_info)) {
			$this->_errorCode[] = "50020";
			return FALSE;
		}
		
		/* 修改购买应用状态为交易完成 */
		if(!$this->_update_order_status($extra_info['bid'], array('status'=> ORDER_FINISHED, 'pay_time' => $time, 'end_time' => $time))) {
			$this->_errorCode[] = "60003";
			return FALSE;
		}
		
		/* 更新所购买的应用的过期时间 */
		if(!$this->_update_order_period($trade_info, $extra_info)){
			$this->_errorCode[] = "60002";
			return FALSE;
		}
	
		return TRUE;
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($tradeNo, $trade_info, $extra_info)
	{
		$data_record = array(
			'tradeNo'		=>	$tradeNo,
			'user_id'		=>	$trade_info['user_id'],
			'amount'		=> 	$trade_info['amount'],
			'balance'		=>	$this->_get_deposit_balance($trade_info['user_id'], $trade_info['amount']), // 扣除后的余额
			'tradeType'		=>  $this->_tradeType,
			'tradeTypeName' => 	Lang::get(strtoupper($this->_tradeType)),
			'flow'			=>	$this->_flow_name,
		);
		return parent::_insert_deposit_record($data_record);
	}
	
	function _update_order_period($trade_info, $extra_info)
	{
		$result = FALSE;
		
		$period = $extra_info['period'];
		
		$apprenewal_mod = &m('apprenewal');
		if($item_info = $apprenewal_mod->checkIsRenewal($extra_info['appid'], $trade_info['user_id']))
		{
			$expired = strtotime("+{$period} months", $item_info['expired']);
		 	$result = $apprenewal_mod->edit($item_info['rid'], array('expired' => $expired));
		}
		else
		{
			$time = gmtime();
			$expired = strtotime("+{$period} months", $time);
			$result = $apprenewal_mod->add(
				array('appid' => $extra_info['appid'], 'user_id'=> $trade_info['user_id'], 'add_time' => $time, 'expired' => $expired));
		}
		
		/* 更新销量 */
		$appmarket_mod = &m('appmarket');
		$appmarket_mod->edit('appid="'.$extra_info['appid'].'"', "sales = sales + 1");
		
		return $result;
	}
	
	function _update_order_status($bid, $data)
	{
		$appbuylog_mod = &m('appbuylog');
		if(!$appbuylog_mod->edit($bid, $data)){
			return FALSE;			
		}
		return TRUE;
	}
	
}

?>
