<?php

/**
 *    收入类型基类
 *	 
 *	  资金流向：收入
 *
 *    @author   Mimall
 *    @usage    none
 */
class IncomeDepopay extends BaseDepopay
{
    var $_flow_name = 'income';
	
	
	function _handle_trade_info($trade_info, $post)
	{
		/* 验证金额 */
		if(isset($trade_info['amount'])) {
			
			$money = $trade_info['amount'];
			
			/* 如果需要扣服务费 */
			if(isset($trade_info['fee'])) {
				$fee = $trade_info['fee'];
				if($fee < 0 || ($money < $fee)) {
					$this->_errorCode[] = "50001";
					return false;
				}
			}
			
			if($money < 0) {
				$this->_errorCode[] = "50002";
				return false;
			}
		}
		
		return true;
	}
	
	function _handle_order_info($extra_info)
	{
		/* 验证是否有order_sn，因为要通过 order_sn 找出 tradesn */
		if(!isset($extra_info['order_sn']) || empty($extra_info['order_sn'])) {
			$this->_errorCode[] = "50003";
			return false;
		}
		return true;
	}
	
	/* 获取增加本期收入后的账户余额 */
	function _get_deposit_balance($user_id, $amount=0)
	{
		$money = parent::_get_deposit_balance($user_id);
		
		/* 如果不传增加的金额，则直接返回账户余额 */
		if(!$amount) return $money;
		
		return $money + $amount;
	}
	
	
}

?>
