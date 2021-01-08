<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>举报管理</p>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=report&act=get_xml';
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '举报时间', name : 'add_time', width : 120, sortable : true, align: 'center'},
			{display: '举报人', name : 'user_name', width : 50, sortable : false, align: 'center'},
			{display: '举报商品', name : 'goods_name', width : 250, sortable : false, align: 'center'},
    		{display: '被举报人', name : 'store_name', width : 50, sortable : false, align: 'center'},
			{display: '举报内容', name : 'content', width : 200, sortable : false, align: 'center'},
    		{display: '上传证明', name : 'files', width : 160, sortable : false, align: 'center'}, 
			{display: '状态', name : 'status', width: 50, sortable : true, align : 'center'}		
    		],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-edit"></i>批量审核', name : 'verify', bclass : 'add', title : '批量审核', onpress : fg_operate }
        ],
		searchitems : [
			{display: '举报人', name : 'user_name'},
			{display: '举报商品', name : 'goods_name'},
            {display: '被举报人', name : 'store_name'}
        ],
    	sortname: "report_id",
    	sortorder: "desc",
    	title: '举报列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'verify') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_verify(itemlist);
	}
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
	   
       fg_delete(itemlist,'report');
	}
}

function fg_verify(id){
	parent.layer.confirm('请您认真核对信息，并做审核操作！',{icon: 3, title:'提示',btn: ['同意', '拒绝']},function(index){
		parent.layer.prompt({
			formType: 2,
			value: '',
			title: '请提交审核意见'
		}, function(value, index, elem){
			$.ajax({
				type: "GET",
				dataType: "json",
				url: 'index.php?app=report&act=verify',
				data: "id="+id+"&verify="+value,
				success: function(data){
					if (data.done){
						parent.layer.alert('审核成功');
						parent.layer.close(index);
						$("#flexigrid").flexReload();
					} else {
						parent.layer.alert(data.msg);
					}
				},
				error: function(data){
					parent.layer.alert(data.msg);
				}
			});
			parent.layer.close(index);
		});
	},function(index){
		parent.layer.close(index);
	});	
}
</script>
<?php echo $this->fetch('footer.html'); ?> 