<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>推荐店铺</p>
  <ul class="subnav">
        <li><span>管理</span></li>
        <?php if ($_GET['act'] != 'add'): ?>
    	<li><a class="btn1" href="index.php?app=ultimate_store&act=add">新增</a></li>
    	<?php endif; ?>
  	</ul>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
    	url: 'index.php?app=ultimate_store&act=get_xml',
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '店铺名称', name : 'store_name', width : 200, sortable : false, align: 'center'},
    		{display: '相关联的品牌', name : 'brand_name', width : 100, sortable : false, align: 'center'},
			{display: '关联的商品分类', name : 'cate_name', width : 400, sortable : false, align: 'center'},
    		{display: '关键字', name : 'keyword', width : 150, sortable : false, align: 'center'},    		
			{display: '状态', name : 'status', width: 50, sortable : false, align : 'center'},		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '推荐店铺列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=ultimate_store&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'ultimate_store');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
       return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 