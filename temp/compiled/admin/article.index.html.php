<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>文章管理</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=article&amp;act=add">新增</a></li>
    </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
    	  <input type="hidden" name="app" value="article" />
          标题:
          <input class="queryInput" type="text" name="title" value="<?php echo htmlspecialchars($_GET['title']); ?>" />
          文章分类:
			<select class="querySelect" id="cate_id" name="cate_id">
			<option value="">请选择...</option>
			<?php echo $this->html_options(array('options'=>$this->_var['parents'],'selected'=>$_GET['cate_id'])); ?>
			</select>
          <input type="submit" class="formbtn" value="查询" />
      <?php if ($this->_var['filtered']): ?>
      <a class="formbtn formbtn1" href="index.php?app=article">撤销检索</a>
      <?php endif; ?>
    </form>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
    	url: 'index.php?app=article&act=get_xml&'+$("#formSearch").serialize(),
    	colModel : [
    		{display: '操作', name : 'operation', width : 200, sortable : false, align: 'center', className: 'handle'},
    		{display: '排序', name : 'sort_order', width : 50, sortable : true, align: 'center'},
			{display: '标题', name : 'title', width : 250, sortable : true, align: 'center'},
    		{display: '文章分类', name : 'cate_name', width : 150, sortable : true, align: 'left'},    		
			{display: '显示', name : 'if_show', width: 50, sortable : true, align : 'center'},
			{display: '添加时间', name : 'add_time', width: 150, sortable : true, align : 'center'}   		
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	sortname: "sort_order",
    	sortorder: "asc",
    	title: '文章列表'
    });
});
function fg_operate(name, bDiv) {
    if (name == 'del') {
        if($('.trSelected',bDiv).length>0){
            var itemlist = new Array();
			$('.trSelected',bDiv).each(function(){
				itemlist.push($(this).attr('data-id'));
			});
            fg_delete(itemlist,'article');
        } else {
            return false;
        }
    } else if (name == 'add') {
    	window.location.href = 'index.php?app=article&act=add';
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 
