<?php

/* 订单商品 ordergoods */
class OrdergoodsModel extends BaseModel
{
    var $table  = 'order_goods';
    var $prikey = 'rec_id';
    var $_name  = 'ordergoods';
    var $_relation = array(
        // 一个订单商品只能属于一个订单
        'belongs_to_order' => array(
            'model'         => 'order',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'order_id',
            'reverse'       => 'has_ordergoods',
        ),
    );
	
	// 找出单个订单的调价幅度，如果原订单商品总价为0，调价后不为0，则调价幅度返回 -1
	function get_order_adjust_rate($order_info)
	{
		return Psmb_init()->get_order_adjust_rate($order_info);
	}
	
	//可以获取评价，订单，和用户详细
	function get_order_relative_info($goods_id,$condition,$count=false,$limit='')
	{
		$order_mod=&m('order');
		$member_mod=&m('member');
		if($limit)
		{
			$lm=" LIMIT ".$limit;
			
		}
		$comments=$this->getAll("SELECT buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation,goods_evaluation,reply_content,reply_time,portrait FROM {$this->table} AS og LEFT JOIN {$order_mod->table} AS ord ON og.order_id=ord.order_id LEFT JOIN {$member_mod->table} AS m ON ord.buyer_id=m.user_id WHERE goods_id = '$goods_id' AND evaluation_status = '1'".$condition." ORDER BY evaluation_time desc ".$lm);
		if($count)
		{
			return count($comments);
		}
		else
		{
			return $comments;
		}
	}
}

?>