<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#user_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('right').text('OK!');
        },
        onkeyup    : false,
        rules : {
            user_name : {
                required : true,
                rangelength: [3,15],
                remote   : {
                    url :'index.php?app=user&act=check_user',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#user_name').val();
                        },
                        id : '<?php echo $this->_var['user']['user_id']; ?>'
                    }
                }
            },
            password: {
                <?php if ($_GET['act'] == 'add'): ?>
                required : true,
                <?php endif; ?>
                maxlength: 20,
                minlength: 6
            },
            <?php if (! $this->_var['set_avatar']): ?>
            /* jquery.validate 版本冲突，暂时屏蔽
			portrait : {
                 accept:'gif|jpe?g|png'
            },*/
            <?php endif; ?>
			email   : {
                required : true,
                email : true
            }
        },
        messages : {
            user_name : {
                required : '会员名称不能为空',
                rangelength: '用户名的长度应在3-15个字符之间',
                remote   : '该会员名已经存在了，请您换一个'
            },
            password : {
                <?php if ($_GET['act'] == 'add'): ?>
                required : '密码不能为空',
                <?php endif; ?>
                maxlength: '密码长度应在6-20个字符之间',
                minlength: '密码长度应在6-20个字符之间'
            },
            
            <?php if (! $this->_var['set_avatar']): ?>
            /*portrait : {
                 accept: '支持格式gif,jpg,jpeg,png'
            },*/
            <?php endif; ?>
			email  : {
                required : '电子邮箱不能为空',
                email   : '请您填写有效的电子邮箱'
            }
        }
    });
});
</script>
<div id="rightTop">
  <p>会员管理</p>
  <ul class="subnav">
    <li><a class="btn1" href="index.php?app=user">管理</a></li>
    <li>
      <span><?php if ($this->_var['user']['user_id']): ?>编辑<?php else: ?>新增<?php endif; ?></span>
    </li>
  </ul>
</div>
<div class="info">
  <form method="post" enctype="multipart/form-data" id="user_form">
    <table class="infoTable">
      <tr>
        <th class="paddingT15"> 会员名:</th>
        <td class="paddingT15 wordSpacing5"><?php if ($this->_var['user']['user_id']): ?>
          <?php echo htmlspecialchars($this->_var['user']['user_name']); ?>
          <?php else: ?>
          <input class="infoTableInput2" id="user_name" type="text" name="user_name" value="<?php echo htmlspecialchars($this->_var['user']['user_name']); ?>" />
          <label class="field_notice">会员名</label>
          <?php endif; ?>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> 密码:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="password" type="text" id="password" />
          <?php if ($this->_var['user']['user_id']): ?>
          <span class="grey">留空表示不修改密码</span>
          <?php endif; ?>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> 电子邮箱:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="email" type="text" id="email" value="<?php echo htmlspecialchars($this->_var['user']['email']); ?>" />
            <label class="field_notice">电子邮箱</label>        </td>
      </tr>
	  <tr>
        <th class="paddingT15"> 推荐人:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="refername" type="text" id="refername" value="<?php echo htmlspecialchars($this->_var['user']['refername']); ?>" />     </td>
      </tr>
      <tr>
        <th class="paddingT15"> 手机号码:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="phone_mob" type="text" id="phone_mob" value="<?php echo htmlspecialchars($this->_var['user']['phone_mob']); ?>" />
            <label class="field_notice">手机号码</label>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> 真实姓名:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="real_name" type="text" id="real_name" value="<?php echo htmlspecialchars($this->_var['user']['real_name']); ?>" />        </td>
      </tr>
      <tr>
        <th class="paddingT15"> 性别:</th>
        <td class="paddingT15 wordSpacing5"><p>
            <label>
            <input name="gender" type="radio" value="0" <?php if ($this->_var['user']['gender'] == 0): ?>checked="checked"<?php endif; ?> />
            保密</label>
            <label>
            <input type="radio" name="gender" value="1" <?php if ($this->_var['user']['gender'] == 1): ?>checked="checked"<?php endif; ?> />
            男</label>
            <label>
            <input type="radio" name="gender" value="2" <?php if ($this->_var['user']['gender'] == 2): ?>checked="checked"<?php endif; ?> />
            女</label>
          </p></td>
      </tr>
      <tr>
        <th class="paddingT15"> QQ:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="im_qq" type="text" id="im_qq" value="<?php echo htmlspecialchars($this->_var['user']['im_qq']); ?>" />        </td>
      </tr>
	  <tr>
        <th class="paddingT15"> 旺旺:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="im_aliww" type="text" id="im_aliww" value="<?php echo htmlspecialchars($this->_var['user']['im_aliww']); ?>" />        </td>
      </tr>
	  <tr>
        <th class="paddingT15"> 锁定:</th>
        <td class="paddingT15">
          <span class="onoff">
          	<label class="cb-enable <?php if ($this->_var['user']['locked']): ?>selected<?php endif; ?>">是</label>
          	<label class="cb-disable <?php if (! $this->_var['user']['locked']): ?>selected<?php endif; ?>">否</label>
            <input name="locked" value="1" type="radio" <?php if ($this->_var['user']['locked']): ?>checked<?php endif; ?>>
            <input name="locked" value="0" type="radio" <?php if (! $this->_var['user']['locked']): ?>checked<?php endif; ?>>
          </span>
          <span class="grey notice"></span>      
          </td>
      </tr>
      <tr>
        <th class="paddingT15"> 禁用IM:</th>
        <td class="paddingT15 wordSpacing5">
			<label><input name="imforbid" type="radio" id="imforbid" value="0" <?php if (! $this->_var['user']['imforbid']): ?> checked="checked" <?php endif; ?> /> 否</label>
			<label><input name="imforbid" type="radio" id="imforbid" value="1" <?php if ($this->_var['user']['imforbid']): ?> checked="checked"<?php endif; ?> /> 是</label>
		</td>
      </tr>
     <?php if (! $this->_var['set_avatar']): ?>
      <tr>
        <th class="paddingT15">头像:</th>
        <td class="paddingT15 wordSpacing5">
          <div class="input-file-show">
                <span class="show"><a href="javascript:;" class="show_image"><i class="fa fa-image"></i></a></span>
                <span class="type-file-box">
                    <input type="text" name="textfield" class="type-file-text" />
                    <input type="button" name="button" value="选择上传..." class="type-file-button" />
                    <input class="type-file-file" name="portrait" id="portrait" type="file" size="30" hidefocus="true">
                    <label class="field_notice">支持格式gif,jpg,jpeg,png</label>
                </span>
                <?php if ($this->_var['user']['portrait']): ?>
              	<div class="show_img"><img src="../<?php echo $this->_var['user']['portrait']; ?>" alt="" width="100" height="100" /></div>
              	<?php endif; ?>
            </div>
        </td>
      </tr>
     <?php else: ?>
        <?php if ($_GET['act'] == 'edit'): ?>
      <tr>
        <th class="paddingT15">头像:</th>
        <td class="paddingT15 wordSpacing5"><?php echo $this->_var['set_avatar']; ?></td>
      </tr>
        <?php endif; ?>
     <?php endif; ?>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn" type="reset" name="Reset" value="重置" />        </td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>