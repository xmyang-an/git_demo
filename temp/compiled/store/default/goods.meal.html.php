<?php if ($this->_var['goods']['mealgoods']): ?>
<div class="mealgoods mb20">
	<div class="attr-tabs">
		<ul class="user-menu J_MealTab">
			<?php $_from = $this->_var['goods']['mealgoods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'meal');$this->_foreach['fe_meal'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_meal']['total'] > 0):
    foreach ($_from AS $this->_var['meal']):
        $this->_foreach['fe_meal']['iteration']++;
?>
			<li class="<?php if (($this->_foreach['fe_meal']['iteration'] <= 1)): ?>active<?php endif; ?>"> <a href="javascript:;"><span><?php echo $this->_var['meal']['title']; ?></span></a></li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
	</div>
	<div class="content">
		<?php $_from = $this->_var['goods']['mealgoods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'meal');$this->_foreach['fe_meal'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_meal']['total'] > 0):
    foreach ($_from AS $this->_var['meal']):
        $this->_foreach['fe_meal']['iteration']++;
?>
		<div class="mealeach J_MealEach clearfix <?php if (! ($this->_foreach['fe_meal']['iteration'] <= 1)): ?>hidden<?php endif; ?>">
			<ul class="master float-left">
				<li>
					<s class="plus"></s>
					<div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><img src="<?php echo $this->_var['goods']['default_image']; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" width="100" height="100" /></a></div>
					<div class="desc"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['goods']['goods_name']; ?>"><?php echo htmlspecialchars(sub_str($this->_var['goods']['goods_name'],40)); ?></a></div>
					<div class="price"><?php echo price_format($this->_var['goods']['price']); ?></div>
				</li>
			</ul>
			<div class="suits float-left clearfix">
				<ul class="suits-lst" style="width:<?php echo $this->_var['meal']['width']; ?>px;"> 
					<?php $_from = $this->_var['meal']['meal_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?>
					<li>
						<s class="<?php if (! ($this->_foreach['fe_item']['iteration'] == $this->_foreach['fe_item']['total'])): ?>plus<?php endif; ?>"></s>
						<div class="pic"><a href="<?php echo url('app=goods&id=' . $this->_var['item']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['item']['goods_name']; ?>"><img src="<?php echo $this->_var['item']['default_image']; ?>" alt="<?php echo $this->_var['item']['goods_name']; ?>" width="100" height="100" /></a></div>
						<div class="desc"><a href="<?php echo url('app=goods&id=' . $this->_var['item']['goods_id']. ''); ?>" target="_blank" title="<?php echo $this->_var['item']['goods_name']; ?>"><?php echo htmlspecialchars(sub_str($this->_var['item']['goods_name'],40)); ?></a></div>
						<div class="price"><?php echo price_format($this->_var['item']['price']); ?></div>
					</li>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
				</ul>
			</div>
			<ul class="buy float-right clearfix">
				<li class="buy-info">
					<s class="eq"></s>
					<div class="name"><a href="<?php echo url('app=meal&id=' . $this->_var['meal']['meal_id']. ''); ?>" target="_blank"><?php echo htmlspecialchars($this->_var['meal']['title']); ?></a></div>
					<div class="meal-price">套餐价：<b><?php echo price_format($this->_var['meal']['price']); ?></b></div>
					<div class="original-price">原&nbsp;&nbsp;&nbsp;价：<del><?php echo price_format($this->_var['meal']['sub_price']); ?></del></div>
					<div class="save-price">立&nbsp;&nbsp;&nbsp;省：<?php echo price_format($this->_var['meal']['save_price']); ?></div>
					<div class="buybtn"><a href="<?php echo url('app=meal&id=' . $this->_var['meal']['meal_id']. ''); ?>" target="_blank">购买套餐</a></div>
				</li>
			</ul>
		</div>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
	</div>
</div>
<script>
$(function(){
	$('.J_MealTab li').click(function(){
		
		$(this).parent().find('li').removeClass('active').addClass('normal');
		$(this).removeClass('normal').addClass('active');
		$('.J_MealEach').hide();
	
		i = $(this).index();
		$('.J_MealEach').parent().find('.J_MealEach:eq('+i+')').show();
	});
});
</script> 
<?php endif; ?>