<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public table">
          <table>
            <?php if ($this->_var['navigations']): ?>
            <tr class="gray">
              <th width="30"><input type="checkbox" id="all" class="checkall"/></th>
              <th class="align1 width9">导航名称</th>
              <th class="width15">是否显示</th>
              <th>排序</th>
              <th>操作</th>
            </tr>
            <?php endif; ?> 
            <?php $_from = $this->_var['navigations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'navigation');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['navigation']):
        $this->_foreach['v']['iteration']++;
?>
            
            <tr class="<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>line_bold<?php else: ?>line<?php endif; ?>">
              <td class="align2"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['navigation']['article_id']; ?>" /></td>
              <td><?php echo htmlspecialchars($this->_var['navigation']['title']); ?></td>
              <td class="align2"><?php if ($this->_var['navigation']['if_show'] == 1): ?>
                
                <p class="padding2"><span class="right_ico"></span></p>
                
                <?php else: ?>
                
                <p class="padding2"><span class="wrong_ico"></span></p>
                
                <?php endif; ?></td>
              <td class="align2"><?php echo $this->_var['navigation']['sort_order']; ?></td>
              <td class="width13"><a href="index.php?app=my_navigation&amp;act=edit&nav_id=<?php echo $this->_var['navigation']['article_id']; ?>" class="edit1">编辑</a> <a href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=my_navigation&amp;act=drop&nav_id=<?php echo $this->_var['navigation']['article_id']; ?>');" class="delete">删除</a></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="5"><div class="notice-word">
                  <p>没有符合条件的导航</p>
                </div></td>
            </tr>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php if ($this->_var['navigations']): ?>
       
            <tr class="btion">
              <th><input id="all2" type="checkbox" class="checkall" /></th>
              <th colspan="4"> <p class="position1"> <span class="all">
                  <label for="all2">全选</label>
                  </span> <a href="javascript:void(0);" class="delete" ectype="batchbutton" uri="index.php?app=my_navigation&act=drop" name="nav_id" presubmit="confirm('您确定要删除它吗？')">删除</a> </p>
                <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
              </th>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 