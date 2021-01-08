<?php

/**
 *    我的收藏控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class My_favoriteApp extends MemberbaseApp
{
    /**
     *    收藏列表
     *
     *    @author    MiMall
     *    @return    void
     */
    function index()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        if ($type == 'goods')
        {
            $this->_list_collect_goods();
        }
        elseif ($type == 'store')
        {
            /* 收藏店铺 */
            $this->_list_collect_store();
        }
    }

    /**
     *    收藏项目
     *
     *    @author    MiMall
     *    @return    void
     */
    function add()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        $item_id = empty($_GET['item_id'])  ? 0 : intval($_GET['item_id']);
        $keyword = empty($_GET['keyword'])  ? '' : trim($_GET['keyword']);
        if (!$item_id)
        {
            $this->json_error('no_such_collect_item');

            return;
        }
        if ($type == 'goods')
        {
            $this->_add_collect_goods($item_id, $keyword);
        }
        elseif ($type == 'store')
        {
            $this->_add_collect_store($item_id, $keyword);
        }
    }
    /**
     *    删除收藏的项目
     *
     *    @author    MiMall
     *    @return    void
     */
    function drop()
    {
        $type = empty($_GET['type'])    ? 'goods' : trim($_GET['type']);
        $item_id = empty($_GET['item_id'])  ? 0 : trim($_GET['item_id']);
        if (!$item_id)
        {
            $this->json_error('no_such_collect_item');
            return;
        }
        if ($type == 'goods')
        {
            $this->_drop_collect_goods($item_id);
        }
        elseif ($type == 'store')
        {
            $this->_drop_collect_store($item_id);
        }
    }

    /**
     *    列表收藏的商品
     *
     *    @author    MiMall
     *    @return    void
     */
    function _list_collect_goods()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
        	$this->_config_seo('title', Lang::get('collect_goods') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_favorite');
        	$this->display('my_favorite.goods.index.html');
		}
		else
		{
			$model_goods =& m('goods');
			$page   =   $this->_get_page(intval($_GET['pageper']));    //获取分页信息
			$collect_goods = $model_goods->find(array(
				'join'  => 'be_collect',
				'fields'=> 'goods_name,default_image,price,cate_id',
				'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id'),
				'count' => true,
				'order' => 'collect.add_time DESC',
				'limit' => $page['limit'],
			));
			foreach ($collect_goods as $key => $goods)
			{
				empty($goods['default_image']) && $collect_goods[$key]['default_image'] = Conf::get('default_goods_image');
			}
			$page['item_count'] = $model_goods->getCount();   //获取统计的数据
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($collect_goods), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }

    /**
     *    列表收藏的店铺
     *
     *    @author    MiMall
     *    @return    void
     */
    function _list_collect_store()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
		
			$this->_config_seo('title', Lang::get('collect_store') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_favorite');
        	$this->display('my_favorite.store.index.html');
		}
		else
		{
			$conditions = $this->_get_query_conditions(array(array(
					'field' => 'store_name',         //可搜索字段title
					'equal' => 'LIKE',          //等价关系,可以是LIKE, =, <, >, <>
				),
			));
			
			$model_store =& m('store');
			$page   =   $this->_get_page(intval($_GET['pageper']));    //获取分页信息
			$collect_store = $model_store->find(array(
				'join'  => 'be_collect',
				'fields'=> 'store_name, store_logo, credit_value',
				'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id'),
				'count' => true,
				'order' => 'collect.add_time DESC',
				'limit' => $page['limit'],
			));
			$page['item_count'] = $model_store->getCount();   //获取统计的数据
			$this->_format_page($page);
			
			$step = intval(Conf::get('upgrade_required'));
			$step < 1 && $step = 5;
			$goods_mod = &m('goods');
			foreach ($collect_store as $key => $store)
			{
				empty($store['store_logo']) && $collect_store[$key]['store_logo'] = Conf::get('default_store_logo');
				$collect_store[$key]['credit_image'] = $this->_view->res_base . '/images/' . $model_store->compute_credit($store['credit_value'], $step);
				$goods_list = $goods_mod->find(array(
					'conditions' => 'store_id = '.$store['store_id'],
					'order'      => 'add_time desc',
					'fields'     => 'default_image',
					'limit'      => 10
				));
				$collect_store[$key]['goods_list'] = $goods_list;
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($collect_store), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }

    /**
     *    删除收藏的商品
     *
     *    @author    MiMall
     *    @param     int $item_id
     *    @return    void
     */
    function _drop_collect_goods($item_id)
    {
        $ids = explode(',', $item_id);

        /* 解除“我”与商品ID为$ids的收藏关系 */
        $model_user =& m('member');
        $model_user->unlinkRelation('collect_goods', $this->visitor->get('user_id'), $ids);
        if ($model_user->has_error())
        {
			$error = current($model_user->get_error());
			$this->json_error($error['msg']);
        	return;
        }
		$this->json_result('', 'drop_collect_goods_successed');
    }

    /**
     *    删除收藏的店铺
     *
     *    @author    MiMall
     *    @param     int $item_id
     *    @return    void
     */
    function _drop_collect_store($item_id)
    {
        $ids = explode(',', $item_id);

        /* 解除“我”与店铺ID为$ids的收藏关系 */
        $model_user =& m('member');
        $model_user->unlinkRelation('collect_store', $this->visitor->get('user_id'), $ids);
        if ($model_user->has_error())
        {
           	$error = current($model_user->get_error());
			$this->json_error($error['msg']);
        	return;
        }
        $this->json_result('', 'drop_collect_store_successed');
    }

    /**
     *    收藏商品
     *
     *    @author    MiMall
     *    @param     int    $goods_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_goods($goods_id, $keyword)
    {
        /* 验证要收藏的商品是否存在 */
        $model_goods =& m('goods');
        $goods_info  = $model_goods->get($goods_id);

        if (empty($goods_info))
        {
            /* 商品不存在 */
            $this->json_error('no_such_goods');
            return;
        }
        $model_user =& m('member');
        $model_user->createRelation('collect_goods', $this->visitor->get('user_id'), array(
            $goods_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));

        /* 更新被收藏次数 */
        $model_goods->update_collect_count($goods_id);

        $goods_image = $goods_info['default_image'] ? $goods_info['default_image'] : Conf::get('default_goods_image');
        $goods_url  = SITE_URL . '/' . url('app=goods&id=' . $goods_id);
        $this->send_feed('goods_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'goods_url'   => $goods_url,
            'goods_name'   => $goods_info['goods_name'],
            'images'    => array(array(
                'url' => SITE_URL . '/' . $goods_image,
                'link' => $goods_url,
            )),
        ));
		
		/* 因为收藏成功之后要改变图标，为了不取缓存，这里把缓存清掉 */
		$cache_server =& cache_server();
        $key = 'page_of_goods_' . $goods_id;
       	$cache_server->delete($key);

        /* 收藏成功 */
        $this->json_result('', 'collect_goods_ok');
    }

    /**
     *    收藏店铺
     *
     *    @author    MiMall
     *    @param     int    $store_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_store($store_id, $keyword)
    {
        /* 验证要收藏的店铺是否存在 */
        $model_store =& m('store');
        $store_info  = $model_store->get($store_id);
        if (empty($store_info))
        {
            /* 店铺不存在 */
			$this->json_error('no_such_store');
            return;
        }
        $model_user =& m('member');
        $model_user->createRelation('collect_store', $this->visitor->get('user_id'), array(
            $store_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));
        $this->send_feed('store_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
            'store_name'   => $store_info['store_name'],
        ));

        /* 收藏成功 */
        $this->json_result('', 'collect_store_ok');
    }
}

?>
