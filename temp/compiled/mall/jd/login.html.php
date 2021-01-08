<?php echo $this->fetch('top.html'); ?> 
<script type="text/javascript">
$(function(){
    $('#login_form').validate({
        errorPlacement: function(error, element){
           var error_td = element.parent('dd');
            error_td.find('label').hide();
			error_td.find('i').hide();
            error_td.append(error);
        },
        success       : function(label){
            label.siblings('i').show().addClass('ok');
            label.remove();
        },
        onkeyup : false,
        rules : {
            user_name : {
                required : true
            },
            password : {
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
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名'
            },
            password  : {
                required : '请填写登录密码'
            },
            captcha : {
                required : '输入验证码',
                remote   : '验证码错误'
            }
        }
    });
	$('input[name="user_name"], input[name="password"], input[name="captcha"]').click(function(){
		$(this).parent().find('label').hide();
	});
	
});
</script>
<style>
#footer{background:#fff;}
#footer, #footer a, #footer .foot-group .footnav .copy, #footer .foot-group .footnav .copy a{color:#bbb;}
#footer .foot-group .footnav{margin-left:0;margin-top:0;}
#footer .foot-group .footnav ul{width:454px; margin:0 auto;}
#footer a:hover{color:#f60; text-decoration:none;}
.w{width:1000px;}
#site-nav{display:none}
.page-auth .auth-body{background: #FFF3B5}
</style>
<div id="main" class="w-full">
  <div id="page-login" class="w-full page-auth mt20 mb20">
    <div class="w-full auth-header pb10 mb10">
      <div class="w logo mb10 clearfix"> <a class="float-left" href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a> <span>欢迎登陆</span></div>
    </div>
    <div class="w-full auth-body">
    <div class="w clearfix">
      <div class="col-main">
        <div class="login-edit-field" area="login_left" widget_type="area"> 
          <?php $this->display_widgets(array('page'=>'login','area'=>'login_left')); ?>
        </div>
      </div>
      <div class="col-sub">
        <div class="form">
          <div class="title">用户登录 <a class="register" href="<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>">立即注册</a></div>
          <div class="content">
            <form method="post" id="login_form">
              <dl class="clearfix">
                <!--<dt>用户名</dt>-->
                <dd>
                  <input class="input" type="text" name="user_name"  id="user_name" title="请填写您的用户名" placeholder="用户名 / 手机号 / 邮箱" />
                  <i class="i-name"></i> </dd>
              </dl>
              <dl class="clearfix">
                <!--<dt>密&nbsp;&nbsp;&nbsp;码</dt>-->
                <dd>
                  <input class="input" type="password" name="password"  id="password" title="请填写您的登录密码" placeholder="密&nbsp;&nbsp;&nbsp;码" />
                  <i class="i-psw"></i> </dd>
              </dl>
			  <?php if ($this->_var['captcha']): ?>
              <dl class="clearfix">
                <!--<dt>验证码</dt>-->
                <dd class="captcha clearfix">
                  <input type="text" class="input float-left" name="captcha" id="captcha1" title="请输入验证码" placeholder="验证码" />
                  <a href="javascript:change_captcha($('#captcha'));" class="float-left"><img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" class="float-left" /></a> <i class="i-psw i-captcha"></i></dd>
              </dl>
              <?php endif; ?>
              <dl class="clearfix">
              	<dd class="clearfix" style="width:334px;">
              		<h3 class="float-left gray mt5">七日内免登录</h3>
              		<p class="float-right">
                    	<label class="switch-checkbox-radio inline-block box-align-center J_SwtcherInput" for="switcher"><span class="switcher-style block"></span></label>
          				<input name="AutoLogin" class="autoLogin hidden" id="switcher" value="1" type="checkbox">
					</p>
				</dd>
              </dl>
              <dl class="clearfix">
                <!--<dt>&nbsp;</dt>-->
                <dd class="clearfix">
                  <input type="submit" class="login-submit" value="登录" title="登录" />
                  <input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                </dd>
              </dl>
              <div class="partner-login">
                <h3>无需注册，选择以下方式登录 <a href="<?php echo url('app=find_password'); ?>" class="find-password">忘记密码？</a></h3>
                <p> <a href="<?php echo url('app=qqconnect&act=login'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/qq_n.png'; ?>" /> QQ</a> &nbsp;&nbsp; <a href="<?php echo url('app=alipayconnect&act=login'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/alipay_n.png'; ?>" /> 支付宝</a> &nbsp;&nbsp; <a href="<?php echo url('app=xwbconnect&act=login'); ?>" target="_blank"><img  src="<?php echo $this->res_base . "/" . 'images/xwb_n.png'; ?>" /> 新浪微博</a> &nbsp;&nbsp; <a href="<?php echo url('app=weixinconnect&act=login'); ?>" target="_blank"><img src="<?php echo $this->res_base . "/" . 'images/wx_n.png'; ?>" /> 微信登录</a> &nbsp;&nbsp; </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?>