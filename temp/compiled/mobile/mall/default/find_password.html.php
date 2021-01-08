<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
	$('body').on('touchend click', '.masker', function(){
		DialogManager.close("find_password");
	});
	
	$('form').submit(function(){
		var codeType = $.trim($('input[name="codeType"]').val());
		var code     = $.trim($('input[name="code"]').val());
		var user_name = $.trim($('input[name="user_name"]').val());
		if(user_name == '' || user_name == undefined) {
			layer.open({content:'请输入会员账号或手机号码'});
			return false;
		}
		else {
   			ajax_form('find_password', '验证码', REAL_SITE_URL + '/index.php?app=gselector&act=captcha&dialog=1&title=验证码&id=find_password&user_name='+user_name, '300', 'simple-blue', '0.5', 'bottom');
			return false;
		}
	});
	
});
function gs_callback(id, codeType, code)
{
	$('input[name="codeType"]').val(codeType);
	$('input[name="code"]').val(code);
	DialogManager.close(id);
	ajaxRequest($('form').attr('method'), window.location.href, $('form'), '', null);
}
</script>
<div id="main" class="w-full">
  <div id="page-find-password" class="w-full page-auth mb20">
    <form  method="POST" class="fun-form-style">
      <div class="form pb20">
        <dl>
          <dd class="webkit-box"> <span>登录账号</span>
            <input type="text" class="input clearInput flex1" name="user_name" value="" oninput="javascript:clearInput(this)" placeholder="请输入会员账号或手机号码" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <!--
        <dl>
          <dd class="webkit-box captcha"> <span>验证码</span>
            <input type="text" name="captcha" class="input clearInput flex1" id="captcha1" oninput="javascript:clearInput(this)" placeholder="请输入验证码" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> <img id="captcha" src="<?php echo url('app=captcha&$random_number='); ?>" onclick="javascript:change_captcha($('#captcha'));" /> </dd>
        </dl>-->
        <div class="extra mt10">
          <p>
            <input type="hidden" name="codeType" value="" />
            <input type="hidden" name="code" value="" />
            <input type="submit" value="提交" class="btn-alipay" title="找回密码" />
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>