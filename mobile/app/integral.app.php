<?php


class IntegralApp extends MallbaseApp
{
	var $_integral_mod;
	
	function __construct()
	{
		parent::__construct();
		$this->_integral_mod = &m('integral');
	}
	
    function index()
    {
		if(!$this->_integral_mod->_get_sys_setting('integral_enabled')){
			$this->show_warning('integral_disabled');
			exit;
		}
		
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js,mobile/search_goods.js'));
			$this->assign('infiniteParams', json_encode($_GET));
        
			/* 配置seo信息 */
			$this->_config_seo($this->_get_seo_info('integral_list'));
			$this->_get_curlocal_title('integral_list');
			$this->display('integral.index.html');
		}
		else
		{
			$orders = array('add_time asc','sales desc', 'price desc');
						
			$page = $this->_get_page(intval($_GET['pageper']));
			$goods_mod = &m('goods');
			$goods_list = $goods_mod->find(array(
				'conditions'=>'gi.max_exchange > 0 ',
				'join'      =>'has_goodsintegral,has_goodsstatistics,belongs_to_store',
				'fields'    =>'gi.max_exchange,g.default_image,g.goods_name,g.price,s.store_name,s.store_id,goods_statistics.sales',
				'limit'     =>$page['limit'],
				'count'     =>true,
				'order'   	=> empty($_GET['order']) || !in_array($_GET['order'], $orders) ? 'gi.max_exchange DESC' : $_GET['order'],
			));
			if($goods_list)
			{
				$rate = $this->_integral_mod->_get_sys_setting('exchange_rate');
				foreach($goods_list as $key=>$goods){
					empty($goods['default_image']) && $goods_list[$key]['default_image']=Conf::get('default_goods_image');
					$goods_list[$key]['exchange'] = $goods['max_exchange'];
					$goods_list[$key]['exchange_price'] = $goods['max_exchange'] * $rate;
					$price = $goods['price'] - $goods_list[$key]['exchange_price'];
					if($price < 0) {
						$goods_list[$key]['exchange'] = round($goods['price'] / $rate, 2);
						$goods_list[$key]['exchange_price'] = $goods['price'];
						$price = 0;
					}
					$goods_list[$key]['price'] = $price;
				} 
			}
			$page['item_count'] = $goods_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($goods_list), 'totalPage' => $page['page_count']);

			echo json_encode($data);
		}
	}
	 function _get_seo_info($type)
    {
        $seo_info = array();
        $seo_info['title'] = Lang::get($type) . ' - ' .Conf::get('site_title');
        $seo_info['keywords'] = Conf::get($type);
        $seo_info['description'] = Lang::get($type) . ' - ' .Conf::get('site_title');
        return $seo_info;
    }
}

?>
