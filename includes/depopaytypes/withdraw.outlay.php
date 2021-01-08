<?php

/**
 *	  资金流向：支出
 *    支出类型：提现
 *
 *    @author   Mimall
 *    @usage    none
 */
class WithdrawOutlay extends OutlayDepopay
{
    // 针对交易记录的交易类型，值有：购物：SHOPPING； 理财：FINANCE；缴费：PUC_CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat		= 'WITHDRAW'; 
	
	// 针对财务明细的资金用途，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType 	= 'WITHDRAW';
	
	// 支付类型，值有：即时到帐：INSTANT；担保交易：SHIELD；货到付款：COD
	var $_payType   	= 'INSTANT';
	
	
	function submit($data)
	{
		/* 释放trade_info和extra_info和post三个变量 */
        extract($data);
		
        /* 处理交易基本信息 */
        $base_info = $this->_handle_trade_info($trade_info, $post);
		$bank_info = $this->_handle_bank_info($post['bid'], $trade_info['user_id']);
        if (!$base_info || !$bank_info)
        {
            /* 基本信息验证不通过 */
            return false;
        }
		
		//$tradeNo = $extra_info['tradeNo'];
		
		/* 开始插入收支记录 */
		if(!$this->_insert_record_info($trade_info, $extra_info, $post)) {
			$this->_errorCode[] = "50016";
			return FALSE;
		}
		
		//插入提现手续费的记录
		if(!$this->_insert_withdraw_fee_info($trade_info, $extra_info, $post)) {
			$this->_errorCode[] = "50016";
			return FALSE;
		}
		
		/* 将提现的金额(加手续费)设置为冻结金额 */
		if(!$this->_update_deposit_frozen($trade_info['user_id'], $trade_info['amount']+$trade_info['fee'], 'add')) {
			$this->_errorCode[] = "50017";
			return FALSE;
		}
		
		/* 插入提现银行的一些信息 */
		if(!$this->_insert_withdraw_info($trade_info, $extra_info, $post)){
			$this->_errorCode[] = "50019";
			return FALSE;
		}
					
		return TRUE;
	}

	function _insert_withdraw_fee_info($trade_info, $extra_info, $post)
	{
		if($trade_info['fee'] > 0)
		{
			$time 				= gmtime();
			$deposit_trade_mod 	= &m('deposit_trade');
			$tradeNo			= $deposit_trade_mod->genTradeNo();
		
			$data_trade = array(
				'tradeNo'		=>	$tradeNo,
				'merchantId'	=>	MERCHANTID,
				'bizOrderId'	=>  $extra_info['tradeNo'],
				'bizIdentity'	=>  TRADE_CHARGE,
				'buyer_id'		=>	$trade_info['user_id'],
				'seller_id'		=>	0,
				'amount'		=>	$trade_info['fee'],
				'status'		=>	'WAIT_ADMIN_VERIFY',
				'payment_code'  =>  'deposit',
				'fundchannel'  	=>  '预存款',
				'tradeCat'		=>	$this->_tradeCat,
				'payType'		=>  $this->_payType,
				'flow'			=>	$this->_flow_name,
				'title'			=>  '提现手续费',
				'buyer_remark'	=>	$post['remark'],
				'add_time'		=>	$time,
				'pay_time'		=>	$time,
			);
			
			if($deposit_trade_mod->add($data_trade)) 
			{
				$data_record = array(
					'tradeNo'		=>	$tradeNo,
					'user_id'		=> 	$trade_info['user_id'],
					'amount'		=>  $trade_info['fee'],
					'balance'		=>	$this->_get_deposit_balance($trade_info['user_id'], $trade_info['fee']), // 扣除后的余额
					'tradeType'		=>  $this->_tradeType,
					'tradeTypeName' => 	'提现手续费',
					'flow'			=>	$this->_flow_name,
				);
				
				return parent::_insert_deposit_record($data_record);	
			}
		}
		
		return TRUE;
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($trade_info, $extra_info, $post)
	{
		$bank = $this->_get_bank_info(intval($post['bid']));
		
		$time 				= gmtime();
		$deposit_trade_mod 	= &m('deposit_trade');
		$bizOrderId			= $deposit_trade_mod->genTradeNo(12);
		
		$data_trade = array(
			'tradeNo'		=>	$extra_info['tradeNo'],
			'merchantId'	=>	MERCHANTID,
			'bizOrderId'	=>  $bizOrderId,
			'bizIdentity'	=>  TRADE_DRAW,
			'buyer_id'		=>	$trade_info['user_id'],
			'seller_id'		=>	0,
			'amount'		=>	$trade_info['amount'],
			'status'		=>	'WAIT_ADMIN_VERIFY',
			'payment_code'  =>  'deposit',
			'fundchannel'  	=>  $bank['bank_name'],
			'tradeCat'		=>	$this->_tradeCat,
			'payType'		=>  $this->_payType,
			'flow'			=>	$this->_flow_name,
			'title'			=>  Lang::get(strtoupper($this->_tradeType)),
			'buyer_remark'	=>	$post['remark'],
			'add_time'		=>	$time,
			'pay_time'		=>	$time,
		);
		
		if($deposit_trade_mod->add($data_trade)) 
		{
			$data_record = array(
				'tradeNo'		=>	$extra_info['tradeNo'],
				'user_id'		=> 	$trade_info['user_id'],
				'amount'		=>  $trade_info['amount'],
				'balance'		=>	$this->_get_deposit_balance($trade_info['user_id'], $trade_info['amount']), // 扣除后的余额
				'tradeType'		=>  $this->_tradeType,
				'tradeTypeName' => 	Lang::get(strtoupper($this->_tradeType)),
				'flow'			=>	$this->_flow_name,
			);
			return parent::_insert_deposit_record($data_record);	
		}
	}
	
	function _insert_withdraw_info($trade_info, $extra_info, $post)
	{
		$deposit_withdraw_mod = &m('deposit_withdraw');
		
		$bank = $this->_get_bank_info(intval($post['bid']));
		unset($bank['bid'], $bank['user_id']);
		
		$tradeInfo = $this->_get_trade_info($extra_info['tradeNo']);
		
		$data_draw = array(
			'orderId'	=>	$tradeInfo['bizOrderId'],
			'user_id'	=>	$trade_info['user_id'],
			'card_info'	=>	serialize($bank),
		);
		
		return $deposit_withdraw_mod->add($data_draw);
	}
	
	function _handle_bank_info($bid, $user_id)
	{
		$bank_mod = &m('bank');
		
		if(!$bank_mod->_check_bank_of_user($bid, $user_id)) {
			$this->_errorCode[] = "50018";
			return false;
		}
		return true;
	}
}

?>