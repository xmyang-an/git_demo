$(function(){
	
	// 处理移动端下:hover 无效的问题
	document.body.addEventListener('touchstart', function () {});
	
	$('.J_pagemore').click(function(){
		$(this).parent().next('.J_eject_tab').toggle();
	});
	
	// 必须用body以便兼容dialog
	$('body').on('click', '.radioUiStyle', function(){
		if(!$(this).hasClass('disabled')) {
			$(this).parents('.radioUiWraper').find('.radioUiStyle').removeClass('active');
			$(this).parents('.radioUiWraper').find('input[type="radio"]').prop('checked', false);
			$(this).addClass('active');
			$(this).find('input[type="radio"]').prop('checked', true);
		} else $(this).find('input[type="radio"]').prop('checked', false);
	});
	
	// 必须用body以便兼容dialog
	$('body').on('click', '.checkboxUiStyle', function(){
		if(!$(this).hasClass('disabled')) {
			$(this).toggleClass('active');
			$(this).find('input[type="checkbox"]').prop('checked', $(this).find('input[type="checkbox"]').prop('checked') == false);
		} else $(this).find('input[type="checkbox"]').prop('checked', false);
	});
	
	/* 移动端下通用异步请求(FORM) */
	$('.J_AjaxFormSubmit').click(function(){
		// 防止重复提交
		$(this).prop('disabled', true);
		var method 	= $(this).parents('form').attr('method').toUpperCase();
		var url		=  window.location.href;
		var ret_url = $(this).parents('form').find('.J_AjaxFormRetUrl').val();
		var formObj = $(this).parents('form');
		ajaxRequest(method, url, formObj, ret_url, $(this));
		return false;
	});
	
	/* 移动端下通用异步请求(GET) */
	$('body').on('click', '.J_AjaxRequest', function(){
		var method = 'GET';
		var url = $(this).attr('action');
		var affirm = $(this).attr('confirm');
		var ret_url = $(this).attr('ret_url');
		
		if(affirm){
			layer.open({
    			content: affirm,
    			btn: [lang.confirm, lang.cancel],
    			shadeClose: false,
    			yes: function(){
					ajaxRequest(method, url, null, ret_url, $(this));
    			}, 
				no: function(){
					// TODO
    			}
			});
		} else {
			ajaxRequest(method, url, null, ret_url, $(this));
		}
	});
	
	// 通用popLayer弹出层触发
	$('.J_PopLayer').each(function(index, element) {
		$(this).popLayer($(this).attr('data-PopLayer'));
 	});
	// 针对PopLayer 需要做初始赋值 
	$('.J_PopLayer__INIT').each(function(index, element) {
		var o = $(this);
        $(this).find('p').html($.trim($(this).next('.pop-layer-common').find('li.active:last').find('.lp span').text()));
		$(this).next('.pop-layer-common').find('li').click(function(){
			o.find('p').html($.trim($(this).find('.lp span').text()));
		});
    });
	
	$("input,select").blur(function(){
        setTimeout(function(){
			const scrollHeight = document.documentElement.scrollTop || document.body.scrollTop || 0;
			window.scrollTo(0, Math.max(scrollHeight - 1, 0));
        }, 100);
    })
});

/* 移动端下通用请求 */
function ajaxRequest(method, url, formObj, ret_url, oClick)
{
	if((formObj == null) || (formObj == undefined)) formObj = $('<form></form>');
	if((oClick == null) || (oClick == undefined))  oClick = $('<input></input>');
	if(url) url = replace_all(url, '&amp;', '&');
	
	formObj.ajaxSubmit({
		type:method,
		url:url,
		async:false,
		cache:false,
		dataType: "json",
		beforeSubmit:function(){
			//return formObj.valid();
		},
		success: function(data){
			if(data.done) {
				var retObj = formObj.find('.J_AjaxFormRetUrl');
				
				if((retObj.hasClass('J_RedirectToWx') == true) && (WXPRO == 1)){
					redirectToWXPro(retObj);
					return false;
				}
				else{
					// 重定向
					var redirect = ''; // 默认刷新当前页
					
					// 1)先判断是否在PHP设置重定向
					if($.inArray(data.retval.ret_url, [undefined, '']) < 0) {
						if(data.retval.wxmp != undefined){
							var url = data.retval.ret_url;
							var redirectType = data.retval.redirectType;
							
							layer.open({content: data.msg, time: 3, end: function(data) {
								GoToWXMP(url,redirectType);
							}});
							
							return false;
						}
						else{
							redirect =  replace_all(data.retval.ret_url, '&amp;', '&');
						}
					}
					
					// 2)判断是否在HTML设置重定向
					else if($.inArray(ret_url, [undefined, '']) < 0) {
						redirect =  replace_all(ret_url, '&amp;', '&');
					}
					
					layer.open({content: data.msg, time: 3, end: function(data) {
						if(redirect == null) { /* NOT TO */ }
						else if(redirect == '') {
							window.location.reload();
						}
						else if(typeof redirect == 'string') {
							go(redirect); 
						} else window.history.go(-1);
					}});
				}
			}
			else
			{
				oClick.prop('disabled', false);
				layer.open({content: data.msg, time: 3});
			}
				 
		},
		error: function(data) {
			oClick.prop('disabled', false);
			layer.open({content: lang.system_busy,time: 5});
		}
	});
}

jQuery.extend({
  getCookie : function(sName) {
    var aCookie = document.cookie.split("; ");
    for (var i=0; i < aCookie.length; i++){
      var aCrumb = aCookie[i].split("=");
      if (sName == aCrumb[0]) return decodeURIComponent(aCrumb[1]);
    }
    return '';
  },
  setCookie : function(sName, sValue, sExpires) {
    var sCookie = sName + "=" + encodeURIComponent(sValue);
    if (sExpires != null) sCookie += "; expires=" + sExpires;
    document.cookie = sCookie;
  },
  removeCookie : function(sName) {
    document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
  }
});

function drop_confirm(msg, url){
    if(confirm(msg)){
        window.location = url;
    }
}

// 获取图片上传本地的地址路径，兼容移动端浏览器
function getTempPath(obj) {
	var ext=obj.value.substring(obj.value.lastIndexOf(".")+1).toLowerCase();
	var file = obj.files[0];
	var reader = new FileReader();
	reader.readAsDataURL(file);
	reader.onload = function(e){
		$(obj).parent().append("<img src='"+this.result+"' />");
	}
}

/* 显示Ajax表单 */
function ajax_form(id, title, url, width, style, opacity, position)
{
    var d = DialogManager.create(id);
    d.setTitle(title);
    d.setContents('ajax', url);
	
	if(!width) {
    	d.setWidth(width);
	}
	if(style)
	{
		d.setStyle(style);
	}
	if(opacity)
	{
		ScreenLocker.style.opacity = opacity;
	}
	if(position) {
		d.show(position);
	} else {
		d.show('center');
	}
    return d;
}
function go(url){
	/*var spm = Math.random() * 5;
	if(url.indexOf("?") != -1) {
		url = url + "&spm=" + spm;
	}
	else url = url + "?spm=" + spm;
    window.location = url;
	*/
	url = decodeURIComponent(url);
	if(url.toLowerCase().indexOf('http') == -1) {
		if(url.substr(0,1) != '/') {
			url = '/' + url;
		}
		url = SITE_URL + url;
	}
    window.location = url;
}

function change_captcha(jqObj){
    jqObj.attr('src', REAL_SITE_URL +'/index.php?app=captcha&' + Math.round(Math.random()*10000));
}

/* 格式化金额 */
function price_format(price){
    if(typeof(PRICE_FORMAT) == 'undefined'){
        PRICE_FORMAT = '&yen;%s';
    }
    price = number_format(price, 2);

    return PRICE_FORMAT.replace('%s', price);
}

function number_format(num, ext){
    if(ext < 0){
        return num;
    }
    num = Number(num);
    if(isNaN(num)){
        num = 0;
    }
    var _str = num.toString();
    var _arr = _str.split('.');
    var _int = _arr[0];
    var _flt = _arr[1];
    if(_str.indexOf('.') == -1){
        /* 找不到小数点，则添加 */
        if(ext == 0){
            return _str;
        }
        var _tmp = '';
        for(var i = 0; i < ext; i++){
            _tmp += '0';
        }
        _str = _str + '.' + _tmp;
    }else{
        if(_flt.length == ext){
            return _str;
        }
        /* 找得到小数点，则截取 */
        if(_flt.length > ext){
            _str = _str.substr(0, _str.length - (_flt.length - ext));
            if(ext == 0){
                _str = _int;
            }
        }else{
            for(var i = 0; i < ext - _flt.length; i++){
                _str += '0';
            }
        }
    }

    return _str;
}

// eval string format to JSON object
function evil(fn) {
	if (typeof fn != "object") {
    	var Fn = Function;
    	return new Fn('return ' + fn)();
	}
	else return fn;
}


/**
 *    启动邮件队列
 *
 *    @author    MiMall
 *    @param     string req_url
 *    @return    void
 */
function sendmail(req_url)
{
    $(function(){
        var _script = document.createElement('script');
        _script.type = 'text/javascript';
        _script.src  = req_url;
        document.getElementsByTagName('head')[0].appendChild(_script);
    });
}
/* 转化JS跳转中的 ＆ */
function transform_char(str)
{
    if(str.indexOf('&'))
    {
        str = str.replace(/&/g, "%26");
    }
    return str;
}

function replace_all(str, s, r) {
	
	if(typeof str != 'string')  return str;
	
	//g 表示全部替换，没用正则的情况下， replace只能替换第一个
	var reg = new RegExp(s,"g");
	
	return str.replace(reg,r);
}


function is_mobile(str)
{
	if (str.match(/^(1[0-9][0-9]{1}[0-9]{8})$/)) {
		return true;
	}
	return false; 
}

function is_email(str)
{
	if (str.match(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/)) { 
		return true;
	}
	return false;
}

/* 通用倒计时 */
function countdown(theDaysBox, theHoursBox, theMinsBox, theSecsBox)
{
	// 避免重复reload
	if(theDaysBox.text() <=0 && theHoursBox.text() <= 0 && theMinsBox.text() <= 0 && theSecsBox.text() <= 0) {
		return;
	}
	
	var refreshId = setInterval(function() {
		var currentSeconds = theSecsBox.text();
		var currentMins    = theMinsBox.text();
		var currentHours   = theHoursBox.text();
		var currentDays    = theDaysBox.text();
	  
		// hide day
		if(currentDays == 0) {
			theDaysBox.next('em').hide();
			theDaysBox.hide();
		}
	  
		if(currentSeconds == 0 && currentMins == 0 && currentHours == 0 && currentDays == 0) {
			// if everything rusn out our timer is done!!
			// do some exciting code in here when your countdown timer finishes
			clearInterval(refreshId);
			window.location.reload();
	  	
		} else if(currentSeconds == 0 && currentMins == 0 && currentHours == 0) {
			// if the seconds and minutes and hours run out we subtract 1 day
			theDaysBox.html(currentDays-1);
			theHoursBox.html("23");
			theMinsBox.html("59");
			theSecsBox.html("59");
		} else if(currentSeconds == 0 && currentMins == 0) {
			// if the seconds and minutes run out we need to subtract 1 hour
			theHoursBox.html(currentHours-1);
			theMinsBox.html("59");
			theSecsBox.html("59");
		} else if(currentSeconds == 0) {
			// if the seconds run out we need to subtract 1 minute
			theMinsBox.html(currentMins-1);
			theSecsBox.html("59");
		} else {
			theSecsBox.html(currentSeconds-1);
		}
	}, 1000);
}

/* 通用验证码发送控制（手机/EMail）*/
function time(o, wait) {
	if (wait == 0) {
		o.attr("disabled", false);			
		o.val(lang.get_captcha);
		wait = 120;
	} else {
		o.attr("disabled", true);
		o.val(lang.get_captcha_again+"(" + wait + lang.miao_hou+")");
		wait--;
		setTimeout(function() {
			time(o, wait);
		},
		1000)
	}
}
function send_phonecode(o, params, interval){
	$.ajax({
		type:"POST",
        url: REAL_SITE_URL + "/index.php?app=default&act=sendcode",
        data:params,
		dataType:"json",
		success:function(data){
			if(data.done){
				time(o, interval);
            	layer.open({content: data.msg, className:'layer-popup', time: 3});
            }
            else{
				o.attr('disabled', false);
				layer.open({content: data.msg, className:'layer-popup', time: 3});
            }
        },
        error: function(){layer.open({content: lang.captcha_send_failure, className:'layer-popup', time: 3})}
    });
}
function send_emailcode(o, params, interval){
    $.ajax({
        type:"POST",
        url: REAL_SITE_URL + "/index.php?app=default&act=sendemail",
        data:params,
        dataType:"json",
        success:function(data){
           	 if(data.done){
				time(o, interval);
            	layer.open({content: data.msg, className:'layer-popup', time: 3});
            }
            else{
				o.attr('disabled', false);
				layer.open({content: data.msg, className:'layer-popup', time: 3});
            }
        },
        error: function(){layer.open({content: lang.captcha_send_failure, className:'layer-popup', time: 3})}
    });
}

/* 类似于PHP的 sprintf */
function sprintf()
{
  var num = arguments.length;
  var oStr = arguments[0] || '';
  for (var i = 1; i < num; i++) {
    var pattern = "\\{" + (i) + "\\}";
    var re = new RegExp(pattern, "g");
    oStr = oStr.replace(re, arguments[i]);
  }
  return oStr;
}

function GetLocation(callback){
	this.callback = callback;
	
	this.init = function(){
		var that = this;
		that.locationMethod();
	},
	
	this.callBack = function(res){
		if(typeof this.callback == 'function'){
			this.callback(res);
		}
	},
	
	this.locationMethod = function(){
		var that = this;
		if(navigator.userAgent.match(/Html5Plus/i)) {
			H5Location();
		}
		else{
			that.baiduGeolocation();
		}
	},
	
	this.baiduGeolocation = function(){
		var that = this;
		var geolocation = new BMap.Geolocation();
		geolocation.getCurrentPosition(function(r){
			if(this.getStatus() == BMAP_STATUS_SUCCESS){
				that.locationInformation(r.point.lat, r.point.lng);
			}
			else {
				console.log(this.getStatus());
			}        
		},{enableHighAccuracy: true})	
	},
	
	this.H5Location = function(){
		navigator.geolocation.getCurrentPosition(locationInformation);
	},
	
	this.locationInformation = function(lat,lng){
		var that = this;
		
		var url = REAL_SITE_URL+'/index.php?act=locationInformation';
		$.getJSON(url, {lng : lng, lat : lat} , function(data){console.log(data);
			if(data.done){
				that.callBack(data);
			}
			else{
				console.log(data.msg);	
			}
		})
	}
}
(function($){
	
 // 弹出多个层（不跳转）
 $.fn.ajaxSwitcher = function(options){
		var defaults = {url:'', title: '请选择', joinStr:' ', startId: 0, callback : function(){}};
		var opts = $.extend({},defaults, options);
		var mlsIdInput = this.find('.mls_id');
		var mlsNamesInput = this.find('.mls_names');
		var mls_names = new Array();
		
		this.find('input').blur().attr('readonly','readonly');
		
		this.click(function(){
			var data = ajaxData(opts.startId);
			outputTemplate(data);
			
			mls_names.splice(0,mls_names.length);//清空数组
		})
		
		$(document).on('click', (opts.model)+' .list li',function(){
			var mls_id = $(this).attr('data-val');
			var data = ajaxData(mls_id);
			mls_names.push($(this).find('span').text());
			
			if(data != '')
			{
				prevPid = mls_id;
				outputTemplate(data);
				
			}else{
				$('.ajaxSwitcher').animate({'right':'-110%','left' : '110%'}, 'fast', 'linear', function(){
				$(this).remove();
			});
				mlsNamesInput.val(mls_names.join(opts.joinStr));
				mlsNamesInput.parent().find('p.mls_names').html(mls_names.join(opts.joinStr)).removeClass('gray');
			}

			mlsIdInput.val(mls_id);

			opts.callback();
		})

		$(document).on('click', (opts.model)+' .backToPrev', function(){
			$(this).parents('.ajaxSwitcher').animate({'right':'-110%','left' : '110%'}, 'fast', 'linear', function(){
				$(this).remove();
			});
			if(mls_names != ''){
				$(this).parents('.ajaxSwitcher').prev().show();
				mls_names.splice(mls_names.length-1, mls_names.length);
			}
		})
		
		function ajaxData(mls_id)
		{
			$.ajaxSettings.async = false; 
			
			var result = '';
			$.getJSON(opts.url,{pid : mls_id},function(data){
				if(data.done)
				{
					result = data.retval;
				}
			})
			
			return result;
		}
		
		function outputTemplate(data)
		{
			var template = "<div class='ajaxSwitcher left'><div class='wraper "+(opts.model).substr(1)+"'>"+
						   "<div class='hd'><div class='wrap webkit-box'><a href='javascript:;' class='float-left backToPrev'><i></i></a><span class='flex1 title'>"+opts.title+"</span></div></div>";
			template += "<div class='bd'><ul class='list'>";
			$.each(data, function(index, res){
				template += "<li data-val='"+res.mls_id+"' class='webkit-box'><span class='block flex1 fs14'>"+res.mls_name+"</span><i class='psmb-icon-font block'></i></li>";
			})
			template += "</ul></div></div></div>";
			
			$('body').append(template);
	
			if($(opts.model).length > 1){
				$(opts.model+':last').siblings(opts.model).hide();
			}
		}
	};

	$.fn.switcher = function(options){
		var defaults = {
			 url:''
			, layer : null
			, spliter : ' '
			,callback:function(e){}
		};
		var opts = $.extend({},defaults, evil(options));
		
		var o = this;
		var selectName = new Array();
		var listEach = o['selector']+' .J-switcherModule .J-switcherEach';
		var listTitle = o['selector']+' .J-switcherTitle';
		
		var showTextDom = $('#'+o.attr('data-showText'));
		var assignValueDom = $('#'+o.attr('data-assignValue'));
		var assignNameDom = $('#'+o.attr('data-assignName'));
		var closePopBtn = o.find('.J-switherClose');

		$(document).on('click',listEach,function(){
			var v = $(this).attr('data-value');
			var t = $(this).attr('data-title');
			var i = $(this).parents('.J-switcherModule').index();
			
			selectName.splice(i,opts.layer,t) ;
			$(this).addClass('active').siblings().removeClass('active');
			o.find('.J-switcherTitle:eq('+i+')').text(t);
			
			if(i < (opts.layer-1)){
				o.find('.J-switcherModule:eq('+(i+1)+')').html('<div class="loading"></div>').show().siblings().hide();
				o.find('.J-switcherTitle:eq('+(i+1)+')').addClass('active').show().siblings().removeClass('active');

				$.getJSON(opts.url,{'pid':v},function(data){
					if(data.done){
						if(data.retval.length){
							var tpl = '';
							$.each(data.retval,function(k,v){
								tpl += '<li class="J-switcherEach" data-title="'+v.value+'" data-value="'+v.id+'">'+v.value+'</li>';
							})
							
							o.find('.J-switcherModule:eq('+(i+1)+')').html(tpl);
						}else{
							o.find('.J-switcherTitle:eq('+i+')').click();
							
							showTextDom.text(selectName.join(opts.spliter));
							assignNameDom.val(selectName.join(opts.spliter));
							assignValueDom.val(v);
							closePopBtn.click();
							opts.callback();
						}
					}
				})
			}else{
				showTextDom.text(selectName.join(opts.spliter));
				assignNameDom.val(selectName.join(opts.spliter));
				assignValueDom.val(v);
				closePopBtn.click();
				opts.callback();
			}
		})
		
		$(document).on('click',listTitle,function(){
			var i = $(this).index();
			
			$(this).addClass('active').siblings().removeClass('active');
			$(this).nextAll().text(lang.select_pls).hide();
			
			o.find('.J-switcherModule:eq('+i+')').show().siblings().hide();
			o.find('.J-switcherModule:eq('+i+')').nextAll().html('');
		})
 	};	
	
   // 简单的弹出一个层
   $.fn.popLayer = function(options){
		var defaults = {popLayer:'.popLayer', closeBtn:'.popClosed', resetBtn:'.popReset', masker:'.masker', direction:'bottom', fixedBody:false,top:0,bottom:0,left:0,right:0, callback:function(e){}};
		var opts = $.extend({},defaults, evil(options));
		
		this.each(function(){
			var obj = $(this);
			obj.click(function(){
				if(!$(this).hasClass('disabled')) {
					openLayer();
					opts.callback(obj);
				
					if(opts.fixedBody == true){
						$('body').css({'position':'fixed', 'left' : 0, 'right' : 0, 'top' : 0});
					}
				}
			})
		})
		// do not use $('body')
		$(opts.popLayer).on('click', opts.closeBtn, function(){
			if(!$(this).hasClass('disabled')) {
				closeLayer();
			}
		});
		// do not use $('body')
		$(opts.popLayer).on('click', opts.resetBtn, function(){
			closeLayer();
			setTimeout(function() {
				window.location = $(opts.resetBtn).attr('uri');
			}, 500);
		});
		
		// touchend for iPhone only
		$('body').on('touchend click', opts.masker, function(){
			closeLayer();
		});
		
		function closeLayer(){
			switch(opts.direction){
				case 'bottom' :
				$(opts.popLayer).animate({'bottom':'-150%','top':'150%'});
				break;
				
				case 'top' :
				$(opts.popLayer).animate({'top':'-150%','bottom':'150%'});
				break;
				
				case 'left' :
				$(opts.popLayer).animate({'left':'-110%','right':'110%'});
				break;
				
				case 'right' :
				$(opts.popLayer).animate({'right':'-110%','left' : '110%'});
				break;
				
				default : 
				$(opts.popLayer).animate({'bottom':'-150%','top':'150%'});
				break;
			}
			
			//将遮盖层去掉
			$(opts.masker).fadeOut('slow', function(){
				$(opts.masker).remove();
			});
			
			$('body').css({'position':'static'});//将固定的body释放
		}
		
		function openLayer(){
			switch(opts.direction){
				case 'bottom' :
				$(opts.popLayer).animate({'bottom':opts.bottom,'top':opts.top});
				break;
				
				case 'top' :
				$(opts.popLayer).animate({'top':opts.top,'bottom':opts.bottom});
				break;
				
				case 'left' :
				$(opts.popLayer).animate({'left':opts.left,'right':opts.right});
				break;
				
				case 'right' :
				$(opts.popLayer).animate({'right':opts.right, 'left':opts.left});
				break;
				
				default : 
				$(opts.popLayer).animate({'bottom':opts.bottom,'top':opts.top});
				break;
			}
			
			// 将其他遮盖层去掉
			$(opts.masker).remove();
	
			var maskerClass = (opts.masker).substr(1);
			$('body').append("<div style='background:rgba(0,0,0,0.3);position:fixed; left:0;bottom:0;width:100%; height:100%; display:none; z-index:991' class='"+maskerClass+"'></div>");
			$(opts.masker).fadeIn();
		}
	};
})(jQuery)