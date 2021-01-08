<?php echo $this->fetch('header.html'); ?>
<style type="text/css">
.flexigrid .bDiv{min-height:35px;}
</style>
<script type="application/ecmascript">
$(function(){
	$('.J_slide').click(function(){
		var closeImg = 'templates/style/images/treetable/tv-expandable.gif';
		var openImg = 'templates/style/images/treetable/tv-collapsable.gif';
		var key = $(this).attr('data-id');
		if($(this).attr('src') == openImg){
			$(this).attr('src',closeImg);
			$('.child_'+key).hide();
		}else{
			$(this).attr('src',openImg);
			$('.child_'+key).show();
		}
	});
	$('.J_update').click(function(){
		$.getJSON(REAL_BACKEND_URL + '/index.php?app=wxmenu&act=update',function(data){
			parent.layer.msg(data.msg);	
		});
	});
})
</script>
<div id="rightTop">
    <p>自定义菜单</p>
    <ul class="subnav">
        <li><span>菜单列表</span></li>
        <li><a class="btn1" href="index.php?app=wxmenu&amp;act=add">新增</a></li>
    </ul>
</div>
<div class="explanation" id="explanation">
  <div class="title" id="checkZoom">
  	<i class="fa fa-lightbulb-o"></i>
    <h4 title="操作提示">操作提示</h4>
  </div>
  <ul>
    <li><i class="fa fa-angle-double-right"></i> 1. 微信菜单一共是两级菜单，一级菜单不能多于3个，字数不能超过4个字;</li>
    <li><i class="fa fa-angle-double-right"></i> 2. 每个一级菜单下不能多于5个二级菜单，二级菜单字数不能超过8个字，添加菜单时，请不要超出规定。</li>
  </ul>
</div>
<div class="info2 flexigrid">
    <table  class="distinction">
        <?php if ($this->_var['menus']): ?>
        <thead>
        <tr class="tatr1">
        	<th class="w30"><input id="checkall_1" type="checkbox" class="checkall" /></th>
            <th><span class="all_checkbox">
                    <label for="checkall_1">全选</label>
                    </span>菜单名称</td>
            <th width="10%">菜单类型</th>
            <th width="20%">菜单值</th>
            <th width="10%">排序</th>
            <th class="handler">操作</th>
        </tr>
        </thead>
        <?php endif; ?>
        <?php if ($this->_var['menus']): ?><tbody id="treet1"><?php endif; ?>
        <?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['menu']):
?>
        <tr>
        	<td class="align_center w30"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['menu']['id']; ?>" /></td>
            <td><?php if ($this->_var['menu']['child']): ?><img src="templates/style/images/treetable/tv-collapsable.gif" data-id="<?php echo $this->_var['key']; ?>" class="J_slide"><?php else: ?><img src="templates/style/images/treetable/tv-item.gif" class="ttimage" id="treet12"><?php endif; ?> <span><?php echo htmlspecialchars($this->_var['menu']['name']); ?></span></td>
            <td class="align_center"><span><?php if ($this->_var['menu']['type'] == 'view'): ?>跳转网页<?php else: ?>发送消息<?php endif; ?></span></td>
            <td class="align_center"><?php if ($this->_var['menu']['type'] == 'view'): ?><?php echo ($this->_var['menu']['link'] == '') ? '-' : $this->_var['menu']['link']; ?><?php else: ?>-<?php endif; ?></td>
            <td class="align_center"><span><?php echo $this->_var['menu']['sort_order']; ?></span></td>
            <td class="handler bDiv" style=" background:none; width:250px; text-align:left;">
                <a href="index.php?app=wxmenu&amp;act=edit&amp;id=<?php echo $this->_var['menu']['id']; ?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a>
                <a href="javascript:goConfirm('您确定要删除它吗？', 'index.php?app=wxmenu&amp;act=drop&amp;id=<?php echo $this->_var['menu']['id']; ?>',true);" class="btn red"><i class="fa fa-trash-o"></i>删除</a>
                <a href="index.php?app=wxmenu&amp;act=add&amp;parent_id=<?php echo $this->_var['menu']['id']; ?>" class="btn green"><i class="fa fa-plus"></i>添加子菜单</a>
                </td>
        </tr>
        <?php if ($this->_var['menu']['child']): ?>
        <?php $_from = $this->_var['menu']['child']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
        <tr class="child_<?php echo $this->_var['key']; ?>">
        	<td class="align_center w30"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['child']['id']; ?>" /></td>
            <td style="padding-left:20px;"><img src="templates/style/images/treetable/tv-item.gif" class="ttimage" id="treet12"> <span><?php echo htmlspecialchars($this->_var['child']['name']); ?></span></td>
            <td class="align_center"><span><?php if ($this->_var['child']['type'] == 'view'): ?>跳转网页<?php else: ?>发送消息<?php endif; ?></span></td>
            <td class="align_center"><?php if ($this->_var['child']['type'] == 'view'): ?><?php echo ($this->_var['child']['link'] == '') ? '-' : $this->_var['child']['link']; ?><?php else: ?>-<?php endif; ?></td>
            <td class="align_center"><span><?php echo $this->_var['child']['sort_order']; ?></span></td>
            <td class="handler bDiv" style=" background:none; width:250px; text-align:left;">
                <a href="index.php?app=wxmenu&amp;act=edit&amp;id=<?php echo $this->_var['child']['id']; ?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a>
                <a href="javascript:goConfirm('您确定要删除它吗？', 'index.php?app=wxmenu&amp;act=drop&amp;id=<?php echo $this->_var['child']['id']; ?>',true);" class="btn red"><i class="fa fa-trash-o"></i>删除</a>
                </td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php endif; ?>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="6">没有符合条件的记录</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php if ($this->_var['menus']): ?></tbody><?php endif; ?>
        <tfoot>
            <tr class="tr_pt10">
            <?php if ($this->_var['menus']): ?>
            	<td class="align_center">
                	<label for="checkall1">
                    	<input id="checkall_2" type="checkbox" class="checkall">
                    </label>
                </td>
                <td colspan="5" id="batchAction">
                	<span class="all_checkbox mt5">
                    <label for="checkall_2">全选</label>
                    </span>&nbsp;&nbsp;
                    <div class="fbutton JBatchDel" uri="index.php?app=wxmenu&act=drop" name="id"><div class="del" title="将选定行数据批量删除"><span><i class="fa fa-trash"></i>批量删除</span></div></div>
                    <div class="fbutton J_update" style="border:0;"><div class="add"><span><i class="fa fa-trash"></i>生成菜单</span></div></div>
                </td>
            <?php endif; ?>
            </tr>
        </tfoot>
    </table>
</div>

<?php echo $this->fetch('footer.html'); ?>