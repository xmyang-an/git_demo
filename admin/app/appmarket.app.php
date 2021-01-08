<?php

/**
 *    应用市场管理控制器
 *
 *    @author   MiMall
 *    @usage    none
 */
class AppmarketApp extends BackendApp
{
    var $_appmarket_mod;
	var $_uploadedfile_mod;

    function __construct()
    {
        $this->Appmarket();
    }

    function Appmarket()
    {
        parent::BackendApp();

        $this->_appmarket_mod = &m('appmarket');
		$this->_uploadedfile_mod = &m('uploadedfile');
    }
	
	function index()
    {
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('appmarket.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1 = 1';
        $param = array('title','category','sales','status');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$appmarket = $this->_appmarket_mod->find(array(
			'conditions' => $conditions,
			'order'	  => $order,
			'limit'   => $page['limit'],
			'count'   => true
		));
		$page['item_count'] = $this->_appmarket_mod->getCount();   //获取统计数据
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($appmarket as $k => $v){
			$config = unserialize($v['config']);
			empty($v['logo']) && $v['logo'] = Conf::get('default_goods_image');
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$k},'appmarket')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=appmarket&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$list['name'] = Lang::get($v['appid']);
			$list['logo'] = '<img src="'.dirname(site_url()) . '/' . $v['logo'].'" height="25" />';
			$list['title'] = $v['title'];
			$list['category'] = $v['category'] == 1 ? Lang::get('promotool') : '';
			$list['charge'] = $config['charge'].'元/月';
			$list['period'] = empty($config['period'])?'':$this->_get_period($config['period']);
			$list['sales'] = $v['sales'];
			$list['status'] = $v['status'] == 0 ? '<em class="no"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ><i class="fa fa-check-circle"></i>是</em>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function _get_period($period)
	{
		$data = "";
		$title = "";
		$periodlist = $this->getPeriodList();
		foreach($periodlist as $key => $val)
		{
			if(in_array($val['key'], $period))
			{
				$title .= $val['value'].',';
				$data .= "<label style='display:inline-block;width:50px;'><input style='vertical-align:middle' type='checkbox' disabled='disabled' value='{$val}' checked='checked' />".$val['value']."</label>&nbsp;&nbsp";
			}
		}
		$result = "<span title='{$title}'>".$data."</span>";
		return $result;
	}

    function add()
    {
        if (!IS_POST)
        {
			/* 应用模型未分配的附件 */
            $files_belong_appmarket = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_APPMARKET . ' AND item_id = 0',
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));
			
			$this->assign('files_belong_appmarket', $files_belong_appmarket);
            $this->assign("id", 0);
            $this->assign('belong', BELONG_APPMARKET);

            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
			
			$template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('belong' => BELONG_APPMARKET, 'item_id' => 0))); // 构建WebUploader上传组件

			$this->assign('applist', $this->getAppList());
			$this->assign('period', $this->getPeriodList());
            $this->display('appmarket.form.html');
        }
        else
        {
			if($this->checkPostData() === TRUE){
				$_POST['config']['charge'] = $this->_filter_price($_POST['config']['charge']);
			}
						
            $data = array(
				'appid'			=> trim($_POST['appid']),
				'category'		=> intval($_POST['category']),
                'title' 		=> addslashes(trim($_POST['title'])),
				'summary'		=> addslashes($_POST['summary']),
				'config'		=> serialize($_POST['config']),
				'description'	=> html_script($_POST['description']),
				'status'		=> intval($_POST['status'])
            );
			
			/* 处理上传的图片 */
            if (!empty($_FILES['logo']))
            {
				$logo = $this->_upload_logo(trim($_POST['appid']));
            	$logo && $data['logo'] = $logo;
			}

            $aid = $this->_appmarket_mod->add($data);
			if($this->_appmarket_mod->has_error())
            {
				$error = current($this->_appmarket_mod->get_error());
            	$this->json_error($error['msg']);
                return;
            }
			
			/* 附件入库 */
            if (isset($_POST['file_id']))
            {
                foreach ($_POST['file_id'] as $file_id)
                {
                    $this->_uploadedfile_mod->edit($file_id, array('item_id' => $aid));
                }
            }
			
			$this->json_result('','add_ok');
        }
    }

    function edit()
    {
        $aid = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$aid)
        {
            $this->show_warning('no_such_app');
            return;
        }
		
        $appmarket = $this->_appmarket_mod->get($aid);
        if (!$appmarket)
        {
            $this->show_warning('no_such_app');
            return;
        }
		
		$appmarket['config'] = unserialize($appmarket['config']);
		
        if (!IS_POST)
        {
            /* 应用模型的附件 */
            $files_belong_appmarket = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_APPMARKET . ' AND item_id = ' . $aid,
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));
			
			$this->assign('files_belong_appmarket', $files_belong_appmarket);
            $this->assign("id", $aid);
            $this->assign('belong', BELONG_APPMARKET);

            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
			
			$template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('belong' => BELONG_APPMARKET, 'item_id' => $aid))); // 构建WebUploader上传组件

            if ($appmarket['logo']) {
                $appmarket['logo']  =   dirname(site_url()) . "/" . $appmarket['logo'];
            }
			
			$this->assign('applist', $this->getAppList());
			$this->assign('period', $this->getPeriodList($aid));
			$this->assign('appmarket', $appmarket);
            $this->display('appmarket.form.html');
        }
        else
        {
			if($this->checkPostData($aid) === TRUE){
				$_POST['config']['charge'] = $this->_filter_price($_POST['config']['charge']);
			}
			
            $data = array(
				//'appid'			=> trim($_POST['appid']), // 编辑状态下不允许修改应用名称
				'category'		=> intval($_POST['category']),
                'title' 		=> addslashes(trim($_POST['title'])),
				'summary'		=> addslashes($_POST['summary']),
				'config'		=> serialize($_POST['config']),
				'description'	=> html_script($_POST['description']),
				'status'		=> intval($_POST['status'])
            );
			if (!empty($_FILES['logo']))
            {
				$logo = $this->_upload_logo($aid);
            	$logo && $data['logo'] = $logo;
			}

            if (!$this->_appmarket_mod->edit($aid, $data) && $this->_appmarket_mod->has_error())
            {
                $error = current($this->_appmarket_mod->get_error());
            	$this->json_error($error['msg']);
                return;
            }
			
			$this->json_result('','edit_ok');
		}
    }

    function drop()
    {
        $aids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$aids)
        {
            $this->json_error('no_such_app');

            return;
        }
        $aids = explode(',', $aids);//获取一个类似array(1, 2, 3)的数组
		
		$promotool_setting_mod 	= &m('promotool_setting');
		$promotool_item_mod 	= &m('promotool_item');
		$apprenewal_mod			= &m('apprenewal');
		
		$dropfail = array();
		foreach($aids as $aid)
		{
			$aid = intval($aid);
			$appmarket = $this->_appmarket_mod->get(array('conditions' => 'aid='.$aid, 'fields' => 'appid'));
			
			$apprenewal = $apprenewal_mod->get(array('conditions' => "appid='{$appmarket['appid']}'", 'fields' => 'expired', 'order' => 'expired DESC'));
			if($apprenewal && ($apprenewal['expired'] > gmtime()))
			{
				$dropfail[] = $appmarket['appid'];
				continue;
			}
			
			/* 删掉应用表 */
			$this->_appmarket_mod->drop($aid);
			
			/* 删掉应用设置表 */
			$promotool_setting_mod->drop('appid="'.$appmarket['appid'].'"');
			
			/* 删除商品应用对应表 */
			$promotool_item_mod->drop('appid="'.$appmarket['appid'].'"');
		}
		if($dropfail)
		{
			$alldropfail = '';
			foreach($dropfail as $appid)
			{
				$alldropfail .= ' ['.Lang::get($appid).'] ';
			}
			$this->json_error(sprintf(Lang::get('you_has_app_drop_fail'), $alldropfail));
		}
		else
		{
        	$this->json_result('drop_ok');
		}
		$this->_clear_cache();
    }
	
	function checkPostData($aid = 0)
	{
		$post = $_POST['config'];
		
		if(!$post['period'] || count($post['period']) < 1)
		{
			$this->show_warning('select_period');
			exit;
		}
		
		$appid = trim($_POST['appid']);
		if($appid)
		{
			if($aid)
			{
				$appmarket = $this->_appmarket_mod->get($aid);
				if($appmarket['appid'] != $appid) {
					$this->show_warning('inEdit_NOT_modify_appid');
					exit;
				}
	
			}
			else
			{
				if($this->_appmarket_mod->get(array('conditions' => 'appid="'.$appid.'"'))){
					$this->show_warning('appid_existed');
					exit;
				}
			}
		}
		
		return TRUE;
	}

    /*
     *    处理上传标志
     */
    function _upload_logo($appid)
    {
        $file = $_FILES['logo'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
			return false;
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['logo']);//上传logo
        if (!$uploader->file_info())
        {
			return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
		$image = $uploader->save('data/files/mall/appmarket', $appid);
		if($image)
		{
			import('image.func');
			$thumbnail = dirname($image) . '/' . basename($image);
			make_thumb(ROOT_PATH . '/' . $image, ROOT_PATH .'/' . $thumbnail, 300, 200, 85);
			$image = $thumbnail;
			return $image;
		}
		return false;
    }
	
	function getAppList()
	{
		$applist = array(
			//array('key' => 'distribution', 'value' => Lang::get('distribution')), 
			array('key' => 'limitbuy', 'value' => Lang::get('limitbuy')),
			array('key' => 'meal', 'value' => Lang::get('meal')),
			array('key'	=> 'fullfree', 'value' => Lang::get('fullfree')),
			array('key' => 'fullprefer', 'value' => Lang::get('fullprefer')),
			array('key' => 'fullgift', 'value' => Lang::get('fullgift')),
			array('key' => 'growbuy', 'value' => Lang::get('growbuy')),
			array('key' => 'exclusive', 'value' => Lang::get('exclusive'))
		);
		return $applist;
	}
	function getPeriodList($aid = 0)
	{
		$periodlist = array();
		
		$period_inc = include(ROOT_PATH . '/data/period.inc.php');

		if($period_inc)
		{
			foreach($period_inc as $key => $val) {
				$periodlist[] = array('key' => $key, 'value' => $val);
			}
		}
		
		return $periodlist;
	}
	
	/* 异步删除附件 */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
        if ($file_id && $this->_uploadedfile_mod->drop($file_id))
        {
            $this->json_result('drop_ok');
            return;
        }
        else
        {
            $this->json_error('drop_error');
            return;
        }
    }
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }

}

?>
