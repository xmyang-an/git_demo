<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'ajax_tree_prop.js'; ?>" charset="utf-8"></script>
<div id="rightTop">
  <p>商品属性</p>
  <ul class="subnav">
    <li><span>属性列表</span></li>
    <li><a class="btn1" href="index.php?app=props&amp;act=add">添加属性</a></li>
    <li><a class="btn1" href="index.php?app=gcategory">分配属性</a></li>
  </ul>
</div>
<div class="info2 flexigrid">
  <form method="get">
    <input type="hidden" name="app" value="props" />
    <input type="hidden" name="act" value="drop" />
    <table  class="distinction">
      <thead>
        <tr class="tatr1">
          <th><input id="checkall_1" type="checkbox" class="checkall" /></th>
          <th width="50%"><span class="all_checkbox">
            <label for="checkall_1">全选</label>
            </span>属性名 / 属性值</th>
          <th>排序</th>
          <th>启用</th>
          <th class="handler">操作</th>
        </tr>
      </thead>
      
      <tbody id="treet1">
     	<?php if ($this->_var['prop_list']): ?>
        <?php $_from = $this->_var['prop_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');if (count($_from)):
    foreach ($_from AS $this->_var['prop']):
?>
        <tr>
          <td class="align_center w30"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['prop']['pid']; ?>"  name="pid[]"/></td>
          <td class="node" width="50%"><img src="templates/style/images/treetable/tv-expandable.gif" ectype="flex" status="open" fieldid="<?php echo $this->_var['prop']['pid']; ?>"> <span><?php echo htmlspecialchars($this->_var['prop']['name']); ?></span></td>
          <td class="align_center"><span><?php echo $this->_var['prop']['sort_order']; ?></span></td>
          <td class="align_center"><?php if ($this->_var['prop']['status']): ?><img src="templates/style/images/positive_enabled.gif" /><?php else: ?><img src="templates/style/images/positive_disabled.gif"/><?php endif; ?></td>
          <td class="handler bDiv" style=" background:none; width:280px; text-align:left;"><a href="index.php?app=props&amp;act=edit&amp;pid=<?php echo $this->_var['prop']['pid']; ?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a> <a href="javascript:goConfirm('删除该属性会同时删除该属性下面的所有属性值，你确定要删除吗？', 'index.php?app=props&amp;act=drop&amp;pid=<?php echo $this->_var['prop']['pid']; ?>',true);" class="btn red"><i class="fa fa-trash-o"></i>删除</a> <a href="index.php?app=props&amp;act=add_value&amp;pid=<?php echo $this->_var['prop']['pid']; ?>" class="btn green"><i class="fa fa-plus"></i>新增属性值</a></span></td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php else: ?>
        <tr>
          <td colspan="5" class="no-data"><i class="fa fa-exclamation-circle"></i>没有符合条件的记录</td>
        </tr>
        <?php endif; ?>
      </tbody>
      
      <tfoot>
        <tr class="tr_pt10">
          <td class="align_center"><input id="checkall_2" type="checkbox" class="checkall"></td>
          <td colspan="4" id="batchAction"><span class="all_checkbox mt5">
            <label for="checkall_2">全选</label>
            </span>&nbsp;&nbsp;
            <div class="fbutton J_FormSubmit" style="border:0;">
              <div class="del" title="将选定行数据批量删除"><span><i class="fa fa-trash"></i>批量删除</span></div>
            </div></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>