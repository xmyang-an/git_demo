<?php echo $this->fetch('header.html'); ?>
<div id="main">
	<div id="page-category-goods"> 
		<div id="gcategory">
			<div class="gcategory">
				<ul class="gcategory-list">
					<?php $_from = $this->_var['scategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scategory');$this->_foreach['fe_scategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_scategory']['total'] > 0):
    foreach ($_from AS $this->_var['scategory']):
        $this->_foreach['fe_scategory']['iteration']++;
?>
					<li> 
						<a class="gcategory-value <?php if (($this->_foreach['fe_scategory']['iteration'] == $this->_foreach['fe_scategory']['total'])): ?>gcategory-value-last<?php endif; ?>" href="<?php echo url('app=search&act=store&cate_id=' . $this->_var['scategory']['id']. ''); ?>"><i></i><span><?php echo htmlspecialchars($this->_var['scategory']['value']); ?></span></a>
						<em class="J_GcategoryLi gcategory-show-child"></em>
                        <?php if ($this->_var['scategory']['children']): ?>
						<div class="category-child">
							<p> 
								<?php $_from = $this->_var['scategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_child']['iteration']++;
?> 
								<a href="<?php echo url('app=search&act=store&cate_id=' . $this->_var['child']['id']. ''); ?>" class="child-value"><span><?php echo htmlspecialchars($this->_var['child']['value']); ?></span></a> 
								<?php if ($this->_foreach['fe_child']['iteration'] % 2 == 0 && ! ($this->_foreach['fe_child']['iteration'] == $this->_foreach['fe_child']['total'])): ?> 
							</p>
							<p> 
								<?php endif; ?> 
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
							</p>
						</div>
                        <?php endif; ?>
					</li>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>