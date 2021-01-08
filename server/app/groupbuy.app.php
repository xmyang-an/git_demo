<?php

class GroupbuyApp extends ApibaseApp
{
	function baseinfo()
	{
		$group_id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$group_id)
        {
            $this->json_fail('no_such_groupbuy');
            return false;
        }
        
		$groupbuy_mod = &m('groupbuy');
        $groupinfo = $groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $group_id . ' AND gb.state<>' . GROUP_PENDING,
            'join' => 'belong_store',
            'fields' => 'gb.*,s.owner_name'
        ));

        if (empty($groupinfo))
        {
            $this->json_fail('no_such_groupbuy');
            return;
        }
		
		if ($groupinfo['end_time'] < gmtime() && $groupinfo['state'] == GROUP_ON)
        {
            $groupinfo['state'] = GROUP_END; // 结束拼团
            $groupbuy_mod->edit($id,array('state' => $groupinfo['state']));
			
			$this->json_fail('group is end');
        }
		else{
			$groupinfo['end_time'] = $groupinfo['end_time']+date('Z');	
		}
		
		$groupinfo['spec_price'] = unserialize($groupinfo['spec_price']);
		
		$team_mod = &m('team');
		$teams = $team_mod->find('group_id='.$groupinfo['group_id'].' AND status IS NULL');
		if(!empty($teams)){
			foreach($teams as $k=>$v){
				$portrait = db()->getOne('select portrait from '.DB_PREFIX.'member where user_id='.$v['user_id']);
				$portrait = portrait($v['user_id'], $portrait);
				if(stripos($portrait, '//:') == FALSE) {
					$portrait = SITE_URL . '/' . $portrait;
				}
				
				$teams[$k]['portrait'] = $portrait;
				
				$end_time = $v['add_time']+$groupinfo['each_expire_time']*3600+date('Z');
				$teams[$k]['end_time'] = $end_time;	
				
				$teams[$k]['left'] = $groupinfo['min_quantity']-$v['number'];
			}
		}

		$groupinfo['teams'] = $teams;
		$groupinfo['teams_count'] = count($teams);
		$groupinfo['teams_json'] = ecm_json_encode($teams, true);
		
		$groupinfo['join_people'] = db()->getOne('select sum(number) from '.DB_PREFIX.'group_team where (status is null or status = 1) and group_id='.$groupinfo['group_id']);
		$groupinfo['team_on'] = db()->getOne('select count(*) from '.DB_PREFIX.'group_team where  status is null and group_id='.$groupinfo['group_id']);
		
		$this->json_success($groupinfo);
	}
	
	function GetGoodsQrcode()
	{
		$this->_checkUserAccess();
		
		$file = $this->GetWxMPQRCode(array(
			'user_id' => $this->PostData['user_id'],
			'id'      => $this->PostData['id'],
			'page'    => 'pages/groupbuy/index'
		));
		
		$this->json_success($file);
	}
	
	function listing()
	{
		$page = $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);
		
		$sort = 'group_id';
		$order = ' desc';
		if(isset($this->PostData['order'])){
			$order_fields = explode('|',$this->PostData['order']);

			if(in_array($order_fields[0],array('add_time', 'sales', 'views', 'price'))){
				if(in_array($order_fields[0],array('add_time', 'price'))){
					$sort = 'gb.'.$order_fields[0];
				}
				else{
					$sort = $order_fields[0];
				}
			}

			if(in_array($order_fields[1],array('asc','desc'))){
				$order = $order_fields[1];
			}
		}
		
		$conditions = 'gb.state ='. GROUP_ON .' AND gb.end_time>' . gmtime();
		
		if($this->PostData['keyword']){//店铺id
			$conditions .= " AND g.goods_name LIKE '%".$this->PostData['keyword']."%'";
		}
		
		if($this->PostData['id']){
			$conditions .= " AND g.store_id =".$this->PostData['id'];
		}
		else{
			$conditions .= " AND g.store_id ".db_create_in($this->GetLocation());
		}
		
		$groupbuy_mod = &m('groupbuy');
		$goods_list = $groupbuy_mod->find(array(
			'conditions'    => $conditions,
			'fields'        => 'gb.*,g.goods_name,g.default_image,g.price,default_spec,s.store_name',
			'join'          => 'has_goodsstatistics,belong_store, belong_goods',
			'limit'         => $page['limit'],
			'count'         => true,   //允许统计
			'order'         => $sort.' '.$order
		));
			
		if($goods_list)
		{
			foreach ($goods_list as $key => $goods)
			{
				$goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
				if(stripos($goods['default_image'], '//:') == FALSE) {
					$goods['default_image'] = SITE_URL . '/' . $goods['default_image'];
				}
				
				$goods_list[$key]['default_image'] = $goods['default_image'];
					
				$spec_price = unserialize($goods['spec_price']);
				$goods_list[$key]['group_price'] = $spec_price[$goods['default_spec']]['price'];
				$goods['state'] == GROUP_ON && $goods_list[$key]['lefttime'] = Psmb_init()->lefttime($goods['end_time']);
				
				$goods_list[$key]['end_time'] = $goods['end_time']+date('Z');
			}
		}
	
		$page['item_count'] = $groupbuy_mod->getCount();
		
		$this->json_success(array_values($goods_list));
	}
	
	function checkValid()
	{
		$this->_checkUserAccess();
		
		$spec_id	= isset($this->PostData['spec_id']) ? intval($this->PostData['spec_id']) : 0;
        $quantity   = isset($this->PostData['quantity']) ? intval($this->PostData['quantity']) : 0;
		$id   = isset($this->PostData['id']) ? intval($this->PostData['id']) : 0;
		$team_id   = isset($this->PostData['team_id']) ? intval($this->PostData['team_id']) : 0;
		$user_id = intval($this->PostData['user_id']);
		
        if (!$spec_id || !$quantity || !$id)
        {
            $this->json_fail('该团已成团或者已过期！');
        }
		
		$team_mod = &m('team');
		if($team_id > 0){
			$team = $team_mod->get('team_id='.$team_id.' AND status is NULL');
			if(empty($team)){
				$this->json_fail('该团已成团或者已过期！');
				exit;
			}

			if($team['user_id'] == $user_id){
				$this->json_fail('不能参加自己开的拼团！');
				exit;
			}
		}
		else{
			$checkExists = $team_mod->get('user_id='.$user_id.' AND (status IS NULL OR status in (1,2)) AND group_id=' . $id);
			if(!empty($checkExists)){
				$this->json_fail('每个会员仅可以对一个团购活动开一次团');
				exit;
			}
		}
 
		$groupbuy_mod = &m('groupbuy');
		$group = $groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $id . ' AND gb.state=' . GROUP_ON,
            'join' => 'belong_store',
            'fields' => 'spec_price'
        ));
		
		if (empty($group))
        {
            $this->json_fail('no_such_groupbuy');
            return false;
        }

        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,gs.stock',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_fail('no_such_goods');
            /* 商品不存在 */
            return;
        }

        /* 如果是自己店铺的商品，则不能购买 */
        if ($user_id)
        {
            if ($spec_info['store_id'] == $user_id)
            {
                $this->json_fail('can_not_buy_yourself');

                return;
            }
        }
		
        if ($quantity > $spec_info['stock'])
        {
            $this->json_fail('no_enough_goods');
            return;
        }
		
		
		$this->json_success('ok');
	}
}

?>
