<script type="text/javascript">
	var shippings = <?php echo $this->_var['shippings']; ?>;
	var addresses = <?php echo $this->_var['addresses']; ?>;
	var integralExchangeRate 	= <?php echo ($this->_var['goods_info']['integralExchange']['rate'] == '') ? '0' : $this->_var['goods_info']['integralExchange']['rate']; ?>;
	var integralMaxPoints 		= <?php echo ($this->_var['goods_info']['integralExchange']['maxPoints'] == '') ? '0' : $this->_var['goods_info']['integralExchange']['maxPoints']; ?>;
	$(function(){
		
		/* 订单总额初始化 */
		fill_order_amount();
		
		/*  收货地址初始化 */
		//fill_address_info($('input[name="addr_id"]').val());
		
		$('.J_LogistFeesSelect').on('click', 'dl', function(){
			$(this).addClass('active').siblings().removeClass('active');
			$(this).find('input[type="radio"]').prop('checked', true);
			
			fill_order_amount();
		});
		
		$('.J_AddressEach').click(function(){
			
			var addr_id = $.trim($(this).attr('data-value'));
			
			/* 选中地址后 */
			select_ship_address(addr_id);
			
			/* 加载该收货地址对应的运费 */
			fill_logist_fee_by_address(addr_id);
						
			/* 赋值收货地址 */
			//fill_address_info(addr_id);
						
			/* 更新订单总额 */
			fill_order_amount();
						
		});
		
		$('.J_GrowBuyOfMobile').click(function(){
			$(this).toggleClass('checked');
			$(this).parents('.J_GrowBuyEach').find('.J_GrowBuy').prop('checked', $(this).hasClass('checked'));
			$(this).html($(this).hasClass('checked') ? '已选购' : '未选购');
			
			if($('.J_GrowBuy').prop('checked') == true) {
				$('.J_UseCouponSelect-'+$(this).parents('.J_Store').attr('store_id')).find('dl:last').addClass('active').siblings().removeClass('active');
				
				$('.J_UseIntegralCheckbox').prop('checked', false);
				$('.J_IntegralAmount').val('').prop('disabled', true);
				$('.J_UseIntegral').find('.J_SwtcherInput').removeClass('checked');
				$('.J_IntegralPrice').html('');
			}
			
			fill_order_amount();
		
			if($(this).hasClass('checked')) {
				layer.open({content:'已选购此商品'});
			} else layer.open({content:'已取消购买此商品'});
		});
		
		$('.J_UseIntegralCheckbox').click(function(){
			$('.J_IntegralAmount').val('');
			if($(this).prop('checked')==true) {
				$('.J_IntegralAmount').val(integralMaxPoints);
			}
			$('.J_IntegralAmount').prop('disabled', $(this).prop('checked')==false);
			fill_order_amount();
		});

		$('.J_IntegralAmount').keyup(function(){
			if(isNaN($(this).val())) {
				layer.open({content: "积分值必须是数字", time: 3});
				$(this).val(integralMaxPoints);  
				$(this).select();
			}
			else if(parseFloat($(this).val()) < 0)
			{
				layer.open({content: "积分值不能为负数", time: 3});
				$(this).val(integralMaxPoints);
				$(this).select();  
			}
			else
			{
				if($(this).val().toString().indexOf('.') > 0)
				{
					// 必须先判断是不是有点，再判断小数点位数，要不JS报错
					if($(this).val().toString().split(".")[1].length > 2)
					{
						$(this).val(number_format($(this).val(), 2));
					}
				}
				else {
					//$(this).val(0); // @edit 2017.10.30
				}
			}
			fill_order_amount();
		});
					
	});
	
	/* 选中地址后 */
	function select_ship_address(addr_id)
	{
		address = addresses[addr_id];
		$('input[name="addr_id"]').val(addr_id);
		$('.J_SelectAddressPop').find('dt b').html('收货人：'+address['consignee']);
		$('.J_SelectAddressPop').find('dt span').html(address['phone_mob'] ? address['phone_mob'] : address['phone_tel']);
		$('.J_SelectAddressPop').find('dd span').html(address['region_name'] + address['address']);	
	}
				
	/* 赋值收货地址 */
	function fill_address_info(addr_id)
	{
		address = addresses[addr_id];
		phone = address['phone_mob'];
		if(phone=='') phone = address['phone_tel'];
		$('.J_AddressDetail').html(address['region_name'] + ' ' + address['address']);
		$('.J_Consignee').html(address['consignee'] + ' ' + phone);
	}
				
	/* 加载该收货地址对应的运费 */
	function fill_logist_fee_by_address(addr_id)
	{
		$('.J_Store').each(function(){
			store_id = $(this).attr('store_id');
			$('.J_LogistFeesSelect-' + store_id).children().remove();
			shipping_data = shippings[store_id][addr_id];
			
			var isfirst = true;
			$.each(shipping_data,function(k,v) {
				//for PC html = '<option value="'+k+'" price="'+v.logist_fees+'">'+v.name+'：'+number_format(v.logist_fees,2)+'</option>';
				
				html = '<dl class="webkit-box border-bottom popClosed radioUiStyle radioUiStyle2 '+(isfirst ? 'active' : '')+'" data-value="'+k+'" data-price="'+v.logist_fees+'">'+
         					'<dt class="flex1">'+v.name+' '+(v.logist_fees > 0 ? price_format(v.logist_fees,2) : '免邮')+'</dt>'+
                			'<dd class="input"><input type="radio" name="delivery_type['+store_id+']" value="'+k+'" price="'+v.logist_fees+'" '+(isfirst ? 'checked="checked"' : '')+'/></dd>'+
          				'</dl>';

				$('.J_LogistFeesSelect-' + store_id).append(html);
				
				if(isfirst) isfirst = false;
			});		
		});
	}
	
	/* 设置总费用 */ 
	function fill_order_amount()
	{
		var order_amount, logist_fee, coupon_value, integral_value, mealprefer_value, fullprefer_value, growbuy_value;
		
		order_amount = integral_value = 0;
	
		$('.J_Store').each(function(index, element){
			store_id = $(this).attr('store_id');
			store_amount = goods_amount = logist_fee = coupon_value = growbuy_value = mealprefer_value = fullprefer_value = 0;
			
			$('.J_Subtotal-'+store_id).each(function(index, element) {
                goods_amount += parseFloat($(this).attr('price'));
            });
			
			$('.J_GrowBuy-' + store_id).each(function(index, element) {
				if($(this).prop('checked') == true) {
					growbuy_value += parseFloat($(this).attr('price'));
					
				}
			});
			
			//  如果存在搭配套餐
			if($('.J_MealPreferPrice-'+store_id).length > 0) {
				mealprefer_value = parseFloat($('.J_MealPreferPrice-'+store_id).attr('data-price'));
			}
			
			// 如果存在满折满减
			if($('.J_FullPreferPrice-'+store_id).length > 0) {
				fullprefer_value = parseFloat($('.J_FullPreferPrice-'+store_id).attr('data-price'));
			}
		
			// for PC logist_fee = parseFloat($('.J_LogistFeesSelect-'+store_id).find('option:selected').attr('price'));
			logist_fee = parseFloat($('.J_LogistFeesSelect-'+store_id).find('dl.active').attr('data-price'));
			$('.J_LogistFees-'+store_id).html($('.J_LogistFeesSelect-'+store_id).find('dl.active').find('dt').html());
			
			store_amount = goods_amount+growbuy_value-mealprefer_value-fullprefer_value+logist_fee;
			
			
			// 店铺优惠券处理
			$('.J_UseCouponSelect-'+store_id+' dl').each(function(index, element) {
                if($(this).attr('data-price') != undefined && (parseFloat($(this).attr('data-price')) > store_amount)) {
					$(this).addClass('disabled').removeClass('active');
					$(this).find('input[type="radio"]').prop('checked', false);
				} else {
					$(this).removeClass('disabled');
				}
            });

			if($('.J_UseCouponSelect-'+store_id).find('dl.active').length > 0){
				$('.J_CouponPrice-'+store_id).html($('.J_UseCouponSelect-'+store_id).find('dl.active').find('dt').html());
				coupon_value = parseFloat($('.J_UseCouponSelect-'+store_id).find('dl.active').attr('data-price'));
				store_amount -= coupon_value;
			}
			else {
				$('.J_UseCouponSelect-'+store_id).find('dl:last').addClass('active');
				$('.J_CouponPrice-'+store_id).html($('.J_UseCouponSelect-'+store_id).find('dl:last').find('dt').html());
				
				$('.J_UseCouponSelect-'+store_id).find('dl:last').val('').attr('price',0).find('input[type="radio"]').prop('checked', true);
			}
			
			store_amount = goods_amount+growbuy_value-mealprefer_value-fullprefer_value+logist_fee-coupon_value;
			
			$('.J_OrderAmount-'+store_id).html(number_format(store_amount.toFixed(2), 2));
			
			order_amount += parseFloat(store_amount);
			
		});

		<?php if ($this->_var['goods_info']['allow_integral']): ?>
		$('.J_UseIntegral').find('.J_SwtcherInput').removeClass('disabled');// init for H5 client only
		if($('.J_UseIntegralCheckbox').prop('checked')==true){
			usePoints = parseFloat($('.J_IntegralAmount').val());
			
			if(usePoints > integralMaxPoints) {
				usePoints = integralMaxPoints;
				layer.open({content: "积分值不合理", time: 3});
				$('.J_IntegralAmount').val(usePoints);
			}
			
			integral_value = (usePoints * integralExchangeRate).toFixed(4);
			if(integral_value > order_amount) {
				integral_value 	= order_amount;
				usePoints		= number_format((integral_value / integralExchangeRate).toFixed(2), 2);
				//layer.open({content: "积分值不合理", time: 3});
				$('.J_IntegralAmount').val(usePoints);
			} 
			integral_value = parseFloat(integral_value).toFixed(2);
			$('.J_IntegralPrice').html('-'+price_format(integral_value, 2));
			
			if(integral_value <=0) $('.J_UseIntegral').find('.J_SwtcherInput').addClass('disabled'); // for H5 client only
			
		} else {
			$('.J_IntegralPrice').html('');
			$('.J_UseIntegral').find('.J_SwtcherInput').removeClass('checked');
			$('.J_UseIntegralCheckbox').attr('checked', false);
			$('.J_IntegralAmount').val('').attr('disabled', true);
		}
		<?php endif; ?>
	
		$('.J_OrderAmount').html(number_format((order_amount-integral_value).toFixed(2), 2));
	}
</script>

<div id="select-address">
	<?php if ($this->_var['my_address']): ?>
	<div class="oldaddress clearfix">
		<?php $_from = $this->_var['my_address']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'address');$this->_foreach['fe_address'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_address']['total'] > 0):
    foreach ($_from AS $this->_var['address']):
        $this->_foreach['fe_address']['iteration']++;
?>
        <?php if (($this->_foreach['fe_address']['iteration'] <= 1)): ?>
		<dl class="clearfix J_SelectAddressPop J_PopLayer" data-PopLayer="{popLayer:'.J_SelectAddressPopLayer', direction:'right',fixedBody:true}">
			<dt class="mb5 clearfix">
            	<i class="psmb-icon-font position" style="margin-bottom:-20px;">&#xe607;</i>
				<input type="hidden" name="addr_id" value="<?php echo $this->_var['address']['addr_id']; ?>" checked="checked" readonly="readonly" />
				<b>收货人：<?php echo htmlspecialchars($this->_var['address']['consignee']); ?></b>
                <span class="float-right"><?php if ($this->_var['address']['phone_mob']): ?><?php echo $this->_var['address']['phone_mob']; ?><?php else: ?><?php echo $this->_var['address']['phone_tel']; ?><?php endif; ?></span>
			</dt>
			<dd class="webkit-box"><span class="flex1"><?php echo $this->_var['address']['region_name']; ?><?php echo htmlspecialchars($this->_var['address']['address']); ?></span> <i class="psmb-icon-font box-align-center">&#xe629;</i></dd>
		</dl>
        <?php endif; ?>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
	<?php endif; ?>
	<div class="use-new-address clearfix">
		<a href="<?php echo url('app=my_address&act=add&ret_url=' . $this->_var['ret_url']. ''); ?>" class="webkit-box" style="color:#222;" ><i class="psmb-icon-font mr5">&#xe695;</i><span class="flex1 btn-new-addr" >使用新地址</span><i class="psmb-icon-font box-align-center mr10">&#xe629;</i></a>
	</div>
   
   	<?php if ($this->_var['my_address']): ?>
	<div class="pop-layer-common pop-wrap-r all-address-list J_SelectAddressPopLayer">
  		<div class="wraper has-title">
      		<div class="hd"><i class="popClosed psmb-icon-font">&#xe628;</i>选择收货地址</div>
       	  	<div class="bd">
 				<?php $_from = $this->_var['my_address']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'address');$this->_foreach['fe_address'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_address']['total'] > 0):
    foreach ($_from AS $this->_var['address']):
        $this->_foreach['fe_address']['iteration']++;
?>
     			<a class="address-each block J_AddressEach popClosed" data-value="<?php echo $this->_var['address']['addr_id']; ?>">
     				<dl <?php if (($this->_foreach['fe_address']['iteration'] == $this->_foreach['fe_address']['total'])): ?> style="border-bottom:0"<?php endif; ?>>
         				<dt class="clearfix mb5"><b><?php echo htmlspecialchars($this->_var['address']['consignee']); ?></b> <span class="float-right"><?php if ($this->_var['address']['phone_mob']): ?><?php echo $this->_var['address']['phone_mob']; ?><?php else: ?><?php echo $this->_var['address']['phone_tel']; ?><?php endif; ?></span></dt>
                		<dd><?php if ($this->_var['address']['setdefault']): ?><em class="f60">[默认地址] </em><?php endif; ?><span><?php echo $this->_var['address']['region_name']; ?><?php echo htmlspecialchars($this->_var['address']['address']); ?></span></dd>
          			</dl>
       			</a>
    			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        	</div>
          	<div class="ft"><a class="pop-btn" href="<?php echo url('app=my_address'); ?>">管理收货地址</a></div>
        </div>
	</div>
    <?php endif; ?>
 
</div>
