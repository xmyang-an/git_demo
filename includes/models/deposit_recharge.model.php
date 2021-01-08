<?php

class Deposit_rechargeModel extends BaseModel
{
    var $table  = 'deposit_recharge';
    var $prikey = 'recharge_id';
    var $_name  = 'deposit_recharge';
	
	var $_relation  = array(
        // 一个充值记录对应一笔交易
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