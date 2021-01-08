$(function(){
	
	/* 用户中心搜索框 */
	$('.tsearch-tabs li').click(function(){
		$(this).parent().find('li').each(function(){
			$(this).attr('class','');
		});
		$(this).attr('class','tsearch-tabs-active');
		$('.tsearch-panel input[name="act"]').val($(this).attr('ectype'));
	});
	$('.tsearch-panel #keyword').focus(function(){
		$('.tsearch-panel label').hide();
	}).blur(function(){
		if($(this).val()==''){
			$('.tsearch-panel label').show();
		}
	});
	
	/* 预存款提现 */
	$('.deposit-withdraw .bank-each').click(function(){
		$(this).parent().find('.bank-each').removeClass('selected');
		$(this).parent().find('.bank-list input').attr('checked', false);
		$(this).find('input').attr('checked', true);
		$(this).addClass('selected');
	});
	$('.pop-tips').hover(function(){
		$(this).children('.pop-bd').show();
	},function(){
		$(this).children('.pop-bd').hide();
	});
	
	/* 预存款充值 */
	$('*[ectype="recharge-method"] input[name="method"]').click(function(){
		$(this).parent().find('input[name="method"]').attr('checked',false);
		$(this).next().removeClass('selected');
		$(this).attr('checked', true);
		$(this).addClass('selected');
		$('*[ectype="online"]').hide();
		$('*[ectype="offline"]').hide();
		$('*[ectype="'+$(this).val()+'"]').show();
	})
	
	/* 左栏菜单折叠 */
	$('#left .menu b').click(function(){
		$(this).toggleClass('fold');
		$(this).parent().parent().find('dd').each(function(){
			$(this).slideToggle();
		});
	});
	
    /* 全选 */
    $('.checkall').click(function(){
        var _self = this;
        $('.checkitem').each(function(){
            if (!this.disabled)
            {
                $(this).prop('checked', _self.checked);
            }
        });
        $('.checkall').prop('checked', this.checked);
    });

    /* 批量操作按钮 */
    $('a[ectype="batchbutton"]').click(function(){
        var items = getCheckItemIds();
		if(items)
		{
        	/* 将选中的项通过GET方式提交给指定的URI */
       		var uri = $(this).attr('uri');
        	window.location = uri + '&' + $(this).attr('name') + '=' + items;
		}
        return false;
    });

    /* 缩小大图片 */
    $('.makesmall').each(function(){
        if(this.complete){
            makesmall(this, $(this).attr('max_width'), $(this).attr('max_height'));
        }else{
            $(this).load(function(){
                makesmall(this, $(this).attr('max_width'), $(this).attr('max_height'));
            });
        }
    });

    $('.su_btn').click(function(){
        if($(this).hasClass('close')){
            $(this).parent().next('.su_block').css('display', '');
            $(this).removeClass('close');
        }
        else{
            $(this).addClass('close');
            $(this).parent().next('.su_block').css('display', 'none');
        }
    });
	
    $('body').on("click", '*[ectype="gselector"]', function(){
        var id = $(this).attr('gs_id');
        var name = $(this).attr('gs_name');
        var callback = $(this).attr('gs_callback');
        var type = $(this).attr('gs_type');
        var store_id = $(this).attr('gs_store_id');
        var title = $(this).attr('gs_title') ? $(this).attr('gs_title') : '';
        var width = $(this).attr('gs_width');
		var style = $(this).attr('gs_class');
		var opacity = $(this).attr('gs_opacity');
        ajax_form(id, title, SITE_URL + '/index.php?app=gselector&act=' + type + '&dialog=1&title=' + title + '&store_id=' + store_id+ '&id=' + id + '&name=' + name + '&callback=' + callback, width, style, opacity);
        return false;
    });

    var url = window.location.search;
    var params  = url.substr(1).split('&');
    var app = '';
    //找出排序的列和排序的方式及app控制器
    var sort  = '';
    var order = '';
    for(var j=0; j < params.length; j++)
    {
        var param = params[j];
        var arr   = param.split('=');
        if(arr[0] == 'app')
        {
            app = arr[1];
        }
        if(arr[0] == 'sort')
        {
            sort = arr[1];
        }
        if(arr[0] == 'order')
        {
            order = arr[1];
        }
    }
    $('span[ectype="order_by"]').each(function(){
        if($(this).parent().attr('column') == sort)
        {
            if(order == 'asc')
            {
            $(this).removeClass();
            $(this).addClass("sort_asc");
            }
        else if (order == 'desc')
            {
            $(this).removeClass();
            $(this).addClass("sort_desc");
            }
        }
    });
    $('span[ectype="order_by"]').click(function(){
        var s_name = $(this).parent().attr('column');
        var found   = false;
        for(var i = 0;i < params.length;i++)
        {
            var param = params[i];
            var arr   = param.split('=');
            if('page' == arr[0])
            {
                params[i] = 'page=1';
            }
            else if('sort' == arr[0])
            {
                params[i] = 'sort'+'='+ s_name;
                found = true;
            }
            else if('order' == arr[0])
            {
                params[i] = 'order'+'='+(arr[1] =='asc' ? 'desc' : 'asc');
            }
        }
        if(!found)
        {
                params.push('sort'+'='+ s_name);
                params.push('order=asc');
        }
        var href = window.location.href.split('?');
        location.assign(href[0] + '?' + params.join('&'));
    });

    // 初始化上传
    trigger_uploader();
});


function set_zindex(parents, index){
    $.each(parents,function(i,n){
        if($(n).css('position') == 'relative'){//alert('relative');
            //alert($(n).css('z-index'));
            $(n).css('z-index',index);
        }
    });
}


function js_success(dialog_id)
{
    DialogManager.close(dialog_id);
    var url = window.location.href;
    url =  url.indexOf('#') > 0 ? url.replace(/#/g, '') : url;
    window.location.replace(url);
}

function js_fail(str)
{
    $('#warning').html('<label class="error">' + str + '</label>');
    $('#warning').show();
}

function check_number(v)
{

    if(isNaN(v))
    {
        alert(lang.only_number);
        return false;
    }
    if(v.indexOf('-') > -1)
    {
        alert(lang.only_number);
        return false;
    }
    return true;
}
function check_required(v)
{
    if(v == '')
    {
        alert(lang.not_empty);
        return false;
    }
    return true;
}

function check_pint(v)
{
    var regu = /^[0-9]{1,}$/;
    if(!regu.test(v))
    {
        alert(lang.only_int);
        return false;
    }
    return true;
}

function check_max(v)
{
    var regu = /^[0-9]{1,}$/;
    if(!regu.test(v))
    {
        alert(lang.only_int);
        return false;
    }
    var max = 255;
    if(parseInt(v) > parseInt(max))
    {

        alert(lang.small+max);
        return false;
    }
    return true;
}

function order_action_result(action, order_id, rzt)
{
    if (rzt.done === false)
    {
        //操作失败
        alert(rzt.msg);

        return;
    }
    else
    {
        //操作成功
        //关闭窗口
        DialogManager.close(action);

        //更新视图
        for (k in rzt.retval)
        {
            switch (k)
            {
                case 'actions':
                    $('#order' + order_id + '_action').children().hide();
                    for (_j in rzt.retval[k])
                    {
                        $('#order' + order_id + '_action_' + rzt.retval[k][_j]).show();
                    }
                    break;
                default:
                    var _tmp = $('#order' + order_id + '_' + k);
                    _tmp.html(rzt.retval[k]);
                    break;
            }
        }
        $.get('index.php?app=sendmail');

        alert(rzt.msg);
    }
}

function trigger_uploader(){
    // 打开商品图片上传器
    $('#open_uploader').unbind('click');
    $('#open_uploader').click(function(){
        if($('#uploader').css('opacity') == 0){
            $('#uploader').css('opacity', 100);
            $(this).find('.hide').attr('class','show');
        }else{
            $('#uploader').css('opacity', 0);
            $(this).find('.show').attr('class','hide');
        }
    });

    // 打开编辑器图片上传器
    $('#open_editor_uploader').unbind('click');
    $('#open_editor_uploader').click(function(){
        if($('#editor_uploader').css('opacity') == 0){
            $('#editor_uploader').css({'opacity':100, 'z-index' : 100});
			$('*[ectype="handle_pic"]').css('z-index', 4);
        }else{
            $('#editor_uploader').css({'opacity':0, 'z-index' : 5});
			$('*[ectype="handle_pic"]').css('z-index', 999);
        }
    });
    // 打开商品远程地址上传
    $('#open_remote').unbind('click');
    $('#open_remote').click(function(){
        if($('#remote').css('display') == 'none'){
            $('#remote').show();
        }else{
            $('#remote').hide();
        }
    });
    // 打开编辑器远程地址上传
    $('#open_editor_remote').unbind('click');
    $('#open_editor_remote').click(function(){
        if($('#editor_remote').css('display') == 'none'){
            $('#editor_remote').show();
        }else{
            $('#editor_remote').hide();
        }
    });

    /* 悬停解释 */
    $('*[ecm_title]').hover(function(){
        $('*[ectype="explain_layer"]').remove();
        $(this).parent().parent().append('<div class="titles" ectype="explain_layer" style="display:none; z-index:999">' + $(this).attr('ecm_title') + '<div class="line"></div></div>');
        $('*[ectype="explain_layer"]').fadeIn();
    },
    function(){
        $('*[ectype="explain_layer"]').fadeOut();
    }
    );

    /* 图片控制 */
    var handle_pic, handler, drop, cover, insert;

    $('*[ectype="handle_pic"]').find('img:first').hover(function(){
		$('#editor_uploader').css('opacity', 0);
		$('*[ectype="handle_pic"]').css('z-index', 999);
        $('*[ectype="explain_layer"]').remove();
        handle_pic = $(this).parents('*[ectype="handle_pic"]');
        handler = handle_pic.find('*[ectype="handler"]');
        var parents = handler.parents();
        handler.show();
        handler.hover(function(){
            $(this).show();
            set_zindex(parents, "999");
        },
        function(){
            $(this).hide();
            //set_zindex(parents, "0");
        });
        set_zindex(parents, '999');

        cover = handler.find('*[ectype="set_cover"]');
        cover.unbind('click');
        cover.click(function(){
            set_cover(handle_pic.attr("file_id"));
        });

        drop = handler.find('*[ectype="drop_image"]');
        drop.unbind('click');
        drop.click(function(){
            drop_image(handle_pic.attr("file_id"));
        });
		
		// use Kindeditor don't need this
        //insert = handler.find('*[ectype="insert_editor"]');
        //insert.unbind('click');
        //insert.click(function(){
            //insert_editor(handle_pic.attr("file_name"),handle_pic.attr("file_path"));
            //return false; use Kindeditor don't return false here
        //});
    },
    function(){
        handler.hide();
        var parents = handler.parents();
        set_zindex(parents, '0');
    });

    //短消息代码说明
    $('#msg_instrunction').toggle(function(){
        $(this).next('div').fadeIn("slow")
    },function(){
        $(this).next('div').fadeOut("slow");
    });
}

// 复制到剪贴板
function copyToClipboard(txt) {
    if(window.clipboardData) {
        window.clipboardData.clearData();
        window.clipboardData.setData("Text", txt);
    } else if(navigator.userAgent.indexOf("Opera") != -1) {
        window.location = txt;
    } else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        } catch (e) {
            return false;
        }
    var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
    if (!clip)
        return false;
    var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
    if (!trans)
        return false;
    trans.addDataFlavor('text/unicode');
    var str = new Object();
    var len = new Object();
    var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
    var copytext = txt;
    str.data = copytext;
    trans.setTransferData("text/unicode",str,copytext.length*2);
    var clipid = Components.interfaces.nsIClipboard;
    if (!clip)
        return false;
    clip.setData(trans,null,clipid.kGlobalClipboard);
    }
}
function makesmall(obj,w,h){
    srcImage=obj;
    var srcW=srcImage.width;
    var srcH=srcImage.height;
    if (srcW==0)
    {
        obj.src=srcImage.src;
        srcImage.src=obj.src;
        var srcW=srcImage.width;
        var srcH=srcImage.height;
    }
    if (srcH==0)
    {
        obj.src=srcImage.src;
        srcImage.src=obj.src;
        var srcW=srcImage.width;
        var srcH=srcImage.height;
    }

    if(srcW>srcH){
        if(srcW>w){
            obj.width=newW=w;
            obj.height=newH=(w/srcW)*srcH;
        }else{
            obj.width=newW=srcW;
            obj.height=newH=srcH;
        }
    }else{
        if(srcH>h){
            obj.height=newH=h;
            obj.width=newW=(h/srcH)*srcW;
        }else{
            obj.width=newW=srcW;
            obj.height=newH=srcH;
        }
    }
    if(newW>w){
        obj.width=w;
        obj.height=newH*(w/newW);
    }else if(newH>h){
        obj.height=h;
        obj.width=newW*(h/newH);
    }
}

function getCheckItemIds()
{
	/* 是否有选择 */
	if($('.checkitem:checked').length == 0){
		return false;
	}
	 /* 运行presubmit */
	if($(this).attr('presubmit')){
		if(!eval($(this).attr('presubmit'))){
			return false;
		}
	}
	var items = '';
	$('.checkitem:checked').each(function(){
		items += this.value + ',';
	});
	items = items.substr(0, (items.length - 1));
		
	return items;
}