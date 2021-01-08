<?php

/* 限时打折管理控制器 */
class Seller_limitbuyApp extends StoreadminbaseApp
{
	var $_appid;
	var $_appmarket_mod;
	var $_goods_mod;
    var $_store_mod;
	var $_store_id;
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
		$this->_limitbuy_mod = & m('limitbuy');
		$this->_appmarket_mod = &m('appmarket');
    }

    function index()
    {
		if(!IS_AJAX)
		{
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => ''
					)
				),
				'style' =>  'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('limitbuy_list') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('limitbuy_list');
			$this->display('seller_limitbuy.index.html');
		}
		else
		{
			$page   =   $this->_get_page(intval($_GET['pageper']));    //获取分页信息
			$limitbuy_list = $this->_limitbuy_mod->find(array(
				'join' => 'belong_goods',
				'conditions' => "pro.store_id=".$this->_store_id,
				'order' => 'pro.pro_id DESC',
				'limit' => $page['limit'],  //获取当前页的数据
				'fields' => 'pro.*,g.goods_name,g.default_image,g.price,g.default_spec',
				'count' => true
			));
			$page['item_count'] = $this->_limitbuy_mod->getCount();   //获取统计的数据
			$this->_format_page($page);
			
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
				
				$limitbuy_list[$key]['start_time'] = local_date('m月d日 H:i', $limitbuy['start_time']);
				$limitbuy_list[$key]['end_time'] = local_date('m月d日 H:i', $limitbuy['end_time']);
				
				
				/* 判断状态 */
				$limitbuy_list[$key]['status'] = $this->_limitbuy_mod->get_limitbuy_status($limitbuy, true);
				$limitbuy_list[$key]['status_label'] = Lang::get($limitbuy_list[$key]['status']);
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($limitbuy_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
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
			
			$this->assign('store_id', $this->_store_id);
			$this->assign('today', gmtime());
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'webuploader/webuploader.js',
						'attr' => ''
					),
					array(
						'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					),
				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			
            $this->_config_seo('title', Lang::get('add_limitbuy') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('add_limitbuy');
			$this->display('seller_limitbuy.form.html');
		}
		else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, 0))
            {
				$error = $this->get_error();
				$error = current($error);
                $this->json_error($error['msg']);
                return;
            }
			
			//  立即更新
			$cache_server =& cache_server();
        	$cache_server->clear();
			
			$this->json_result(array('ret_url' => url("app=seller_limitbuy")),'add_limitbuy_ok');
        }
		
	}
	function edit()
    {
        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        
        if (!IS_POST)
        {
			if (!$id)
        	{
            	$this->show_warning('no_such_limitbuy');
           		return false;
        	}
		
            /* 促销信息 */
            $limitbuy = $this->_limitbuy_mod->get(array(
				'conditions' => 'pro_id='.$id, 'fields' => 'goods_id, pro_name, start_time, end_time, image'));
            $this->assign('limitbuy', $limitbuy);
			$this->assign('store_id', $this->_store_id);
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'webuploader/webuploader.js',
						'attr' => ''
					),
					array(
						'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					),
				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			
			$this->_config_seo('title', Lang::get('edit_limitbuy') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('edit_limitbuy');
            $this->display('seller_limitbuy.form.html');
        }
        else
        {
			if (!$id)
        	{
            	$this->json_error('no_such_limitbuy');
           		return false;
        	}
			
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
            /* 检查数据 */
            if (!$this->_handle_post_data($_POST, $id))
            {
                $error = $this->get_error();
				$error = current($error);
                $this->json_error($error['msg']);
                return;
            }
			//  立即更新
			$cache_server =& cache_server();
        	$cache_server->clear();
			
			$this->json_result(array('ret_url' => url("app=seller_limitbuy")), 'edit_limitbuy_ok');
        }
    }
	function drop()
    {
        $id = empty($_GET['id']) ? 0 : $_GET['id'];
        if (!$id)
        {
            $this->json_error('no_such_limitbuy');
            return false;
        }
        if (!$this->_limitbuy_mod->drop($id))
        {
			$error = $this->_limitbuy_mod->get_error();
			$error = current($error);
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result('','drop_limitbuy_successed');
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
            //$post['end_time'] = gmstr2time_end($post['end_time']) -1;
			$post['end_time'] =  gmstr2time($post['end_time']);// 前台时间允许到分，可以使用gmstr2time
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
				if($post['pro_type'][$val] == 'price' && ($post['pro_price'][$val] >= $post['price'][$val] || $post['pro_price'][$val] == 0)) {
					$this->_error('invalid_pro_price_price');
                	return false;
					break;
				}
			}
			
            $spec_price[$val] = array('price' => $post['pro_price'][$val], 'pro_type' => $post['pro_type'][$val]);
        }
        $data = array(
            'pro_name' 		=> $post['pro_name'],
            'pro_desc' 		=> $post['pro_desc'],
            'start_time' 	=> $post['start_time'],
            'end_time'  	=> $post['end_time'],
            'goods_id'   	=> $post['goods_id'],
            'spec_price' 	=> serialize($spec_price),
            'store_id'   	=> $this->_store_id,
        );
		if($post['image']) {
			$data['image'] = $post['image'];
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

        return true;
    }
	function query_goods_info()
    {
		$result = array();
        $goods_id = intval($_GET['goods_id']);
        if ($goods_id)
        {
			$goods = $this->_goods_mod->findAll(array(
				'conditions'=>'goods_id='.$goods_id,
				'fields'	=>'goods_id,goods_name,default_image,price,spec_name_1,spec_name_2,default_spec,spec_qty',
				'include' 	=> array('has_goodsspec'),
			));
			if($goods)
			{
				$goods = current($goods);
				$goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
				$goods['gs'] = array_values($goods['gs']);
				
				// 编辑促销的情况
				$limitbuy = $this->_limitbuy_mod->get(array('conditions' => 'goods_id='.$goods_id, 'fields' => 'spec_price'));
				if($limitbuy) {
					$limitbuy['spec_price'] = unserialize($limitbuy['spec_price']);
				}
				
				foreach($goods['gs'] as $k => $v) {
					if($v['spec_1'] == '' && $v['spec_2'] == '') {
						$goods['gs'][$k]['spec_1'] = Lang::get('default_spec');
					}
					if($limitbuy && isset($limitbuy['spec_price'][$v['spec_id']])) {
						$goods['gs'][$k]['pro_price'] += $limitbuy['spec_price'][$v['spec_id']]['price'];
						$goods['gs'][$k]['pro_type'] = $limitbuy['spec_price'][$v['spec_id']]['pro_type'];
					} else $goods['gs'][$k]['pro_price'] = '';
				}
			}
		}
		$this->json_result($goods);
    }
	
	/* 上传活动图片 */
	function upload()
    {
        import('uploader.lib');
        $file = $_FILES['image'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            $image = $uploader->save('data/files/store_'.$this->_store_id.'/limitbuy', $uploader->random_filename());
			
			if($image)
			{
				// 图片压缩处理（如：手机拍照上传图片）
				if($file['size'] >= 1024 * 1024) // 1M才压缩
				{
					import('image.func');
					$thumbnail = dirname($image) . '/' . basename($image);
					make_thumb(ROOT_PATH . '/' . $image, ROOT_PATH .'/' . $thumbnail, 200, 200, 85);
					$image = $thumbnail;
				}
				echo json_encode($image);
				exit;
			}
        }
		else echo json_encode('');
    }
	function dropfile()
	{
		$pro_id = intval($_GET['id']);
		if($pro_id) {
			$limitbuy = $this->_limitbuy_mod->get(array('conditions' => 'pro_id='.$pro_id. ' AND store_id='.$this->_store_id, 'fields' => 'image'));
			if($limitbuy) {
				if($this->_limitbuy_mod->edit('pro_id='.$pro_id.' AND store_id='.$this->_store_id, array('image' => ''))) {
					@unlink(ROOT_PATH . '/' . $limitbuy['image']);
					$this->json_result('', 'drop_ok');
					return;
				}
			}
		}
		$this->json_error('drop_fail');
	}
}

?>
