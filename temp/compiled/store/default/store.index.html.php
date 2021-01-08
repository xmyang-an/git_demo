<?php echo $this->fetch('header.html'); ?>
<div id="page-store-index">
	<?php echo $this->fetch('store.slides.html'); ?>
	<div class="w-shop clearfix">
        <div class="col-sub w210">
        	<?php echo $this->fetch('left.html'); ?>	
        </div>
        <div class="col-main float-right w980">
        	<div class="new-goods goods-list-shop mb10 border">
            	<div class="title clearfix border-b">
                	<h3 class="float-left">新品上架</h3>
                    <a class="float-right" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. '&order=add_time desc'); ?>">更多新品</a>
                </div>
                <ul class="content  clearfix">
                    <?php $_from = $this->_var['new_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ngoods');$this->_foreach['fe_n'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_n']['total'] > 0):
    foreach ($_from AS $this->_var['ngoods']):
        $this->_foreach['fe_n']['iteration']++;
?>
                    <li class="float-left">
                    	<dl>
                            <dt class="border"><a href="<?php echo url('app=goods&id=' . $this->_var['ngoods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['ngoods']['default_image']; ?>"  /></a></dt>
                            <dd class="desc mt10"><a href="<?php echo url('app=goods&id=' . $this->_var['ngoods']['goods_id']. ''); ?>" target="_blank"><?php echo sub_str(htmlspecialchars($this->_var['ngoods']['goods_name']),50); ?></a></dd>
                            <dd class="mt10 J_GoodsEvaluation" data-score="<?php echo $this->_var['ngoods']['goods_evaluation']; ?>"><span></span></dd>
                            <dd class="price mt10 w-full clearfix"><strong><?php echo price_format($this->_var['ngoods']['price']); ?></strong><em><a href="<?php echo url('app=goods&id=' . $this->_var['ngoods']['goods_id']. '&act=saleslog'); ?>#module" target="_blank">售出<?php echo $this->_var['ngoods']['sales']; ?></a>&nbsp;|&nbsp;<a href="<?php echo url('app=goods&id=' . $this->_var['ngoods']['goods_id']. '&act=comments'); ?>#module" target="_blank">评论<?php echo $this->_var['ngoods']['comments']; ?></a></em></dd>
                         </dl>
                   </li>
                   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            
            <?php if ($this->_var['recommended_goods']): ?>
        	<div class="new-goods goods-list-shop mb10 border">
            	<div class="title clearfix border-b">
                	<h3 class="float-left">推荐商品</h3>
                    <!--a class="float-right" href="">更多推荐</a-->
                </div>
                <ul class="content  clearfix">
                    <?php $_from = $this->_var['recommended_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'rgoods');$this->_foreach['fe_r'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_r']['total'] > 0):
    foreach ($_from AS $this->_var['rgoods']):
        $this->_foreach['fe_r']['iteration']++;
?>
                    <li class="float-left">
                    	<dl>
                            <dt class="border"><a href="<?php echo url('app=goods&id=' . $this->_var['rgoods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['rgoods']['default_image']; ?>"  /></a></dt>
                            <dd class="desc mt10"><a href="<?php echo url('app=goods&id=' . $this->_var['rgoods']['goods_id']. ''); ?>" target="_blank"><?php echo sub_str(htmlspecialchars($this->_var['rgoods']['goods_name']),50); ?></a></dd>
                            <dd class="mt10 J_GoodsEvaluation" data-score="<?php echo $this->_var['rgoods']['goods_evaluation']; ?>"><span></span></dd>
                            <dd class="price mt10 w-full clearfix"><strong><?php echo price_format($this->_var['rgoods']['price']); ?></strong><em><a href="<?php echo url('app=goods&id=' . $this->_var['rgoods']['goods_id']. '&act=saleslog'); ?>#module" target="_blank">售出<?php echo $this->_var['rgoods']['sales']; ?></a>&nbsp;|&nbsp;<a href="<?php echo url('app=goods&id=' . $this->_var['rgoods']['goods_id']. '&act=comments'); ?>#module" target="_blank">评论<?php echo $this->_var['rgoods']['comments']; ?></a></em></dd>
                         </dl>
                   </li>
                   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($this->_var['hot_sale_goods']): ?>
        	<div class="new-goods goods-list-shop mb10 border">
            	<div class="title clearfix border-b">
                	<h3 class="float-left">热卖商品</h3>
                    <a class="float-right" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. '&order=sales desc'); ?>">更多热卖</a>
                </div>
                <ul class="content  clearfix">
                    <?php $_from = $this->_var['hot_sale_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'hgoods');$this->_foreach['fe_h'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_h']['total'] > 0):
    foreach ($_from AS $this->_var['hgoods']):
        $this->_foreach['fe_h']['iteration']++;
?>
                    <li class="float-left">
                    	<dl>
                            <dt class="border"><a href="<?php echo url('app=goods&id=' . $this->_var['hgoods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['hgoods']['default_image']; ?>"  /></a></dt>
                            <dd class="desc mt10"><a href="<?php echo url('app=goods&id=' . $this->_var['hgoods']['goods_id']. ''); ?>" target="_blank"><?php echo sub_str(htmlspecialchars($this->_var['hgoods']['goods_name']),50); ?></a></dd>
                            <dd class="mt10 J_GoodsEvaluation" data-score="<?php echo $this->_var['hgoods']['goods_evaluation']; ?>"><span></span></dd>
                            <dd class="price mt10 w-full clearfix"><strong><?php echo price_format($this->_var['hgoods']['price']); ?></strong><em><a href="<?php echo url('app=goods&id=' . $this->_var['hgoods']['goods_id']. '&act=saleslog'); ?>#module" target="_blank">售出<?php echo $this->_var['hgoods']['sales']; ?></a>&nbsp;|&nbsp;<a href="<?php echo url('app=goods&id=' . $this->_var['hgoods']['goods_id']. '&act=comments'); ?>#module" target="_blank">评论<?php echo $this->_var['hgoods']['comments']; ?></a></em></dd>
                         </dl>
                   </li>
                   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.plugins/raty/jquery.raty.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
	$('.J_GoodsEvaluation').each(function(index, element) {
        $(this).raty({
    		readOnly: true,
            score: $(this).attr('data-score')
		});
    });
	$.each($('.countdown'),function(){
		var theDaysBox  = $(this).find('.NumDays');
		var theHoursBox = $(this).find('.NumHours');
		var theMinsBox  = $(this).find('.NumMins');
		var theSecsBox  = $(this).find('.NumSeconds');	
		countdown(theDaysBox, theHoursBox, theMinsBox, theSecsBox)
	});
});
</script>
<?php echo $this->fetch('footer.html'); ?>
