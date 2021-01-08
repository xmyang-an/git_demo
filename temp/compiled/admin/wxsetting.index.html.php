<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#weixin_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('right').text('OK!');
        },
        onkeyup    : false,
        rules : {
            appid: {
                required : true
            },
            appsecret   : {
                required : true
            },
			token   : {
                required : true
            }
        },
        messages : {
            appid : {
                required : '不能为空'
            },
            appsecret : {
                required : '不能为空'
            },
			token   : {
                required : '不能为空'
            }
        }
    });
});
</script>
<div id="rightTop">
  <p>微信设置</p>
</div>
<div class="info">
  <form method="post" id="weixin_form">
    <table class="infoTable">
      <tr>
        <th class="paddingT15"> 公众账号名称:</th>
        <td class="paddingT15 wordSpacing5">
          <input class="infoTableInput" type="text" name="name" value="<?php echo htmlspecialchars($this->_var['weixin']['name']); ?>" />
          <label class="field_notice">填写公众账号名称</label>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> AppID(应用ID):</th>
        <td class="paddingT15 wordSpacing5">
          <input class="infoTableInput" id="appid" type="text" name="appid" value="<?php echo htmlspecialchars($this->_var['weixin']['appid']); ?>" />
          <label class="field_notice">AppID(应用ID)，微信公众平台获取</label>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> AppSecret(应用密钥):</th>
        <td class="paddingT15 wordSpacing5">
          <input class="infoTableInput" name="appsecret" type="text" id="appsecret" value="<?php echo htmlspecialchars($this->_var['weixin']['appsecret']); ?>"  />
          <label class="field_notice">AppSecret(应用密钥)，微信公众平台获取</label>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> URL(服务器地址):</th>
        <td class="paddingT15 wordSpacing5">
        	<input class="infoTableInput" name="url" type="text" id="url" value="<?php echo htmlspecialchars($this->_var['weixin']['url']); ?>" readonly />
            <label class="field_notice">填写在微信公众平台服务器配置</label>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> Token(令牌):</th>
        <td class="paddingT15 wordSpacing5">
        	<input class="infoTableInput" name="token" type="text" id="token" value="<?php echo htmlspecialchars($this->_var['weixin']['token']); ?>" />
            <label class="field_notice">令牌对应的是微信公众平台的token，要保持一致</label>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> 关注自动登陆:</th>
        <td class="paddingT15">
          <span class="onoff">
          	<label class="cb-enable <?php if ($this->_var['weixin']['auto_login']): ?>selected<?php endif; ?>">开启</label>
          	<label class="cb-disable <?php if (! $this->_var['weixin']['auto_login']): ?>selected<?php endif; ?>">关闭</label>
            <input name="auto_login" value="1" type="radio" <?php if ($this->_var['weixin']['auto_login']): ?>checked<?php endif; ?>>
            <input name="auto_login" value="0" type="radio" <?php if (! $this->_var['weixin']['auto_login']): ?>checked<?php endif; ?>>
          </span>
          <span class="grey notice">关注公众号后自动注册登陆</span>      
          </td>
      </tr>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn" type="reset" name="Reset" value="重置" />
        </td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>