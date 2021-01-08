<?php

/**
 *	  资金流向：支出
 *    支出类型：转账
 *
 *    @author   Mimall
 *    @usage    none
 */
class TransferOutlay extends OutlayDepopay
{
    // 针对交易记录的交易类型，值有：购物：SHOPPING； 理财：FINANCE；缴费：PUC_CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat	= 'TRANSFER'; 
	
	// 针对财务明细的资金用途，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType = 'TRANSFER';
	
	// 支付类型，值有：即时到帐：INSTANT；担保交易：SHIELD；货到付款：COD
	var $_payType   = 'INSTANT';
	
	
	function submit($data)
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
		
		//$tradeNo = $extra_info['tradeNo'];
		
		/* 开始插入收支记录 */
		if(!$this->_insert_record_info($trade_info, $extra_info, $post)) {
			return FALSE;
		}
		
		/* 如果有转账手续费，则扣除转出账户的手续费 */
		if($transfer_rate = $this->_get_deposit_setting($trade_info['user_id'], 'transfer_rate')) {
			if(!parent::_sys_chargeback($extra_info['tradeNo'], $trade_info, $transfer_rate, 'transfer_fee')) {
				$this->_errorCode[] = "50015";
				return FALSE;
			}
		}
					
		return TRUE;
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($trade_info, $extra_info, $post)
	{
		$time = gmtime();
		$deposit_trade_mod 	= &m('deposit_trade');
		$bizOrderId			= $deposit_trade_mod->genTradeNo(12);
		
		$data_trade = array(
			'tradeNo'		=>	$extra_info['tradeNo'],
			'merchantId'	=>	MERCHANTID,
			'bizOrderId'	=>  $bizOrderId,
			'bizIdentity'	=>  TRADE_TRANS,
			'buyer_id'		=>	$trade_info['user_id'],
			'seller_id'		=>	$trade_info['party_id'],
			'amount'		=>	$trade_info['amount'],
			'status'		=>	'SUCCESS',
			'payment_code'  =>  'deposit',
			'fundchannel'	=> 	Lang::get('deposit'),
			'tradeCat'		=>	$this->_tradeCat,
			'payType'		=>  $this->_payType,
			'flow'			=>	$this->_flow_name,
			'title'			=>	LANG::get(strtolower($this->_tradeType)),
			'buyer_remark'	=>	$post['remark'],
			'add_time'		=>	$time,
			'pay_time'		=>	$time,
			'end_time'		=>	$time
		);
		
		if($deposit_trade_mod->add($data_trade)) 
		{
			// 转出的账户
			$data_record = array(
				'tradeNo'		=>	$extra_info['tradeNo'],
				'user_id'		=> 	$trade_info['user_id'],
				'amount'		=>  $trade_info['amount'],
				'balance'		=>	$this->_get_deposit_balance($trade_info['user_id'], $trade_info['amount']), // 扣除后的余额
				'tradeType'		=>  $this->_tradeType,
				'tradeTypeName' => 	Lang::get(strtoupper($this->_tradeType)),
				'flow'			=>	$this->_flow_name,
			);
			
			$step1 = parent::_insert_deposit_record($data_record);
			
			if($step1)
			{
				// 转入的账户
				$data_record['tradeNo']			= 	$extra_info['tradeNo'];
				$data_record['user_id']			= 	$trade_info['party_id'];
				$data_record['amount']			=   $trade_info['amount'];
				$data_record['balance']			=	$this->_get_deposit_balance($trade_info['party_id']) + $trade_info['amount']; // 增加后的余额
				$data_record['tradeType']   	=   $this->_tradeType;
				$data_record['tradeTypeName'] 	=   Lang::get(strtoupper($this->_tradeType));
				$data_record['flow']			=	'income';
	
				$step2 = parent::_insert_deposit_record($data_record);
				
				if(!$step2) {
					$this->_errorCode[] = "50012";
					return false;
				}
				
				return true;
			}
			else
			{
				$this->_errorCode[] = "50011";
				return false;
			}
		}
	}
}

?>
