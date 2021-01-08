<?php echo $this->fetch('member.header.html'); ?> 
<script type="text/javascript">
//<!CDATA[
$(function()
{
    var map = <?php echo $this->_var['map']; ?>;
    var path = "<?php echo $this->res_base . "/" . 'images/'; ?>";
    if (map.length > 0)
    {
        var option = {openImg: path + "treetable/tv-collapsable.gif", shutImg: path + "treetable/tv-expandable.gif", leafImg: path + "treetable/tv-item.gif", lastOpenImg: path + "treetable/tv-collapsable-last.gif", lastShutImg: path + "treetable/tv-expandable-last.gif", lastLeafImg: path + "treetable/tv-item-last.gif", vertLineImg: path + "treetable/vertline.gif", blankImg: path + "treetable/blank.gif", collapse: false, column: 1, striped: false, highlight: false, state:false};
        $("#treet1").jqTreeTable(map, option);
    }
    var t = new EditableTable($('#my_category'));
});
//]]>
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="eject_btn_two eject_pos3" title="新增分类" style="left:105px; width:100px;border-radius:4px 4px 0 0; top:-33px"><b class="ico3 fs13" ectype="dialog" dialog_title="新增分类" dialog_id="my_category_add" dialog_width="480" uri="index.php?app=my_category&amp;act=add">新增分类</b></div>
        <div class="eject_btn_three eject_pos1" title="导入" style="left:218px; top:-33px; "><b class="ico3 fs13"  ectype="dialog" dialog_title="导入" dialog_id="my_category_import" dialog_width="480" uri="index.php?app=my_category&amp;act=import">导入</b></div>
        <div class="eject_btn_three eject_pos4" title="导出" style="left:328px; top:-33px; border-radius:4px 4px 0 0"><b class="ico4" <?php if ($this->_var['charset'] == 'utf-8'): ?>ectype="dialog" dialog_title="导出" dialog_id="my_category_export" dialog_width="480" uri="index.php?app=my_category&amp;act=export" <?php else: ?>onclick="window.location.href='index.php?app=my_category&amp;act=export'"<?php endif; ?>>导出</b></div>
        <div class="public table">
          <table id="my_category" server="<?php echo $this->_var['site_url']; ?>/index.php?app=my_category&act=ajax_col">
            <?php if ($this->_var['gcategories']): ?>
            <tr class="gray" ectype="table_header">
              <th width="30"><input id="all" type="checkbox" class="checkall" /></th>
              <th class="align1" coltype="editable" column="cate_name" checker="check_required" inputwidth="50%">分类名称</th>
              <th class="width15 align2" coltype="editable" column="sort_order" checker="check_max" inputwidth="30px">排序</th>
              <th class="width15 align2" coltype="switchable" column="if_show" checker="" onclass="right_ico" offclass="wrong_ico">显示</th>
              <th class="width3 align1">操作</th>
            </tr>
            <?php endif; ?> 
            <?php if ($this->_var['gcategories']): ?>
            <tbody id="treet1">
              <?php endif; ?> 
              <?php $_from = $this->_var['gcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['v']['iteration']++;
?>
              <tr class="line<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?> last_line<?php endif; ?>" ectype="table_item" idvalue="<?php echo $this->_var['gcategory']['cate_id']; ?>">
                <td class="align2"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['gcategory']['cate_id']; ?>" /></td>
                <td class="width7">&nbsp;&nbsp;<span ectype="editobj"><?php echo htmlspecialchars($this->_var['gcategory']['cate_name']); ?></span></td>
                <td class="align2"><span ectype="editobj"><?php echo $this->_var['gcategory']['sort_order']; ?></span></td>
                <td><span <?php if ($this->_var['gcategory']['if_show']): ?>class="right_ico" status="on"<?php else: ?>class="wrong_ico" status="off"<?php endif; ?>ectype="editobj"></span></td>
                <td class="padding5"><?php if ($this->_var['gcategory']['layer'] < 2): ?> 
                  <a href="javascript:void(0);" ectype="dialog" dialog_width="480" dialog_title="新增下级" dialog_id="my_category_add" uri="index.php?app=my_category&amp;act=add&amp;pid=<?php echo $this->_var['gcategory']['cate_id']; ?>" class="add1_ico">新增下级</a> 
                  <?php endif; ?> 
                  <a href="javascript:void(0);" ectype="dialog" dialog_width="480" dialog_title="编辑" dialog_id="my_category_edit" uri="index.php?app=my_category&amp;act=edit&amp;id=<?php echo $this->_var['gcategory']['cate_id']; ?>" class="edit1">编辑</a> <a href="javascript:drop_confirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗', 'index.php?app=my_category&amp;act=drop&id=<?php echo $this->_var['gcategory']['cate_id']; ?>');" class="delete">删除</a></td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="5"><div class="notice-word">
                    <p>您还没有添加分类</p>
                  </div></td>
              </tr>
              <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
              <?php if ($this->_var['gcategories']): ?>
            </tbody>
            <?php endif; ?> 
            <?php if ($this->_var['gcategories']): ?>
            <tr class="btion">
              <th><input id="all2" type="checkbox" class="checkall" /></th>
              <th colspan="4"><p class="position1">
                  <label for="all2"><span class="all">全选</span></label>
                  <a href="javascript:void(0);" ectype="batchbutton" class="delete" uri="index.php?app=my_category&act=drop" name="id" presubmit="confirm('删除该分类将会同时删除该分类的所有下级分类，您确定要删除吗')">删除</a></p></th>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe name="pop_warning" style="display:none"></iframe>
<?php echo $this->fetch('member.footer.html'); ?>