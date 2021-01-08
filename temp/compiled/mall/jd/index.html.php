<?php echo $this->fetch('header.html'); ?>
<div id="main" class="w-full">
  <div id="page-home">
    <div class="col-1 clearfix">
      <div class="col-1-r-l" area="col-1-r-l" widget_type="area"> 
        <?php $this->display_widgets(array('page'=>'index','area'=>'col-1-r-l')); ?> 
      </div>
    </div>
    <div class="col-2 w clearfix">
     <div class="col-2-r-c float-left" area="col-1-r-c" widget_type="area" >
        	<?php $this->display_widgets(array('page'=>'index','area'=>'col-1-r-c')); ?>
        </div>
        <div class="col-2-r-r float-right" area="col-1-r-r" widget_type="area" >
          <?php $this->display_widgets(array('page'=>'index','area'=>'col-1-r-r')); ?>
        </div>
    </div>
    <div class="col-3 w" area="col-3" widget_type="area"> 
      <?php $this->display_widgets(array('page'=>'index','area'=>'col-3')); ?> 
    </div>
  </div>
  <?php if ($this->_var['index']): ?>
  <div class="J_FloorNav floor-nav"></div>
  <?php endif; ?> 
</div>
<?php echo $this->fetch('footer.html'); ?> 