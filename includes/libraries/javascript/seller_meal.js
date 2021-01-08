$(function(){
	
	updatePriceTotal();
	
	$('body').on("click", '.J_GselectorAdd', function(){
		update_MEAL('add',$(this).attr('goods_id'),$(this).attr('goods_name'));

		$(this).attr('class','J_GselectorDel btn-gselector-del');
		$(this).text(lang.drop);

		update_select_list();
	});
	
	$('body').on("click", '.J_GselectorDel', function(){
		update_MEAL('drop',$(this).attr('goods_id'),'');

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
	MEAL = [];
	$.each($('*[ectype="meal_goods_list"] li'), function(){
		update_MEAL('add',$(this).find('.cell-input input').val(),$(this).find('.cell-title a').text());
	});
	
	showPage(1);
}

/* 更新弹窗中商品选择列表，更新分页，将已选择的商品设为选中 */
function update_gselector_list(data)
{
	html = '';
	if(data.goods_list.length != 0) {
		$.each(data.goods_list,function(i,item){
			html += '<ul class="clearfix"><li class="col-1 center clearfix"><div class="pic float-left"><a href="index.php?app=goods&id='+item.goods_id+'" target="_blank"><img width="40" height="40" src="'+item.default_image+'" /></div><div class="desc float-left"><a href="index.php?app=goods&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a></div></li><li class="col-2"><span class="price">'+item.price+'</span></li><li class="col-3">'+item.stock+'</li><li class="col-4 center"><a href="javascript:;" class="J_GselectorAdd btn-gselector-add" goods_name="'+item.goods_name+'" goods_id="'+item.goods_id+'">'+lang.add+'</a></li></ul>';
		});
	}
	else {
		html = '<div class="notice-word mt10"><p>'+lang.no_records+'</p></div>';
	}
	$('*[ectype="gselector-goods-list"]').html(html);
	
	/* 更新分页 */
	$('*[ectype="gselector-page-info"]').html(ajax_page(data.page_info));
	
	/* 设置选中，将选中的商品修改为删除按钮 */
	$.each(MEAL, function(i,item) {
		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+item.goods_id+'"]').attr('class','J_GselectorDel btn-gselector-del');
		$('*[ectype="gselector-goods-list"]').find('a[goods_id="'+item.goods_id+'"]').text(lang.drop);
	});
}
/* 更新弹窗中已选择的商品的列表 */
function update_select_list(){
	if(MEAL.length == 0) {
		$('.J_ListAdded').hide();
		msg(getLangMessage('add_records'));
	}else {
		$('.J_ListAdded').show();
		$('.J_Warning').hide();
		$('*[ectype="sel-list"]').html('');
		$.each(MEAL, function(i,item){
			html = '<li><a href="index.php?app=goods&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a><a href="javascript:;" class="J_GselectorDel btn-gselector-del" goods_id="'+item.goods_id+'"></a></li>';
			$('*[ectype="sel-list"]').append(html);
		});
	}
}

/* 更新选中的搭配宝贝的总价 */
function updatePriceTotal()
{
	price_min = price_max = 0;
	$('*[ectype="meal_goods_list"] .J_getPrice').each(function() {
		price = $(this).attr('price').split('-');
		if(price[0] != undefined) {
			price_min += Number(price[0]);
		}
		if(price[1] != undefined) {
			price_max += Number(price[1]);
		} else price_max += Number(price[0]);
	});
	
	if(price_max > price_min) {
		$('.J_priceTotal').val(price_min+'~'+price_max);
	} else $('.J_priceTotal').val(price_min);
}

function showPage(page)
{
	goods_name = $('#gs_goods_name').val();
	sgcate_id = $('#gs_sgcate_id').val();
	$.getJSON('index.php?app=seller_meal&act=gselector', {'goods_name':goods_name,'sgcate_id':sgcate_id,'page':page},function(data){
		if(data.done){
			update_gselector_list(data.retval);
			update_select_list();
		}
	});
}

function gs_callback(id)
{
	var goods_ids = '';
	$.each(MEAL, function(i,item){
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
	$.getJSON('index.php?app=seller_meal&act=query_goods_info',{'goods_id':goods_ids},function(data){
		if(data.done){
			var goods_list = data.retval.goods_list;
			$('*[ectype="meal_goods_list"]').html('');
			$.each(goods_list,function(i,item){
				$('*[ectype="meal_goods_list"]').append('<li class="clearfix"><p class="cell-input"><input name="selected_ids[]" type="hidden" value="'+item.goods_id+'" /></p><p class="cell-thumb float-left"><a href="index.php?app=goods&id='+item.goods_id+'" target="_blank"><img src="'+item.default_image+'" width="50" height="50" /></a></p><p class="cell-title float-left"><a href="index.php?app=goods&id='+item.goods_id+'" target="_blank">'+item.goods_name+'</a></p><p class="J_getPrice cell-price float-left" price="'+item.price+'">'+item.price+'</p><p class="cell-action float-left"><a class="J_MealDel" href="javascript:;">'+lang.drop+'</a></p></li>');
			});
			updatePriceTotal();
		}
	});
}

function update_MEAL(flow, goods_id, goods_name)
{
	if(flow=='add') {
		MEAL.push({"goods_id":goods_id,"goods_name":goods_name});
	}
	else if(flow=='drop') {
		MEAL_NEW = [];
		$.each(MEAL, function(i,item){
			if(item.goods_id != goods_id) {
				MEAL_NEW.push(item);
			}
		});
		MEAL = MEAL_NEW;
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

function add_uploadedfile(file_data)
{
	$('#desc_images').append('<li style="z-index:4" file_name="'+ file_data.file_name +'" file_path="'+ file_data.file_path +'" ectype="handle_pic" file_id="'+ file_data.file_id +'"><input type="hidden" name="desc_file_id[]" value="'+ file_data.file_id +'"><div class="pic" style="z-index: 2;"><img src="' + SITE_URL + '/'+ file_data.file_path +'" width="80" height="80" alt="'+ file_data.file_name +'" /></div><div ectype="handler" class="bg" style="z-index: 3;display:none"><p class="operation"><a href="javascript:void(0);" class="cut_in" ectype="insert_editor" ecm_title="'+lang.insert_editor+'"></a><span class="delete" ectype="drop_image" ecm_title="'+lang.drop+'"></span></p></div></li>');
	trigger_uploader();
	if(EDITOR_SWFU.getStats().progressNum == 0){
		window.setTimeout(function(){
			$('#editor_uploader').css('opacity', 0);
			$('*[ectype="handle_pic"]').css('z-index', 999);
		},5000);
	}
}
function drop_image(file_id)
{
    if (confirm(lang.uploadedfile_drop_confirm)) {
		var url = SITE_URL + '/index.php?app=seller_meal&act=drop_uploadedfile';
		$.getJSON(url, {'file_id':file_id}, function(data){
			if (data.done){
				$('*[file_id="' + file_id + '"]').remove();
			} else {
				alert(data.msg);
			}
		});
	}
}

function getLangMessage(code)
{
	var Lang = new Array();
	
	Lang['add_records']   = '请添加搭配宝贝';
	Lang['records_error'] = '您至少添加2个搭配商品，且数量不能操作10个';
	
	
	if(typeof(Lang[code]) == "undefined") {
		return code;
	}
	else return Lang[code];
}