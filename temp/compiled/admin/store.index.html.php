<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>店铺管理</p>
  <ul class="subnav">
    <?php if ($_GET['wait_verify']): ?>
    <li><a class="btn1" href="index.php?app=store">管理</a></li>
    <?php else: ?>
    <li><span>管理</span></li>
    <?php endif; ?>
    <li><a class="btn1" href="index.php?app=store&amp;act=test">新增</a></li>
    <?php if ($_GET['wait_verify'] == 1): ?>
    <li><span>待审核</span></li>
    <?php else: ?>
    <li><a class="btn1" href="index.php?app=store&amp;wait_verify=1">待审核</a></li>
    <?php endif; ?>
	<?php if ($_GET['wait_verify'] == 3): ?>
	<li><span>已拒绝</span></li>
	<?php else: ?>
    <li><a class="btn1" href="index.php?app=store&amp;wait_verify=3">已拒绝</a></li>
    <?php endif; ?>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
      <input type="hidden" name="app" value="store" />
      <?php if ($_GET['wait_verify']): ?>
      <input type="hidden" name="wait_verify" value="<?php echo $_GET['wait_verify']; ?>" />
      <?php endif; ?>
      店主 : 
      <input class="queryInput" type="text" name="owner_name" value="<?php echo htmlspecialchars($_GET['owner_name']); ?>" />
      店铺名称 : 
      <input class="queryInput" type="text" name="store_name" value="<?php echo htmlspecialchars($_GET['store_name']); ?>" />
      所属等级 : 
      <select class="querySelect" name="sgrade">
        <option value="">请选择...</option>
        <?php echo $this->html_options(array('options'=>$this->_var['sgrades'],'selected'=>$_GET['sgrade'])); ?>
      </select>
      <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=store<?php if ($_GET['wait_verify']): ?>&amp;wait_verify=<?php echo $_GET['wait_verify']; ?><?php endif; ?>">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var wait_verify = '<?php echo $_GET['wait_verify']; ?>';
	var data_url = 'index.php?app=store&act=get_xml&'+$("#formSearch").serialize();
	if(wait_verify == '1'){
		data_url += '&wait_verify=1';
	}
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '用户名', name : 'user_name', width : 50, sortable : true, align: 'center'},
			{display: '店主', name : 'owner_name', width : 100, sortable : true, align: 'center'},
    		{display: '店铺名称', name : 'store_name', width : 100, sortable : true, align: 'center'},
			{display: '所在地区', name : 'region_name', width : 200, sortable : true, align: 'center'},
    		{display: '所属等级', name : 'sgrade', width : 100, sortable : true, align: 'center'},
			{display: '开店时间', name : 'add_time', width: 100, sortable : true, align : 'center'},    		

			{display: '状态', name : 'state', width: 50, sortable : true, align : 'center'},  
			{display: '排序', name : 'sort_order', width: 50, sortable : true, align : 'center'},
			{display: '推荐', name : 'recommended', width: 50, sortable : true, align : 'center'},
			{display: '开启分销', name : 'enable_distribution', width: 80, sortable : true, align : 'center'}	
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
			{display: '<i class="fa fa-edit"></i>批量编辑', name : 'edit', bclass : 'csv', title : '批量编辑', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],
    	sortname: "sort_order",
    	sortorder: "asc",
    	title: '店铺列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=store&act=test';
		return false;
	}
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'edit') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       window.location.href = 'index.php?app=store&act=batch_edit&id='+itemlist;
	}
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'store');
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