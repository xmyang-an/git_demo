<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="w-full">
  <div id="page-member-bind" class="w-full page-auth">
    <ul class="bind-list">
      <?php $_from = $this->_var['bindlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
      <li class="bgf padding10 border-bottom">
        <label class="switch-checkbox-radio w-full block box-align-center J_SwtcherInput <?php if ($this->_var['item']['enabled']): ?>checked<?php endif; ?>" for="switcher"> <em class="block flex1 fs14"><?php echo $this->_var['item']['label']; ?></em> <span class="switcher-style block"></span> </label>
        <input name="<?php echo $this->_var['item']['key']; ?>" class="hidden" id="switcher" value="1" type="checkbox" <?php if ($this->_var['item']['enabled']): ?>checked="checked"<?php endif; ?> />
      </li>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
  </div>
</div>
<script type="text/javascript">
$(function(){
	$('.J_SwtcherInput').click(function(){
		var checked = $(this).parent().find('input').prop('checked');
		var appid = $(this).parent().find('input').attr('name');
		
		// 未绑定，提示跳转绑定
		if(checked == false)
		{
			$(this).removeClass('checked');
			$(this).parent().find('input').prop('checked', false);
				
			location.href = replace_all("<?php echo url('app="+appid+"&act=login'); ?>", '&amp;', '&');
		}
		// 已绑定过，取消绑定
		else
		{
			ajaxRequest('GET', replace_all("<?php echo url('app=bind&act=relieve&appid="+appid+"'); ?>", '&amp;', '&'), null, null, null);
		}
	});
});
</script>
<?php echo $this->fetch('footer.html'); ?> 