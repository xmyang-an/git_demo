<?php

class CartApp extends ApibaseApp
{
	function cartitems()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);

		if(empty($user_id))
        {
            $this->json_fail('login_pls');
            return;
        }
		
        $cart_model =& m('cart');
        $cart_items = $cart_model->find(array(
            'conditions'    => 'user_id='.$user_id,
            'fields'        => 'this.*,store.store_name',
            'join'          => 'belongs_to_store',
        ));
		
		$carts = array();
        if (!empty($cart_items))
        {
			$allAmount = 0;
			$kinds = array();
			foreach ($cart_items as $item)
			{
				/* 小计 */
				$item['subtotal']   = $item['price'] * $item['quantity'];
				$kinds[$item['store_id']][$item['goods_id']] = 1;
	
				/* 以店铺ID为索引 */
				empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
				if(stripos($item['goods_image'], '//:') == FALSE) {
					$item['goods_image'] = SITE_URL . '/' .$item['goods_image'];
				}
			
				$carts[$item['store_id']]['store_name'] = $item['store_name'];
				$carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
				$carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
				$carts[$item['store_id']]['goods'][$item['rec_id']]    = $item;
				
				// 购物车中所有商品的总金额
				$allAmount += $item['subtotal'];
			}
			
			$allKind = 0;
			$coupon_mod = &m('coupon');
			
			/* 店铺满折满减 */
			foreach($carts as $store_id => $cart)
			{
				$carts[$store_id]['kinds'] =   count(array_keys($kinds[$store_id]));  //各店铺的商品种类数
				$allKind += $carts[$store_id]['kinds'];
				
				$Promotool_mod = &bm('promotool_setting', array('_store_id' => $store_id, '_appid' => 'fullprefer'));
				if($Promotool_mod->checkAvailable()){
					$fullprefer = $Promotool_mod->get_info();
					if($fullprefer['status']) {
						if($fullprefer['rules']['type']=='discount') {
							$carts[$store_id]['storeFullPreferInfo'] = array(
								'text' => sprintf('购满%s元可享%s折', $fullprefer['rules']['amount'], $fullprefer['rules']['discount']),
								'amount' => $fullprefer['rules']['amount'],
								'detail' => array('discount' => $fullprefer['rules']['discount'])
							);
						} else $carts[$store_id]['storeFullPreferInfo'] = array(
							'text' => sprintf('购满%s元可减%s元', $fullprefer['rules']['amount'], $fullprefer['rules']['decrease']),
							'amount' => $fullprefer['rules']['amount'],
							'detail' => array( 'decrease' => $fullprefer['rules']['decrease'])
						);
					}
				}
				
				// 是否显示领取优惠券按钮
				$coupons = $coupon_mod->find(array(
					'conditions' => 'clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  coupon.end_time > '.gmtime().' AND store_id='.$store_id,
					'limit'      => 1
				));
				
				if($coupons) $carts[$store_id]['couponReceive'] = TRUE;
			}
		}
		
		$this->json_success(array('items' => $carts, 'kinds' => $allKind ? $allKind : 0, 'amount' => $allAmount ? $allAmount :0));
	}
	
	function add()
    {
        $spec_id	= isset($this->PostData['spec_id']) ? intval($this->PostData['spec_id']) : 0;
        $quantity   = isset($this->PostData['quantity']) ? intval($this->PostData['quantity']) : 0;
		$selected   = isset($this->PostData['selected']) ? intval($this->PostData['selected']) : 0;
		$user_id   = isset($this->PostData['user_id']) ? intval($this->PostData['user_id']) : 0;
        if (!$spec_id || !$quantity)
        {
            $this->json_fail('black_hacker');
			exit;
        }
		
		$cart_model =&  m('cart');
		
        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image, gs.spec_1, gs.spec_2, gs.stock, gs.price,gs.spec_image',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_fail('no_such_goods');
            return;
        }

        /* 如果是自己店铺的商品，则不能购买 */
		if ($spec_info['store_id'] == $user_id)
        {
             $this->json_fail('can_not_buy_yourself');
             return;
        }

		
        if ($quantity > $spec_info['stock'])
        {
            $this->json_error('no_enough_goods');
            return;
        }
		
		/* 读取促销价格 */
		import('promotool.lib');
		$promotool = new Promotool(array('_store_id' => $spec_info['store_id']));
		$result = $promotool->getItemProInfo($spec_info['goods_id'], $spec_info['spec_id']);
		if($result !== FALSE) {
			$spec_info['price'] = $result['pro_price'];
		}
		
		/* 是否添加过 */
		$inCartChangeQuantity = FALSE;
        $item_info  = $cart_model->get("spec_id={$spec_id} AND user_id=".$user_id);
        if (!empty($item_info))
        {
			// 如果已经添加过，且购物车中的价格跟现在的一致，以及库存还足够 则修改数量
			if(($item_info['price'] == $spec_info['price']) && ($item_info['goods_name'] == $spec_info['goods_name'])) {
				if($spec_info['stock'] >= ($item_info['quantity'] + $quantity)) {
					$cart_model->edit($item_info['rec_id'], "quantity=quantity+".$quantity);
					$inCartChangeQuantity = TRUE;
				}
				else {
					$this->json_fail('no_enough_goods');
            		return;
				}
			}
			else
			{
				$cart_model->drop($item_info['rec_id']);
			}
        }
		
		// 购物车中无本次购买的商品，加入到购物车
		if(!$inCartChangeQuantity)
		{
			$spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
			$spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];
	
			$specification = $spec_1 . ' ' . $spec_2;
			$goods_image = $spec_info['spec_image'] ? $spec_info['spec_image'] : $spec_info['default_image'];
	
			/* 将商品加入购物车 */
			$cart_item = array(
				'user_id'       => $user_id,
				'store_id'      => $spec_info['store_id'],
				'spec_id'       => $spec_id,
				'goods_id'      => $spec_info['goods_id'],
				'goods_name'    => addslashes($spec_info['goods_name']),
				'specification' => addslashes(trim($specification)),
				'price'         => $spec_info['price'],
				'quantity'      => $quantity,
				'goods_image'   => addslashes($goods_image),
				'selected'		=> $selected,
			);
	
			/* 添加并返回购物车统计即可 */
			$rec_id = $cart_model->add($cart_item);
		}
		
		// 立即购买的操作（确保只购买的是当前立即购买的商品）
		if($selected) {
			$cart_model->edit("user_id=".$user_id. ' AND selected=1', array('selected' => 0));
			$cart_model->edit($inCartChangeQuantity ? $item_info['rec_id'] : $rec_id, array('quantity' => $quantity, 'selected' => 1));
		}

        /* 更新被添加进购物车的次数 */
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');

        $this->json_success('', '已加入购物车');
    }
	
	function update()
    {
        $spec_id  = isset($this->PostData['spec_id']) ? intval($this->PostData['spec_id']) : 0;
        $quantity = isset($this->PostData['quantity'])? intval($this->PostData['quantity']): 0;
		$user_id   = isset($this->PostData['user_id']) ? intval($this->PostData['user_id']) : 0;
        if (!$spec_id || !$quantity)
        {
            $this->json_fail('black_hacker');
			exit;
        }

        /* 判断库存是否足够 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
        if (empty($spec_info))
        {
            /* 没有该规格 */
            $this->json_fail('no_such_spec');
            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            /* 数量有限 */
            $this->json_fail('no_enough_goods');
            return;
        }

        /* 修改数量 */
        $where = "spec_id={$spec_id} AND user_id=".$user_id;
        $cart_model =& m('cart');
		

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_spec_info = $cart_model->get($where);
        if (empty($cart_spec_info))
        {
            $this->json_fail('black_hacker');
            return;
        }
		
        /* 修改数量 */
        $cart_model->edit($where, array(
            'quantity'  =>  $quantity,
        ));
		
		$subtotal   =   $quantity * $cart_spec_info['price'];

        $data = array(
			'price'     =>  $cart_spec_info['price'],
			'quantity'  =>  $quantity,
            'subtotal'  =>  $subtotal
        );

        $this->json_success($data,'update_item_successed');
    }
	
	function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $rec_id = isset($this->PostData['rec_id']) ? intval($this->PostData['rec_id']) : 0;
		$user_id   = isset($this->PostData['user_id']) ? intval($this->PostData['user_id']) : 0;
        if (!$rec_id)
        {
			$this->json_fail('no_such_spec');
            return;
        }

        /* 从购物车中删除 */
        $cart_model =& m('cart');
        $droped_rows = $cart_model->drop('rec_id=' . $rec_id . ' AND user_id='.$user_id, 'store_id');
        if (!$droped_rows)
        {
			$this->json_fail('drop_fail');
            return;
        }
		
		$this->json_success('','已删除');
    }
	
	function order()
	{
		$buy = isset($this->PostData['buy']) ? $this->PostData['buy'] : array();
		$user_id   = isset($this->PostData['user_id']) ? intval($this->PostData['user_id']) : 0;
		if(!$user_id){
			$this->json_fail('login_pls');
			exit;
		}
		
		if(!$buy){
			$this->json_fail('select_empty_by_cart');
			exit;
		}
		
		$rec_ids = array_filter(explode(',', $buy));
		if(count($rec_ids) <= 0){
			$this->json_fail('select_empty_by_cart');
			exit;
		}
		
		$selectedList = array();
		$cart_mod = &m('cart');
		foreach($rec_ids as $rec_id)
		{
			// 过滤掉不是购物车中的商品，或者是购物车中的商品但不是自己的商品
			$rec = $cart_mod->get(array(
				'conditions' => 'rec_id='.$rec_id.' AND user_id='.$user_id,
				'fields'	 => 'rec_id,store_id, goods_id',
			));
			
			if($rec) {
				$selectedList[] = $rec['rec_id'];
			}
		}
			
		if(empty($selectedList))
		{
			$this->json_fail('select_empty_by_cart');
			return;
		}
			
			
		//　保存选中的商品
		$carts = $cart_mod->find(array(
			'conditions' => 'user_id='.$user_id,
			'fields'	 => 'rec_id',
		));
		
		foreach($carts as $rec_id => $val)
		{
			$selected = 0;
			if(in_array($rec_id, $selectedList)) {
				$selected = 1;
			}
			
			$cart_mod->edit($rec_id, array('selected' => $selected));
		}
		
		$this->json_success($selectedList);
		
	}
}

?>
