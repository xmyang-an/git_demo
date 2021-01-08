<?php

/**
 *	  资金流向：收入
 *    支出类型：卖出商品
 *
 *    @author   Mimall
 *    @usage    none
 */
class SellgoodsIncome extends IncomeDepopay
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
        /* 处理交易基本信息 */
        $base_info = $this->_handle_trade_info($trade_info, $post);
		$order_info = $this->_handle_order_info($extra_info);
        if (!$base_info || !$order_info)
        {
            /* 基本信息验证不通过 */
            return FALSE;
        }
		
		$tradeNo = $extra_info['tradeNo'];
		
		/* 修改交易状态为交易完成 */
		if(!$this->_update_trade_status($tradeNo, array('status'=> 'SUCCESS', 'end_time' => gmtime()))){
			$this->_errorCode[] = "50022";
			return FALSE;
		}
		
		/* 如果是货到付款的订单，则执行到此就可以结束了 */
		if(in_array($extra_info['payment_code'], array('cod')))
		{
			return TRUE;
			exit;
		}
		
		/* 插入收支记录，并变更账户余额，变更买家收支记录状态，插入卖家收支记录 */
		if(!$this->_insert_record_info($tradeNo, $trade_info, $extra_info)) {
			$this->_errorCode[] = "50008";
			return FALSE;
		}
		
		/* 如果有交易服务费，则扣除卖家手续费 */
		if($trade_rate = $this->_get_deposit_setting($trade_info['user_id'], 'trade_rate')) {
			if(!parent::_sys_chargeback($tradeNo, $trade_info, $trade_rate, 'trade_fee')) {
				$this->_errorCode[] = "50009";
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($tradeNo, $trade_info, $extra_info)
	{	
		// 增加卖家的收支记录
		$data_record['tradeNo']			= 	$tradeNo;
		$data_record['user_id']			=	$trade_info['user_id']; // 卖家ID
		$data_record['amount']  		=   $trade_info['amount'];
		$data_record['balance']			=	$this->_get_deposit_balance($trade_info['user_id'], $trade_info['amount']); // 增加后的余额
		$data_record['tradeType']		=	$this->_tradeType;
		$data_record['tradeTypeName'] 	= 	Lang::get(strtoupper($this->_tradeType));
		$data_record['flow']			=	$this->_flow_name;
			
		/* 插入卖家的收支记录 */
		return parent::_insert_deposit_record($data_record);
	}
}

?>
