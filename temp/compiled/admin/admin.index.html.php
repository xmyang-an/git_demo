<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>管理员管理</p>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="admin" />
          用户名:
          <input class="queryInput" type="text" name="user_name" value="<?php echo htmlspecialchars($_GET['user_name']); ?>" />
          真实姓名:
          <input class="queryInput" type="text" name="real_name" value="<?php echo htmlspecialchars($_GET['real_name']); ?>" />
          电子邮件:
          <input class="queryInput" type="text" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>" />
          手机:
          <input class="queryInput" type="text" name="phone_mob" value="<?php echo htmlspecialchars($_GET['phone_mob']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=admin">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=admin&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '用户名', name : 'user_name', width : 100, sortable : true, align: 'center'},
			{display: '真实姓名', name : 'real_name', width : 100, sortable : true, align: 'center'},
    		{display: '电子邮件', name : 'email', width : 150, sortable : true, align: 'center'},
			{display: '手机', name : 'phone_mob', width : 80, sortable : true, align: 'center'},
			{display: '注册时间', name : 'reg_time', width: 100, sortable : true, align : 'center'},    		
			{display: '上次登录', name : 'last_login', width: 150, sortable : true, align : 'center'},
			{display: '最后登录IP', name : 'last_ip', width: 100, sortable : true, align : 'center'},  
			{display: '登录次数', name : 'logins', width: 100, sortable : true, align : 'center'}		
    		],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '管理员列表'
    });
});
function fg_operate(name, bDiv) {
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'admin');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
        return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 