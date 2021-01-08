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
			$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
			
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullprefer'), 	'index.php?app=seller_fullprefer',
							 LANG::get('fullprefer_setting'));
	
			/* 当前用户中心菜单 */
			$this->_curitem('fullprefer');;
	
			/* 当前所处子菜单 */
			$this->_curmenu('fullprefer_setting');
			
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullprefer'));
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->assign('fullprefer', $fullprefer);
			$this->display('seller_fullprefer.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
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
			$this->show_message('handle_ok');
		}
    }
	function checkPostData()
	{
		$prefer	= $_POST['prefer'];
		
		if($this->_filter_price($prefer['amount']) <= 0) {
			$this->show_warning('not_allempty');
			exit;
		}
		
		if(!empty($prefer['discount']))
		{
			$discount = $prefer['discount'];
			if($discount <= 0 || $discount >= 10) {
				$this->show_warning('discount_invalid');
				exit;
			}
		}
		elseif(!empty($prefer['decrease']))
		{
			if($this->_filter_price($prefer['decrease']) <= 0) {
				$this->show_warning('price_le_0');
				exit;
			}
			if($this->_filter_price($prefer['amount']) <= $this->_filter_price($prefer['decrease'])) {
				$this->show_warning('amount_le_decrease');
				exit;
			}
			
		}
		else {
			$this->show_warning('pls_select_type');
			exit;
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
				'name'  => 'fullprefer_setting',
				'url'   => 'index.php?app=seller_fullprefer',
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