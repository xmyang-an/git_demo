<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>会员管理</p>
  <ul class="subnav">
    <li><span>管理</span></li>
    <li><a class="btn1" href="index.php?app=user&amp;act=add">新增</a></li>
    <?php if (in_array ( $_GET['act'] , array ( 'disteam' ) )): ?>
    <li><span>分销关系</span></li>
    <?php else: ?>
    <li><a class="btn1" href="index.php?app=user&act=disteam">分销关系</a></li>
    <?php endif; ?>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
    	  <input type="hidden" name="app" value="user" />
          会员名:
          <input class="queryInput" type="text" name="user_name" value="<?php echo htmlspecialchars($_GET['user_name']); ?>" />
          真实姓名:
          <input class="queryInput" type="text" name="real_name" value="<?php echo htmlspecialchars($_GET['real_name']); ?>" />
          电子邮箱:
          <input class="queryInput" type="text" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>" />
          手机号码:
          <input class="queryInput" type="text" name="phone_mob" value="<?php echo htmlspecialchars($_GET['phone_mob']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=user">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=user&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 200, sortable : false, align: 'center', className: 'handle'},
			{display: '会员名', name : 'user_name', width : 100, sortable : true, align: 'center'},
			{display: '真实姓名', name : 'real_name', width : 100, sortable : true, align: 'center'},
			{display: '上级', name : 'refer_name', width : 100, sortable : true, align: 'center'},
    		{display: '电子邮箱', name : 'email', width : 150, sortable : true, align: 'center'},
			{display: '手机号码', name : 'phone_mob', width : 100, sortable : true, align: 'center'},
			{display: '注册时间', name : 'reg_time', width: 100, sortable : true, align : 'center'},    		
			{display: '最后登录', name : 'last_login', width: 150, sortable : true, align : 'center'},
			{display: '最后登录IP', name : 'last_ip', width: 100, sortable : true, align : 'center'},  
			{display: '登录次数', name : 'logins', width: 100, sortable : true, align : 'center'},
			{display: '是否是管理员', name : 'if_admin', width: 100, sortable : true, align : 'center'} 		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }	
        ],
    	title: '会员列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=user&act=add';
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
       fg_delete(itemlist,'user');
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