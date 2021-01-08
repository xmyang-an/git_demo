<div id="footer"> 
    <?php if (in_array ( $_GET['app'] , array ( 'goods' , 'search' ) )): ?>
	<div class="float-back-top" onclick="window.scroll(0,0);"></div>
    <?php endif; ?>
	
	<?php if (in_array ( $_GET['app'] , array ( 'default' , '' , 'member' ) ) && ! in_array ( $_GET['act'] , array ( 'login' , 'register' , 'setting' , 'profile' , 'password' , 'email' , 'bind' , 'bindlist' ) )): ?>
    <div class="fixed-gap"></div>
	<div class="float-layer">
		<div class="global-nav global-nav-current">
			<div class="global-nav__nav-wrap iphonex">
				<div class="global-nav__nav-item"> <a href="<?php echo $this->_var['real_site_url']; ?>" class="global-nav__nav-link <?php if ($_GET['app'] == '' || $_GET['app'] == 'default'): ?>current<?php endif; ?>"><i class="psmb-icon-font global-nav__icon-index">&#xe63c;</i> <span class="global-nav__nav-tit">首页</span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=category'); ?>" class="global-nav__nav-link <?php if ($_GET['app'] == 'category'): ?>current<?php endif; ?>"><i class="psmb-icon-font global-nav__icon-category">&#xe639;</i> <span class="global-nav__nav-tit">分类</span></a> </div>
                
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=webim&act=friend'); ?>" class="global-nav__nav-link <?php if ($_GET['act'] == 'form'): ?>current<?php endif; ?>"><i class="psmb-icon-font global-nav__icon-search">&#xe642;</i> <span class="global-nav__nav-tit">消息</span></a> </div>
                
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=cart'); ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-shop-cart">&#xe663;</i> <span class="global-nav__nav-tit">购物车</span> <span class="global-nav__nav-shop-cart-num" id="carId"><?php echo $this->_var['cart_goods_kinds']; ?></span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=member'); ?>" class="global-nav__nav-link <?php if ($_GET['app'] == 'member'): ?>current<?php endif; ?>"><i class="psmb-icon-font global-nav__icon-my-yhd">&#xe635;</i> <span class="global-nav__nav-tit">我的</span></a> </div>
			</div>
		</div>
	</div>
	<?php endif; ?> 
</div>
</body>
</html>