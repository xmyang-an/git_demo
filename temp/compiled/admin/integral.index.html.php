<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
	<p>积分管理</p>
	<ul class="subnav">
    	<li><span>积分列表</span></li>
        <li><a class="btn1" href="index.php?app=integral&act=setting">积分设置</a></li>
    </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
    	  <input type="hidden" name="app" value="integral" />
          会员名:
          <input class="queryInput" type="text" name="user_name" value="<?php echo htmlspecialchars($_GET['user_name']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=integral">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=integral&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
			{display: 'ID', name : 'user_id', width : 200, sortable : true, align: 'center'},
			{display: '会员名', name : 'user_name', width : 200, sortable : true, align: 'center'},
			{display: '积分', name : 'amount', width : 200, sortable : true, align: 'center'}		
    		],
        buttons : [
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],
    	sortname: "amount",
    	sortorder: "desc",
    	title: '积分列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'integral');
	}
	if(name == 'csv'){
		if($('.trSelected',bDiv).length==0){
		   parent.layer.confirm('您确定要下载全部数据吗？',{icon: 3, title:'提示'},function(index){
				fg_csv(itemlist);
				parent.layer.close(index);
			},function(index){
				parent.layer.close(index);
			});
	   }else{
		   fg_csv(itemlist);
	   }
	}
}
</script>
<?php echo $this->fetch('footer.html'); ?> 