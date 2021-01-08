<?php

/**
 * 商品挂件
 *
 */
class Jd_channel2_special_goodsWidget extends BaseWidget
{
    var $_name = 'jd_channel2_special_goods';
    var $_ttl  = 1800;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$amount = isset($this->options['amount']) ? intval($this->options['amount']) : 9;
			
			$goods_mod    = &m('goods');
			$limitbuy_mod = &m('limitbuy');
			$limitbuyList = $limitbuy_mod->find(array(
				'conditions' => 'start_time < ' . gmtime() . ' AND end_time > ' . gmtime(),
				'limit'		 => $amount, 
			));
			if($limitbuyList)
			{
				foreach($limitbuyList as $key => $limitbuy)
				{
					$goods = $goods_mod->get(array('conditions' => 'goods_id='.$limitbuy['goods_id'], 'fields' => 'default_image, goods_id, goods_name,default_spec, price'));
					
					$limitbuyList[$key] = array_merge($limitbuyList[$key], $goods);
					
					list($proPrice) = $limitbuy_mod->get_limitbuy_price($goods['goods_id'], $goods['default_spec']);
					if($proPrice) {
						$limitbuyList[$key]['pro_price'] = $proPrice;
					}
					unset($limitbuyList[$key]['spec_price']);	
				}
				
				$goods_list = $limitbuyList;
			}
			else
			{
				$recom_mod =& m('recommend');
				$goods_list = $recom_mod->get_recommended_goods($this->options['img_recom_id'], $amount, true, $this->options['img_cate_id']);
			}
			
			$data = array(
				'model_id'			=> mt_rand(),
				'model_name'	 	=> $this->options['model_name'],
				'goods_list'	 	=> $goods_list,
			);
			
			
            $cache_server->set($key, $data, $this->_ttl);
        }

        return $data;
    }

    function get_config_datasrc()
    {
        // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }

    function parse_config($input)
    {
        if ($input['img_recom_id'] >= 0)
        {
            $input['img_cate_id'] = 0;
        }

        return $input;
    }
}

?>