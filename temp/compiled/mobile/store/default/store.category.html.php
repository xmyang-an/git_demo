<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div id="page-store-category">
    <div id="gcategory">
      <div class="gcategory">
        <ul class="gcategory-list">
          <?php $_from = $this->_var['gcategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
          <li class="bgf" <?php if ($this->_foreach['fe_gcategory']['iteration'] % 2 == 0): ?> style="background:#fbfbfb"<?php endif; ?>> <a class="webkit-box gcategory-value" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&cate_id=' . $this->_var['gcategory']['id']. ''); ?>"><span class="ml10 fs13 flex1"><?php echo htmlspecialchars($this->_var['gcategory']['value']); ?></span><i class="psmb-icon-font mr10">&#xe629;</i></a> 
            <?php if ($this->_var['gcategory']['children']): ?>
            <dl class="category-child bgf" <?php if ($this->_foreach['fe_gcategory']['iteration'] % 2 != 0): ?> style="background:#fbfbfb"<?php endif; ?>>
              <dd class="clearfix"> 
                <?php $_from = $this->_var['gcategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_child']['iteration']++;
?> 
                <a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&cate_id=' . $this->_var['child']['id']. ''); ?>" class="child-value"> <span <?php if ($this->_foreach['fe_child']['iteration'] % 2 == 0): ?> style="margin-right:0"<?php endif; ?>><?php echo htmlspecialchars($this->_var['child']['value']); ?></span></a> 
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
              </dd>
            </dl>
            <?php endif; ?> 
          </li>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>