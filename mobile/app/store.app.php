<?php

class StoreApp extends StorebaseApp
{
    function index()
    {
        /* 店铺信息 */
        $_GET['act'] = 'index';
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->set_store($id);
        $store = $this->get_store_data();
		
		$distribution_mod =&m('distribution');
		$store = array_merge($store, $distribution_mod->getCheckJoinDistributionInfo($this->visitor->get('user_id'), $id, intval($_GET['did'])));
		$this->setCookieDid($store['did'], $id);
		
        /* 取得推荐商品 */
		$store['recommended_goods'] = $this->_get_goods($id,6, 'recommended');
        /* 取得最新商品 */
		$store['new_goods'] = $this->_get_goods($id,6, 'new');
		/* 取得热卖商品 */
		$store['sales_goods'] = $this->_get_goods($id,6, 'sales');
		/* 取得人气商品 */
		$store['hot_goods'] = $this->_get_goods($id,6, 'hot');
		
		$this->assign('store', $store);

		// 微信分享
		import('jssdk.lib');
		$jssdk = new JSSDK(Conf::get('weixinkey.AppID'), Conf::get('weixinkey.AppSecret')); // 微信公众号的
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage', json_encode($signPackage));
		$this->import_resource('mobile/weixin/jweixin-1.0.0.js,mobile/weixin/share.js, mobile/jquery.plugins/jquery.form.min.js');

		$this->_config_seo('title', $store['store_name'] . ' - ' . Conf::get('site_title'));
		$this->_get_curlocal_title($store['store_name']);
		$this->display('store.index.html');
    }
	
    function search()
    {
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
		if(!IS_AJAX)
		{		
			if (!$id)
			{
				$this->show_warning('Hacking Attempt');
				return;
			}
			$this->set_store($id);
			$store = $this->get_store_data();
			$this->assign('store', $store);
			
			$gcategorys = $this->_get_store_gcategory();
			$this->assign('categories', $gcategorys);
			
			/* 商品展示方式 */
			$display_mode = ecm_getcookie('storeGoodsDisplayMode');
			if (empty($display_mode) || !in_array($display_mode, array('list', 'squares')))
			{
				$display_mode = 'list'; // 默认列表方式
			}
			$this->assign('display_mode', $display_mode);
	
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,mobile/jquery.plugins/jquery.infinite.js,mobile/search_goods.js');
			$this->assign('infiniteParams', json_encode($_GET));
	
			/* 配置seo信息 */
			$this->_config_seo('title', Lang::get('goods_list') . ' - ' . $store['store_name']);
			$this->_get_curlocal_title(Lang::get('goods_list'));
			$this->display('store.search.html');
		}
		else
		{
        	/* 搜索到的商品 */
			$this->_assign_searched_goods($id);
		}
    }
	
	function limitbuy()
    {
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
        if(!IS_AJAX)
		{
			if (!$id)
			{
				$this->show_warning('Hacking Attempt');
				return;
			}
			$this->set_store($id);
			$store = $this->get_store_data();
			$this->assign('store', $store);
		
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));

			$this->_get_curlocal_title('limitbuy');
			$this->assign('goods_list', $goods_list);
			$this->_config_seo('title', Lang::get('limitbuy').' - ' . $store['store_name']);
			$this->display('store.limitbuy.html');
		}
		else
		{
			$limitbuy_mod = &m('limitbuy');
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$goods_list = $limitbuy_mod->find(array(
				'conditions'=>'start_time <='.gmtime(). ' AND end_time>='.gmtime() .' AND pro.store_id='.$id,
				'join'      =>'belong_goods,has_goodsstatistics',
				'fields'    =>'this.*,g.default_image,g.price,g.default_spec,g.goods_name,g.default_spec,g.cate_id_1,g.cate_id_2',
				'limit'     =>$page['limit'],
				'count'     =>true,
				'order'     =>'pro_id DESC'
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
					
					$goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
					
					$goods_list[$key]['lefttime'] = Psmb_init()->lefttime($goods['end_time']);
				}
			}
	
			$page['item_count'] = $limitbuy_mod->getCount();
			$this->_format_page($page);
		
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($goods_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	function category()
	{
		$id = intval($_GET['id']);
		if(!$id)
		{
			$this->show_warning('Hacking Attempt');
			return;
		}
		$this->set_store($id);
        $store = $this->get_store_data();
        $this->assign('store', $store);
		
    	$gcategorys = $this->_get_store_gcategory();
		$this->assign('gcategorys', $gcategorys);
		
		/* 配置seo信息 */
        $this->_config_seo('title', Lang::get('gcategory') . ' - ' . $store['store_name']);
		$this->_get_curlocal_title(Lang::get('gcategory'));
        $this->display('store.category.html');
	}
	
	function map()
	{
		$store_id = intval($_GET['id']);
		if(!$store_id)
		{
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		$store_mod = &m('store');
		$store = $store_mod->get(array('conditions'=>'store_id='.$store_id, 'fields'=> 'lat,lng,store_name,store_logo'));
		if(!$store || !$store['lat'] || !$store['lng'])
		{
			$this->show_warning('not_config_position');
			return;
		}
		empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');

		$this->assign('store', $store);
		
		$this->headtag('<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak='.Conf::get('baidukey.browser').'"></script>');
		$this->_config_seo('title', Lang::get('store_map') . ' - ' . $store['store_name']);
		$this->_get_curlocal_title('store_map');
		$this->display('store.map.html');
	}
	
	/* 取得店铺分类 */
    function _get_store_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $gcategory_mod->get_list(-1, true);
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }
	
	// 取得推荐，热卖，最新，人气商品
	function _get_goods($id, $num = 10, $type = 'recommended')
	{
		$conditions ='';
		$order = 'g.add_time DESC';
		if($type == 'recommended') {
			$conditions .= ' AND recommended = 1';
		}
		elseif($type == 'new') {
			$order = 'g.add_time DESC';
		}
		elseif($type == 'sales') {
			$order = 'goodsstatistics.sales DESC';	
		}
		elseif($type == 'hot') {
			$order = 'goodsstatistics.views DESC';
		}
		
		$goods_mod =& bm('goods', array('_store_id' => $id));
        $goods_list = $goods_mod->find(array(
            'conditions' => "closed = 0 AND if_show = 1 " . $conditions,
			'join'       => 'has_goodsstatistics',
            'fields'     => 'goods_name, default_image, price,sales',
            'limit'      => $num,
			'order'		 => $order
        ));
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        return $goods_list;
	}

    /* 搜索到的结果 */
    function _assign_searched_goods($id)
    {
        $goods_mod =& bm('goods', array('_store_id' => $id));

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'name'  => 'keyword',
                'equal' => 'like',
            ),
        ));
        if ($conditions)
        {
            //$search_name = $_GET['keyword'];
            $sgcate_id   = 0;
        }
        else
        {
            $sgcate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        }

        if ($sgcate_id > 0)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => $id));
            $sgcate = $gcategory_mod->get_info($sgcate_id);
            //$search_name = $sgcate['cate_name'];
            $sgcate_ids = $gcategory_mod->get_descendant_ids($sgcate_id);
        }
        else
        {
            $sgcate_ids = array();
        }
		
		if(isset($_GET['new'])) {
			(intval($_GET['new']) == 1)  && $_GET['order'] = 'add_time desc';
		}

        /* 排序方式 */
        $orders = array(
           'add_time desc' 	=> LANG::get('add_time_desc'),
		   'add_time asc' 	=> LANG::get('add_time_asc'),
           'price asc' 		=> LANG::get('price_asc'),
           'price desc' 	=> LANG::get('price_desc'),
		   'views desc'     => Lang::get('views_desc'),
		   'sales desc'     => Lang::get('sales_desc'),
        );
	
        $page = $this->_get_page(intval($_GET['pageper']));
        $goods_list = $goods_mod->get_list(array(
            'conditions' 	=> 'closed = 0 AND if_show = 1' . $conditions,
            'count' 		=> true,
			'order'    		=> isset($_GET['order']) && isset($orders[$_GET['order']]) ? $_GET['order'] : 'views DESC',
            'limit'			=> $page['limit'],
        ), $sgcate_ids);
        foreach ($goods_list as $key => $goods)
        {
            empty($goods['default_image']) && $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
        }

        $page['item_count'] = $goods_mod->getCount();
        $this->_format_page($page);
		
		// 必须加 array_values() js遍历顺序才对
		$data = array('result' => array_values($goods_list), 'totalPage' => $page['page_count']);
		echo json_encode($data);
    }
}

?>
