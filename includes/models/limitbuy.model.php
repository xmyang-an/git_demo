<?php

/* 促销活动（打折减价） */
class LimitbuyModel extends BaseModel
{
    var $table  = 'limitbuy';
    var $alias  = 'pro';
    var $prikey = 'pro_id';
    var $_name  = 'limitbuy';
	var $_relation  = array(
        // 一个促销活动属于一个商品
        'belong_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_limitbuy',
        ),
	);
	
	// 获取促销价格， 没有促销价格，或者促销价格不合理，则返回FALSE
	function get_limitbuy_price($goods_id, $spec_id = 0)
	{
		$proPrice = FALSE;
		
		if(!$goods_id || !$spec_id) return array($proPrice, 0);
		
		$limitbuy = parent::get(array('conditions'=>"start_time <= ".gmtime(). " AND end_time >= ".gmtime()." AND goods_id=".$goods_id, 'fields'=>'spec_price', 'order' => 'pro_id DESC'));

		if($limitbuy)
		{
			// 读取该商品原始价格
			$spec_mod 	= &m('goodsspec');
			$spec 		= $spec_mod->get(array('conditions'=>'goods_id='.$goods_id.' AND spec_id='.$spec_id,'fields'=>'price'));
			$price 		= $spec['price'];
				
			$spec_price = unserialize($limitbuy['spec_price']);
			
			if($spec_price[$spec_id]['pro_type'] == 'price') 
			{
				$proPrice = round($price - $spec_price[$spec_id]['price'], 2);
				if($proPrice < 0) {
					$proPrice = 0;
				}
				
			}
			else $proPrice = round($price * $spec_price[$spec_id]['price'] / 1000, 4) * 100;
		}
		
		return array($proPrice, $limitbuy['pro_id']);
	}
	
	/* 判断促销状态 */
	function get_limitbuy_status($data, $checkPrice = false)
	{
		$status = '';
		$check_prcie = $check_time = true;
		
		if(is_array($data)) $limitbuy = $data;
		else $limitbuy = parent::get($data);// data = pro_id
		
		// 此为预留接口，仅用户中心用，如果为TRUE的话，促销挂件中按数量搜索商品则不太好处理，会导致促销挂件中的商品有可能不是促销商品，因为促销挂件中的商品无法判断价格是否合理
		if($checkPrice === true)
		{
			$goods_mod = &m('goods');
			$goods = $goods_mod->findAll(array(
				'conditions'=> 'goods_id='.$limitbuy['goods_id'],
				'include' => array('has_goodsspec' => array('order'=>'spec_id')),
				'fields' => "this.goods_name,this.goods_id,this.spec_name_1,this.spec_name_2",
			));
			$goods = current($goods);
		
			$limitbuy['spec_price'] = unserialize($limitbuy['spec_price']);
		
			// 先判断价格是否合理（以为设置促销后，卖家有可能再次去修改了价格，导致价格为负数的情况，这个时候就要设置促销商品状态为失效）
			foreach($limitbuy['spec_price'] as $key=>$val)
			{
				// 如果优惠类型是减价，那么必须保证优惠幅度不能大于商品原价格就行
				if($val['pro_type'] == 'price') {
					if($val['price'] > $goods['gs'][$key]['price']) {
						$check_prcie = false;
						break;
					}
				}
			}
		}
		
		if($check_prcie)
		{
			if(($limitbuy['start_time'] < gmtime()) && ($limitbuy['end_time'] > gmtime()))
			{
				$status = 'going';
			}
			if($limitbuy['start_time'] > gmtime())
			{
				$status = 'waiting';
			}
			if($limitbuy['end_time'] < gmtime())
			{
				$status = 'ended';
			}
		}
		else
		{
			$status = 'price_invalid';
		}
		return $status;
	}
}
?>
