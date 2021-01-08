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
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe-ui-default.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe.init.js',
						'attr' => '',
					)
				),
				'style' =>  'mobile/photoswipe/css/photoswipe.css,mobile/photoswipe/css/default-skin/default-skin.css',
			));
			
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_curlocal_title('my_report');
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_report'));
        	$this->display('my_report.index.html');
		}
		else
		{
			$member_mod = &m('member');
			$goods_mod = &m('goods');
			$store_mod = &m('store');
			
			$page = $this->_get_page(intval($_GET['pageper']));
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
					
					$images = unserialize($val['images']);
					$reports[$key]['images'] = array();
					if(!empty($images))
					{
						foreach($images as $k=>$image)
						{
							$file = ROOT_PATH.'/'.$image;
							if(file_exists($file))
							{
								$image_info = getimagesize($file);
								$reports[$key]['images'][$k]['url'] = $image;
								$reports[$key]['images'][$k]['data_size'] = implode('x', array($image_info[0],$image_info[1]));
							}
						}
					}
					
					$reports[$key]['add_time'] = local_date('Y-m-d H:i:s', $val['add_time']);
				}
			}
		   
			$page['item_count'] = $this->report_mod->getCount();   //获取统计的数据
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($reports), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	function drop()
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

}

?>