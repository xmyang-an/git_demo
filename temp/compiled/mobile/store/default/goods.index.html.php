<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
	$('.detail-info img').attr('style','width:100%; height:100%;');
})
</script>
<div id="main">
  <div id="page-goods" class="page-goods" style="padding-bottom:47px;">
  	<?php echo $this->fetch('goodsinfo.html'); ?>
    <div class="floor">
    	<div class="mt"> <em class="vline vleft"></em> <span class="fs12"><i class="psmb-icon-font mr5 fs14">&#xe6e8;</i>详情</span> <em class="vline vright"></em> </div>
    </div>
    <div class="detail-info bgf clearfix">
    
    <?php echo html_filter($this->_var['goods']['description']); ?>
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?> 