<?php

class DcenterApp extends MemberbaseApp
{
	var $_distribution_mod;
	var $_member_mod;
	var $_user_id;
	var $_goods_mod;
	
    function __construct()
    {
        $this->DcenterApp();
    }
    function DcenterApp()
    {
        parent::__construct();
		$this->_goods_mod = &m('goods');
		$this->_distribution_mod = &m('distribution');
		$this->_member_mod = &m('member');
		$this->_user_id = $this->visitor->get('user_id');
		
    }
    function index()
    {
		$distribution = $this->_distribution_mod->get('user_id='.$this->_user_id);
		if(!$distribution)
		{
			$this->show_warning('not_join');
			exit;
		}
		
		$model_order = &m('order');
		$descendant_dids = $this->_distribution_mod->get_dids_by_user($this->_user_id);
        $allorders = $model_order->find(array(
            'conditions'=> 'did'. db_create_in($descendant_dids) . $conditions,
			'fields' 	=> 'order_id',
        ));
		$this->assign('orderscount',count($allorders));
        $this->assign('teamscount',count($this->_distribution_mod->find('parent_id='.$this->_user_id)));
		$this->assign('storescount',count($this->_distribution_mod->find('user_id='.$this->_user_id)));
		$this->assign('statistics',$this->_get_statistics());
		
		$this->_config_seo('title', Lang::get('distribution_center') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('distribution_center');
        $this->display('dcenter.index.html');
	}
	
	//修改小店名称上传小店logo
	function edit()
	{
		$did = intval($_GET['did']);
		$distribution = $this->_distribution_mod->get('user_id='.$this->_user_id.' AND did='.$did);
		if(!$did || !$distribution)
		{
			$this->show_warning('limit_error');
			return;
		}
		if(!IS_POST)
		{
			$this->assign('distribution', $distribution);
			$this->_get_curlocal_title('edit_info');
			$this->_config_seo('title', Lang::get('edit_info') . ' - ' . Lang::get('member_center'));
			$this->display('dcenter.form.html');
		}
		else
		{
			$real_name = trim($_POST['real_name']);
			$phone_mob = trim($_POST['phone_mob']);
			if(empty($real_name) || empty($phone_mob))
			{
				$this->show_warning('name_or_phone_empty');
				return;
			}
			if(!is_mobile($phone_mob)){
				$this->show_warning('input_phone_mob');
				return;
			}
			$data = array(
				'real_name' => $real_name,
				'phone_mob' => $phone_mob,
			);
			if($logo = $this->_upload_logo($did)) {
				$data['logo'] = $logo;
			}
			$this->_distribution_mod->edit('did='.$did,$data);
			$this->show_message('edit_info_ok','back_list', url('app=store&id='.$distribution['store_id'].'&did='.$did));			
		}
	}
	
	function profit()
	{
		if(!IS_AJAX)
		{
			$this->assign('statistics', $this->_get_statistics());
			
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('total_profit') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('total_profit');
			$this->display('dcenter.profit.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$model_order = &m('order');
			$descendant_dids = $this->_distribution_mod->get_dids_by_user($this->_user_id);
			$orders = $model_order->findAll(array(
				'conditions'    => 'status=40 AND did > 0 AND did'. db_create_in($descendant_dids),
				'limit' => $page['limit'],
				'count' => true,
				'order' => 'order_id desc',
			));
			$page['item_count'] = $model_order->getCount();
			$this->_format_page($page);
			
			foreach ($orders as $key => $order)
			{
				$orders[$key]['add_time'] = local_date('Y-m-d H:i:s', $order['add_time']);
				$refund = $this->_get_refund($order);
				$profit = $this->_distribution_mod->get_profit($order['order_id'], $refund);
				foreach($profit as $k=>$v)
				{
					if($v['user_id'] == $this->_user_id)
					{
						$orders[$key]['layer'] = $k+1;
						$orders[$key]['item_profit'] = $v['amount'];
					}
				}
			}
			if($_GET['layer'])  //刷选
			{
				foreach($orders as $k=>$v)
				{
					if($v['layer'] != $_GET['layer'])
					{
						unset($orders[$k]);
					}
				}
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($orders), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	function team()
	{
		if(!IS_AJAX)
		{
			$teams = $this->_distribution_mod->find(array(
				'conditions' =>'parent_id='.$this->_user_id,
				'count' => true,
				'fields' => 'did'
			));
			
			$this->assign('teamscount', $this->_distribution_mod->getCount());
			$this->assign('statistics',$this->_get_statistics());
			
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_curlocal_title('my_team');
			$this->_config_seo('title', Lang::get('my_team') . ' - ' . Lang::get('member_center'));
			$this->display('dcenter.team.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$teams = $this->_distribution_mod->find(array(
				'conditions' =>'parent_id='.$this->_user_id,
				'limit' => $page['limit'],
				'count' => true,
			));
			foreach($teams as $key => $team)
			{
				$teams[$key]['add_time'] = local_date('Y-m-d H:i:s', $team['add_time']);
				$member = $this->_member_mod->get(array('conditions' => 'user_id='.$team['user_id'], 'fields' => 'portrait'));
				$teams[$key]['portrait'] = portrait($team['user_id'], $member['portrait'], 'middle');
				$childs = $this->_distribution_mod->find(array(
					'conditions'=>'parent_id='.$team['user_id'], 
					'count' => true,
					'fields'=>'did'
				));
				$teams[$key]['childcount'] = $this->_distribution_mod->getCount();
			}
			$page['item_count'] = $this->_distribution_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($teams), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	function stores()
	{
		if(!IS_AJAX)
		{
			$stores = $this->_distribution_mod->find(array(
				'conditions' =>'user_id='.$this->_user_id,
				'fields' => 'did',
				'count'  => true
			));
			$this->assign('storescount', $this->_distribution_mod->getCount());
			$this->assign('statistics',$this->_get_statistics());
			
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('my_stores') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_stores');
			$this->display('dcenter.stores.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$stores = $this->_distribution_mod->find(array(
				'conditions' =>'user_id='.$this->_user_id,
				'limit' => $page['limit'],
				'count' => true
			));
			$store_mod = &m('store');
			$deposit_trade_mod = &m('deposit_trade');
			foreach($stores  as $key => $store)
			{
				$store_info = $store_mod->get(array(
					'conditions'=>'store_id='.$store['store_id'],
					'fields'=>'store_logo,store_name,owner_name,tel,enable_distribution,distribution_1,distribution_2,distribution_3'
				));
				if(empty($store['logo'])) {
					$stores[$key]['logo'] = $store_info['store_logo'] ? $store_info['store_logo'] : Conf::get('default_store_logo');
				}
				$stores[$key]['store_name'] = $store_info['store_name'];
				$stores[$key]['owner_name'] = $store_info['owner_name'];
				$stores[$key]['tel'] 		= $store_info['tel'];
				$stores[$key]['enable_distribution'] = $store_info['enable_distribution'];
				
				if(($find = $this->_distribution_mod->get_parent_ids($store['did'], true)) && in_array(count($find), array(1,2,3))) {
					$stores[$key]['distribution_rate'] = $store_info['distribution_'.count($find)];
				} else $stores[$key]['distribution_rate'] = 0;
				 
				$trades = end($deposit_trade_mod->find(array(
					'conditions' => "bizIdentity='".TRADE_FX."' AND buyer_id=".$this->_user_id.' AND seller_id='.$store['store_id'],
					'fields' => 'sum(amount) as allamount',
				)));
				if($trades['allamount']) $stores[$key]['amount'] = $trades['allamount']; else $stores[$key]['amount'] = 0;
			}
			
			$page['item_count'] = $this->_distribution_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($stores), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	function order()
	{
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_curlocal_title('my_order');
			$this->_config_seo('title', Lang::get('my_order') . ' - ' . Lang::get('member_center'));
			$this->display('dcenter.order.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$model_order =& m('order');	
			!$_GET['type'] && $_GET['type'] = 'all_orders';
			$conditions = $this->_get_query_conditions(array(
				array(
					'field' => 'status',
					'name'  => 'type',
					'handler' => 'order_status_translator',
				),
			));
			$descendant_dids = $this->_distribution_mod->get_dids_by_user($this->_user_id);
			$orders = $model_order->findAll(array(
				'conditions'    => ' did > 0 AND did'. db_create_in($descendant_dids) . $conditions,
				'count'         => true,
				'join'          => 'has_orderextm',
				'limit'         => $page['limit'],
				'order'         => 'add_time DESC',
				'include'       =>  array(
					'has_ordergoods',       //取出商品
				),
			));
			
			foreach ($orders as $key => $order)
			{
				$orders[$key]['add_time'] = local_date('Y-m-d H:i:s', $order['add_time']);
				
				
				$distribution = $this->_distribution_mod->get('did='.$order['did']);
				if($distribution)
				{
					$orders[$key]['dtb_name'] = $distribution['real_name'];
					$orders[$key]['dtb_phone_mob'] = $distribution['phone_mob'];
				}
				$refund = $this->_get_refund($order);
				$profit = $this->_distribution_mod->get_profit($order['order_id'], $refund);
				foreach($profit as $k=>$v)
				{
					if($v['user_id'] == $this->_user_id)
					{
						$orders[$key]['layer'] = $k+1;
						$orders[$key]['item_profit'] = $v['amount'];
					}
				}
				if($refund)
				{
					$orders[$key]['status'] = 'refund_success';
				}
				else
				{
					$orders[$key]['status'] = order_status($order['status']);
				}
				
				// JSON  need
				$orders[$key]['order_goods'] = array_values($order['order_goods']);
			}
			$page['item_count'] = $model_order->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array(
				'retval' => array(
								'list' => array_values($orders), 
								//'statistics' => $this->_get_order_statistics($conditions), 
								'total_statistics' => $this->_get_statistics(),
							)
				), 
				'totalPage' => $page['page_count']
			);
			echo json_encode($data);
		}
	}
	
	function _get_orders()
	{
		$page = $this->_get_page(10);
        $model_order =& m('order');	
        !$_GET['type'] && $_GET['type'] = 'all_orders';
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
        ));
		$descendant_dids = $this->_distribution_mod->get_dids_by_user($this->_user_id);
        $orders = $model_order->findAll(array(
            'conditions'    => ' did > 0 AND did'. db_create_in($descendant_dids).$conditions,
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
		
		foreach ($orders as $key => $order)
        {
			$distribution = $this->_distribution_mod->get('did='.$order['did']);
			if($distribution)
			{
				$orders[$key]['dtb_name'] = $distribution['real_name'];
				$orders[$key]['dtb_phone_mob'] = $distribution['phone_mob'];
			}
			$refund = $this->_get_refund($order);
			$profit = $this->_distribution_mod->get_profit($order['order_id'],$refund);
			foreach($profit as $k=>$v)
			{
				if($v['user_id'] == $this->_user_id)
				{
					$orders[$key]['layer'] = $k+1;
					$orders[$key]['item_profit'] = $v['amount'];
				}
			}
			if($refund)
			{
				$orders[$key]['status'] = 'refund_success';
			}
        }
        $page['item_count'] = $model_order->getCount();
		$this->_format_page($page);
		return array('orders'=>$orders,'page_info'=>$page,'statistics'=>$this->_get_order_statistics($conditions));
	}
	
	function _get_order_statistics($conditions='')
	{
		$model_order = &m('order');
		$descendant_dids = $this->_distribution_mod->get_dids_by_user($this->_user_id);
        $allorders = $model_order->find(array(
            'conditions'    => ' did > 0 AND did'. db_create_in($descendant_dids).$conditions,
        ));

		$order_statistics = array();
		foreach($allorders as $key => $order)
		{
			$refund = $this->_get_refund($order);
			$profit = $this->_distribution_mod->get_profit($order['order_id'],$refund);
			foreach($profit as $k=>$v)
			{
				if($v['user_id'] == $this->_user_id)
				{
					$order_statistics['layer'.($k+1)]['count'] ++;
					$order_statistics['total']['count'] ++;
					$order_statistics['total']['amount'] += $v['amount'];
				}
			}
		}
		return $order_statistics;
	}
	
	//佣金统计
	function _get_statistics()
	{
		$model_distribution_statistics = &m('distribution_statistics');
		$statistics = $model_distribution_statistics->get($this->_user_id);
		return $statistics;
	}
	
	function ranks()
	{
		$deposit_trade_mod = &m('deposit_trade');
		$all_records = $deposit_trade_mod->find(array(
			'conditions' => "flow='income' AND bizIdentity='".TRADE_FX."' GROUP BY buyer_id",
			'fields' => 'buyer_id,sum(amount) as amount',
			'order'  => 'amount desc',
		));
		$member_mod = &m('member');	
		foreach($all_records as $record)
		{
			$distribution = $this->_distribution_mod->get('user_id='.$record['buyer_id']);
			$ranks[$record['buyer_id']]['user_id'] = $record['buyer_id']; 
			$ranks[$record['buyer_id']]['real_name'] = $distribution['real_name'];
			$member = $member_mod->get($record['buyer_id']);
			$ranks[$record['buyer_id']]['logo'] = $distribution['logo'] ? $distribution['logo'] : portrait($distibution['user_id'], $member['portrait'], 'middle');
			$ranks[$record['buyer_id']]['teams'] = count($this->_distribution_mod->find('parent_id='.$record['buyer_id']));
			$ranks[$record['buyer_id']]['amount'] = $record['amount']; 
		}
		if(is_array($ranks) && $rank = array_keys(array_keys($ranks),$this->_user_id,false))
		{
			$my_rank['rank'] = $rank[0]+1;
		}
		$my_rank['amount'] = $ranks[$this->_user_id]['amount'];
		$this->assign('my_rank',$my_rank);
		$this->assign('ranks',$ranks);
		$this->_get_curlocal_title('ranks');
		$this->_config_seo('title', Lang::get('ranks') . ' - ' . Lang::get('member_center'));
		$this->display('dcenter.ranks.html');
	}
	
	function _upload_logo($did)
    {
        import('uploader.lib');
        $file = $_FILES['logo'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            //$uploader->allowed_size(SIZE_STORE_LOGO);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            return $uploader->save('data/files/mall/distribution', 'store_logo_'.$did);
        }
	}
	
	function _get_refund($order)
	{
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod = &m('refund');
		$sql = "select refund_goods_fee from {$deposit_trade_mod->table} as t left join {$refund_mod->table} as r on t.tradeNo=r.tradeNo where r.status='SUCCESS' and bizOrderId='{$order['order_sn']}' and r.buyer_id={$order['buyer_id']}";
		$data = current($deposit_trade_mod->getAll($sql));
		return $data['refund_goods_fee'];
	}
}

?>
