<?php

class WxmenuApp extends BackendApp
{
	var $weixin_menu_mod;
	var $weixin_reply_mod;

    function __construct()
    {
        $this->WxmenuApp();
    }

    function WxmenuApp()
    {
        parent::__construct();
		$this->weixin_menu_mod = & m('weixin_menu');
		$this->weixin_reply_mod = & m('weixin_reply');
    }
	
	function index()
    {
        $menus = $this->weixin_menu_mod->get_list(0);

        foreach ($menus as $key => $val)
        {
            if ($child = $this->weixin_menu_mod->get_list($val['id']))
            {
			   $menus[$key]['child'] = $child;
            }
        }
        $this->assign('menus', $menus);

        $this->import_resource(array(
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->display('wxmenu.index.html');
    }
	
	function add()
	{
		if (!IS_POST)
        {
			$parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
            $menu = array('parent_id' => $parent_id, 'sort_order' => 255);
			$this->assign('menu',$menu);
			$parents = array();
			$menus = $this->weixin_menu_mod->get_list(0);
			foreach($menus as $key => $val)
			{
				$parents[$key] = $val['name'];
			}
			$this->assign('parents',$parents);
            $this->display('wxmenu.form.html');
        }
        else
        {
			$name = isset($_POST['name']) ? trim($_POST['name']) : ''; 
			$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
			if(!$this->weixin_menu_mod->unique($name,$parent_id))
			{
				$this->json_error('menu_exist');
                return;
			}
			if(!$this->weixin_menu_mod->check_menu_name($name,$parent_id))
			{
				$error = current($this->weixin_menu_mod->get_error());
                $this->json_error($error['msg']);
                return;
			}
			$type = !in_array($_POST['type'],array('view','click')) ? 'view' : trim($_POST['type']);  
			$data = array(
                'user_id' 		=> 0,
				'parent_id' 	=> $parent_id,
                'name' 			=> $name,
                'type' 			=> $type,
				'sort_order'	=> $_POST['sort_order'],
				'add_time'		=> gmtime(),
            );
			if($type == 'view')
			{
				if(empty($_POST['link']))
				{
					$this->json_error('link_not_empty');
					return;
				}
				$data['link'] = $_POST['link'];	
			}
			else
			{
				if($_POST['reply_id']) //在图文消息库里选择消息
				{
					$data['reply_id'] = intval($_POST['reply_id']);
				}
				else
				{
					$reply = array(
						'user_id' 	=> 0,
						'type'		=> 1,
						'action'	=> 'menu',
						'title'		=> $_POST['reply_title'],
						'link'		=> $_POST['reply_link'],
						'content' 	=> $_POST['reply_content'],
						'add_time'	=> gmtime(),
					);
					if (!empty($_FILES['image']))
					{
						$image = $this->_upload_image();
						$image && $reply['image'] = $image;
					}
					$reply_id = $this->weixin_reply_mod->add($reply);
					$data['reply_id'] = $reply_id;
				}
			}
			
			if(!$this->weixin_menu_mod->add($data)) 
			{
				$error = current($this->weixin_menu_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }

            $this->json_result('','add_menu_successed');
        }
	}
	
	function edit()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$menu = $this->weixin_menu_mod->get($id);
		if(!$id || empty($menu))
		{
			$this->show_warning('no_such_menu');
			return;
		}
		$menu['reply_id'] && $menu['reply'] = $this->weixin_reply_mod->get($menu['reply_id']);
		
		if (!IS_POST)
        {
			$parents = array();
			$menus = $this->weixin_menu_mod->get_list(0);
			foreach($menus as $key => $val)
			{
				$parents[$key] = $val['name'];
			}
			$this->assign('parents',$parents);
			$this->assign('menu',$menu);
            $this->display('wxmenu.form.html');
        }
        else
        {
			$name = isset($_POST['name']) ? trim($_POST['name']) : ''; 
			$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
			if(!$this->weixin_menu_mod->unique($name,$parent_id,$id))
			{
				$this->json_error('menu_exist');
                return;
			}
			if(!$this->weixin_menu_mod->check_menu_name($name,$parent_id,$id))
			{
				$error = current($this->weixin_menu_mod->get_error());
                $this->json_error($error['msg']);
                return;
			}
			$type = !in_array($_POST['type'],array('view','click')) ? 'view' : trim($_POST['type']);  
			$data = array(
                'user_id' 		=> 0,
				'parent_id' 	=> $parent_id,
                'name' 			=> $name,
                'type' 			=> $type,
				'sort_order'	=> $_POST['sort_order'],
				'add_time'		=> gmtime(),
            );
			if($type == 'view')
			{
				if(empty($_POST['link']))
				{
					$this->json_error('link_not_empty');
					return;
				}
				$data['link'] = $_POST['link'];	
			}
			else
			{
				if($_POST['reply_id'] && $_POST['reply_id'] != $menu['reply_id'])  //后期在图文消息库里选择消息
				{
					$data['reply_id'] = intval($_POST['reply_id']);
				}
				else
				{
					$reply = array(
						'user_id' 	=> 0,
						'type'		=> 1,
						'action'	=> 'menu',
						'title'		=> $_POST['reply_title'],
						'link'		=> $_POST['reply_link'],
						'content' 	=> $_POST['reply_content'],
						'add_time'	=> gmtime(),
					);
					if (!empty($_FILES['image']))
					{
						$image = $this->_upload_image();
						$image && $reply['image'] = $image;
					}
					
					if($this->weixin_reply_mod->get($menu['reply_id'])){
						$this->weixin_reply_mod->edit($menu['reply_id'],$reply);
						$reply_id = $menu['reply_id'];
					}else{//菜单中保持reply_id 但是reply已经删除则重新添加
						$reply_id = $this->weixin_reply_mod->add($reply);
						$data['reply_id'] = $reply_id;
					}
				}
				
			}

			$this->weixin_menu_mod->edit($id, $data);
			if($this->weixin_menu_mod->has_error()) 
			{
				$error = current($this->weixin_menu_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
            $this->json_result('','edit_menu_successed');
        }
	}
	
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_such_menu');
            return;
        }

        $ids = explode(',', $id);

        if (!$this->weixin_menu_mod->drop($ids))
        {
            $error = current($this->weixin_menu_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }
		
        $this->json_result('','drop_ok');
    }
	
	function update()
	{
		$data = $this->weixin_menu_mod->get_menus();
		if($this->weixin_menu_mod->has_error()){
			$error = current($this->weixin_menu_mod->get_error());
            $this->json_error($error['msg']);
            return;
		}
		import('weixin.lib');
		$weixinInit = new weixin(); 
		$return = $weixinInit->createMenus($data);
		if(!$return)
		{
			$this->json_error('update fail。');
			return;
		}
		else if($return->errcode)
		{
			$this->json_error('update fail. errcode:'.$return->errcode.',errmsg:'.$return->errmsg);
			return;
		}
		else
		{
			$this->json_result('','update_menu_successed');
		}
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
