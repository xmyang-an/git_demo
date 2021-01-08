<?php

/**
 *	  资金流向：收入
 *    支出类型：线上充值
 *
 *    @author   Mimall
 *    @usage    none
 */
class RechargeIncome extends IncomeDepopay
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
		if(!$this->_insert_recharge_info($trade_info, $extra_info, $post)) {
			
			$this->_errorCode[] = "50005";
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
				'fundchannel'   => $payment_bank ? Lang::get($post['payment_bank']) : Lang::get($post['payment_code']),
				'title'			=> Lang::get('recharge'),
				'buyer_remark'	=> $post['remark'],
				'add_time'		=> gmtime()
			);
			
			if($deposit_trade_mod->add($data_trade))
			{
				$data_recharge = array(
					'orderId'		=>	$bizOrderId,
					'user_id'		=>	$trade_info['user_id'],
					'is_online'		=>	1,
				);
		
				$result = $deposit_recharge_mod->add($data_recharge);
			}
		}
		
		return $result;
	}
	
	/* 线上充值（支付充值，资金退回）响应通知 */
	function respond_notify($orderInfo = array(), $notify_result, $outTradeNo = '')
	{
		$deposit_account_mod 	= &m('deposit_account');
		$deposit_record_mod 	= &m('deposit_record');
		$deposit_trade_mod		= &m('deposit_trade');
		$tradeList				= FALSE;
		$time   				= gmtime();
			
				
		/* 如果是支付订单的预充值通知（有正常的交易记录，需要创建新的充值记录） 
		 * 数据来源有两种，一种是：从正常的交易记录中读取，二种是：从交易日志中读取（支付变更的情况）
		*/
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER, TRADE_BUYAPP)))
		{
			$tradeNo = $orderInfo['payTradeNo'];
				
			$trade_info = array('user_id' => $orderInfo['buyer_id'], 'party_id' => 0, 'amount' => $orderInfo['amount']);
			$extra_info = array('tradeNo' => $tradeNo);
			$post		= array('payment_code' => $orderInfo['payment_code'], 'payment_bank' => $orderInfo['payment_bank']);
				
			if($this->_insert_recharge_info($trade_info, $extra_info, $post)) {
				$orderInfo['tradeList'] = $deposit_trade_mod->find("tradeNo='{$tradeNo}'");
			}
		}
		/* 如果是单纯充值的订单通知（有正常的交易记录，无需创建新的充值记录，只需改变交易状态）*/
		elseif(in_array($orderInfo['bizIdentity'], array(TRADE_RECHARGE)))
		{
			!isset($orderInfo['tradeList']) && $orderInfo['tradeList'] = array();
		}
		
		$result = TRUE;
		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$tradeNo = $tradeInfo['tradeNo'];
			
			// 修改交易状态
			$deposit_trade_mod->edit("tradeNo='{$tradeNo}'", 
				array('status' => 'SUCCESS', 'pay_time' => $time, 'end_time' => $time, 'outTradeNo' => $outTradeNo));
				
			/* 增加后的余额（仅获取余额，不更新账户余额表） */
			//$balance = $this->_get_deposit_balance($tradeInfo['buyer_id'], $tradeInfo['amount']);
			
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
		}
		
		// 如果是从交易日志中取交易数据，删掉交易日志文件
		if(isset($orderInfo['tradelogfile'])) @unlink($orderInfo['tradelogfile']);
		
		return $result;
	}
}

?>
