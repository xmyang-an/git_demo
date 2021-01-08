<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p><strong>插件管理</strong></p>
  <ul class="subnav">
    <li><span>管理</span></li>
  </ul>
</div>
<div class="info2">
  <table class="distinction">
    <?php if ($this->_var['plugins']): ?>
    <thead>
      <tr>
        <th width="15%" align="left" style="padding-left:15px;">插件名称</th>
        <th align="left">插件描述</th>
        <th width="15%">作者</th>
        <th width="10%">版本</th>
        <th class="handler">操作</th>
      </tr>
    </thead>
    <?php endif; ?>
    <tbody>
      <?php $_from = $this->_var['plugins']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'plugin');if (count($_from)):
    foreach ($_from AS $this->_var['plugin']):
?>
      <tr>
        <td style="padding-left:15px;"><?php echo htmlspecialchars($this->_var['plugin']['name']); ?></td>
        <td align="left"><?php echo htmlspecialchars($this->_var['plugin']['desc']); ?></td>
        <td align="center"><a href="<?php echo $this->_var['plugin']['website']; ?>" target="_blank" title="作者链接"><?php echo htmlspecialchars($this->_var['plugin']['author']); ?></a></td>
        <td align="center"><?php echo htmlspecialchars($this->_var['plugin']['version']); ?></td>
        <td class="handler"><?php if (! $this->_var['plugin']['enabled']): ?> 
          <a href="index.php?app=plugin&amp;act=enable&amp;id=<?php echo $this->_var['plugin']['id']; ?>">启用</a> 
          <?php else: ?> 
          <a href="javascript:goConfirm('您确定要禁用它吗？','index.php?app=plugin&act=disable&id=<?php echo $this->_var['plugin']['id']; ?>',true);">禁用</a> 
          <?php if ($this->_var['plugin']['config']): ?> 
          | <a href="index.php?app=plugin&amp;act=config&id=<?php echo $this->_var['plugin']['id']; ?>">配置</a> 
          <?php endif; ?> 
          <?php endif; ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr class="no_data">
        <td class="no-data" colspan="5">尚未安装任何插件</td>
      </tr>
      <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </tbody>
  </table>
</div>
<?php echo $this->fetch('footer.html'); ?>