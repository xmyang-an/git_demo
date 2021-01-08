<?php

class My_storeApp extends StoreadminbaseApp
{
    var $_store_id;
    var $_store_mod;
	var $_uploadedfile_mod;

    function __construct()
    {
        $this->My_storeApp();
    }
    function My_storeApp()
    {
        parent::__construct();
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_store_mod =& m('store');
		$this->_uploadedfile_mod = &m('uploadedfile');
    }

    function index()
    {
        $tmp_info = $this->_store_mod->get(array(
            'conditions' => $this->_store_id,
            'join'       => 'belongs_to_sgrade',
            'fields'     => 'domain, functions',
        ));
        $functions = $tmp_info['functions'] ? explode(',', $tmp_info['functions']) : array();
        $subdomain_enable = false;
        if (ENABLED_SUBDOMAIN && in_array('subdomain', $functions))
        {
            $subdomain_enable = true;
        }
        if (!IS_POST)
        {
            $store = $this->_store_mod->get_info($this->_store_id);
			
            foreach ($functions as $k => $v)
            {
                $store['functions'][$v] = $v;
            }

            $this->assign('store', $store);
			$this->assign('subdomain_enable', $subdomain_enable);
			
			$this->assign('location' , true);

			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,webuploader/webuploader.js,webuploader/webuploader.compressupload.js');

			$this->_config_seo('title', Lang::get('my_store') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_store');
            $this->display('my_store.index.html');
        }
        else
        {
			$store_name = trim($_POST['store_name']);
			$region_id  = intval($_POST['region_id']);
			$region_name= trim($_POST['region_name']);
			$address    = trim($_POST['address']);
			
			if(empty($store_name)) {
				$this->json_error('store_name_empty');
     			return;
			}
			elseif(!$this->_check_store_name($store_name)) {
				$this->json_error('name_exist');
     			return;
			}
			if(!$region_id || empty($region_name) || empty($address)) {
				$this->json_error('region_empty');
     			return;
			}
			
            $subdomain = $tmp_info['domain'];
            if ($subdomain_enable && !$tmp_info['domain'])
            {
                $subdomain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
                if (!$this->_store_mod->check_domain($subdomain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
                {
					$error = current($this->_store_mod->get_error());
                    $this->json_error($error['msg']);
                    return;
                }
            }
			
            $data = array(
                'store_name' => $store_name,
                'region_id'  => $region_id,
                'region_name'=> $region_name,
                'address'    => $address,
                'tel'        => trim($_POST['tel']),
                'im_qq'      => trim($_POST['im_qq']),
                'im_ww'      => trim($_POST['im_ww']),
                'domain'     => $subdomain,
				//'business_scope'=> trim($_POST['business_scope']),
				//'description'=> $_POST['description']
            );
			
			if(isset($_POST['store_logo']) && !empty($_POST['store_logo'])) {
				$data = array_merge($data, array('store_logo' => $_POST['store_logo']));
			}
			
            $this->_store_mod->edit($this->_store_id, $data);

            $this->json_result('','edit_ok');
        }
    }
	
	function banner()
	{
		if(!IS_POST)
		{
			$store = $this->_store_mod->get(array(
				'conditions' => 'store_id='.$this->_store_id, 'fields' => 'wap_store_banner as store_banner, store_name'));
			$this->assign('store', $store);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,webuploader/webuploader.js,webuploader/webuploader.compressupload.js');
			
			/* 当前页面信息 */
			$this->_config_seo('title', Lang::get('store_banner') . ' - ' . $store['store_name']);
			$this->_get_curlocal_title('store_banner');
			$this->display('my_store.banner.html');
		}
		else
		{
			$store_banner = $_POST['store_banner'];
			if($store_banner)
			{
				$this->_store_mod->edit($this->_store_id, array('wap_store_banner' => $store_banner));
				$this->json_result('', 'upload_ok');
				return;
			}
			$this->json_error('upload_fail');
		}
	}
	
	function slides()
	{
		if(!IS_POST)
		{
			$store = $this->_store_mod->get(array(
				'conditions' => 'store_id='.$this->_store_id, 'fields' => 'wap_store_slides as store_slides, store_name'));
			if($store['store_slides']) {
				$store['store_slides'] = json_decode($store['store_slides'], true);
			}
			$this->assign('store', $store);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,webuploader/webuploader.js,webuploader/webuploader.compressupload.js');
			
			/* 当前页面信息 */
			$this->_config_seo('title', Lang::get('store_slides') . ' - ' . $store['store_name']);
			$this->_get_curlocal_title('store_slides');
			$this->display('my_store.slides.html');
		}
		else
		{
			$store_slides = array();
			$post = $_POST['store_slides'];
			foreach($post['url'] as $key => $val) {
				if(!empty($val)) {
					$store_slides[] = array('url' => $val, 'link' => $post['link'][$key]);
				}
			}
			
			if($_GET['from'] && in_array($_GET['from'], array('upload', 'modify'))) {
				$langpre = trim($_GET['from']);
			} else $langpre = 'save';
			
			if($store_slides)
			{
				$this->_store_mod->edit($this->_store_id, array('wap_store_slides' => json_encode($store_slides)));
				$this->_clean_file();
				$this->json_result('', $langpre.'_ok');
				return;
			}
			$this->json_error($langpre.'_fail');
		}
	}
	
	function map()
	{
		if(!IS_POST)
		{
			$store = $this->_store_mod->get(array('conditions'=>'store_id='.$this->_store_id, 'fields'=>'lat,lng,zoom,store_name'));
			$this->assign('store', $store);
			$this->assign('baidukey', Conf::get('baidukey'));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			/* 当前页面信息 */
			$this->_config_seo('title', Lang::get('store_map') . ' - ' . $store['store_name']);
			$this->_get_curlocal_title('store_map');
			$this->display('my_store.map.html');
		}
		else
		{
			if(!$this->_store_mod->edit($this->_store_id,  array('lat' => trim($_POST['lat']),'lng' => trim($_POST['lng']),'zoom' => trim($_POST['zoom'])))){
				$this->json_error('position_save_fail');
				return;
			}
			$this->json_result('', 'position_save_ok');
		}
		
	}
	
	function _check_store_name($store_name = '')
	{
		if($store_name) {
			if($this->_store_mod->get("store_name='{$store_name}' AND store_id<>{$this->_store_id}")) {
				return false;
			} else return true;
		}
		return false;
	}
	
	/**
     * 上传店铺店标、横幅
     *
     */
    function upload()
    {
        $data      = array();
		
		$key = current(array_keys($_FILES));
		$fileds = array('store_logo', 'store_banner', 'store_slides');
		
		if(in_array($key, $fileds))
		{
			$filename = $key;
			$thumbnail = array('width' => 200, 'height' => 200);
			if(!in_array($key, array('store_logo'))) {
				$filename = 'wap_'.$key;
				if($key == 'store_slides') $filename = $filename . '-' . mt_rand(); // 兼容多图
				$thumbnail = array('width' => 640, 'height' => ($key == 'store_banner') ? 150 : 250);
			}
	
			$file = $_FILES[$key];
			if ($file['error'] == UPLOAD_ERR_OK && $file !='')
			{
				import('uploader.lib');
				$uploader = new Uploader();
				$uploader->allowed_type(IMAGE_FILE_TYPE);
				$uploader->addFile($file);
				$uploader->root_dir(ROOT_PATH);
				
				$image = $uploader->save('data/files/store_' . $this->_store_id . '/other', $filename);
				if($image)
				{
					// 图片压缩处理（如：手机拍照上传图片）
					if($file['size'] >= 1024 * 1024) // 1M才压缩
					{
						import('image.func');
						$thumbnail = dirname($image) . '/' . basename($image);
						make_thumb(ROOT_PATH . '/' . $image, ROOT_PATH .'/' . $thumbnail, $thumbnail['width'], $thumbnail['height'], 85);
						$image = $thumbnail;
					}
					echo json_encode($image);
					exit;
				} 
			}
		}
		else echo json_encode('');
    }
	
	// 清除多余图片
	function _clean_file()
	{
		$store = $this->_store_mod->get(array('conditions' => 'store_id='.$this->_store_id, 'fields' => 'store_logo, store_banner, wap_store_banner, store_slides, wap_store_slides'));
		unset($store['store_id']);
		
		$allow = array();
		foreach($store as $key => $val)
		{
			if(in_array($key, array('store_slides', 'wap_store_slides'))) {
				if(!empty($val)) $val = json_decode($val, true);
				if(is_array($val)) {
					foreach($val as $k => $v) {
						$allow[] = $v['url'];
					}
				}
			}
			elseif(!empty($val))
			{
				$allow[] = $val;
			}
		}
		$files = $this->_get_exist_file('data/files/store_'.$this->_store_id.'/other');
		
		foreach($files as $file)
		{
			if(!in_array($file, $allow)) {
				@unlink(ROOT_PATH . '/' . $file);
			}
		}
	}
}

?>
