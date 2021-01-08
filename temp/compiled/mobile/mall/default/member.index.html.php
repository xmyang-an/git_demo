<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
$(function(){  
	$('.J_SignIntegral').click(function(){
		var user_id = '<?php echo $this->_var['visitor']['user_id']; ?>';
		if(user_id == 0){
			layer.open({content: '登陆后才可以领取积分！', className:'layer-popup', time: 3,end:function(){
				window.location.href = REAL_SITE_URL + '/index.php?app=member&act=login';
			}});
		}
		$.getJSON(REAL_SITE_URL + '/index.php?app=my_integral&act=sign_in_integral', function(data){
			if(data.done) {
				$('.J_SignIntegral').html('<i class="psmb-icon-font fs14">&#xe711;</i> 今日已签到');
			}
			layer.open({content: data.msg, className:'layer-popup', time: 3});
		});	
	});
});
</script>
<div id="main">
  <div id="page-member">
    <div class="relative top-info mb10">
      <div class="right-top-po clearfix"> <a class="set-btn float-left fs14" href="<?php echo url('app=member&act=setting'); ?>">设置</a> <a class="float-right mr10 fs16" href="<?php echo url('app=category'); ?>" ><i class="psmb-icon-font">&#xe644;</i></a> <a class="float-right mr10 fs16" href="<?php echo url('app=search&act=form'); ?>" ><i class="psmb-icon-font">&#xe62a;</i></a> </div>
      <div class="user-info clearfix">
        <div class="user-photo float-left"><a href="<?php echo url('app=member&act=setting'); ?>"><img src="<?php echo $this->_var['user']['portrait']; ?>" width="50" height="50" /></a></div>
        <div class="webkit-box">
          <div class="user-name flex1 overflow-ellipsis"><?php if ($this->_var['visitor']['user_id']): ?><?php echo htmlspecialchars($this->_var['user']['user_name']); ?><?php else: ?> <a href="<?php echo url('app=member&act=register'); ?>" class="fff" >注册</a> / <a href="<?php echo url('app=member&act=login'); ?>" class="fff" >登陆</a><?php endif; ?></div>
          <?php if ($this->_var['integral_enabled']): ?>
          <div class="user-integral">
          	<?php if ($this->_var['user']['can_sign']): ?>
          	<a class="box-align-center J_SignIntegral" href="javascript:;"><i class="psmb-icon-font fs14">&#xe711;</i> 签到领积分 <i class="psmb-icon-font fs12">&#xe63d;</i></a>
          	<?php else: ?>
          	<a class="box-align-center" href="<?php echo url('app=my_integral'); ?>">积分 <?php echo ($this->_var['user']['integral'] == '') ? '0' : $this->_var['user']['integral']; ?><i class="psmb-icon-font fs12">&#xe63d;</i></a>
          	<?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="top-menu">
        <ul class="clearfix webkit-box">
          <li class="flex1"> <a href="<?php echo url('app=my_favorite'); ?>"> <span class="fs14"><?php echo $this->_var['user']['count_collect_goods']; ?></span><br />
            <span>收藏夹</span> </a> </li>
          <li class="flex1"> <a href="<?php echo url('app=my_favorite&type=store'); ?>"> <span class="fs14"><?php echo $this->_var['user']['count_collect_store']; ?></span><br />
            <span>关注店铺</span> </a> </li>
          <li class="flex1"> <a href="<?php echo url('app=my_footmark'); ?>" style="border-right:0"> <span class="fs14"><?php echo ($this->_var['user']['count_footmark'] == '') ? '0' : $this->_var['user']['count_footmark']; ?></span><br />
            <span>我的足迹</span> </a> </li>
        </ul>
      </div>
    </div>
    <div class="fun-list clearfix"> 
      <?php $_from = $this->_var['_member_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?> 
      <?php $_from = $this->_var['item']['submenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'subitem');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['subitem']):
?> 
      <?php if (in_array ( $this->_var['subitem']['name'] , array ( 'promotool' ) )): ?>
      <div class="line-background"></div>
      <?php endif; ?>
      <div class="fun-row <?php if (in_array ( $this->_var['subitem']['name'] , array ( 'my_order' , 'order_manage' , 'my_capital' , 'promotool' ) )): ?>fun-row-line<?php endif; ?>"> <a href="<?php echo $this->_var['subitem']['url']; ?>" class="clearfix block">
        <p class="title <?php echo $this->_var['subitem']['name']; ?> clearfix"><i></i><span><?php echo $this->_var['subitem']['text']; ?></span></p>
        <em class="float-right view mr10 hidden"><?php echo $this->_var['subitem']['sub_text']; ?></em> </a> </div>
      <?php if ($this->_var['subitem']['name'] == 'my_order'): ?>
      <div class="fun-row child-row">
        <ul class="clearfix">
          <li> <a href="<?php echo url('app=buyer_order&type=pending'); ?>"> <i>&#xe6eb;<?php if ($this->_var['buyer_stat']['pending']): ?><ins><?php echo $this->_var['buyer_stat']['pending']; ?></ins><?php endif; ?></i>
            <p>待付款</p>
            </a> </li>
          <li> <a href="<?php echo url('app=buyer_order&type=accepted'); ?>"> <i>&#xe6f1;<?php if ($this->_var['buyer_stat']['accepted']): ?><ins><?php echo $this->_var['buyer_stat']['accepted']; ?></ins><?php endif; ?></i>
            <p>待发货</p>
            </a> </li>
          <li> <a href="<?php echo url('app=buyer_order&type=shipped'); ?>"> <i>&#xe6f2;<?php if ($this->_var['buyer_stat']['shipped']): ?><ins><?php echo $this->_var['buyer_stat']['shipped']; ?></ins><?php endif; ?></i>
            <p>待收货</p>
            </a> </li>
          <li> <a href="<?php echo url('app=buyer_order&type=finished&evaluation_status=0'); ?>"> <i>&#xe6f0;<?php if ($this->_var['buyer_stat']['finished']): ?><ins><?php echo $this->_var['buyer_stat']['finished']; ?></ins><?php endif; ?></i>
            <p>待评价</p>
            </a> </li>
          <li> <a href="<?php echo url('app=refund'); ?>"> <i>&#xe6ef;<?php if ($this->_var['buyer_stat']['refund']): ?><ins><?php echo $this->_var['buyer_stat']['refund']; ?></ins><?php endif; ?></i>
            <p>退款</p>
            </a> </li>
        </ul>
      </div>
      <div class="line-background"></div>
      <?php elseif ($this->_var['subitem']['name'] == 'order_manage'): ?>
      <div class="fun-row child-row">
        <ul class="clearfix">
          <li> <a href="<?php echo url('app=seller_order&type=pending'); ?>"> <i>&#xe6eb;<?php if ($this->_var['seller_stat']['pending']): ?><ins><?php echo $this->_var['seller_stat']['pending']; ?></ins><?php endif; ?></i>
            <p>待付款</p>
            </a> </li>
          <li> <a href="<?php echo url('app=seller_order&type=accepted'); ?>"> <i>&#xe6f1;<?php if ($this->_var['seller_stat']['accepted']): ?><ins><?php echo $this->_var['seller_stat']['accepted']; ?></ins><?php endif; ?></i>
            <p>待发货</p>
            </a> </li>
          <li> <a href="<?php echo url('app=seller_order&type=shipped'); ?>"> <i>&#xe6f2;<?php if ($this->_var['seller_stat']['shipped']): ?><ins><?php echo $this->_var['seller_stat']['shipped']; ?></ins><?php endif; ?></i>
            <p>待收货</p>
            </a> </li>
          <li> <a href="<?php echo url('app=seller_order&type=finished&evaluation_status=0'); ?>"> <i>&#xe6f0;<?php if ($this->_var['seller_stat']['finished']): ?><ins><?php echo $this->_var['seller_stat']['finished']; ?></ins><?php endif; ?></i>
            <p>待评价</p>
            </a> </li>
          <li> <a href="<?php echo url('app=refund&act=receive'); ?>"> <i>&#xe6ef;<?php if ($this->_var['seller_stat']['refund']): ?><ins><?php echo $this->_var['seller_stat']['refund']; ?></ins><?php endif; ?></i>
            <p>退款</p>
            </a> </li>
        </ul>
      </div>
      <div class="line-background"></div>
      <?php endif; ?> 
      
      <?php if ($this->_var['subitem']['name'] == 'promotool'): ?> 
      <?php $_from = $this->_var['subitem']['submenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k2', 'subitem2');if (count($_from)):
    foreach ($_from AS $this->_var['k2'] => $this->_var['subitem2']):
?>
      <div class="fun-row"> <a href="<?php echo $this->_var['subitem2']['url']; ?>" class="clearfix block">
        <p class="title <?php echo $this->_var['subitem2']['name']; ?> clearfix"><i></i><span><?php echo $this->_var['subitem2']['text']; ?></span></p>
        <em class="float-right view mr10 hidden"><?php echo $this->_var['subitem2']['sub_text']; ?></em> </a> </div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      <?php endif; ?> 
      
      <?php if ($this->_var['subitem']['name'] == 'my_capital'): ?>
      <div class="fun-row child-row <?php echo $this->_var['subitem']['name']; ?>">
        <ul class="clearfix">
          <li> <a href="<?php echo url('app=deposit'); ?>"> <i>&#xe740;</i>
            <p>钱包</p>
            </a> </li>
          <li> <a href="<?php echo url('app=my_coupon'); ?>"> <i>&#xe722;</i>
            <p>优惠券</p>
            </a> </li>
          <li> <a href="<?php echo url('app=my_integral'); ?>"> <i>&#xe70e;</i>
            <p>积分</p>
            </a> </li>
          <li> <a href="<?php echo url('app=my_cashcard'); ?>"> <i>&#xe719;</i>
            <p>充值卡</p>
            </a> </li>
          <!--
          <li> <a href="javascript:;"> <i>&#xe7d5;</i>
            <p>红包</p>
            </a> </li>-->
        </ul>
      </div>
      <div class="line-background"></div>
      <?php endif; ?> 
      
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      
      <?php if ($this->_var['_member_menu']['overview']): ?>
      <div class="fun-row"> <a href="<?php echo $this->_var['_member_menu']['overview']['url']; ?>" class="clearfix block">
        <p class="title <?php echo $this->_var['_member_menu']['overview']['name']; ?> clearfix"><i></i><span><?php echo $this->_var['_member_menu']['overview']['text']; ?></span></p>
        </a> </div>
      <?php endif; ?> 
      <?php if (in_array ( $this->_var['member_role'] , array ( 'buyer_admin' ) ) && $this->_var['visitor']['store_id']): ?>
      <div class="fun-row"> <a href="<?php echo url('app=seller_admin'); ?>" class="clearfix block">
        <p class="title im_seller clearfix"><i></i><span>卖家中心</span></p>
        </a> </div>
      <?php endif; ?> 
      
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>