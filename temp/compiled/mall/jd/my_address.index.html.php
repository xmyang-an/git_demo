<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="eject_btn" title="新增地址"><b class="ico1" ectype="dialog" dialog_title="新增地址" dialog_id="my_address_add" dialog_width="600" uri="index.php?app=my_address&act=add&ret_url=<?php echo urlencode($_GET['ret_url']); ?>">新增地址</b></div>
        <div class="public table">
          <table>
            <?php if ($this->_var['addresses']): ?>
            <tr class="gray line tr_color">
              <th>收货人姓名</th>
              <th>所在地区</th>
              <th class="width3">详细地址</th>
              <th>邮政编码</th>
              <th class="width5">电话/手机</th>
              <th>操作</th>
            </tr>
            <?php endif; ?> 
            <?php $_from = $this->_var['addresses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'address');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['address']):
        $this->_foreach['v']['iteration']++;
?>
            <tr class="<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>line_bold<?php else: ?>line<?php endif; ?> tr_align">
              <td><?php echo htmlspecialchars($this->_var['address']['consignee']); ?><?php if ($this->_var['address']['setdefault']): ?><font color="#3366CC">（默认）</font><?php endif; ?></td>
              <td><?php echo htmlspecialchars($this->_var['address']['region_name']); ?></td>
              <td><?php echo htmlspecialchars($this->_var['address']['address']); ?></td>
              <td><?php echo htmlspecialchars($this->_var['address']['zipcode']); ?></td>
              <td><?php echo $this->_var['address']['phone_tel']; ?> / <?php echo $this->_var['address']['phone_mob']; ?></td>
              <td><a href="javascript:void(0);" ectype="dialog" dialog_id="my_address_edit" dialog_title="编辑地址" dialog_width="700" uri="index.php?app=my_address&act=edit&addr_id=<?php echo $this->_var['address']['addr_id']; ?>" class="edit1 float_none">编辑</a> <a href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=my_address&amp;act=drop&addr_id=<?php echo $this->_var['address']['addr_id']; ?>');" class="delete float_none">删除</a></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="6" class="padding6"><div class="notice-word">
                  <p><?php echo $this->_var['lang'][$_GET['act']]; ?>您没有添加收货地址</p>
                </div></td>
            </tr>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe id='iframe_post' name="iframe_post" frameborder="0" width="0" height="0"> </iframe>
<?php echo $this->fetch('member.footer.html'); ?> 