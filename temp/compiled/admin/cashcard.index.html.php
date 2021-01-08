<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>
<div id="rightTop">
  <p>充值卡管理</p>
  <ul class="subnav">
    <li><span>管理</span></li>
    <li><a class="btn1" href="index.php?app=cashcard&amp;act=add">新增</a></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="cashcard" />
          卡名称 : <input class="queryInput" type="text" name="name" value="<?php echo $_GET['name']; ?>" style="width:100px;" />
          卡号 : <input class="queryInput" type="text" name="cardNo" value="<?php echo $_GET['cardNo']; ?>" style="width:200px;" />
          生成时间从 : <input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
           到 : <input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
            制卡状态 : 
           <select name="printed">
              <option value="0" <?php if (! in_array ( $_GET['printed'] , array ( 1 , 2 ) )): ?> selected="selected"<?php endif; ?>>不限制</option>
           	  <option value="1" <?php if (in_array ( $_GET['printed'] , array ( 1 ) )): ?> selected="selected"<?php endif; ?>>未制卡</option>
              <option value="2" <?php if (in_array ( $_GET['printed'] , array ( 2 ) )): ?> selected="selected"<?php endif; ?>>已制卡</option>
           </select>
            激活状态 :
           <select name="active_time">
              <option value="0" <?php if (! in_array ( $_GET['active_time'] , array ( 1 , 2 ) )): ?> selected="selected"<?php endif; ?>>不限制</option>
           	  <option value="1" <?php if (in_array ( $_GET['active_time'] , array ( 1 ) )): ?> selected="selected"<?php endif; ?>>未激活</option>
              <option value="2" <?php if (in_array ( $_GET['active_time'] , array ( 2 ) )): ?> selected="selected"<?php endif; ?>>已激活</option>
           </select>
           <input type="submit" class="formbtn" value="查询" />
          <?php if ($this->_var['filtered']): ?>
		  <a class="formbtn formbtn1" href="index.php?app=cashcard">撤销检索</a>
		  <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=cashcard&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
			{display: '卡名称', name : 'name', width : 100, sortable : false, align: 'center'},
			{display: '卡号', name : 'cardNo', width : 150, sortable : true, align: 'center'},
    		{display: '密&nbsp;&nbsp;&nbsp;码', name : 'password', width : 150, sortable : false, align: 'center'},
			{display: '卡金额', name : 'money', width : 100, sortable : true, align: 'center'},
			{display: '使用者', name : 'user_name', width: 80, sortable : true, align : 'center'},    		
			{display: '生成时间', name : 'add_time', width: 150, sortable : true, align : 'center'},
			{display: '制卡状态', name : 'printed', width: 80, sortable : true, align : 'center'},  
			{display: '激活时间', name : 'active_time', width: 150, sortable : true, align : 'center'},
			{display: '过期时间', name : 'expire_time', width: 150, sortable : true, align : 'center'} 		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
			{display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
            {display: '<i class="fa fa-edit"></i>制卡', name : 'print', bclass : 'add', title : '将选定行数据批量制卡', onpress : fg_operate },
			{display: '<i class="fa fa-ban"></i>取消制卡', name : 'print_cancel', bclass : 'del', title : '将选定行数据批量取消制卡', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }	
        ],
    	title: '充值卡列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=cashcard&act=add';
		return false;
	}
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'cashcard');
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
	if(name == 'print'){
		if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	    }
		var url = 'index.php?app=cashcard&act=printed&value=1&id=' + itemlist;
        goConfirm('将选定行数据批量制卡',url,true);
	}
	if(name == 'print_cancel'){
		if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	    }
		var url = 'index.php?app=cashcard&act=printed&value=0&id=' + itemlist;
        goConfirm('将选定行数据批量取消制卡',url,true);
	}
}
</script>
<?php echo $this->fetch('footer.html'); ?>