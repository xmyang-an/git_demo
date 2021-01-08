<?php

/* 拼团活动 groupbuy */
class GroupbuyModel extends BaseModel
{
    var $table  = 'groupbuy';
    var $alias  = 'gb';
    var $prikey = 'group_id';
    var $_name  = 'groupbuy';
    var $_relation  = array(
        // 一个拼团活动属于一个商品
        'belong_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_groupbuy',
        ),
		'has_goodsstatistics' => array(
            'model'         => 'goodsstatistics',
            'type'          => HAS_ONE,
            'foreign_key'   => 'goods_id',
            'dependent'     => true
        ),
        // 一个拼团活动属于一个店铺
        'belong_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_groupbuy',
        ),
		
		'has_team' => array(
            'model'       => 'group_team',       //模型的名称
            'type'        => HAS_MANY,       //关系类型
            'foreign_key' => 'group_id',    //外键名
            'dependent'   => true           //依赖
        )
    );
    var $_autov = array(
        'group_name' => array(
            'required'  => true,
            'filter'    => 'trim',
            'max'       => 255,
        ),
        'group_desc' => array(
            'filter'    => 'trim',
        ),
        'min_quantity' => array(
            'required'  => true,
            'type'      => 'int',
            'filter'    => 'intval',
            'max'       => 65535,
        ),
        'max_per_user' => array(
            'type'      => 'int',
            'filter'    => 'intval',
            'max'       => 65535,
        ),
    );
	
	function __construct($db, $params = array())
	{
		parent::__construct($db, $params);
		$this->checkGroupExpire();
	}
	
	function checkGroupExpire()
	{
		$groupbuys = parent::find('end_time <'.gmtime().' AND state ='.GROUP_ON);
		if(!empty($groupbuys))
		{
			parent::edit(array_keys($groupbuys), 'state='.GROUP_END);
		}
	}
}
?>
