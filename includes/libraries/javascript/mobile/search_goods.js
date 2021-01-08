$(function(){
   
	$("*[ectype='ul_cate'] a").click(function(){
        replaceParam('cate_id', this.id);
        return false;
    });
    $("*[ectype='ul_brand'] a").click(function(){
        replaceParam('brand', this.id);
        return false;
    });
	
    $("*[ectype='ul_price'] a").click(function(){
        replaceParam('price', this.id);
        return false;
    });
    $("*[ectype='ul_region'] a").click(function(){
        replaceParam('region_id', this.id);
        return false;
    });
	
	$("*[ectype='ul_prop'] a").click(function(){
        id = $(this).attr('selected_props')+this.id;
		replaceParam('props',id);
		return false;
    });
	
	$(".selected-attr a.each-filter").click(function(){
		dropParam(this.id);
		return false;
	});
	
	$("[ectype='current_display_mode']").each(function(index, element) {
        if($.getCookie($(this).attr('data-cookie'))) {
			if($.getCookie($(this).attr('data-cookie')) == 'list'){
				$("#"+$(this).attr('data-cookie')).removeClass('squares').addClass('list');
			}else{
				$("#"+$(this).attr('data-cookie')).removeClass('list').addClass('squares');
			}		
		}
    });
	
	$(".J_ChangeDisplayMode").click(function(){
		var currMode = $(this).hasClass('list') == true ? 'list'    : 'squares';
		var showMode = $(this).hasClass('list') == true ? 'squares' : 'list';
		$(this).removeClass(currMode).addClass(showMode);

		$("[data-cookie='"+$(this).attr('id')+"']").removeClass(currMode).addClass(showMode);
		$.setCookie($(this).attr('id'), showMode);
	});
	
	$(".J_ActiveSort").click(function(){	
		if($(".J_SortEject").is(":hidden")) {
			$(".J_SortEject").show();
			$(this).find('i').addClass('up').html('&#xe620;');
		} else {
			$(".J_SortEject").hide();
			$(this).find('i').removeClass('up').html('&#xe61f;');
		}
	});
	
	$("[ectype='sort']").click(function(){
		if(this.id==''){
			dropParam('order');// default order
			return false;
		}
		else
		{
			var id = this.id;
			var sortStr = id.split('-');
			var dd = sortStr[1] ? sortStr[1] : 'desc';
			
			replaceParam('order', sortStr[0]+' '+dd);
			return false;
		}
	});
	
	$('.J_SearchFilterPrice').click(function(){
		start_price = number_format($(this).parent().find('input[name="start_price"]').val(),0);
		end_price   = number_format($(this).parent().find('input[name="end_price"]').val(),0);
		if(start_price>=end_price){
			end_price = Number(start_price) + 200;
		}
		replaceParam('price', start_price+'-'+end_price);
		return false;
	});
	
});

/** 打开/关闭过滤器
 *  参数 filter 过滤器   brand | price | region
 *  参数 status 目标状态 on | off
 */
function switch_filter(filter, status)
{
    $("li[ectype='dropdown_filter_title']").attr('class', 'normal');
    $("li[ectype='dropdown_filter_title'] img").attr('src', downimg);
    $("div[ectype='dropdown_filter_content']").hide();

    if (status == 'on')
    {
        $("li[ectype='dropdown_filter_title'][ecvalue='" + filter + "']").attr('class', 'active');
        $("li[ectype='dropdown_filter_title'][ecvalue='" + filter + "'] img").attr('src', upimg);
        $("div[ectype='dropdown_filter_content'][ecvalue='" + filter + "']").show();
    }
}

/* 替换参数 */
function replaceParam(key, value)
{
    var params = [];
	
	/* 后五位是.html，说明开启了伪静态 */
	//if(location.href.substr(-5) == '.html'){ 不兼容IE8
	if(location.href.indexOf('index.php') <= -1) {
		if(location.href.substr(location.href.length-5,5)=='.html'){
		
			var app = $('*[ectype="sort"]').parents('.items').attr('app');
			if(app) 
			{
				params[0] = 'app='+app;
				params[1] = '';
			}
			else
			{
				// 目前只针对商城商品搜索页
				params = location.href.replace(SITE_URL,'').replace('index.php?','').substr(1).replace('.html','').split('-');
				params[0] = 'app=search';
				params[1] = 'cate_id='+params[1];
				if(params[2]) {
					params[2] = 'page='+params[2];
				}
			}
		}
		else
		{
			// 没有.html后缀的伪静态， 目前只针对店铺的商品搜索页
			params = location.href.replace(SITE_URL,'').replace('index.php?','').substr(1).split('/');
			
			// 店铺全部商品搜索页面
			if(params[3] == 'goods') {
				
				//index.php?app=store&act=search&id=2
				params[0] = 'app=store';
				params[1] = 'act=search';
				params[2] = 'id='+params[2];
				params.splice(3); // 不需要该键值
			}
			// 店铺按分类搜索商品页
			else
			{
				//app=store&id=38&act=search&cate_id=1494
				params[0] = 'app=store';
				params[1] = 'act=search';
				params[2] = 'id='+params[2];
				params[3] = 'cate_id='+params[4];
				params.splice(4); // 不需要该键值
			}
		}
	}
	else
	{
		params = location.search.substr(1).split('&');
	}
    var found  = false;
    for (var i = 0; i < params.length; i++)
    {
        param = params[i];
        arr   = param.split('=');
        pKey  = arr[0];

        if (pKey == 'page')
        {
            params[i] = 'page=1';
        }
        if (pKey == key)
        {
            params[i] = key + '=' + value;
            found = true;
        }
    }
    if (!found)
    {
        value = transform_char(value);
        params.push(key + '=' + value);
    }
	
	// 缓动关闭筛选层
	$('.J_GoodsFilterPop').animate({'right':'-110%','left' : '110%'});
	setTimeout(function() {
    	location.assign(REAL_SITE_URL + '/index.php?' + params.join('&'));
	}, 500);
}

/* 删除参数 */
function dropParam(key)
{
    var params = location.search.substr(1).split('&');
    for (var i = 0; i < params.length; i++)
    {
        param = params[i];
        arr   = param.split('=');
        pKey  = arr[0];
        if (pKey == 'page')
        {
            params[i] = 'page=1';
        }
		if (pKey == 'props' || pKey == 'brand')
		{
			arr1 = arr[1];
			arr1 = arr1.replace(key,'');
			arr1 = arr1.replace(";;",';');
			if(arr1.substr(0,1)==";") {
				arr1 = arr1.substr(1,arr1.length-1);
			}
			if(arr1.substr(arr1.length-1,1) == ";") {
				arr1 = arr1.substr(0,arr1.length-1);
			}
			params[i]=pKey + "=" + arr1;
		}
        if (pKey == key || params[i]=='props=' || params[i]=='brand=')
        {
            params.splice(i, 1);
        }
    }
	
	// 缓动关闭筛选层
	$('.J_GoodsFilterPop').animate({'right':'-110%','left' : '110%'});
	setTimeout(function() {
    	location.assign(REAL_SITE_URL + '/index.php?' + params.join('&'));
	}, 500);
    
}
