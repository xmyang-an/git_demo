$(function(){
	// 表单验证
	$('.J_SubmitDelivery').click(function(){
		error = 0;
		if($('#delivery_template').find('input[name="name"]').val()==''){
			error++;
			layer.open({content: '请输入模板名称', time: 3});
			return false;
		}
		
		$('#delivery_template').find('input[group="dests"]').each(function(index, element) {
			if($(this).attr('value')==''){
				error++;
				layer.open({content: '您还有“未添加地区”项，请设置地区', time: 3});
				return false;
			}
		});

		$('#delivery_template').find('.default_fee input[type="text"], .other_fee input[type="text"]').each(function(index, element) {
            if($(this).val()== '' || $(this).val() < 0 || isNaN($(this).val())){
				error++;
				$(this).css('border', '1px blue solid');
				$(this).val(1);
				layer.open({content: '运费或数量必须是数字且不能为负数或空值', time: 3});
				return false;
			}
        });
		if(error==0){
			$('#delivery_template').submit();
		} else return false;
	});
	
	$('#delivery_template').find('.default_fee input[type="text"], .other_fee input[type="text"]').keyup(function(){
		if($(this).val() >= 0 && !isNaN($(this).val())){
			$(this).css('border', '1px #7F9DB9 solid');
		} else {
			$(this).css('border', '1px blue solid');
			$(this).val(1);
			layer.open({content: '运费或数量必须是数字且不能为负数或空值', time: 3});
			return false;
		}
	});
	
	$('body').on("click", '.J_Province', function(){
		var input = $(this).find('input[fileds="province"]');
		var value = input.val();

		if(input.prop('checked') == true){
			$('[province_id="'+value+'"]').find('.J-city').addClass('active');
			$('[province_id="'+value+'"]').find('input').prop('checked','checked');
			
			checkCount = $('[province_id="'+value+'"]').find('.J-city.active').length;
			if(checkCount > 0) {
				$(this).find('.J-count').html('('+checkCount+')');
			}
		} else {
			$('[province_id="'+value+'"]').find('.J-city').removeClass('active');
			$('[province_id="'+value+'"]').find('input').prop('checked','');
			$(this).find('.J-count').html('');
		}
	});
	
	
	$('body').on("click", '.J_ExpandCity', function(){
		$(this).parents('.regionGroup').find('.cityList').toggleClass('hidden');
		$(this).toggleClass('close');
	});
	
	$('body').on("click", '.J-city', function(){
		checkCount = 0;
		checkAll = true;
		$(this).parents('.cityList').find('input[fileds="city"]').each(function(index, element) {
			if($(this).prop('checked')==true){
				checkCount++;
			}
			else{
				checkAll = false;
			}
		});
		
		if(checkAll == true){
			$(this).parents('.regionGroup').find('.J_Province').addClass('active');
			$(this).parents('.regionGroup').find('.J_Province input').prop('checked','checked');
		}else{
			$(this).parents('.regionGroup').find('.J_Province').removeClass('active');
			$(this).parents('.regionGroup').find('.J_Province input').prop('checked','');
		}

		if(checkCount==0) {
			$(this).parents('.regionGroup').find('.J-count').html('');
		} else {
			$(this).parents('.regionGroup').find('.J-count').html('('+checkCount+')');
		}
	});
	
	$('body').on("click", '.del_area', function(){
		type = $(this).attr('type');
		
		if($('#'+type+' .mc').find('li').length > 1){
			$(this).parents('li').remove();
		}
	});
	
	$('body').on("click", '.add_area', function(){
		type = $(this).attr('type');
		area_id = new Date().getTime();
		
		var html = '<li class="meanDelivery default_fee">' + 
					'<dl>'+
				 	'<dd class="cell-area box-align-center" style="height:auto;">' +
					'	<span class="selected_area J_SelectedAreaName flex1"><font color="#999">未添加地区</font></span>' +
					'	<input type="hidden" group="dests" name="'+type+'_dests[]" value="" />'+
					'	<a href="javascript:;" gs_id="gselector-delivery" gs_name="gselector-delivery" gs_callback="gs_callback" gs_title="设置指定地区" gs_width="480" gs_type="delivery" gs_store_id="" ectype="gselector" gs_class="simple-blue scroll" gs_position="bottom" class="btn-add-product">编辑</a><a href="javascript:;" class="del_area" type="'+type+'">删除</a>' +
					'</dd>'+
					'</dl>'+
					'<dl>'+
					'<dd class="webkit-box">'+
					'<span>首</span><ins class="relative pl20 flex1"><input type="text" class="input" value="1" name="'+type+'_start[]"  oninput="javascript:clearInput(this)"/><i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i></ins><em  class="text-width1">件内，</em><ins class="relative pl20 flex1"><input type="text" class="input" value="10" name="'+type+'_postage[]"  oninput="javascript:clearInput(this)"/><i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i></ins><em class="pr10 mr5">元</em>'+
					'</dd>'+
					'</dl>'+
					'<dl>'+
					'<dd class="webkit-box">'+
					'<span>每增加</span><ins class="relative pl20 flex1"><input type="text" class="input" value="1" name="'+type+'_plus[]"  oninput="javascript:clearInput(this)"/><i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i></ins><em class="text-width1">件，增加运费</em><ins class="relative pl20 flex1"><input type="text" class="input" value="0" name="'+type+'_postageplus[]"  oninput="javascript:clearInput(this)"/><i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i></ins><em class="pr10 mr5">元</em>'+
					'</dd>'+
					'</dl>'+
			'</li>';

			$('#'+type+' .mc').append(html);
	});
		
});

function bind(id)
{
	// 获取指定地区运费的地区ID
	$('*[gs_id="'+id+'"]').parent().find('input[group="dests"]').each(function(index, element) {
		dests = $(this).val().split('|');
    });

	// 设置选择的地区为选中状态
	$.each(dests, function(i,item){
		$('[province_id="'+item+'"]').parents('.regionGroup').find('.J_Province').addClass('active');
		$('[province_id="'+item+'"]').parents('.regionGroup').find('.J_Province input').prop('checked','checked');
	});
	
	// 如果省选中的话，设置该省下面的所有城市为选中状态
	$('.J_Province input[fileds="province"]').each(function(index, element) {
        if($(this).prop('checked')==true) {
			$(this).parents('.regionGroup').find('.cityList').find('.J-city').addClass('active');
			$(this).parents('.regionGroup').find('.cityList').find('.J-city input').prop('checked','checked');
		}
    });
	
	// 计算城市选中的数量，赋值到省后面
	$('.regionGroup').find('.cityList').each(function(index, element) {
        checkCount = $(this).find('input[type="checkbox"]:checked').length;
		if(checkCount > 0) {
			$(this).parents('.regionGroup').find('.J-count').html('('+checkCount+')');
		}
    });
}

function gs_callback(id)
{
	dests = AreaName = '';
	$('.J_Province').find('input[fileds="province"]').each(function(index, element) {
		if($(this).prop('checked')==true){
			dests += '|'+$(this).val();
			AreaName += ','+$(this).attr('title');
		}
		else
		{
			// 城市
			$(this).parents('.regionGroup').find('.cityList').find('input[fileds="city"]').each(function(index, element) {
				if($(this).prop('checked')==true){
					dests += '|'+$(this).val();
					AreaName += ','+$(this).attr('title');
				}
			});
		}
	});
	if(dests.length==0){
		layer.open({content: '您没有选择任何地区', time: 3});
		return false;
	}
	$('*[gs_id="'+id+'"]').parent().find('input[group="dests"]').val(dests.substr(1));
	
	if(AreaName.length==0) {
		AreaName = '未添加地区';
	} else AreaName = AreaName.substr(1);
	
	$('*[gs_id="'+id+'"]').parent().find('.J_SelectedAreaName').html(AreaName);
	
	DialogManager.close(id);

}
