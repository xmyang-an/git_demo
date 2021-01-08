<?php echo $this->fetch('top.html'); ?>
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
                                <li class="succeed">签订入驻协议</li>
                                <li class="succeed">填写商家信息</li>
                                <li class="current">平台审核</li>
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
				<div class="apply-status">
				    <p class="clearfix"><i class="block float-left"></i>
                        <span class="mt5 block float-left" style="width:500px;">
                        <?php if ($this->_var['store']['apply_remark']): ?>
                        审核不通过，原因为：<a href="<?php echo url('app=apply&step=2&id=' . $this->_var['store']['store_id']. ''); ?>"><?php echo $this->_var['store']['apply_remark']; ?></a>
                        <?php else: ?>
                        您的开店申请已提交，我们会尽快处理并通知您，请在24小时内保持电话畅通，感谢您的合作！
                        <?php endif; ?>
                        </span>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>