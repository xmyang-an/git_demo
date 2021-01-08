<!DOCTYPE html>
<html>
<head>
<base href="<?php echo $this->_var['site_url']; ?>/" />
<meta charset="<?php echo $this->_var['charset']; ?>" />
<?php if (! $this->_var['in_wxmp']): ?>
<?php echo $this->_var['page_seo']; ?>
<?php endif; ?>
<meta name="author" content="www.mibao123.com" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Expires" CONTENT="-1">
<meta http-equiv="Cache-Control" CONTENT="no-cache">
<meta http-equiv="Pragma" CONTENT="no-cache">
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/header.css'; ?>" rel="stylesheet" />
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/user.css'; ?>" rel="stylesheet"  />
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/footer.css'; ?>" rel="stylesheet" />
<?php if ($this->_var['in_wxmp']): ?>
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/fit.css'; ?>" rel="stylesheet" />
<?php endif; ?>
<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var REAL_SITE_URL = "<?php echo $this->_var['real_site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//]]>
</script>
<script type="text/javascript" src="mobile/index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/jquery.js'; ?>" charset="utf-8"></script>
<?php if ($this->_var['location']): ?>
<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=<?php echo $this->_var['baidukey']['browser']; ?>"></script><script type="text/javascript" src="https://api.map.baidu.com/library/GeoUtils/1.2/src/GeoUtils_min.js"></script>
<?php endif; ?>
<?php if ($this->_var['in_wxmp']): ?>
<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/fit.js'; ?>" charset="utf-8"></script>
<?php endif; ?>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/base.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/member.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/layer.m/layer.m.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/jquery.plugins/jquery.form.min.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/main.js'; ?>" charset="utf-8"></script>
<?php echo $this->_var['_head_tags']; ?>
</head>
<body id="page-layout-<?php echo ($_GET['app'] == '') ? 'default' : $_GET['app']; ?>-<?php echo ($_GET['act'] == '') ? 'index' : $_GET['act']; ?>">
<div id="header" class="w-full">
  <div class="bar-wrap J_BarWrap">
    <div class="top-bar <?php if (in_array ( $_GET['app'] , array ( 'goods' ) )): ?>J_BarGradient<?php endif; ?>">
      <div class="barbg"></div>
      <ul class="barbtn">
        <li class="webkit-box">
          <div class="lp"> 
            <?php if ($this->_var['index']): ?> 
            <a href="javascript:;" onclick="javascript:self.location='<?php echo url('app=category'); ?>';" class="float-left category"><i>&#xe644;</i></a> 
            <?php else: ?> 
            <a href="javascript:pageBack();" class="float-left pageback"><i>&#xe628;</i></a> 
            <?php endif; ?> 
          </div>
          <div class="mp flex1 ml10 mr10"> 
            <?php if (! in_array ( $_GET['app'] , array ( 'search' , 'default' , '' ) )): ?> 
            <span class="yahei curlocal-title"><?php echo ($_GET['keyword'] == '') ? $this->_var['curlocal_title'] : $_GET['keyword']; ?></span> 
            <?php else: ?>
            <form action="<?php echo $this->_var['real_site_url']; ?>" method="get">
              <?php $_from = $this->_var['formSearchBoxParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
              <input type="hidden" name="<?php echo $this->_var['key']; ?>" value="<?php echo $this->_var['item']; ?>" />
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              <div class="input-wraper">
                <input class="<?php if ($_GET['act'] != 'form'): ?>J_SearchInputGradient<?php endif; ?>" placeholder="<?php echo $this->_var['curlocal_title']; ?>" searchType="<?php echo $_GET['act']; ?>" value="<?php echo $_GET['keyword']; ?>" name="keyword"/>
              </div>
            </form>
            <?php endif; ?> 
          </div>
          <div class="rp"> <a href="javascript:;" class="float-right pagemore J_PageMenu"><i>&#xe648;</i></a> 
            <?php if (! $this->_var['index']): ?> 
            <a href="<?php echo url('app=cart'); ?>" class="float-right mr5 pagecart"><i>&#xe663;</i></a> 
            <?php endif; ?> 
          </div>
        </li>
      </ul>
      <div class="J_PageMenuBox hidden">
        <div class="page-menu-box"> <span class="arrow"></span>
          <ul class="clearfix">
            <li><a href="<?php echo $this->_var['real_site_url']; ?>"><i class="psmb-icon-font">&#xe63c;</i> 首页</a></li>
            <li><a href="<?php echo url('app=cart'); ?>"><i class="psmb-icon-font">&#xe663;</i> 购物车</a></li>
            <li><a href="<?php echo url('app=member'); ?>"><i class="psmb-icon-font">&#xe635;</i> 用户中心</a></li>
            <li><a href="<?php echo url('app=message&act=newpm'); ?>"><i class="psmb-icon-font">&#xe642;</i> 消息</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
