<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <ul class="subnav">
    <li><span>素材管理</span></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="material" />
          设备：
    	  <select id="device" name="device">
          	<option value="">请选择...</option>
          	<?php echo $this->html_options(array('options'=>$this->_var['devices'],'selected'=>$_GET['device'])); ?>
          </select>
          类型：
    	  <select id="device" name="type">
          	<option value="">请选择...</option>
          	<?php echo $this->html_options(array('options'=>$this->_var['types'],'selected'=>$_GET['type'])); ?>
          </select>
          名称:
          <input class="queryInput" type="text" name="name" value="<?php echo htmlspecialchars($_GET['name']); ?>" />
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=material">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
	var data_url = 'index.php?app=material&act=get_xml&'+$("#formSearch").serialize();

    $("#flexigrid").flexigrid({
    	url: data_url,
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '素材名称', name : 'name', width : 100, sortable : true, align: 'center'},
    		{display: '图片', name : 'url', width : 150, sortable : true, align: 'center'},
			{display: '链接', name : 'link', width : 200, sortable : true, align: 'center'},
    		{display: '类型', name : 'type', width : 100, sortable : true, align: 'center'},    		
			{display: '设备', name : 'device', width : 100, sortable : true, align: 'center'},    		
			{display: '排序', name : 'sort_order', width: 50, sortable : true, align : 'center'},
			{display: '显示', name : 'if_show', width: 50, sortable : true, align : 'center'} 		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '素材列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=material&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'material');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
       return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 
