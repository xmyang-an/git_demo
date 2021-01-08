<?php

/**
 *    购物车控制器，负责会员购物车的管理工作，她与下一步售货员的接口是：购物车告诉售货员，我要买的商品是我购物车内的商品
 *
 *    @author    MiMall
 */

class CartApp extends MallbaseApp
{
    /**
     *    列出购物车中的商品
     *
     *    @author    MiMall
     *    @return    void
     */
    function index()
    {
        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
		
		if(!IS_POST)
		{
			list($carts, $allAmount) = $this->_get_carts($store_id);
			$this->_curlocal(
				LANG::get('cart')
			);
		
			if (empty($carts))
			{
				$this->_cart_empty();
	
				return;
			}
			
			$coupon_mod = &m('coupon');
			
			/* 店铺满折满减 */
			foreach($carts as $store_id => $cart)
			{
				$Promotool_mod = &bm('promotool_setting', array('_store_id' => $store_id, '_appid' => 'fullprefer'));
				if($Promotool_mod->checkAvailable()){
					$fullprefer = $Promotool_mod->get_info();
					if($fullprefer['status']) {
						if($fullprefer['rules']['type']=='discount') {
							$carts[$store_id]['storeFullPreferInfo'] = array(
								'text' => sprintf('购满%s元可享%s折', $fullprefer['rules']['amount'], $fullprefer['rules']['discount']),
								'amount' => $fullprefer['rules']['amount'],
								'detail' => '{discount:'.$fullprefer['rules']['discount'].'}'
							);
						} else $carts[$store_id]['storeFullPreferInfo'] = array(
							'text' => sprintf('购满%s元可减%s元', $fullprefer['rules']['amount'], $fullprefer['rules']['decrease']),
							'amount' => $fullprefer['rules']['amount'],
							'detail' => '{decrease:'.$fullprefer['rules']['decrease'].'}'
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

			$this->assign('myCart', array('carts' => $carts, 'allAmount' => $allAmount));
			
			// 目前弹窗JS只用在选择优惠券
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/cart.js',
						'attr' => '',
					)
				),
			));
			
			$this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('confirm_goods');
			$this->display('cart.index.html');
		}
		else
		{
			$selectedList = array();
			$cart_mod = &m('cart');
			foreach($_POST['buy'] as $rec_id => $val)
			{
				// 过滤掉不是购物车中的商品，或者是购物车中的商品但不是自己的商品
				$rec = $cart_mod->get(array(
					'conditions' => 'rec_id='.$rec_id.' AND user_id='.$this->visitor->get('user_id').' AND session_id="'.SESS_ID.'"',
					'fields'	 => 'rec_id,store_id, goods_id',
				));
				if($rec) {
					$selectedList[] = $rec['rec_id'];
				}
			}
			
			if(empty($selectedList))
			{
				$this->show_warning('select_empty_by_cart');
				return;
			}
			
			// 到此，可以认为是正常的购买数据
			
			//　保存选中的商品
			$carts = $cart_mod->find(array(
				'conditions' => 'user_id='.$this->visitor->get('user_id').' AND session_id="'.SESS_ID.'"',
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
			
			header('Location:index.php?app=order&goods=cart');

		}
    }

    /**
     *    放入商品(根据不同的请求方式给出不同的返回结果)
     *
     *    @author    MiMall
     *    @return    void
     */
    function add()
    {
        $spec_id	= isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
		$selected   = isset($_GET['selected']) ? intval($_GET['selected']) : 0;
        if (!$spec_id || !$quantity)
        {
            return;
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
            $this->json_error('no_such_goods');
            /* 商品不存在 */
            return;
        }

        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
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
        $item_info  = $cart_model->get("spec_id={$spec_id} AND session_id='" . SESS_ID . "'");
        if (!empty($item_info))
        {
			// 如果已经添加过，且购物车中的价格跟现在的一致，以及库存还足够 则修改数量
			if(($item_info['price'] == $spec_info['price']) && ($item_info['goods_name'] == $spec_info['goods_name'])) {
				if($spec_info['stock'] >= ($item_info['quantity'] + $quantity)) {
					$cart_model->edit($item_info['rec_id'], "quantity=quantity+".$quantity);
					$inCartChangeQuantity = TRUE;
				}
				// 库存不足
				//else {
					//$this->json_error('no_enough_goods');
            		//return;
				//}
			}
			// 购物车中的价格过时（或修改过商品名称），删除购物车中的数据
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
				'user_id'       => $this->visitor->get('user_id'),
				'session_id'    => SESS_ID,
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
			$cart_model->edit("session_id='" . SESS_ID . "' AND user_id=".$this->visitor->get('user_id'). ' AND selected=1', array('selected' => 0));
			$cart_model->edit($inCartChangeQuantity ? $item_info['rec_id'] : $rec_id, array('quantity' => $quantity, 'selected' => 1));
		}
		
        $cart_status = $this->_get_cart_status();

        /* 更新被添加进购物车的次数 */
        $model_goodsstatistics =& m('goodsstatistics');
        $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');

        $this->json_result(array(
            'cart'      =>  $cart_status['status'],  //返回购物车状态
        ), 'addto_cart_successed');
    }
	
	function groupbuy()
    {
        $spec_id	= isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
		$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$team_id   = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;
		
        if (!$spec_id || !$quantity || !$id)
        {
            return;
        }
		
		$team_mod = &m('team');
		if($team_id > 0){
			$team = $team_mod->get('team_id='.$team_id.' AND status is NULL');
			if(empty($team)){
				$this->json_error('该团已成团或者已过期！');
				exit;
			}
			
			if($team['user_id'] == $this->visitor->get('user_id')){
				$this->json_error('不能参与自己开的团！');
				exit;
			}
		}
		else{
			$checkExists = $team_mod->get('user_id='.$this->visitor->get('user_id').' AND (status IS NULL OR status in (1,2)) AND group_id=' . $id);
			if(!empty($checkExists)){
				$this->json_error('每个会员仅可以对一个团购活动开一次团');
				exit;
			}
		}

		$groupbuy_mod = &m('groupbuy');
		$group = $groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $id . ' AND gb.state=' . GROUP_ON,
            'join' => 'belong_store',
            'fields' => 'spec_price'
        ));
		
		if (empty($group))
        {
            $this->json_error('no_such_groupbuy');
            return false;
        }

        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id,gs.stock',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_error('no_such_goods');
            /* 商品不存在 */
            return;
        }

        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }
		
        if ($quantity > $spec_info['stock'])
        {
            $this->json_error('no_enough_goods');
            return;
        }
		
		$_SESSION['groupbuy'] = md5(var_export($_GET,true));
		
		$this->json_result('','ok');
    }
	
    /**
     *    丢弃商品
     *
     *    @author    MiMall
     *    @return    void
     */
    function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
        if (!$rec_id)
        {
            return;
        }

        /* 从购物车中删除 */
        $cart_model =& m('cart');
        $droped_rows = $cart_model->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
        if (!$droped_rows)
        {
            return;
        }
		
        /* 返回结果 */
        $dropped_data = $cart_model->getDroppedData();
        $store_id     = $dropped_data[$rec_id]['store_id'];
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'  =>  $cart_status['status'],                      //返回总的购物车状态
            'amount'=>  $cart_status['carts'][$store_id]['amount']   //返回指定店铺的购物车状态
        ),'drop_item_successed');
    }

    /**
     *    更新购物车中商品的数量，以商品为单位，AJAX更新
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function update()
    {
        $spec_id  = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$spec_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }

        /* 判断库存是否足够 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
        if (empty($spec_info))
        {
            /* 没有该规格 */
            $this->json_error('no_such_spec');
            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            /* 数量有限 */
            $this->json_error('no_enough_goods');
            return;
        }

        /* 修改数量 */
        $where = "spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
        $cart_model =& m('cart');
		

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_spec_info = $cart_model->get($where);
        if (empty($cart_spec_info))
        {
            /* 并没有添加该商品到购物车 */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

        /* 修改数量 */
        $cart_model->edit($where, array(
            'quantity'  =>  $quantity,
        ));

        /* 小计 */
        $subtotal   =   $quantity * $cart_spec_info['price'];

        /* 返回JSON结果 */
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
			'price'     =>  $cart_spec_info['price'],
			'quantity'  =>  $quantity,
            'subtotal'  =>  $subtotal,                                  //小计
            'amount'    =>  $cart_status['carts'][$store_id]['amount']  //店铺购物车总计
        ), 'update_item_successed');
    }

    /**
     *    获取购物车状态
     *
     *    @author    MiMall
     *    @return    array
     */
    function _get_cart_status()
    {
        /* 默认的返回格式 */
        $data = array(
            'status'    =>  array(
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
                'kinds'     =>  0,      //总种类
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        list($carts) = $this->_get_carts();
        if (empty($carts))
        {
            return $data;
        }
        $data['carts']  =   $carts;
        foreach ($carts as $store_id => $cart)
        {
            $data['status']['quantity'] += $cart['quantity'];
            $data['status']['amount']   += $cart['amount'];
            $data['status']['kinds']    += $cart['kinds'];
        }

        return $data;
    }

    /**
     *    购物车为空
     *
     *    @author    MiMall
     *    @return    void
     */
    function _cart_empty()
    {
		$this->_config_seo('title', Lang::get('cart') . ' - ' . Conf::get('site_title'));
		$this->_get_curlocal_title('cart');
        $this->display('cart.empty.html');
    }

    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();

        /* 获取所有购物车中的内容 */
        $where_store_id = $store_id ? ' AND cart.store_id=' . $store_id : '';

        /* 只有是自己购物车的项目才能购买 */
        $where_user_id = $this->visitor->get('user_id') ? " AND cart.user_id=" . $this->visitor->get('user_id') : '';
        $cart_model =& m('cart');
        $cart_items = $cart_model->find(array(
            'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_store_id . $where_user_id,
            'fields'        => 'this.*,store.store_name',
            'join'          => 'belongs_to_store',
        ));
        if (empty($cart_items))
        {
            return $carts;
        }
		
		$allAmount = 0;
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            $kinds[$item['store_id']][$item['goods_id']] = 1;

            /* 以店铺ID为索引 */
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts[$item['store_id']]['goods'][]    = $item;
			
			// 购物车中所有商品的总金额
			$allAmount += $item['subtotal'];
        }
		
		
        foreach ($carts as $_store_id => $cart)
        {
            $carts[$_store_id]['kinds'] =   count(array_keys($kinds[$_store_id]));  //各店铺的商品种类数
        }

        return array($carts, $allAmount);
    }
}

?>
