$(function(){
    /* 显示全部分类 */
    $("#show_category").click(function(){
        $("ul[ectype='ul_category'] li").show();
        $(this).hide();
    });

    /* 显示全部品牌 */
    $("#show_brand").click(function(){
        $("ul[ectype='ul_brand'] li").show();
        $(this).hide();
    });

    /* 自定义价格区间 */
    $("#set_price_interval").click(function(){
        $("ul[ectype='ul_price'] li").show();
        $(this).hide();
    });

    /* 显示全部地区 */
    $("#show_region").click(function(){
        $("ul[ectype='ul_region'] li").show();
        $(this).hide();
    });

    /* 筛选事件 */
    $("ul[ectype='ul_category'] a").click(function(){
        replaceParam('cate_id', this.id);
        return false;
    });

    $("li[ectype='li_filter'] img").click(function(){
        dropParam(this.title);
        return false;
    });	
	
		
	$("div[ectype='dl_props'] a").click(function(){
		id = $(this).attr('selected_props')+this.id;
		replaceParam('props',id);
		return false;
	});
    $("[ectype='order_by']").change(function(){
        replaceParam('order', this.value);
        return false;
    });
	
    /* 下拉过滤器 */
    $("li[ectype='dropdown_filter_title'] a").click(function(){
        var jq_li = $(this).parents("li[ectype='dropdown_filter_title']");
        var status = jq_li.find("img").attr("src") == upimg ? 'off' : 'on';
        switch_filter(jq_li.attr("ecvalue"), status)
    });


	
    if($.getCookie("goodsDisplayMode")) {
		$(".display_mod #"+$.getCookie('goodsDisplayMode')).addClass('filter-'+$.getCookie("goodsDisplayMode")+'-cur');
	} else {
		$(".display_mod #squares").addClass('filter-squares-cur');
	}
	$(".display_mod a").click(function(){
		$("div[ectype='current_display_mode']").attr("class",this.id + " clearfix");
		$(".display_mod a").each(function(){
			$(this).removeClass('filter-'+this.id+'-cur');
		});
		$(".display_mod #"+this.id).addClass('filter-'+this.id+'-cur');
		$.setCookie('goodsDisplayMode', this.id);
	});
	$('.sub-images img').click(function(){
		$(this).parent().find('img').each(function(){
			$(this).removeClass('active');
		});
		$(this).addClass('active');
		$('.dl-'+$(this).attr('goods_id')).find('dt img').attr('src',$(this).attr('image_url'));
	});
	
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
    $("#search_by_price").click(function(){
        replaceParam('price', $(this).siblings("input:first").val() + '-' + $(this).siblings("input:last").val());
        return false;
    });
    $("*[ectype='ul_region'] a").click(function(){
        replaceParam('region_id', this.id);
        return false;
    });
	
	$(".selected-attr a").click(function(){
		dropParam(this.id);
		return false;
	});
	
	$('.filter-price .ui-btn-s-primary').click(function(){
		start_price = number_format($(this).parent().find('input[name="start_price"]').val(),0);
		end_price   = number_format($(this).parent().find('input[name="end_price"]').val(),0);
		if(start_price>=end_price){
			end_price = Number(start_price) + 200;
		}
		replaceParam('price', start_price+'-'+end_price);
		return false;
	});

	if($('body').find('.J_ListSort').length >　0) {
		var a = $('.J_ListSort').offset().top;
		$(window).scroll(function () {
			if($(this).scrollTop() > a) 
			{
				$('.J_ListSort').addClass('fixed-show');
			}
			else $('.J_ListSort').removeClass('fixed-show');
		});
	}

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
    var params = null;
	
	/* 后五位是.html，说明开启了伪静态 */
	//if(location.href.substr(-5) == '.html'){ 不兼容IE8
	if(location.href.indexOf('index.php') <= -1) {
		if(location.href.substr(location.href.length-5,5)=='.html'){ // 目前只针对商城商品搜索页
			params = location.href.replace(SITE_URL,'').replace('index.php?','').substr(1).replace('.html','').split('-');
			params[0] = 'app=search';
			params[1] = 'cate_id='+params[1];
			if(params[2]) {
				params[2] = 'page='+params[2];
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
    location.assign(SITE_URL + '/index.php?' + params.join('&'));
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
		<!-- sku psmb -->
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
		<!-- end sku -->
    }
    location.assign(SITE_URL + '/index.php?' + params.join('&'));
}
