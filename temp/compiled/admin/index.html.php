<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge charset=<?php echo $this->_var['charset']; ?>">
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $this->_var['charset']; ?>" />
<title>商城后台管理中心</title>
<link href="templates/style/admin.css" rel="stylesheet" type="text/css" />
<link href="templates/style/font/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="//at.alicdn.com/t/font_635041_8akn9oixr0j7zaor.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery-1.11.1.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layer/layer.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layer/extend/layer.ext.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'base.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
var menu = <?php echo $this->_var['menu_json']; ?>;
</script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/index.js'; ?>" charset="utf-8"></script>
</head>
<body style="background:#16B8D8">
<div class="back_nav">
  <div class="back_nav_list"> 
    <?php $_from = $this->_var['back_nav']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['menu']):
?>
    <dl>
      <dt><?php echo $this->_var['menu']['text']; ?></dt>
      <?php $_from = $this->_var['menu']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sub_key', 'sub_menu');if (count($_from)):
    foreach ($_from AS $this->_var['sub_key'] => $this->_var['sub_menu']):
?>
      <dd><a href="javascript:;" onclick="openItem('<?php echo $this->_var['sub_key']; ?>','<?php echo $this->_var['key']; ?>');"><?php echo $this->_var['sub_menu']['text']; ?></a></dd>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </dl>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
  </div>
</div>
<div class="admincp-header" id="head" style="width:100%;box-shadow: 3px 3px 5px #000;">
  <div class="admincp-name">
  	<h1 style=" font-weight:normal; font-size:16px; position:relative;">壹点网 <span style="font-size:12px;"></span></h1>
    <p style="color:#fff;">后台管理中心</p>
  </div>
  <div class="module-menu">
    <ul class="row clearfix" id="nav">
    </ul>
  </div>
  <div class="admincp-header-r">
    <div class="manager">
      您好，<?php echo $this->_var['visitor']['user_name']; ?>
    </div>
    <ul class="operate row">
      <li><a href="javascript:;" class="refresh show-option" id="iframe_refresh"  title="刷新页面">&nbsp;</a></li>
      <li><a href="javascript:;" class="update-cach show-option" id="clear_cache"  title="更新缓存">&nbsp;</a></li>
      <li><a class="sitemap show-option" id="back_btn" href="javascript:void(0);" title="查看全部管理菜单"></a></li>
      <li><a class="homepage show-option" target="_blank" href="<?php echo $this->_var['site_url']; ?>/index.php" title="新窗口打开商城首页">&nbsp;</a></li>
      <li><a class="login-out show-option" href="index.php?act=logout" title="安全退出管理中心">&nbsp;</a></li>
    </ul>
  </div>
  <div class="clear"></div>
</div>
<div class="admincp-container">
  <div class="admincp-container-left" id="left">
    <div class="top-border"></div>
    <div id="leftMenus">
      <dl id="submenu">
        <dt><i class="iconfont icon-liebiao2"></i><a class="ico1" id="submenuTitle" href="javascript:;"></a></dt>
      </dl>
      <dl id="history" class="history">
        <dt><i class="iconfont icon-shenpijilu"></i><a class="ico2" id="historyText" href="#">操作历史</a> </dt>
      </dl>
    </div>
    <div class="about" title="about MiMall"><a href="#" target="_blank">&copy; 技术支持 MiMall.NET</a></div>
  </div>
  <div class="admincp-container-right" id="right">
    <div class="top-border"></div>
    <iframe frameborder="0" style="display:none;" width="100%" id="workspace"></iframe>
  </div>
</div>
</body>
</html>
