<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p><span>模块管理</span></p>
    <ul class="subnav">
    	<li><span>管理</span></li>
  	</ul>
</div>
<div class="info2 flexigrid">
    <table class="distinction">
        <?php if ($this->_var['module']): ?>
        <thead>
        <tr class="tatr1">
            <th>名称</th>
            <th align="left">描述</th>
            <th width="10%">作者</th>
            <th width="10%">版本</th>
            <th width="200">操作</th>
        </tr>
        <?php endif; ?>
        <tbody>
        <?php $_from = $this->_var['modules']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'module');if (count($_from)):
    foreach ($_from AS $this->_var['module']):
?>
        <tr class="tatr2">
            <td><?php echo htmlspecialchars($this->_var['module']['name']); ?></td>
            <td align="left"><?php echo htmlspecialchars($this->_var['module']['desc']); ?></td>
            <td><a href="<?php echo $this->_var['module']['website']; ?>" target="_blank" title="作者链接"><?php echo htmlspecialchars($this->_var['module']['author']); ?></a></td>
            <td><?php echo htmlspecialchars($this->_var['module']['version']); ?></td>
            <td width="200">
                <?php if (! $this->_var['module']['installed']): ?>
            <a href="index.php?app=module&amp;act=install&amp;id=<?php echo $this->_var['module']['id']; ?>">安装</a>
                <?php else: ?>
                <a href="javascript:if(confirm('您确定要卸载它吗？'))window.location='index.php?app=module&act=uninstall&id=<?php echo $this->_var['module']['id']; ?>';">卸载</a>
                <?php if ($this->_var['module']['config']): ?>
                |
                <a href="index.php?app=module&amp;act=config&id=<?php echo $this->_var['module']['id']; ?>">配置</a>
                <?php endif; ?>
                <?php if ($this->_var['module']['outofdate']): ?>
                <a href="#">upgrade</a>
                <?php endif; ?>
                <?php $_from = $this->_var['module']['menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'm');if (count($_from)):
    foreach ($_from AS $this->_var['m']):
?>
                |
                <a href="index.php?module=<?php echo $this->_var['module']['id']; ?>&act=<?php echo $this->_var['m']['act']; ?>"><?php echo $this->_var['m']['text']; ?></a>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
                </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="5">没有安装任何模块</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
    </table>
</div>
<?php echo $this->fetch('footer.html'); ?>