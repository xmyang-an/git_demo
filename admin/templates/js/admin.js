$(function(){
    /* 全选 */
    $('.checkall').click(function(){
        $('.checkitem').prop('checked', this.checked);
    });

    /* 批量操作按钮 */
    if($('#batchAction').length == 1){
        $('.batchButton').click(function(){
            /* 是否有选择 */
            if($('.checkitem:checked').length == 0){    //没有选择
				parent.layer.alert('没有选择项',{icon:2});
                return false;
            }
            /* 运行presubmit */
            if($(this).attr('presubmit')){
                if(!eval($(this).attr('presubmit'))){
                    return false;
                }
            }
            /* 获取选中的项 */
            var items = '';
            $('.checkitem:checked').each(function(){
                items += this.value + ',';
            });
            items = items.substr(0, (items.length - 1));
            /* 将选中的项通过GET方式提交给指定的URI */
            var uri = $(this).attr('uri');
            window.location = uri + '&' + $(this).attr('name') + '=' + items;
        });
    }
	
	$('.JBatchDel').click(function(){
             //是否有选择 
            if($('.checkitem:checked').length == 0){    //没有选择
				parent.layer.alert('没有选择项',{icon:2});
                return false;
            }
             //获取选中的项 
            var items = '';
            $('.checkitem:checked').each(function(){
                items += this.value + ',';
            });
            items = items.substr(0, (items.length - 1));
			var url = $(this).attr('uri') + '&' + $(this).attr('name') + '=' + items;
            parent.layer.confirm('删除后将不能恢复，确认删除吗？',{icon: 3, title:'提示'},function(index){
				$.ajax({
					type: "GET",
					dataType: "json",
					url: url,
					async : false,
					success: function(data){
						if (data.done){
							window.location.reload();
							parent.layer.msg('删除成功',{icon: 1});
						} else {
							parent.layer.alert(data.msg);
						}
					}
				});
				parent.layer.close(index);
			},function(index){
				parent.layer.close(index);
			});
			
      });
	
	$(".show_image").mouseover(function(){
        $(this).parent().siblings(".show_img").show();
    });
    $(".show_image").mouseout(function(){
        $(this).parent().siblings(".show_img").hide();
    });
	$(".type-file-file").change(function(){
		$(this).siblings(".type-file-text").val($(this).val());
	});
	
	//自定义radio样式
    $(".cb-enable").click(function(){
        $(this).addClass('selected');
		$(this).siblings('.cb-disable').removeClass('selected');
		$(this).siblings('input:first').attr('checked', true);
		$(this).siblings('input:first').click();
    });
    $(".cb-disable").click(function(){
		$(this).addClass('selected');
		$(this).siblings('.cb-enable').removeClass('selected');
		$(this).siblings('input:last').attr('checked', true);
		$(this).siblings('input:last').click();
    });
	
	/* 此方法可以不加，这里加这个的目的是去除在IE下点击出现的虚线框 */
	$("button").click(function () {
		if (this.focus) {
			this.blur();
		}
	})
	/* 点击返回顶部箭头的事件 */
	$("#btntop").click(function () {
		$("body,html").animate({ scrollTop: 0 }, 500); //返回顶部，用JQ的animate动画
	});
	/* 点击返回底部箭头的事件 */
	$("#btnbottom").click(function () {
		$("body,html").animate({ scrollTop: document.body.clientHeight }, 500); //返回底部，用JQ的animate动画
	});
	
	$('.J_FormSubmit').click(function(){
		var type = $(this).parents('form').attr('method').toUpperCase();
		var url =  window.location.href; 
		var fromObj = $(this).parents('form');
		ajaxFormSubmit(type,url,fromObj);
		return false;	
	});
	
	$('.J_loginFormSubmit').click(function(){
		var type = $(this).parents('form').attr('method').toUpperCase();
		var url =  window.location.href; 
		var fromObj = $(this).parents('form');
		ajaxFormSubmit1(type,url,fromObj);
		return false;	
	});
	
	// 高级搜索边栏动画
	$('#searchBarOpen').click(function(){
		$('.search-ban-s').animate({'right': '-40px'},200,function(){
			$('.search-bar').animate({'right': '0'},300);
		});
	});
	$('#searchBarClose').click(function(){
		$('.search-bar').animate({'right': '-240px'},300,function(){
			$('.search-ban-s').animate({'right': '0'},200);
		});            
	});
});

function submitConfirm(msg,obj,ajax){
	parent.layer.confirm(msg,{icon: 3, title:'提示'},function(index){
		parent.layer.close(index);
		if(ajax == 'ajax'){
			var type = obj.parents('form').attr('method').toUpperCase();
			var url =  window.location.href; 
			var fromObj = obj.parents('form');
			ajaxFormSubmit(type,url,fromObj);	
		}else{
			obj.parents('form').submit();
		}
		return false;	
	},function(index){
		parent.layer.close(index);
        return false;
	});
}

function goConfirm(msg,url,ajax){
	parent.layer.confirm(msg,{icon: 3, title:'提示'},function(index){
		if(ajax === true){
            $.ajax({
				type: "GET",
				dataType: "json",
				url: url,
				async : false,
				success: function(data){
					if(data.done) {
						parent.layer.msg(data.msg,{icon: 1,time: 1000}, function(){
							if(data.retval.ret_url) {
								window.location.href = data.retval.ret_url;
							}else{
								window.location.reload();
							}
						});
					}
				}
			});
        }else{
			if(url){
				window.location.href = url;
			}
		}
		parent.layer.close(index);
		return false;	
	},function(index){
		parent.layer.close(index);
        return false;
	});
}

function drop_confirm(msg, url){
    if(confirm(msg)){
        if(url == undefined){
            return true;
        }
        window.location = url;
    }else{
        if(url == undefined){
            return false;
        }
    }
}

function fg_delete(id,app,act,reason) {
	var id = id;
	if (typeof(id) == 'number') {
    	id = new Array(id.toString());
	} else if (typeof(id) == 'string') {
		id = new Array(id);
	}
	var act = act ? act : 'drop';
	var url = 'index.php?app='+app+'&act='+act;
	parent.layer.confirm('删除后将不能恢复，确认删除这 ' + id.length + ' 项吗？',{icon: 3, title:'提示'},function(index){
		id = id.join(',');
		if(reason === true){
			parent.layer.prompt({
				formType: 2,
				value: '',
				title: '删除原因'
			}, function(value, index, elem){
				$.ajax({
					type: "GET",
					dataType: "json",
					url: url,
					data: "id="+id+"&content="+value,
					async : false,
					success: function(data){
						if (data.done){
							parent.layer.close(index);
							$("#flexigrid").flexReload();
						} else {
							parent.layer.alert(data.msg);
							$("#flexigrid").flexReload();
						}
					}
				});
			});
		}else{
			$.ajax({
				type: "GET",
				dataType: "json",
				url: url,
				data: "id="+id,
				async : false,
				success: function(data){
					if (data.done){
						$("#flexigrid").flexReload();
					} else {
						parent.layer.alert(data.msg);
						$("#flexigrid").flexReload();
					}
				}
			});
		}
		parent.layer.close(index);
	},function(index){
		parent.layer.close(index);
	});
}

function fg_csv(ids,act) {
    var id = ids.join(',');
	var act = act ? act : 'export_csv';
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&act='+act+'&id=' + id;
}

function ajaxFormSubmit(type, url, formObj)
{
	formObj.ajaxSubmit({
		type:type,
		dataType: "json",
		url:url,
		async: false,
		beforeSubmit:function(){
			return formObj.valid();
		},
		success: function(data){
			if(data.done) {
				parent.layer.msg(data.msg,{icon: 1,time: 1000}, function(){
					if(data.retval.ret_url) {
						window.location.href = data.retval.ret_url;
					}else if(type.toUpperCase() == 'GET' || data.retval.rel){
						window.location.reload();
					}else{
						window.history.go(-1);
					}
				});
			}
			else
			{
				parent.layer.msg(data.msg);	
			}
				 
		},
		error: function(data) {
			parent.layer.msg(lang.system_busy);
		}
	});
}

//本窗口弹出layer，用户用户登录
function ajaxFormSubmit1(type, url, formObj)
{
	formObj.ajaxSubmit({
		type:type,
		dataType: "json",
		url:url,
		success: function(data){
			if(data.done) {
				layer.msg(data.msg,{icon: 1,time: 1000}, function(){
					if(data.retval.ret_url) {
						window.location.href = data.retval.ret_url;
					}else if(type.toUpperCase() == 'GET' || data.retval.rel){
						window.location.reload();
					}else{
						window.history.go(-1);
					}
				});
			}
			else
			{
				layer.msg(data.msg);	
			}
				 
		},
		error: function(data) {
			layer.msg(lang.system_busy);
		}
	});
}