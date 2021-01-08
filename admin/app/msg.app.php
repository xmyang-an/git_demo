<?php

class MsgApp extends BackendApp
{
	var $_msg_mod; 
	var $_msglog_mod;
	var $_member_mod;
	
	function __construct()
    {
        $this->MsgApp();
    }
    function MsgApp()
    {
        parent::BackendApp();
		$this->_msg_mod =& m('msg');
		$this->_msglog_mod =& m('msglog');
		$this->_member_mod = &m('member');
    }
	
    function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('msg.index.html');
    }
	
	function get_xml()
	{
		$conditions = 'type=0';
		$conditions .= $this->get_query_conditions();
		$order = 'id DESC';
        $param = array('to_mobile','content','quantity','time','user_name','state','result');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$msglogs = $this->_msglog_mod->find(array(
	        'conditions' => $conditions,
			'join' => 'belongs_to_user',
			'fields' => 'this.*,member.user_name',
            'limit' => $page['limit'],
			'order' => $order,
			'count' => true
		));
		$page['item_count'] = $this->_msglog_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($msglogs as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$k},'msg')\"><i class='fa fa-trash-o'></i>删除</a>";
			$list['to_mobile'] = $v['to_mobile'];
			$list['content'] = $v['content'];
			$list['quantity'] = $v['quantity'];
			$list['time'] = local_date('Y-m-d H:i:s',$v['time']);
			$list['user_name'] = $v['user_name'] ? $v['user_name'] : Lang::get('system');
			$list['state'] = $v['state'] ? Lang::get('send_success') : Lang::get('send_failed');
			$list['result'] = $v['result'];
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'to_mobile',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
	function user()
    {
		$query = $this->get_user_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('msg.user.html');
    }
	
	function get_user_xml()
	{
		$conditions = '1=1';

		$conditions .= $this->get_user_query_conditions();
		$order = 'this.user_id DESC';
        $param = array('user_name','phone_mob','num','functions','state');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$users = $this->_msg_mod->find(array(
	        'conditions' 	=> $conditions,
			'join'       	=> 'belongs_to_user',
			'fields'     	=> 'this.*,member.user_name,phone_mob',
            'limit' 		=> $page['limit'],
			'order' 		=> $order,
			'count'			 => true
		));
		$page['item_count'] = $this->_msg_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		
		import('sms.lib');
		$sms = new SMS();
		$checked_functions = $functions = array();
        $functions = $sms->getFunctions();
		foreach($users as $key => $user)
		{
			$tmp = explode(',', $user['functions']);
			if ($functions)
			{
				 foreach ($functions as $k => $func)
				 {
					 $checked_functions[$key][$func] = in_array($func, $tmp);
				 }
			}
		}
						
		foreach ($users as $k => $v){
			
			$functionText = '';
			foreach($functions as $funKey => $funVal) {
				$checked = $checked_functions[$k][$funVal] ? 'checked="checked"' : '';
				$functionText .= "<input type='checkbox' disabled='disabled' {$checked} /> <label>".Lang::get($funVal)."</label>&nbsp;&nbsp;";
			}
			
			$list = array();
			$list['operation'] = "<a class='btn green' href='index.php?app=msg&act=add&user_id={$v['user_id']}'><i class='fa fa-trash-o'></i>分配短信</a>";
			$list['user_id'] = $v['user_id'];
			$list['user_name'] = $v['user_name'];
			$list['phone_mob'] = $v['phone_mob'];
			$list['functions'] = $functionText;
			$list['num'] = $v['num'];
			$list['state'] = $v['state'] ? '<em class="yes"><i class="fa fa-check-circle"></i>是</em>' : '<em class="no"><i class="fa fa-ban"></i>否</em>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_user_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'user_name',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
	function statist()
	{
		import('sms.lib');
		$sms = new SMS();
		
		// 可使用的短信数
		$available = $sms->getNum();
		if($available < 0){
			$available = 0;
		}
		
		// 已使用的短信数
		$msgstatistics_mod = &m('msgstatistics');
		$msgstatistics = $msgstatistics_mod->get(0);
		$used = $msgstatistics['used'];
		
		// 已分配但未使用的短信数
		$allocated = 0;
		$msg_mod = &m('msg');
		$allmsg = $msg_mod->find(array('conditions'=>'user_id > 0','fields'=>'num'));
		foreach($allmsg as $key=>$val)
		{
			$allocated += $val['num'];
		}
		
		// 可用于分配的短信数
		$distributable = $available - $allocated;
		if($distributable < 0) $distributable = 0;
		
		return array('available' => $available, 'distributable' => $distributable, 'used' => $used, 'allocated' => $allocated);
	}
	   
 	function add()
    {	
		$statist = $this->statist();
		$this->assign('statist', $statist);
		$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
		if(!IS_POST)
		{
			if(!empty($user_id))
			{
				$user = $this->_msg_mod->get(array(
					'conditions' => 'msg.user_id='.$user_id,
					'join' => 'belongs_to_user',
					'fields' => 'this.*,member.user_name'
				));
			}
			$this->assign('user', $user);
			$this->display('msg.add.html');
		}
		else
		{
			$user_name = trim($_POST['user_name']);
		   	$num = intval($_POST['num']);
		   	$add_dec = intval($_POST['add_dec']);
		   	$log_text = trim($_POST['log_text']);	
		   	if(empty($user_name) || empty($num))
		   	{
				$this->json_error('err_no_null');
				return;
		   	}  
		   	if (preg_match("/[^0.-9]/",$num))
		   	{
			   $this->json_error('err_not_num'); 
			   return;
		   	} 
			if($num > $statist['distributable']) {
				$this->json_error(sprintf(Lang::get('distributable_note'),$statist['distributable'])); 
			   	return;
			}
			
		   	$row_msg = $this->_msg_mod->get(array(
				'conditions' => "user_name='{$user_name}'",
				'join' => 'belongs_to_user',
				'fields' => 'msg.num,msg.user_id',
			));	
		   	if($row_msg)
		   	{
			   $num_old = $row_msg['num']; 
			   $id = $row_msg['user_id'];
			   if($add_dec)
			   {
					$num_new = $num_old + $num;
			   }
			   else
			   {
				   if($num_old >= $num)
				   {	   
						$num_new = $num_old - $num;
				   }
				   else
				   {
						$this->json_error('err_num_smaller');
						return;
				   }
			   } 
			   $this->_msg_mod->edit("user_id='{$id}'",array('num' => $num_new));
			   $edit_msglog = array(
			   		'user_id' => $id,
					'content' => $log_text,
					'quantity' => $num, //记录分配数目
					'state' => $add_dec, //1为增加，0为减少
					'type' => 1,//1为分配短信，0为发送短信
					'time' => gmtime(), 
			   );
			   $this->_msglog_mod->add($edit_msglog);
			   $this->json_result(array('ret_url'=>'index.php?app=msg&act=user'),'add_msgnum_successed');
		    }
		    else
		    {
			   $this->json_error('err_no_user'); 
			   return;
		    } 
		}
	}
	
	function send()
    {
        if (!IS_POST)
		{
			$this->display('msg.send.html');
        }
        else
        {
			$phone_mob	 = trim($_POST['to_mobile']);	//号码
			$smsText = trim($_POST['msg_content']);//内容
			if(!is_mobile($phone_mob))
			{
				$this->json_error('phone_no_null');
				return;
			}
			if(empty($smsText))
			{
				$this->json_error('content_no_null');
				return;
			}
			
			import('sms.lib');
			$sms = new SMS();
			
			if(!$sms->checkSendMsg($phone_mob)) {
				$error = current($sms->get_error());
				$this->json_error($error['msg']);
				return;
			}
			
			$result = $sms->send(array('phone_mob' => $phone_mob, 'text' => $smsText, 'sender' => 0));
		
			if($result === false)
			{
				$this->json_error('send_msg_faild');
				return;
			}
			$this->json_result(array('ret_url'=>'index.php?app=msg'),'send_msg_successed');
        }
    }
	
	function drop()
    {
        $ids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$ids)
        {
            $this->json_error('no_such_log');

            return;
        }
        $ids = explode(',', $ids);
        if (!$this->_msglog_mod->drop($ids))
        {
			$error = current($this->_msglog_mod->get_error());
            $this->json_error($error['msg']);

            return;
        }

        $this->json_result('','drop_successed');
    }
	
	function setting()
    {
		$msg_setting_mod = &m('msg_setting');
		$setting = $msg_setting_mod->get('');
        if (!IS_POST)
        {
			if(!empty($setting)){
				$setting['msg_status'] = unserialize($setting['msg_status']);
            	$this->assign('setting', $setting);
			}
            $this->display('msg.setting.html');
        }
        else
        {
            $data['msg_pid']     = $_POST['msg_pid'];
            !empty($_POST['msg_key']) && $data['msg_key'] = trim($_POST['msg_key']);
			$data['msg_status']  = serialize($_POST['msg_status']);
			if(empty($setting)){
				$msg_setting_mod->add($data);
			}else{
				$msg_setting_mod->edit($setting['id'],$data);
			}			
            $this->json_result(array('ret_url'=>'index.php?app=msg'),'setting_successed');
        }
    }
}
?>