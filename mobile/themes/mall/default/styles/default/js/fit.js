$(function(){
	$(document).on('click', '.J_RedirectToWx',function(e){
		redirectToWXPro($(this))
		return false;
	})
})

function redirectToWXPro(obj){
	var href = obj.attr('href') || obj.val();
	var tmp = href.split('?');

	if(tmp[1] != undefined){
		var new_arr = new Array();
		var arr = tmp[1].split('&');
		var APP = "";
		var ACT = "index";
		for(var i = 0;i< arr.length ; i++){
			if(arr[i].indexOf('app=') != -1){
				APP = arr[i].replace('app=','');
			}
			else{
				if(arr[i].indexOf('act=') != -1){
					ACT = arr[i].replace('act=','');
				}
				else{
						new_arr.push(arr[i]);
				}
			}
		}
			
		if(APP != ""){
			var url = '/pages/'+APP+'/'+ACT+'?'+new_arr.join('&');
			var type = obj.attr('toType');

			if(type == undefined || type == 'navigate'){
				wx.miniProgram.navigateTo({url: url});  
			}
			else if(type == 'redirect'){
				wx.miniProgram.redirectTo({url: url});  
			}
			else if(type == 'relaunch'){
				wx.miniProgram.reLaunch({url: url});  
			}
		}
	}
	
	return false;
}

function GoToWXMP(url, redirectType)
{
	if(redirectType == undefined || redirectType == 'navigate'){
		wx.miniProgram.navigateTo({url: url});  
	}
	else if(redirectType == 'redirect'){
		wx.miniProgram.redirectTo({url: url});  
	}
	else if(redirectType == 'relaunch'){
		wx.miniProgram.reLaunch({url: url});  
	}
}

