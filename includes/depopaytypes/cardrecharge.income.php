<?php

/**
 *	  资金流向：收入
 *    支出类型：充值卡充值
 *
 *    @author   Mimall
 *    @usage    none
 */
class CardrechargeIncome extends IncomeDepopay
{
	// 针对交易记录的交易类型，值有：购物：SHOPPING； 理财：FINANCE；缴费：PUC_CHARGE； 还款：CCR；转账：TRANSFER ...
	var $_tradeCat	= 'RECHARGE'; 
	
	// 针对财务明细的资金用途，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType = 'RECHARGE';
	
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
		
		/* 插入充值记录 */
		if(!$tradeInfo = $this->_insert_recharge_info($trade_info, $extra_info, $post)) {
			
			$this->_errorCode[] = "50005";
			return FALSE;
		}
		/* 插入收支记录 */
		if(!$this->_insert_record_info($tradeInfo, $post)) {
			$this->_errorCode[] = "50020";
			return FALSE;
		}
					
		return TRUE;
	}
	
	/* 插入交易记录，充值记录 */
	function _insert_recharge_info($trade_info, $extra_info, $post)
	{
		$result = TRUE;
		
		$deposit_trade_mod    = &m('deposit_trade');
		$deposit_recharge_mod = &m('deposit_recharge');
		$bizOrderId			  = $deposit_trade_mod->genTradeNo(12);
		
		// 如果添加有记录，则不用再添加了
		if(!$deposit_trade_mod->get("tradeNo='{$extra_info['tradeNo']}'"))
		{
			// 增加交易记录
			$data_trade = array(
				'tradeNo'		=> $extra_info['tradeNo'],
				'payTradeNo'	=> $deposit_trade_mod->genPayTradeNo(),
				'bizOrderId'	=> $bizOrderId,
				'bizIdentity'	=> TRADE_RECHARGE,
				'buyer_id'		=> $trade_info['user_id'],
				'seller_id'		=> $trade_info['party_id'],
				'amount'		=> $trade_info['amount'],
				'status'		=> 'PENDING',
				'payment_code'	=> $post['payment_code'],
				'payment_bank'	=> $post['payment_bank'],
				'tradeCat'		=> $this->_tradeCat,
				'payType'		=> $this->_payType,
				'flow'     		=> $this->_flow_name,
				'fundchannel'   => Lang::get('rechargecard'),
				'title'			=> Lang::get('recharge') . ' - ' . Lang::get('rechargecard'),
				'buyer_remark'	=> $post['remark'],
				'add_time'		=> gmtime()
			);
			
			if($deposit_trade_mod->add($data_trade))
			{
				$data_recharge = array(
					'orderId'		=>	$bizOrderId,
					'user_id'		=>	$trade_info['user_id'],
					'is_online'		=>	0,
				);
		
				if($deposit_recharge_mod->add($data_recharge)) {
					$result = $deposit_trade_mod->get("tradeNo='{$extra_info['tradeNo']}'");
				}
			}
		}
		
		return $result;
	}
	
	/* 充值卡充值 */
	function _insert_record_info($tradeInfo = array(), $post = array())
	{
		$deposit_account_mod= &m('deposit_account');
		$deposit_record_mod = &m('deposit_record');
		$deposit_trade_mod	= &m('deposit_trade');
		$time 				= gmtime();
		
		$tradeNo 			= $tradeInfo['tradeNo'];
		
		// 修改交易状态
		$deposit_trade_mod->edit("tradeNo='{$tradeNo}'", array('status' => 'SUCCESS', 'pay_time' => $time, 'end_time' => $time));
			
		/* 增加后的余额（通过事务获取最新余额，并更新账户余额表， 这样能保证高并发时获取的余额也是安全的）*/
		$balance = $deposit_account_mod->_update_deposit_money_transaction($tradeInfo['buyer_id'], $tradeInfo['amount']);
				
		/* 插入充值者收入记录 */
		$data_record = array(
			'tradeNo'  		=> $tradeInfo['tradeNo'],
			'user_id'  		=> $tradeInfo['buyer_id'],
			'amount'   		=> $tradeInfo['amount'],
			'balance'  		=> $balance,
			'tradeType'		=> $this->_tradeType,
			'tradeTypeName' => Lang::get(strtoupper($this->_tradeType)),
			'flow'	   		=> $this->_flow_name,
		);
		
		$result = $deposit_record_mod->add($data_record);
		if($result) {
			if($post['card_id']) {
				$cashcard_mod = &m('cashcard');
				$result = $cashcard_mod->edit($post['card_id'], array('useId' => $tradeInfo['buyer_id'],'active_time' => gmtime()));
			}
		}
		
		return $result;
	}
}

?>
