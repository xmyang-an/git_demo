<?php

/**
 *    卖家搭配套餐管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class Seller_mealApp extends StoreadminbaseApp
{
    var $_store_id;
    var $_goods_mod;
    var $_store_mod;
	var $_meal_mod;
	var $_mealgoods_mod;
	var $_gcategory_mod;
	var $_uploadedfile_mod;
	var $_spec_mod;
	var $_appmarket_mod;
	var $_appid;

    /* 构造函数 */
    function __construct()
    {
         $this->Seller_mealApp();
    }

    function Seller_mealApp()
    {
        parent::__construct();

        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
		$this->_gcategory_mod = & bm('gcategory', array('_store_id' => $this->_store_id));
		$this->_uploadedfile_mod = &m('uploadedfile');
        $this->_store_mod =& m('store');
		$this->_meal_mod  =& m('meal');
		$this->_mealgoods_mod = &m('mealgoods');
		$this->_spec_mod = &m('goodsspec');
		$this->_appmarket_mod = &m('appmarket');
		$this->_appid = 'meal';
	}

    function index()
    {
		$page   =   $this->_get_page(7);    //获取分页信息
		
		$meal_list = $this->_meal_mod->find(array(
			'conditions'		=>	'user_id = '.$this->_store_id,
			'order'				=>  'meal_id desc',
			'limit'				=>  $page['limit'],
			'count'   			=>  true
		));
		$page['item_count'] = $this->_meal_mod->getCount();
		$this->_format_page($page);
		
		
        /* 当前位置 */
        $this->_curlocal(LANG::get('seller_meal'), 	'index.php?app=seller_meal',
                         LANG::get('meal_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('seller_meal');

        /* 当前所处子菜单 */
        $this->_curmenu('meal_list');
		
		$this->assign('meal_list', $meal_list);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('seller_meal'));
        
		$this->_import_resource();
		$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
		$this->display('seller_meal.index.html');
    }

    function add()
    {
        if (!IS_POST)
        {
			//传给iframe参数belong, item_id
            $this->assign('belong', BELONG_MEAL);
            $this->assign('id', 0);
			
			/* 编辑器图片批量上传器 */
			$this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_MEAL,
                'item_id' => 0,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
            )));
            
			/* 所见即所得编辑器 */
            extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));
			
			/* 取得游离状的图片 */
            $files_belong_meal = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = ' . $this->visitor->get('manage_store') . ' AND belong = ' . BELONG_MEAL . ' AND item_id = 0 ',
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));
			
			
			/* 当前位置 */
        	$this->_curlocal(LANG::get('seller_meal'), 	'index.php?app=seller_meal',
                         LANG::get('add_meal'));

        	/* 当前用户中心菜单 */
       	 	$this->_curitem('seller_meal');
			
            /* 当前所处子菜单 */
            $this->_curmenu('add_meal');
			
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('add_meal'));
            $this->_import_resource();
			$this->assign('store_id', $this->_store_id);
			$this->assign('files_belong_meal', $files_belong_meal);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->display('seller_meal.form.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
			/* 验证套餐宝贝 */
			if(!$this->_check_post_data($_POST, 0)) {
				exit;
			}

			$data = array(
				'user_id'		=> $this->_store_id,
				'title' 		=> html_script(trim($_POST['title'])),
				'price' 		=> $this->_filter_price(trim($_POST['price'])),
				'selected_ids'  => html_script($_POST['selected_ids']),
				'description' 	=> html_script($_POST['description']),
				'status'		=> 1,// 生效
			);
			
			if($meal_id = $this->_meal_mod->add($data)) {
				if ($_POST['file_id']) {
                	$this->_uploadedfile_mod->edit(db_create_in($_POST['file_id'], 'file_id'), array('item_id' => $meal_id));
            	}
				
				$this->show_message('add_ok',
                	'back_list', 'index.php?app=seller_meal',
                	'continue_add', 'index.php?app=seller_meal&amp;act=add'
            	);
			} else {
				$this->show_warning('add_error');
			}
        }
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        if (!IS_POST)
        {
			if(!$id || !($meal = current($this->_meal_mod->findAll(array('conditions'=>'meal_id='.$id.' AND user_id='.$this->_store_id,'include'=>array('has_mealgoods'))))))
            {
                $this->show_warning('no_such_meal');
                return;
            }
			
			foreach($meal['meal_goods'] as $key=>$mg) 
			{
				$goods = $this->_goods_mod->get(array('conditions'=>'goods_id='.$mg['goods_id'],'fields'=>'price,goods_name,default_image'));
				if($goods)
				{
					$mg['goods_name'] || $meal['meal_goods'][$key]['goods_name'] = $goods['goods_name'];
					
					if($goods['default_image']) {
						$meal['meal_goods'][$key]['default_image'] = $goods['default_image'];
					} else $meal['meal_goods'][$key]['default_image'] = Conf::get('default_goods_image');
					
					$price_data =  $this->_spec_mod->_get_spec_min_max($goods['goods_id']);
					if($price_data && ($price_data['min'] < $price_data['max'])) {
						$meal['meal_goods'][$key]['price'] = $price_data['min'].'-'.$price_data['max'];
					} else $meal['meal_goods'][$key]['price'] = $goods['price'];
				}
			}
			
			//传给iframe参数belong, item_id
            $this->assign('belong', BELONG_MEAL);
            $this->assign('id', $id);
			
			/* 编辑器图片批量上传器 */
			$this->assign('editor_upload', $this->_build_upload(array(
                'obj' => 'EDITOR_SWFU',
                'belong' => BELONG_MEAL,
                'item_id' => $id,
                'button_text' => Lang::get('bat_upload'),
                'button_id' => 'editor_upload_button',
                'progress_id' => 'editor_upload_progress',
                'upload_url' => 'index.php?app=swfupload',
                'if_multirow' => 1,
            )));
            
			/* 所见即所得编辑器 */
            extract($this->_get_theme());
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'description',
                'content_css' => SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}" . '/shop.css', // for preview
            )));
			
			/* 取得该套餐描述的图片 */
            $files_belong_meal = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = ' . $this->visitor->get('manage_store') . ' AND belong = ' . BELONG_MEAL . ' AND item_id = ' . $id,
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));
			
            /* 当前位置 */
        	$this->_curlocal(LANG::get('seller_meal'), 	'index.php?app=seller_meal',
                         LANG::get('edit_meal'));

        	/* 当前用户中心菜单 */
       	 	$this->_curitem('seller_meal');
			
            /* 当前所处子菜单 */
            $this->_curmenu('edit_meal');
			
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('edit_meal'));
            $this->_import_resource();
			$this->assign('store_id', $this->_store_id);
			$this->assign('meal', $meal);
			$this->assign('files_belong_meal', $files_belong_meal);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
            $this->display('seller_meal.form.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
            /* 验证套餐宝贝 */
			if(!$this->_check_post_data($_POST, $id)) {
				exit;
			}
	
			$data = array(
				'user_id'		=> $this->_store_id,
				'title' 		=> html_script(trim($_POST['title'])),
				'price' 		=> $this->_filter_price(trim($_POST['price'])),
				'selected_ids'  => html_script($_POST['selected_ids']),
				'description' 	=> html_script($_POST['description']),
				'status'		=> 1,// 生效
			);
			
			$this->_meal_mod->edit_data($id, $data, $id);
			if ($_POST['file_id']) {
				$this->_uploadedfile_mod->edit(db_create_in($_POST['file_id'], 'file_id'), array('item_id' => $id));
			}
				
			$ret_page = empty($_GET['ret_page']) ? 1 : intval($_GET['ret_page']);
			$this->show_message('edit_ok',
				'back_list', 'index.php?app=seller_meal&page='.$ret_page
			);
		}
    }
	
	function drop()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
		if($this->_meal_mod->drop('user_id='.$this->_store_id.' AND meal_id='.$id)){
			$this->json_result(true);
			return;
		}
		$this->json_error(false);
	}

	function gselector()
	{
        /* 搜索条件 */
        $conditions = "1 = 1";
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
	
	/* 验证提交的数据 */
	function _check_post_data($post, $id=0) {
		
		$price 		  = trim($post['price']);
		$title        = trim($post['title']);
		
		if(!$title) {
			$this->show_warning('note_for_title');
			return;
		}
		
		if(!$price || $price <= 0) {
			$this->show_warning('meal_price_gt0');
			return;
		}
			
		if(!isset($post['selected_ids'])) {
			$this->show_warning('add_meal_records');
			return;
		}
		
		$selected_ids = $post['selected_ids'];
		
		/* 搭配宝贝是否属于本店的 */
		if(!$this->_goods_mod->get_filtered_ids($selected_ids)) {
			$this->show_warning('meal_goods_not_you');
			return;
		}
		
		/* 套餐中的宝贝是否处在禁售或者下架中 */
		if($this->_goods_mod->find(array('conditions'=>'(if_show=0 OR closed=1) AND goods_id ' . db_create_in($selected_ids)))){
			$this->show_warning('meal_goods_not_on_sale');
			return;
		}
		
		/* 套餐商品的数量必须在2-10之间 */
		if(!is_array($selected_ids) || count($selected_ids) < 2 || count($selected_ids) >10) {
			$this->show_warning('meal_records_error');
			return;
		}
		
		/* 套餐的价格必须小于原总价的最高价（如果有多个规格的话，就是小于价格最小的总价） */
		$goods_list = $this->_goods_mod->findAll(array(
			'conditions'	=> "goods_id" . db_create_in($selected_ids),
			'fields'		=> "g.goods_id,g.price",
		));
		$total_min = 0;
		foreach($goods_list as $key=>$goods) {
			$price_data = $this->_spec_mod->_get_spec_min_max($goods['goods_id']);
			$price_data && $total_min += $price_data['min'];
		}
		if($price > $total_min) {
			$this->show_warning('meal_price_error');
			return;
		}
		return true;
	}

    function query_goods_info()
    {
        $goods_ids = empty($_GET['goods_id']) ? 0 : trim($_GET['goods_id']);
        if ($goods_ids)
        {
			$ids = array_unique(explode(',', $goods_ids));
            $ids = $this->_goods_mod->get_filtered_ids($ids); // 过滤掉非本店goods_id

			
			$goods_list = $this->_goods_mod->findAll(array(
                'conditions' => "goods_id" . db_create_in($ids),
				'fields' => "g.goods_name,g.goods_id,g.price,g.default_image",
            ));
			foreach($goods_list as $key=>$goods)
			{
				$price_data = $this->_spec_mod->_get_spec_min_max($goods['goods_id']);
				if($price_data && ($price_data['min'] < $price_data['max'])) {
					$goods_list[$key]['price'] = $price_data['min'].'-'.$price_data['max'];
				} else $goods_list[$key]['price'] = $goods['price'];
				
				$goods_list[$key]['goods_name'] = htmlspecialchars($goods['goods_name']); // json need
				$goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
			}
            $this->json_result(array('goods_list'=>$goods_list));
        }
    }
	
    function _import_resource()
    {
		$resource['script'] = array(array( // JQUERY UI
			'path' => 'jquery.ui/jquery.ui.js'
		),
		array( // 对话框
			'attr' => 'id="dialog_js"',
			'path' => 'dialog/dialog.js'
		),
		array(
			'path' => 'seller_meal.js',
			'attr' => 'charset="utf-8"',
		));		
        $this->import_resource($resource);
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
                'name'  => 'meal_list',
                'url'   => 'index.php?app=seller_meal',
            ),
			array(
				'name'  => 'add_meal',
				'url'   => 'index.php?app=seller_meal&act=add',
			),
        );
        if (ACT == 'edit' || ACT == 'drop')
        {
            $menus[] = array(
                'name' => ACT . '_meal',
                'url'  => '',
            );
        }
        return $menus;
    }
	
	/* 异步删除附件 */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
        $file = $this->_uploadedfile_mod->get($file_id);
        if ($file_id && $file['store_id'] == $this->visitor->get('manage_store') && $this->_uploadedfile_mod->drop($file_id))
        {
            $this->json_result('drop_ok');
            return;
        }
        else
        {
            $this->json_error('drop_error');
            return;
        }
    }
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>