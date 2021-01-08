<?php

/**
 *    满赠管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_fullgiftApp extends StoreadminbaseApp
{
	var $_appid;
    var $_store_id;
	var $_gift_mod;
    var $_fullgift_mod;
	var $_appmarket_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_fullgiftApp();
    }

    function Seller_fullgiftApp()
    {
        parent::__construct();
		
		$this->_appid = 'fullgift';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_fullgift_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		$this->_gift_mod = &bm('gift', array('_store_id' => $this->_store_id));
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
		
			$this->_config_seo('title', Lang::get('fullgift'). ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift');
			$this->display('seller_fullgift.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
		
			$fullgiftList = $this->_fullgift_mod->get_list(array(
				'conditions' 	=> 'store_id = ' . $this->_store_id . ' AND appid = "' .$this->_appid . '"',
				'limit' 		=> $page['limit'],
				'count' 		=> true,	
				'order'			=> 'psid DESC'
			));
			$page['item_count'] = $this->_fullgift_mod->getCount();
			$this->_format_page($page);
			$this->assign('page_info', $page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($fullgiftList), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}

    function add()
    {		
		if(!IS_POST)
		{	
			$this->assign('store_id', $this->_store_id);
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
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
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
			));
				
			$this->_config_seo('title', Lang::get('fullgift_add') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift_add');
			$this->display('seller_fullgift.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 			= $_POST['fullgift'];
			$status			= intval($_POST['status']);
			$post['amount'] 		= $this->_filter_price(trim($post['amount']));
			$post['items'] 			= html_script($post['selected_ids']);
			unset($post['selected_ids']);
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status,
				'add_time'	=> gmtime()
			);
			
			if(!$this->_fullgift_mod->add($data) && $this->_fullgift_mod->has_error()) {
				$error = current($this->_fullgift_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$this->json_result(array('ret_url' => url('app=seller_fullgift')), 'add_ok');
		}
    }
	function edit()
    {
		$id = intval($_GET['id']);
		
		if(!IS_POST)
		{
			if(!$id) 
			{
				$this->show_warning('no_such_data');
				return;
			}
		
			$fullgift = $this->_fullgift_mod->get_info($id);
			$this->assign('fullgift', $fullgift);
			$this->assign('store_id', $this->_store_id);

			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
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
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
			));
				
			$this->_config_seo('title', Lang::get('fullgift_edit') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift_edit');
			$this->display('seller_fullgift.form.html');
		}
		else
		{
			if(!$id) 
			{
				$this->show_warning('no_such_data');
				return;
			}
			
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 			= $_POST['fullgift'];
			$status			= intval($_POST['status']);
			
			$post['amount'] 		= $this->_filter_price(trim($post['amount']));
			$post['items'] 		= html_script($post['selected_ids']);
			unset($post['selected_ids']);
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status
			);
			
			if(!$this->_fullgift_mod->edit($id, $data) && $this->_fullgift_mod->has_error()) {
				$error = current($this->_fullgift_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$ret_page = intval($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			
			$this->json_result(array('ret_url' => url('app=seller_fullgift')), 'edit_ok');	
		}
    }
	
	function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $rows = $this->_fullgift_mod->drop($ids);
        if ($this->_fullgift_mod->has_error())
        {
			$error = current($this->_fullgift_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result('', 'drop_ok');
    }
	
	function itemlist()
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
			
			$this->_config_seo('title', Lang::get('fullgift') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift');
			$this->display('seller_fullgift.itemlist.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$itemlist = $this->_gift_mod->find(array(
				'conditions' 	=> 'store_id=' . $this->_store_id . $conditions,
				'order'			=> 'goods_id DESC',
				'limit' 		=> $page['limit'],
				'count' 		=> true,		
			));
			$page['item_count'] = $this->_gift_mod->getCount();
			$this->_format_page($page);
			$this->assign('page_info', $page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($itemlist), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	function itemadd()
	{
		if(!IS_POST)
		{
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
						'path' => 'webuploader/webuploader.js',
						'attr' => ''
					),
					array(
						'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => ''
					),

				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			
			$this->_config_seo('title', Lang::get('fullgift_itemadd') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift_itemadd');
			$this->display('seller_fullgift.item.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$data = array();
			if($this->checkPostData(0) === TRUE) {
				$data['price'] = $this->_filter_price($_POST['price']);
			}

			$data['goods_name']       = trim($_POST['goods_name']);
			$data['store_id']    = $this->_store_id;
			$data['description'] = html_script($_POST['description']);
			$data['if_show']     = intval($_POST['if_show']);
			$data['default_image']  = trim($_POST['default_image']) ? trim($_POST['default_image']) : Conf::get('default_goods_image');
			
			if(!$goods_id = $this->_gift_mod->add($data)) {
				$error = current($this->_gift_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$this->json_result(array('ret_url' =>url("app=seller_fullgift&act=itemlist")), 'add_ok');
		}
	}
	
	function itemedit()
	{
		$id = intval($_GET['id']);
		$goods = $this->_gift_mod->get('store_id=' . $this->_store_id . ' AND goods_id='.$id);
	
		if(!IS_POST)
		{
			if(!$goods) {
				$this->show_warning('no_such_goods');
				return;
			}
			$this->assign('goods', $goods);
			
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
						'path' => 'webuploader/webuploader.js',
						'attr' => ''
					),
					array(
						'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => ''
					),

				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			
			$this->_config_seo('title', Lang::get('fullgift_itemedit') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullgift_itemedit');
			$this->display('seller_fullgift.item.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			if(!$goods) {
				$this->json_error('no_such_goods');
				return;
			}
			
			$data = array();
			if($this->checkPostData(0) === TRUE) {
				$data['price'] = $this->_filter_price($_POST['price']);
			}
			
			$data['goods_name']  = trim($_POST['goods_name']);
			$data['store_id']    = $this->_store_id;
			$data['description'] = html_script($_POST['description']);
			$data['if_show']     = intval($_POST['if_show']);
			
			if($default_image = trim($_POST['default_image'])) {
				$data['default_image']  = $default_image;
			}

			if(!$this->_gift_mod->edit($id, $data) && $this->_gift_mod->get_error()) {
				$error = current($this->_gift_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$this->json_result(array('ret_url' => url("app=seller_fullgift&act=itemlist")), 'edit_ok');
		}
	}
	
	function itemdrop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $this->_gift_mod->drop_data($ids, $this->_store_id);
        $rows = $this->_gift_mod->drop('goods_id'.db_create_in($ids). ' AND store_id='.$this->_store_id);
        if (!$rows)
        {
            $this->json_error('drop_fail');
            return;
        }

        $this->json_result('', 'drop_ok');
    }
	
	function checkPostData($id = 0)
	{
		if(!isset($_POST['goods_name']) || empty($_POST['goods_name'])) {
			$this->json_error('title_empty');
			exit;
		}
		if(strlen($_POST['goods_name']) > 150) {
			$this->json_error('title_len_valid');
			exit;
		}
		if(!isset($_POST['price']) || !is_numeric($_POST['price']) || floatval($_POST['price']) < 0) {
			$this->json_error('price_ge_0');
			exit;
		}
		return TRUE;
	}

	function query_goods_info()
    {
        $goods_ids = empty($_GET['goods_id']) ? 0 : trim($_GET['goods_id']);
		$id   = intval($_GET['psid']);
        if ($goods_ids || $id)
        {
			// 可选择多个赠品
			$ids = array();
			if($goods_ids) $ids = array_unique(explode(',', $goods_ids));
			elseif($id) 
			{
				$fullgift = $this->_fullgift_mod->get_info($id);
				$ids = isset($fullgift['rules']['items']) ? $fullgift['rules']['items'] : array();
			} 
			
			$goods_list = $this->_gift_mod->findAll(array(
                'conditions' => "goods_id" . db_create_in($ids). ' AND store_id='.$this->_store_id,
				'fields' => "goods_name,goods_id,price,default_image",
            ));
			foreach($goods_list as $key => $goods)
			{
				$goods_list[$key]['goods_name'] = htmlspecialchars($goods['goods_name']); // json need
				$goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
			}
            $this->json_result(array('goods_list'=>$goods_list));
        }
    }
	
	/* 上传主图 */
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
            $image = $uploader->save('data/files/store_'.$this->_store_id.'/gift', $uploader->random_filename());
			
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
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>