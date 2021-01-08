<div id="left">
  <h3 class="curmenu"> 
    <?php if ($this->_var['member_role'] == 'buyer_admin'): ?> 
    我是买家 
    <?php elseif ($this->_var['member_role'] == 'seller_admin'): ?> 
    我是卖家 
    <?php elseif ($this->_var['member_role'] == 'promotool_admin'): ?> 
    营销中心 
    <?php endif; ?> 
  </h3>
  <?php $_from = $this->_var['_member_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?> 
  <?php if ($this->_var['item']['submenu']): ?>
  <dl class="menu">
    <?php $_from = $this->_var['item']['submenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'subitem');if (count($_from)):
    foreach ($_from AS $this->_var['subitem']):
?>
    <dd><a href="<?php echo $this->_var['subitem']['url']; ?>" class="<?php if ($this->_var['subitem']['name'] == $this->_var['_curitem']): ?>active<?php else: ?>normal<?php endif; ?>"><span class="ico <?php echo $this->_var['subitem']['name']; ?>"><?php echo $this->_var['subitem']['text']; ?></span></a></dd>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </dl>
  <?php endif; ?> 
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
</div>
