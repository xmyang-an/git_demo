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
			$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
			
			/* 当前位置 */
			$this->_curlocal(LANG::get('exclusive'), 	'index.php?app=seller_exclusive',
							 LANG::get('exclusive_setting'));
	
			/* 当前用户中心菜单 */
			$this->_curitem('exclusive');;
	
			/* 当前所处子菜单 */
			$this->_curmenu('exclusive_setting');
			
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('exclusive'));
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('exclusive', $exclusive);
			$this->display('seller_exclusive.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
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
			$this->show_message('handle_ok');
		}
    }
	function checkPostData()
	{
		$exclusive 	= $_POST['exclusive'];
		
		if(isset($exclusive['discount']) && !empty($exclusive['discount'])) {
			$discount = $exclusive['discount'];
			if($discount <= 0 || $discount >= 10) {
				$this->show_warning('discount_invalid');
				exit;
			}
		}		
		return true;
	}
	
	/**
     *    三级菜单
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
			array(
				'name'  => 'exclusive_setting',
				'url'   => 'index.php?app=seller_exclusive',
			),
        );
        return $menus;
    }
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>