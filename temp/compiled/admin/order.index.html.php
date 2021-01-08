<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>
<div id="rightTop">
	<p>订单管理</p>
	<ul class="subnav">
		<?php if ($_GET['type'] == 'distribution'): ?>
		<li><a class="btn1" href="index.php?app=order">所有订单</a></li>
		<li><span>分销订单</span></li>
		<?php else: ?>
		<li><span>所有订单</span></li>
		<li><a class="btn1" href="index.php?app=order&type=distribution">分销订单</a></li>
		<?php endif; ?>
	</ul>
</div>
<div class="search-form clearfix">
	<form method="get" id="formSearch">
		<input type="hidden" name="app" value="order" />
		<input type="hidden" name="type" value="<?php echo $_GET['type']; ?>" />
		<select class="querySelect" name="field">
			<?php echo $this->html_options(array('options'=>$this->_var['search_options'],'selected'=>$_GET['field'])); ?>
		</select>:
		<input class="queryInput" type="text" name="search_name" value="<?php echo htmlspecialchars($this->_var['query']['search_name']); ?>" />
		<select class="querySelect" name="status">
			<option value="">订单状态</option>
            <?php echo $this->html_options(array('options'=>$this->_var['order_status_list'],'selected'=>$this->_var['query']['status'])); ?> 
		</select>
		下单时间从 :
		<input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_from']; ?>" id="add_time_from" name="add_time_from" class="pick_date" />
		至 :
		<input class="queryInput" type="text" value="<?php echo $this->_var['query']['add_time_to']; ?>" id="add_time_to" name="add_time_to" class="pick_date" />
		订单金额从 :
		<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['order_amount_from']; ?>" name="order_amount_from" />
		至 :
		<input class="queryInput2" type="text" style="width:60px;" value="<?php echo $this->_var['query']['order_amount_to']; ?>" name="order_amount_to" class="pick_date" />
		<input type="submit" class="formbtn" value="查询" />
		<?php if ($this->_var['filtered']): ?> 
		<a class="formbtn formbtn1" href="index.php?app=order<?php if ($_GET['type']): ?>&amp;type=<?php echo $_GET['type']; ?><?php endif; ?>">撤销检索</a> 
		<?php endif; ?>
	</form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var type = '<?php echo $_GET['type']; ?>';
	var data_url = 'index.php?app=order&act=get_xml&'+$("#formSearch").serialize();
	if(type == 'distribution'){
		data_url = data_url+'&type='+type;
	}
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
			{display: '订单号', name : 'order_sn', width : 100, sortable : true, align: 'center'},
			{display: '店铺名称', name : 'seller_name', width : 100, sortable : true, align: 'center'},
    		{display: '下单时间', name : 'add_time', width : 200, sortable : true, align: 'center'},
			{display: '买家名称', name : 'buyer_name', width : 100, sortable : true, align: 'center'},
    		{display: '订单总价', name : 'order_amount', width : 80, sortable : true, align: 'center'},    		
			{display: '支付方式', name : 'payment_name', width: 100, sortable : true, align : 'center'},
			{display: '订单状态', name : 'status', width: 100, sortable : true, align : 'center'}	,
			{display: '是否分销', name : 'distribution', width: 100, sortable : true, align : 'center'}			
    		],
		buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '导出数据', onpress : fg_operate }
        ],
    	sortname: "add_time",
    	sortorder: "desc",
    	title: '订单列表'
    });
});
function fg_operate(name, bDiv) {
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
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