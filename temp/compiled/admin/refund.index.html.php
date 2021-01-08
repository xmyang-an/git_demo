<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>退款管理</p>
    <ul class="subnav">
        <li><span>管理</span></li>
    </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
    	  <input type="hidden" name="app" value="refund" />
          退款单编号 : 
          <input class="queryInput" type="text" name="refund_sn" value="<?php echo htmlspecialchars($_GET['refund_sn']); ?>" />
          买家 : 
          <input class="queryInput" type="text" name="buyer_name" value="<?php echo htmlspecialchars($_GET['buyer_name']); ?>" />
          店铺名称 : 
          <input class="queryInput" type="text" name="store_name" value="<?php echo htmlspecialchars($_GET['store_name']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=refund">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=refund&act=get_xml&'+$("#formSearch").serialize();
    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 50, sortable : false, align: 'center', className: 'handle'},
			{display: '退款单编号', name : 'refund_sn', width : 100, sortable : true, align: 'center'},
			{display: '买家', name : 'buyer_name', width : 100, sortable : true, align: 'center'},
    		{display: '店铺名称', name : 'store_name', width : 100, sortable : true, align: 'center'},    		
			{display: '交易金额', name : 'total_fee', width: 80, sortable : true, align : 'center'},
			{display: '退款金额', name : 'refund_goods_fee', width: 80, sortable : true, align : 'center'}, 
			{display: '退运费', name : 'refund_shipping_fee', width : 80, sortable : true, align: 'center'},
    		{display: '申请时间', name : 'created', width : 150, sortable : true, align: 'center'},    		
			{display: '退款状态', name : 'status', width: 200, sortable : true, align : 'center'},
			{display: '客服介入', name : 'ask_customer', width: 100, sortable : true, align : 'center'},  		
    		],
        buttons : [
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],
    	sortname: "created",
    	sortorder: "desc",
    	title: '退款单列表'
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