<?php

/* 退款 */
class RefundModel extends BaseModel
{
    var $table  = 'refund';
    var $prikey = 'refund_id';
    var $_name  = 'refund';
	
	var $_relation  = array(
        // 一个退款只能属于一个订单
        'belongs_to_order' => array(
            'model'         => 'order',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_refund',
        ),
	);
	
	function gen_refund_sn()
	{
		/* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('Y', $timestamp);
        $z = date('z', $timestamp);
        $refund_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $refund = parent::get(array('conditions'=>'refund_sn=' . $refund_sn,'fields'=>'refund_id'));
        if (!$refund)
        {
            /* 否则就使用这个退款编号 */
            return $refund_sn;
        }

        /* 如果有重复的，则重新生成 */
        return $this->_gen_refund_sn();
	}
}

?>