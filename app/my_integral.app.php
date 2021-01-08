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
		
		/* 当前所处子菜单 */
        $this->_curmenu('integral_log');
        /* 当前用户中心菜单 */
        $this->_curitem('my_integral');
		
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
		$this->assign('integral', $integral);
		
		
		// 读取最新的20条记录
		$integral_log_mod=&m('integral_log');
		$order_mod=&m('order');
		$integral_log=array();
		$integral_log=$integral_log_mod->find(array(
			'conditions'=>'user_id='.$this->visitor->get('user_id').$conditions,
			'order'     =>'add_time DESC',
			'limit'     =>20,
		));
		
		foreach($integral_log as $key => $val)
		{
			$integral_log[$key]['state'] = $this->_integral_mod->status($val['state']);
			$integral_log[$key]['name'] = Lang::get($val['type']);
			$order=$order_mod->get(array(
				'conditions'=>'order_id='.$val['order_id'],
				'fields'    =>'order_sn',
			));
			$integral_log[$key]['order_sn']=$order['order_sn'];
		}
        $this->assign('integral_log', $integral_log);
		
        /* 当前位置 */
        $this->_curlocal(LANG::get('my_integral'),         'index.php?app=my_integral',
                         LANG::get('integral_log')
        );
		
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_integral'));
        $this->display('my_integral.index.html');
    }
	
	function logs()
    {
		if(!$this->_integral_mod->_get_sys_setting('integral_enabled'))
		{	
			$this->show_warning('integral_disabled');
			exit;
		}
		
		$type=$_GET['type'] ? $_GET['type'] :'';
		switch($type)
		{
			case 'integral_income':
			$curlocal='integral_income';
			$conditions=" AND changes > 0  AND state = 'finished' ";
			break;
			
			case 'integral_pay':
			$curlocal='integral_pay';
			$conditions=" AND changes < 0 AND state = 'finished' ";
			break;
			
			case 'integral_frozen':
			$curlocal='frozen_integral';
			$conditions=" AND  state = 'frozen' ";
			break;
			
			default:
			$curlocal='integral_log';
		}
		
        /* 当前所处子菜单 */
        $this->_curmenu($curlocal);
        /* 当前用户中心菜单 */
        $this->_curitem('my_integral');
        
        $page = $this->_get_page(20);
		$integral_log_mod=&m('integral_log');
		$order_mod=&m('order');
		$integral_log=array();
		$integral_log=$integral_log_mod->find(array(
			'conditions'=>'user_id='.$this->visitor->get('user_id').$conditions,
			'order'     =>'add_time DESC',
			'limit'     =>$page['limit'],
			'count'     =>true
		));
		
		foreach($integral_log as $key => $val)
		{
			$integral_log[$key]['state'] = $this->_integral_mod->status($val['state']);
			$integral_log[$key]['name'] = Lang::get($val['type']);
			$order=$order_mod->get(array(
				'conditions'=>'order_id='.$val['order_id'],
				'fields'    =>'order_sn',
			));
			$integral_log[$key]['order_sn']=$order['order_sn'];
		}
        $page['item_count'] = $integral_log_mod->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('integral_log', $integral_log);
        
		/* 当前位置 */
        $this->_curlocal(LANG::get('my_integral'),         'index.php?app=my_integral',
                         LANG::get($curlocal)
        );
		  
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get($curlocal));
        $this->display('my_integral.logs.html');
    }
	
	//签到送积分
	function sign()
	{
		$user_id = $this->visitor->get('user_id');
		
		$integral_log_mod = &m('integral_log');
		$log = $integral_log_mod->get(array(
			'conditions'=>"type ='sign_in_integral' AND user_id = ".$user_id,'order'=>'add_time desc'));
		
		if(local_date('Ymd', gmtime()) == local_date('Ymd', $log['add_time']))
		{
			$this->json_error('you_have_got_integral_for_sign_in');
			return;
		}
		
		$integral_mod=&m('integral');
		$data = array(
			'user_id' => $user_id,
			'type'    => 'sign_in_integral',
			'amount'  => $integral_mod->_get_sys_setting('sign_in_integral')
		);
	    $integral_mod->update_integral($data);
		
		$new_amount = $integral_mod->get($user_id);
		
		$this->json_result(array(
			'amount' => $new_amount['amount']),sprintf(Lang::get('success_get_integral_for_sign_in'),
			$integral_mod->_get_sys_setting('sign_in_integral')
		));
	}
	
    function _get_member_submenu()
    {
        return array(
            array(
                'name'  => 'integral_log',
                'url'   => 'index.php?app=my_integral',
            ),
			array(
                'name'  => 'integral_income',
                'url'   => 'index.php?app=my_integral&act=logs&type=integral_income',
            ),
			array(
                'name'  => 'integral_pay',
                'url'   => 'index.php?app=my_integral&act=logs&type=integral_pay',
            ),
			array(
                'name'  => 'frozen_integral',
                'url'   => 'index.php?app=my_integral&act=logs&type=integral_frozen',
            ),
        );
    }
}
?>
