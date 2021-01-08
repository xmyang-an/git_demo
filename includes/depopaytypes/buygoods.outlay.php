<?php

/**
 *	  资金流向：支出
 *    支出类型：购物
 *
 *    @author   Mimall
 *    @usage    none
 */
class BuygoodsOutlay extends OutlayDepopay
{
	// 针对交易记录的交易分类，值有：购物：SHOPPING； 理财：FINANCE；缴费：CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat	   	= 'SHOPPING'; 
	
	// 针对财务明细的交易类型，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType 	= 'PAY';
	
	// 支付类型，值有：即时到帐：INSTANT；担保交易：SHIELD；货到付款：COD
	var $_payType   	= 'SHIELD';
	
	function submit($data)
	{
		/* 释放trade_info和extra_info和post三个变量 */
        extract($data);
		
		if($trade_info['amount'] < 0) {
			$this->_errorCode[] = "10001";
			return FALSE;
		}
		
		$tradeNo = $extra_info['tradeNo'];
		
		if(!$this->_deposit_trade_mod->get("tradeNo='{$tradeNo}'")) 
		{
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
		
		$tradeNo = $extra_info['tradeNo'];
		
		/* 修改交易状态为已付款 */
		if(!$this->_update_trade_status($tradeNo, array('status'=> 'ACCEPTED', 'pay_time' => gmtime()))){
			$this->_errorCode[] = "50024";
			return FALSE;
		}
				
		/* 插入收支记录，并变更账户余额 */
		if(!$this->_insert_record_info($tradeNo, $trade_info, $extra_info)) {
			$this->_errorCode[] = "50020";
			return FALSE;
		}
		
		/* 修改订单状态为已付款 */
		if(!$this->_update_order_status($extra_info['order_id'], array('status'=> ORDER_ACCEPTED, 'pay_time' => gmtime()))) {
			$this->_errorCode[] = "50021";
			return FALSE;
		}
	
		return TRUE;
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($tradeNo, $trade_info, $extra_info)
	{
		$result = TRUE;
		
		//  加此判断，目的为允许提交订单金额为零的处理
		if($trade_info['amount'] > 0)
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
			
			$result = parent::_insert_deposit_record($data_record);
		}
		
		return $result;
	}
}

?>
