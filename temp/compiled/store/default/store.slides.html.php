<?php if ($this->_var['store']['store_slides']): ?>
<div class="w-shop mb10" style="width:100%;">
    <div class="store-slides J_StoreSlides">
		<div class="scroller">
			<ul class="ks-content ks-switchable-content clearfix">
				<?php $_from = $this->_var['store']['store_slides']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'slides');$this->_foreach['fe_slides'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_slides']['total'] > 0):
    foreach ($_from AS $this->_var['slides']):
        $this->_foreach['fe_slides']['iteration']++;
?>
				<li class="clearfix">
					<a href="<?php echo $this->_var['slides']['link']; ?>" target="_blank" style="background:url(<?php echo $this->_var['slides']['url']; ?>) no-repeat scroll center center transparent;"></a>
				</li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
		</div>
        <div class="store-slides-btn hidden">
        	<a href="javascript:;" class="prev"></a>
            <a href="javascript:;" class="next"></a>
        </div>
		<div class="ks-switchable-nav">
			<?php $_from = $this->_var['store']['store_slides']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'slides');$this->_foreach['fe_slides'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_slides']['total'] > 0):
    foreach ($_from AS $this->_var['slides']):
        $this->_foreach['fe_slides']['iteration']++;
?>
			<span <?php if (($this->_foreach['fe_slides']['iteration'] <= 1)): ?>class="ks-active" <?php endif; ?>></span>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
    </div>
</div>
<script type="text/javascript">
$('.J_StoreSlides ul li').css('width', $(window).width() +'px');
$(function(){
	$('.J_StoreSlides ul li,.store-slides-btn').hover(function(){
		$('.store-slides-btn').toggle();
	});
		
	$(".J_StoreSlides").slide({mainCell:".ks-switchable-content", titCell:".ks-switchable-nav span", effect:"leftLoop", trigger:"mouseover", prevCell:".prev", nextCell:".next", titOnClassName:"ks-active", autoPlay:true});					
});	
</script>
<?php else: ?>
<div class="w mb10"></div>
<?php endif; ?>