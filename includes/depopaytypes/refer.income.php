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
class ReferIncome extends IncomeDepopay
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
		
		return TRUE;
	}
	
	function _insert_record_info($trade_info, $extra_info)
	{
		$result = TRUE;
		
		$refer_reward = unserialize($extra_info['refer_reward']);
		if(!empty($refer_reward))
		{
			$time = gmtime();
			
			$member_mod = &m('member');
			foreach($refer_reward as $key=>$val)
			{
				if(!$key){
					continue;
				}
				
				$amount = 0;
				foreach($val['total'] as $k=>$v)
				{
					$amount += $v;
				}
				
				if($amount <= 0)
				{
					continue;
				}
				
				$info = $member_mod->get(array(
					'conditions' => 'user_id='.$key,
					'fields'     => 'user_name'
				));
				
				$data_trade = array(
					'tradeNo'		=>	$this->_deposit_trade_mod->genTradeNo(),
					'merchantId'	=>	MERCHANTID,
					'bizOrderId'	=>  $extra_info['order_sn'],
					'bizIdentity'	=>  TRADE_FX,
					'buyer_id'		=>	$info['user_id'],
					'seller_id'		=>	$extra_info['seller_id'],
					'amount'		=>	$amount,
					'status'		=>	'SUCCESS',
					'payment_code'  =>  'deposit',
					'fundchannel'	=> 	Lang::get('deposit'),
					'tradeCat'		=>	$this->_tradeCat,
					'payType'		=>  $this->_payType,
					'flow'			=>	$this->_flow_name,
					'title'			=>	sprintf('支付%s级分销佣金,佣金被分销商%s获得，订单号：%s',$val['layer'], $info['user_name'],$extra_info['order_sn']),
					'seller_remark' =>  sprintf('支付%s级分销佣金',$val['layer']),
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
						'amount'		=>  $amount,
						'balance'		=>	$this->_get_deposit_balance($extra_info['seller_id']) - $amount, // 扣除后的余额
						'tradeType'		=>  $this->_tradeType,
						'tradeTypeName' => 	Lang::get('distribution_outlay'),
						'flow'			=>	'outlay',
						'name'			=>  sprintf('支付%s级分销佣金',$val['layer']),
						'remark'		=>  sprintf('支付%s级分销佣金,佣金被分销商%s获得，订单号：%s',$val['layer'], $info['user_name'],$extra_info['order_sn'])
					);
					
					$step1 = parent::_insert_deposit_record($data_record);
					
					if($step1)
					{
						// 转入的账户
						$data_record = array(
							'tradeNo'		=>	$data_trade['tradeNo'],
							'user_id'		=> 	$info['user_id'],
							'amount'		=>  $amount,
							'balance'		=>	$this->_get_deposit_balance($info['user_id'], $amount), // 增加后的余额
							'tradeType'		=>  $this->_tradeType,
							'tradeTypeName' => 	Lang::get('distribution_income'),
							'flow'			=>	$this->_flow_name,
							'name'			=>  sprintf('获得%s级分销佣金',$val['layer']),
							'remark'		=>  sprintf('获得%s级分销佣金,佣金由店铺%s支付，订单号：%s',$val['layer'], $extra_info['seller_name'],$extra_info['order_sn'])
						);
			
						parent::_insert_deposit_record($data_record);
						
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
}

?>