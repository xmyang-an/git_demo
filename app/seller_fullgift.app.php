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
	var $_uploadedfile_mod;
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
		$this->_uploadedfile_mod =& m('uploadedfile');
		$this->_gift_mod = &bm('gift', array('_store_id' => $this->_store_id));
		$this->_appmarket_mod = &m('appmarket');
    }
	
	function index()
	{
		$page = $this->_get_page(10);
		
		$fullgiftList = $this->_fullgift_mod->get_list(array(
			'conditions' 	=> 'store_id = ' . $this->_store_id . ' AND appid = "' .$this->_appid . '"',
			'limit' 		=> $page['limit'],
			'count' 		=> true,	
		));
		$page['item_count'] = $this->_fullgift_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info', $page);
		
		$this->assign('fullgift_list', $fullgiftList);
		
		// 用于提示是否添加赠品
		$itemlist = $this->_gift_mod->find(array('conditions' => 'if_show=1', 'fields' => 'goods_id', 'limit' =>1));
		$this->assign('hasGift', $itemlist);
				
		/* 当前位置 */
		$this->_curlocal(LANG::get('fullgift'), 'index.php?app=seller_fullgift',
								LANG::get('fullgift_list'));
		
		/* 当前用户中心菜单 */
		$this->_curitem('fullgift');;
		
		/* 当前所处子菜单 */
		$this->_curmenu('fullgift_list');
			
		$this->import_resource('seller_fullgift.js');	
				
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_list'));
		$this->assign('store_id', $this->_store_id);
		$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		$this->display('seller_fullgift.index.html');
		
	}

    function add()
    {		
		if(!IS_POST)
		{
			// 用于提示是否添加赠品
			$itemlist = $this->_gift_mod->find(array('conditions' => 'if_show=1', 'fields' => 'goods_id', 'limit' =>1));
			$this->assign('hasGift', $itemlist);
				
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullgift'), 	'index.php?app=seller_fullgift',
								LANG::get('fullgift_add'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('fullgift');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('fullgift_add');
			
			$this->_import_resource();
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_add'));
			$this->assign('store_id', $this->_store_id);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_fullgift.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
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
				$this->show_warning($this->_fullgift_mod->get_error());
				return;
			}
			$this->show_message('add_ok', '', url('app=seller_fullgift'));
			
		}
    }
	function edit()
    {
		$id = intval($_GET['id']);
		if(!$id) {
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		if(!IS_POST)
		{
			$fullgift = $this->_fullgift_mod->get_info($id);
			
			$itemList = array();
			$items = (isset($fullgift['rules']['items']) && is_array($fullgift['rules']['items'])) ? $fullgift['rules']['items'] : array();
			foreach($items as $id)
			{
				if($gift = $this->_gift_mod->get(array('conditions' => 'goods_id='.$id.' AND store_id='.$this->_store_id.' AND if_show=1', 'fields' => 'goods_id, goods_name, price, default_image, store_id'))){
					$itemList[$id] = $gift;
				}
			}
			$fullgift['rules']['items'] = $itemList;
			
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullgift'), 	'index.php?app=seller_fullgift',
								LANG::get('fullgift_edit'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('fullgift');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('fullgift_edit');
			
			$this->_import_resource();
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_edit'));
			$this->assign('store_id', $this->_store_id);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('fullgift', $fullgift);
			$this->display('seller_fullgift.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
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
				$this->show_warning($this->_fullgift_mod->get_error());
				return;
			}
			$ret_page = intval($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			$this->show_message('edit_ok', '', url('app=seller_fullgift&page='.$ret_page));			
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
		$conditions = '';
		if(!empty($_GET['goods_name'])) 
		{
			$conditions .= " AND goods_name LIKE '%" . html_script($_GET['goods_name']) . "%'";
		}
		
		//更新排序
        if (isset($_GET['sort']) && isset($_GET['order']) && in_array(trim($_GET['sort']), array('price', 'if_show')))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'goods_id';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }
		
		$page = $this->_get_page(20);
		$itemlist = $this->_gift_mod->find(array(
			'conditions' 	=> 'store_id=' . $this->_store_id . $conditions,
			'limit' 		=> $page['limit'],
			'order' 		=> "$sort $order",
			'count' 		=> true,		
		));
		$page['item_count'] = $this->_gift_mod->getCount();

        $this->_format_page($page);
        $this->assign('page_info', $page);
		$this->assign('goods_list', $itemlist);
		$this->assign('filtered', $conditions ? 1 : 0);
		
		/* 当前页面信息 */
        $this->_curlocal(LANG::get('fullgift'), 'index.php?app=seller_fullgift',
                         LANG::get('fullgift_itemlist'));
        $this->_curitem('fullgift');
        $this->_curmenu('fullgift_itemlist');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_itemlist'));
		$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		$this->display('seller_fullgift.itemlist.html');
	}
	
	function itemadd()
	{
		if(!IS_POST)
		{
			/* 添加传给iframe空的id,belong*/
             $this->assign("id", 0);
             $this->assign("belong", BELONG_GIFT);

             /* 取得游离状的图片（描述） */
             $desc_images =array();
             $uploadfiles = $this->_uploadedfile_mod->find(array(
                 'conditions' => "belong=".BELONG_GIFT." AND item_id=0 AND store_id=".$this->_store_id,
                 'order' => 'add_time ASC'
             ));

             $this->assign('desc_images', $uploadfiles);
			 

             /* 编辑器图片批量上传器 */
             $this->assign('editor_upload', $this->_build_upload(array(
                 'obj' => 'EDITOR_SWFU',
                 'belong' => BELONG_GIFT,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'button_id' => 'editor_upload_button',
                 'progress_id' => 'editor_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=desc_image',
                 'if_multirow' => 1,
             )));
			 
			 
             /* 所见即所得编辑器 */
             extract($this->_get_theme());
             $this->assign('build_editor', $this->_build_editor(array(
                 'name' => 'description',
                 'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
             )));
			 
			 $this->import_resource('jquery.plugins/jquery.validate.js');			 
			 
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullgift'), 	'index.php?app=seller_fullgift',
								LANG::get('fullgift_itemadd'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('fullgift');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('fullgift_itemadd');
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_itemadd'));
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_fullgift.item.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
			$data = array();
			if($this->checkPostData(0) === TRUE) {
				$data['price'] = $this->_filter_price($_POST['price']);
			}
			if($upload = $this->_upload_image()) {
				$data = array_merge($data, $upload);
			} else $data['default_image'] = Conf::get('default_goods_image');
			$data['goods_name']       = trim($_POST['goods_name']);
			$data['store_id']    = $this->_store_id;
			$data['description'] = html_script($_POST['description']);
			$data['if_show']     = intval($_POST['if_show']);
			
			
			if(!$goods_id = $this->_gift_mod->add($data)) {
				$this->show_warning($this->_gift_mod->has_error() ? $this->_gift_mod->get_error() : 'add_fail');
				return;
			}
			
			if ($_POST['desc_file_id'])
            {
                $uploadfiles = $_POST['desc_file_id'];
                $this->_uploadedfile_mod->edit(db_create_in($uploadfiles, 'file_id'), array('item_id' => $goods_id));
            }
			
			$this->show_message('add_ok', '', url('app=seller_fullgift&act=itemlist'));
		}
	}
	
	function itemedit()
	{
		$id = intval($_GET['id']);
		
		$goods = $this->_gift_mod->get('store_id=' . $this->visitor->get('manage_store') . ' AND goods_id='.$id);
		if(!$goods) {
			$this->show_warning('no_such_goods');
			return;
		}
		
		if(!IS_POST)
		{
			/* 添加传给iframe空的id,belong*/
             $this->assign("id", 0);
             $this->assign("belong", BELONG_GIFT);

             /* 取得图片（描述） */
             $desc_images =array();
             $uploadfiles = $this->_uploadedfile_mod->find(array(
                 'conditions' => "belong=".BELONG_GIFT." AND item_id=".$id." AND store_id=".$this->_store_id,
                 'order' => 'add_time ASC'
             ));

             $this->assign('desc_images', $uploadfiles);
			 

             /* 编辑器图片批量上传器 */
             $this->assign('editor_upload', $this->_build_upload(array(
                 'obj' => 'EDITOR_SWFU',
                 'belong' => BELONG_GIFT,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'button_id' => 'editor_upload_button',
                 'progress_id' => 'editor_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=desc_image',
                 'if_multirow' => 1,
             )));
			 
			 
             /* 所见即所得编辑器 */
             extract($this->_get_theme());
             $this->assign('build_editor', $this->_build_editor(array(
                 'name' => 'description',
                 'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
             )));
			 
			 $this->import_resource('jquery.plugins/jquery.validate.js');			 
			 
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullgift'), 	'index.php?app=seller_fullgift',
								LANG::get('fullgift_itemedit'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('fullgift');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('fullgift_itemedit');
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullgift') . ' - ' . Lang::get('fullgift_itemedit'));
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('goods', $goods);
			$this->display('seller_fullgift.item.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
			$data = array();
			if($this->checkPostData(0) === TRUE) {
				$data['price'] = $this->_filter_price($_POST['price']);
			}
			if($upload = $this->_upload_image()) {
				$data = array_merge($data, $upload);
			}
			$data['goods_name']  = trim($_POST['goods_name']);
			$data['store_id']    = $this->_store_id;
			$data['description'] = html_script($_POST['description']);
			$data['if_show']     = intval($_POST['if_show']);
			
			if(!$this->_gift_mod->edit($id, $data) && $this->_gift_mod->has_error()) {
				$this->show_warning($this->_gift_mod->get_error());
				return;
			}
			
			$ret_page = intval($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			$this->show_message('edit_ok', '', url("app=seller_fullgift&act=itemlist&page=$ret_page"));
		}
	}
	
	function itemdrop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $this->_gift_mod->drop_data($ids, $this->_store_id);
        $rows = $this->_gift_mod->drop('goods_id'.db_create_in($ids). ' AND store_id='.$this->_store_id);
        if (!$rows)
        {
            $this->show_warning('drop_fail');
            return;
        }

        $this->show_message('drop_ok');
    }
	
	function checkPostData($id = 0)
	{
		if(!isset($_POST['goods_name']) || empty($_POST['goods_name'])) {
			$this->show_warning('title_empty');
			exit;
		}
		if(strlen($_POST['goods_name']) > 150) {
			$this->show_warning('title_len_valid');
			exit;
		}
		if(!isset($_POST['price']) || !is_numeric($_POST['price']) || floatval($_POST['price']) < 0) {
			$this->show_warning('price_ge_0');
			exit;
		}
		return TRUE;
	}
	
	function gselector()
	{
        /* 搜索条件 */
        $conditions = " 1 = 1 ";
        if (trim($_GET['goods_name'])){
            $str = "LIKE '%" . trim($_GET['goods_name']) . "%'";
            $conditions .= " AND (goods_name {$str})";
        }
		$page   =   $this->_get_page(5);    //获取分页信息

        /* 取得赠品列表 */
        $itemlist = $this->_gift_mod->find(array(
            'conditions' 	=> 'store_id='.$this->_store_id . ' AND if_show=1 AND ' .$conditions,
            'order' 		=> 'add_time DESC',
            'limit' 		=> $page['limit'],
			'count' 		=> true
        ));
		
		$page['item_count'] =$this->_gift_mod->getCount();
		$this->_format_page($page);

        foreach ($itemlist as $key => $val)
        {
            $itemlist[$key]['goods_name'] = htmlspecialchars($val['goods_name']);
        }
        $this->json_result(array('goods_list' => $itemlist, 'page_info' => $page));
	}
	
	function query_goods_info()
    {
        $goods_ids = empty($_GET['goods_id']) ? 0 : trim($_GET['goods_id']);
        if ($goods_ids)
        {
			$ids = array_unique(explode(',', $goods_ids));
			
			$itemlist = $this->_gift_mod->findAll(array(
                'conditions' 	=> "goods_id" . db_create_in($ids) . ' AND store_id='.$this->_store_id,
				'fields' 		=> "goods_name,goods_id,price,default_image",
            ));
			foreach($itemlist as $key=>$goods)
			{
				$itemlist[$key]['goods_name'] = htmlspecialchars($goods['goods_name']); // json need
				$goods['default_image'] || $itemlist[$key]['default_image'] = Conf::get('default_goods_image');
			}
            $this->json_result(array('goods_list' => $itemlist));
        }
    }
	
	
	/* 上传主图 */
	function _upload_image()
    {
        import('uploader.lib');
        $data      = array();
		
        $file = $_FILES['goods_image'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            //$uploader->allowed_size(SIZE_STORE_LOGO); // 20KB
            $uploader->addFile($file);
            if ($uploader->file_info() === false)
            {
                $this->show_warning($uploader->get_error());
                exit;
            }
            $uploader->root_dir(ROOT_PATH);
			$data['default_image'] = $uploader->save('data/files/store_' . $this->_store_id . '/gift', $uploader->random_filename());
        }
		
        return $data;
    }
	
	function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $uploadedfile = $this->_uploadedfile_mod->get(array(
                  'conditions' => "f.file_id = '$id' AND f.store_id = '{$this->_store_id}'",
                  'fields' => 'file_path',
        ));
        if ($uploadedfile)
        {
            $this->_uploadedfile_mod->drop($id);
	
			// 删除文件
			if (file_exists(ROOT_PATH . '/' . $uploadedfile['file_path']))
			{
				@unlink(ROOT_PATH . '/' . $uploadedfile['file_path']);
			}
                
   
            $this->json_result($id);
            return;
        }
        $this->json_error(Lang::get('no_image_droped'));
    }
	
	/**
     *    三级菜单
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
			array(
                'name'  => 'fullgift_list',
                'url'   => 'index.php?app=seller_fullgift',
            ),
            array(
                'name'  => 'fullgift_add',
                'url'   => 'index.php?app=seller_fullgift&act=add',
            )
        );
		if(ACT == 'edit')
		{
			$menus[] = array(
				'name'  => 'fullgift_edit',
				'url'   => '',
			);
		}
		$menus = array_merge($menus, array(
			array(
				'name'  => 'fullgift_itemlist',
				'url'   => 'index.php?app=seller_fullgift&act=itemlist',
			),
			array(
                'name'  => 'fullgift_itemadd',
                'url'   => 'index.php?app=seller_fullgift&act=itemadd',
            ),
		));
		
		if(ACT == 'itemedit')
		{
			$menus[] = array(
				'name'  => 'fullgift_itemedit',
				'url'   => '',
			);
		}
		
        return $menus;
    }
	function _import_resource()
    {	
        $this->import_resource(array(
			'script' => array(
                    array(
                    	'path' => 'dialog/dialog.js',
                    	'attr' => 'id="dialog_js"',
               	 	),
                	array(
                    	'path' => 'jquery.ui/jquery.ui.js',
                    	'attr' => '',
                	),
                	array(
                    	'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    	'attr' => '',
                	),
                	array(
                    	'path' => 'jquery.plugins/jquery.validate.js',
                    	'attr' => '',
                	),
					array(
						'path' => 'seller_fullgift.js',
						'attr' => 'charset="utf-8"',
					)
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
		));
    }
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>