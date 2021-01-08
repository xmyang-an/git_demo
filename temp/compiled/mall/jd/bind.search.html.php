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
      <div class="bind clearfix">
        <div class="notice-word">
          <p>使用第三方账号绑定本站，您可以第三方账号轻松登录，无需记住本站的账号和密码，随时轻松登录…</p>
        </div>
        <div class="con clearfix"> 
          <?php $_from = $this->_var['bindlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
          <dl class="float-left mr10 clearfix">
            <dt class="float-left"><img src="static/images/<?php echo $this->_var['item']['name']; ?>.png" width="80" height="80" /></dt>
            <dd class="float-left ml10">
              <h3><?php echo $this->_var['item']['label']; ?></h3>
              <div class="status"> 
                <?php if ($this->_var['item']['enabled']): ?>
                <p class="clearfix"><i class="ico good"></i><span class="gray mt5 mb5">已绑定</span></p>
                <a class="btn J_Relieve" href="javascript:;" data-id="<?php echo $this->_var['item']['key']; ?>">解除绑定</a> 
                <?php else: ?>
                <p class="clearfix"><i class="ico error"></i><span class="gray mt5 mb5">未绑定</span></p>
                <a class="btn J_Bind" href="javascript:;" uri="<?php echo url('app=' . $this->_var['item']['key']. '&act=login'); ?>">添加绑定</a> 
                <?php endif; ?> 
              </div>
            </dd>
          </dl>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 