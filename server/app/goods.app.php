<?php

class GoodsApp extends ApibaseApp
{
	function listing()
	{
		$post = parent::_getPostData();
		
		$page = $this->_get_page((isset($post['perpage']) && $post['perpage'] > 0) ? $post['perpage'] : 10);
		
		$sort = 'g.add_time';
		$order = ' desc';
		if(isset($post['order'])){
			$order_fields = explode('|',$post['order']);

			if(in_array($order_fields[0],array('add_time', 'sales', 'views', 'price'))){
				if(in_array($order_fields[0],array('add_time', 'price'))){
					$sort = 'g.'.$order_fields[0];
				}
				else{
					$sort = $order_fields[0];
				}
			}

			if(in_array($order_fields[1],array('asc','desc'))){
				$order = $order_fields[1];
			}
		}
		
		$conditions = "g.if_show = 1 AND g.closed = 0 AND s.state = 1";
		
		if($post['id']){//店铺id
			$conditions .= " AND g.store_id = ".$post['id'];
		}
		else{
			if($post['goods_id']){
				$ids = explode(',', $post['goods_id']);
				$conditions .= " AND g.goods_id ".db_create_in($ids);
			}
			else{
				$conditions .= " AND s.store_id ".db_create_in($this->GetLocation());
			}
		}
		
		if($post['recommended'] == 1){//店铺id
			$conditions .= " AND g.recommended = 1";
		}
		
		if($post['keyword']){//店铺id
			$conditions .= " AND g.goods_name LIKE '%".$post['keyword']."%'";
		}
		
        if (!isset($post['recom_id']))
        {
            /* 最新商品 */
            if(isset($post['cate_id']) && $post['cate_id'] > 0)
            {
                $gcategory_mod = &bm('gcategory',array('_store_id' => $post['id']?$post['id']:0));
                $scate_ids =  $gcategory_mod->get_descendant($post['cate_id']);
				
				$sql = "SELECT DISTINCT goods_id FROM ".DB_PREFIX ."category_goods WHERE cate_id " . db_create_in($scate_ids);
				
				$gs_mod    = & m('goodsspec');
           	    $goods_ids = $gs_mod->getCol($sql);
                $conditions .= " AND g.goods_id " . db_create_in($goods_ids);
            }
			
			$tables = " FROM " . DB_PREFIX . "goods AS g " .
                    "LEFT JOIN " . DB_PREFIX . "goods_spec AS gs ON g.default_spec = gs.spec_id " .
                    "LEFT JOIN " . DB_PREFIX . "store AS s ON g.store_id = s.store_id " .
					"LEFT JOIN " . DB_PREFIX . "goods_statistics AS goodsstatistics ON goodsstatistics.goods_id=g.goods_id " ;
			
            $fields = " g.goods_id, g.goods_name, g.default_image, gs.price, gs.stock,gs.spec_id,goodsstatistics.sales,s.store_id,s.store_name " ;
        }
        else
        {
			$tables = " FROM " . DB_PREFIX . "recommended_goods AS rg " .
                    "   LEFT JOIN " . DB_PREFIX . "goods AS g ON rg.goods_id = g.goods_id " .
                    "   LEFT JOIN " . DB_PREFIX . "goods_spec AS gs ON g.default_spec = gs.spec_id " .
                    "   LEFT JOIN " . DB_PREFIX . "store AS s ON g.store_id = s.store_id " .
					"	LEFT JOIN " . DB_PREFIX . "goods_statistics AS goodsstatistics ON goodsstatistics.goods_id=g.goods_id ";
            /* 推荐商品 */
            $fields = " g.goods_id, g.goods_name, g.default_image, gs.price, gs.stock,gs.spec_id,goodsstatistics.sales,s.store_id,s.store_name " .
            $conditions .= "AND rg.recom_id = '".$post['recom_id']."' " ." AND g.goods_id IS NOT NULL ";
        }
		
		$goodsList = db()->getAll("SELECT {$fields} {$tables} WHERE {$conditions} ORDER BY " .$sort." ".$order." LIMIT ".$page['limit']);

		$page['item_count'] = db()->getOne("SELECT COUNT(*) as c {$tables} WHERE {$conditions}");
		
		import('promotool.lib');
		$promotool = new Promotool();
		
        foreach ($goodsList as $key=>$goods)
        {
            empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');

			if(stripos($goods['default_image'], '//:') == FALSE) {
				$goods['default_image'] = SITE_URL . '/' . $goods['default_image'];
			}
			
			/* 读取促销价格 */
			$result = $promotool->getItemProInfo($goods['goods_id'], $goods['spec_id']);
			if($result !== FALSE) {
				$goods['old_price'] = $goods['price'];
				$goods['price'] = $result['pro_price'];
			}
			
            $goodsList[$key] = $goods;
        }

		$this->json_success(array_values($goodsList));
	}
	
	function comments()
	{
		$id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
		if(empty($id))
        {
            $this->json_fail('没有7关于该商品的评论信息');
            return;
        }
		
		$page = $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);
		
		if(isset($this->PostData['eval']) && in_array($this->PostData['eval'],array(1,2,3)))
		{
			$conditions .= " AND evaluation =".intval($this->PostData['eval']);
		}
		elseif($this->PostData['eval'] == 4)
		{
			$conditions .= " AND share_images != ''";
		}
		
		$ordergoods_mod =& m('ordergoods');
		$comments = $ordergoods_mod->find(array(
			'conditions'=> "goods_id = '$id' AND evaluation_status = '1' ".$conditions,
			'join'  	=> 'belongs_to_order',
			'fields'	=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation,share_images,reply_content',
			'order' 	=> 'evaluation_time desc',
			'limit' 	=> $page['limit'],
			'count'		=> true
		));
		
		$page['item_count'] = $ordergoods_mod->getCount();
		
		if($comments)
		{
			$member_mod = &m('member');
			foreach($comments as $key => $comment)
			{
				$share_images = unserialize($comment['share_images']);
				$comments[$key]['images'] = array();
				if(!empty($share_images))
				{
					foreach($share_images as $k=>$image)
					{
						if(stripos($image, '//:') == FALSE) {
							$comments[$key]['images'][$k]['url'] = SITE_URL . '/' .$image;
						}
					}
				}
					
				$member = $member_mod->get(array(
					'conditions'	=> 'user_id='.$comment['buyer_id'],
					'fields'		=> 'portrait',
				));
				empty($member['portrait']) && $member['portrait'] = Conf::get('default_user_portrait');
				if(stripos($member['portrait'], '//:') == FALSE) {
					$member['portrait'] = SITE_URL . '/' .$member['portrait'];
				}
				
				$comments[$key]['portrait'] = $member['portrait'];
				$comments[$key]['buyer_name'] = cut_str($comment['buyer_name']);
				
				$comments[$key]['evaluation_time'] = local_date('Y-m-d H:i',$comment['evaluation_time']);
			}
		}
		
		$statistics['total_count'] = $total_count = $this->_count_eval($id);
		$statistics['bad_count'] = $bad_count = $this->_count_eval($id,1);
		$statistics['middle_count'] = $middle_count = $this->_count_eval($id,2);
		$statistics['good_count'] = $goods_count = $this->_count_eval($id,3);
		$statistics['share_count'] = $this->_count_eval($id,4);
		
		$this->json_success(array('list' => array_values($comments), 'statistics' => $statistics));
	}
	
	function _checkValid()
	{
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('Product_is_not_exsist');
			exit;
        }
		
		$goods_mod = &m('goods');
        $goods = $goods_mod->get(array(
			'conditions' => 'goods_id='.$id.' AND g.if_show = 1 AND g.closed = 0 AND s.state = 1',
			'join'       => 'belongs_to_store',
			'fields'     => 'goods_id'
		));
		
		if (empty($goods))
        {
            $this->json_fail('Product_is_not_exsist');
			exit;
        }
	}
	
	function baseinfo()
	{
		$this->_checkValid();
		
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
		
		$cache_server =& cache_server();
        $key = 'page_of_goods_baseinfo' . $id;
        $goods = $cache_server->get($key);
        $cached = true;
        if($goods === false)
        {
            $cached = false;
            $data = array('id' => $id);
			
			$goods_mod = &m('goods');
            $goods = $goods_mod->get(array(
				'conditions' => 'g.goods_id='.$id.' AND g.if_show = 1 AND g.closed = 0 AND s.state = 1',
				'join'       => 'belongs_to_store,has_goodsstatistics',
				'fields'     => 'this.*,state,sales'
			));

			if ($goods['state'] == 2)
            {
                $this->json_fail('the_store_is_closed');
                exit;
            }
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->json_fail('goods_not_exist');
                return false;
            }
			
            /* 店铺信息 */
            if (!$goods['store_id'])
            {
                $this->json_fail('store of goods is empty');
                return false;
            }
			
			empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
			if(stripos($goods['default_image'], '//:') == FALSE) {
				$goods['default_image'] = SITE_URL . '/' .$goods['default_image'];
			}
			
			$goods['couponReceive'] = db()->getOne('select count(*) from '.DB_PREFIX.'coupon c where clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  end_time > '.gmtime().' AND store_id='.$goods['store_id']);
			
			//$meal_mod = &m('meal');
			//$goods['has_meal'] = $meal_mod->has_meal($id);
			
			$goods_pvs_mod = &m('goods_pvs');
			$goods_pvs = $goods_pvs_mod->get($id);// 取出该商品的属性字符串
			
			if(!empty($goods_pvs)){
				$goods['hasProps'] = 1;
			}
			
			$goods['integral']['radio'] = 0;
			$goods['integral']['enabled'] = 0;
			
			$integral_mod = &m('integral');
			if($integral_mod->_get_sys_setting('integral_enabled'))
			{
				$goods['integral']['enabled'] = 1;
				
				$store_mod  =& m('store');
				$store = $store_mod->get(array(
					'conditions' => $goods['store_id'],
					'fields'     => 'sgrade',
				));
				
				// 购买商品可获得多少积分
				$integralRadio = $integral_mod->_get_sys_setting(array('buying_integral', $store['sgrade']));
					
				if($integralRadio > 0 && $integralRadio <=1) {
					$goods['integral']['radio'] = $integralRadio;
				}
			}
			
			$goods['lastComments'] = $this->_getLastComments($goods['goods_id']);
				
			$statistics['total_count'] = $total_count = $this->_count_eval($goods['goods_id']);
			$statistics['bad_count'] = $bad_count = $this->_count_eval($goods['goods_id'],1);
			$statistics['middle_count'] = $middle_count = $this->_count_eval($goods['goods_id'],2);
			$statistics['good_count'] = $goods_count = $this->_count_eval($goods['goods_id'],3);
			$statistics['share_count'] = $this->_count_eval($goods['goods_id'],4);
				
			$goods['statistics'] = $statistics;
			
            $cache_server->set($key, $goods, 1800);
        }
		
		$this->json_success($goods);
	}
	
	function GetGoodsQrcode()
	{
		$this->_checkUserAccess();
		
		$file = $this->GetWxMPQRCode(array(
			'user_id' => $this->PostData['user_id'],
			'id'      => $this->PostData['id'],
			'page'    => 'pages/goods/index'
		));
		
		$this->json_success($file);
	}
	
	function _getLastComments($id = 0, $num = 3)
	{
		$ordergoods_mod =& m('ordergoods');
		$comments = $ordergoods_mod->find(array(
			'conditions'=> "goods_id = '$id' AND evaluation_status = '1' AND comment <> '' ",
			'join'  	=> 'belongs_to_order',
			'fields'	=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
			'order' 	=> 'evaluation_time desc',
			'limit' 	=> $num,
			'count'		=> true
		));
		if($comments)
		{
			$member_mod = &m('member');
			foreach($comments as $key => $comment)
			{
				$member = $member_mod->get(array(
					'conditions'	=> 'user_id='.$comment['buyer_id'],
					'fields'		=> 'portrait',
				));
				empty($member['portrait']) && $member['portrait'] = Conf::get('default_user_portrait');
				if(stripos($member['portrait'], '//:') == FALSE) {
					$member['portrait'] = SITE_URL . '/' .$member['portrait'];
				}
				
				$comments[$key]['portrait'] = $member['portrait'];
				$comments[$key]['buyer_name'] = cut_str($comment['buyer_name']);
				
				$comments[$key]['evaluation_time'] = local_date('Y-m-d H:i',$comment['evaluation_time']);
			}
		}
		return array('list' => $comments, 'total' => $ordergoods_mod->getCount());
	}
	
	function _count_eval($goods_id,$eval = '')
	{
		$order_goods_mod =& m('ordergoods');
		if(in_array($eval,array(1,2,3)) || !$eval)
		{
			if(in_array($eval,array(1,2,3)))
			{
				$condition=" AND evaluation =".$eval;
			}
			$count=count($order_goods_mod->find(array('conditions' => "goods_id = '$goods_id' AND evaluation_status = '1' ".$condition,'join'  => 'belongs_to_order')));
		}
		elseif($eval == 4)
		{
			$count = count($order_goods_mod->find(array('conditions' => "goods_id = '$goods_id' AND share_images != ''")));
		}
		
		return $count;
		
	}
	

	function productImages()
	{
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('Product_is_not_exsist');
            return;
        }
		
		$cache_server =& cache_server();
        $key = 'page_of_goods_images' . $id;
        $images = $cache_server->get($key);
        $cached = true;
        if($images === false)
        {
			$image_mod = &m('goodsimage');
			$images = $image_mod->find(array(
				'conditions' => 'goods_id='.$id
			));
			
			if(!empty($images)){
				foreach($images as $key=>$val){
					if(stripos($val['thumbnail'], '//:') == FALSE) {
						$images[$key]['thumbnail'] = SITE_URL . '/' . $val['thumbnail'];
					}
					
					if(stripos($val['image_url'], '//:') == FALSE) {
						$images[$key]['image_url'] = SITE_URL . '/' . $val['image_url'];
					}
				}
			}
			  
			$cache_server->set($key, $images, 1800);
		}
		
		$this->json_success(array_values($images));
	}
	
	function productSpecs()
	{
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('Product_is_not_exsist');
            return;
        }
		
		$cache_server =& cache_server();
        $key = 'page_of_goods_specs' . $id;
        $specs = $cache_server->get($key);
        $cached = true;
        if($specs === false)
        {
			$spec_mod = &m('goodsspec');
			$specs = $spec_mod->find(array(
				'conditions' => 'goods_id='.$id
			));
			
			foreach($specs as $key=>$val){
				if($val['spec_image'])
				{
					if(stripos($val['spec_image'], '//:') == FALSE) {
						$specs[$key]['spec_image'] = SITE_URL . '/' .$val['spec_image'];
					}	
				}
			}
			
			$cache_server->set($key, $specs, 1800);
		}
		
		$this->json_success(array_values($specs));
	}
	
	function defaultLogist()
	{
		$store_mod = &m('store');
		$region_mod= &m('region');
		$delivery_mod = &m('delivery_template');
		
		// 如果没有设置运费模板，则取该店铺默认的运费模板
		if(!$this->PostData['delivery_template_id'] || !$delivery_mod->get($this->PostData['delivery_template_id']))
		{
			$delivery = $delivery_mod->get(array(
				'conditions'=>'store_id='.$this->PostData['store_id'],
				'order'=>'template_id',
			));
		}
		else {
			$delivery = $delivery_mod->get($this->PostData['delivery_template_id']);
		}
		
		$city_id = $this->PostData['city_id'];

		$logist_fee = $delivery_mod->get_city_logist($delivery, $this->PostData['city_id']);

		$this->json_success(!empty($logist_fee) ? current($logist_fee) : array());
	}
	
	function collect()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$goods_id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
        $keyword = empty($this->PostData['keyword'])  ? '' : trim($this->PostData['keyword']);
		
		if (empty($user_id))
        {
            $this->json_fail('login_pls');
            return;
        }
	
		$model_goods =& m('goods');
        $goods_info  = $model_goods->get($goods_id);

        if (empty($goods_info))
        {
            $this->json_fail('no_such_goods');
            return;
        }
		
        $model_user =& m('member');
        $model_user->createRelation('collect_goods', $user_id, array(
            $goods_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));

        /* 更新被收藏次数 */
        $model_goods->update_collect_count($goods_id);
		
		$this->json_success('','collect_goods_ok');
	}
	
	//是否已经收藏
	function collectinfo(){
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$goods_id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
		
		if(empty($user_id) || empty($goods_id))
        {
            $this->json_fail('black_hacker');
            return;
        }
		
		$goods_mod = &m('goods');
		$collected = $goods_mod->get(array(
            'join'  => 'be_collect',
            'fields'=> 'goods_id',
            'conditions' => 'collect.user_id = ' . $user_id.' AND goods_id='.$goods_id,
        ));
		
		$this->json_success($collected ? 1 : 0);
	}
	
	function limitbuy()
	{
		$goods_mod = &m('goods');
			
		$page = $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);
		
		$sort = 'pro_id';
		$order = ' desc';
		if(isset($this->PostData['order'])){
			$order_fields = explode('|',$this->PostData['order']);

			if(in_array($order_fields[0],array('add_time', 'sales', 'views', 'price'))){
				if(in_array($order_fields[0],array('add_time', 'price'))){
					$sort = 'g.'.$order_fields[0];
				}
				else{
					$sort = $order_fields[0];
				}
			}

			if(in_array($order_fields[1],array('asc','desc'))){
				$order = $order_fields[1];
			}
		}
		
		if($this->PostData['keyword']){//店铺id
			$conditions = " AND g.goods_name LIKE '%".$this->PostData['keyword']."%'";
		}
		
		if($this->PostData['id']){
			$conditions .= " AND g.store_id =".$this->PostData['id'];
		}
		else{
			$conditions .= " AND g.store_id ".db_create_in($this->GetLocation());
		}
		
		$goods_list = $goods_mod->find(array(
			'conditions'=> 'pro.start_time <='.gmtime(). ' AND pro.end_time>='.gmtime().$conditions,
			'join'      => 'belongs_to_store,has_limitbuy,has_goodsstatistics',
			'fields'    => 'pro.*,s.store_name,g.default_image,g.price,g.default_spec,g.goods_name,g.default_spec,g.cate_id_1,g.cate_id_2,goods_statistics.sales',
			'limit'     => $page['limit'],
			'count'     => true,
			'order'     => $sort.' '.$order
		));
			
		import('promotool.lib');
		$promotool = new Promotool();
			
		if($goods_list)
		{
			foreach ($goods_list as $key => $goods)
			{
				$result = $promotool->getItemProInfo($goods['goods_id'], $goods['default_spec']);
				if($result !== FALSE) {
					$goods_list[$key]['pro_price'] = $result['pro_price'];
				} else $goods_list[$key]['pro_price'] = $goods['price'];
					
				$goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
				if(stripos($goods['default_image'], '//:') == FALSE) {
					$goods['default_image'] = SITE_URL . '/' . $goods['default_image'];
				}
				
				$goods_list[$key]['default_image'] = $goods['default_image'];
					
				$goods_list[$key]['end_time'] = $goods['end_time']+date('Z');
			}
		}
	
		$page['item_count'] = $goods_mod->getCount();
		
		$this->json_success(array_values($goods_list));
	}
	
	function props()
	{
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('Product_is_not_exsist');
            return;
        }
		
		$goods_pvs_mod = &m('goods_pvs');
		$props_mod = &m('props');
		$prop_value_mod = &m('prop_value');
		$goods_pvs = $goods_pvs_mod->get($id);// 取出该商品的属性字符串
		$goods_pvs_str = $goods_pvs['pvs'];
		$props = array();
		if(!empty($goods_pvs_str))
		{
			$goods_pvs_arr = explode(';',$goods_pvs_str);//  分割成数组
			foreach($goods_pvs_arr as $pv)
			{
				if($pv)
				{
					$pv_arr = explode(':',$pv);
					$prop = $props_mod->get(array('conditions'=>'pid='.$pv_arr[0].' AND status=1'));
					if($prop)
					{
						$prop_value = $prop_value_mod->get(array('conditions'=>'pid='.$pv_arr[0].' AND vid='.$pv_arr[1].' AND status=1'));
						if($prop_value){
							if(isset($props[$pv_arr[0]])){
								$props[$pv_arr[0]]['value'] .= '，'.$prop_value['prop_value'];
							}
							else {
								$props[$pv_arr[0]] = array('name'=>$prop['name'],'value'=>$prop_value['prop_value']);
							}
						}
					}
				}
			}
		}

		$this->json_success($props);	
	}
	
}

?>
