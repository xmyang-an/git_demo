<?php

class DeliveryApp extends BackendApp {
	
	var $_setting_mod;
	
	function __construct()
	{
		parent::__construct();
		$this->_setting_mod = &af('settings');
		$_POST = stripslashes_deep($_POST);
	}
	
	function index()
    {
       
    }
	
	function config()
	{
		if(!IS_POST)
		{
			$delivery = $this->_setting_mod->getOne('delivery');
			if(empty($delivery))
			{
				$delivery = array(
					'express' => '快递',
					'post'    => '平邮',
					'ems' => 'EMS'
				);
			}
			
			$this->assign('delivery', $delivery);
			
			$this->display('delivery.config.html');
		}
		else
		{
			$express = $_POST['delivery']['express'];
			$post = $_POST['delivery']['post'];
			$ems = $_POST['delivery']['ems'];
			if(!$express || !$post || !$ems)
			{
				$this->json_error('物流名称不能为空');
				exit;
			}
			
			$data['delivery'] = $_POST['delivery'];
			
			$this->_setting_mod->setAll($data);
			
			$this->json_result(array('rel' => 1), '修改成功');
		}
	}
}
?>