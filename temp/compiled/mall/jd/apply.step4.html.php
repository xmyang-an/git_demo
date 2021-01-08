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
                                <li class="succeed">平台审核</li>
                                <li class="current">店铺开通</li>
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
				<div class="apply-status apply-end">
				    <div class="text">
                    	<p><i></i>您的店铺已经开通了。</p> 
                        <p class="mt10 pt5"><a href="<?php echo url('app=seller_admin'); ?>">管理我的店铺</a> <a href="<?php echo url('app=store&id=' . $this->_var['visitor']['user_id']. ''); ?>" target="_blank">查看我的店铺</a></p>
                   </div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>