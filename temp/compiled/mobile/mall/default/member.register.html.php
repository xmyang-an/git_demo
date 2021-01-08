<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
	$('#send_phonecode').click(function(){
		var phone_mob = $.trim($("input[name='phone_mob']").val());
		if(phone_mob=='' || !is_mobile(phone_mob)){
			layer.open({content: "请输入正确的手机号码", className:'layer-popup', time: 3});
			return;
		}
		send_phonecode($(this), {from: "register", phone_mob: phone_mob}, 120);
	});
});
</script>
<div id="main">
  <div class="page-actions">
  	<a href="<?php echo url('app=member&act=login'); ?>">登录</a>
  </div>
  <div id="page-register" class="page-auth page-body">
    <form method="post" class="fun-form-style">
      <div class="form pb20">
      <dl>
        <dd class="webkit-box"> <span>用<s style="margin:0 7px">户</s>名</span>
          <input type="text" id="user_name" name="user_name" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请设置用户名"/>
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
      </dl>
      <dl>
        <dd class="webkit-box"> <span>登录密码</span>
          <input type="password" id="password" name="password" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请设置登录密码"/>
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
      </dl>
      <dl>
        <dd class="webkit-box"> <span>重复密码</span>
          <input type="password" name="password_confirm" id="password_confirm" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请再次输入密码"/>
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
      </dl>
      
      <dl class="mt10">
        <dd class="webkit-box"> <span>手机号码</span>
          <input type="text" name="phone_mob" id="phone_mob" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请填写手机号"/>
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
      </dl>
      
      <?php if ($this->_var['phone_captcha']): ?>
      <dl>
        <dd class="webkit-box captcha captchaCode"> <span>手机短信</span>
          <input type="text" name="check_code" class="input clearInput flex1" id="captcha1" oninput="javascript:clearInput(this)" placeholder="请填写验证码" />
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i>
          <input id="send_phonecode" type="button" class="btn-captcha"  value="获取验证码" />
        </dd>
      </dl>
      <?php endif; ?>
      
      
      <dl class="hidden">
        <dd class="webkit-box"> <span>电子邮箱</span>
          <input type="text" name="email" id="email" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="请填写电子邮件"/>
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
      </dl> 
      
      <?php if ($this->_var['captcha']): ?>
      <dl>
        <dd class="webkit-box captcha clearfix"> <span>验<s style="margin:0 7px">证</s>码</span>
          <input type="text" name="captcha" class="input clearInput flex1" id="captcha1" oninput="javascript:clearInput(this)" placeholder="请输入验证码" />
          <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> <img id="captcha" src="<?php echo url('app=captcha&$random_number='); ?>" onclick="javascript:change_captcha($('#captcha'));" /> </dd>
      </dl>
      <?php endif; ?>
      <div class="extra mt10">
        <input type="hidden" class="J_AjaxFormRetUrl" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>"  />
        <input type="submit" class="J_AjaxFormSubmit btn-alipay"  value="免费注册" />
        <p class="center mt20 gray fs12">
          <input id="clause" type="checkbox"  name="agree" value="1" checked="checked" class="hidden"  />
          点击"免费注册"表示您同意<a class="fs12" style="color:#07a5ff" href="<?php echo url('app=article&act=system&code=eula'); ?>">《用户服务协议》</a></p>
      </div>
    </form>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?> 