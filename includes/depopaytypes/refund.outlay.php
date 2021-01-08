<?php

/**
 *	  资金流向：流出
 *    收入类型：卖家同意退款
 *
 *    @author   Mimall
 *    @usage    none
 */
class RefundOutlay extends OutlayDepopay
{	
	// 针对财务明细的资金用途，值有：在线支付：PAY；充值：RECHARGE；提现：WITHDRAW; 服务费：SERVICE；转账：TRANSFER
    var $_tradeType = 'TRANSFER';
	
	function submit($data)
	{
		/* 释放trade_info和extra_info和post三个变量 */
        extract($data);
        /* 处理交易基本信息 */
        $base_info = $this->_handle_trade_info($trade_info, $post, FALSE);
		
        if (!$base_info)
        {
            /* 基本信息验证不通过 */
            return FALSE;
        }
		
		//$tradeNo = $extra_info['tradeNo'];
		
		/* 修改退款状态，并增加退款日志 */
		if(!$this->_handle_refund_status($trade_info, $extra_info, $post)) {
			$this->_errorCode[] = "50007";
			return FALSE;
		}
		
		/* 退款成功后，处理订单状态 */
		if(!$this->_handle_order_status($trade_info, $extra_info)) {
			return FALSE;
		}
		
		/* 处理交易状态 */
		if(!$this->_handle_trade_status($trade_info, $extra_info)) {
			return FALSE;
		}
		
		/* 插入收支记录，并变更账户余额 */
		if(!$this->_insert_record_info($trade_info, $extra_info, $post)) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	/* 修改退款状态，并增加退款日志 */
	function _handle_refund_status($trade_info, $extra_info, $post)
	{
		$refund_id = $extra_info['refund_id'];
		$refund_mod = &m('refund');
		$refund_message_mod = &m('refund_message');
		
		$refund_mod->edit($refund_id, array('status' => 'SUCCESS', 'end_time' => gmtime()));
		
		// 判断是平台客服处理退款，还是卖家同意退款
		if(isset($extra_info['operator']) && ($extra_info['operator'] == 'admin')) 
		{
			$refund_goods_fee    = $post['refund_goods_fee'] ? round($post['refund_goods_fee'],2) : 0;
			$refund_shipping_fee = $post['refund_shipping_fee'] ? round($post['refund_shipping_fee'],2) : 0;
			$refund_total_fee    = $refund_goods_fee + $refund_shipping_fee;
			
			$content = sprintf(Lang::get('admin_agree_refund_content_change'), Lang::get('system_customer'), $refund_goods_fee, $refund_shipping_fee, $post['content']);

			$refund_mod->edit($refund_id, array(
				'refund_total_fee' 		=> $refund_total_fee ,
				'refund_goods_fee' 		=> $refund_goods_fee, 
				'refund_shipping_fee' 	=> $refund_shipping_fee, 
				'ask_customer'			=> 1
			));
			
			$userpriv_mod = &m('userpriv');
			$admin = $userpriv_mod->get("privs='all' AND store_id=0");
			$owner_id = $admin['user_id'];
			
		} else {
			$content = sprintf(Lang::get('seller_agree_refund_content_change'), $extra_info['seller_name']);
			$owner_id = $extra_info['seller_id'];
		}
		
		/* 增加退款日志 */
		$data = array(
			'owner_id'	=> $owner_id,
			'owner_role'=> $extra_info['operator'],
			'refund_id'	=> $refund_id,
			'content'	=> $content,
			'created'	=> gmtime()				
		);
		return $refund_message_mod->add($data);
	}
	
	/* 退款成功过后，修改订单状态，并插入订单变更日志 */
	function _handle_order_status($trade_info, $extra_info)
	{
		$order_mod 		= &m('order');
		$order_log_mod 	= &m('orderlog');
		
		// 修改订单状态为，全额退款：交易关闭， 部分退款：交易成功
		$status = $extra_info['chajia'] > 0 ? ORDER_FINISHED : ORDER_CANCELED;
		if(!$order_mod->edit($extra_info['order_id'], array('status' => $status, 'finished_time' => gmtime()))){
			$this->_errorCode[] = "50013";
			return FALSE;
		}
		
		// 判断是管理员处理退款，还是卖家同意退款
		if(isset($extra_info['operator']) && ($extra_info['operator'] == 'admin')) {
			$remark = Lang::get('admin_agree_refund_order_status_change');
		} else $remark = Lang::get('seller_agree_refund_order_status_change');

		/* 记录订单操作日志 */
		$data = array(
			'order_id'  		=> $extra_info['order_id'],
			'operator'  		=> 0,
			'order_status' 		=> order_status($extra_info['status']),
			'changed_status' 	=> order_status($status),
			'remark'    		=> $remark,
			'log_time'  		=> gmtime(),
        );

		if(!$order_log_mod->add($data)) {
			$this->_errorCode[] = "50014";
			return FALSE;
		}
		return TRUE;
	}
	
	function _handle_trade_status($trade_info, $extra_info)
	{
		// 修改交易记录状态为，全额退款：交易关闭， 部分退款：交易成功
		$status = $extra_info['chajia'] > 0 ? 'SUCCESS' : 'CLOSED';
		
		return parent::_update_trade_status($extra_info['tradeNo'], array('status' => $status, 'end_time' => gmtime()));		
	}
	
	/* 插入收支记录，并变更账户余额 */
	function _insert_record_info($trade_info, $extra_info, $post)
	{
		// 退款给买家
		$time = gmtime();
		$data_record = array(
			'tradeNo'		=>	$extra_info['tradeNo'],
			'user_id'		=>	$trade_info['party_id'], // 买家
			'amount'		=>  $trade_info['amount'],
			'balance'		=>	$this->_get_deposit_balance($trade_info['party_id']) + $trade_info['amount'], // 增加后的余额
			'tradeType' 	=>  $this->_tradeType,
			'tradeTypeName'	=>  Lang::get('trade_refund_return'),
			'flow'			=>	'income',
		);
		$step1 = parent::_insert_deposit_record($data_record);
		
		if($step1)
		{
			/* 如果不是全额（单个退款商品，含分摊运费）退款，则需要把退款差价打给卖家 */
			if($extra_info['chajia'] > 0)
			{
				$time = gmtime();
				$data_record = array(
					'tradeNo'		=>	$extra_info['tradeNo'],
					'user_id'		=>	$trade_info['user_id'], // 卖家
					'amount'		=>  $extra_info['chajia'],
					'balance'		=>	$this->_get_deposit_balance($trade_info['user_id']) + $extra_info['chajia'], // 增加后的余额
					'tradeType' 	=>  $this->_tradeType,
					'tradeTypeName'	=>  Lang::get('trade_refund_pay'),
					'flow'			=>	'income',	
				);
				$step2 = parent::_insert_deposit_record($data_record);
			}
		}
		else
		{
			$this->_errorCode[] = "50006";
			return FALSE;
		}
		
		return TRUE;
	}
}

?>
