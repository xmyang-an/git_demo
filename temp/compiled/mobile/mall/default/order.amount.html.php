<div class="w-full clearfix">
	<div class="make_sure clearfix">
		<div class="realPay float-left"> 
			<strong class="ml10">实付款：</strong>
			<span class="price fs14"><i>&yen;</i><em class="J_OrderAmount"><?php echo $this->_var['goods_info']['amount']; ?></em></span>
		</div>
		<p class="btn-next float-right">
			<a href="javascript:void($('#order_form').submit());" class="btn-step fff center strong fs14 J_SubmitOrder">提交订单</a>
		</p>
	</div>
</div>
