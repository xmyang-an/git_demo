$(function(){
	if($('body').find('.J_GlobalPop').length > 0) {
		$(".J_GlobalPop").slide({
			titOnClassName:"hover", type:"menu", titCell:".item", targetCell:".J_GlobalPopSub",effect:"slideDown",
			delayTime:300,triggerTime:0,defaultPlay:false, returnDefault:true
		});
	}
	if($('body').find('.J_SearchFixed').length > 0) {
		$(window).scroll(function() {
        	if ($(window).scrollTop() > 100) {
            	$(".J_SearchFixed").show();
         	} else {
            	$(".J_SearchFixed").hide();
         	}
     	});
	}
	
	if($('body').find('.backtop').length > 0) {
		$(".backtop").hide();
		
    	$(window).scroll(function() {
        	if ($(window).scrollTop() > 320) {
            	$(".backtop").show();
         	} else {
            	$(".backtop").hide();
         	}
     	});
	}
	 $('.backtop').click(function(){
		 $("html,body").animate({scrollTop: 0}, 500);
	 });
	
	$('.J_ShowCategory .allcategory').hover(function(){
		$(this).find('.allcategory-list').show();
	},function(){
		$(this).find('.allcategory-list').hide();
	});
	
	if($('body').find('.J_SearchType li').length > 0) {
		initSearchType($('.J_SearchType li'));
	}
	
	$('.J_SearchType').hover(function(){
		$(this).addClass('hover');
	}, function(){
		$(this).removeClass('hover');
	});
	$('.J_SearchType li').click(function(){
		clickSearchType($(this));
	});
   
	$('.J_GlobalImageAdsBotton').click(function(){
		$(this).hide();
		$(this).parent().slideUp();
	});
	
	$('.J_SwtcherInput').click(function(){
		$(this).toggleClass('checked');
	});
})

// 页面刷新后初始化搜索框筛选类型
function initSearchType()
{
	var selected = $('.J_SearchType li').parent().find('li.current').html();
	var selectedValue = $('.J_SearchType li').parent().find('li.current').find('span').attr('value');
	var first = $('.J_SearchType li').parent().find('li:first').html().replace('<b></b>', '');
	
	$('.J_SearchType li').parent().find('li.current').html(first).removeClass('current');
	$('.J_SearchType li').parent().find('li:first').html(selected).addClass('current').find('span').after('<b></b>');
	$('.J_SearchType li').parent().find('li:first').show();
	
	$('.J_SearchType li').parent().parent().find("input[name='act']").val(selectedValue);
}
//  用户点击搜索框筛选类型事件
function clickSearchType(o)
{
	var selected = o.html();
	var selectedValue = o.find('span').attr('value');
	var first = o.parent().find('li:first').html().replace('<b></b>', '');
		
	o.parent().find('li:first').html(selected).addClass('current').find('span').after('<b></b>');
	o.html(first).removeClass('current');
	
	o.parent().parent().find("input[name='act']").val(selectedValue);
	o.parent().removeClass('hover');
}

// 领取优惠券（方便调用，比如上传一张图片，设置href="javascript:couponReceive(coupon_id);" 即可领取，实现多处可领券的目的
function couponReceive(coupon_id)
{
	var url = REAL_SITE_URL + '/index.php?app=coupon&act=receive&id='+coupon_id;
	$.getJSON(url, function(data){console.log(data);
		alert(data.msg);
	});
}