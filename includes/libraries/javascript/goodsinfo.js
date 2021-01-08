/* spec对象 */
function spec(id, spec1, spec2, spec_image, price, stock, goods_id)
{
    this.id    = id;
    this.spec1 = spec1;
    this.spec2 = spec2;
    this.price = price;
    this.stock = stock;
	this.goods_id = goods_id;
	this.spec_image = spec_image;
}

/* goodsspec对象 */
function goodsspec(specs, specQty, defSpec)
{
    this.specs = specs;
    this.specQty = specQty;
    this.defSpec = defSpec;
    this.spec1 = null;
    this.spec2 = null;
    if (this.specQty >= 1)
    {
        for(var i = 0; i < this.specs.length; i++)
        {
            if (this.specs[i].id == this.defSpec)
            {
                this.spec1 = this.specs[i].spec1;
                if (this.specQty >= 2)
                {
                    this.spec2 = this.specs[i].spec2;
                }
                break;
            }
        }
    }

    // 取得某字段的不重复值，如果有spec1，以此为条件
    this.getDistinctValues = function(field, spec1)
    {
        var values = new Array();
        for (var i = 0; i < this.specs.length; i++)
        {
            var value = this.specs[i][field];
            if (spec1 != '' && spec1 != this.specs[i].spec1) continue;
            if ($.inArray(value, values) < 0)
            {
                values.push(value);
            }
        }
        return (values);
    }
	
	
	
	// 取得某规格名称的不重复的项， 
    this.getDistinctItems = function(field, spec1)
    {
		var values = new Array();
		var items = new Array();
        for (var i = 0; i < this.specs.length; i++)
        {
            var value = this.specs[i][field];
            if (spec1 != '' && spec1 != this.specs[i].spec1) continue;
            if ($.inArray(value, values) < 0)
            {
                values.push(value);
				items.push(this.specs[i]);
            }
        }
        return (items);
    }

    // 取得选中的spec
    this.getSpec = function()
    {
        for (var i = 0; i < this.specs.length; i++)
        {
            if (this.specQty >= 1 && this.specs[i].spec1 != this.spec1) continue;
            if (this.specQty >= 2 && this.specs[i].spec2 != this.spec2) continue;

            return this.specs[i];
        }
        return null;
    }

    // 初始化
    this.init = function()
    {
		var spec = this.getSpec();
        if (this.specQty >= 1)
        {
			var specImage = this.getDistinctItems('spec1', '');
            var spec1Values = this.getDistinctValues('spec1', '');
            for (var i = 0; i < spec1Values.length; i++)
            {
				var spec_img = "";
				if(specImage[i].spec_image) 
				{
					spec_img = "<img width='25' height='25' src='" + specImage[i].spec_image + "'/>";
				}

                if (spec1Values[i] == this.spec1)
                {
					
                    $(".handle ul:eq(0)").append("<li class='solid' onclick='selectSpec(1, this)'><a href='javascript:;'>" + spec_img + "<span>" + spec1Values[i] + "</span><b></b></a></li>");

                }
                else
                {
                    $(".handle ul:eq(0)").append("<li class='dotted' onclick='selectSpec(1, this)'><a href='javascript:;'>" + spec_img + "<span>" + spec1Values[i] + "</span><b></b></a></li>");
                }
            }
        }
        if (this.specQty >= 2)
        {
            var spec2Values = this.getDistinctValues('spec2', this.spec1);
            for (var i = 0; i < spec2Values.length; i++)
            {
                if (spec2Values[i] == this.spec2)
                {
                    $(".handle ul:eq(1)").append("<li class='solid' onclick='selectSpec(2, this)'><a href='javascript:;'><span>" + spec2Values[i] + "</span><b></b></a></li>");
                }
                else
                {
                    $(".handle ul:eq(1)").append("<li class='dotted' onclick='selectSpec(2, this)'><a href='javascript:;'><span>" + spec2Values[i] + "</span><b></b></a></li>");
                }
            }
        }
        var spec = this.getSpec();
		setGoodsProInfo(spec.goods_id, spec.id, spec.price);
        $("[ectype='current_spec']").html(spec.spec1 + ' ' + spec.spec2);
		$("[ectype='goods_stock']").html(spec.stock);
		if(spec.spec_image){
			$(".big_pic a img").attr('src',spec.spec_image);
			$(".big_pic a").attr('href',spec.spec_image);
			$(".tiny-pics ul li").attr('class','');
		}
    }
}

/* 选中某规格 num=1,2 */
function selectSpec(num, liObj)
{
    goodsspec['spec' + num] = $(liObj).find('a span').html();
	
    $(liObj).attr("class", "solid");
    $(liObj).siblings(".solid").attr("class", "dotted");
	if(num == 1)
	{
		if($(liObj).find('img').length > 0)
		{
			$(".big_pic a img").attr('src',$(liObj).find('img').attr('src'));
			$(".tiny-pics ul li").attr('class','');
		}
		else
		{
			$(".big_pic a img").attr('src',$(".tiny-pics ul li:first").find('img').attr('src'));
			$(".tiny-pics ul li:first").attr('class','pic_hover');
		}
	}

    // 当有2种规格并且选中了第一个规格时，刷新第二个规格
    if (num == 1 && goodsspec.specQty == 2)
    {
        goodsspec.spec2 = null;
        $(".aggregate").html("");
        $(".handle ul:eq(1) li[class='handle_title']").siblings().remove();

        var spec2Values = goodsspec.getDistinctValues('spec2', goodsspec.spec1);
        for (var i = 0; i < spec2Values.length; i++)
        {
            $(".handle ul:eq(1)").append("<li class='dotted' onclick='selectSpec(2, this)'><a href='javascript:;'><span>" + spec2Values[i] + "</span><b></b></a></li>");
        }
    }
    else
    {
        var spec = goodsspec.getSpec();
        if (spec != null)
        {
			setGoodsProInfo(spec.goods_id, spec.id, spec.price);
			$("[ectype='current_spec']").html(spec.spec1 + ' ' + spec.spec2);
            $("[ectype='goods_stock']").html(spec.stock);
        }
    }
}
function slideUp_fn()
{
    $('.ware_cen').slideUp('slow');
}
$(function(){
    goodsspec.init();
	
    //放大镜效果/
    if ($(".jqzoom img").attr('jqimg'))
    {
        $(".jqzoom").jqueryzoom({ xzoom: 425, yzoom: 310 });
    }
    // 图片替换效果
    $('.ware_box li').mouseover(function(){
        $('.ware_box li').removeClass();
        $(this).addClass('ware_pic_hover');
        $('.big_pic img').attr('src', $(this).children('img:first').attr('src'));
        $('.big_pic img').attr('jqimg', $(this).attr('bigimg'));
    });

    //点击后移动的距离
    var left_num = -61;

    //整个ul超出显示区域的尺寸
    var li_length = ($('.ware_box li').width() + 6) * $('.ware_box li').length - 305;

    $('.right_btn').click(function(){
        var posleft_num = $('.ware_box ul').position().left;
        if($('.ware_box ul').position().left > -li_length){
            $('.ware_box ul').css({'left': posleft_num + left_num});
        }
    });

    $('.left_btn').click(function(){
        var posleft_num = $('.ware_box ul').position().left;
        if($('.ware_box ul').position().left < 0){
            $('.ware_box ul').css({'left': posleft_num - left_num});
        }
    });
	
	//查看晒单图片的大图
	$('.J-showBigImage').click(function(){
		var src = $(this).attr('src');
		$(this).parents('dd').find('.J-bigImage').html('<img width="250" style="display:block;margin-top:10px;" src="'+src+'"/>');
	})

    // 加入购物车弹出层
    $('.close_btn').click(function(){
        $('.ware_cen').slideUp('slow');
    });
	//by mimall start
	$('.tiny-pics .list li').mouseover(function(){
        $('.tiny-pics .list li').removeClass();
        $(this).addClass('pic_hover');
    });
	
	var moveNum=-62;
	var lengthLi = ($('.tiny-pics .list li').width()) * $('.tiny-pics .list li').length - 310;
	$('#forword').click(function(){
        var posleftNum = $('.tiny-pics .list').position().left;
        if($('.tiny-pics .list').position().left > -lengthLi){
            $('.tiny-pics .list').css({'left': posleftNum + moveNum});
        }
    });
	$('#backword').click(function(){
        var posleftNum = $('.tiny-pics .list').position().left;
        if($('.tiny-pics .list').position().left < 0){
            $('.tiny-pics .list').css({'left': posleftNum - moveNum});
        }
    });
    
	// tyioocom delivery 
	$('.postage-cont').hover(function(){
		$(this).find('.postage-area').show();
	},function(){
		$(this).find('.postage-area').hide();
	});
	$('.province a').click(function(){
		$('.cities').find('div').hide();
		$('.cities .city_'+this.id).show();		
		$('.province').find('a').attr('class','');
		$(this).attr('class','selected');
	});
	$('.cities a').click(function(){
		$('.cities').find('a').attr('class','');
		$(this).attr('class','selected');
						
		delivery_template_id = $(this).attr('delivery_template_id');
		city_id 	= $(this).attr('city_id');
		store_id    = $(this).attr('store_id');
			
		//  加载指定城市的运费
		load_city_logist(delivery_template_id,store_id,city_id); //传递 store_id,是为了在delivery_templaet_id 为0 的情况下，获取店铺的默认运费模板
	});

});

//  加载城市的运费(指定城市id或者根据ip自动判断城市id)
function load_city_logist(delivery_template_id,store_id,city_id)
{
	html = '';
	if(city_id==undefined) {
		city_id = '';
	}
	var url = SITE_URL + '/index.php?app=logist&delivery_template_id='+delivery_template_id+'&store_id='+store_id+'&city_id='+city_id;
		$.getJSON(url,function(data){
			if (data.done){
				data = data.retval;
				$.each(data.logist_fee,function(n,v){
					html += v.name+':'+v.start_fees+'元 ';
				});
				$('#selected_city').html('至&nbsp;'+data.city_name+'<b></b>');
				$('.postage-info').html(html);
				$('.postage-area').hide();
			}
			else
			{
				$('#selected_city').html('至&nbsp;全国');
				$('.postage-info').html(data.msg);
				$('.postage-area').hide();
			}
	});
}

// 获取促销商品，会员价格等的优惠信息
function setGoodsProInfo(goods_id, spec_id, price)
{
	var url = REAL_SITE_URL + "/index.php?app=goods&act=getGoodsProInfo&goods_id="+goods_id+"&spec_id="+spec_id;
	$.getJSON(url,function(data){
		if (data.done){
			pro_price = data.retval.pro_price;
			pro_type  = data.retval.pro_type;
			
			// 目前只有限时打折商品有倒计时效果
			if($.inArray(pro_type, ['limitbuy']) > -1) {
				$('.J_CountDown').css('display', 'block');
			}
			$(".J_IsPro").css('display','block');
			$(".J_IsNotPro").css('display','none');
			$("[ectype='goods_price']").html('<del>'+price_format(price)+'</del>');
			$("[ectype='goods_pro_price']").html(price_format(pro_price));
		}
		else
		{
			$("[ectype='goods_price']").html(price_format(price));
			$('.J_CountDown').css('display', 'none');
			$(".J_IsPro").css('display','none');
			$(".J_IsNotPro").css('display','block');
		}
	});
}