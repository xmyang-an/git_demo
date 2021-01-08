$(function(){
	
	$('body').on("click", '.J_GselectorAdd', function(){
		update_DATA('add',$(this).attr('goods_id'),$(this).attr('goods_name'));

		$(this).attr('class','J_GselectorDel btn-gselector-del');
		$(this).text(lang.drop);

		update_select_list();
	});
	
	$('body').on("click", '.J_GselectorDel', function(){
		update_DATA('drop',$(this).attr('goods_id'),'');

		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+$(this).attr('goods_id')+'"]').attr('class', 'J_GselectorAdd btn-gselector-add');
		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+$(this).attr('goods_id')+'"]').text(lang.add);
		
		$(this).attr('class', 'J_GselectorAdd btn-gselector-add');
		$(this).text(lang.add);
				
		update_select_list();
		

	});
	$('body').on("click", '.J_MealDel', function(){
		if($(this).parent().parent().parent().find('li').length==1) {
			$(this).parent().parent().parent().html('<div class="pt5 pb5 align2 gray-color">'+getLangMessage('add_records')+'</div>');
		}
		$(this).parent().parent().remove();
		updatePriceTotal();
	});
	
	$('.J_Del').click(function(){
		if($.trim($(this).attr('confirm')) != ''){
			if(!confirm($.trim($(this).attr('confirm')))){
				return false;
			}
		}
			
		url = $(this).attr('uri');
		$.getJSON(url, function(result){
			if(result.done) {
				alert(lang.drop_ok);
				window.location.reload();
			}
			else {
				alert(lang.drop_error);
			}
		});
	});
	
});

function init()
{
	DATA_LIST_TEMP = [];
	$.each($('*[ectype="goods_list"] li'), function(){
		update_DATA('add',$(this).find('.cell-input input').val(),$(this).find('.cell-title a').text());
	});
	
	showPage(1);
}

/* 更新弹窗中商品选择列表，更新分页，将已选择的商品设为选中 */
function update_gselector_list(data)
{
	html = '';
	if(data.goods_list.length != 0) {
		$.each(data.goods_list,function(i,item){
			html += '<ul class="clearfix"><li class="col-1 center clearfix"><div class="pic float-left"><a href="index.php?app=gift&id='+item.goods_id+'" target="_blank"><img width="40" height="40" src="'+item.default_image+'" /></div><div class="desc float-left"><a href="index.php?app=gift&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a></div></li><li class="col-2"><span class="price">'+item.price+'</span></li><li class="col-3">'+item.stock+'</li><li class="col-4 center"><a href="javascript:;" class="J_GselectorAdd btn-gselector-add" goods_name="'+item.goods_name+'" goods_id="'+item.goods_id+'">'+lang.add+'</a></li></ul>';
		});
	}
	else {
		html = '<div class="notice-word mt10"><p>'+lang.no_records+'</p></div>';
	}
	$('*[ectype="gselector-goods-list"]').html(html);
	
	/* 更新分页 */
	$('*[ectype="gselector-page-info"]').html(ajax_page(data.page_info));
	
	/* 设置选中，将选中的商品修改为删除按钮 */
	$.each(DATA_LIST_TEMP, function(i,item) {
		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+item.goods_id+'"]').attr('class','J_GselectorDel btn-gselector-del');
		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+item.goods_id+'"]').text(lang.drop);
	});
}
/* 更新弹窗中已选择的商品的列表 */
function update_select_list(){
	if(DATA_LIST_TEMP.length == 0) {
		$('.J_ListAdded').hide();
		msg(getLangMessage('add_records'));
	}else {
		$('.J_ListAdded').show();
		$('.J_Warning').hide();
		$('*[ectype="sel-list"]').html('');
		$.each(DATA_LIST_TEMP, function(i,item){
			html = '<li><a href="index.php?app=gift&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a><a href="javascript:;" class="J_GselectorDel btn-gselector-del" goods_id="'+item.goods_id+'"></a></li>';
			$('*[ectype="sel-list"]').append(html);
		});
	}
}

function showPage(page)
{
	goods_name = $('#gs_goods_name').val();
	$.getJSON('index.php?app=seller_fullgift&act=gselector', {'goods_name':goods_name,'page':page},function(data){
		if(data.done){
			update_gselector_list(data.retval);
			update_select_list();
		}
	});
}

function gs_callback(id)
{
	var goods_ids = '';
	$.each(DATA_LIST_TEMP, function(i,item){
		goods_ids += ',' + item.goods_id;
	});
	ids = goods_ids.substr(1);
	
	if(ids.length == 0){
		$('.J_ListAdded').hide();
        msg(getLangMessage('add_records'));
	}else{
		gs_query_info(ids);
		DialogManager.close(id);
	}
}
function gs_query_info(goods_ids)
{
	$.getJSON('index.php?app=seller_fullgift&act=query_goods_info',{'goods_id':goods_ids},function(data){
		if(data.done){
			var goods_list = data.retval.goods_list;
			$('*[ectype="goods_list"]').html('');
			$.each(goods_list,function(i,item){
				$('*[ectype="goods_list"]').append('<li class="clearfix"><p class="cell-input"><input name="fullgift[selected_ids][]" type="hidden" value="'+item.goods_id+'" /></p><p class="cell-thumb float-left"><a href="index.php?app=gift&id='+item.goods_id+'" target="_blank"><img src="'+item.default_image+'" width="50" height="50" /></a></p><p class="cell-title float-left"><a href="index.php?app=gift&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a></p><p class="J_getPrice cell-price float-left" price="'+item.price+'">'+item.price+'</p><p class="cell-action float-left"><a class="J_MealDel" href="javascript:;">'+lang.drop+'</a></p></li>');
			});
		}
	});
}

function update_DATA(flow, goods_id, goods_name)
{
	if(flow=='add') {
		DATA_LIST_TEMP.push({"goods_id":goods_id,"goods_name":goods_name});
	}
	else if(flow=='drop') {
		DATA_LIST_TEMP_NEW = [];
		$.each(DATA_LIST_TEMP, function(i,item){
			if(item.goods_id != goods_id) {
				DATA_LIST_TEMP_NEW.push(item);
			}
		});
		DATA_LIST_TEMP = DATA_LIST_TEMP_NEW;
	}
}

function msg(msg){
    $('.J_Warning').show();
    $('.J_Warning>p').text(msg);
    //window.setTimeout(function(){
        //$('.J_Warning').hide();
    //},6000)
}

function gs_submit(id,name,callback){
    if(id.length == 0){
        msg('id_mission');
    }
	if(callback.length>0){
		eval(callback+'("'+id+'")');
	}
}

function getLangMessage(code)
{
	var Lang = new Array();
	
	Lang['add_records']   = '您还没有添加赠品';
	Lang['records_error'] = '您至少添加一个赠品，且数量不能操作10个';
	
	if(typeof(Lang[code]) == "undefined") {
		return code;
	}
	else return Lang[code];
}

