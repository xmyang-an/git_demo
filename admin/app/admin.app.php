<?php

/* 管理员控制器 */
class AdminApp extends BackendApp
{
    var $_admin_mod;
    var $_user_mod;

    function __construct()
    {
        $this->AdminApp();
    }

    function AdminApp()
    {
        parent::__construct();
        $this->_admin_mod = & m('userpriv');
        $this->_user_mod = & m('member');
    }
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('admin.index.html');
    }
	
	function get_xml()
	{
        $conditions = 'store_id = 0';
		$conditions .= $this->get_query_conditions();
		$order = 'userpriv.user_id asc';
        $param = array('user_name','real_name','email','region_name','phone_mob','reg_time','last_login','logins','last_ip');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$admin_info = $this->_admin_mod->find(array(
            'conditions' => $conditions,
            'join' => 'mall_be_manage',
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $this->_admin_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($admin_info as $k => $v){
			$list = array();
			if($v['privs'] == 'all'){
				$operation = "<em class='red'>".Lang::get('system_manager')."</em>";
			}else{
				$operation = "<a class='btn red' onclick=\"fg_delete({$k},'admin')\"><i class='fa fa-trash-o'></i>删除</a>";
				$operation .= "<a class='btn blue' href='index.php?app=admin&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>权限</a>";
			}
			$list['operation'] = $operation;
			$list['user_name'] = $v['user_name'];
			$list['real_name'] = $v['real_name'];
			$list['email'] = $v['email'];
			$list['phone_mob'] = $v['phone_mob'];
			$list['reg_time'] = local_date('Y-m-d',$v['reg_time']);
			$list['last_login'] = local_date('Y-m-d H:i:s',$v['last_login']);
			$list['last_ip'] = $v['last_ip'];
			$list['logins'] = $v['logins'];
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'user_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'real_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'email',
                'equal' => 'like',
            ),
            array(
                'field' => 'phone_mob',
				'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
    function drop()
    {
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';
        //判断是否选择管理员
        $ids = explode(',',$id);
        if (!$id||$this->_admin_mod->check_admin($id))
        {
            $this->json_error('choose_admin');
            return;
        }
        //判断是否是系统初始管理员
        if ($this->_admin_mod->check_system_manager($id))
        {
            $this->json_error('system_admin_drop');
            return;
         }
         //删除管理员
        $conditions = "store_id = 0 AND user_id " . db_create_in($ids);
        if (!$res = $this->_admin_mod->drop($conditions))
        {
            $this->json_error('drop_failed');
            return;
        }
        $this->json_result(array('ret_url'=>'index.php?app=admin'),'drop_ok');
    }
	
    function edit()
    {
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? intval($_GET['id']) : '';
        //判断是否选择了管理员
        if (!$id || $this->_admin_mod->check_admin($id))
        {
            $this->show_warning('choose_admin');
            return;
        }
        //判断是否是系统初始管理员
         if ($this->_admin_mod->check_system_manager($id))
        {
            $this->show_warning('system_admin_edit');
            return;
        }
        if (!IS_POST)
        {
            //获取当前管理员权限
            $privs = $this->_admin_mod->get(array(
                'conditions' => '1=1 AND  store_id =0 AND user_id = '.$id,
                'fields' => 'privs',
            ));
           $admins = $this->_user_mod->get(array(
                    'conditions' => '1=1 AND user_id ='.$id,
                    'fields' => 'user_name,real_name',
                ));
            $priv=explode(',', $privs['privs']);
            include(APP_ROOT . '/includes/priv.inc.php');
            $act = 'edit';
            $this->assign('act',$act);
            $this->assign('admin',$admins);
            $this->assign('checked_priv',$priv);
            $this->assign('priv',$menu_data);
            $this->display('admin.form.html');
        }
        else
        {
            //更新管理员权限
            $privs = (isset($_POST['priv']) && $_POST['priv']!='priv') ? $_POST['priv']: '';
            $priv = '';
            if ($privs == '')
            {
                $this->json_error('add_priv');
                return;
            }
            else
            {
                $priv = implode(',', $privs);
            }
            $data = array('privs' => $priv);
            $this->_admin_mod->edit('store_id=0 AND user_id='.$id, $data);
            if($this->_admin_mod->has_error())
            {
				$error = current($this->_admin_mod->get_error());
				$this->json_error($error['msg']);
				return;
             }
             $this->json_result('','edit_admin_ok');
        }
    }
	
    function add()
    {
        $id = (isset($_GET['id']) && $_GET['id'] != '') ? intval($_GET['id']) : '';
		if(!IS_POST)
        {
			$condition = ' AND  user_id = '.$id;
			$admin = $this->_user_mod->get(array(
				'conditions' => '1=1' . $condition,
				'fields' => 'user_name,real_name',
			));
			//查询是否是管理员
			if (!$admin)
			{
				$this->show_warning('choose_admin');
				return;
			}
			//查询是否已是管理员
			if (!$this->_admin_mod->check_admin($id))
			{
				$this->show_warning('already_admin');
				return;
			}
			$this->assign('admin',$admin);
			include(APP_ROOT . '/includes/priv.inc.php');
			$this->assign('priv', $menu_data);
			$this->display('admin.form.html');
        }
        else
        {
            //获取权限并处理
            $privs = (isset($_POST['priv']) && $_POST['priv'] != 'priv') ? $_POST['priv'] : '';
            $priv = 'default|all,';
            if ($privs == '')
            {
                $this->json_error('add_priv');
                return;
            }
            else
            {
                $priv .= implode(',', $privs);
            }
            $data = array(
                'user_id' => $id,
                'store_id' => '0',
                'privs' => $priv,
            );
            if($this->_admin_mod->add($data) === false)
            {
				$error = current($this->_admin_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
            $this->json_result(array('ret_url'=>'index.php?app=admin'),'add_admin_ok');
        }
    }
}

?>
