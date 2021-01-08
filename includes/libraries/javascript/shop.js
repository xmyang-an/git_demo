$(function(){   
	url = location.href;
	href = url.split('?');
	href = 'index.php?'+href[1];
	$('.J_ShopNav li').each(function(){
		if($(this).find('a').attr('href')==href || $(this).find('a').attr('href')==url){
			$(this).children('a').addClass('current');
		}
	});
});