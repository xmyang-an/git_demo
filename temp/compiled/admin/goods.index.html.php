<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>商品</p>
  <ul class="subnav">
    <li><?php if ($_GET['closed']): ?><a class="btn1" href="index.php?app=goods">所有商品</a><?php else: ?><span>所有商品</span><?php endif; ?></li>
    <li><?php if ($_GET['closed']): ?><span>禁售商品</span><?php else: ?><a class="btn1" href="index.php?app=goods&amp;closed=1">禁售商品</a><?php endif; ?></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
      <input type="hidden" name="app" value="goods" />
      <?php if ($_GET['closed']): ?>
      <input type="hidden" name="closed" value="1" />
      <?php endif; ?> 
      商品名称:
      <input class="queryInput" type="text" name="goods_name" value="<?php echo htmlspecialchars($_GET['goods_name']); ?>" />
      店铺名:
      <input class="queryInput" type="text" name="store_name" value="<?php echo htmlspecialchars($_GET['store_name']); ?>" />
      品牌:
      <input class="queryInput" type="text" name="brand" value="<?php echo htmlspecialchars($_GET['brand']); ?>" />
      分类名称:
      <div id="gcategory" style="display:inline;">
        <select class="querySelect" name="cate_id">
          <option>请选择...</option>
          <?php echo $this->html_options(array('options'=>$this->_var['gcategories'],'selected'=>$_GET['cate_id'])); ?>
        </select>
      </div>
      <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=goods<?php if ($_GET['closed']): ?>&amp;closed=1<?php endif; ?>">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var closed = '<?php echo $_GET['closed']; ?>';
	var data_url = 'index.php?app=goods&act=get_xml&'+$("#formSearch").serialize();
	if(closed == '1'){
		data_url += '&closed=1';
	}
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '商品名称', name : 'goods_name', width : 250, sortable : true, align: 'center'},
			{display: 'ID', name : 'goods_id', width : 50, sortable : true, align: 'center'},
			{display: '价格', name : 'price', width : 50, sortable : true, align: 'center'},
    		{display: '店铺名', name : 'store_name', width : 100, sortable : true, align: 'center'},
			{display: '品牌', name : 'brand', width : 100, sortable : true, align: 'center'},
    		{display: '所属分类', name : 'cate_name', width : 250, sortable : true, align: 'center'}, 
			{display: '分销比率', name : 'distribution_rate', width : 100, sortable : false, align: 'center'},    		
			{display: '上架', name : 'if_show', width: 50, sortable : true, align : 'center'},
			{display: '禁售', name : 'closed', width: 50, sortable : true, align : 'center'},  
			{display: '浏览数', name : 'views', width: 50, sortable : true, align : 'center'} 		
    		],
        buttons : [
            {display: '<i class="fa fa-thumbs-o-up"></i>批量推荐', name : 'recommend', bclass : 'csv', title : '批量推荐', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-edit"></i>批量编辑', name : 'edit', bclass : 'add', title : '批量编辑', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],
    	sortname: "goods_id",
    	sortorder: "desc",
    	title: '商品列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if(name == 'recommend'){
		if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	    }
		window.location.href = 'index.php?app=goods&act=recommend&id='+itemlist;
	}
	if (name == 'edit') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       window.location.href = 'index.php?app=goods&act=edit&id='+itemlist;
	}
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'goods','',true);
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