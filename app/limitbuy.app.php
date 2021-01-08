<?php


class LimitbuyApp extends MallbaseApp
{
    function index()
    {
		$conditions = '';
		if($_GET['cate_id'])
		{
			$cate_id = intval($_GET['cate_id']);
			$conditions = " AND (cate_id_1={$cate_id} OR cate_id_2={$cate_id} OR cate_id_3={$cate_id} OR cate_id_4={$cate_id})";
		} 
		$limitbuy_mod = &m('limitbuy');
		
		$page = $this->_get_page(20);
		$goods_list = $limitbuy_mod->find(array(
			'conditions'=>'start_time <='.gmtime(). ' AND end_time>='.gmtime() . $conditions,
			'join'      =>'belong_goods',
			'fields'    =>'this.*,g.default_image,g.price,g.default_spec,g.goods_name,g.default_spec,g.cate_id_1,g.cate_id_2',
			'limit'     =>$page['limit'],
			'count'     =>true,
			'order'     =>'pro_id DESC'
		));
		
		import('promotool.lib');
		$promotool = new Promotool();
		
		if($goods_list)
		{
			$catList = array();
			foreach ($goods_list as $key => $goods)
			{
				$result = $promotool->getItemProInfo($goods['goods_id'], $goods['default_spec']);
				if($result !== FALSE) {
					$goods_list[$key]['pro_price'] = $result['pro_price'];
				} else $goods_list[$key]['pro_price'] = $goods['price'];
				
				$goods['default_image'] || $goods_list[$key]['default_image'] = Conf::get('default_goods_image');
				
				//  读取促销商品的类目，便于前台筛选
				$catList[$goods['cate_id_1']][] = $goods['cate_id_2'];
				$catList[$goods['cate_id_1']] = array_unique($catList[$goods['cate_id_1']]);
				sort($catList[$goods['cate_id_1']]);
			}
			
			//  读取促销商品的类目，便于前台筛选
			$gcategoryList = array();
			$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
			foreach($catList as $k => $v)
			{
				$gcategory = $gcategory_mod->get(array('conditions' => 'cate_id='.$k, 'fields' => 'cate_id,cate_name'));
				$gcategoryList[$k] = $gcategory;
				foreach($v as $k1 => $v1)
				{
					$gcategory1 = $gcategory_mod->get(array('conditions' => 'cate_id='.$v1, 'fields' => 'cate_id,cate_name'));
					$gcategoryList[$k]['children'][] = $gcategory1;
				}
			}
		}
		
		$page['item_count'] = $limitbuy_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
		
		/* 当前位置 */
        $this->_curlocal($this->_get_limitbuy_curlocal(intval($_GET['cate_id'])));
		 /* 取得导航 */
        $this->assign('navs', $this->_get_navs());
        
        /* 配置seo信息 */
		$this->_config_seo('title', Lang::get('limitbuy') . ' - ' . Conf::get('site_title'));

		$this->assign('goods_list',$goods_list);
		$this->assign('gcategoryList', $gcategoryList);
        $this->display('limitbuy.index.html');
	}
	
	function _get_limitbuy_curlocal($cate_id)
    {
		$curlocal = array();
        if ($cate_id)
        {
			$curlocal = array(
            	array('text' => LANG::get('all_categories'), 'url' => url('app=limitbuy')),
        	);
			
            $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $parents = $gcategory_mod->get_ancestor($cate_id);

			foreach ($parents as $category)
			{
				$curlocal[] = array('text' => $category['cate_name'], 'url' => url('app=limitbuy&cate_id=' . $category['cate_id']));
			}
			unset($curlocal[count($curlocal) - 1]['url']);
		}
        return $curlocal;
    }
}

?>
