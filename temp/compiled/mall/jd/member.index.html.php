<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful">
      <div class="profile clearfix">
        <div class="photo">
          <p><img src="<?php echo $this->_var['user']['portrait']; ?>" width="70" height="70" /></p>
        </div>
        <div class="info clearfix">
          <dl class="col-1 float-left">
            <dt> <span>欢迎您，</span><strong><?php echo htmlspecialchars($this->_var['user']['user_name']); ?></strong> <a href="<?php echo url('app=member&act=profile'); ?>">编辑个人资料</a> </dt>
            <dd class="gray"> <span>上次登录时间：<?php echo local_date("Y-m-d H:i:s",$this->_var['user']['last_login']); ?></span> <span>上次登录 IP：<?php echo $this->_var['user']['last_ip']; ?></span> </dd>
          </dl>
        </div>
      </div>
      <div class="platform clearfix"> 
        
        <?php if ($this->_var['store'] && $this->_var['member_role'] == 'seller_admin'): ?>
        <div class="clearfix w-full deal">
            <div class="welitem clearfix">
                <a class="wrap" href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>">
                    <p class="l1">好评率</p>
                    <p class="l2"><img src="static/images/praise.png" /></p>
                    <h3 class="l3"><?php echo ($this->_var['store']['praise_rate'] == '') ? '0' : $this->_var['store']['praise_rate']; ?>%</h3>
                </a> 
            </div>
             <div class="welitem clearfix">
                <a class="wrap" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>">
                    <p class="l1">商家等级</p>
                    <p class="l2"><img src="static/images/grade.png" /></p>
                    <h3 class="l3"><span><?php echo $this->_var['sgrade']['grade_name']; ?></span></h3>
                </a> 
            </div>
             <div class="welitem clearfix">
                <a class="wrap" href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>">
                    <p class="l1">有效期</p>
                    <p class="l2"><img src="static/images/period.png" /></p>
                    <h3 class="l3"><?php if ($this->_var['sgrade']['add_time']): ?><?php echo sprintf('剩余 %s 天', $this->_var['sgrade']['add_time']); ?><?php else: ?><span>不限制</span><?php endif; ?></h3>
                </a> 
            </div>
             <div class="welitem clearfix">
                <a class="wrap" href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>">
                    <p class="l1">商品发布</p>
                    <p class="l2"><img src="static/images/product.png" /></p>
                    <h3 class="l3"><?php echo $this->_var['sgrade']['goods']['used']; ?>/<?php if ($this->_var['sgrade']['goods']['total']): ?><?php echo $this->_var['sgrade']['goods']['total']; ?><?php else: ?><span>不限制</span><?php endif; ?></h3>
                </a> 
            </div>
             <div class="welitem clearfix">
                <a class="wrap" href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>">
                    <p class="l1">空间使用</p>
                    <p class="l2"><img src="static/images/space.png" /></p>
                    <h3 class="l3"><?php echo $this->_var['sgrade']['space']['used']; ?>M/<?php if ($this->_var['sgrade']['space']['total']): ?><?php echo $this->_var['sgrade']['space']['total']; ?>M<?php else: ?><span>不限制</span><?php endif; ?></h3>
                </a> 
            </div>
        </div>
        <?php else: ?>
        <div class="clearfix w-full">
          <div class="welitem clearfix"> <a class="wrap" href="<?php echo url('app=deposit'); ?>">
            <p class="l1">账户余额</p>
            <p class="l2"><img src="static/images/yue.png" /></p>
            <h3 class="l3"><i class="gray">&yen;</i> <?php echo ($this->_var['user']['deposit_account']['money'] == '') ? '0' : $this->_var['user']['deposit_account']['money']; ?></h3>
            </a> </div>
          <div class="welitem clearfix"> <a class="wrap" href="<?php echo url('app=my_coupon'); ?>">
            <p class="l1">优惠券</p>
            <p class="l2"><img src="static/images/coupon2.png" /></p>
            <h3 class="l3"><?php echo ($this->_var['user']['coupon_count'] == '') ? '0' : $this->_var['user']['coupon_count']; ?> <i class="gray">张</i></h3>
            </a> </div>
          <div class="welitem clearfix"> <a class="wrap" href="<?php echo url('app=my_integral'); ?>">
            <p class="l1">商城积分</p>
            <p class="l2"><img src="static/images/integral.png" /></p>
            <h3 class="l3"><?php echo ($this->_var['user']['integral'] == '') ? '0' : $this->_var['user']['integral']; ?> <i class="gray">个</i></h3>
            </a> </div>
          <div class="welitem clearfix"> <a class="wrap" href="<?php echo url('app=message&act=newpm'); ?>">
            <p class="l1">未读短信息</p>
            <p class="l2"><img src="static/images/message.png" /></p>
            <h3 class="l3"><?php echo $this->_var['new_message']; ?> <i class="gray">条</i></h3>
            </a> </div>
          <div class="welitem clearfix"> <a class="wrap" href="<?php echo url('app=friend'); ?>">
            <p class="l1">好友</p>
            <p class="l2"><img src="static/images/friends.png" /></p>
            <h3 class="l3"><?php echo ($this->_var['user']['friends'] == '') ? '0' : $this->_var['user']['friends']; ?> <i class="gray">人</i></h3>
            </a> </div>
        </div>
 
        <div class="buyer-notice">
          <div class="box-notice box">
            <div class="hd">
              <h2>买家提醒</h2>
            </div>
            <div class="bd dealt">
              <div class="list clearfix">
                <dl>
                  <dd> <span><?php echo sprintf('<a href="index.php?app=buyer_order&type=pending">待付款订单<p><em>%s</em></p></a>', $this->_var['buyer_stat']['pending']); ?></span> </dd>
                </dl>
                <dl>
                  <dd> <span><?php echo sprintf('<a href="index.php?app=buyer_order&type=shipped">待确认的订单<p><em>%s</em></p></a>', $this->_var['buyer_stat']['shipped']); ?></span> </dd>
                </dl>
                <dl>
                  <dd><span><?php echo sprintf('<a href="index.php?app=buyer_order&type=finished">待评价的订单<p><em>%s</em></p></a>', $this->_var['buyer_stat']['finished']); ?></span> </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
        
        <div class="order-list box">
        	<div class="hd">
				<h2>我的订单 <a href="<?php echo url('app=buyer_order'); ?>">查看全部订单</a></h2>
          </div>
          <ul>
          	 <?php $_from = $this->_var['my_orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');$this->_foreach['fe_order'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_order']['total'] > 0):
    foreach ($_from AS $this->_var['order']):
        $this->_foreach['fe_order']['iteration']++;
?>
			  <li class="clearfix">
         		  <div class="order_goods">
         		  	 <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
					  <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['goods_image']; ?>" width="50" height="50"/></a>
         		  	 <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
         		  </div>
         		  <div class="price gray"><?php echo price_format($this->_var['order']['order_amount']); ?></div>
				  <div class="add_time gray"><?php echo local_date("Y-m-d H:i:s",$this->_var['order']['add_time']); ?></div>
				  <div class="status gray"><?php echo call_user_func("order_status",$this->_var['order']['status']); ?></div>
				  <div class="op"><a href="<?php echo url('app=buyer_order&act=view&order_id=' . $this->_var['order']['order_id']. ''); ?>">查看订单</a></div>
         	</li>
            <?php endforeach; else: ?>
            <div class="member-noorders">
            	<img src="<?php echo $this->res_base . "/" . 'images/orderEmptyBg.png'; ?>" />
                <div class="empty-tips">
                    <div class="empty-tips-title">您还没有订单</div>
                    <div class="empty-tips-text">好货这么多，快去买买买！</div>
                </div>
            </div>
          	<?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($this->_var['store'] && $this->_var['member_role'] == 'seller_admin'): ?>
        <div class="seller-notice">
          <div class="box-notice box">
            <div class="hd">
              <h2>卖家提醒</h2>
            </div>
            <div class="bd dealt">
              <div class="list clearfix">
                <dl>
                  <dd> <span><?php echo sprintf('<a href="index.php?app=seller_order&type=submitted">待处理的订单<p><em>%s</em></p></a>', $this->_var['seller_stat']['submitted']); ?></span></dd>
                </dl>
                <dl>
                  <dd> <span><?php echo sprintf('<a href="index.php?app=seller_order&type=accepted">待发货的订单<p><em>%s</em></p></a>', $this->_var['seller_stat']['accepted']); ?></span> </dd>
                </dl>
                <dl>
                  <dd> <span><?php echo sprintf('<a href="index.php?app=my_qa&type=to_reply_qa">待回复的商品咨询<p><em>%s</em></p></a>', $this->_var['seller_stat']['replied']); ?></span> </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?> 
        <?php if ($this->_var['_member_menu']['overview']): ?>
        <div class="apply-notice box-notice box">
          <div class="hd">
            <h2>开店提醒</h2>
          </div>
          <div class="bd">
            <div class="extra"> 
              <div class="notice-word">
                <p class="yellow">
              <?php if ($this->_var['applying']): ?> 
              <?php if ($this->_var['apply_remark']): ?> 
              <?php echo sprintf('您的店铺审核没通过，原因为：<span class="f60">%s</span>。你可以：<a href="index.php?app=apply&step=2&id=%s">点此修改</a>', $this->_var['apply_remark'],$this->_var['user']['sgrade']); ?> 
              <?php else: ?> 
              <?php echo sprintf('您的店铺正在审核中。你可以：<a href="index.php?app=apply&step=2&id=%s">查看或修改店铺信息</a>', $this->_var['user']['sgrade']); ?> 
              <?php endif; ?> 
              <?php else: ?>
              您目前不是卖家，您可以：<a href="<?php echo $this->_var['_member_menu']['overview']['url']; ?>" title="<?php echo $this->_var['_member_menu']['overview']['text']; ?>"><?php echo $this->_var['_member_menu']['overview']['text']; ?></a>
              <?php endif; ?> 
              </p>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?> 
        
        <?php if ($this->_var['store'] && $this->_var['member_role'] == 'seller_admin'): ?>
        <div class="rate-info box">
        	<div class="hd">
            <h2>店铺评分</h2>
          </div>
          <ul>
            <li> 商品评分 <span class="credit"><?php echo $this->_var['store']['avg_goods_evaluation']; ?></span> <span class="compare">与行业相比</span> <span class="<?php echo $this->_var['store']['industy_compare']['goods_compare']['class']; ?>"> <i></i> <?php echo $this->_var['store']['industy_compare']['goods_compare']['name']; ?> <em><?php if ($this->_var['store']['industy_compare']['goods_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>%<?php endif; ?></em></span> </li>
            <li> 服务评分 <span class="credit"><?php echo $this->_var['store']['avg_service_evaluation']; ?></span> <span class="compare">与行业相比</span> <span class="<?php echo $this->_var['store']['industy_compare']['service_compare']['class']; ?>"> <i></i> <?php echo $this->_var['store']['industy_compare']['service_compare']['name']; ?> <em><?php if ($this->_var['store']['industy_compare']['service_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>%<?php endif; ?></em> </span> </li>
            <li> 发货评分 <span class="credit"><?php echo $this->_var['store']['avg_shipped_evaluation']; ?></span> <span class="compare">与行业相比</span> <span class="<?php echo $this->_var['store']['industy_compare']['shipped_compare']['class']; ?>"> <i></i> <?php echo $this->_var['store']['industy_compare']['shipped_compare']['name']; ?> <em><?php if ($this->_var['store']['industy_compare']['shipped_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['shipped_compare']['value']; ?>%<?php endif; ?></em> </span> </li>
            
            
            <li> 综合评分 <span class="credit" style="color:#f50;"><?php echo ($this->_var['store']['avg_evaluation'] == '') ? '0' : $this->_var['store']['avg_evaluation']; ?></span></li>
            
          </ul>
        </div>
        <?php endif; ?> 
        
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 