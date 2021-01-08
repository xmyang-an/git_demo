<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>会员管理</p>
  <ul class="subnav">
  	<li><a class="btn1" href="index.php?app=user">管理</a></li>
    <li><a class="btn1" href="index.php?app=user&amp;act=add">新增</a></li>
    <li><span>分销关系</span></li>
  </ul>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=user&act=get_disteam_xml';
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
			{display: '用户名', name : 'user_name', width : 100, sortable : false, align: 'center'},
			{display: '手机', name : 'phone_mob', width : 150, sortable : false, align: 'center'},
			{display: '注册时间', name : 'reg_time', width: 100, sortable : false, align : 'center'},
			{display: '累计收益', name : 'profit', width: 150, sortable : false, align : 'center'},    		
			{display: '上级用户', name : 'parentName', width: 100, sortable : false, align : 'center'},
			{display: '上级手机', name : 'parentPhone', width: 150, sortable : false, align : 'center'}
    		],
		/*searchitems : [
			{display: '用户名', name : 'user_name'},
			{display: '手机', name : 'phone_mob'}
        ],*/
    	title: '分销关系'
    });
});
</script>
<?php echo $this->fetch('footer.html'); ?> 