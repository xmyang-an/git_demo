<?php

/**
  *    手机短信
  *
*/
	 
class MsgApp extends StoreadminbaseApp
{
	var $mod_msg;
	var $mod_msglog;
    
	function __construct()
    {
        $this->MsgApp();
    }

    function MsgApp()
    {
        parent::__construct();
		$this->mod_msg =& m('msg');
		$this->mod_msglog =& m('msglog');
		$this->msginit();
    }
	
    function index()
    {
		$msg = $this->mod_msg->get(array(
			'conditions' 	=> 'msg.user_id='.$this->visitor->get('user_id'),
			'join' 			=> 'belongs_to_user',
			'fields' 		=> 'this.*,phone_mob'
		));
			
		if (!IS_POST)
        {
			/* 当前所处子菜单 */
        	$this->_curmenu('set');
        	/* 当前用户中心菜单 */
        	$this->_curitem('msg');
			
			import('sms.lib');
			$sms = new SMS();
			$checked_functions = $functions = array();
        	$functions = $sms->getFunctions();
            $checked_functions = explode(',', $msg['functions']);
			
			$sendTotal = 0;
			$msglog = $this->mod_msglog->find(array(
				'conditions'=>'type = 0 AND state = 1 AND user_id='.$this->visitor->get('user_id'), 'fields'=>'quantity'));
			foreach($msglog as $log) {
				$sendTotal += $log['quantity'];
			}
			$msg['sendTotal']= $sendTotal;
			
			$this->assign('msg',$msg);
			$this->assign('functions', $functions);
			$this->assign('checked_functions', $checked_functions);
			$this->assign('ret_url', rawurlencode(SITE_URL . '/index.php?app=msg'));
			
			/* 当前位置 */
        	$this->_curlocal(LANG::get('msg'),         'index.php?app=msg',
                         LANG::get('set')
                   );
						
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('msg'));
			$this->display('msg.index.html');
		}
		else
		{
			if(!$msg['phone_mob']) {
				$this->show_warning('您还没有绑定手机，请先绑定');
				return;
			}
			$functions = isset($_POST['functions']) ? implode(',', $_POST['functions']) : '';
			$data = array(
                'state' 		=> intval($_POST['state']),
                'functions'    	=> $functions,
            );
			$this->mod_msg->edit('user_id='.$this->visitor->get('user_id'), $data);
            $this->show_message('set_ok',
                'back_list',    'index.php?app=msg'
            );
		}
    }
    
	function log()
    {
        /* 当前所处子菜单 */
        $this->_curmenu('sendlog');
        /* 当前用户中心菜单 */
        $this->_curitem('msg');
		
        
		$mod_msglog = &m('msglog');
		$page = $this->_get_page(10);		
		$msglog = $mod_msglog->find(array(
	        'conditions' => 'type=0 and state=1 and user_id='.$this->visitor->get('user_id'),
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $mod_msglog->getCount();
        $this->_format_page($page);
	    $this->assign('page_info', $page);
	    $this->assign('msglog', $msglog);
		
		/* 当前位置 */
		$this->_curlocal(LANG::get('msg'),         'index.php?app=msg',
                             LANG::get('sendlog')
                );
		
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('sendlog'));
        $this->display('msg.log.html');
	}
	
	/*三级菜单*/
    function _get_member_submenu()
    {
        $array = array(
            array(
                'name' => 'set',
                'url' => 'index.php?app=msg',
            ),
			array(
                'name' => 'sendlog',
                'url' => 'index.php?app=msg&act=log',
            ),
        );
        return $array;
    }
	
	function msginit()
	{
		$msg_setting_mod = &m('msg_setting');
		if(!$msg_setting = $msg_setting_mod->get('')) 
		{
			$this->show_warning('Hacking Attempt');
			return;
		}
		elseif(!$msg_setting['msg_pid'] || !$msg_setting['msg_key']) 
		{
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		$user_id = $this->visitor->get('user_id');
		$msg_id =$this->mod_msg->get("user_id='{$user_id}'");
		$mod_store = &m('store');
		$store = $mod_store->get(array('conditions' => 'store_id='.$user_id, 'fields' => 'store_id')); 
		
		/* msg没有该user，并且store表里面有（未被删除）的时候增加到msg表 */
		if(!$msg_id && $store) {
			$this->mod_msg->add(array("user_id" => $user_id));
		}
	}
}

?>
