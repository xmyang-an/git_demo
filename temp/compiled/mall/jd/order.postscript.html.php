<div class="order-confirm-extra w clearfix mt20" style="background:#fff;">
	<div class="confirm-extra-info float-left" style="margin-left:670px; ">
		<div class="extra-list use-integral clearfix">
			<div class="subtitle float-left">&nbsp;</div>
			<div class="subcontent float-left">
				<label class="mr10"><input type="checkbox" class="J_UseIntegralCheckbox" <?php if (! $this->_var['goods_info']['allow_integral']): ?> disabled="disabled"<?php endif; ?>/>使用积分</label>
				<input type="text" name="exchange_integral" class="integral-input J_IntegralAmount" disabled="disabled" /> 点
				<i class="icon-remark J_IconRemark">
					<div class="remark-body hidden"><div class="box">1积分可以抵扣<?php echo $this->_var['goods_info']['integralExchange']['rate']; ?>元</div></div>
				</i>
			</div>
			<div class="fee float-right f66">-<em class="J_IntegralPrice">0.00</em></div>
		</div>
	</div>
</div>

                    
     