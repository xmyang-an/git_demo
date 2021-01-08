<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
//<!CDATA[
$(function()
{
    var map = <?php echo $this->_var['map']; ?>;
    if (map.length > 0)
    {
        var option = {openImg: "templates/style/images/treetable/tv-collapsable.gif", shutImg: "templates/style/images/treetable/tv-expandable.gif", leafImg: "templates/style/images/treetable/tv-item.gif", lastOpenImg: "templates/style/images/treetable/tv-collapsable-last.gif", lastShutImg: "templates/style/images/treetable/tv-expandable-last.gif", lastLeafImg: "templates/style/images/treetable/tv-item-last.gif", vertLineImg: "templates/style/images/treetable/vertline.gif", blankImg: "templates/style/images/treetable/blank.gif", collapse: false, column: 1, striped: false, highlight: true, state:false};
        $("#treet1").jqTreeTable(map, option);
    }
});
//]]>
</script>

<div id="rightTop">
    <p>店铺分类</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=scategory&amp;act=add">新增</a></li>
        <li><a class="btn1" href="index.php?app=scategory&amp;act=export">导出</a></li>
        <li><a class="btn1" href="index.php?app=scategory&amp;act=import">导入</a></li>
    </ul>
</div>

<div class="info2 flexigrid">
    <table class="distinction">
        <?php if ($this->_var['scategories']): ?>
        <thead>
        <tr>
            <th class="w30"><input id="checkall_1" type="checkbox" class="checkall" /></th>
            <th width="50%"><span class="all_checkbox"><label for="checkall_1">全选</label></span>分类名称</td>
            <th>排序</th>
            <th class="handler" style="width:200px;">操作</th>
        </tr>
        </thead>
        <tbody id="treet1"><?php endif; ?>
        <?php $_from = $this->_var['scategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scategory');if (count($_from)):
    foreach ($_from AS $this->_var['scategory']):
?>
        <tr>
            <td class="align_center w30"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['scategory']['cate_id']; ?>" /></td>
            <td class="node" width="50%"><span ectype="inline_edit" fieldname="cate_name" fieldid="<?php echo $this->_var['scategory']['cate_id']; ?>" required="1" title="可编辑" class="node_name editable"><?php echo htmlspecialchars($this->_var['scategory']['cate_name']); ?></span></td>
            <td class="align_center"><span ectype="inline_edit" fieldname="sort_order" fieldid="<?php echo $this->_var['scategory']['cate_id']; ?>" datatype="pint" maxvalue="255" title="可编辑" class="editable"><?php echo $this->_var['scategory']['sort_order']; ?></span></td>
            <td class="handler bDiv" style=" background:none; width:250px; text-align:left;">
                <a href="index.php?app=scategory&amp;act=edit&amp;id=<?php echo $this->_var['scategory']['cate_id']; ?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a>
                <a href="javascript:goConfirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗', 'index.php?app=scategory&amp;act=drop&amp;id=<?php echo $this->_var['scategory']['cate_id']; ?>',true);" class="btn red"><i class="fa fa-trash-o"></i>删除</a>
                <?php if ($this->_var['scategory']['layer'] < 2): ?><a href="index.php?app=scategory&amp;act=add&amp;pid=<?php echo $this->_var['scategory']['cate_id']; ?>" class="btn green"><i class="fa fa-plus"></i>新增下级</a><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="4">暂无店铺分类</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php if ($this->_var['scategories']): ?></tbody><?php endif; ?>
        <tfoot>
            <tr class="tr_pt10">
            <?php if ($this->_var['scategories']): ?>
                <td class="align_center"><label for="checkall1"><input id="checkall_2" type="checkbox" class="checkall"></label></td>
                <td colspan="3" id="batchAction">
                    <span class="all_checkbox mt5"><label for="checkall_2">全选</label></span>&nbsp;&nbsp;
                    <div class="fbutton JBatchDel" style="border:0;" uri="index.php?app=scategory&act=drop" name="id"><div class="del" title="将选定行数据批量删除"><span><i class="fa fa-trash"></i>批量删除</span></div></div>
                </td>
            <?php endif; ?>
            </tr>
        </tfoot>

    </table>
</div>

<?php echo $this->fetch('footer.html'); ?>