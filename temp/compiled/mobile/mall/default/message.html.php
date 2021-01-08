<?php echo $this->fetch('member.header.html'); ?>
<style>
.bar-wrap .top-bar .barbtn li a,.bar-wrap .top-bar .barbtn .curlocal-title,.bar-wrap .top-bar .barbtn input{opacity:1 !important}
.bar-wrap .top-bar .barbtn input{background:none;}
.bar-wrap .top-bar .barbtn form,#footer{display:none}
</style>
<div id="main">
<div class="showMessage">
  <div class="<?php if ($this->_var['icon'] == 'notice'): ?>successMsg<?php else: ?>defeatedMsg<?php endif; ?>"> <span class="icon"><i class="psmb-icon-font fff"></i></span>
    <h2>操作提示</h2>
    <div class="msgDesc"> <?php echo $this->_var['message']; ?> 
      <?php if ($this->_var['err_file']): ?>
      <p>Error File: <strong><?php echo $this->_var['err_file']; ?></strong> at <strong><?php echo $this->_var['err_line']; ?></strong> line.</p>
      <?php endif; ?> 
    </div>
    
    <?php if ($this->_var['icon'] != 'notice' && $this->_var['links']): ?>
    <div class="padding10 mt10"> 
      <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?> 
      <a href="<?php echo $this->_var['item']['href']; ?>" class="btn-alipay <?php if (($this->_foreach['fe_item']['iteration'] <= 1)): ?>btn-alipay-green<?php else: ?>btn-alipay-gray mt10<?php endif; ?>"><?php echo $this->_var['item']['text']; ?></a> 
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
    </div>
    <?php endif; ?> 
  </div>
  <script type="text/javascript">
	<?php if ($this->_var['redirect']): ?>
	window.setTimeout("<?php echo $this->_var['redirect']; ?>", 5000);
	<?php endif; ?>
	</script> 
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>