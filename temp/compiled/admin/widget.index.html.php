<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
function clean_file()
{
    $.getJSON('index.php?app=widget&act=clean_file', function(data){
        if (data.done)
        {
			parent.layer.confirm(data.msg,{icon: 3, title:'提示'},function(index){
				parent.layer.close(index);
				$.getJSON('index.php?app=widget&act=clean_file&continue', function(rzt){
                    parent.layer.alert(rzt.msg);
                });
				return false;	
			},function(index){
				parent.layer.close(index);
				return false;
			});
        }
        else
        {
			parent.layer.alert(data.msg);
            return false;
        }
    });
}
</script>
<div id="rightTop">
    <p><strong>挂件管理</strong><a href="javascript:void(0);" onclick="clean_file();" title="孤立文件是指被上传到服务器上，但实际并没有被引用的文件，重复配置有上传文件的挂件，会产生孤立文件，因此需要定时清理这些文件以释放硬盘空间" style="font-size:12px; font-weight:normal;">[ 清理孤立文件 ]</a></p>
    <ul class="subnav">
        <li><span>管理</span></li>
    </ul>
</div>
<div class="info2 flexigrid">
    <table class="distinction">
        <?php if ($this->_var['widgets']): ?>
        <thead>
        <tr>
            <th width="15%">挂件名称</th>
            <th align="left">挂件描述</th>
            <th width="10%">作者</th>
            <th width="50">版本</th>
            <th class="handler" style="width:150px;">操作</th>
        </tr>
        </thead>
        <?php endif; ?>
        <tbody>
        <?php $_from = $this->_var['widgets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'widget');if (count($_from)):
    foreach ($_from AS $this->_var['widget']):
?>
        <tr class="tatr2">
            <td><?php echo htmlspecialchars($this->_var['widget']['display_name']); ?></td>
            <td align="left"><?php echo htmlspecialchars($this->_var['widget']['desc']); ?></td>
            <td><a href="<?php echo $this->_var['widget']['website']; ?>" target="_blank" title="作者链接"><?php echo htmlspecialchars($this->_var['widget']['author']); ?></a></td>
            <td><?php echo htmlspecialchars($this->_var['widget']['version']); ?></td>
            <td class="handler">
                <!--<a href="index.php?app=widget&amp;act=edit&name=<?php echo $this->_var['widget']['name']; ?>&file=script">编辑脚本</a>
                |-->
                <a href="index.php?app=widget&amp;act=edit&name=<?php echo $this->_var['widget']['name']; ?>&file=template">编辑模板</a>
                </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="5">尚未安装任何挂件</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
    </table>
</div>
<?php echo $this->fetch('footer.html'); ?>
