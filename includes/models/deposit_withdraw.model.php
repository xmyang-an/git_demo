<?php

class Deposit_withdrawModel extends BaseModel
{
    var $table  = 'deposit_withdraw';
    var $prikey = 'withdraw_id';
    var $_name  = 'deposit_withdraw';
	
	var $_relation  = array(
        // 一个提现记录对应一笔交易
		'has_trade' => array(
            'model'         => 'deposit_trade',
            'type'          => HAS_ONE,
            'foreign_key'   => 'bizOrderId',
			'refer_key'		=> 'orderId',
            'dependent'     => false
        ),
	);
}

?>