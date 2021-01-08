<?php echo $this->fetch('header.html'); ?>
<div id="main" class="pb20">
  <div id="page-login" class="page-auth">
    <form method="POST" class="fun-form-style">
      <div class="form pb20">
      	<?php if ($_GET['type'] == 'phone'): ?>
        <dl>
          <dd class="webkit-box"> <span>手机号码</span>
            <input type="text" name="phone_mob" id="phone_mob" class="input flex1 w-full"  placeholder="手机号码" />
          </dd>
        </dl>
        <dl class="J_PhoneCaptcha">
          <dd class="captchaCode webkit-box clearfix"> <span>验<s style="margin:0 7px">证</s>码</span>
            <input type="text" name="code" id="code" class="input flex1 w-full" placeholder="短信验证码" />
            <s class="line"></s>
            <input type="button" id="send_phonecode" class="btn-captcha" value="获取验证码" />
          </dd>
        </dl>
        <?php else: ?>
        <dl>
          <dd class="webkit-box"> <span>用户名</span>
            <input type="text" id="user_name" name="user_name" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="用户名/手机号/邮箱" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl>
          <dd class="webkit-box"> <span>密<s style="margin:0 7px"></s>码</span>
            <input type="password" name="password" id="password" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请输入密码" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <?php if ($this->_var['captcha']): ?>
        <dl>
          <dd class="webkit-box captcha"> <span>验证码</span>
            <input type="text" name="captcha" class="input clearInput flex1" id="captcha1" oninput="javascript:clearInput(this)" placeholder="请输入验证码" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> <img id="captcha" src="<?php echo url('app=captcha&$random_number='); ?>" onclick="javascript:change_captcha($('#captcha'));" /> </dd>
        </dl>
        <?php endif; ?>
        <?php endif; ?>
        
        <div class="extra">
          <p style="display:none">
            <label class="switch-checkbox-radio w-full block mb20 box-align-center J_SwtcherInput checked" for="switcher"> <em class="block flex1 gray">七日内免登录</em> <span class="switcher-style block"></span> </label>
            <input name="AutoLogin" class="autoLogin hidden" id="switcher" value="1" type="checkbox" checked="checked">
          </p>
          <p>
            <input type="hidden" class="J_AjaxFormRetUrl" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
            <input type="submit" class="J_AjaxFormSubmit btn-alipay mt10" value="登录" />
          </p>
        </div>
      </div>
    </form>
    <?php if ($_GET['type'] != 'phone'): ?>
    <div class="partner pl10 pr10">
      <div class="mt webkit-box"> <ins class="vleft vline flex1"></ins><span class="fs14 gray">合作账号登录</span><ins class="vright vline flex1"></ins> </div>
      <div class="mc webkit-box"> <a href="<?php echo url('app=qqconnect&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>" class="flex1"><i class="qq psmb-icon-font">&#xe6bd;</i></a> 
        <?php if (! $this->_var['isWeixin']): ?> 
        <a href="<?php echo url('app=alipayconnect&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>" class="flex1"><i class="alipay psmb-icon-font">&#xe6bb;</i></a> 
        <?php endif; ?> 
        <?php if ($this->_var['isWeixin']): ?> 
        <a href="<?php echo url('app=weixinconnect&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>" class="flex1"><i class="weixin psmb-icon-font">&#xe6f4;</i></a> 
        <?php endif; ?> 
        <a href="<?php echo url('app=xwbconnect&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>" class="flex1"><i class="xwb psmb-icon-font">&#xe6b7;</i></a> </div>
    </div>
    <?php endif; ?>
    <div class="auth-fixed webkit-box"> <a class="fs14 center flex1" href="<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>">免费注册</a> 
    <?php if ($_GET['type'] != 'phone'): ?>
    <a class="fs14 center flex1" href="<?php echo url('app=member&act=login&type=phone&ret_url=' . $this->_var['ret_url']. ''); ?>">手机短信登录</a>
    <?php else: ?>
    <a class="fs14 center flex1" href="<?php echo url('app=member&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>">账号密码登录</a> 
    <?php endif; ?>
    <a class="fs14 center flex1" href="<?php echo url('app=find_password'); ?>" style="border-right:0;">找回密码</a> </div>
  </div>
</div>
 <?php if ($_GET['type'] == 'phone'): ?>
<script type="text/javascript">
$(function(){
	$('#send_phonecode').click(function(){
		var phone_mob = $.trim($("input[name='phone_mob']").val());
		if(phone_mob=='' || !is_mobile(phone_mob)){
			layer.open({content: "请输入正确的手机号码", className:'layer-popup', time: 3});
			return;
		}
		$(this).attr("disabled", true);
		send_phonecode($(this), {from: "login", phone_mob: phone_mob}, 120);
	});
});
</script>
<?php endif; ?>
<?php echo $this->fetch('footer.html'); ?>