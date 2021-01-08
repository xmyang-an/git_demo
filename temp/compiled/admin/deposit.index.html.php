<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>
<div id="rightTop">
  <p>预存款管理</p>
  <ul class="subnav">
    <li><span>管理</span></li>
    <li><a class="btn1" href="index.php?app=deposit&amp;act=tradelist">交易记录</a></li>
    <li><a class="btn1" href="index.php?app=deposit&amp;act=drawlist">提现管理</a></li>
    <li><a class="btn1" href="index.php?app=deposit&amp;act=rechargelist">充值管理</a></li>
    <li><a class="btn1" href="index.php?app=deposit&amp;act=setting">系统设置</a></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
      <input type="hidden" name="app" value="deposit" />
      <select class="querySelect" name="field"><?php echo $this->html_options(array('options'=>$this->_var['search_options'],'selected'=>$_GET['field'])); ?>
      </select>: <input class="queryInput" type="text" name="search_name" value="<?php echo $_GET['search_name']; ?>" />
      开启余额支付 :
      <select class="querySelect" name="pay_status">
          <option value="">不限制</option>
          <?php echo $this->html_options(array('options'=>$this->_var['pay_status_list'],'selected'=>$_GET['pay_status'])); ?>
      </select>
      创建时间从 : <input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
      至 : <input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
      金钱从 : <input class="queryInput2" type="text" value="<?php echo $this->_var['query']['money_from']; ?>" name="money_from" />
      至 : <input class="queryInput2" type="text" style="width:60px;" value="<?php echo $this->_var['query']['money_to']; ?>" name="money_to" class="pick_date" />
      <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=deposit">撤销检索</a>
      <?php endif; ?>
   </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=deposit&act=get_account_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '账户', name : 'account', width : 200, sortable : true, align: 'center'},
			{display: '真实姓名', name : 'real_name', width : 100, sortable : true, align: 'center'},
    		{display: '用户名', name : 'user_name', width : 100, sortable : false, align: 'center'},
			{display: '金钱', name : 'money', width : 100, sortable : true, align: 'center'},
    		{display: '冻结', name : 'frozen', width : 100, sortable : true, align: 'center'},    		
			{display: '开启余额支付', name : 'pay_status', width: 100, sortable : true, align : 'center'},
			{display: '创建时间', name : 'add_time', width: 150, sortable : true, align : 'center'},  		
    		],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],

    	sortname: "add_time",
    	sortorder: "desc",
    	title: '预存款账号列表'
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
       fg_delete(itemlist,'deposit');
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