<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
	$(function(){
		$('.J_Bind').click(function(){
			var uri = $(this).attr('uri');
			layer.open({
			  type: 2,
			  title: '使用第三方账号绑定本站',
			  shadeClose: true,
			  shade: false,
			  maxmin: true, //开启最大化最小化按钮
			  area: ['800px', '600px'],
			  content: uri,
			  end:function(){
					window.location.reload();
				}
			});
		});
		$('.J_Relieve').click(function(){
			var appid = $(this).attr("data-id");
			layer.confirm('解绑后将无法继续使用此账户快速登录网站。',{icon: 3, title:'解除绑定'},function(index){
				$.getJSON('index.php?app=bind&act=relieve', {'appid':appid},function(data){
					if(data.done){
						layer.msg(data.msg, {icon: 1});
						layer.close(index);
						window.location.reload();
					}else{
						layer.msg(data.msg);
						layer.close(index);
					}
				});
			},function(index){
				layer.close(index);
			});
		});
	})
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful">
      <div class="wrap">
        <div class="public setting">
          <h3 class="ml10 mb10">账号安全</h3>
          <dl class="clearfix">
            <dt>用户头像</dt>
            <dd>
            	<?php if ($this->_var['member']['portrait']): ?>
                <span class="mr20"><a href="<?php echo url('app=member&act=profile'); ?>" class="ml10">编辑资料</a></span><span class="ok"><i></i></span>
                <?php else: ?>
                <span class="error f60"><a href="<?php echo url('app=member&act=profile'); ?>">去上传</a></span>
                <?php endif; ?>
            </dd>
          </dl>
          <dl class="clearfix">
            <dt>手机号码</dt>
            <dd>
            	<?php if ($this->_var['member']['phone_mob']): ?>
				<span class="mr20"><?php echo $this->_var['member']['phone_mob']; ?> <a href="<?php echo url('app=member&act=phone'); ?>" class="ml20">修改</a></span><span class="ok"><i></i></span>
                <?php else: ?>
                <span class="error f60"><a href="<?php echo url('app=member&act=phone'); ?>">去绑定</a></span>
                <?php endif; ?>
             </dd>
          </dl>
          <dl class="clearfix">
            <dt>电子邮箱</dt>
            <dd>
            	<?php if ($this->_var['member']['email']): ?>
                <span class="mr20"><?php echo $this->_var['member']['email']; ?> <a href="<?php echo url('app=member&act=email'); ?>" class="ml20">修改</a></span><span class="ok"><i></i></span>
                <?php else: ?>
                <span class="error f60"><a href="<?php echo url('app=member&act=email'); ?>">去绑定</a></span>
                <?php endif; ?>
             </dd>
          </dl>
          <dl class="clearfix">
            <dt>资金账户</dt>
            <dd>
            	<?php if ($this->_var['deposit_account']['account']): ?>
                <span class="mr20"><?php echo $this->_var['deposit_account']['account']; ?> <a href="<?php echo url('app=deposit&act=config'); ?>" class="ml20">配置</a></span><span class="ok"><i></i></span>
                <?php else: ?>
                <span class="error f60"><a href="<?php echo url('app=deposit&act=config'); ?>">去设置</a></span>
                <?php endif; ?>
             </dd>
          </dl>
          <dl class="clearfix">
            <dt>支付密码</dt>
            <dd>
            	<?php if ($this->_var['deposit_account']['password']): ?>
                <span class="mr20"><a href="<?php echo url('app=deposit&act=config'); ?>" >重置</a></span><span class="ok"><i></i></span>
                <?php else: ?>
                <span class="error f60"><a href="<?php echo url('app=deposit&act=config'); ?>">去设置</a></span>
                <?php endif; ?>
             </dd>
          </dl>
          <div class="line"></div>
          <h3 class="ml10 mt10 mb10">社交账号登录</h3>
          <?php $_from = $this->_var['bindlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
          <dl class="clearfix">
            <dt><img src="static/images/<?php echo $this->_var['item']['name']; ?>.png" width="20" height="20" class="mr10" /><?php echo $this->_var['item']['label']; ?></dt>
            <dd>
            <?php if ($this->_var['item']['enabled']): ?>
            <span class="mr20"><a class="btn J_Relieve" href="javascript:;" data-id="<?php echo $this->_var['item']['key']; ?>">解除绑定</a> </span><span class="ok"><i></i></span>
            <?php else: ?>
            <span class="error f60"><a class="btn J_Bind" href="javascript:;" uri="<?php echo url('app=' . $this->_var['item']['key']. '&act=login'); ?>">添加绑定</a></span>
            <?php endif; ?>
            </dd>
          </dl>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 