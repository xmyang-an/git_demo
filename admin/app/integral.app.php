<?php

/* 积分管理控制器 */
class IntegralApp extends BackendApp
{
	var $_member_mod;
	var $_integral_mod;
    function __construct()
    {
        $this->IntegralApp();
    }

    function IntegralApp()
    {
        parent::__construct();
		$this->_member_mod =& m('member');
		$this->_integral_mod = &m('integral');
    }

	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('integral.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_query_conditions();
        $param = array('user_name','amount','user_id');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
			if($_POST['sortname'] == 'user_id') $_POST['sortname'] = 'member.user_id';
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$users = $this->_member_mod->find(array(
            'fields' => 'member.user_id,member.user_name,amount',
			'join'   => 'has_integral',
            'conditions' => $conditions,
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $this->_member_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($users as $k => $v)
		{
			$list = array();
			$operation = "<a class='btn blue' href='index.php?app=integral&act=recharge&id={$k}'><i class='fa fa-yen'></i>充值</a>";
			$operation .= "<a class='btn green' href='index.php?app=integral&act=record&id={$k}'><i class='fa fa-search-plus'></i>查看记录</a>";
			$list['operation'] = $operation;
			$list['id'] = $v['user_id'];
			$list['user_name'] = $v['user_name'];
			$list['amount'] = $v['amount']?$v['amount']:0;
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
        ));
		return $conditions;
	}
		
	function setting()
	{
		$model_setting = &af('settings');
        $setting = $model_setting->getAll(); //载入系统设置数据
        if (!IS_POST)
        {
			$sgrade_mod =& m('sgrade');
			$integral_mod =& m('integral');
			$sgrades = $sgrade_mod->find(array('fields'=>'grade_id,grade_name'));
			foreach($sgrades as $key=>$val){
				$sgrades[$key]['buying_integral'] = $integral_mod->_get_sys_setting(array('buying_integral',$val['grade_id']));
			}

        	$this->assign('sgrades', $sgrades);
            $this->assign('setting', $setting['integral_manage']);
            $this->display('integral.setting.html');
        }
        else
        {
            $data['integral_enabled']  	= intval($_POST['integral_enabled']);
			$data['exchange_rate']     	= empty($_POST['exchange_rate'])? 0.1 : round($_POST['exchange_rate'],2);
			$data['register_integral'] 	= floatval($_POST['register_integral']);
			$data['sign_in_integral'] 	= floatval($_POST['sign_in_integral']);
			$data['open_integral'] 		= floatval($_POST['open_integral']);
			
			foreach($_POST['buying_integral'] as $k=>$v)
			{
				$data['buying_integral'][$k] = $v;
				if(!is_numeric($v) || $v > 1 || $v < 0) {
					$data['buying_integral'][$k] = 0;
				}
			}
            $model_setting->setAll(array('integral_manage' => $data));
            $this->json_result(array('ret_url'=>'index.php?app=integral'),'edit_integral_setting_successed');
        }
	}
	
	function recharge()
	{
		$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
		if(!$user_id || !$user = $this->_member_mod->get(array('conditions'=>'user_id='.$user_id, 'fields'=> 'user_id,user_name'))) {
			$this->show_warning('user_empty');
			return;
		}
		
		if(!IS_POST) 
		{
			$this->assign('user',$user);
			$this->display('integral.form.html');
		}
		else
		{
			$flow	 = trim($_POST['flow']);
			if(!in_array($flow, array('add', 'minus'))) {
				$flow = 'minus';
			}
			$data = array(
				'user_id' => $user_id,
				'type' 	  => 'admin_handle',
				'flow'    => $flow,
				'amount'  => floatval($_POST['amount']),
				'flag'    => html_script($_POST['flag'])
			);
			$this->_integral_mod->update_integral($data);
			$this->json_result('','edit_ok');
		}
	}
	
	function record()
    {
		$user_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$user_id){
			$this->show_warning('no_such_user');
			return;	
		}
		$user_info = $this->_member_mod->get(array('conditions'=>$user_id,'fields'=>'user_id,user_name'));
		if(empty($user_info))
		{
			$this->show_warning('no_such_user');
			return;	
		}
		$this->assign('user_info',$user_info);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('integral.record.html');
    }
	
	function get_record_xml()
	{
		$user_id = intval($_GET['user_id']);
        $param = array('user_name','amount');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        } else $order = 'log_id DESC';
		
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		
		$page   =   $this->_get_page($pre_page);
		$integral_log_mod = &m('integral_log');
		$integral_log = $integral_log_mod->find(array(
			'conditions'=>'user_id='.$user_id,
			'order'     => $order,
			'limit'     =>$page['limit'],
			'count'     =>true
		));
        $page['item_count'] = $integral_log_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($integral_log as $k => $v)
		{
			$list = array();
			$list['type'] = Lang::get($v['type']);
			$list['changes'] = $v['changes']>0 ? "<span class='plus'>+{$v['changes']}</span>" : "<span class='minus'>{$v['changes']}</span>";
			$list['balance'] = $v['balance'];
			$list['state'] = $this->_integral_mod->status($v['state']);
			$list['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
			$list['flag'] = $v['flag'] . ($v['order_id'] ? "订单号：{$v['order_sn']} <a href='index.php?app=order&act=view&id={$v['order_id']}'>订单详情</a>" : "");
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function export_csv()
	{
		$conditions = '1 = 1';
		if ($_POST['query'] != '') 
		{
			$conditions .= " AND ".$_POST['qtype']." like '%" . $_POST['query'] . "%'";
		}
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND member.user_id' . db_create_in($ids);
        }
        $users = $this->_member_mod->find(array(
			'conditions' => $conditions,
            'fields' => 'member.user_id,member.user_name,ig.*',
			'join'   => 'has_integral',
            'order' => "amount desc",
        ));
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'user_id' 		=> 	'ID',
    		'user_name' 		=> 	'用户名',
			'amount' => '积分',
		);
		$folder = 'integral_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($users as $key=>$val)
    	{
			$record_value['user_id']	=	$val['user_id'];
			$record_value['user_name']	=	$val['user_name'];
			$record_value['amount']	=	$val['amount']?$val['amount']:0;
        	$record_xls[] = $record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
}

?>
