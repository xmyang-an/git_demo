<?php echo $this->fetch('header.html'); ?>
<style type="text/css">
.flexigrid .bDiv{min-height:35px;}
</style>
<div id="rightTop">
    <p>商品分类</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=add">新增</a></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=export">导出</a></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=import">导入</a></li>
    </ul>
</div>
<div class="info2 flexigrid">
    <table  class="distinction">
        <?php if ($this->_var['gcategories']): ?>
        <thead>
        <tr class="tatr1">
            <th class="w30"><input id="checkall_1" type="checkbox" class="checkall" /></th>
            <th width="50%"><span class="all_checkbox"><label for="checkall_1">全选</label></span>分类名称</td>
			<th>ID</th>
            <th>分组</th>
            <th>排序</th>
            <th>显示</th>
            <th class="handler">操作</th>
        </tr>
        </thead>
        <?php endif; ?>
        <?php if ($this->_var['gcategories']): ?><tbody id="treet1"><?php endif; ?>
        <?php $_from = $this->_var['gcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');if (count($_from)):
    foreach ($_from AS $this->_var['gcategory']):
?>
        <tr>
            <td class="align_center w30"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['gcategory']['cate_id']; ?>" /></td>
            <td class="node" width="50%"><?php if ($this->_var['gcategory']['switchs']): ?><img src="templates/style/images/treetable/tv-expandable.gif" ectype="flex" status="open" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>"><?php else: ?><img src="templates/style/images/treetable/tv-item.gif"><?php endif; ?><span class="node_name editable" ectype="inline_edit" fieldname="cate_name" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>" required="1" title="可编辑"><span><?php echo htmlspecialchars($this->_var['gcategory']['cate_name']); ?></span></td>
			<td class="align_center w30"><?php echo $this->_var['gcategory']['cate_id']; ?></span></td>
            <td class="align_center"><span class="editable" ectype="inline_edit" fieldname="groupid" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>" datatype="number" title="可编辑"><?php echo ($this->_var['gcategory']['groupid'] == '') ? '-' : $this->_var['gcategory']['groupid']; ?></span></td>
            <td class="align_center"><span class="editable" ectype="inline_edit" fieldname="sort_order" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>" datatype="number" title="可编辑"><?php echo $this->_var['gcategory']['sort_order']; ?></span></td>
            <td class="align_center"><?php if ($this->_var['gcategory']['if_show']): ?><img src="templates/style/images/positive_enabled.gif" ectype="inline_edit" fieldname="if_show" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>" fieldvalue="1" title="可编辑"/><?php else: ?><img src="templates/style/images/positive_disabled.gif" ectype="inline_edit" fieldname="if_show" fieldid="<?php echo $this->_var['gcategory']['cate_id']; ?>" fieldvalue="0" title="可编辑"/><?php endif; ?></td>
            <td class="handler bDiv" style=" background:none; width:400px; text-align:left;">
                <a href="index.php?app=gcategory&amp;act=edit&amp;id=<?php echo $this->_var['gcategory']['cate_id']; ?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a>
                <a href="javascript:goConfirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗', 'index.php?app=gcategory&amp;act=drop&amp;id=<?php echo $this->_var['gcategory']['cate_id']; ?>',true);" class="btn red"><i class="fa fa-trash-o"></i>删除</a>
                <a href="index.php?app=gcategory&amp;act=add&amp;pid=<?php echo $this->_var['gcategory']['cate_id']; ?>" class="btn green"><i class="fa fa-plus"></i>新增下级</a>
                <a href='index.php?app=props&act=distribute&cate_id=<?php echo $this->_var['gcategory']['cate_id']; ?>' class="btn blue"><i class="fa fa-list-ul"></i>分配属性</a>
				
				<?php if ($this->_var['gcategory']['parent_id'] == 0): ?>
                <a href='index.php?app=gcategory&amp;act=gads&amp;id=<?php echo $this->_var['gcategory']['cate_id']; ?>' class="btn green"><i class="fa fa-file-image-o"></i>广告图</a>
				<?php endif; ?>
                </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="6">暂无商品分类</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php if ($this->_var['gcategories']): ?></tbody><?php endif; ?>
        <tfoot>
            <tr class="tr_pt10">
            <?php if ($this->_var['gcategory']): ?>
                <td class="align_center"><input id="checkall_2" type="checkbox" class="checkall"></td>
                <td colspan="6" id="batchAction">
                    <span class="all_checkbox mt5"><label for="checkall_2">全选</label></span>&nbsp;&nbsp;
                    <div class="fbutton batchButton" style="border:0;" uri="index.php?app=gcategory&act=batch_edit" name="id"><div class="add"><span><i class="fa fa-edit"></i>批量编辑</span></div></div>
                    <div class="fbutton JBatchDel" style="border:0;" uri="index.php?app=gcategory&act=drop" name="id"><div class="del" title="将选定行数据批量删除"><span><i class="fa fa-trash"></i>批量删除</span></div></div>
                </td>
            <?php endif; ?>
            </tr>
        </tfoot>
    </table>
</div>

<?php echo $this->fetch('footer.html'); ?>