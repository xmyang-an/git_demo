<?php

/**
 *    营销工具类库接口
 *
 *    @author   MiMall
 *    @usage    none
 */
class Promotool extends Object
{
	var $_store_id;
	
	function __construct($config = array())
    {
        foreach($config as $key=>$val) {
			$this->$key = $val;
		}
    }
	
	/* 获取商品详情页显示所有该商品具有的营销工具信息 */
	function getGoodsAllPromotoolInfo($goods_id = 0)
	{
		$data = array();
		
		/* 店铺满包邮 */
		$Promotool_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullfree'));
		if($Promotool_mod->checkAvailable()){
			$fullfree = $Promotool_mod->get_info();
			if($fullfree['status']) {
				if(isset($fullfree['rules']['fullamount'])) {
					$data['storeFullfreeInfo'] = sprintf(Lang::get('free_amount_ship_title'), $fullfree['rules']['fullamount']);
				} else  $data['storeFullfreeInfo'] = sprintf(Lang::get('free_acount_ship_title'), $fullfree['rules']['fullquantity']);
			}
		}
		
		/* 店铺满折满减 */
		$Promotool_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullprefer'));
		if($Promotool_mod->checkAvailable()){
			$fullprefer = $Promotool_mod->get_info();
			if($fullprefer['status']) {
				if($fullprefer['rules']['type']=='discount') {
					$data['storeFullPreferInfo'] = sprintf(Lang::get('fullperfer_discount_title'), $fullprefer['rules']['amount'], $fullprefer['rules']['discount']);
				} else  $data['storeFullPreferInfo'] = sprintf(Lang::get('fullperfer_decrease_title'), $fullprefer['rules']['amount'], $fullprefer['rules']['decrease']);
			}
		}
		
		/* 店铺商品满赠 */
		$Promotool_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullgift'));
		if($Promotool_mod->checkAvailable()){
			$fullgiftList = $Promotool_mod->get_list();
			if($fullgiftList) {
				foreach($fullgiftList as $key => $fullgift) {
					if($fullgift['status']) {
						$data['storeFullGiftList'][$key] = $fullgift['rules'];
					}
				}
			}
		}
		
		/* 店铺商品加价购（排除本商品） */
		$Promotool_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
		if($Promotool_mod->checkAvailable()){
			$growbuyList = $Promotool_mod->getGrowBuyList($goods_id, TRUE);
			foreach($growbuyList as $key => $growbuy) {
				if($growbuy['selected']) {
					$data['goodsGrowbuyList'][$key] = $growbuy;
				}
			}
		}
				
		return $data;
	}
	
	/* 获取订单提交页面显示该订单所有营销工具信息（兼容多店铺合并付款） */
	function getOrderAllPromotoolInfo(&$goods_info = array())
	{
		$order_info = $goods_info['orderList'][$this->_store_id];
		
		/* 获取搭配套餐优惠 */
		if($goods_info['otype'] == 'meal') {
			$goods_info['orderList'][$this->_store_id]['mealprefer']  	= $this->getOrderMealPreferInfo($order_info);
		}
		
		/* 判断商品金额（不含运费）是否满足满折满减优惠 */
		$goods_info['orderList'][$this->_store_id]['fullprefer'] 	= $this->getOrderFullPreferInfo($order_info);
			
		/* 判断商品总额是否满足满赠条件，满足则读取满赠赠品信息 */
		$goods_info['orderList'][$this->_store_id]['fullgift'] 		= $this->getOrderFullgiftInfo($order_info);
			
		/* 判断商品总额是否满足加价够条件，满足则读取加价够商品信息 */
		$goods_info['orderList'][$this->_store_id]['growbuy_list'] 	= $this->getOrderGrowbuyInfo($order_info);
	}
	
	/* 获取某个商品，某个规格的促销价格信息（如限时促销，会员价格，手机专享价） */
	function getItemProInfo($goods_id, $spec_id = 0)
	{
		// 返回结果数组
		$result 	= FALSE; 
		
		// 用于标识是否获取到了优惠价格
		$proPrice 	= FALSE;
		
		if(!$this->_store_id || !$spec_id)
		{
			$goods_mod = &m('goods');
			$goods = $goods_mod->get(array('conditions' => 'goods_id='.$goods_id, 'fields' => 'store_id, default_spec'));
			!$this->_store_id && $this->_store_id = $goods['store_id'];
			!$spec_id && $spec_id = $goods['default_spec'];
		}
		
		// 优先级一：限时促销功能
		if($result === FALSE) {
			$limitbuy_mod = &m('limitbuy');
			list($proPrice, $id) = $limitbuy_mod->get_limitbuy_price($goods_id, $spec_id);
		 	($proPrice !== FALSE) && $result = array('pro_price' => $proPrice, 'pro_type' => 'limitbuy', 'pro_id' => $id);
		}
		
		// 优先级二：手机专享价格
		if($result === FALSE) {
			if(Psmb_init()->check_view_device('', FALSE)) {
				$exclusive_mod = &bm('promotool_item', array('_store_id' => $this->_store_id, '_appid' => 'exclusive'));
				list($proPrice) = $exclusive_mod->get_limitbuy_price($goods_id, $spec_id);
				($proPrice !== FALSE) && $result = array('pro_price' => $proPrice, 'pro_type' => 'exclusive');
			}
		}
		
		return $result;
	}

	/* 获取订单满包邮设置 */
	function getOrderFullfree($goods_info)
	{
		$data = array();
		$fullfree_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullfree'));
		if($fullfree_mod->checkAvailable()){
			$fullfree 	  = $fullfree_mod->get_info();
			if($fullfree['status']) {
				
				if(($goods_info['amount'] >= $fullfree['rules']['fullamount']) && ($fullfree['rules']['fullamount'] > 0)) {
					$data = array('title' => sprintf(Lang::get('free_amount_ship_title'), $fullfree['rules']['fullamount']));
				}
				elseif(($goods_info['quantity'] >= $fullfree['rules']['fullquantity']) && ($fullfree['rules']['fullquantity'] > 0)){
					$data = array('title' => sprintf(Lang::get('free_acount_ship_title'), $fullfree['rules']['fullquantity']));
				}
			}
		}
		return $data;
	}
	
	/* 获取订单搭配套餐优惠 */ 
	function getOrderMealPreferInfo($goods_info)
	{
		$data =  array('text' => Lang::get('submit_order_reduce'), 'price' => $goods_info['oldAmount'] - $goods_info['amount']);
		
		return $data;
	}
	
	
	/* 获取订单是否满足满折满减设置 */
	function getOrderFullPreferInfo($goods_info)
	{
		$data = array();
		$fullprefer_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullprefer'));
		if($fullprefer_mod->checkAvailable()){
			$fullprefer = $fullprefer_mod->get_info();
			if($fullprefer['status']) {
				$amount = $fullprefer['rules']['amount'];
				if($amount <= $goods_info['amount']){
					if($fullprefer['rules']['type'] == 'discount') {
						$decrease = round($goods_info['amount'] * (10 - $fullprefer['rules']['discount'])/10, 2);
						$data = array(
							'text' => sprintf(Lang::get('order_for_fullperfer_discount'), $amount, $fullprefer['rules']['discount']),
							'price'=> $decrease
						); 
					} elseif($fullprefer['rules']['type'] == 'decrease') {
						$decrease = $fullprefer['rules']['decrease'];
						$data = array(
							'text' => sprintf(Lang::get('order_for_fullperfer_decrease'), $amount, $decrease),
							'price'=> $decrease
						); 
					}
				}
			}
		}
		
		return $data;
	}
	
	/* 获取订单满赠赠品列表 */
	function getOrderFullgiftInfo($goods_info)
	{
		$data = array();
		
		$fullgift_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'fullgift'));
		if($fullgift_mod->checkAvailable()){
			$list = $fullgift_mod->get_list();
			$temp = array();
			foreach($list as $key=> $val){
				if($val['status'] && ($val['rules']['amount'] <= $goods_info['amount'])) {
					$temp[$key] = $val;
				}
			}
			
			/* 筛选出满足条件所有记录，满金额最大那条记录 */
			foreach($temp as $key=>$val) {
				if(!$data) $data = $val;
				else {
					if($data['rules']['amount'] < $val['rules']['amount']) {
						$data = $val;
					}
				}
			}
		}
		return $data;
	}
	
	/* 插入订单满赠赠品信息到数据表 */
	function saveOrderFullgiftItem($order_id, $fullgift)
	{
		$goods_items = array();
		foreach($fullgift['rules']['items'] as $key=>$value)
		{
			$goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'price'         =>  $value['price'],
                'quantity'      =>  1,
                'default_image' =>  $value['default_image'],
            );
		}
		$ordergift_mod = &m('ordergift');
        $ordergift_mod->add(addslashes_deep($goods_items)); //防止二次注入
	}
	
	/* 读取订单加价够商品信息 */
	function getOrderGrowbuyInfo($goods_info)
	{
		$data = array();
		
		$growbuy_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
		if($growbuy_mod->checkAvailable()){
			
			$id = array();
			
			// 找出订单中已存在的商品
			foreach($goods_info['items'] as $key=>$val) {
				$id[] = $val['goods_id'];
			}
			
			// 找出订单中所有商品的加价购商品（如果加价购商品是本次订单正常购买商品，则排除之）
			foreach($goods_info['items'] as $key => $val) {
				
				if($growbuy_list = $growbuy_mod->getGrowBuyList($val['goods_id'], $id)) {
					$data = array_merge($data, $growbuy_list);
				}
			}
		}
		
		// 使用新数组存在，排查重复值
		$result = array();
		foreach($data as $key=>$val)
		{
			foreach($val['items'] as $k=>$v) {
				$val['items'][$k]['decrease'] = $v['price'] - $val['money'];
				$val['items'][$k]['subtotal'] = $val['money'];
			}
			$result[$val['psid']] = $val;
		}
		
		return $result;
	}
	
	/* 获取用户最终选择的加价购商品 */
	function getOrderGrowbuyInfoByUserChecked($goods_info, $checkedGrowbuyIds = array())
	{
		if(!$checkedGrowbuyIds) return FALSE;
		
		if(!is_array($checkedGrowbuyIds)) $checkedGrowbuyIds = array($checkedGrowbuyIds);
		
		$result = $userGrowbuyList = array();
		
		if($growbuy_list = $this->getOrderGrowbuyInfo($goods_info)) 
		{
			// 排除不是该订单的非法加价购
			foreach($checkedGrowbuyIds as $k => $v) {
				if(in_array($v, array_keys($growbuy_list))) {
					$userGrowbuyList[$v] = $growbuy_list[$v];
				}
			}
		}
		
		if($userGrowbuyList) {
			
			$result = array('amount' => 0, 'items' => array());
		
			foreach($userGrowbuyList as $key=>$val) 
			{			
				$result['amount'] += $val['money'];
			
				$result['items'] += $val['items'];
			
			}
		}
		
		return $result;
	}
	
	// 保存订单加价购商品项目
	function saveOrderGrowbuyItem($order_id, $userGrowbuyList)
	{
		$goods_items = array();
		foreach($userGrowbuyList as $key => $value)
		{
			$goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['default_spec'],
                'specification' =>  '',//$value['specification'],
                'price'         =>  $value['price'] - $value['decrease'],
                'quantity'      =>  1,
                'goods_image'   =>  $value['default_image'],
            );
		}
		$order_goods_model =& m('ordergoods');
        $order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入
	}
		
}

?>