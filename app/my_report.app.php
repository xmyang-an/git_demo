<?php

class My_reportApp extends MemberbaseApp
{
    var $report_mod;
	
    function __construct()
    {
        $this->My_reportApp();
    }
	
    function My_reportApp()
    {
        parent::__construct();
        $this->report_mod = & m('report');
    }
	
    function index()
    {
		$member_mod = &m('member');
		$goods_mod = &m('goods');
		$store_mod = &m('store');
		
        $page =$this->_get_page(10);
		$reports = $this->report_mod->find(array(
			'conditions' => 'user_id='.$this->visitor->get('user_id'),
			'count' => true,
			'limit' => $page['limit']
		));
		
		if(!empty($reports)){
			foreach($reports as $key=>$val){
				$goods = $goods_mod->get(array('conditions'=>$val['goods_id'],'fields'=>'goods_name,default_image'));
				$store = $store_mod->get(array('conditions'=>$val['store_id'],'fields'=>'store_name'));

				$reports[$key]['goods_name'] = $goods['goods_name'];
				$reports[$key]['goods_image'] = $goods['default_image'] ? $goods['default_image'] : Conf::get('default_goods_image');
				$reports[$key]['store_name'] = $store['store_name'];
				
				$reports[$key]['images'] = unserialize($val['images']);
			}
		}
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
                         LANG::get('report_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_report');

        /* 当前所处子菜单 */
        $this->_curmenu('report_list');
        $page['item_count'] = $this->report_mod->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('page_info',$page);
        $this->assign('reports',$reports);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_report'));
        $this->display('my_report.index.html');
    }
	
	function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$id)
        {
            $this->show_warning('no_such_item');
            return;
        }
		
        $ids = explode(',', $id);
		$file_ids = $this->report_mod->reportRelationImages($ids);//举报相关的图片
        $drop_count = $this->report_mod->drop("status IS NULL AND user_id = " . $this->visitor->get('user_id') . " AND report_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* 没有可删除的项 */
            $this->show_warning('no_such_item');

            return;
        }

        if ($this->report_mod->has_error())    //出错了
        {
            $this->show_warning($this->report_mod->get_error());

            return;
        }
		
		if(!empty($file_ids))
		{
			$upload_mod = &m('uploadedfile');
			$upload_mod->drop('file_id '.db_create_in($file_ids));
		}

        $this->show_message('drop_ok');
    }
	
	function ajaxDrop()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$report = $this->report_mod->get("status IS NULL AND user_id = " . $this->visitor->get('user_id') . " AND report_id =".$id);
        if(!$id || empty($report))
        {
            $this->json_error('no_such_item');
            return;
        }
		if($report['status'])
        {
            $this->json_error('cannot_drop');
            return;
        }
		
		$file_ids = $this->report_mod->reportRelationImages($id);//举报相关的图片
        $this->report_mod->drop($id);
        if($this->report_mod->has_error())
        {
			$error = current($this->report_mod->get_error());
			$this->json_error($error['msg']);
			return;
        }
		
		if(!empty($file_ids))
		{
			$upload_mod = &m('uploadedfile');
			$upload_mod->drop('file_id '.db_create_in($file_ids));
		}

        $this->json_result('','drop_ok');
    }
	
    //三级菜单:
    function _get_member_submenu()
    {
        return array(
            array(
                'name' => 'report_list',
                'url' => 'index.php?app=my_report',
            )          
        );
    }
}

?>