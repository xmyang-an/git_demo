<?php echo $this->fetch('header.html'); ?>
<div id="page-channel">
    <div class="col-1 w clearfix">
        <div class="col-1-left" area="col-1-left" widget_type="area">
            <?php $this->display_widgets(array('page'=>'15255109678','area'=>'col-1-left')); ?>
        </div>
        <div class="col-1-right" area="col-1-right" widget_type="area" >
            <?php $this->display_widgets(array('page'=>'15255109678','area'=>'col-1-right')); ?>
        </div>
    </div> 
    <div class="col-2 w" area="col-2" widget_type="area">
        <?php $this->display_widgets(array('page'=>'15255109678','area'=>'col-2')); ?>
    </div>
</div>
<?php echo $this->fetch('footer.html'); ?>

