<div class="w mt20 clearfix">
	<div class="address-confirm">
		<div class="box">
			<div class="realPay"> 
				<strong>实付款：</strong>
				<span class="price"><i>&yen;</i><em class="J_OrderAmount"><?php echo $this->_var['goods_info']['amount']; ?></em></span>
			</div>
			<div class="address">
				<p> 
					<strong>寄送至：</strong>
					<span class="address-detail J_AddressDetail"></span>
				</p>
				<p>
					<strong>收货人：</strong>
					<span class="address-detail J_Consignee"></span>
				</p>
			</div>
		</div>
	</div>
</div>
<div class="w clearfix">
	<div class="make_sure mb10">
		<p>
			<?php if (! in_array ( $_GET['goods'] , array ( 'meal' ) )): ?>
            <a href="<?php echo url('app=cart'); ?>" class="back">返回购物车</a>
            <?php endif; ?>
			<a href="javascript:void($('#order_form').submit());" class="btn-step fff center strong fs14 ml20 J_SubmitOrder">提交订单</a>
		</p>
	</div>
</div>
<div class="w clearfix">
	<div class="notice-word price-notice"><span class="yellow">若价格变动，请在提交订单后联系卖家改价，并查看已买到的宝贝</span></div>
</div>
