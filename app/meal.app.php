<?php

/* 套餐 */
class MealApp extends MallbaseApp
{
    var $_goods_mod;
	var $_meal_mod;
	var $_mealgoods_mod;
	
    function __construct()
    {
        $this->MealApp();
    }
    function MealApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
		$this->_meal_mod  =& m('meal');	
		$this->_mealgoods_mod = &m('mealgoods');
    }

    function index()
    {
        /* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		
		$meal = $this->_meal_mod->findAll(array(
			'conditions' => 'status = 1 AND meal_id='.$id,
			'include' => array('has_mealgoods'),
		));
		if(!$meal) {
			$this->show_warning('meal_not_existed_or_invalid');
			return;
		}
		
		$meal = current($meal);
		$meal_goods = $meal['meal_goods'];
		$price_old_total = $price_save_total = array('min' => 0, 'max' => 0);
		$default_spec_price_total = 0;
		foreach($meal_goods as $key=>$val) 
		{
			$goods = $this->get_specs($val['goods_id']);
			if($goods) 
			{
				empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
				$default_spec_price_total += $goods['price'];
				
				//去重复取得spec_value
				$spec_1 = '';
				$spec_2 = '';
				foreach($goods['gs'] as $k=>$gs)
				{
					$spec_1[$k] = $gs['spec_1'];
					$spec_2[$k] = $gs['spec_2'];
				}
				$goods['spec_1'] = array_unique($spec_1);
				$goods['spec_2'] = array_unique($spec_2);
				
				/* 兼容规格图片功能，给每项增加图片路径，第二个规格不需要图片（BEGIN） */
				$format_spec = array();
				foreach($goods['spec_1'] as $k1 => $v1) {
					$format_spec[$k1] = array('name' => $v1, 'image' => $goods['gs'][$k1]['spec_image']); 
				}
				$goods['spec_1'] = $format_spec;
				
				$format_spec = array();
				foreach($goods['spec_2'] as $k1 => $v1) {
					$format_spec[$k1] = array('name' => $v1);
				}
				$goods['spec_2'] = $format_spec;
				/* 兼容规格图片功能，给每项增加图片路径，第二个规格不需要图片（END） */
				
				/* 找出这个商品的最高价与最低价*/
				$spec_mod = &m('goodsspec');
				$price_data = $spec_mod->_get_spec_min_max($goods['goods_id']);
				$price_old_total['min'] += $price_data['min'];
				$price_old_total['max'] += $price_data['max'];
				
				$meal_goods[$key] = array_merge($meal_goods[$key], $goods);
			}
			else
			{
				$this->_meal_mod->edit($id, array('status' => 0));
				header('Location:index.php?app=meal&id='.$id);
				exit;
			}
		}
		
		/* 判断价格是否合适，如果套餐价格大于原商品总价的最小价格，则认为该套餐价格不合理，设置为无效套餐 */
		if($meal['price'] > $price_old_total['min']) {
			$this->_meal_mod->edit($id, array('status' => 0));
			header('Location:index.php?app=meal&id='.$id);
		}
		
		$meal['default_save'] = price_format($default_spec_price_total - $meal['price']);
		$meal['meal_goods'] = $meal_goods;
		$meal['price_old_total'] = $price_old_total;
		$price_save_total['min'] = ($price_old_total['min']-$meal['price']) > 0 ? $price_old_total['min'] - $meal['price'] : 0;
		$price_save_total['max'] = $price_old_total['max'] - $meal['price'];
		
		$this->assign('meal', $meal);
		
		$_curlocal = array(array(
				'text' => Lang::get('index'),
				'url'  => 'index.php',
			),
  			array(
                'text' => Lang::get('meal'),
                'url'  => 'javascript:;',
			),
			array(
				'text' => Lang::get('meal_detail'),
				'url'  => '',
			),
		);
        $this->assign('_curlocal', $_curlocal);
        $this->_config_seo('title', Lang::get('meal_detail') . ' - ' . Lang::get('meal') . ' - ' . Conf::get('site_title'));
		$this->headtag('<script type="text/javascript" src="{lib file=meal.js}"></script>');
        $this->display('meal.index.html');
    }
	
	function get_specs($goods_id)
	{
		$goods = $this->_goods_mod->findAll(array(
			'conditions'=>'goods_id='.$goods_id,
			'fields'	=>'goods_id,goods_name,default_image,price,spec_name_1,spec_name_2,default_spec,spec_qty',
			'include' 	=> array('has_goodsspec'),
		));
		$goods = current($goods);
		
		return $goods;
	}
	
	function get_specs_json()
	{
		$num    = isset($_GET['num']) ? intval($_GET['num']) : 0;
		$specQty    = isset($_GET['specQty']) ? intval($_GET['specQty']) : 0;
		$goods_id   = isset($_GET['goods_id']) ? intval($_GET['goods_id']) : 0;
        $spec_1   = isset($_GET['spec_1']) ? trim($_GET['spec_1']) : '';
		$spec_2   = isset($_GET['spec_2']) ? trim($_GET['spec_2']) : '';
		if (!$goods_id || !$spec_2 || !$num || !$specQty)
        {
            return;
        }
		$goods = $this->_goods_mod->findAll(array(
			'conditions'=>'goods_id='.$goods_id,
			'fields'	=>'goods_id,goods_name,default_image,price,spec_name_1,spec_name_2,default_spec,spec_qty',
			'include' 	=> array('has_goodsspec'),
		));
		$goods = current($goods);
		foreach($goods['gs'] as $spec)
		{
			//两个属性项 比较两个
			if($num == 2 && $specQty == 2)
			{
				if($spec_1 == $spec['spec_1'] && $spec_2 == $spec['spec_2'])
				{
					 $this->json_result(array('spec'=>$spec));
				}
			}
			else
			{
				if($spec_2 == $spec['spec_1'])
				{
					 $this->json_result(array('spec'=>$spec));
				}
			}
		}
	}
}

?>
