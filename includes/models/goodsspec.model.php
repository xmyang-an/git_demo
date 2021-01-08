<?php

/* 商品规格 goodsspec */
class GoodsspecModel extends BaseModel
{
    var $table  = 'goods_spec';
    var $prikey = 'spec_id';
    var $alias  = 'gs';
    var $_name  = 'goodsspec';

    var $_relation  = array(
        // 一个商品规格只能属于一个商品
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_goodsspec',
        ),
        'has_cart_items' => array(
            'model'         => 'cart',
            'type'          => HAS_MANY,
            'foreign_key'   => 'spec_id',
        ),
    );
	
	function _get_spec_min_max($goods_id)
	{	
		$goodsspec = parent::find(array(
			'conditions'=>'goods_id='.$goods_id,
			'fields'=>'price,stock',
			'order'=>'spec_id asc'
		));
		if(!$goodsspec) return array();
		
		$goodsspec_first = current($goodsspec);
		
		$price_min = $price_max = $goodsspec_first['price'];
		
		if(count($goodsspec) > 1) 
		{
			foreach($goodsspec as $k=>$v)
			{
				if($price_min > $v['price']) {
					$price_min = $v['price'];
				}
				if($price_max < $v['price']) {
					$price_max = $v['price'];
				}
			}
		}
		return array('min'=>$price_min, 'max'=>$price_max);
	}
}

?>