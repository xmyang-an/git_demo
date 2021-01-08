<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p><strong>支付方式管理</strong></p>
    <ul class="subnav">
  	　 <li><span>支付方式管理</span></li>
  </ul>
</div>
<div class="info2 flexigrid">
    <table class="distinction">
        <?php if ($this->_var['payments']): ?>
        <thead>
        <tr>
            <th class="firstCell" width="15%">支付方式名称</th>
            <th align="left">支付方式描述</th>
            <th width="5%">启用</th>
            <th width="10%">支持的货币</th>
            <th width="10%">作者</th>
            <th width="10%" class="table-center">版本</th>
            <th width="50" class="handler" style="width: 100px">操作</th>
        </tr>
        </thead>
        <?php endif; ?>
        <tbody>
        <?php $_from = $this->_var['payments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'payment');if (count($_from)):
    foreach ($_from AS $this->_var['payment']):
?>
        <tr class="tatr2">
            <td class="firstCell"><?php echo $this->_var['payment']['name']; ?></td>
            <td align="left"><span class="padding1"><?php echo $this->_var['payment']['desc']; ?></span></td>
            <td><?php if ($this->_var['payment']['system_enabled']): ?>是<?php else: ?>否<?php endif; ?></td>
            <td><span class="padding1"><?php echo $this->_var['payment']['currency']; ?></span></td>
            <td><a href="<?php echo $this->_var['payment']['website']; ?>" target="_blank" title="作者链接"><?php echo $this->_var['payment']['author']; ?></a></td>
            <td class="table-center"><?php echo $this->_var['payment']['version']; ?></td>
            <td class="handler" width="50" style="width: 100px">
                <?php if (! $this->_var['payment']['system_enabled']): ?>
            <a href="javascript:goConfirm('您确定要启用它吗？','index.php?app=payment&amp;act=enable&amp;code=<?php echo $this->_var['payment']['code']; ?>',true);">启用</a>
                <?php else: ?>
                <a href="javascript:goConfirm('您确定要禁用它吗？','index.php?app=payment&act=disable&code=<?php echo $this->_var['payment']['code']; ?>',true);">禁用</a>
				<?php if (! in_array ( $this->_var['payment']['code'] , array ( 'cod' ) )): ?>
				<a href="index.php?app=payment&amp;act=conf&amp;code=<?php echo $this->_var['payment']['code']; ?>">配置</a>
				<?php endif; ?>
                <?php endif; ?>
                </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="7">尚未安装任何支付方式</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
    </table>
</div>
<?php echo $this->fetch('footer.html'); ?>
