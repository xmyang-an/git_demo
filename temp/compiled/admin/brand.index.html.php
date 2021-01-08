<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <ul class="subnav">
    <?php if ($_GET['wait_verify']): ?>
    <li><a class="btn1" href="index.php?app=brand">商品品牌</a></li>
    <?php else: ?>
    <li><span>商品品牌</span></li>
    <?php endif; ?>
    <?php if ($_GET['wait_verify']): ?>
    <li><span>待审核</span></li>
    <?php else: ?>
    <li><a class="btn1" href="index.php?app=brand&amp;wait_verify=1">待审核</a></li>
    <?php endif; ?>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="brand" />
          <?php if ($_GET['wait_verify']): ?>
          <input type="hidden" name="wait_verify" value="<?php echo $_GET['wait_verify']; ?>" />
          <?php endif; ?>
          品牌名称:
          <input class="queryInput" type="text" name="brand_name" value="<?php echo htmlspecialchars($_GET['brand_name']); ?>" />
          类别:
          <input class="queryInput" type="text" name="tag" value="<?php echo htmlspecialchars($_GET['tag']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=brand">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var wait_verify = '<?php echo $_GET['wait_verify']; ?>';
	var data_url = 'index.php?app=brand&act=get_xml&'+$("#formSearch").serialize();
	if(wait_verify == '1'){
		data_url += '&wait_verify=1';
	}
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '品牌ID', name : 'brand_id', width : 50, sortable : true, align: 'center'},
    		{display: '品牌名称', name : 'brand_name', width : 200, sortable : true, align: 'center'},
			{display: '类别', name : 'tag', width : 100, sortable : true, align: 'left'},
    		{display: '图片标识', name : 'brand_logo', width : 150, sortable : true, align: 'center'},    		
			{display: '排序', name : 'sort_order', width: 50, sortable : true, align : 'center'},
			{display: '推荐', name : 'recommended', width: 50, sortable : true, align : 'center'},  
			{display: '显示', name : 'if_show', width: 50, sortable : true, align : 'center'} 		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	sortname: "sort_order",
    	sortorder: "asc",
    	title: '品牌列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=brand&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'brand');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
       return false;
    }
}
function fg_apply(id) {
	if (typeof id == 'number') {
    	var id = new Array(id.toString());
	};
	parent.layer.confirm('您确定要通过品牌申请吗？',{btn: ['通过', '拒绝'],icon: 3, title:'提示'},function(index){
		id = id.join(',');
		$.ajax({
			type: "GET",
			dataType: "json",
			url: "index.php?app=brand&act=pass",
			data: "id="+id,
			success: function(data){
				if (data.done){
					$("#flexigrid").flexReload();
				} else {
					parent.layer.alert(data.msg);
				}
			}
		});
		parent.layer.close(index);
	},function(index){
		parent.layer.prompt({
			formType: 2,
			value: '',
			title: '拒绝理由'
		}, function(value, index, elem){
			$.ajax({
				type: "GET",
				dataType: "json",
				url: "index.php?app=brand&act=refuse",
				data: "id="+id+"&content="+value,
				success: function(data){
					if (data.done){
						parent.layer.close(index);
						$("#flexigrid").flexReload();
					} else {
						parent.layer.alert(data.msg);
					}
				}
			});
		});
		parent.layer.close(index);
	});	
}
</script>
<?php echo $this->fetch('footer.html'); ?> 
