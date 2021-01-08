<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mlselection.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'dialog/dialog.js'; ?>" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.ui/jquery.ui.js'; ?>" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'member.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
	var shippings = <?php echo $this->_var['shippings']; ?>;
	var addresses = <?php echo $this->_var['addresses']; ?>;
	var integralExchangeRate 	= <?php echo ($this->_var['goods_info']['integralExchange']['rate'] == '') ? '0' : $this->_var['goods_info']['integralExchange']['rate']; ?>;
	var integralMaxPoints 		= <?php echo ($this->_var['goods_info']['integralExchange']['maxPoints'] == '') ? '0' : $this->_var['goods_info']['integralExchange']['maxPoints']; ?>;
	$(function(){
		
		/* 订单总额初始化 */
		fill_order_amount();
					
		/*  收货地址初始化 */
		fill_address_info($('.J_AddressEach').find('input[name="addr_id"]').val());
					 
		$('.J_LogistFeesSelect').on('change', function(){
			fill_order_amount();
		});
		
		$('.J_AddressEach').click(function(){
			$(this).parent().children().removeClass('selected_address');
			$(this).addClass('selected_address');
			$(this).parent().children().find('input[name="addr_id"]').prop('checked' ,false);
			$(this).find('input[name="addr_id"]').prop('checked', true);
			
			var addr_id = $(this).find('input[name="addr_id"]').val();
						
			/* 加载该收货地址对应的运费 */
			fill_logist_fee_by_address(addr_id);
						
			/* 赋值收货地址 */
			fill_address_info(addr_id);
						
			/* 更新订单总额 */
			fill_order_amount();
						
		});
		
		$('.J_GrowBuy').click(function(){
			
			// 重置优惠券和积分
			if($(this).prop('checked') == false) {
				$('.J_UseCouponSelect-'+$(this).parents('.J_Store').attr('store_id')).prop('selectedIndex', 0);
				$('.J_UseIntegralCheckbox').prop('checked', false);
				$('.J_IntegralAmount').val('').prop('disabled', true);
				$('.J_IntegralPrice').html('0.00');
			}
			fill_order_amount();
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
				alert('积分值必须是数字');
				$(this).val(integralMaxPoints);  
				$(this).select();
			}
			else if(parseFloat($(this).val()) < 0)
			{
				alert('积分值不能为负数');
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
					// $(this).val(0); // @edit 2017.10.30
				}
			}
			fill_order_amount();
		});
	});
				
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
				
			$.each(shipping_data,function(k,v) {
				html = '<option value="'+k+'" price="'+v.logist_fees+'">'+v.name+'：'+number_format(v.logist_fees,2)+'</option>';
				$('.J_LogistFeesSelect-' + store_id).append(html);
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
		
			logist_fee = parseFloat($('.J_LogistFeesSelect-'+store_id).find('option:selected').attr('price'));
			$('.J_LogistFees-'+store_id).html(number_format(logist_fee, 2));
			
			store_amount = goods_amount+growbuy_value-mealprefer_value-fullprefer_value+logist_fee;
			
			$('.J_UseCouponSelect-'+store_id+' option').each(function(index, element) {
                if($(this).attr('price') != undefined && (parseFloat($(this).attr('price')) > store_amount)) {
					$(this).prop('disabled', true);
				} else {
					$(this).prop('disabled', false);
				}
            });

			if($('.J_UseCouponSelect-'+store_id).val() != ''){
				coupon_value = parseFloat($('.J_UseCouponSelect-'+store_id).find('option:selected').attr('price'));
				store_amount -= coupon_value;
			}
			
			store_amount = goods_amount+growbuy_value-mealprefer_value-fullprefer_value+logist_fee-coupon_value;
			
			$('.J_OrderAmount-'+store_id).html(number_format(store_amount.toFixed(2), 2));
			
			order_amount += parseFloat(store_amount);
			
		});

		<?php if ($this->_var['goods_info']['allow_integral']): ?>
		if($('.J_UseIntegralCheckbox').prop('checked')==true && $('.J_IntegralAmount').val()>0){
			usePoints = parseFloat($('.J_IntegralAmount').val());
			
			if(usePoints > integralMaxPoints) {
				usePoints = integralMaxPoints;
				$('.J_IntegralAmount').val(usePoints);
			}
			
			integral_value = (usePoints * integralExchangeRate).toFixed(4);
			if(integral_value > order_amount) {
				integral_value 	= order_amount;
				usePoints		= number_format((integral_value / integralExchangeRate).toFixed(2), 2);
				$('.J_IntegralAmount').val(usePoints);
			} 
			integral_value = parseFloat(integral_value).toFixed(2);
			$('.J_IntegralPrice').html(number_format(integral_value, 2));
			
		} else $('.J_IntegralPrice').html('0.00');
		<?php endif; ?>
	
		$('.J_OrderAmount').html(number_format((order_amount-integral_value).toFixed(2), 2));
	}
</script>

<div id="select-address" class="w mt20">
	<div class="title w mb10">
		<b class="fs14">收货人地址</b> <a href="<?php echo url('app=my_address'); ?>" target="_blank">[管理收货地址]</a> </div>
	<?php if ($this->_var['my_address']): ?>
	<div class="oldaddress w clearfix"> 
		<?php $_from = $this->_var['my_address']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'address');$this->_foreach['fe_address'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_address']['total'] > 0):
    foreach ($_from AS $this->_var['address']):
        $this->_foreach['fe_address']['iteration']++;
?>
		<dl class="f66 clearfix <?php if (($this->_foreach['fe_address']['iteration'] <= 1)): ?> selected_address<?php endif; ?> J_AddressEach" <?php if ($this->_foreach['fe_address']['iteration'] % 4 == 0): ?> style="margin-right:0;"<?php endif; ?>>
			<dt>
				<input type="checkbox" name="addr_id" value="<?php echo $this->_var['address']['addr_id']; ?>" <?php if (($this->_foreach['fe_address']['iteration'] <= 1)): ?> checked="checked" <?php endif; ?>/>
				<b><?php echo $this->_var['address']['region_name']; ?> ( <?php echo htmlspecialchars($this->_var['address']['consignee']); ?>收 )</b> <ins class="deftip" <?php if ($this->_var['address']['setdefault']): ?> style="display:block"<?php endif; ?>>默认地址</ins>
			</dt>
			<dd class="addr-bd"> <?php echo htmlspecialchars($this->_var['address']['address']); ?>
				<?php if ($this->_var['address']['phone_mob']): ?><?php echo $this->_var['address']['phone_mob']; ?><?php else: ?><?php echo $this->_var['address']['phone_tel']; ?><?php endif; ?> 
			</dd>
			<dd class="addr-toolbar">
				<i class="curmarker"></i>
				<a href="javascript:;" class="btn-modify" ectype="dialog" dialog_id="my_address_edit" dialog_title="编辑收货地址" dialog_width="700" uri="index.php?app=my_address&act=edit&addr_id=<?php echo $this->_var['address']['addr_id']; ?>">修改</a>
			</dd>
		</dl>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
	<?php endif; ?>
	<div class="mb10 mt10 clearfix">
		<a href="javascript:;" ectype="dialog" dialog_title="添加收货地址" dialog_id="my_address_add" dialog_width="600" uri="index.php?app=my_address&act=add" class="btn-new-addr">使用新地址</a>
	</div>
</div>
