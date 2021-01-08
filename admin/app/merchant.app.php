<?php

class MerchantApp extends BackendApp
{
	var $_merchant_mod;
	
    function __construct()
    {
        $this->Kjt_merchantApp();
    }

    function Kjt_merchantApp()
    {
        parent::BackendApp();
		$this->_merchant_mod = &m('merchant');

    }
	function index()
	{
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'name',         //可搜索字段title
                'equal' => 'LIKE',          //等价关系,可以是LIKE, =, <, >, <>
                'type'  => 'string',        //GET的值的类型
            ),
			array(
                'field' => 'appId',         //可搜索字段title
                'equal' => '=',          //等价关系,可以是LIKE, =, <, >, <>
                'type'  => 'string',        //GET的值的类型
            ),
        ));
		
		$page   =   $this->_get_page(10);   //获取分页信息
        $merchants = $this->_merchant_mod->find(array(
			'conditions'  => '1=1 ' . $conditions,
			'limit'   => $page['limit'],
			'order'	=> 'id DESC',
		));
		$this->_format_page($page);
		$this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('page_info', $page);   //将分页信息传递给视图，用于形成分页条
        $this->assign('merchants', $merchants);
		$this->display('merchant.index.html');
	}
	function add()
	{
		if (!IS_POST)
        {
            $this->display('merchant.form.html');
        }
        else
        {
            $data = array();
            $data['name']      	=   html_script(trim($_POST['name']));
            $data['appId']    	=   html_script(trim($_POST['appId']));
            $data['appKey']     =   html_script(trim($_POST['appKey']));
			$data['closed']		=	intval($_POST['closed']);
            $data['add_time']  	=   gmtime();
			
			if($this->_merchant_mod->get("name='".$data['name']."'")) {
				$this->show_warning('name_existed');
				return;
			}
			elseif($this->_merchant_mod->get("appId='".$data['appId']."'")) {
				$this->show_warning('appId_existed');
				return;
			}
			
			if(empty($data['name']) || empty($data['appId'])) {
				$this->show_warning('name_OR_appId_empty');
				return;
			}
			
			// 因为需要使用商户来下单，需要注册一个站内用户跟商户对应
			$ms =& ms();			
			do { 
				$user_name  = 'APP'.gmtime().mt_rand(10,99);
				$password  = mt_rand(1000, 9999);
				if(!$local_data['email']) $local_data['email'] = $user_name.'@163.com';
				$local_data['real_name'] = $data['name'];
				$user_id = $ms->user->register($user_name, $password, $local_data['email'], $local_data);
			} while (!$user_id);
			
			$data['user_id'] = $user_id;
            if (!$id = $this->_merchant_mod->add($data))
            {
                $this->show_warning($this->_merchant_mod->get_error());

                return;
            }

            $this->show_message('add_merchant_successed',
                'back_list',    'index.php?app=merchant',
                'continue_add', 'index.php?app=merchant&amp;act=add'
            );
        }
	}
	
	function edit()
	{
		$id = intval($_GET['id']);
		
		if (!IS_POST)
        {
			$merchant = $this->_merchant_mod->get($id);
			$this->assign('merchant', $merchant);
            $this->display('merchant.form.html');
        }
        else
        {
            $data = array();
            $data['name']      	=   html_script(trim($_POST['name']));
            $data['appId']    		=   html_script(trim($_POST['appId']));
            $data['appKey']       	=   html_script(trim($_POST['appKey']));
			$data['closed']				=	intval($_POST['closed']);
			
			if($this->_merchant_mod->get("name='".$data['name']."' AND id !=".$id)) {
				$this->show_warning('name_existed');
				return;
			}
			elseif($this->_merchant_mod->get("appId='".$data['appId']."' AND id !=".$id)) {
				$this->show_warning('appId_existed');
				return;
			}
			
			if(empty($data['name']) || empty($data['appId'])) {
				$this->show_warning('name_OR_appId_empty');
				return;
			}

            if (!$this->_merchant_mod->edit($id, $data))
            {
                $this->show_warning($this->_merchant_mod->get_error());

                return;
            }

            $this->show_message('edit_merchant_successed',
                'back_list',    'index.php?app=merchant',
                'continue_edit', 'index.php?app=merchant&amp;act=edit&id='.$id
            );
        }
	}
	
	function drop()
	{
		$id = intval($_GET['id']);
		
		if(!$this->_merchant_mod->drop($id))
		{
			$this->show_warning('drop_fail');
			return;
		}
		$this->show_message('drop_ok');
	}
	
}

?>