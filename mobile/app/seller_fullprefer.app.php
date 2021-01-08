<?php

/**
 *    满折满减管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_fullpreferApp extends StoreadminbaseApp
{
	var $_appid;
    var $_store_id;
    var $_fullprefer_mod;
	var $_appmarket_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_fullpreferApp();
    }

    function Seller_fullpreferApp()
    {
        parent::__construct();
		$this->_appid     = 'fullprefer';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_fullprefer_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		$this->_appmarket_mod = &m('appmarket');
    }

    function index()
    {
		$fullprefer = $this->_fullprefer_mod->get_info();
		
		if(!IS_POST)
		{
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('fullprefer', $fullprefer);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');

			$this->_config_seo('title', Lang::get('fullprefer_setting') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('fullprefer_setting');
			$this->display('seller_fullprefer.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 	= $_POST['prefer'];
			$status = intval($_POST['status']);
			
			if(($error = $this->checkPostData()) === TRUE) {
				$post['amount'] 	= $this->_filter_price($post['amount']);
				if(!empty($post['discount'])) {
					$post['discount']  	= round(floatval($post['discount']),1); 
					unset($post['decrease']);
				}
				else {
					$post['decrease']  	= $this->_filter_price($post['decrease']);
					unset($post['discount']); 
				}
			}
			else
			{
				$this->json_error($error);
				return;
			}
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status,
				'add_time'	=> gmtime()
			);
			if($fullprefer){
				$this->_fullprefer_mod->edit($fullprefer['psid'], $data);
			} else {
				$this->_fullprefer_mod->add($data);
			}
			$this->json_result('', 'handle_ok');
		}
    }
	function checkPostData()
	{
		$prefer	= $_POST['prefer'];
		if($this->_filter_price($prefer['amount']) <= 0) {
			return Lang::get('not_allempty');
		}
		
		if(!empty($prefer['discount']))
		{
			$discount = $prefer['discount'];
			if($discount <= 0 || $discount >= 10) {
				return Lang::get('discount_invalid');
			}
		}
		elseif(!empty($prefer['decrease']))
		{
			if($this->_filter_price($prefer['decrease']) <= 0) {
				return Lang::get('price_le_0');
			}
			if($this->_filter_price($prefer['amount']) <= $this->_filter_price($prefer['decrease'])) {
				return Lang::get('amount_le_decrease');
			}
		}
		else {
			return Lang::get('pls_select_type');
		}
		return true;
	}
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>