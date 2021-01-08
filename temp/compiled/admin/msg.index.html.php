<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
	<p>手机短信管理</p>
    <ul class="subnav" style="margin-left:0px;">
        <li><span>发送记录</span></li>
        <li><a class="btn1" href="index.php?app=msg&act=user">短信用户</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=add">分配短信</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=send">短信发送</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=setting">设置</a></li>
    </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
    	  <input type="hidden" name="app" value="msg" />
          手机号码 : 
          <input class="queryInput" type="text" name="to_mobile" value="<?php echo htmlspecialchars($_GET['to_mobile']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=msg">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=msg&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
			{display: '手机号码', name : 'to_mobile', width : 100, sortable : true, align: 'center'},
			{display: '内 容', name : 'content', width : 350, sortable : true, align: 'left'},
    		{display: '数量', name : 'quantity', width : 50, sortable : true, align: 'center'},
			{display: '时间', name : 'time', width : 150, sortable : true, align: 'center'},
    		{display: '用户名', name : 'user_name', width : 100, sortable : true, align: 'center'}, 
			{display: '状态', name : 'state', width : 50, sortable : true, align: 'center'},    		
			{display: '说明', name : 'result', width: 200, sortable : true, align : 'center'}		
    		],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '发送记录列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'msg');
	}
}
</script>
<?php echo $this->fetch('footer.html'); ?>