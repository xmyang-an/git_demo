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
			$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
			
			/* 当前位置 */
			$this->_curlocal(LANG::get('fullfree'), 	'index.php?app=seller_fullfree',
							 LANG::get('fullfree_setting'));
	
			/* 当前用户中心菜单 */
			$this->_curitem('fullfree');
	
			/* 当前所处子菜单 */
			$this->_curmenu('fullfree_setting');
			
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('fullfree'));
			$this->assign('fullfree', $fullfree);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_fullfree.index.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
			$post			= $_POST['fullfree'];
			$status 		= intval($_POST['status']);
			
			if($this->checkPostData() === TRUE) {
				$post['fullamount'] 	= $this->_filter_price($post['fullamount']);
				$post['fullquantity'] 	= intval($post['fullquantity']);
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
			$this->show_message('handle_ok');
		}
    }
	function checkPostData()
	{
		$fullfree = $_POST['fullfree'];
		
		if(empty($fullfree['fullamount']) && empty($fullfree['fullquantity'])) {
			$this->show_warning('not_allempty');
			exit;
		}
		return TRUE;
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
                'name'  => 'fullfree_setting',
                'url'   => 'index.php?app=seller_fullfree',
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