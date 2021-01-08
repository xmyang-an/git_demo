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
	var $_meal_mod;
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
		
		$this->_appid = 'meal';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
		$this->_meal_mod  =& m('meal');
		$this->_spec_mod = &m('goodsspec');
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
			
			$this->_config_seo('title', Lang::get('meal_list') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('meal_list');
			$this->display('seller_meal.index.html');
		}
		else
		{
			$page   =   $this->_get_page(intval($_GET['pageper']));    //获取分页信息
			$meal_list = $this->_meal_mod->findAll(array(
				'conditions'		=>	'user_id = '.$this->_store_id,
				'fields' 			=>  'price, user_id, title, status',
				'order'				=>  'meal_id DESC',
				'limit'				=>  $page['limit'],
				'count'   			=>  true,
				'include'			=>  array('has_mealgoods')
			));
			$page['item_count'] = $this->_meal_mod->getCount();
			$this->_format_page($page);
			
			foreach($meal_list as $key1 => $val)
			{
				if(!isset($val['meal_goods'])) {
					unset($meal_list[$key1]);
					continue;
				}
				$meal_list[$key1]['status_label'] = $val['status'] ? Lang::get('available') : Lang::get('unavailable');
				$meal_list[$key1]['quantity'] = count($val['meal_goods']);
				
				$total = array('min' => 0, 'max' => 0);
				foreach($val['meal_goods'] as $key2 => $mg) 
				{
					$goods = $this->_goods_mod->get(array('conditions'=>'goods_id='.$mg['goods_id'],'fields'=>'price,default_image','sort_order'));
					if($goods)
					{
						if($goods['default_image']) {
							$meal_list[$key1]['default_image'] = $goods['default_image'];
						} 
						
						$price_data =  $this->_spec_mod->_get_spec_min_max($goods['goods_id']);
						if($price_data) {
							$total['min'] += $price_data['min'];
							$total['max'] += $price_data['max'];
						}
					}
				}
				empty($meal_list[$key1]['default_image']) && $meal_list[$key1]['default_image'] = Conf::get('default_goods_image');
				
				$meal_list[$key1]['total'] = ($total['min'] != $total['max']) ?  price_format($total['min']) . '~' . price_format($total['max']) : price_format($total['min']);
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($meal_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }

    function add()
    {
        if (!IS_POST)
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
			
            $this->_config_seo('title', Lang::get('add_meal') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('add_meal');
			$this->display('seller_meal.form.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
			/* 验证套餐宝贝 */
			if(($error = $this->_check_post_data($_POST, 0)) !== TRUE) {
				$this->json_error($error);
				return;
			}

			$data = array(
				'user_id'		=> $this->_store_id,
				'title' 		=> html_script(trim($_POST['title'])),
				'price' 		=> $this->_filter_price(trim($_POST['price'])),
				'selected_ids'  => html_script($_POST['selected_ids']),
				'status'		=> 1,// 生效
			);
			
			if(!$this->_meal_mod->add($data)) {
				$this->json_error('add_error');
				return;
			}
			$this->json_result(array('ret_url' => url('app=seller_meal')), 'add_ok');
        }
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);

        if (!IS_POST)
        {
			if(!$id)
            {
                $this->show_warning('no_such_meal');
                return;
            }
			
			$meal = $this->_meal_mod->get($id);
			$this->assign('meal', $meal);
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
			
            $this->_config_seo('title', Lang::get('edit_meal') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('edit_meal');
			$this->display('seller_meal.form.html');
        }
        else
        {
			if(!$id)
            {
                $this->json_error('no_such_meal');
                return;
            }
			
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}
			
            /* 验证套餐宝贝 */
			if(($error = $this->_check_post_data($_POST, 0)) !== TRUE) {
				$this->json_error($error);
				return;
			}
	
			$data = array(
				'user_id'		=> $this->_store_id,
				'title' 		=> html_script(trim($_POST['title'])),
				'price' 		=> $this->_filter_price(trim($_POST['price'])),
				'selected_ids'  => html_script($_POST['selected_ids']),
				'status'		=> 1,// 生效
			);
			
			$this->_meal_mod->edit_data($id, $data, $id);
			$this->json_result(array('ret_url' => url('app=seller_meal')), 'edit_ok');
		}
    }
	
	function drop()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
		if($this->_meal_mod->drop('user_id='.$this->_store_id.' AND meal_id='.$id)){
			$this->json_result('', 'drop_ok');
			return;
		}
		$this->json_error('drop_fail');
	}
	
	/* 验证提交的数据 */
	function _check_post_data($post, $id = 0) {
		
		$price 		  = trim($post['price']);
		$title        = trim($post['title']);
		
		if(!$title) {
			return Lang::get('note_for_title');
		}
		
		if(!$price || $price <= 0) {
			return Lang::get('meal_price_gt0');
		}
			
		if(!isset($post['selected_ids'])) {
			return Lang::get('add_meal_records');
		}
		
		$selected_ids = $post['selected_ids'];
		
		/* 搭配宝贝是否属于本店的 */
		if(!$this->_goods_mod->get_filtered_ids($selected_ids)) {
			return Lang::get('meal_goods_not_you');
		}
		
		/* 套餐中的宝贝是否处在禁售或者下架中 */
		if($this->_goods_mod->find(array('conditions'=>'(if_show=0 OR closed=1) AND goods_id ' . db_create_in($selected_ids)))){
			return Lang::get('meal_goods_not_on_sale');
		}
		
		/* 套餐商品的数量必须在2-10之间 */
		if(!is_array($selected_ids) || count($selected_ids) < 2 || count($selected_ids) >10) {
			return Lang::get('meal_records_error');
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
			return Lang::get('meal_price_error');
		}
		return TRUE;
	}

    function query_goods_info()
    {
        $goods_ids = empty($_GET['goods_id']) ? 0 : trim($_GET['goods_id']);
		$id   = intval($_GET['meal_id']);
        if ($goods_ids || $id)
        {
			$ids = array();
			if($goods_ids) {
				$ids = array_unique(explode(',', $goods_ids));
			}
			else
			{
				$meal = $this->_meal_mod->findAll(array(
					'conditions'		=>	'user_id = '.$this->_store_id.' AND meal_id='.$id,
					'fields' 			=>  'status',
					'include'			=>  array('has_mealgoods')
				));
				if($meal) $meal = current($meal);
				foreach($meal['meal_goods'] as $k => $v) {
					$ids[] = $v['goods_id'];
				}
			}
			
			$goods_list = $this->_goods_mod->findAll(array(
                'conditions' => "goods_id" . db_create_in($ids).' AND store_id='.$this->_store_id,
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
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>