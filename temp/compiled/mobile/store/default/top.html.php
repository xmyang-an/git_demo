<!DOCTYPE html>
<html>
<head>
<base href="<?php echo $this->_var['site_url']; ?>/" />
<meta charset="<?php echo $this->_var['charset']; ?>" />
<meta name="author" content="MiMall.NET" />
<meta name="generator" content="MiMall.NET <?php echo $this->_var['mimall_version']; ?>" />
<meta name="copyright" content="MiMall.NET All Rights Reserved" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Expires" CONTENT="-1">           
<meta http-equiv="Cache-Control" CONTENT="no-cache">        
<meta http-equiv="Pragma" CONTENT="no-cache">           
<?php echo $this->_var['page_seo']; ?>

<!--<link rel="icon" href="favicon.ico" type="image/x-icon" />-->
<link type="text/css" href="<?php echo $this->_var['mall_theme_root']; ?>/css/header.css" rel="stylesheet" />
<link type="text/css" href="<?php echo $this->res_base . "/" . 'shop.css'; ?>" rel="stylesheet"  />
<link type="text/css" href="<?php echo $this->_var['mall_theme_root']; ?>/css/footer.css" rel="stylesheet" />
<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var REAL_SITE_URL = "<?php echo $this->_var['real_site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
var APP = '<?php echo $_GET['app']; ?>';
var ID  = '<?php echo $_GET['id']; ?>';
//]]>
</script>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/jquery.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/layer.m/layer.m.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/SuperSlide/TouchSlide.1.1.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/base.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/jquery.plugins/jquery.lazyload.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'mobile/cart.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->_var['mall_theme_root']; ?>/js/main.js" charset="utf-8"></script>

<?php echo $this->_var['_head_tags']; ?>
<!--<editmode></editmode>-->
</head>
<body id="page-layout-<?php echo ($_GET['app'] == '') ? 'default' : $_GET['app']; ?>-<?php echo ($_GET['act'] == '') ? 'index' : $_GET['act']; ?>">
<div id="site-nav" class="w-full"> </div>