<?php

class deposit_recordModel extends BaseModel
{
    var $table  = 'deposit_record';
    var $prikey = 'record_id';
    var $_name  = 'deposit_record';
	
	var $_relation  = array(
        // 一个收支记录对应一笔交易
		'has_trade' => array(
            'model'         => 'deposit_trade',
            'type'          => HAS_ONE,
            'foreign_key'   => 'tradeNo',
			'refer_key'		=> 'tradeNo',
            'dependent'     => false
        ),
	);
}

?>