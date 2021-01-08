<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>自动回复</p>
    <ul class="subnav">
        <li><span>回复列表</span></li>
        <li><a class="btn1" href="index.php?app=wxreply&act=add">新增</a></li>
    </ul>
</div>
<div class="explanation" id="explanation">
  <div class="title" id="checkZoom">
  	<i class="fa fa-lightbulb-o"></i>
    <h4 title="操作提示">操作提示</h4>
  </div>
  <ul>
    <li><i class="fa fa-angle-double-right"></i> 1. 自动回复分三种类型，关键词自动回复、关注自动回复、消息自动回复;</li>
    <li><i class="fa fa-angle-double-right"></i> 2. 关注自动回复、消息自动回复，两种类型回复只能增加一条，可以编辑，不能重复添加;</li>
    <li><i class="fa fa-angle-double-right"></i> 3. 关键词自动回复，可以增加多条，多个关键词用分号隔开。</li>
  </ul>
</div>
<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
    	url: 'index.php?app=wxreply&act=get_xml',
    	colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: 'ID', name : 'reply_id', width : 50, sortable : true, align: 'center'},
			{display: '回复类型', name : 'action', width : 100, sortable : true, align: 'center'},
    		{display: '规则名称', name : 'rule_name', width : 200, sortable : false, align: 'center'},
			{display: '关键字', name : 'keywords', width : 200, sortable : false, align: 'center'},
    		{display: '消息类型', name : 'type', width : 100, sortable : true, align: 'center'},
			{display: '回复内容', name : 'content', width: 300, sortable : false, align : 'center'}	
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
    	title: '回复列表'
    });
});
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=wxreply&act=add';
		return false;
	}
	if($('.trSelected',bDiv).length>0){
        var itemlist = new Array();
		$('.trSelected',bDiv).each(function(){
			itemlist.push($(this).attr('data-id'));
		});
		if (name == 'del') {	
            fg_delete(itemlist,'wxreply');
		}
    } else {
		parent.layer.alert('没有选择操作项',{icon: 0});
       return false;
    }
}
</script>
<?php echo $this->fetch('footer.html'); ?> 
