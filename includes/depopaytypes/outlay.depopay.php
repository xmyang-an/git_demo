<?php

/**
 *    支出类型基类
 *	 
 *	  资金流向：支出
 *
 *    @author   Mimall
 *    @usage    none
 */
class OutlayDepopay extends BaseDepopay
{
    var $_flow_name = 'outlay';
	
	
	function _handle_trade_info($trade_info, $post, $checkAmount = TRUE)
	{
		/* 如果是退款操作，无需验证金额是否足够 */
		if($checkAmount === FALSE){
			return TRUE;
		}
		
		/* 验证是否有足够的金额用于支出 */
		if(isset($trade_info['amount'])) {
			
			$money = $trade_info['amount'];
			if($money < 0) {
				$this->_errorCode[] = "50002";
				return FALSE;
			}
			
			/* 如果需要扣服务费，则加上服务费后再验证 */
			if(isset($trade_info['fee'])) {
				if($trade_info['fee'] < 0) {
					$this->_errorCode[] = "50001";
					return FALSE;
				}
				
				$money += $trade_info['fee'];
			}
			
			if(!parent::_check_enough_money($money, $trade_info['user_id'])) {
				$this->_errorCode[] = "50019";
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/* 获取扣除本期支出后的账户余额 */
	function _get_deposit_balance($user_id, $amount=0)
	{
		$money = parent::_get_deposit_balance($user_id);
		
		/* 如果不传扣除的金额，则直接返回账户余额 */
		if(!$amount) return $money;
		
		return $money - $amount;
	}
	
	
}

?>
