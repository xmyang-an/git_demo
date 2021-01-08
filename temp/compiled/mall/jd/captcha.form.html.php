<script type="text/javascript">
//<!CDATA[
$(function(){
	$('.J_Tab li').click(function(){
		$('.J_CodeType').val($(this).attr('codeType'));
		$(this).parent().find('li').removeClass('active');
		$('.J_TabForm').find('.each').hide();		
		$(this).addClass('active');
		$('.J_TabForm').find('.each:eq('+$(this).parent().children('li').index(this)+')').show();		
	});
	
	$('#send_phonecode').click(function(){
		$(this).attr("disabled", true);
		send_phonecode($(this), {from: "<?php echo $this->_var['captcha']['from']; ?>", user_id: "<?php echo $this->_var['user']['user_id']; ?>"}, 120);
	});
	$('#send_emailcode').click(function(){
		$(this).attr("disabled", true);
		send_emailcode($(this), {from: "<?php echo $this->_var['captcha']['from']; ?>", user_id: "<?php echo $this->_var['user']['user_id']; ?>"}, 120);
	});
	
	$('#gs_submit').click(function(){
		var codeType = $('.J_CodeType').val();
		gs_callback('<?php echo $_GET['id']; ?>', codeType, $('input[name="'+codeType+'_code"]').val());
	});
});
//]]>
</script>
<div class="captcha-form">
	<ul class="tab J_Tab clearfix hidden">
		<li class="active" codeType="phone">手机短信验证</li>
		<li codeType="email">邮箱验证</li>
	</ul>
	<div class="eject_con">
		<div class="add">
			<div id="warning"></div>
			<div class="captcha-fields J_TabForm">
				<form method="post" action="<?php echo $this->_var['action_url']; ?>" id="captcah_form" target="iframe_post">
					<ul class="each">
						<li class="clearfix">
							<p class="first">您的手机号</p>
							<p>
								<input type="hidden" name="phone_mob" value="<?php echo $this->_var['user']['phonb_mob']; ?>" />
								<?php echo $this->_var['user']['phone_mob']; ?></p>
						</li>
						<li class="clearfix">
							<p class="first">手机验证码 </p>
							<p>
								<input type="text" name="phone_code" class="text width_short" />
								<input type="button" value="获取验证码" id="send_phonecode"/>
							</p>
						</li>
					</ul>
					<ul class="each hidden">
						<li class="clearfix">
							<p class="first">您的邮箱</p>
							<p>
								<input type="hidden" name="email" value="<?php echo $this->_var['user']['email']; ?>" />
								<?php echo $this->_var['user']['email']; ?></p>
						</li>
						<li class="clearfix">
							<p class="first">邮箱验证码</p>
							<p>
								<input type="text" name="email_code" class="text width_short" />
								<input type="button" value="获取验证码" id="send_emailcode"/>
							</p>
						</li>
					</ul>
					<div class="mt10 clearfix">
						<input type="hidden" value="phone" class="J_CodeType" />
						<input type="button" value="提交" id="gs_submit" class="btn-submit" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
