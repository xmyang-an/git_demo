<?php

/* 积分数据模型 */
class Integral_logModel extends BaseModel
{
    var $table  = 'integral_log';
    var $prikey = 'log_id';
    var $alias  = 'ilog';
    var $_name  = 'integral_log';
	
	function add_log($data)
	{
		extract($data);
		$log = array(
			'user_id'  => $user_id,
			'order_id' => $order_id,
			'order_sn' => $order_sn,
			'changes'  => $flow == 'minus' ? -$amount: $amount,
			'balance'  => $balance,
			'type'     => $type,
			'state'   => $state?$state:'finished',
			'flag'    => $flag,
			'add_time' => gmtime()
		);
		$this->add($log);
	}
}

?>