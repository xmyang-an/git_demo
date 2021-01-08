<?php echo $this->fetch('top.html'); ?> 
<script type="text/javascript">
$(function(){
    $('#register_form').validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
			error_td.find('i').removeClass('ok');
            error_td.find('label').hide();
            error_td.append(error);
        },
        success       : function(label){
			label.siblings('i').addClass('ok');
            label.remove();
        },
        onkeyup: false,
        rules : {
            user_name : {
                required : true,
                rangelength: [3,15,'<?php echo $this->_var['charset']; ?>'],
                remote   : {
                    url :'index.php?app=member&act=check_user&ajax=1',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#user_name').val();
                        }
                    }
                }
            },
            password : {
                required : true,
                minlength: 6
            },
            password_confirm : {
                required : true,
                equalTo  : '#password'
            },
            email : {
                required : true,
                email    : true,
				remote   : {
                    url :'index.php?app=member&act=check_email_info&ajax=1',
                    type:'get',
                    data:{
                        email : function(){
                            return $('#email').val();
                        }
                    }
                }
            },
			phone_mob : {
                required : true,
				number : true,
                minlength: 11,
				maxlength: 11,
				remote   : {
                    url :'index.php?app=member&act=check_phone_mob&ajax=1',
                    type:'get',
                    data:{
                        phone_mob : function(){
                            return $('#phone_mob').val();
                        }
                    }
                }
            },
			check_code:{
				required : true	
			},
            captcha : {
                required : true,
                remote   : {
                    url : 'index.php?app=captcha&act=check_captcha',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#captcha1').val();
                        }
                    }
                }
            },
            agree : {
                required : true
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名',
                rangelength: '用户名必须在3-15个字符之间',
                remote   : '您提供的用户名已存在'
            },
            password  : {
                required : '请填写登录密码',
                minlength: '密码长度应在6-20个字符之间'
            },
            password_confirm : {
                required : '您必须再次确认您的密码',
                equalTo  : '两次输入的密码不一致'
            },
            email : {
                required : '您必须提供您的电子邮箱',
                email    : '这不是一个有效的电子邮箱',
				remote   : '此邮箱已经被注册'
            },
			phone_mob : {
				required : '手机号码不能为空',
				number : '请输入数字',
                minlength: '手机号码错误！',
				maxlength: '手机号码错误！',
				remote   : '此手机已经被注册'
            },
			<?php if ($this->_var['phone_captcha']): ?>
			check_code : {
				required : '短信验证码不能为空',
			},
			<?php endif; ?>
            captcha : {
                required : '输入验证码',
                remote   : '验证码错误'
            },
            agree : {
                required : '请先阅读并同意商城服务协议'
            }
        }
    });

	$('#send_phonecode').click(function(){
		var phone_mob = $.trim($("input[name='phone_mob']").val());
		if(phone_mob=='' || !is_mobile(phone_mob)){
			alert("请输入正确的手机号");
			return;
		}
		send_phonecode($(this), {from: "register", phone_mob: phone_mob}, 120);
	});
});
</script>
<style>
#footer{background:#f4f4f4;}
#footer, #footer a, #footer .foot-group .footnav .copy, #footer .foot-group .footnav .copy a{color:#bbb;}
#footer .foot-group .footnav{margin-left:0;margin-top:0;}
#footer .foot-group .footnav ul{width:454px; margin:0 auto;}
#footer a:hover{color:#f60; text-decoration:none;}
.w{width:1000px;}
#site-nav{display:none}
</style>
<div id="main" class="w-full">
  <div id="page-register" class="w-full page-auth mt20 mb20">
      <div class="w-full auth-header pb10 mb10">
      <div class="w logo mb10 clearfix"> <a class="float-left" href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a> <span>欢迎注册</span><em class="float-right gray fs14 mt20">已有账号 请<a href="<?php echo url('app=member&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>">登录</a></em></div>
    </div>
      <div class="w-full auth-body">
      <div class="form w clearfix">
        <form id="register_form" method="post">
          <div class="each">
          <dl class="clearfix">
            <dt>用<span style="margin:0 7px">户</span>名</dt>
            <dd>
              <input type="text" id="user_name" class="input"  name="user_name" placeholder="3-15位字符"  />
              <i class="i-name"></i> </dd>
          </dl>
          </div>
          <dl class="clearfix">
            <dt>密<span style="margin:0 15px"></span>码</dt>
            <dd>
              <input class="input" type="password" id="password" name="password" placeholder="密码长度在6-20个字符" />
              <i class="i-psw"></i> </dd>
          </dl>
          <dl class="clearfix">
            <dt>确认密码</dt>
            <dd>
              <input class="input" type="password" id="password_confirm" name="password_confirm" placeholder="请再次输入你的密码" />
              <i class="i-psw"></i> </dd>
          </dl>
          <dl class="clearfix">
            <dt>电子邮箱</dt>
            <dd>
              <input class="input" type="text" id="email" name="email" placeholder="常用电子邮箱，用于找回密码等" />
              <i class="i-email"></i> </dd>
          </dl>
          <dl class="clearfix">
            <dt>手<span style="margin:0 7px">机</span>号</dt>
            <dd>
              <input type="text" name="phone_mob" id="phone_mob" class="input" placeholder="您的手机号，用于接收手机短信，找回密码等" />
              <i class="i-phone"></i> </dd>
          </dl>
          <?php if ($this->_var['phone_captcha']): ?>
          <dl class="clearfix">
            <dt>短信验证码</dt>
            <dd class="captchaCode clearfix">
              <input type="text" name="check_code" id="check_code" class="input float-left" />
              <input type="button" id="send_phonecode" class="float-left btn-captcha" value="获取验证码" />
            </dd>
          </dl>
          <?php endif; ?> 
          
          <?php if ($this->_var['captcha']): ?>
          <dl class="clearfix">
            <dt>验<span style="margin:0 7px">证</span>码</dt>
            <dd class="captcha clearfix">
              <input type="text" class="input float-left" name="captcha" id="captcha1" title="请输入验证码" />
              <a href="javascript:change_captcha($('#captcha'));" class="float-left mt5" style="margin-right:2px;"><img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" /></a> </dd>
          </dl>
          <?php endif; ?>
          <dl class="clearfix">
            <dt>&nbsp;</dt>
            <dd class="mall-eula">
              <input id="clause" type="checkbox" name="agree" value="1" class="agree-checkbox" checked="checked" />
              <span style="margin-right:85px;">我已阅读并同意 <a href="<?php echo url('app=article&act=system&code=eula'); ?>" target="_blank">《用户服务协议》</a></span> </dd>
          </dl>
          <dl class="clearfix">
            <dt>&nbsp;</dt>
            <dd>
              <input type="submit" name="Submit" value="立即注册" class="register-submit" title="立即注册" />
              <input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
            </dd>
          </dl>
        </form>
      </div>
      </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 