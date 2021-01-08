<?php

class My_integralApp extends MemberbaseApp
{
	var $_integral_mod;
	
    function __construct()
	{
		parent::__construct();
		$this->_integral_mod = &m('integral');
	}
	
    function index()
    {
		if(!$this->_integral_mod->_get_sys_setting('integral_enabled'))
		{	
			$this->show_warning('integral_disabled');
			exit;
		}
		
		//会员当前的可用积分
		$integral_mod = &m('integral');
		$integral = $integral_mod->get($this->visitor->get('user_id'));
		
		//会员当前被冻结的积分
		$order_integral_mod = &m('order_integral');
		$frozen_integral = $order_integral_mod->get(array(
			'conditions'=>'buyer_id='.$this->visitor->get('user_id'),
			'fields'    =>'SUM(frozen_integral) AS frozenIntegral',
		));
		if($frozen_integral) {
			$integral['frozen_integral'] = $frozen_integral['frozenIntegral'];
		}
		
		//是否可以签到
		$sign_in_integral = $this->_integral_mod->_get_sys_setting('sign_in_integral');
		if($sign_in_integral > 0)
		{
			$integral['sign_in_integral'] = TRUE;
			$integral['signIntegral'] = $sign_in_integral; 
			$integral['can_sign'] = TRUE;
			
			$integral_log_mod = &m('integral_log');
		
			$logs = $integral_log_mod->find(array(
				'conditions'=> "type='sign_in_integral' AND user_id=".$this->visitor->get('user_id'),
				'fields'    => "add_time",
				'order'		=> "log_id DESC",
				'limit'		=> 1
			));
			if($logs) {
				$lastlog = end($logs);
				if(local_date('Y-m-d', $lastlog['add_time']) == local_date('Y-m-d', gmtime())) {
					$integral['can_sign'] = FALSE;
				}
			}
		}

		$this->assign('integral', $integral);

        
        $this->_config_seo('title', Lang::get('my_integral') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('my_integral');
        $this->display('my_integral.index.html');
    }
	
	function logs()
	{
		$type = $_GET['type'] ? $_GET['type'] : '';
		switch($type)
		{
			case 'integral_income':
			$curlocal = 'integral_income';
			$conditions = " AND changes > 0  AND state = 'finished' ";
			break;
			
			case 'integral_pay':
			$curlocal = 'integral_pay';
			$conditions = " AND changes < 0 AND state = 'finished' ";
			break;
			
			case 'integral_frozen':
			$curlocal = 'frozen_integral';
			$conditions = " AND  state = 'frozen' ";
			break;
			
			default:
			$curlocal = 'integral_log';
		}
		
		if(!IS_AJAX)
		{
			if(!$this->_integral_mod->_get_sys_setting('integral_enabled'))
			{	
				$this->show_warning('integral_disabled');
				exit;
			}
			
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get($curlocal) . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title($curlocal);
        	$this->display('my_integral.logs.html');
		}
		else
		{
			$integral_log_mod = &m('integral_log');
			$order_mod = &m('order');
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$integral_log = $integral_log_mod->find(array(
				'conditions'=>'user_id='.$this->visitor->get('user_id').$conditions,
				'order'     =>'add_time DESC',
				'limit'     =>$page['limit'],
				'count'     =>true
			));
			$page['item_count'] = $integral_log_mod->getCount();   //获取统计的数据
			$this->_format_page($page);
			
			foreach($integral_log as $key => $val)
			{
				$integral_log[$key]['state'] = $this->_integral_mod->status($val['state']);
				$integral_log[$key]['name'] = Lang::get($val['type']);
				$order = $order_mod->get(array(
					'conditions'=>'order_id='.$val['order_id'],
					'fields'    =>'order_sn',
				));
				$integral_log[$key]['order_sn']=$order['order_sn'];
			}
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($integral_log), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	//签到送积分
	function sign_in_integral()
	{
		$user_id = $this->visitor->get('user_id');
		
		$integral_log_mod = &m('integral_log');
		
		$logs = $integral_log_mod->find(array(
			'conditions'=> "type='sign_in_integral' AND user_id=".$this->visitor->get('user_id'),
			'fields'    => "add_time",
			'order'		=> "log_id DESC",
			'limit'		=> 1
		));
		if($logs) {
			$lastlog = end($logs);
			if(local_date('Y-m-d', $lastlog['add_time']) == local_date('Y-m-d', gmtime())) {
				$this->json_error('you_have_got_integral_for_sign_in');
				return;
			}
		}

		$sign_in_integral = $this->_integral_mod->_get_sys_setting('sign_in_integral');
		$data = array(
			'user_id' => $user_id,
			'type'    => 'sign_in_integral',
			'amount'  => $sign_in_integral
		);
	    $this->_integral_mod->update_integral($data);
		
		$new_data = $this->_integral_mod->get(array('conditions' => 'user_id='.$user_id, 'fields' => 'amount'));
		
		$this->json_result($new_data, sprintf(Lang::get('success_get_integral_for_sign_in'), $sign_in_integral));
	}
}
?>
