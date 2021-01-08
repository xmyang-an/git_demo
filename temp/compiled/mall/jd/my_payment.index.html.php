<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_index table">
          <table>
            <?php if ($this->_var['payments']): ?>
            <tr class="gray gray_new">
              <th class="width13">名称</th>
              <th>插件说明</th>
              <th class="width4">启用</th>
              <th class="width13">操作</th>
            </tr>
            <?php endif; ?> 
            <?php $_from = $this->_var['payments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'payment');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['payment']):
        $this->_foreach['v']['iteration']++;
?>
            <tr class="<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>line_bold<?php else: ?>line<?php endif; ?>">
              <td><span class="padding1"><?php echo htmlspecialchars($this->_var['payment']['name']); ?></span></td>
              <td><?php echo $this->_var['payment']['desc']; ?></td>
              <td class="align2"><?php if ($this->_var['payment']['enabled'] || $this->_var['payment']['code'] != 'cod'): ?>是<?php else: ?>否<?php endif; ?></td>
              <td><div class="clearfix"> 
                  <?php if ($this->_var['payment']['installed']): ?> 
                  <a href="javascript:void(0);" ectype="dialog" uri="index.php?app=my_payment&amp;act=config&payment_id=<?php echo $this->_var['payment']['payment_id']; ?>&amp;code=<?php echo $this->_var['payment']['code']; ?>" dialog_id="my_payment_config" dialog_title="配置" dialog_width="600" class="add2_ico">配置</a> <a href="javascript:drop_confirm('卸载后所有使用该支付方式的订单将无法支付，若您只是不希望让用户可以选择该支付方式，可以使用“配置”将该支付方式禁用，您确定要卸载它吗？', 'index.php?app=my_payment&amp;act=uninstall&payment_id=<?php echo $this->_var['payment']['payment_id']; ?>');" class="delete">卸载</a> 
                  <?php else: ?> 
                  <?php if (in_array ( $this->_var['payment']['code'] , array ( 'cod' ) )): ?> 
                  <a href="javascript:void(0);" ectype="dialog" dialog_id="my_payment_install" dialog_title="安装" uri="index.php?app=my_payment&amp;act=install&code=<?php echo $this->_var['payment']['code']; ?>" dialog_width="600" class="add1_ico">安装</a> 
                  <?php else: ?> 
                  <span class="gray">已安装，无需配置</span> 
                  <?php endif; ?> 
                  <?php endif; ?> 
                </div></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="4"><div class="notice-word">
                  <p>没有可用的支付方式，请联系管理员解决此问题</p>
                </div></td>
            </tr>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe name="my_payment" style="display:none"></iframe>
<?php echo $this->fetch('footer.html'); ?> 