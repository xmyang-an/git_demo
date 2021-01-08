<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <ul class="subnav">
    <li><span>店铺情况一览</span></li>
  </ul>
</div>

<div class="explanation" id="explanation">
  <div class="title" id="checkZoom">
  	<i class="fa fa-lightbulb-o"></i>
    <h4 title="操作提示">操作提示</h4>
    <span id="explanationZoom" title="收起提示"></span>
  </div>
  <ul>
    <li><i class="fa fa-angle-double-right"></i> 1. 统计图展示了符合搜索条件的店铺数量在时间段内的走势情况及与前一个时间段的趋势对比。</li>
    <li><i class="fa fa-angle-double-right"></i> 2. 统计表显示了符合搜索条件的全部记录并可以点击“导出数据”将订单记录导出为Excel文件。</li>
  </ul>
</div>
<div class="clearfix">  
  <div class="form-all stat-general-single">
    <dl class="row">
      <dd class="opt">
        <ul class="clearfix">
          <li class="clearfix">
            <h4>店铺数量：</h4>
            <h2 class="timer"></h2>
            <h6>人</h6>
          </li>
        </ul>
      </dd>
    </dl>
  </div>
  <div id="stat_tabs" class="ui-tabs" style="min-height:250px">
    <div id="store_amount_div" style="text-align:center;"></div>
  </div>
  <div id="flexigrid"></div>
  <div class="search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
  <div class="search-bar">
    <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
    <div class="title">
      <h3>高级搜索</h3>
    </div>
    <form method="get" action="index.php" name="formSearch" id="formSearch">
      <div id="searchCon" class="content">
        <div class="layout-box">
          <dl>
            <dt>按时间周期筛选</dt>
            <dd>
              <label>
                <select name="search_type" id="search_type" class="s-select">
                  <option value="day" <?php if ($this->_var['search_arr']['search_type'] == 'day'): ?> selected="selected"<?php endif; ?>>按照天统计</option>
                  <option value="week" <?php if ($this->_var['search_arr']['search_type'] == 'week'): ?> selected="selected"<?php endif; ?>>按照周统计</option>
                  <option value="month" <?php if ($this->_var['search_arr']['search_type'] == 'month'): ?> selected="selected"<?php endif; ?>>按照月统计</option>
                </select>
              </label>
            </dd>
            <dd id="searchtype_day" style="display:none;">
              <label>
                <input class="s-input-txt" type="text" value="<?php echo local_date("Y-m-d",$this->_var['search_arr']['day']['search_time']); ?>" id="search_time" name="search_time">
              </label>
            </dd>
            <dd id="searchtype_week" style="display:none;">
              <label>
                <select name="searchweek_year" class="s-select">
                  <?php $_from = $this->_var['year_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['val']):
?>
                  <option value="<?php echo $this->_var['key']; ?>" <?php if ($this->_var['search_arr']['week']['current_year'] == $this->_var['key']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['val']; ?></option>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </select>
              </label>
              <label>
                <select name="searchweek_month" class="s-select">
                  <?php $_from = $this->_var['month_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['val']):
?>
                  <option value="<?php echo $this->_var['key']; ?>" <?php if ($this->_var['search_arr']['week']['current_month'] == $this->_var['key']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['val']; ?></option>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </select>
              </label>
              <label>
                <select name="searchweek_week" class="s-select">
                  <?php $_from = $this->_var['week_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['val']):
?>
                  <option value="<?php echo $this->_var['val']['key']; ?>" <?php if ($this->_var['search_arr']['week']['current_week'] == $this->_var['val']['key']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['val']['val']; ?></option>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </select>
              </label>
            </dd>
            <dd id="searchtype_month" style="display:none;">
              <label>
                <select name="searchmonth_year" class="s-select">
                  <?php $_from = $this->_var['year_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['val']):
?>
                  <option value="<?php echo $this->_var['key']; ?>" <?php if ($this->_var['search_arr']['month']['current_year'] == $this->_var['key']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['val']; ?></option>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </select>
              </label>
              <label>
                <select name="searchmonth_month" class="s-select">
                  <?php $_from = $this->_var['month_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['val']):
?>
                  <option value="<?php echo $this->_var['key']; ?>" <?php if ($this->_var['search_arr']['month']['current_month'] == $this->_var['key']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['val']; ?></option>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </select>
              </label>
            </dd>
          </dl>
        </div>
      </div>
      <div class="bottom"> <a href="javascript:void(0);" id="submit" class="search-btn">提交查询</a> </div>
    </form>
  </div>
</div>
<script>
//展示搜索时间框
function show_searchtime(){
	s_type = $("#search_type").val();
	$("[id^='searchtype_']").hide();
	$("#searchtype_"+s_type).show();
}

function update_flex(){
	$('.stat-general-single').load('index.php?app=store&act=get_plat_sale&'+$("#formSearch").serialize());
	getStatdata();
    $("#flexigrid").flexigrid({
        url: 'index.php?app=store&act=get_xml&'+$("#formSearch").serialize(),
        colModel : [
    		{display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
			{display: '用户名', name : 'user_name', width : 50, sortable : true, align: 'center'},
			{display: '店主', name : 'owner_name', width : 100, sortable : true, align: 'center'},
    		{display: '店铺名称', name : 'store_name', width : 100, sortable : true, align: 'center'},
			{display: '所在地区', name : 'region_name', width : 200, sortable : true, align: 'center'},
    		{display: '所属等级', name : 'sgrade', width : 100, sortable : true, align: 'center'},
			{display: '开店时间', name : 'add_time', width: 100, sortable : true, align : 'center'},    		

			{display: '状态', name : 'state', width: 50, sortable : true, align : 'center'},  
			{display: '排序', name : 'sort_order', width: 50, sortable : true, align : 'center'},
			{display: '推荐', name : 'recommended', width: 50, sortable : true, align : 'center'},
			{display: '开启分销', name : 'enable_distribution', width: 80, sortable : true, align : 'center'}	
    		],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增数据', name : 'add', bclass : 'add', title : '新增数据', onpress : fg_operate },
			{display: '<i class="fa fa-edit"></i>批量编辑', name : 'edit', bclass : 'csv', title : '批量编辑', onpress : fg_operate },
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate },
			{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operate }
        ],
    	sortname: "sort_order",
    	sortorder: "asc",
    	title: '店铺列表'
    });
}
$(function () {

	//统计数据类型
	$('#search_time').datepicker({dateFormat: 'yy-mm-dd'});

	show_searchtime();

	//更新周数组
	$("*[name='searchweek_month']").change(function(){
		var year = $("*[name='searchweek_year']").val();
		var month = $("*[name='searchweek_month']").val();
		$("*[name='searchweek_week']").html('');
		$.getJSON('index.php?app=stat&act=getweekofmonth',{y:year,m:month},function(data){
	        if(data.done){
	        	$.each(data.retval,function(index,value){
					$("*[name='searchweek_week']").append('<option value="'+value.key+'">'+value.val+'</option>');
				});
	        }
	    });
	});

	$('#submit').click(function(){
    	$(".tab-base li.active").trigger("click");
	    $('.flexigrid').after('<div id="flexigrid"></div>').remove();
	    update_flex();
    });

  	//加载统计数据
    update_flex();
});
//加载统计地图
function getStatdata(){
	$('#store_amount_div').load('index.php?app=store&act=increase_trend&'+$("#formSearch").serialize());
}
function fg_operate(name, bDiv) {
	if(name == 'add'){
		window.location.href = 'index.php?app=store&act=test';
		return false;
	}
	var itemlist = new Array();
	$('.trSelected',bDiv).each(function(){
		itemlist.push($(this).attr('data-id'));
	});
	if (name == 'edit') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       window.location.href = 'index.php?app=store&act=batch_edit&id='+itemlist;
	}
	if (name == 'del') {
	   if($('.trSelected',bDiv).length==0){
		   parent.layer.alert('没有选择操作项',{icon: 0});
			return false;
	   }
       fg_delete(itemlist,'store');
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