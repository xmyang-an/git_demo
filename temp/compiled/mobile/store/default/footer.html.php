<div id="footer">
	<?php if (in_array ( $_GET['app'] , array ( 'goods' , 'search' ) )): ?>
    <a class="float-report" href="<?php echo url('app=report&id=' . $_GET['id']. ''); ?>"></a>
	<div class="float-back-top" onclick="window.scroll(0,0);"></div>
    <?php endif; ?>

    <?php if (in_array ( $_GET['app'] , array ( '' ) ) && ! in_array ( $_GET['act'] , array ( '' ) )): ?>
    <div class="fixed-gap"></div>
	<div class="float-layer">
		<div class="global-nav global-nav-current">
			<div class="global-nav__nav-wrap"> 
                <div class="global-nav__nav-item"> <a href="<?php echo $this->_var['real_site_url']; ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-index">&#xe63c;</i> <span class="global-nav__nav-tit">首页</span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=store&act=category&id=' . $this->_var['store']['store_id']. ''); ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-category">&#xe639;</i> <span class="global-nav__nav-tit">分类</span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=webim&act=friend'); ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-search">&#xe642;</i> <span class="global-nav__nav-tit">消息</span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=cart'); ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-shop-cart">&#xe663;</i> <span class="global-nav__nav-tit">购物车</span> <span class="global-nav__nav-shop-cart-num" id="carId"><?php echo $this->_var['cart_goods_kinds']; ?></span></a> </div>
				<div class="global-nav__nav-item"> <a href="<?php echo url('app=member'); ?>" class="global-nav__nav-link"><i class="psmb-icon-font global-nav__icon-my-yhd">&#xe635;</i> <span class="global-nav__nav-tit">我的</span></a> </div>
			</div>
			<div class="global-nav__operate-wrap hidden"> <span class="global-nav__operate-cart-num" id="globalId"><?php echo $this->_var['cart_goods_kinds']; ?></span> </div>
		</div>
	</div>
	<?php endif; ?> 
    <?php echo $this->_var['statistics_code']; ?>
</div>
</body>
</html>