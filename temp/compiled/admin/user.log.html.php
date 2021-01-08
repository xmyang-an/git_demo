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
	var data_url = 'index.php?app=user&act=getLogxml&id=<?php echo $_GET['id']; ?>';
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
			{display: '登陆名', name : 'user_name', width : 100, sortable : false, align: 'center'},
			{display: 'ip地址', name : 'ip', width : 100, sortable : false, align: 'center'},
    		{display: '所在地区', name : 'region_name', width : 150, sortable : false, align: 'center'},
			{display: '登录时间', name : 'add_time', width : 200, sortable : false, align: 'center'}	
    		],
		buttons : [
			{display: '<i class="fa fa-step-backward"></i>返回上页', name : 'go_back', bclass : 'go_back', title : '返回上页', onpress : go_back}
        ],
    	title: '登陆记录'
    });
});
function go_back() {
	history.go(-1);
}
</script>
<?php echo $this->fetch('footer.html'); ?> 