<?php

/**
 *    加价购管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_growbuyApp extends StoreadminbaseApp
{
	var $_appid;
    var $_store_id;
    var $_growbuy_mod;
	var $_uploadedfile_mod;
	var $_goods_mod;
	var $_gcategory_mod;
	var $_appmarket_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_growbuyApp();
    }

    function Seller_growbuyApp()
    {
        parent::__construct();
		$this->_appid = 'growbuy';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_growbuy_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		$this->_uploadedfile_mod =& m('uploadedfile');
		$this->_goods_mod = &bm('goods', array('_store_id' => $this->_store_id));
		$this->_gcategory_mod = & bm('gcategory', array('_store_id' => $this->_store_id));
		$this->_appmarket_mod = &m('appmarket');
    }
	
	function index()
	{
		if(!IS_AJAX)
		{
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => ''
					)
				),
				'style' =>  'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			$this->assign('infiniteParams', json_encode($_GET));
		
			$this->_config_seo('title', Lang::get('growbuy') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('growbuy');
			$this->display('seller_growbuy.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$growbuyList = $this->_growbuy_mod->get_list(array(
				//'conditions' 	=> '',
				'limit' 		=> $page['limit'],
				'count' 		=> true,	
				'order'			=> 'psid DESC'
			));
			$page['item_count'] = $this->_growbuy_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($growbuyList), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}

    function add()
    {
		if(!IS_POST)
		{
			$this->assign('store_id', $this->_store_id);
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
			));
				
			$this->_config_seo('title', Lang::get('growbuy') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('growbuy_add');
			$this->display('seller_growbuy.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 			= $_POST['growbuy'];
			$status			= intval($_POST['status']);
			
			if($this->checkPostData(0) == TRUE) {
				$post['money'] 		= $this->_filter_price(trim($post['money']));
				$post['items'] 		= html_script($post['selected_ids']);
				unset($post['selected_ids'], $post['price']);
			}
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status,
				'add_time'	=> gmtime()
			);
			
			if(!$this->_growbuy_mod->add($data) && $this->_growbuy_mod->has_error()) {
				$error = current($this->_growbuy_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}

			$this->json_result(array('ret_url' => url("app=seller_growbuy")),'add_ok');
		}
    }
	function edit()
    {
		$id = intval($_GET['id']);

		if(!IS_POST)
		{
			if(!$id) 
			{
				$this->show_warning('no_such_data');
				return;
			}
		
			$growbuy = $this->_growbuy_mod->get_info($id);
			$this->assign('growbuy', $growbuy);
			$this->assign('store_id', $this->_store_id);
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
			));
			
			$this->_config_seo('title', Lang::get('growbuy_edit') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('growbuy_edit');
			$this->display('seller_growbuy.form.html');
		}
		else
		{
			if(!$id) 
			{
				$this->json_error('no_such_data');
				return;
			}
			
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			$post 			= $_POST['growbuy'];
			$status			= intval($_POST['status']);
			
			if($this->checkPostData($id) == TRUE) {
				$post['money'] 	= $this->_filter_price(trim($post['money']));
				$post['items'] 	= html_script($post['selected_ids']);
				unset($post['selected_ids'], $post['price']);
			}
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status
			);
			
			if(!$this->_growbuy_mod->edit($id, $data) && $this->_growbuy_mod->has_error()) {
				$error = current($this->_growbuy_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$this->json_result(array('ret_url' => url("app=seller_growbuy")), 'edit_ok');	
		}
    }
	
	function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $rows = $this->_growbuy_mod->drop($ids);
        if ($this->_growbuy_mod->has_error())
        {
            $this->show_warning($this->_growbuy_mod->get_error());
            return;
        }

        $this->json_result('', 'drop_ok');
    }
	
	
	function checkPostData($id = 0)
	{
		$growbuy = $_POST['growbuy'];
		
		if(!isset($growbuy['title']) || empty($growbuy['title'])){
			$this->json_error('title_empty');
			exit;
		}
		if(strlen($growbuy['title']) > 60) {
			$this->json_error('title_len_valid');
			exit;
		}
		if(!isset($growbuy['money']) || !is_numeric($growbuy['money']) || floatval($growbuy['money']) < 0) {
			$this->json_error('grow_money_invalid');
			exit;
		}
		if(!isset($growbuy['selected_ids'])) {
			$this->json_error('records_invalid');
			exit;
		}
		if(count($growbuy['selected_ids']) != 1) {
			$this->json_error('records_error');
			exit;
		}
		
		/* 检查各个商品的最低价格总和是否低于加价够的金额（要求加价的金额必须小于商品的最低价格）
		 * 另：这里虽然考虑计算了多个加价够商品的价格总和，但目前功能实例中暂时不支持设置多个加价购商品 
		 */
		$priceTotle = 0;
		$spec_mod   = &m('goodsspec');
		foreach($growbuy['selected_ids'] as $goods_id)
		{
			$price_data = $spec_mod->_get_spec_min_max($goods_id);
			$priceTotle += $price_data['min']; // 取最小的金额
		}
		if($growbuy['money'] > $priceTotle){
			$this->json_error('growbuy_money_ge_price');
			exit;
		}
		return TRUE;
	}
	
	function query_goods_info()
    {
        $goods_id = intval($_GET['goods_id']);
		$id   = intval($_GET['psid']);
        if ($goods_id || $id)
        {
			// 加价购只能选择一个商品，这里保留兼容选择多个商品的写法
			$ids = array();
			if($goods_id) $ids = array_unique(explode(',', $goods_id));
			elseif($id) 
			{
				$growbuy = $this->_growbuy_mod->get_info($id);
				$ids = isset($growbuy['rules']['items']) ? $growbuy['rules']['items'] : array();
			} 
			
			$goods_list = $this->_goods_mod->findAll(array(
                'conditions' 	=> "goods_id" . db_create_in($ids) . ' AND store_id='.$this->_store_id,
				'fields' 		=> "goods_name,goods_id,price,default_image",
            ));
			foreach($goods_list as $key => $goods)
			{
				$goods_list[$key]['price'] = $this->_get_format_price($goods['goods_id'], $goods['price']);
				$goods_list[$key]['goods_name'] = htmlspecialchars($goods['goods_name']); // json need
				$goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
			}
            $this->json_result(array('goods_list' => $goods_list));
        }
    }
	
	function _get_format_price($goods_id = 0, $oldPrice)
	{
		$spec_mod = &m('goodsspec');
		$price_data = $spec_mod->_get_spec_min_max($goods_id);
		if($price_data && ($price_data['min'] < $price_data['max'])) {
			$price = price_format($price_data['min']).'-'.price_format($price_data['max']);
		} else $price = price_format($oldPrice);
		
		return $price;
	}
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>