<?php

/* 会员 member */
class MemberModel extends BaseModel
{
    var $table  = 'member';
    var $prikey = 'user_id';
    var $_name  = 'member';

    /* 与其它模型之间的关系 */
    var $_relation = array(
        // 一个会员拥有一个积分值，id相同   add by psmb
		'has_integral'=>array(
		   'model'        => 'integral',
		   'type'         => HAS_ONE,
		   'foreign_key'  => 'user_id',
		   'dependent'    => true
		),  // end  by psmb
		
		'has_team' => array(
            'model'       => 'group_team',       //模型的名称
            'type'        => HAS_MANY,       //关系类型
            'foreign_key' => 'user_id',    //外键名
            'dependent'   => true           //依赖
        ),
		
		// 一个会员拥有一个店铺，id相同
        'has_store' => array(
            'model'       => 'store',       //模型的名称
            'type'        => HAS_ONE,       //关系类型
            'foreign_key' => 'store_id',    //外键名
            'dependent'   => true           //依赖
        ),
		'has_msg' => array(
            'model'       => 'msg',       //模型的名称
            'type'        => HAS_ONE,       //关系类型
            'foreign_key' => 'user_id',    //外键名
            'dependent'   => true           //依赖
        ),
        'manage_mall'   =>  array(
            'model'       => 'userpriv',
            'type'        => HAS_ONE,
            'foreign_key' => 'user_id',
            'ext_limit'   => array('store_id' => 0),
            'dependent'   => true
        ),
        // 一个会员拥有多个收货地址
        'has_address' => array(
            'model'       => 'address',
            'type'        => HAS_MANY,
            'foreign_key' => 'user_id',
            'dependent'   => true
        ),
        'has_msglog' => array(
            'model'       => 'msglog',
            'type'        => HAS_MANY,
            'foreign_key' => 'user_id',
            'dependent'   => true
        ),
        // 一个用户有多个订单
        'has_order' => array(
            'model'         => 'order',
            'type'          => HAS_MANY,
            'foreign_key'   => 'buyer_id',
            'dependent' => true
        ),
         // 一个用户有多条收到的短信
        'has_received_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'to_id',
            'dependent' => true
        ),
        // 一个用户有多条发送出去的短信
        'has_sent_message' => array(
            'model'         => 'message',
            'type'          => HAS_MANY,
            'foreign_key'   => 'from_id',
            'dependent' => true
        ),
        // 会员和商品是多对多的关系（会员收藏商品）
        'collect_goods' => array(
            'model'        => 'goods',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'collect',    //中间表名称
            'foreign_key'  => 'user_id',
            'ext_limit'    => array('type' => 'goods'),
            'reverse'      => 'be_collect', //反向关系名称
        ),
        // 会员和店铺是多对多的关系（会员收藏店铺）
        'collect_store' => array(
            'model'        => 'store',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'collect',
            'foreign_key'  => 'user_id',
            'ext_limit'    => array('type' => 'store'),
            'reverse'      => 'be_collect',
        ),
        // 会员和店铺是多对多的关系（会员拥有店铺权限）
        'manage_store' => array(
            'model'        => 'store',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'user_priv',
            'foreign_key'  => 'user_id',
            'reverse'      => 'be_manage',
        ),
        // 会员和好友是多对多的关系（会员拥有多个好友）
        'has_friend' => array(
            'model'        => 'member',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'owner_id',
            'reverse'      => 'be_friend',
        ),
        // 好友是多对多的关系（会员拥有多个好友）
        'be_friend' => array(
            'model'        => 'member',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'friend',
            'foreign_key'  => 'friend_id',
            'reverse'      => 'has_friend',
        ),
        //用户与商品咨询是一对多的关系，一个会员拥有多个商品咨询
        'user_question' => array(
            'model' => 'goodsqa',
            'type' => HAS_MANY,
            'foreign_key' => 'user_id',
        ),
        //会员和优惠券编号是多对多的关系
        'bind_couponsn' => array(
            'model'        => 'couponsn',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'user_coupon',
            'foreign_key'  => 'user_id',
            'reverse'      => 'bind_user',
        ),
    );

    var $_autov = array(
        'user_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'password' => array(
            'required' => true,
            'filter'   => 'trim',
            'min'      => 6,
        ),
    );
	
	function getPrevRefer($user_id,$max_layer=3,$layer=1)
	{
		$user_ids = array();
		$user = parent::get($user_id);
		if(!empty($user['referid'])){
			$user_ids[$user['referid']] = $user['referid'];
			if($layer < $max_layer)
			{
				$layer++;
				$ids = $this->getPrevRefer($user['referid'],$max_layer,$layer);
				$user_ids = array_merge($user_ids, $ids);
			}
		}
		
		return $user_ids;
	}
	
	function getUserRefer($user_id,$max_layer=3,$layer=1)
	{
		if(!is_array($user_id)) $user_id = array($user_id);

		$user_ids = array();
		$users = $this->find(db_create_in($user_id,'referid'));
		if(!empty($users))
		{
			$user_ids  = array_keys($users);
			if($layer < $max_layer)
			{
				$layer++;
				$ids = $this->getUserRefer($user_ids,$max_layer ,$layer);
				$user_ids = array_merge($user_ids, $ids);
			}
		}
		
		return $user_ids;
	}

    /*
     * 判断名称是否唯一
     */
    function unique($user_name, $user_id = 0)
    {
        $conditions = "user_name = '" . $user_name . "'";
        $user_id && $conditions .= " AND user_id <> '" . $user_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }
	
	function insertLoginLog($info)
	{
		list($user_name,$user_id) = $info;
		
		$ip = real_ip();
		
		$location = '';
/*		$region_mod = &m('region');
		$result = $region_mod->get_address_from_ip($ip);
		if($result)
		{
			$result = ecm_json_decode($result,true);
			if($result['status'] == 0)
			{
				$str_array = array_unique(array($result['data']['country'],$result['data']['region'],$result['data']['city']));//把中国北京北京处理成中国北京
				$location = implode('',$str_array);
			}
		}*/
		
		$loginlog = &m('loginlog');
		$log_id = $loginlog->add(array(
			'user_id' => $user_id,
			'user_name' => $user_name,
			'ip'  => $ip,
			'region_name' => str_replace('XX','',$location),//把未知的数据XX去掉
			'add_time' => gmtime()
		));
		
		return $log_id;
	}

    function drop($conditions, $fields = 'portrait')
    {
        if ($droped_rows = parent::drop($conditions, $fields))
        {
            restore_error_handler();
            $droped_data = $this->getDroppedData();
            foreach ($droped_data as $row)
            {
                $row['portrait'] && @unlink(ROOT_PATH . '/' . $row['portrait']);
            }
            reset_error_handler();
        }
        return $droped_rows;
    }
}

?>