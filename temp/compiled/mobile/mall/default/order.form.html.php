<?php echo $this->fetch('header.html'); ?>
<div id="main" class="w-full">
  <div id="page-order" class="w-full">
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
<?php echo $this->fetch('footer.html'); ?>