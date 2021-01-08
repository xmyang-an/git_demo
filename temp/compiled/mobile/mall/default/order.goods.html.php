<script type="text/javascript">
$(function(){

	$('.J_UseCouponSelect').on('click', 'dl', function(){
		if(!$(this).hasClass('disabled')) {
			$(this).addClass('active').siblings().removeClass('active');
			$(this).find('input[type="radio"]').prop('checked', true);
			
			fill_order_amount();
		}
	});
	
});	

</script>

<div class="cartbox mt20">
  <div class="content" style="background:#f9f9f9;"> 
    <?php $_from = $this->_var['goods_info']['orderList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('store_id', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['store_id'] => $this->_var['order']):
?>
    <div class="store-each J_Store" store_id="<?php echo $this->_var['store_id']; ?>">
      <div class="store-name clearfix"> <span class="col-desc"> <a href="<?php echo url('app=store&id=' . $this->_var['order']['store_id']. ''); ?>"><i class="psmb-icon-font fs16 mr5">&#xe656;</i><?php echo htmlspecialchars($this->_var['order']['store_name']); ?><i class="psmb-icon-font fs12">&#xe629;</i></a> 
        </span>
      </div>
      <div class="order-goods">
      <?php $_from = $this->_var['order']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
      <dl class="goods-each clearfix J_TouchActive">
        <dd class="pic"><a class="block" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['goods_image']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" width="78" height="78" /></a></dd>
        <dd class="desc">
          <p class="goods-name"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" class="fs13 J_TouchUri line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></a></p>
          <p class="f66 fs12"><?php echo htmlspecialchars($this->_var['goods']['specification']); ?></p>
        </dd>
	
        <dd class="price clearfix"><?php echo price_format($this->_var['goods']['price']); ?> <span class="quantity">x<?php echo $this->_var['goods']['quantity']; ?></span></dd>
      	<dd class="hidden subtotal fs14 J_Subtotal-<?php echo $this->_var['store_id']; ?>" price="<?php echo $this->_var['goods']['subtotal']; ?>"><?php echo price_format($this->_var['goods']['subtotal']); ?></dd>
	  </dl>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      </div>
      
      <?php if ($this->_var['order']['fullgift']): ?>
      <div class="order-fullgift">
        <p class="padding5" style="display:none">
          <label class="subtit" for="fullgift-<?php echo $this->_var['store_id']; ?>_<?php echo $this->_var['order']['fullgift']['psid']; ?>">
            <input type="hidden" name="fullgift" id="fullgift-<?php echo $this->_var['store_id']; ?>_<?php echo $this->_var['order']['fullgift']['psid']; ?>" value="<?php echo $this->_var['order']['fullgift']['psid']; ?>" checked="checked" disabled="disabled" />
            <?php echo sprintf('卖家赠送赠品（购物满 &yen;<em class="f60">%s</em> 元）', $this->_var['order']['fullgift']['rules']['amount']); ?> </label>
        </p>
        <?php $_from = $this->_var['order']['fullgift']['rules']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
        <dl class="goods-each clearfix J_TouchActive" <?php if (! ($this->_foreach['fe_goods']['iteration'] <= 1)): ?> style="border-top:0;"<?php endif; ?>>
          <dd class="pic"><a class="block J_TouchUri" href="<?php echo url('app=gift&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['default_image']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" width="78" height="78" /></a></dd>
          <dd class="desc">
            <p class="goods-name"><a href="<?php echo url('app=gift&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" class="fs13 line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></a></p>
          </dd>
          <dd><em class="gico">赠品</em></dd>
          <dd class="price clearfix">&yen;0.00 <del class="gray pt5 pb5 inline-block"><?php echo price_format($this->_var['goods']['price']); ?></del><span class="quantity">x<?php echo ($this->_var['goods']['quantity'] == '') ? '1' : $this->_var['goods']['quantity']; ?></span></dd>
        </dl>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      </div>
      <?php endif; ?>
      
      <?php if ($this->_var['order']['growbuy_list']): ?>
      <div class="order-growbuy"> 
        <?php $_from = $this->_var['order']['growbuy_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'growbuy');$this->_foreach['fe_growbuy'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_growbuy']['total'] > 0):
    foreach ($_from AS $this->_var['growbuy']):
        $this->_foreach['fe_growbuy']['iteration']++;
?>
        <div class="growbuy-each J_GrowBuyEach">
          <p class="hd padding10" style="display:none">
            <label class="subtit" for="growbuy-<?php echo $this->_var['store_id']; ?>_<?php echo $this->_var['growbuy']['psid']; ?>">
              <input class="J_GrowBuy J_GrowBuy-<?php echo $this->_var['store_id']; ?>" price="<?php echo $this->_var['growbuy']['money']; ?>" type="checkbox" name="growbuy[<?php echo $this->_var['store_id']; ?>][]" id="growbuy-<?php echo $this->_var['store_id']; ?>_<?php echo $this->_var['growbuy']['psid']; ?>" value="<?php echo $this->_var['growbuy']['psid']; ?>" />
              <?php echo sprintf('加价 &yen;<em class="f60">%s</em> 元可购买以下商品', $this->_var['growbuy']['money']); ?> </label>
          </p>
          <?php $_from = $this->_var['growbuy']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
          <dl class="goods-each clearfix" <?php if (! ($this->_foreach['fe_goods']['iteration'] <= 1)): ?> style="border-top:0;"<?php endif; ?>>
            <dd class="pic"><a class="block" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['default_image']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" width="78" height="78" /></a></dd>
            <dd class="desc">
              <p class="goods-name"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" class="fs13 line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></a></p>
            </dd>
            <dd class="overflowHidden clearfix"><em class="gico">加价购</em><span class="xz J_GrowBuyOfMobile">未选购</span></dd>
            <dd class="price clearfix"><?php echo price_format($this->_var['growbuy']['money']); ?> <del class="gray pt5 pb5 inline-block"><?php echo price_format($this->_var['goods']['price']); ?></del><span class="quantity">x<?php echo ($this->_var['goods']['quantity'] == '') ? '1' : $this->_var['goods']['quantity']; ?></span></dd>
          </dl>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      </div>
      <?php endif; ?> 
      
      
      <div class="order-confirm-extra clearfix">
        <div class="confirm-extra-info">
        
          <?php if ($this->_var['shipping_methods'][$this->_var['store_id']]): ?>
          <div class="extra-list ship-method clearfix J_ShippingMethod J_PopLayer" data-PopLayer="{popLayer:'.J_ShippingMethodPopLayer<?php echo $this->_var['store_id']; ?>',top:'35%'}">
            <div class="subtitle float-left mr10">配送方式</div>
            <div class="subcontent float-left hidden"></div>
            <div class="fee float-right"><em class="J_LogistFees-<?php echo $this->_var['store_id']; ?>"></em><i class="psmb-icon-font">&#xe629;</i></div>
          </div>

    	  <div class="pop-layer-common pop-wrap-b shipping-method-pop-layer J_ShippingMethodPopLayer<?php echo $this->_var['store_id']; ?>">
          	 <div class="wraper has-title">
          		<div class="hd"><i class="closed popClosed"></i>配送方式</div>
       	 		<div class="bd radioUiWraper J_LogistFeesSelect J_LogistFeesSelect-<?php echo $this->_var['store_id']; ?>">
 					<?php $_from = $this->_var['shipping_methods'][$this->_var['store_id']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'logist');$this->_foreach['fe_logist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_logist']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['logist']):
        $this->_foreach['fe_logist']['iteration']++;
?>
     				<dl class="webkit-box popClosed radioUiStyle radioUiStyle2 <?php if (($this->_foreach['fe_logist']['iteration'] <= 1)): ?>active<?php endif; ?>" data-value="<?php echo $this->_var['key']; ?>" data-price="<?php echo $this->_var['logist']['logist_fees']; ?>">
         				<dt class="flex1">
                    		<?php echo $this->_var['logist']['name']; ?> <?php if ($this->_var['logist']['logist_fees'] == 0): ?>免邮<?php else: ?><?php echo price_format($this->_var['logist']['logist_fees']); ?><?php endif; ?>
                    	</dt>
                		<dd class="input"><input type="radio" name="delivery_type[<?php echo $this->_var['store_id']; ?>]" value="<?php echo $this->_var['key']; ?>" price="<?php echo $this->_var['logist']['logist_fees']; ?>" <?php if (($this->_foreach['fe_logist']['iteration'] <= 1)): ?>checked="checked"<?php endif; ?>/></dd>
          			</dl>
    				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            	</div>
       		 	<div class="ft pop-btn popClosed">关闭</div>
             </div>
		 </div>
    	 <?php endif; ?>
          
          <?php if ($this->_var['order']['coupon_list']): ?>
          <div class="extra-list use-coupon clearfix J_UseCoupon J_PopLayer" data-PopLayer="{popLayer:'.J_UseCouponPopLayer<?php echo $this->_var['store_id']; ?>',top:'35%'}">
            <div class="subtitle float-left mr10">店铺优惠</div>
            <div class="subcontent float-left hidden"></div>
            <div class="fee float-right"><em class="J_CouponPrice J_CouponPrice-<?php echo $this->_var['store_id']; ?>"></em><i class="psmb-icon-font">&#xe629;</i></div>
          </div>
           
          <div class="pop-layer-common pop-wrap-b use-coupon-pop-layer J_UseCouponPopLayer<?php echo $this->_var['store_id']; ?>">
          	 <div class="wraper has-title">
          		<div class="hd"><i class="closed popClosed"></i>店铺优惠</div>
       	 		<div class="bd radioUiWraper J_UseCouponSelect J_UseCouponSelect-<?php echo $this->_var['store_id']; ?>">
 					<?php $_from = $this->_var['order']['coupon_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'coupon');$this->_foreach['fe_coupon'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_coupon']['total'] > 0):
    foreach ($_from AS $this->_var['coupon']):
        $this->_foreach['fe_coupon']['iteration']++;
?>
     				<dl class="webkit-box popClosed radioUiStyle radioUiStyle2 <?php if (($this->_foreach['fe_coupon']['iteration'] <= 1)): ?>active<?php endif; ?>" data-value="<?php echo $this->_var['coupon']['coupon_sn']; ?>" data-price="<?php echo $this->_var['coupon']['coupon_value']; ?>">
         				<dt class="flex1">省<?php echo $this->_var['coupon']['coupon_value']; ?>元:<?php echo $this->_var['coupon']['coupon_name']; ?></dt>
                		<dd class="input"><input type="radio" name="coupon_sn[<?php echo $this->_var['store_id']; ?>]" value="<?php echo $this->_var['coupon']['coupon_sn']; ?>" price="<?php echo $this->_var['coupon']['coupon_value']; ?>" <?php if (($this->_foreach['fe_coupon']['iteration'] <= 1)): ?>checked="checked"<?php endif; ?>/></dd>
          			</dl>
    				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <dl class="webkit-box popClosed radioUiStyle radioUiStyle2" data-value="" data-price="0">
         				<dt class="flex1">不使用优惠券</dt>
                		<dd class="input"><input type="radio" name="coupon_sn[<?php echo $this->_var['store_id']; ?>]" value="" price="0" <?php if (! $this->_var['order']['coupon_list']): ?>checked="checked"<?php endif; ?>/></dd>
          			</dl>
            	</div>
       		 	<div class="ft pop-btn popClosed">关闭</div>
             </div>
		  </div>
          <?php endif; ?>
          
          <?php if ($this->_var['order']['mealprefer']): ?>
          <div class="extra-list use-mealprefer clearfix J_PopLayer" data-PopLayer="{popLayer:'.J_UseMealPreferPopLayer<?php echo $this->_var['store_id']; ?>',top:'35%'}">
            <div class="subtitle float-left mr10">搭配套餐</div>
            <div class="subcontent float-left"></div>
            <div class="fee float-right"><em class="J_MealPreferPrice J_MealPreferPrice-<?php echo $this->_var['store_id']; ?>" data-price="<?php echo $this->_var['order']['mealprefer']['price']; ?>"><?php echo $this->_var['order']['mealprefer']['text']; ?> -<?php echo price_format($this->_var['order']['mealprefer']['price']); ?></em><i class="psmb-icon-font">&#xe629;</i></div>
          </div>
          
           <div class="pop-layer-common pop-wrap-b use-mealprefer-pop-layer J_UseMealPreferPopLayer<?php echo $this->_var['store_id']; ?>">
          	 <div class="wraper has-title">
          		<div class="hd"><i class="closed popClosed"></i>搭配套餐</div>
       	 		<div class="bd">
 					<p class="pl10 pr10"><?php echo $this->_var['order']['mealprefer']['text']; ?> -<?php echo price_format($this->_var['order']['mealprefer']['price']); ?></p>
            	</div>
       		 	<div class="ft pop-btn popClosed">关闭</div>
             </div>
		 </div>
          <?php endif; ?> 
          
          <?php if ($this->_var['order']['fullprefer']): ?>
          <div class="extra-list use-fullprefer clearfix J_PopLayer" data-PopLayer="{popLayer:'.J_UseFullpreferPopLayer<?php echo $this->_var['store_id']; ?>',top:'35%'}">
            <div class="subtitle float-left mr10">满折满减</div>
            <div class="subcontent float-left">&nbsp;</div>
            <div class="fee float-right"><em class="J_FullPreferPrice J_FullPreferPrice-<?php echo $this->_var['store_id']; ?>" data-price="<?php echo $this->_var['order']['fullprefer']['price']; ?>">优惠 -<?php echo price_format($this->_var['order']['fullprefer']['price']); ?></em><i class="psmb-icon-font">&#xe629;</i></div>
          </div>
          
          <div class="pop-layer-common pop-wrap-b use-fullprefer-pop-layer J_UseFullpreferPopLayer<?php echo $this->_var['store_id']; ?>">
          	 <div class="wraper has-title">
          		<div class="hd"><i class="closed popClosed"></i>满折满减</div>
       	 		<div class="bd">
 					<p class="pl10 pr10"><?php echo $this->_var['order']['fullprefer']['text']; ?></p>
            	</div>
       		 	<div class="ft pop-btn popClosed">关闭</div>
             </div>
		 </div>
          <?php endif; ?>
          
          <div  class="extra-list postscript clearfix webkit-box">
            <div class="subtitle float-left mr10">买家留言</div>
            <div class="subcontent flex1">
              <textarea class="f66 J_Postscript" name="postscript[<?php echo $this->_var['store_id']; ?>]" placeholder="对商品的特殊需求，如颜色、尺码等"></textarea>
            </div>
          </div>
          
        </div>
        <div class="confirm-extra-bottom clearfix"> <span class="price fs13 store-amount-fields">店铺合计（含运费） <i class="float-right">&yen;<em class="J_OrderAmount-<?php echo $this->_var['store_id']; ?>"><?php echo $this->_var['order']['amount']; ?></em></i> </span> </div>
      </div>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
  </div>
</div>
