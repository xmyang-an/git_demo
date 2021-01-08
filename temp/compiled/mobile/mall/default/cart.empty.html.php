<?php echo $this->fetch('header.html'); ?>
<div id="main">
	<div id="page-cart" class="page-cart-empty page-body"> 
		<div class="empty-cart-tip"> <span>购物车空空如也，赶快去购物吧。</span>
			<p class="mt20"><a href="<?php echo $this->_var['real_site_url']; ?>" class="enter">去购物</a></p>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>