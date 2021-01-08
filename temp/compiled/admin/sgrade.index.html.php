<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>店铺等级</p>
  <ul class="subnav">
    <li><span>管理</span></li>
    <li><a class="btn1" href="index.php?app=sgrade&amp;act=add">新增</a></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="sgrade" />
          等级名称:
          <input class="queryInput" type="text" name="grade_name" value="<?php echo htmlspecialchars($_GET['grade_name']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=sgrade">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
    	url: 'index.php?app=sgrade&act=get_xml&'+$("#formSearch").serialize(),
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '等级名称', name : 'grade_name', width : 150, sortable : true, align: 'center'},
			{display: '允许发布商品数', name : 'goods_limit', width : 150, sortable : true, align: 'center'},
    		{display: '上传空间大小(MB)', name : 'space_limit', width : 150, sortable : true, align: 'center'},
			{display: '可选模板套数', name : 'skin_limit', width : 100, sortable : true, align: 'center'},
    		{display: '收费标准', name : 'charge', width : 150, sortable : true, align: 'center'},    		
			{display: '需要审核', name : 'need_confirm', width: 100, sortable : true, align : 'center'}		
    		],
        buttons : [
			{display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '店铺等级列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=sgrade&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'sgrade');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
        return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 