<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
	<p>手机短信管理</p>
    <ul class="subnav" style="margin-left:0px;">
        <li><a class="btn1" href="index.php?app=msg">发送记录</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=user">短信用户</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=add">分配短信</a></li>
        <li><a class="btn1" href="index.php?app=msg&act=send">短信发送</a></li>
        <li><span>设置</span></li>
    </ul>
</div>
<div class="info">
	<form method="post">
  		<table class="infoTable">
            <tr>
                <th class="paddingT15">短信用户名:</th>
                <td class="paddingT15 wordSpacing5">
				<input name="msg_pid" type="text" value="<?php echo $this->_var['setting']['msg_pid']; ?>" size="20">
				</td>
            </tr>
            <tr>
                <th class="paddingT15">短信密钥:</th>
              <td class="paddingT15 wordSpacing5">
				<input name="msg_key" type="password" value="" style="width:200px;">
				<label class="field_notice">留空表示不修改（注意：短信密钥不是登录密码，请到此获取：<a href="http://sms.webchinese.com.cn/User/?action=key" target="_blank">点此获取</a>）</label>
				</td>
            </tr>
            <tr>
                <th class="paddingT15">启用时机:</th>
              <td class="paddingT15 wordSpacing5">
					<input id="msg_status1" type="checkbox" name="msg_status[register]" value="1" <?php if ($this->_var['setting']['msg_status']['register']): ?>checked<?php endif; ?>/> <label for="msg_status1">前台注册</label>
                    <input id="msg_status2" type="checkbox" name="msg_status[find_password]" value="1" <?php if ($this->_var['setting']['msg_status']['find_password']): ?>checked<?php endif; ?>/> <label for="msg_status2">找回密码</label>
				</td>
            </tr>
            <tr>
            <th></th>
            <td class="ptb20">
                <input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
                <input class="formbtn" type="reset" name="Submit2" value="重置" />
            </td>
            </tr>
      </table>	
      </form>
</div>
<?php echo $this->fetch('footer.html'); ?>