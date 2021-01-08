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

/* 显示Ajax表单 */
function ajax_form(id, title, url, width, style, opacity)
{
    if (!width)
    {
        width = 400;
    }
    var d = DialogManager.create(id);
    d.setTitle(title);
    d.setContents('ajax', url);
    d.setWidth(width);
	if(style)
	{
		d.setStyle(style);
	}
	if(opacity)
	{
		ScreenLocker.style.opacity = opacity;
	}
    d.show('center');

    return d;
}
function go(url){
	//var spm = Math.random() * 5;
	//if(url.indexOf("?") != -1) {
		//url = url + "&spm=" + spm;
	//}
	//else url = url + "?spm=" + spm;
    window.location = url;
}

function change_captcha(jqObj){
    jqObj.attr('src', 'index.php?app=captcha&' + Math.round(Math.random()*10000));
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

/* 收藏商品 */
function collect_goods(id)
{
    var url = SITE_URL + '/index.php?app=my_favorite&act=add&type=goods&ajax=1';
    $.getJSON(url, {'item_id':id}, function(data){
        alert(data.msg);
    });
}

/* 收藏店铺 */
function collect_store(id)
{
    var url = SITE_URL + '/index.php?app=my_favorite&act=add&type=store&jsoncallback=?&ajax=1';
    $.getJSON(url, {'item_id':id}, function(data){
        alert(data.msg);
    });
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
        url: SITE_URL + "/index.php?app=default&act=sendcode",
        data:params,
		dataType:"json",
		success:function(data){
			if(data.done){
				time(o, interval);
            	alert(data.msg);
            }
            else{
				o.attr('disabled', false);
                alert(data.msg);
            }
        },
        error: function(){alert(lang.captcha_send_failure);}
    });
}
function send_emailcode(o, params, interval){
    $.ajax({
        type:"POST",
        url: SITE_URL + "/index.php?app=default&act=sendemail",
        data:params,
        dataType:"json",
        success:function(data){
           	 if(data.done){
				time(o, interval);
            	alert(data.msg);
            }
            else{
				o.attr('disabled', false);
                alert(data.msg);
            }
        },
        error: function(){alert(lang.captcha_send_failure);}
    });
}

/* Ajax page */
function ajax_page(page_info)
{
	html = '';
	if(page_info.length == 0) return html;

	html = '<div class="page page-small"><b class="stat">共 '+page_info.item_count+' 个项目</b>';
							
	if(page_info.prev_link) {
		html += '<a class="former" href="javascript:showPage('+page_info.prev_page+');"></a>';
	}
	else {
		html += '<span class="former_no"></span>';
	}
	if (page_info.first_link) {
		html += '<a class="page_link" href="javascript:showPage(1);">1&nbsp;'+page_info.first_suspen+'</a>';
	}
	$.each(page_info.page_links, function(page, item) {
		if(page_info.curr_page==page) {
			html += '<a class="page_hover" href="javascript:showPage('+page+');">'+page+'</a>';
		}
		else {
			html += '<a class="page_link" href="javascript:showPage('+page+');">'+page+'</a>';
		}
	});
	if(page_info.last_link) {
		html += '<a class="page_link" href="javascript:showPage('+page_info.page_count+');">'+page_info.last_suspen+'&nbsp;'+page_info.page_count+'</a>';
	}
	if(page_info.next_link) {
		html += '<a class="down" href="javascript:showPage('+page_info.next_page+');">下一页</a>';
	} 
	else {
		html += '<span class="down_no">下一页</span>';
	}
	html += '</div>';
							
	return html;
}

function replace_all(str, s, r) {
	
	if(typeof str != 'string')  return str;
	
	//g 表示全部替换，没用正则的情况下， replace只能替换第一个
	var reg = new RegExp(s,"g");
	
	return str.replace(reg,r);
}


function randomString(len)
{
 　 len = len || 32;
 　 var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
 　 var maxPos = $chars.length;
　　var pwd = '';
　　for (i = 0; i < len; i++) {
　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
　　}
　　return pwd;
}