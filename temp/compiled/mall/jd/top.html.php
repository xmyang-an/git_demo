<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?php echo $this->_var['site_url']; ?>/" />
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />

<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="author" content="mibao123.com" />
<meta name="generator" content="mibao123.com <?php echo $this->_var['mimall_version']; ?>" />
<meta name="copyright" content="mibao123.com All Rights Reserved" />
<meta name="format-detection" content="telephone=no">
<?php echo $this->_var['page_seo']; ?>
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/main.css'; ?>" rel="stylesheet" />
<script type="text/javascript">
//<!CDATA[
var SITE_URL = "<?php echo $this->_var['site_url']; ?>";
var REAL_SITE_URL = "<?php echo $this->_var['real_site_url']; ?>";
var PRICE_FORMAT = '<?php echo $this->_var['price_format']; ?>';
//]]>
</script>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/mall.js'; ?>" charset="utf-8"></script>
<?php echo $this->_var['_head_tags']; ?>
<!--<editmode></editmode>-->
<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key=4M3BZ-F7FRX-YXO4L-ZZQFG-FW42H-EKB43&libraries=convertor"></script>
<script>
getLocation();
function getLocation(){
  //判断是否支持 获取本地位置
  if (navigator.geolocation)
    {
    navigator.geolocation.getCurrentPosition(showPosition);
    }
  else{x.innerHTML="浏览器不支持定位.";}
  }
function showPosition(position)
  {
	  console.log(position);
var lat=position.coords.latitude; 
var lng=position.coords.longitude;
//调用地图命名空间中的转换接口   type的可选值为 1:GPS经纬度，2:搜狗经纬度，3:百度经纬度，4:mapbar经纬度，5:google经纬度，6:搜狗墨卡托
qq.maps.convertor.translate(new qq.maps.LatLng(lat,lng), 1, function(res){
  //取出经纬度并且赋值
     latlng = res[0];
  
  var map = new qq.maps.Map(document.getElementById("container"),{
        center:  latlng,
        zoom: 13
    });
    //设置marker标记
   var marker = new qq.maps.Marker({
            map : map,
            position : latlng
        });
    });
  }
</script>
</head>
<body>
<div id="site-nav" class="w-full">
   <div class="shoptop w clearfix">
      <div class="login_info">
         您好,
         <?php if (! $this->_var['visitor']['user_id']): ?>
         <?php echo htmlspecialchars($this->_var['visitor']['user_name']); ?>
         <a href="<?php echo url('app=member&act=login&ret_url=' . $this->_var['ret_url']. ''); ?>">登录</a>
         <a href="<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>">免费注册</a>
         <?php else: ?>
         <a href="<?php echo url('app=member'); ?>"><span><?php echo htmlspecialchars($this->_var['visitor']['user_name']); ?></span></a>
         <a href="<?php echo url('app=member&act=logout'); ?>">退出</a>
         <a href="<?php echo url('app=message&act=newpm'); ?>">站内消息<?php if ($this->_var['new_message']): ?>(<span><?php echo $this->_var['new_message']; ?></span>)<?php endif; ?></a>
         <?php endif; ?>
      </div>
      <ul class="quick-menu J_GlobalPop">
        <?php if (! $this->_var['index']): ?><li class="home"><a href="<?php echo $this->_var['site_url']; ?>">回到首页</a></li><?php endif; ?>
        <li class="item">
           <div class="menu iwantbuy">
              <a class="menu-hd" href="<?php echo url('app=category'); ?>">我要买<b></b></a>
              <div class="menu-bd J_GlobalPopSub">
                 <div class="menu-bd-panel">
                    <div>
                       <p><a href="<?php echo url('app=category'); ?>">商品分类</a></p>
                       <p><a href="<?php echo url('app=search&order=sales desc'); ?>">大家都喜欢</a></p>
					   <p><a href="<?php echo url('app=search&order=add_time desc'); ?>">最新上架</a></p>
                    </div>
                 </div>
              </div>
           </div>
         </li>
         <li class="item">
            <div class="menu mytb">
               <a class="menu-hd" href="<?php echo url('app=buyer_admin'); ?>">我是买家<b></b></a>
               <div class="menu-bd J_GlobalPopSub">
                  <div class="menu-bd-panel">
                     <div>
                        <p><a href="<?php echo url('app=buyer_order'); ?>">已买到的宝贝</a></p>
                        <p><a href="<?php echo url('app=friend'); ?>">我的好友</a></p>
                        <p><a href="<?php echo url('app=my_question'); ?>">我的咨询</a></p>
                     </div>
                  </div>
               </div>
            </div>
         </li>
         <li class="item">
            <div class="menu seller-center">
               <a class="menu-hd" href="<?php echo url('app=seller_admin'); ?>">我是卖家<b></b></a>
               <div class="menu-bd J_GlobalPopSub">
                  <div class="menu-bd-panel">
                     <div>
                        <p><a href="<?php echo url('app=seller_order'); ?>">已卖出的宝贝</a></p>
                        <p><a href="<?php echo url('app=my_goods'); ?>">出售中的宝贝</a></p>
                     </div>
                  </div>
               </div>
            </div>
         </li>
         <li class="item">
            <div class="menu sites">
               <a class="menu-hd" href="javascript:;">用户中心<b></b></a>
               <div class="menu-bd J_GlobalPopSub">
                  <div class="cart-list  eject-box">
				<div class="login-status"> 你好，<?php if (! $this->_var['visitor']['user_id']): ?>请 <a href="<?php echo url('app=member'); ?>">登录</a><?php else: ?><a href="<?php echo url('app=member'); ?>"><?php echo htmlspecialchars($this->_var['visitor']['user_name']); ?></a><a href="<?php echo url('app=member&act=logout'); ?>" class="ml5">[退出]</a><?php endif; ?> 
				</div>
				<div class="member-nav-list">
					<ul class="ls">
						<li><a href="<?php echo url('app=buyer_order'); ?>" target="_blank">我的订单</a></li>
						<li><a href="<?php echo url('app=my_question'); ?>" target="_blank">咨询回复</a></li>
						<li><a href="<?php echo url('app=my_coupon'); ?>" target="_blank">优惠券</a></li>
					</ul>
					<ul class="ls">
						<li><a href="<?php echo url('app=friend'); ?>" target="_blank">我的好友</a></li>
						<li><a href="<?php echo url('app=my_favorite'); ?>" target="_blank">我的关注</a></li>
						<li><a href="<?php echo url('app=member&act=password'); ?>" target="_blank">修改密码</a></li>
						<li><a href="<?php echo url('app=find_password'); ?>" target="_blank">找回密码</a></li>
					</ul>
				</div>
				<div class="view-list"> 
					<?php if ($this->_var['g_history']): ?>
					<p>我浏览过的商品：</p>
					<ul class="clearfix w-full mt5">
						<?php $_from = $this->_var['g_history']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'h');$this->_foreach['fe_h'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_h']['total'] > 0):
    foreach ($_from AS $this->_var['h']):
        $this->_foreach['fe_h']['iteration']++;
?>
						<li class="float-left"> <a class="border block" href="<?php echo url('app=goods&id=' . $this->_var['h']['goods_id']. ''); ?>"><img src="<?php echo $this->_var['h']['default_image']; ?>" alt="<?php echo $this->_var['h']['goods_name']; ?>" title="<?php echo htmlspecialchars($this->_var['h']['goods_name']); ?>"  width="50" height="50"/></a> </li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</ul>
					<?php else: ?>
					<div class="w-full center">暂时没有浏的商品览记录</div>
					<?php endif; ?> 
				</div>
			</div>
               </div>
            </div>
        </li>
         <li class="item">
            <div class="menu favorite">
               <a class="menu-hd" href="<?php echo url('app=my_favorite'); ?>">收藏夹<b></b></a>
               <div class="menu-bd J_GlobalPopSub">
                  <div class="menu-bd-panel">
                     <div>
                       <p><a href="<?php echo url('app=my_favorite'); ?>">收藏的宝贝</a></p>
                       <p><a href="<?php echo url('app=my_favorite&type=store'); ?>">收藏的店铺</a></p>
                    </div>
                 </div>
               </div>
           </div>
         </li>
         <li class="item" style="background:none">
            <div class="menu sites">
               <a class="menu-hd" href="javascript:;">网站导航<b></b></a>
               <div class="menu-bd padding10 J_GlobalPopSub">
                  <?php $_from = $this->_var['navs']['header']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['nav']):
?>
                  <a href="<?php echo $this->_var['nav']['link']; ?>" <?php if ($this->_var['nav']['open_new']): ?> target="_blank" <?php endif; ?>><?php echo htmlspecialchars($this->_var['nav']['title']); ?></a>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
               </div>
            </div>
        </li>
     </ul>
   </div>
</div>