<?php echo $this->fetch('header.html'); ?>
<div id="page-integral" class="integral-w">
	<div class="page-main">
    	<div class="col-1 clearfix integral-w">
        	<div class="col-sub" area="col1-left" widget_type="area">
            <?php $this->display_widgets(array('page'=>'integral','area'=>'col1-left')); ?>
            </div>
            <div class="col-main" area="col1-right" widget_type="area">
            <?php $this->display_widgets(array('page'=>'integral','area'=>'col1-right')); ?>
            </div>
        </div>
        <div class="co-2 integral-w" area="col-2" widget_type="area">
            <?php $this->display_widgets(array('page'=>'integral','area'=>'col-2')); ?>
        </div>
        <div class="col-3 clearfix integral-w">
        	<div class="col-sub" area="col3-left" widget_type="area">
            <?php $this->display_widgets(array('page'=>'integral','area'=>'col3-left')); ?>
            </div>
            <div class="integral-list col-main mb10 clearfix">
				<?php if ($this->_var['goods_list']): ?>
          		<ul class="clearfix">
                 	<?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
                 	<li class="item mb10" <?php if ($this->_foreach['fe_goods']['iteration'] % 4 == 1): ?> style="margin-left:0"<?php endif; ?>>
                 	  <div class="pic"><a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>"><img src="<?php echo $this->_var['goods']['default_image']; ?>" alt="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" /></a></div>
					  <span class="price"><?php echo price_format($this->_var['goods']['price']); ?> <em>+ <?php echo $this->_var['goods']['exchange']; ?> 个积分</em></span>
					  <div class="pro-info">
                      	<span><i>可抵扣<?php echo price_format($this->_var['goods']['exchange_price']); ?>元</i></span>
                      </div>
                      <div class="desc"><a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>"><?php echo sub_str(htmlspecialchars($this->_var['goods']['goods_name']),60); ?></a></div>
                 	</li>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              	</ul>
				<?php echo $this->fetch('page.bottom.html'); ?>
                <?php else: ?>
                <div class="notice-word"><p class="yellow-big">没有限时打折商品</p></div>
				<?php endif; ?>
       		</div>
        </div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>