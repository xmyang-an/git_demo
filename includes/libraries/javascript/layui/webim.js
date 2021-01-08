$(function(){
	var TITLE = "在线客服";
	var IM_VISITOR = {};
	var host = window.location.hostname;
	//$.ajaxSettings.async = false;
	$.ajax({ url: "index.php?app=webim&act=getUser", dataType: "json", async:false, success: function(data){
		if(data.done) {
			IM_VISITOR = data.retval;
			TITLE = IM_VISITOR.username;
			//console.log(IM_VISITOR);
		}
	}});
	
	layui.use(['layim', 'layer', 'jquery'], function(layim) {
		var layer = layui.layer,
		$ = layui.jquery,
		socket = new WebSocket('ws://'+host+':8282');
		
		// 要在onopen后执行，要不然火狐会报：InvalidStateError: An attempt was made to use an object that is not, or is no longer, usable
		socket.onopen = function()
		{
			//alert('onopen');
			socket.send('socket open');
  		
		  	//基础配置
		  	layim.config({
		
				//初始化接口
				init: {
				  url: "index.php?app=webim&act=getList"
				  //url : ""
				  ,
				  data: {}
				}
				,
				uploadImage: {
				  url: "index.php?app=webim&act=uploadImage" //（返回的数据格式见下文）
				  ,
				  type: '' //默认post
				}
			
				/*,
				uploadFile: {
				  url: "index.php?app=webim&act=uploadFile" //（返回的数据格式见下文）
				  ,
				  type: '' //默认post
				}
				*/
			
				,brief: false //是否简约模式（默认false，如果只用到在线客服，且不想显示主面板，可以设置 true）
				,title: TITLE //主面板最小化后显示的名称
				,min: true //用于设定主面板是否在页面打开时，始终最小化展现。默认false，即记录上次展开状态。
				,isgroup: false //是否开启群组
				,chatLog: "index.php?app=webim&act=getLog" //聊天记录地址
				//,find: 'index.php?app=webim&act=find' //查找好友/群的地址（如果未填则不显示）
				,copyright: true //是否授权
		  });
		  
		  //监听收到的聊天消息
		  function chatMessage(res) {
			//alert('chat');
			//console.log(res);
			layim.getMessage(res);
		
		  };
			  
		  $('.J_StartLayim').on('click', function(){
			  
			  $.ajax({url: "index.php?app=webim&act=getUser", dataType: "json", async:false, data: {toid: $(this).attr('data-toid')}, success: function(data){
				  if(data.done) {
					//跟客服创造一个临时会话
					layim.chat(data.retval);
					//layim.setChatMin();
					//layer.msg('也就是说，此人可以不在好友面板里');
				  } 
				  else {
					  layer.msg(data.msg);
					  return;
				  }
			  }});
		  });
		  
		  //layim建立就绪
		  layim.on('ready', function(res) {
		
			//捕捉socket端发来的事件
			socket.onmessage = function(event) {
		
			  var e = JSON.parse(event.data);
				
			  switch (e.type) {
		
				//好友上线 添加好友
			  case 'addList':
				addList(e);
				break;
		
				//用户上线 把在线用户 加到好友列表
			  case 'regUser':
				regUser(e);
				break;
		
				//用户离线移除好哟
			  case 'out':
				out(e);
				break;
		
				//处理聊天消息 
			  case 'getMessage':
				chatMessage(e.content);
				break;
		
				//用户不在线
			  case 'notLine':
				notLine();
				break;
			  case 'sayDisabled':
				sayDisabled();
				break;
		
			  case 'js':
				console.log(e.cls);
				break;
		
			   default:
				console.log(e);
				
			  }
			}
		
			//监听发送消息
			layim.on('sendMessage', function(data) {
				
			   // 先检查用户是否被禁言（影响些效率，暂时保留。不过好处在于禁言内容不会插入数据库）
			   //$.ajax({url: "index.php?app=webim&act=checkUserForbid", dataType: "json", async:false, data: {uid: data.mine.id}, success: function(res){
				  //if(res === 1) {
					//sayDisabled();
				 // }
				  //else
				  //{
					  // 保存会话到本地数据库（解析表情，图片需要）
					  data['mine']['formatContent'] = $.trim(layim.content(data.mine.content));
					  console.log(data);
					  
					  //发送消息Socket服务
					  socket.send(JSON.stringify({
						type: 'chatMessage',
						content: data
					  }));
				 //}
			  //}});  
		
			});
			
			// 用户上线，注册事件
			if(!$.isEmptyObject(IM_VISITOR)) {
				//console.log(IM_VISITOR);
				setTimeout(function() {
					socket.send(JSON.stringify({type: 'reg', content: IM_VISITOR}));
				}, 0);
			}
			
			// 监听在线状态切换
			layim.on('online', function(status){
				//console.log(status); //获得online或者hide
		  
				//此时，你就可以通过Ajax将这个状态值记录到数据库中了。
				//服务端接口需自写。
			});
		  });
	 
		};
		
	 	 //离线反馈
	  	function notLine() {
	
			layer.msg('对方不在线');
			//  置灰好友
			//$('#layim-'+type+''+id).addClass('layim-list-gray');
	  	}
	  
	  	function sayDisabled()
	  	{
		  	layer.msg('对不起，您已经被禁言');
		  	//layim.setChatMin();
	 	 }
	
	 	 function addList(e) {
	
			//如果有用户突然关机，或同一账号 两处登陆，系统就会统计有误，查看实际连接数
			//console.log(e.cls);
			layim.addList(e.content);
	  	}
	
	  	function regUser(res) {
		  
		 	$.each(res.uuser, function(k, v){
				layim.addList(v);
			});
	  	}
	
	  	function out(res) {
	
			//console.log(res.cls);
		
			layim.removeList({
		  	type: 'friend' //或者group
		 	,id: res.id //好友或者群组ID
			});
	  	}
	
	});
});