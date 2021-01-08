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
		$page = $this->_get_page(10);
		
		$growbuyList = $this->_growbuy_mod->get_list(array(
			//'conditions' 	=> '',
			'limit' 		=> $page['limit'],
			'count' 		=> true,	
		));
		$page['item_count'] = $this->_growbuy_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info', $page);
		
		$this->assign('growbuy_list', $growbuyList);
		
				
		/* 当前位置 */
		$this->_curlocal(LANG::get('growbuy'), 'index.php?app=seller_growbuy',
								LANG::get('growbuy_list'));
		
		/* 当前用户中心菜单 */
		$this->_curitem('growbuy');;
		
		/* 当前所处子菜单 */
		$this->_curmenu('growbuy_list');
		
		
		$this->import_resource('seller_growbuy.js');		
       	
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('growbuy') . ' - ' . Lang::get('growbuy_list'));
		$this->assign('store_id', $this->_store_id);
		$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		$this->display('seller_growbuy.index.html');
		
	}

    function add()
    {
		if(!IS_POST)
		{
				
			/* 当前位置 */
			$this->_curlocal(LANG::get('growbuy'), 	'index.php?app=seller_growbuy',
								LANG::get('growbuy_add'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('growbuy');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('growbuy_add');
			
			$this->_import_resource();
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('growbuy') . ' - ' . Lang::get('growbuy_add'));
			$this->assign('store_id', $this->_store_id);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_growbuy.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
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
				$this->show_warning($this->_growbuy_mod->get_error());
				return;
			}
			$this->show_message('add_ok', '', url('app=seller_growbuy'));
			
		}
    }
	function edit()
    {
		$id = intval($_GET['id']);
		if(!$id) {
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		if(!IS_POST)
		{
			$growbuy = $this->_growbuy_mod->get_info($id);
			
			$itemList = array();
			$items = isset($growbuy['rules']['items']) ? $growbuy['rules']['items'] : array();
			foreach($items as $k => $id)
			{
				if($goods = $this->_goods_mod->get(array('conditions' => 'goods_id='.$id.' AND store_id='.$this->_store_id.' AND if_show=1 AND closed=0', 'fields' => 'goods_id, goods_name, price, default_image, store_id'))){
					$goods['price'] = $this->_get_format_price($goods['goods_id'], $goods['price']);
					$itemList[$id] = $goods;
				}
			}
			$growbuy['rules']['items'] = $itemList;
			
			/* 当前位置 */
			$this->_curlocal(LANG::get('growbuy'), 	'index.php?app=seller_growbuy',
								LANG::get('growbuy_edit'));
		
			/* 当前用户中心菜单 */
			$this->_curitem('growbuy');;
		
			/* 当前所处子菜单 */
			$this->_curmenu('growbuy_edit');
			
			$this->_import_resource();
				
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('growbuy') . ' - ' . Lang::get('growbuy_edit'));
			$this->assign('store_id', $this->_store_id);
			$this->assign('growbuy', $growbuy);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_growbuy.form.html');
		}
		else
		{
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
			$post 			= $_POST['growbuy'];
			$status			= intval($_POST['status']);
			
			if($this->checkPostData($id) == TRUE) {
				$post['money'] 	= $this->_filter_price(trim($post['money']));
				$post['items'] 		= html_script($post['selected_ids']);
				unset($post['selected_ids'], $post['price']);
			}
			
			$data = array(
				'store_id'  => $this->_store_id,
				'appid' 	=> $this->_appid,
				'rules' 	=> serialize($post),
				'status' 	=> $status
			);
			
			if(!$this->_growbuy_mod->edit($id, $data) && $this->_growbuy_mod->has_error()) {
				$this->show_warning($this->_growbuy_mod->get_error());
				return;
			}
			$ret_page = intval($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
			$this->show_message('edit_ok', '', url('app=seller_growbuy&page='.$ret_page));	
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
			$this->show_warning('title_empty');
			exit;
		}
		if(strlen($growbuy['title']) > 60) {
			$this->show_warning('title_len_valid');
			exit;
		}
		if(!isset($growbuy['money']) || !is_numeric($growbuy['money']) || floatval($growbuy['money']) < 0) {
			$this->show_warning('grow_money_invalid');
			exit;
		}
		if(!isset($growbuy['selected_ids'])) {
			$this->show_warning('records_invalid');
			exit;
		}
		if(count($growbuy['selected_ids']) != 1) {
			$this->show_warning('records_error');
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
			$this->show_warning('growbuy_money_ge_price');
			exit;
		}
		return TRUE;
	}
	
	function gselector()
	{
        /* 搜索条件 */
        $conditions = " 1 = 1 ";
        if (trim($_GET['goods_name'])){
            $str = "LIKE '%" . trim($_GET['goods_name']) . "%'";
            $conditions .= " AND (goods_name {$str})";
        }
		if (intval($_GET['sgcate_id']) > 0){
            $cate_ids = $this->_gcategory_mod->get_descendant(intval($_GET['sgcate_id']));
        }else {
            $cate_ids = 0;
        }
		
		$page   =   $this->_get_page(5);    //获取分页信息

        /* 取得商品列表 */
		$goods_list = $this->_goods_mod->get_list(array(
            'conditions' => $conditions . ' AND g.if_show=1 AND g.closed=0',
            'order' => 'g.add_time DESC',
            'limit' => $page['limit'],
			'count' => true
        ), $cate_ids);
		
		$page['item_count'] = $this->_goods_mod->getCount();
		$this->_format_page($page);

        foreach ($goods_list as $key => $val)
        {
            $goods_list[$key]['goods_name'] = htmlspecialchars($val['goods_name']);
        }
        $this->json_result(array('goods_list' => $goods_list, 'page_info' => $page));
	}
	
	function query_goods_info()
    {
        $goods_ids = empty($_GET['goods_id']) ? 0 : trim($_GET['goods_id']);
        if ($goods_ids)
        {
			$ids = array_unique(explode(',', $goods_ids));
			
			$goods_list = $this->_goods_mod->findAll(array(
                'conditions' 	=> "goods_id" . db_create_in($ids) . ' AND store_id='.$this->_store_id,
				'fields' 		=> "goods_name,goods_id,price,default_image",
            ));
			foreach($goods_list as $key=>$goods)
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
			$price = $price_data['min'].'-'.$price_data['max'];
		} else $price = $oldPrice;
		
		return $price;
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
                'name'  => 'growbuy_list',
                'url'   => 'index.php?app=seller_growbuy',
            ),
            array(
                'name'  => 'growbuy_add',
                'url'   => 'index.php?app=seller_growbuy&act=add',
            )
        );
		if(ACT == 'edit')
		{
			$menus[] = array(
				'name'  => 'growbuy_edit',
				'url'   => '',
			);
		}
		
        return $menus;
    }
	function _import_resource()
    {
		$resource['script'] = array(array( // JQUERY UI
			'path' => 'jquery.ui/jquery.ui.js'
		),
		array(
			'path' => 'jquery.plugins/jquery.validate.js'
		),
		array( // 对话框
			'attr' => 'id="dialog_js"',
			'path' => 'dialog/dialog.js'
		),
		array(
			'path' => 'seller_growbuy.js',
			'attr' => 'charset="utf-8"',
		));		
        $this->import_resource($resource);
    }
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>