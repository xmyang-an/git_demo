<div class="order-confirm-extra clearfix" style="margin:20px 0 65px 0; padding:10px;">
		<div class="extra-list use-integral clearfix J_UseIntegral">
			<div class="title float-left fs13">使用积分</div>
			<div class="content float-left clearfix hidden">
				<label class="mr10">
                	<input id="switcher" type="checkbox" class="J_UseIntegralCheckbox" <?php if (! $this->_var['goods_info']['allow_integral']): ?> disabled="disabled"<?php endif; ?>/>使用积分
                </label>
				<input type="text" name="exchange_integral" class="integral-input J_IntegralAmount" disabled="disabled" />
			</div>
			<div class="fee float-right clearfix"><em class="J_IntegralPrice float-left"></em><label class="switch-checkbox-radio inline-block box-align-center J_SwtcherInput float-left ml10 <?php if (! $this->_var['goods_info']['allow_integral']): ?>disabled<?php endif; ?>" for="switcher"><span class="switcher-style block"></span></label></div>
		</div>
	</div>
</div>