<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/global.css'; ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo $this->lib_base . "/" . 'layui/css/modules/layim/layim.css'; ?>" rel="stylesheet" />
<style type="text/css">
body {
	min-width: 300px;
	padding-bottom: 30px;
}
.layim-chat-main {
	height: auto
}
</style>
<div class="layim-chat-main clearfix">
  <ul>
    <?php $_from = $this->_var['imlog']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'log');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['log']):
        $this->_foreach['fe_item']['iteration']++;
?>
    <li class="<?php if ($this->_var['visitor']['user_id'] == $this->_var['log']['fromid']): ?>layim-chat-mine<?php endif; ?>">
      <div class="layim-chat-user"><img src="<?php echo $this->_var['log']['avatar']; ?>"> <cite> 
        <?php if ($this->_var['visitor']['user_id'] == $this->_var['log']['fromid']): ?> 
        <i><?php echo local_date("Y-m-d H:i:s",$this->_var['log']['add_time']); ?></i><?php echo $this->_var['log']['fromName']; ?>
        <?php else: ?> 
        <?php echo $this->_var['log']['fromName']; ?><i><?php echo local_date("Y-m-d H:i:s",$this->_var['log']['add_time']); ?></i> 
        <?php endif; ?> 
        </cite> </div>
      <div class="layim-chat-text"><?php echo $this->_var['log']['formatContent']; ?></div>
    </li>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </ul>
  <?php if ($this->_var['imlog']): ?> 
  <?php echo $this->fetch('page.bottom.html'); ?> 
  <?php endif; ?> 
</div>
