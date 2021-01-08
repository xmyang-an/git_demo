<?php echo $this->fetch('header.html'); ?>
<style type="text/css">
.mall-nav{display:none}
</style>
<div id="main" class="w-full">
<div id="page-order" class="w">
   <div class="step step2 mt10 clearfix">
      <span class="fs14 f60">1.查看购物车</span>
      <span class="fs14 fff">2.确认订单信息</span>
      <span class="fs14">3.付款</span>
      <span class="fs14">4.确认收货</span>
      <span class="fs14">5.评价</span>
   </div>
   <div class="order-form">
      <form method="post" id="order_form">
	     <?php echo $this->fetch('order.shipping.html'); ?>
         <?php echo $this->fetch('order.goods.html'); ?>
         <?php echo $this->fetch('order.postscript.html'); ?>
	     <?php echo $this->fetch('order.amount.html'); ?>
      </form>
   </div>
</div>
</div>
<iframe id='iframe_post' name="iframe_post" frameborder="0" width="0" height="0"></iframe>
<?php echo $this->fetch('footer.html'); ?>