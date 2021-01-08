<?php

/*
 * @author   Mimall
 * 
 * 满包邮，满折满减， 手机专享等的商品对应表
 */
class Promotool_itemModel extends BaseModel
{
    var $table  = 'promotool_item';
    var $prikey = 'piid';
    var $_name  = 'promotool_item';
}

class Promotool_itemBModel extends Promotool_itemModel
{
	var $_appid;
	var $_store_id;
	
	function get_info($params = array(), $format = TRUE)
	{
		if(empty($this->_appid) || !$this->_store_id) return array();
		
		$conditions = ' AND store_id='.$this->_store_id.' AND appid="'.$this->_appid.'"';
		
		if(is_array($params)) {
			 if(isset($params['conditions']) && !empty($params['conditions'])){
				 $params['conditions'] .= $conditions;
			 }  else $params['conditions'] = ' 1=1 ' . $conditions;
		} elseif($params) {
			$params = $params . $conditions;
		} else $params = ' 1=1 ' . $conditions;
		
		$info = parent::get($params);

		if($info && isset($info['config']) && $info['config'] && $format) {
			$info['config'] = unserialize($info['config']);
		}
		return $info;
	}
	
	/* 保存卖家设置的某个商品对应加价商品（加价购），赠送赠品（满赠）的商品信息/赠品信息 */
	function savePromotoolItem($data)
	{
		if(!isset($data['goods_id']) || empty($data['goods_id'])){
			return FALSE;
		}
		
		$data['appid'] 		= $this->_appid;
		$data['store_id'] 	= $this->_store_id;
		
		if(isset($data['config']) && !empty($data['config'])){
		    
		    $config = serialize($data['config']);
		    
            if($this->_appid == 'exclusive'){	
				if($data['config']['discount']) $data['config']['discount'] = floor(abs($data['config']['discount']) * 10)/10;
                if($data['config']['decrease']) $data['config']['decrease'] = floor(abs($data['config']['decrease']) * 100)/100;
                if(!$data['config']['discount']) unset($data['config']['discount']);
                if($data['config']['discount']) unset($data['config']['decrease']);
		    
                if(!$data['config']['discount'] && !$data['config']['decrease']){
                    $config = '';
                } 
		    }
		    
		    $data['config'] = $config;
		}
		
		if($promotool_item = $this->get_info('goods_id=' . $data['goods_id'])){
			return parent::edit($promotool_item['piid'], $data);
		} else {
			$data['add_time'] = gmtime();
			return parent::add($data);
		}
	}
	
	/* 获取卖家设置的某个商品的优惠价格 */
	function get_limitbuy_price($goods_id, $spec_id = 0)
	{
		$proPrice = FALSE;
		
		$promotool_setting_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
		if($promotool_setting_mod->checkAvailable())
		{
			$item_info = $this->get_info('goods_id='.$goods_id);
			if($item_info)
			{
				// 如果某个商品的配置信息为空，则说明每个商品的配置信息都是一致的，那么就从卖家营销工具综合配置表读取配置（规则）
				if(!$item_info['config']) {
					$promotool_setting = $promotool_setting_mod->get_info();
					$config = $promotool_setting['rules'];
				} else {
				    $config = $item_info['config'];
				}
					
				if($this->_appid == 'exclusive')
				{
				    // 读取该商品原始价格
				    $spec_mod 	= &m('goodsspec');
				    $spec 		= $spec_mod->get(array('conditions'=>'goods_id='.$goods_id.' AND spec_id='.$spec_id,'fields'=>'price'));
				    $price 		= $spec['price'];
				    
				    if(isset($config['discount']) && !empty($config['discount'])) {
                        $proPrice = round($price * $config['discount'] / 1000, 4) * 100;
				    }
				    elseif(isset($config['decrease']) && !empty($config['decrease'])) {
                        $proPrice = $price - $config['decrease'];
	                   if($proPrice < 0) $proPrice = 0;
				    }
				}
			}
		}
		
		return array($proPrice);
	}
}

?>