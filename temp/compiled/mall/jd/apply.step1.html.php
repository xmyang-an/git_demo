<?php echo $this->fetch('top.html'); ?>
<script type="text/javascript">
$(function(){
	$("#agreement_next").click(function(){
		var agreement = $("#input_apply_agreement").prop('checked');
		if(agreement){
			location.href = 'index.php?app=apply&step=2';
			return;
		}else{
			alert('请阅读并同意入驻协议');
			return false;
		}
	});
});
</script>
<style type="text/css">
.w{width:1000px;}
</style>
<div id="main" class="w-full">
	<div class="page-apply">
		<div class="w logo mt10">
			<p><a href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a></p>
		</div>
		<div class="w content clearfix">
			<div class="left">
            	<div class="steps">
                    <dl class="setpbox">
                        <dt>申请步骤</dt>
                        <dd>
                            <ul>
                                <li class="succeed">入驻指南</li>
                                <li class="current">签订入驻协议</li>
                                <li>填写商家信息</li>
                                <li>平台审核</li>
                                <li>店铺开通</li>
                            </ul>
                        </dd>
                    </dl>
                    <dl class="setpbox contact-mall mt10">
                        <dt>平台联系方式</dt>
                        <dd>
                            <p class="tel"><span>电话：</span><?php echo $this->_var['setting']['phone']; ?></p>
                            <p class="email mt10"><span>邮箱：</span><?php echo $this->_var['setting']['email']; ?></p>
                        </dd>
                    </dl>
                </div>
			</div>
			<div class="right">
				<div class="apply-agreement">
				  <h3>入驻协议</h3>
				  <div class="agreement-content"><?php echo $this->_var['setup_store']['content']; ?></div>
				  <div class="agreement-btn">
					<input id="input_apply_agreement" name="agreement" type="checkbox" checked="checked">
					<label for="input_apply_agreement">我已阅读并同意以上协议</label>
				  </div>
				  <div class="bottom"><a id="agreement_next" href="javascript:;" class="agreement_next">下一步，填写商家信息</a></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>