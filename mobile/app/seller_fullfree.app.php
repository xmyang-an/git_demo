<?php

/**
 *    满包邮管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_fullfreeApp extends StoreadminbaseApp
{
	var $_appid;
    var $_store_id;
    var $_fullfree_mod;
	var $_appmarket_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_fullfreeApp();
    }

    function Seller_fullfreeApp()
    {
        parent::__construct();
		$this->_appid = 'fullfree';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_fullfree_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		$this->_appmarket_mod = &m('appmarket');
    }

    function index()
    {
		$fullfree = $this->_fullfree_mod->get_info();
		
		if(!IS_POST)
		{
			$this->assign('fullfree', $fullfree);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('fullfree_setting') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullfree_setting');
			$this->display('seller_fullfree.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post			= $_POST['fullfree'];
			$status 		= intval($_POST['status']);
			
			if($this->checkPostData() === TRUE) {
				$post['fullamount'] 	= $this->_filter_price($post['fullamount']);
				$post['fullquantity'] 	= intval($post['fullquantity']);
			}
			else
			{
				$this->json_error($this->checkPostData());
				return;	
			}
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status,
				'add_time'	=> gmtime()
			);
			if($fullfree){
				$this->_fullfree_mod->edit($fullfree['psid'], $data);
			} else {
				$this->_fullfree_mod->add($data);
			}
			$this->json_result('', 'handle_ok');
		}
    }
	function checkPostData()
	{
		$fullfree = $_POST['fullfree'];
		
		if(empty($fullfree['fullamount']) && empty($fullfree['fullquantity'])) {
			return Lang::get('not_allempty');
		}
		return TRUE;
	}
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>