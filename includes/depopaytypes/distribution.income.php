<?php

/**
 *	  资金流向：收入
 *    支出类型：分销资金分配
 
 1. 三级分销利润分配
 2. 扣除卖家设置的分销资金
 
 *
 *    @author   Mimall
 *    @usage    none
 */
class DistributionIncome extends IncomeDepopay
{
	// 针对交易记录的交易分类，值有：购物：SHOPPING； 理财：FINANCE；缴费：CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat	   	= 'TRANSFER'; 
	
	// 针对财务明细的交易类型，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType 	= 'TRANSFER';
	
	// 支付类型，值有：即时到帐：INSTANT；担保交易：SHIELD；货到付款：COD
	var $_payType   	= 'INSTANT';	
	
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
		
		/* 分配分销费用 */
		if(!$this->_insert_record_info($trade_info,$extra_info)){
			return FALSE;
		}
		
		/* 更新分销统计 */
		$this->_update_statistics($extra_info);
		
		return TRUE;
	}

	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($trade_info, $extra_info)
	{
		$result = TRUE;
		$time = gmtime();
		
		foreach($extra_info['d_profit'] as $key => $info)
		{
			// 金额为0不分配
			if($info['amount'] <= 0) {
				continue;
			}
			
			$data_trade = array(
				'tradeNo'		=>	$this->_deposit_trade_mod->genTradeNo(),
				'merchantId'	=>	MERCHANTID,
				'bizOrderId'	=>  $extra_info['order_sn'],
				'bizIdentity'	=>  TRADE_FX,
				'buyer_id'		=>	$info['user_id'],
				'seller_id'		=>	$extra_info['seller_id'],
				'amount'		=>	$info['amount'],
				'status'		=>	'SUCCESS',
				'payment_code'  =>  'deposit',
				'fundchannel'	=> 	Lang::get('deposit'),
				'tradeCat'		=>	$this->_tradeCat,
				'payType'		=>  $this->_payType,
				'flow'			=>	$this->_flow_name,
				'title'			=>	Lang::get('distribution_order_fenpei'),
				'seller_remark' =>  Lang::get('distribution_'.($key+1)),
				'add_time'		=>	$time,
				'pay_time'		=>	$time,
				'end_time'		=>	$time
			);
			
			if($this->_deposit_trade_mod->add($data_trade)) 
			{
				// 转出的账户
				$data_record = array(
					'tradeNo'		=>	$data_trade['tradeNo'],
					'user_id'		=> 	$extra_info['seller_id'],
					'amount'		=>  $info['amount'],
					'balance'		=>	$this->_get_deposit_balance($extra_info['seller_id']) - $info['amount'], // 扣除后的余额
					'tradeType'		=>  $this->_tradeType,
					'tradeTypeName' => 	Lang::get('distribution_outlay'),
					'flow'			=>	'outlay',
					'name'			=>  Lang::get('distribution_'.($key+1)),
					'remark'		=>  sprintf(Lang::get('distribution_order_tradeNo'), $extra_info['tradeNo'])
				);
				
				$step1 = parent::_insert_deposit_record($data_record);
				
				if($step1)
				{
					// 转入的账户
					$data_record = array(
						'tradeNo'		=>	$data_trade['tradeNo'],
						'user_id'		=> 	$info['user_id'],
						'amount'		=>  $info['amount'],
						'balance'		=>	$this->_get_deposit_balance($info['user_id'], $info['amount']), // 增加后的余额
						'tradeType'		=>  $this->_tradeType,
						'tradeTypeName' => 	Lang::get('distribution_income'),
						'flow'			=>	$this->_flow_name,
						'name'			=>  Lang::get('distribution_'.($key+1)),
						'remark'		=>  sprintf(Lang::get('distribution_order_tradeNo'), $extra_info['tradeNo'])
					);
		
					$step2 = parent::_insert_deposit_record($data_record);
					
					if(!$step2) {
						$this->_errorCode[] = "70002";
						$result = FALSE;
						break;
					}
					
				}
				else
				{
					$this->_errorCode[] = "70001";
					$result = FALSE;
					break;
				}
			}
		}
		return $result;
	}
	
	function _update_statistics($extra_info)
	{
		$model_distribution_statistics = &m('distribution_statistics');
		$model_distribution_statistics->update_statistics($extra_info['d_profit']);
	}
}

?>