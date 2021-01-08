<?php


class Jd_limitbuyWidget extends BaseWidget
{
    var $_name = 'jd_limitbuy';
    var $_ttl  = 86400;
    var $_num;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$goodsstatistics_mod = &m('goodsstatistics');
            $limitbuy_mod = &m('limitbuy');
			
			//$this->_num = $this->options['amount'] ? intval($this->options['amount']) : 2;
			if($this->options['goods_id']) {
				$conditions = ' AND g.goods_id='.intval($this->options['goods_id']);
			}
			
			$limitbuy_list = $limitbuy_mod->find(array(
				'conditions'=> 'start_time <='.gmtime().' AND end_time >='.gmtime() . $conditions,
				'join' 		=> 'belong_goods',
				'order' 	=> 'pro.pro_id DESC',
				'limit' 	=> 1,
				'fields' 	=> 'pro.*,g.goods_name,g.default_image,g.price,g.default_spec',
			));
			
			import('promotool.lib');
			$promotool = new Promotool();
		
			foreach ($limitbuy_list as $key => $limitbuy)
			{		
				$result = $promotool->getItemProInfo($limitbuy['goods_id'], $limitbuy['default_spec']);
				if($result !== FALSE) {
					$limitbuy_list[$key]['pro_price'] = $result['pro_price'];
				}
	
				if($limitbuy['image']) {
					$limitbuy_list[$key]['default_image'] = $limitbuy['image'];
				}
				else {
					$limitbuy['default_image'] || $limitbuy_list[$key]['default_image'] = Conf::get('default_goods_image');
				}
				
				$limitbuy_list[$key]['lefttime'] = Psmb_init()->lefttime($limitbuy['end_time']);
				
				/* 判断状态 */
				//$limitbuy_list[$key]['status'] = Lang::get($limitbuy_mod->get_limitbuy_status($limitbuy, true));
				
				// 读取商品的销量
				$goodsstatistics = $goodsstatistics_mod->get(array('conditions' => 'goods_id='.$limitbuy['goods_id'], 'fields' => 'sales'));
				if($goodsstatistics) {
					$limitbuy_list[$key] = array_merge($limitbuy_list[$key], $goodsstatistics);
				}
			}
		
			$data = array(
				'model_id' 		=> mt_rand(), 
				'model_name' 	=> $this->options['model_name'] ? $this->options['model_name'] : '限时抢购',
				'limitbuy_list' => $limitbuy_list
			);
			
            $cache_server->set($key, $data, $this->_ttl);
        }
		return $data;
    }


}
?>