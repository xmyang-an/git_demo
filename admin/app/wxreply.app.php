<?php

// 关键词回复

class WxreplyApp extends BackendApp
{
	var $weixin_menu_mod;
	var $weixin_reply_mod;

    function __construct()
    {
        $this->WxreplyApp();
    }

    function WxreplyApp()
    {
        parent::__construct();
		$this->weixin_menu_mod = & m('weixin_menu');
		$this->weixin_reply_mod = & m('weixin_reply');
    }
	
	function index()
    {
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('wxreply.index.html');
    }
	
	function get_xml()
	{
		$order = 'action,reply_id';
        $param = array('reply_id','type','action');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $replys = $this->weixin_reply_mod->find(array(
        	'conditions'    => "user_id = 0 AND action ".db_create_in(array('beadded','autoreply','smartreply')),
			'limit'   => $page['limit'],
			'order'   => $order,
			'count'   => true 
        )); 
        $page['item_count'] = $this->weixin_reply_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($replys as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['reply_id']},'wxreply')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=wxreply&act=edit&id={$v['reply_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$list['reply_id']  = $v['reply_id'];
			$list['action']    = Lang::get("{$v['action']}");
			$list['rule_name'] = $v['action'] == 'smartreply' ? $v['rule_name'] : '-';
			$list['keywords']  = $v['action'] == 'smartreply' ? $v['keywords'] : '-';
			$list['type'] 	   = $v['type'] ? Lang::get('imgmsg') : Lang::get('textmsg');
			$list['content']   = "<span title=\"{$v['content']}\">{$v['content']}</span>";
			$data['list'][$k]  = $list;
		}
		$this->flexigridXML($data);
	}
	
	function add()
	{
		if (!IS_POST)
        {
            $this->display('wxreply.form.html');
        }
        else
        {
			$type = isset($_POST['type']) && intval($_POST['type']) == 1 ? 1 : 0; 
			$action = trim($_POST['action']);
			if(in_array($action,array('beadded','autoreply')) && $this->weixin_reply_mod->get("user_id=0 AND action='".$action."'"))
			{
				$this->json_error($action.'_add_already');
                return;
			}
			$data = array(
                'user_id' 		=> 0,
                'type' 			=> trim($_POST['type']),
				'action'		=> $action,
				'rule_name' 	=> trim($_POST['rule_name']),
				'keywords' 		=> trim($_POST['keywords']),
				'content' 		=> $_POST['content'],
				'add_time'		=> gmtime(),
			);
			if($type)
			{
				$data += array(
					'title'		=> $_POST['title'],
					'link'		=> $_POST['link']
				);
				if(!empty($_FILES['image']))
				{
					$image = $this->_upload_image();
					$image && $data['image'] = $image;
				}
			}

			if(!$reply_id = $this->weixin_reply_mod->add($data)) 
			{
				$error = current($this->weixin_reply_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
		
            $this->json_result('','add_reply_successed');
        }
	}
	
	function edit()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$reply = $this->weixin_reply_mod->get($id);
		if(!$id || empty($reply))
		{
			$this->show_warning('no_such_reply');
			return;
		}
		
		if (!IS_POST)
        {
			$this->assign('reply',$reply);
            $this->display('wxreply.form.html');
        }
        else
        {
			$type = isset($_POST['type']) && intval($_POST['type']) == 1 ? 1 : 0; 
			$action = trim($_POST['action']);
			$data = array(
                'user_id' 		=> 0,
                'type' 			=> trim($_POST['type']),
				'action'		=> $action,
				'rule_name' 	=> trim($_POST['rule_name']),
				'keywords' 		=> trim($_POST['keywords']),
				'content' 		=> trim($_POST['content']),
				'add_time'		=> gmtime(),
			);
			if($type)
			{
				$data += array(
					'title'		=> $_POST['title'],
					'link'		=> $_POST['link']
				);
				if (!empty($_FILES['image']))
				{
					$image = $this->_upload_image();
					$image && $data['image'] = $image;
				}
			}

			$this->weixin_reply_mod->edit($id,$data);

			if($this->weixin_reply_mod->has_error()) 
			{
				$error = current($this->weixin_reply_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }

            $this->json_result('','edit_reply_successed');
        }
	}
	
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_such_reply');
            return;
        }

        $ids = explode(',', $id);
		
        if (!$this->weixin_reply_mod->drop($ids))
        {
            $error = current($this->weixin_reply_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }
        $this->json_result('','drop_ok');
    }
	
	function _upload_image()
    {
        $file = $_FILES['image'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('logo_accept_error');
			return false;
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['image']);//上传logo
        if (!$uploader->file_info())
        {
			$error = current($uploader->get_error());
            $this->json_error($error['msg']);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        if ($file_path = $uploader->save('data/files/mall/weixin', $uploader->random_filename()))
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
}

?>
