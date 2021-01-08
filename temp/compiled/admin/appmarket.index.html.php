<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>应用市场</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=appmarket&amp;act=add">新增</a></li>
    </ul>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=appmarket&act=get_xml';
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
			{display: '名称', name : 'name', width : 150, sortable : false, align: 'center'},
			{display: '图片标识', name : 'logo', width : 50, sortable : false, align: 'center'},
    		{display: '标题', name : 'title', width : 150, sortable : true, align: 'center'},
			{display: '应用分类', name : 'category', width : 100, sortable : true, align: 'center'},
    		{display: '收费标准', name : 'charge', width : 100, sortable : false, align: 'center'}, 
			{display: '允许购买期限', name : 'period', width : 300, sortable : false, align: 'center'},    		
			{display: '订购数', name : 'sales', width: 50, sortable : true, align : 'center'},
			{display: '开发订购', name : 'status', width: 80, sortable : true, align : 'center'}		
    		],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '应用市场列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'edit') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       window.location.href = 'index.php?app=appmarket&act=edit&id='+itemlist;
	}
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'appmarket');
	}
}
</script>
<?php echo $this->fetch('footer.html'); ?> 
