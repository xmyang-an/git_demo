<?php

/*
 * @author   Mimall
 * 
 * 满包邮，满折满减， 手机专享等的综合设置表
 */
class Promotool_settingModel extends BaseModel
{
    var $table  = 'promotool_setting';
    var $prikey = 'psid';
    var $_name  = 'promotool_setting';
}

class Promotool_settingBModel extends Promotool_settingModel
{
	var $_appid;
	var $_store_id;
	
	/* 获取某个卖家设置的营销工具详细信息，并格式化配置项 */
	function get_info($params = array(), $format = TRUE)
	{
		if(empty($this->_appid) || !$this->_store_id) return array();
		
		$conditions = ' AND store_id='.$this->_store_id.' AND appid="'.$this->_appid.'"';
		
		if(is_array($params)) {
			 if(isset($params['conditions']) && !empty($params['conditions'])){
				 $params['conditions'] .= $conditions;
			 } else $params['conditions'] = ' 1=1 ' . $conditions;
		} elseif(is_numeric($params)) { 
			$params = 'psid='.$params; // 键值
		} elseif($params){
			$params = $params . $conditions;
		} else $params = ' 1=1 ' . $conditions;
		
		$info = parent::get($params);

		if($info && isset($info['rules']) && $info['rules'] && $format) {
			$info['rules'] = unserialize($info['rules']);
		}
		return $info;
	}
	
	/* 获取某个卖家设置的营销工具列表，并格式化配置项 */
	function get_list($params = array())
	{
		$conditions = ' store_id=' . $this->_store_id . ' AND appid="' . $this->_appid . '"';
		if(isset($params['conditions']) && !empty($params['conditions'])) {
			$conditions = ' AND ' . $conditions;
		}
		$params['conditions'] .= $conditions;
			
		$list = parent::find($params);
		foreach($list as $key=>$val)
		{
			if($val['rules']) 
			{
				$items 		= array();
				$if_show 	= 0;
				$rules = unserialize($val['rules']);
				if(isset($rules['items']) && is_array($rules['items'])) {
					foreach($rules['items'] as $v) {
						if($item = $this->get_rules_item($v)) {
							if($item['available']) $if_show++;
							unset($item['available']);
							$items[$v] = $item;
						}
					}
					if(!$if_show) 
					{
						$list[$key]['status'] = 0; //  当所有项目都是下架状态，则该营销工具不可用
						parent::edit($val['psid'], array('status' => 0));
					}
				}
				$list[$key]['rules'] = array(
					'title' => $rules['title'], 'amount' => $rules['amount'], 'money' => $rules['money'], 
					'items' => $items
				);
			}
		}
		return $list;
	}
	
	/* 获取卖家设置的营销工具中的每一项配置的值 */
	function get_rules_item($goods_id = 0)
	{
		$item = Psmb_init()->getRulesItem($goods_id, $this->_appid);
		return $item;
	}
	
	/* 检查卖家设置的该营销工具是否可用，并且还在购买期限内 */
	function checkAvailable()
	{
		$result = Psmb_init()->checkAvailable($this->_store_id, $this->_appid);
		return $result;
	}
	
	/* 排查不是本店铺的加价购ID */
	function growbuyDataExclude($data = array())
	{		
		$result = array();
		
		if($data)
		{
			$goods_mod = &m('goods');
			foreach($data as $key => $id)
			{
				if(parent::get(array('conditions' => 'appid="' . $this->_appid . '" AND store_id=' . $this->_store_id . ' AND status = 1', 'fields'=>'psid'))){
					$result[] = $id;
				}
			}
		}
		return $result;
	}
	
	/* 获取可供选择的加价购商品列表 （订单页，发布商品都会用到） */
	function getGrowBuyList($goods_id = 0, $removeIds = array(), $removeUnSelected = TRUE)
	{
		if($removeIds === TRUE) {
			$removeIds = array($goods_id); // TRUE 表示从加价够列表中排除本商品
		} elseif(!is_array($removeIds)) $removeIds = array($removeIds);
		
		// 可供选择的加价购商品列表
		$growListUsable = array();
		
		/* 获取该商品已经存在的设置 */
		$toolId = array();
		if($goods_id)
		{
			$promotool_item_mod = &bm('promotool_item', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
			if($item = $promotool_item_mod->get_info(array('conditions'=>'goods_id=' . $goods_id))){
				if($item['status'] && $item['config']) {
					if(isset($item['config']['toolId']) && !empty($item['config']['toolId'])) {
						$toolId = $item['config']['toolId'];
					}
				}
			}
			
			// 商品订单页(不含未选中的项)
			if($removeUnSelected === TRUE) 
			{
				$growListUsable = $this->get_list(array('conditions' => 'psid ' . db_create_in($toolId)));
			}
			// 商品编辑页(需要包含未选中的项)
			else
			{
				$growListUsable = $this->get_list();
			}
		}
		else
		{
			// 发布商品页，获取店铺所有加价购商品（排查本商品）
			$growListUsable = $this->get_list(); 
		}
		
		$datalist = array();
		foreach($growListUsable as $key => $val)
		{
			if($val['status'])
			{
				$selected = 0;
				$items = array();
				foreach($val['rules']['items'] as $k => $item)
				{
					if(!in_array($k, $removeIds)) {
						$items[$k] = $item;
					}
				}
				if($items) {
					if(in_array($key, $toolId)) $selected = 1;
					$datalist[$key] = array('psid' => $key, 'money' => $val['rules']['money'], 'items' => $items, 'selected' => $selected);
				}
			}
		}
		return $datalist;
	}
	function getExclusive($goods_id = 0)
	{
		$data = $this->get_info();
		if($data && $data['status']) 
		{
			$desc = '';
			if($data['rules']['discount']) {
				$data['discount'] = $data['rules']['discount'];
				$desc = sprintf('开启后，通过手机下单，可享%s折优惠（默认）', $data['discount']);
			} else {
				$data['decrease'] = $data['rules']['decrease'];
				$desc = sprintf('开启后，通过手机下单，可立减%s元（默认）', $data['decrease']);
			}
			unset($data['rules'], $data['status']);
			$data['desc'] = $desc;
		}
		
		/* 设置选中状态 */
		if($goods_id) {
			$data['selected'] = 0;
			
			$promotool_item_mod = &bm('promotool_item', array('_store_id' => $this->_store_id, '_appid' => $this->_appid));
			if($item = $promotool_item_mod->get_info(array('conditions'=>'goods_id=' . $goods_id, 'fields' => 'config, status'), TRUE)){
				$item['status'] && $data['selected'] = 1;
				if(!$item['config']) $item['config'] = array();
                $data = array_merge($data, $item);
			}
		}
		return $data;
	}
}

?>