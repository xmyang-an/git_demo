<?php

/**
 *    Desc
 *
 *    @author    mimall
 *    @usage    none
 */
class My_footmarkApp extends MemberbaseApp
{
    function __construct()
    {
        $this->My_footmarkApp();
    }
    function My_footmarkApp()
    {
        parent::__construct();
       
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('my_footmark') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_footmark');
			$this->display('my_footmark.index.html');
		}
		else
		{
			$goods_list = array();
			if(intval($_GET['page']) == 1) {
				$goods_list = $this->_get_goods_history(0, 30);
			}
						
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($goods_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
}

?>
