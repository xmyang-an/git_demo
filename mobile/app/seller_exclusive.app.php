<?php

/**
 *    手机专享管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_exclusiveApp extends StoreadminbaseApp
{
	var $_appid;
    var $_store_id;
    var $_exclusive_mod;
	var $_appmarket_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_exclusiveApp();
    }

    function Seller_exclusiveApp()
    {
        parent::__construct();
		$this->_appid     = 'exclusive';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_exclusive_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		$this->_appmarket_mod = &m('appmarket');
    }

    function index()
    {
		$exclusive = $this->_exclusive_mod->get_info();
		
		if(!IS_POST)
		{			
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('exclusive', $exclusive);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('exclusive_setting') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('exclusive_setting');
			$this->display('seller_exclusive.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 				= $_POST['exclusive'];
			$status 			= intval($_POST['status']);
			
			if($this->checkPostData() === TRUE) {
				if(isset($post['discount']) && !empty($post['discount'])){
					$post['discount'] = round(abs($post['discount']), 1);
				}
				if(isset($post['decrease']) && !empty($post['decrease'])){
					$post['decrease'] = round(abs($post['decrease']), 2);
				}
			}
			else
			{
				$this->json_error('discount_invalid');
				exit;
			}
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status,
				'add_time'	=> gmtime()
			);
			if($exclusive){
				$this->_exclusive_mod->edit($exclusive['psid'], $data);
			} else {
				$this->_exclusive_mod->add($data);
			}
			$this->json_result(array('rel' => true),'handle_ok');
		}
    }
	function checkPostData()
	{
		$exclusive 	= $_POST['exclusive'];
		
		if(isset($exclusive['discount']) && !empty($exclusive['discount'])) {
			$discount = $exclusive['discount'];
			if($discount <= 0 || $discount >= 10) {
				return false;
			}
		}		
		return true;
	}
}


?>