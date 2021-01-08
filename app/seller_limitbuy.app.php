<?php

/* 限时打折管理控制器 */
class Seller_limitbuyApp extends StoreadminbaseApp
{
	var $_appid;
	var $_appmarket_mod;
	var $_goods_mod;
    var $_store_mod;
	var $_spec_mod;
	var $_limitbuy_mod;
	

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_limitbuyApp();
    }

    function Seller_limitbuyApp()
    {
        parent::__construct();

		$this->_appid     = 'limitbuy';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
        $this->_spec_mod  =& m('goodsspec');
		$this->_limitbuy_mod = & m('limitbuy');
		$this->_appmarket_mod = &m('appmarket');
    }

    function index()
    {
		$conditions = '';
		if(!empty($_GET['pro_name'])){
			$conditions = " AND (pro.pro_name LIKE '%".html_script(trim($_GET['pro_name']))."%' OR goods_name LIKE '%".html_script(trim($_GET['pro_name']))."%' ) ";
		}
		
		$page   =   $this->_get_page(10);    //获取分页信息
        $limitbuy_list = $this->_limitbuy_mod->find(array(
			'join' => 'belong_goods',
			'conditions' => "pro.store_id=".$this->_store_id . $conditions,
			'order' => 'pro.pro_id DESC',
			'limit' => $page['limit'],  //获取当前页的数据
			'fields' => 'pro.*,g.goods_name,g.default_image,g.price,g.default_spec',
			'count' => true
		));
        $page['item_count'] = $this->_limitbuy_mod->getCount();   //获取统计的数据
		
		import('promotool.lib');
		$promotool = new Promotool();
		
        foreach ($limitbuy_list as $key => $limitbuy)
        {
			$spec_price = unserialize($limitbuy['spec_price']);
			if(isset($spec_price[$limitbuy['default_spec']])) {
				if($spec_price[$limitbuy['default_spec']]['pro_type'] == 'discount') {
					$limitbuy_list[$key]['pro_price'] = round($limitbuy['price'] * $spec_price[$limitbuy['default_spec']]['price']/10, 2);
				} else {
					$limitbuy_list[$key]['pro_price'] = $limitbuy['price'] - $spec_price[$limitbuy['default_spec']]['price'];
				}
			}
			if($limitbuy_list[$key]['pro_price'] < 0) $limitbuy_list[$key]['pro_price'] = $limitbuy['price'];

            if($limitbuy['image']) {
				$limitbuy_list[$key]['default_image'] = $limitbuy['image'];
			}
			else {
				$limitbuy['default_image'] || $limitbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
			}
			
			
			/* 判断状态 */
			$limitbuy_list[$key]['status'] = Lang::get($this->_limitbuy_mod->get_limitbuy_status($limitbuy, true));
        }
	
		/* 当前位置 */
        $this->_curlocal(LANG::get('limitbuy_manage'), 'index.php?app=seller_limitbuy',
                         LANG::get('limitbuy_list'));
		/* 当前用户中心菜单 */
        $this->_curitem('limitbuy_manage');
		 /* 当前所处子菜单 */
        $this->_curmenu('limitbuy_list');
		$this->_format_page($page);
        $this->_import_resource();
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('limitbuy_list', $limitbuy_list);
		$this->assign('filtered', $conditions ? 1 : 0);
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('limitbuy_list'));
		$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		$this->display('seller_limitbuy.index.html');
	}
	
	function add() 
	{
		if(!IS_POST) 
		{
			$goods_mod = &bm('goods', array('_store_id' => $this->_store_id));
            $goods_count = $goods_mod->get_count();
            if ($goods_count == 0)
            {
                $this->show_warning('has_no_goods', 'add_goods', 'index.php?app=my_goods&act=add');
                return;
            }
			
			/* 当前位置 */
            $this->_curlocal(LANG::get('limitbuy_manage'), 'index.php?app=seller_limitbuy',
                             LANG::get('add_limitbuy'));

            /* 当前用户中心菜单 */
            $this->_curitem('limitbuy_manage');

            /* 当前所处子菜单 */
            $this->_curmenu('add_limitbuy');
			$this->assign('store_id', $this->_store_id);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('add_limitbuy'));
			$this->_import_resource();
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_limitbuy.form.html');
		}
		else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, 0))
            {
                $this->show_warning($this->get_error());
                return;
            }
            $limitbuy_info = $this->_limitbuy_mod->get($this->_last_update_id);
            if ($limitbuy_info)
            {
                $_goods_info  = $this->_query_goods_info($limitbuy_info['goods_id']);
                $limitbuy_url = SITE_URL . '/' . url('app=goods&id=' . $limitbuy_info['goods_id']);
                $feed_images = array();
                $feed_images[] = array(
                    'url'   => SITE_URL . '/' . $_goods_info['default_image'],
                    'link'   => $limitbuy_url,
                );
                $this->send_feed('limitbuy_created', array(
                    'user_id' => $this->visitor->get('user_id'),
                    'user_name' => $this->visitor->get('user_name'),
                    'limitbuy_url' => $limitbuy_url,
                    'pro_name' => $limitbuy_info['pro_name'],
                    'message' => $limitbuy_info['pro_desc'],
                    'images' => $feed_images,
                ));
            }
			//  立即更新
			$cache_server =& cache_server();
        	$cache_server->clear();
			
            $this->show_message('add_limitbuy_ok',
                'back_list', 'index.php?app=seller_limitbuy',
                'continue_add', 'index.php?app=seller_limitbuy&amp;act=add'
            );
        }
		
	}
	function edit()
    {
        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$id)
        {
            $this->show_warning('no_such_limitbuy');
            return false;
        }
        if (!IS_POST)
        {
            /* 当前位置 */
            $this->_curlocal(LANG::get('limitbuy_manage'), 'index.php?app=seller_limitbuy',
                             LANG::get('edit_limitbuy'));

            /* 当前用户中心菜单 */
            $this->_curitem('limitbuy_manage');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_limitbuy');

            /* 促销信息 */
            $limitbuy = $this->_limitbuy_mod->get($id);
            $limitbuy['spec_price'] = unserialize($limitbuy['spec_price']);
            $goods = $this->_query_goods_info($limitbuy['goods_id']);
            foreach ($goods['_specs'] as $key => $spec)
            {
                if (!empty($limitbuy['spec_price'][$spec['spec_id']]))
                {
                    $goods['_specs'][$key]['pro_price'] = $limitbuy['spec_price'][$spec['spec_id']]['price'];
					$goods['_specs'][$key]['pro_type'] = $limitbuy['spec_price'][$spec['spec_id']]['pro_type'];
                }
            }
            $this->assign('limitbuy', $limitbuy);
            $this->assign('goods', $goods);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('edit_limitbuy'));
            $this->_import_resource();
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
            $this->display('seller_limitbuy.form.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }
			//  立即更新
			$cache_server =& cache_server();
        	$cache_server->clear();
			
            $this->show_message('edit_limitbuy_ok',
                'back_list', 'index.php?app=seller_limitbuy',
                'continue_edit', 'index.php?app=seller_limitbuy&act=edit&id=' . $id
            );
        }
    }
	function drop()
    {
        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$id)
        {
            $this->show_warning('no_such_limitbuy');
            return false;
        }
        if (!$this->_limitbuy_mod->drop($id))
        {
            $this->show_warning($this->_limitbuy_mod->get_error());

            return;
        }

        $this->show_message('drop_limitbuy_successed');
    }
	/**
     * 检查提交的数据
     */
    function _handle_post_data($post, $id = 0)
    {
		if (gmstr2time($post['start_time']) <= gmtime())
        {
            $post['start_time'] = gmtime();
        }
        else
        {
            $post['start_time'] = gmstr2time($post['start_time']);
        }
        if (intval($post['end_time']))
        {
			/* 不能为 gmstr2time($post['end_time'])，如果用 gmstr2time 将会导致前一天就结束 */
			/* 如果发现提交后自动增加一天，则是时区+8问题 */
            $post['end_time'] = gmstr2time_end($post['end_time']) -1;
        }
        else
        {
            $this->_error('fill_end_time');
            return false;
        }
        if ($post['end_time'] < $post['start_time'])
        {
            $this->_error('start_not_gt_end');
            return false;
        }
		
		// 如果结束的时间大于该应用的购买时限，则不允许
		$apprenewal_mod = &m('apprenewal');
		$apprenewal = $apprenewal_mod->get(array(
			'conditions' => "appid='limitbuy' AND user_id=" . $this->visitor->get('user_id'), 'fields' => 'expired', 'order' => 'rid DESC'));
			
		if(!$apprenewal) {
			$this->_error('appHasNotBuy');
			return false;	
		}
		if($apprenewal['expired'] <= ($post['end_time']))
		{
			$this->_error(sprintf(Lang::get('limitbuy_end_time_gt_app_expired'), local_date('Y-m-d', $apprenewal['expired'])));
			return false;
		}

        if (($post['goods_id'] = intval($post['goods_id'])) == 0)
        {
            $this->_error('fill_goods');
            return false;
        }
		if(($limitbuy = $this->_limitbuy_mod->get(array('conditions'=>'goods_id='.$post['goods_id'], 'fields' => 'pro_id'))) && ($limitbuy['pro_id'] != $id))
		{
			$this->_error('goods_has_set_limitbuy');
			return false;
		}
        if (empty($post['spec_id']) || !is_array($post['spec_id']))
        {
            $this->_error('fill_spec');
            return false;
        }
		$spec_price = array();
        foreach ($post['spec_id'] as $key => $val)
        {
			if (empty($post['pro_price'][$val]))
            {
                $this->_error('invalid_pro_price');
                return false;
				break;
            }
			else
			{
				if($post['pro_type'][$val] == 'discount' && ($post['pro_price'][$val] >= 10 || $post['pro_price'][$val] <=0)) {
					$this->_error('invalid_pro_price_discount');
                	return false;
					break;
				}
				if($post['pro_type'][$val] == 'price' && ($post['pro_price'][$val] >= $post['price'][$val] || $post['pro_price'][$val] == 0)) {dump($post);
					$this->_error('invalid_pro_price_price');
                	return false;
					break;
				}
			}
			
            $spec_price[$val] = array('price' => $post['pro_price'][$val], 'pro_type' => $post['pro_type'][$val]);
        }
        $data = array(
            'pro_name' => $post['pro_name'],
            'pro_desc' => $post['pro_desc'],
            'start_time' => $post['start_time'],
            'end_time'   => $post['end_time'],
            'goods_id'   => $post['goods_id'],
            'spec_price' => serialize($spec_price),
            'store_id'     => $this->_store_id
        );
		$image = $this->_upload_image();
		if ($image != false){
			$data['image'] = $image;
		}
        if ($id > 0)
        {
            $this->_limitbuy_mod->edit($id, $data);
            if ($this->_limitbuy_mod->has_error())
            {
                $this->_error($this->_limitbuy_mod->get_error());
                return false;
            }
        }
        else
        {
            if (!($id = $this->_limitbuy_mod->add($data)))
            {
                $this->_error($this->_limitbuy_mod->get_error());
                return false;
            }
        }
        $this->_last_update_id = $id;

        return true;
    }
	function query_goods_info()
    {
        $goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
        if ($goods_id)
        {
            $goods = $this->_query_goods_info($goods_id);
            $this->json_result($goods);
        }
    }
	function _query_goods_info($goods_id)
    {
        $goods = $this->_goods_mod->get_info($goods_id);
        if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
        {
            $goods['spec_name'] = htmlspecialchars($goods['spec_name_1'] . ($goods['spec_name_2'] ? ' ' . $goods['spec_name_2'] : ''));
        }
        else
        {
            $goods['spec_name'] = Lang::get('spec');
        }
		
        foreach ($goods['_specs'] as $key => $spec)
        {
            if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
            {
                $goods['_specs'][$key]['spec'] = htmlspecialchars($spec['spec_1'] . ($spec['spec_2'] ? ' ' . $spec['spec_2'] : ''));
			}
		    else
            {
                $goods['_specs'][$key]['spec'] = Lang::get('default_spec');
            }
					
        }
        $goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
        return $goods;
    }
	function query_goods()
    {
        $goods_mod = &bm('goods', array('_store_id' => $this->_store_id));

        /* 搜索条件 */
        $conditions = "1 = 1";
        if (trim($_GET['goods_name']))
        {
            $str = "LIKE '%" . trim($_GET['goods_name']) . "%'";
            $conditions .= " AND (goods_name {$str})";
        }

        if (intval($_GET['sgcate_id']) > 0)
        {
            $cate_mod =& bm('gcategory', array('_store_id' => $this->visitor->get('manage_store')));
            $cate_ids = $cate_mod->get_descendant(intval($_GET['sgcate_id']));
        }
        else
        {
            $cate_ids = 0;
        }

        /* 取得商品列表 */
        $goods_list = $goods_mod->get_list(array(
            'conditions' => $conditions . ' AND g.if_show=1 AND g.closed=0',
            'order' => 'g.add_time DESC',
            'limit' => 100,
        ), $cate_ids);

        foreach ($goods_list as $key => $val)
        {
            $goods_list[$key]['goods_name'] = htmlspecialchars($val['goods_name']);
        }
        $this->json_result($goods_list);
    }
	function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'limitbuy_list',
                'url'   => 'index.php?app=seller_limitbuy',
            ),
			array(
                'name'  => 'add_limitbuy',
                'url'   => 'index.php?app=seller_limitbuy&act=add',
            ),
			
        );
        if (ACT == 'edit')
        {
            $menus[] = array(
                'name' => ACT . '_limitbuy',
                'url'  => '',
            );
        }
        return $menus;
	}
	function _import_resource()
    {
        if(in_array(ACT, array('index' , 'add', 'edit')))
        {
            $resource['script'][] = array( // JQUERY UI
                'path' => 'jquery.ui/jquery.ui.js'
            );
        }
        if(in_array(ACT, array('index', 'add', 'edit')))
        {
            $resource['script'][] = array( // 对话框
                'attr' => 'id="dialog_js"',
                'path' => 'dialog/dialog.js'
            );
        }
        if(in_array(ACT, array('add', 'edit')))
        {
            $resource['script'][] = array( // 验证
                'path' => 'jquery.plugins/jquery.validate.js'
            );
        }
        if(in_array(ACT, array('add', 'edit'))) //日历相关
        {
            $resource['script'][] = array(
                'path' => 'jquery.ui/i18n/' . i18n_code() . '.js'
            );
            $resource['style'] .= 'jquery.ui/themes/smoothness/jquery.ui.css';
        }
        $this->import_resource($resource);
    }
	
	/* 上传活动图片 */
	function _upload_image()
    {
        import('uploader.lib');
        $file = $_FILES['image'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            return $uploader->save('data/files/store_'.$this->_store_id.'/limitbuy', $uploader->random_filename());
        }
        return false;
    }
}

?>
