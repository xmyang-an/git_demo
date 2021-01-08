<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
	TouchSlide({slideCell:"#slides",titCell:".hd",mainCell:".bd",effect:"leftLoop", autoPlay:true,autoPage:true, titOnClassName:"active", delayTime:1000, interTime: 5000});
	
	<?php if ($this->_var['signPackage']): ?>
	wxshare({signPackage: <?php echo $this->_var['signPackage']; ?>, content: {desc: '<?php echo $this->_var['site_title']; ?>', imgUrl:'<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['store_logo']; ?>'}});
	<?php endif; ?>
});
</script>
<div id="main">
  <div class="page-store page J_page">
    <div class="store-info">
      <div class="store-banner"> 
        <?php if ($this->_var['store']['wap_store_banner']): ?> 
        <img src="<?php echo $this->_var['store']['wap_store_banner']; ?>" height="150" />
        <?php endif; ?> 
      </div>
      <div class="d-info clearfix">
        <h3 class="float-left ml5"><a class="block" href="<?php if ($this->_var['my_store']): ?><?php echo url('app=dcenter&act=edit&did=' . $_GET['did']. ''); ?><?php else: ?>javascript:;<?php endif; ?>"><img src="<?php echo $this->_var['store']['store_logo']; ?>" /></a></h3>
        <div class="name-and-credit float-left">
          <p class="name" style="margin-top:8px;"><a href="<?php if ($this->_var['my_store']): ?><?php echo url('app=dcenter&act=edit&did=' . $_GET['did']. ''); ?><?php else: ?>javascript:;<?php endif; ?>"><?php echo $this->_var['store']['store_name']; ?></a></p>
          <p><?php if ($this->_var['store']['credit_value'] >= 0): ?><img src="<?php echo $this->_var['store']['credit_image']; ?>" alt="" /><?php endif; ?></p>
        </div>
        <div class="collect clearfix"> 
          <?php if ($this->_var['store']['collected']): ?>
          <div class="collect-btn collected">已收藏</div>
          <?php else: ?>
          <div class="collect-btn J_AjaxRequest" action="<?php echo url('app=my_favorite&act=add&type=store&item_id=' . $this->_var['store']['store_id']. '&ajax=1'); ?>">收藏</div>
          <?php endif; ?>
          <div class="collect-cn">
            <p class="num"><?php echo ($this->_var['store']['be_collect'] == '') ? '0' : $this->_var['store']['be_collect']; ?></p>
            <p class="txt">粉丝</p>
          </div>
        </div>
      </div>
    </div>
    <div class="store-menus">
      <ul class="webkit-box">
        <li class="flex1"><a class="block center active fs14" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><span>首页</span></a></li>
        <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. ''); ?>"><span>全部商品</span></a></li>
        <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. '&new=1'); ?>"><span>上新</span></a></li>
        <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=limitbuy&id=' . $this->_var['store']['store_id']. ''); ?>"><span>促销</span></a></li>
      </ul>
    </div>
    
    <?php if ($this->_var['store']['wap_store_slides']): ?>
    <div class="store-index-slide">
      <div id="slides" class="scroller J_Slides">
        <ul class="bd clearfix">
          <?php $_from = $this->_var['store']['wap_store_slides']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'slide');$this->_foreach['fe_slide'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_slide']['total'] > 0):
    foreach ($_from AS $this->_var['slide']):
        $this->_foreach['fe_slide']['iteration']++;
?> 
          <?php if ($this->_var['slide']['url'] && $this->_var['slide']['link']): ?>
          <li><a href="<?php echo $this->_var['slide']['link']; ?>"><img src="<?php echo $this->_var['slide']['url']; ?>" /></a></li>
          <?php endif; ?> 
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
        <ul class="hd">
        </ul>
      </div>
    </div>
    <?php endif; ?> 
    
    <?php if ($this->_var['store']['recommended_goods']): ?>
    <div class="rec-goods goods-model-si mt5"> 
      <!--<h3 class="mt5"><span><em>推荐</em></span></h3>-->
      <ul class="clearfix">
        <?php $_from = $this->_var['store']['recommended_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
        <li class="item"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>">
          <div class="pic"><img src="<?php echo $this->_var['goods']['default_image']; ?>" /></div>
          <p class="goods-name line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></p>
          <div class="webkit-box pb5">
            <p class="price flex1"><?php echo price_format($this->_var['goods']['price']); ?></p>
            <p class="sales fs10 gray">已售<?php echo ($this->_var['goods']['sales'] == '') ? '0' : $this->_var['goods']['sales']; ?></p>
          </div>
          </a> </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
    </div>
    <?php endif; ?> 
    <?php if ($this->_var['store']['new_goods']): ?>
    <div class="new-goods goods-model-si">
      <h3><span><em>新品</em></span></h3>
      <ul class="clearfix">
        <?php $_from = $this->_var['store']['new_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
        <li class="item"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>">
          <div class="pic"><img src="<?php echo $this->_var['goods']['default_image']; ?>" /></div>
          <p class="goods-name line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></p>
          <div class="webkit-box pb5">
            <p class="price flex1"><?php echo price_format($this->_var['goods']['price']); ?></p>
            <p class="sales fs10 gray">已售<?php echo ($this->_var['goods']['sales'] == '') ? '0' : $this->_var['goods']['sales']; ?></p>
          </div>
          </a> </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
    </div>
    <?php endif; ?> 
    <?php if ($this->_var['store']['sales_goods']): ?>
    <div class="hot-goods goods-model-si">
      <h3><span><em>热卖</em></span></h3>
      <ul class="clearfix">
        <?php $_from = $this->_var['store']['sales_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
        <li class="item"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>">
          <div class="pic"><img src="<?php echo $this->_var['goods']['default_image']; ?>" /></div>
          <p class="goods-name line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></p>
          <div class="webkit-box pb5">
            <p class="price flex1"><?php echo price_format($this->_var['goods']['price']); ?></p>
            <p class="sales fs10 gray">已售<?php echo ($this->_var['goods']['sales'] == '') ? '0' : $this->_var['goods']['sales']; ?></p>
          </div>
          </a> </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
    </div>
    <?php endif; ?> 
    <?php if ($this->_var['store']['hot_goods']): ?>
    <div class="new-goods goods-model-si">
      <h3><span><em>人气</em></span></h3>
      <ul class="clearfix">
        <?php $_from = $this->_var['store']['hot_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
        <li class="item"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>">
          <div class="pic"><img src="<?php echo $this->_var['goods']['default_image']; ?>" /></div>
          <p class="goods-name line-clamp-2"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></p>
          <div class="webkit-box pb5">
            <p class="price flex1"><?php echo price_format($this->_var['goods']['price']); ?></p>
            <p class="sales fs10 gray">已售<?php echo ($this->_var['goods']['sales'] == '') ? '0' : $this->_var['goods']['sales']; ?></p>
          </div>
          </a> </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
    </div>
    <?php endif; ?>
    <div class="view-all"> <a href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. ''); ?>">查看全部商品<i class="psmb-icon-font fs13">&#xe629;</i></a> </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?> 