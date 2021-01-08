<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div class="page-store page J_page">
    <div class="page-body store-limitbuy">
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
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><span>首页</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. ''); ?>"><span>全部商品</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. '&new=1'); ?>"><span>上新</span></a></li>
          <li class="flex1"><a class="block center active fs14" href="<?php echo url('app=store&act=limitbuy&id=' . $this->_var['store']['store_id']. ''); ?>"><span>促销</span></a></li>
          
        </ul>
      </div>
      
      <div class="border-top">
      
      
      <div class="list goods-list clearfix J_InfiniteList">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <div class="infinite-template">
          <li class="pt10 pb10"> <a href="{1}">
            <dl class="webkit-box">
              <dt><img src="{2}"  /></dt>
              <dd class="flex1 ml10 mr10">
                <p class="name line-clamp-2">{3}</p>
                
                <div class="countdown waiting mt5"> <span class="time NumDays" >{4}</span><em>天</em> <span class="time NumHours">{5}</span><em>小时</em> <span class="time NumMins" >{6}</span><em>分</em> <span class="time NumSeconds">{7}</span><em>秒</em> </div>

                <div class="mt5"><span class="pro-name"><em>{8}</em></span></div>
                <div class="extra mt5">
                  <div class="lp"><em class="price mr10">{9}</em><del>{10}</del></div>
                </div>
              </dd>
            </dl>
            </a> </li>
          
        </div>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>更多优惠将上线，记得关注我们哦<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i>
          <p>很抱歉! 没有找到相关商品</p>
        </div>
      </div>
      
      
        
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
	$('.J_InfiniteList').infinite({pageper: 10, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			var template = TEMP.clone(true);
			$.each(data, function(k, goods) {
				html += sprintf(template.html(), "<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, goods.goods_name, goods.lefttime.d, goods.lefttime.h, goods.lefttime.m, goods.lefttime.s, goods.pro_name, price_format(goods.pro_price), price_format(goods.price));
			});
			target.find('.infinite-result').append(html).show();
			
			$.each($('.countdown.waiting'), function(index, element) {
				var theDaysBox  = $(this).find('.NumDays');
				var theHoursBox = $(this).find('.NumHours');
				var theMinsBox  = $(this).find('.NumMins');
				var theSecsBox  = $(this).find('.NumSeconds');	
				countdown(theDaysBox, theHoursBox, theMinsBox, theSecsBox)
			});
			// 避免下拉刷新后，多个倒计时作用
			target.find('.infinite-result').find('.countdown').removeClass('waiting');
		}
	});
	
});
</script> 
<?php echo $this->fetch('footer.html'); ?> 