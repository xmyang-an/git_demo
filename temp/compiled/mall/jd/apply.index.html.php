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
                                <li class="current">入驻指南</li>
                                <li>签订入驻协议</li>
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
				<div class="joinin-info">
					<ul class="nav J_tab">
						<?php $_from = $this->_var['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');$this->_foreach['fe_article'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_article']['total'] > 0):
    foreach ($_from AS $this->_var['article']):
        $this->_foreach['fe_article']['iteration']++;
?>
						<li <?php if (($this->_foreach['fe_article']['iteration'] <= 1)): ?>class="on"<?php endif; ?> <?php if (($this->_foreach['fe_article']['iteration'] == $this->_foreach['fe_article']['total'])): ?> style="border:0;"<?php endif; ?>><?php echo htmlspecialchars($this->_var['article']['title']); ?></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</ul>
					<ul class="tab-content">
						<?php $_from = $this->_var['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');$this->_foreach['fe_article'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_article']['total'] > 0):
    foreach ($_from AS $this->_var['article']):
        $this->_foreach['fe_article']['iteration']++;
?>
						<li <?php if (! ($this->_foreach['fe_article']['iteration'] <= 1)): ?> class="hidden"<?php endif; ?>><?php echo $this->_var['article']['content']; ?></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</ul>
              </div>
			  <div class="joinin-btn">
			  		<a href="<?php echo url('app=apply&step=1'); ?>" target="_self">我要入驻</a>
			  </div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>