<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public table">
          <table>
            <?php if ($this->_var['messages']): ?>
            
            
            <tr class="gray">
              <th><input type="checkbox" id="all" class="checkall"/></th>
              <th class="align1">用户名</th>
              <th>内容</th>
              <th>最后更新</th>
              <th class="width4">操作</th>
            </tr>
            <?php endif; ?> 
            <?php $_from = $this->_var['messages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'message');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['message']):
        $this->_foreach['v']['iteration']++;
?>
            
              <tr class="<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>line_bold<?php else: ?>line<?php endif; ?>">
            
              <td class="align2"><?php if ($_GET['act'] != 'announcepm'): ?>
                
                <input type="checkbox" class="checkitem" value="<?php echo $this->_var['message']['msg_id']; ?>"/>
                
                <?php endif; ?></td>
              <td class="width13"><img class="makesmall" max_width="48" max_height="48" src="<?php echo $this->_var['message']['user_info']['portrait']; ?>" style="vertical-align:middle; margin-right:5px;" /><span class="user_name"><?php echo $this->_var['message']['user_info']['user_name']; ?></span></td>
                <td class="link2 <?php if ($this->_var['message']['new'] == 1): ?>font_bold<?php endif; ?>"><?php echo sub_str($this->_var['message']['content'],110); ?></td>
              <td class="align2 color1 width8"><?php echo local_date("Y-m-d H:i",$this->_var['message']['last_update']); ?></td>
              <td class="width8"><a href="<?php echo url('app=message&act=view&msg_id=' . $this->_var['message']['msg_id']. ''); ?>" class="desc">查看详情</a> 
                <?php if ($_GET['act'] != 'announcepm'): ?><a href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=message&amp;act=drop&msg_id=<?php echo $this->_var['message']['msg_id']; ?>');" class="delete">删除</a><?php endif; ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="5"><div class="notice-word">
                  <p>没有<?php echo $this->_var['lang'][$_GET['act']]; ?></p>
                </div></td>
            </tr>
            
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php if ($this->_var['messages']): ?> 
            
            <tr class="btion">
              <th><input type="checkbox" id="all2" class="checkall"/></th>
              <th colspan="4"> 
             	<?php if ($_GET['act'] != 'announcepm'): ?>
                <p class="position1 clearfix">
                  
                  <span class="all"><label for="all2">全选</label></span>
                  <a href="javascript:void(0);" class="delete" uri="index.php?app=message&act=drop" name="msg_id" presubmit="confirm('您确定要删除它吗？')" ectype="batchbutton">删除</a> </p>
                   <?php endif; ?> 
                <div class="position2 block clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
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