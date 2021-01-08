$(function(){
	
	$("*[ectype='ul_cate'] a").click(function(){
        replaceParam('cate_id', this.id);
        return false;
    });

    $("*[ectype='ul_region'] a").click(function(){
        replaceParam('region_id', this.id);
        return false;
    });
	
	$(".J_ActiveSort").click(function(){	
		if($(".J_SortEject").is(":hidden")) {
			$(".J_SortEject").show();
			$(this).find('i').html('&#xe620;');
		} else {
			$(".J_SortEject").hide();
			$(this).find('i').html('&#xe61f;');
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
	
	$(".selected-attr a.each-filter").click(function(){
		dropParam(this.id);
		return false;
	});
	
});


/* 替换参数 */
function replaceParam(key, value)
{
    var params = location.search.substr(1).split('&');
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
        params.push(key + '=' + encodeURIComponent(value));
    }
    location.assign(REAL_SITE_URL + '/index.php?' + params.join('&'));
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
        if (pKey == key)
        {
            params.splice(i, 1);
        }
    }
    location.assign(REAL_SITE_URL + '/index.php?' + params.join('&'));
}