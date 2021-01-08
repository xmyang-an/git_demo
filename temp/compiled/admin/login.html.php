<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge charset=<?php echo $this->_var['charset']; ?>">
<meta name="author" content="mibao123.com" />
<meta name="generator" content="mibao123.com" />
<meta name="copyright" content="mibao123.com All Rights Reserved" />
<title>您需要登录后才能使用本功能</title>

<!--<link rel="icon" href="favicon.ico" type="image/x-icon" />-->
<link rel="stylesheet" href="templates/style/login.css">
<link rel="stylesheet" href="templates/style/supersized/css/supersized.css">

<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery-1.11.1.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layer/layer.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.form.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/admin.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
// 登陆页跳出框架
if(self != top){
	top.location = self.location;
}
$(function(){
    $('#user_name').focus();
});
</script>
<style>
.enter{
    border: 1px solid #ccc;
    background: url("templates/style/supersized/img/backgrounds/center_bg.png");
    top: 80px;
    padding: 20px;
	width:500px;margin:0 auto;
	box-shadow: 11px 14px 20px 3px #111111d4;
}
.enter1{
    border: 1px solid #ccc;
    background:#464646;
    top: 80px;
    padding: 20px;
	margin:0 auto;
}
</style>
</head>
<body>
<div class="login">
  <div class="bg">
    <div class="enter">
	<div class="enter1">
      <h1>登&nbsp;&nbsp;录</h1>
      <form method="POST" class="form-login">
        <dl>
          <!--<dt>用户名 :</dt>-->
          <dd>
            <input type="text" id="user_name" name="user_name" class="input" placeholder="用户名" />
          </dd>
        </dl>
        <dl>
          <!--<dt>密&nbsp;&nbsp;&nbsp;码 :</dt>-->
          <dd>
            <input type="password" name="password" class="input" placeholder="密&nbsp;&nbsp;&nbsp;码" />
          </dd>
        </dl>
        <?php if ($this->_var['captcha']): ?>
        <dl>
          <!--<dt>验证码 :</dt>-->
          <dd class="clearfix " style="position:relative">
            <input class="captcha input J_Captcha" type="text" name="captcha" placeholder="验证码"  />
            <img onclick="this.src='index.php?app=captcha&' + Math.round(Math.random()*10000)" style="cursor:pointer; position:absolute;bottom:11px; right:10px" src="index.php?app=captcha&<?php echo $this->_var['random_number']; ?>" /></dd>
        </dl>
        <?php endif; ?>
        <dl>
        <dd>
          <input class="J_loginFormSubmit btn-submit" type="submit" value="登&nbsp;&nbsp;录" />
        </dd>
        <dl>
        <dl>
          <dd class="clearfix"> <a href="<?php echo $this->_var['site_url']; ?>" class="back_home float-left"><i class="fa fa-home"></i>返回首页</a> <a href="<?php echo $this->_var['site_url']; ?>/index.php?app=find_password" class="find-password float-right"><i class="fa fa-question-circle"></i>忘记密码</a> </dd>
        </dl>
      </form>
    </div>
  </div>
  </div>
</div>

 

<script src="templates/style/supersized/js/supersized.3.2.7.min.js"></script> 
<script src="templates/style/supersized/js/supersized-init.js"></script>
</body>
</html>
