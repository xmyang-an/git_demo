<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>推荐类型</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=recommend&amp;act=add">新增</a></li>
    </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="recommend" />
          推荐类型名称:
          <input class="queryInput" type="text" name="recom_name" value="<?php echo htmlspecialchars($_GET['recom_name']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=recommend">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
    	url: 'index.php?app=recommend&act=get_xml&'+$("#formSearch").serialize(),
    	colModel : [
    		{display: '操作', name : 'operation', width : 250, sortable : false, align: 'center', className: 'handle'},
			{display: 'ID', name : 'recom_id', width : 50, sortable : true, align: 'center'},
    		{display: '推荐类型名称', name : 'recom_name', width : 200, sortable : true, align: 'center'},
			{display: '商品数', name : 'goods_count', width : 100, sortable : true, align: 'center'},	
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '推荐类型列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=recommend&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'recommend');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
       return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 