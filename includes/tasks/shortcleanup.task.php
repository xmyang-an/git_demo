<?php

class ShortcleanupTask extends BaseTask
{
    function run()
    {
        $this->groupCancelOrder();
    }
	
	function groupCancelOrder()
	{
		$team_mod = &m('team');
		$teams = $team_mod->find('status=0 AND refund IS NULL');
		
		if(empty($teams)){
			return false;
		}
		
		$team_ids = array_keys($teams);
		
		$orders = db()->getAll('select o.*,team_id from '.DB_PREFIX.'order o where extension ="groupbuy" AND o.status in (11,20) AND team_id '.db_create_in($team_ids).' limit 20');

		if(!empty($orders)){
			$order_mod = &m('order');
			foreach($orders as $key=>$order)
			{
				if($order['status'] == 11){
					$order_mod->edit($order['order_id'], 'status=0');
				}
				elseif($order['status'] == 20){
					$this->_handleAfterCancel($order);
				}
				
				$team_mod->edit($order['team_id'], 'refund=1');//已经操作了自动退款
			}
		}
	}
	
	function _handleAfterCancel($order)
	{
		if(!$order['order_id']){
			return false;
		}
		
		$deposit_trade_mod 	= &m('deposit_trade');
		$deposit_record_mod = &m('deposit_record');
		$deposit_account_mod = &m('deposit_account');
		
		$deposit_trade_mod->edit('bizOrderId='.$order['order_sn'].' AND bizIdentity="'.TRADE_ORDER.'"', array('status' => 'CLOSED', 'end_time' => gmtime()));

		$user_id = $order['buyer_id'];
		$account = $deposit_account_mod->get('user_id='.$user_id);
		$balance = $account['money'] + $order['order_amount'];
		
		$time = gmtime();
		$tradeNo	= $deposit_trade_mod->genTradeNo();
		$data_trade = array(
		    'merchantId'	=> MERCHANTID,
			'tradeNo'	=>	$tradeNo,
			'bizIdentity'	=> TRADE_TRANS,
			'buyer_id'		=>	$user_id, // 买家
			'amount'		=>  $order['order_amount'],
			'status'		=> 'SUCCESS',
			'payment_code'  => 'deposit',
			'tradeCat'		=> 'TANSFER',
			'payType'		=> 'TANSFER',
			'title'	=>  sprintf('拼团未成功，退回已支付的货款。订单号:%s。',$order['order_sn']),
			'flow'			=>	'income',
			'add_time'		=> $time,
			'pay_time'		=> $time,
			'end_time'		=> $time,
		);

		if($deposit_trade_mod->add($data_trade))
		{ 
			$deposit_account_mod->edit('user_id='.$user_id, array('money' => $balance));
			$data_record = array(
				'tradeNo'		=>	$tradeNo,
				'user_id'		=>	$user_id,
				'amount'		=>  $order['order_amount'],
				'balance'		=>	$balance,
				'tradeType' 	=>  'TANSFER',
				'tradeTypeName' => 	sprintf('拼团未成功，退回已支付的货款。订单号:%s。',$order['order_sn']),
				'flow'	    	=>  'income',
				'remark'		=>  ''
			);
			
			$deposit_record_mod->add($data_record);
		}
	
		$order_mod 		= &m('order');
		$order_log_mod 	= &m('orderlog');
		
		$status = ORDER_CANCELED;
		$order_mod->edit($order['order_id'], array('status' => $status, 'finished_time' => gmtime()));
	}
}

?>
