<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_index table1">
          <table>
  
            <?php $_from = $this->_var['my_qa_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'my_qa_list');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['my_qa_list']):
        $this->_foreach['v']['iteration']++;
?>
            <tr><td height="20"></td></tr>
            <tr class="bgcolor line_normal">
              <td class="align2 width1"></td>
              <td><a href="<?php if ($this->_var['my_qa_list']['type'] == 'goods'): ?><?php echo url('app=' . $this->_var['my_qa_list']['type']. '&id=' . $this->_var['my_qa_list']['item_id']. '&act=qa'); ?><?php else: ?><?php echo url('app=' . $this->_var['my_qa_list']['type']. '&id=' . $this->_var['my_qa_list']['item_id']. ''); ?><?php endif; ?>" target="_blank" class="link3"><?php echo htmlspecialchars($this->_var['my_qa_list']['item_name']); ?></a> &nbsp;&nbsp;[<?php echo $this->_var['lang'][$this->_var['my_qa_list']['type']]; ?>]</td>
              <td class="width8"><span class="table_user"><?php if ($this->_var['my_qa_list']['user_name'] != ''): ?><?php echo htmlspecialchars($this->_var['my_qa_list']['user_name']); ?><?php else: ?>游客<?php endif; ?></span></td>
            </tr>
            <tr>
              <td></td>
              <td><h3 style="padding:0;">咨询内容:&nbsp;</h3>
                <p><?php echo nl2br(htmlspecialchars($this->_var['my_qa_list']['question_content'])); ?></p></td>
              <td><span class="time"><?php echo local_date("Y-m-d H:i:s",$this->_var['my_qa_list']['time_post']); ?></span></td>
            </tr>
            <?php if ($this->_var['my_qa_list']['reply_content'] != ''): ?>
            
              <tr <?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>class="line_bold"<?php endif; ?>>
            
              <td></td>
              <td><h3 style="padding:0;"><span class="color8">商家回复:&nbsp;</span></h3>
                <p><span class="color3"><?php echo htmlspecialchars($this->_var['my_qa_list']['reply_content']); ?></span></p></td>
              <td><span class="time"><?php echo local_date("Y-m-d H:i:s",$this->_var['my_qa_list']['time_reply']); ?></span></td>
            </tr>
            <?php else: ?>
            
              <tr <?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>class="line_bold"<?php endif; ?>>
            
              <td colspan="3">&nbsp;</td>
            </tr>
            <?php endif; ?> 
            
            <?php endforeach; else: ?>
            <tr char="bgcolor">
              <td colspan="3"><div class="notice-word">
                  <p><?php echo $this->_var['lang'][$_GET['act']]; ?>没有符合条件的记录</p>
                </div></td>
            </tr>
            
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php if ($this->_var['my_qa_data']): ?>
            <tr>
              <th></th>
              <th colspan="3"> <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
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