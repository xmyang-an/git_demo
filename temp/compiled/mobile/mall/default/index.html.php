<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
	<?php if ($this->_var['signPackage']): ?>
	wxshare({signPackage: <?php echo $this->_var['signPackage']; ?>, content: {desc: '<?php echo $this->_var['site_title']; ?>', imgUrl:'<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['site_logo']; ?>'}});
	<?php endif; ?>
})
</script>
<div id="page-index" class="page J_page derect-left">
	<div id="main">
		<div class="full-width-area" area="full-width-area" widget_type="area">
            <?php $this->display_widgets(array('page'=>'index','area'=>'full-width-area')); ?>
        </div>
		
        <div class="slides">
        	<div id="slide" class="scroller" >
            	<ul class="bd">
                <?php $_from = $this->_var['slides']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'slide');$this->_foreach['fe_slide'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_slide']['total'] > 0):
    foreach ($_from AS $this->_var['slide']):
        $this->_foreach['fe_slide']['iteration']++;
?>
                <?php if ($this->_var['slide']['ad_image_url'] && $this->_var['slide']['ad_link_url']): ?>
                <li> <a href='<?php echo $this->_var['slide']['ad_link_url']; ?>'><img src="<?php echo $this->_var['slide']['ad_image_url']; ?>" /></a> </li>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
                <ul class="hd"></ul>
            </div>
        </div>

	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>