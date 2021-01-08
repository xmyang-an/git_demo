<script type="text/javascript">
//<!CDATA[
/* buy */
function buy()
{
    if (goodsspec.getSpec() == null)
    {
        layer.open({content:lang.select_specs, time:2});
        return;
    }
    var spec_id = goodsspec.getSpec().id;

    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        layer.open({content:lang.input_quantity, time: 2});
        return;
    }
    if (parseInt(quantity) < 1 || isNaN(quantity))
    {
        layer.open({content:lang.invalid_quantity, time: 2});
        return;
    }

    add_to_cart(spec_id, quantity);
}

/* add cart */
function add_to_cart(spec_id, quantity)
{
    var url = REAL_SITE_URL + '/index.php?app=cart&act=add';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
    	if (data.done)
    	{
			<?php if ($this->_var['goods']['spec_name_1'] || $this->_var['goods']['spec_name_2']): ?>
			$('.close-spec-pop').click();
			<?php endif; ?>
			layer.open({content:lang.success_add_to_cart, className:'layer-popup',time: 2});
        	return;
   	 	}
    	else
    	{
       		layer.open({content:data.msg, time: 2});
    	}
    })
}
/*buy_now*/
function buy_now()
{
    //验证数据
	if (goodsspec.getSpec() == null)
    {
        layer.open({content:lang.select_specs, time: 2});
        return;
    }
    var spec_id = goodsspec.getSpec().id;
 
    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        layer.open({content:lang.input_quantity, time: 2});
        return;
    }
    if (parseInt(quantity) < 1 || isNaN(quantity))
    {
        layer.open({content:lang.invalid_quantity, time: 2});
        return;
    }
    buy_now_add_cart(spec_id, quantity);
}

/* add buy_now_add_cart */
function buy_now_add_cart(spec_id, quantity)
{
    var url = REAL_SITE_URL + '/index.php?app=cart&act=add&selected=1';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
		if (data.done)
        {
			location.href= REAL_SITE_URL + '/index.php?app=order&goods=cart';
        }else{
            layer.open({content:data.msg, time: 2});
        }
    });
}
var specs = new Array();
<?php $_from = $this->_var['goods']['_specs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'spec');if (count($_from)):
    foreach ($_from AS $this->_var['spec']):
?>
specs.push(new spec(<?php echo $this->_var['spec']['spec_id']; ?>, '<?php echo htmlspecialchars($this->_var['spec']['spec_1']); ?>', '<?php echo htmlspecialchars($this->_var['spec']['spec_2']); ?>', '<?php echo htmlspecialchars($this->_var['spec']['spec_image']); ?>', <?php echo $this->_var['spec']['price']; ?>, <?php echo $this->_var['spec']['stock']; ?>, <?php echo $this->_var['goods']['goods_id']; ?>));
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

var specQty = <?php echo $this->_var['goods']['spec_qty']; ?>;
var defSpec = <?php echo htmlspecialchars($this->_var['goods']['default_spec']); ?>;
var goodsspec = new goodsspec(specs, specQty, defSpec);

$(function(){
	
	$('.J_SelectSpecLayer').find('.handle').css('top', $('.J_SelectSpecLayer').find('.info').height()+20);

	/* 商品图切换 */
	TouchSlide({slideCell:"#slides",titCell:".hd",mainCell:".bd",effect:"leftLoop", autoPlay:true,autoPage:true, titOnClassName:"active", delayTime:1000, interTime: 5000});
	
	/* 抢购倒计时 */
	$.each($('.countdown'),function(){
		var theDaysBox  = $(this).find('.NumDays');
		var theHoursBox = $(this).find('.NumHours');
		var theMinsBox  = $(this).find('.NumMins');
		var theSecsBox  = $(this).find('.NumSeconds');
			
		countdown(theDaysBox, theHoursBox, theMinsBox, theSecsBox)
	});
	 
	 
	 $('.handle .selected').click(function(){
		 $(this).parent().find('.J_hidden').toggle();
		var cl = $(this).find('span').attr('class');
		if(cl == 'icon-arr')
		{
			$(this).find('span').attr('class','icon-arr-on');
		}
		else
		{
			$(this).find('span').attr('class','icon-arr');
		}
	})
	$('.change-quality em').click(function(){
		var type = $(this).attr('class');
		var _v = Number($('#quantity').val());
		var stock = Number($('*[ectype="goods_stock"]').text());
		if(type == 'plus')
		{
			if(_v > 1)
			{
				$('#quantity').val(_v-1);
			}
		}
		else if(_v < stock) {
			$('#quantity').val(_v+1);
		}else{
			layer.open({content:"没有足够的商品", time: 5});
		}
	});
		
	$('.change-quality #quantity').keyup(function(){
		var _v = Number($('#quantity').val());
		var stock = Number($('*[ectype="goods_stock"]').text());
		if(_v > stock){ 
			layer.open({content:"没有足够的商品", time: 5});
			$(this).val(stock);
		}
		if(_v < 1 || isNaN(_v)) {
			layer.open({content:lang.invalid_quantity, time: 5});
			$(this).val(1);
		}
	});

	$('.J_GoBuy').popLayer({
		popLayer : '.J_SelectSpecLayer',
		top: '20%',
		//fixedBody: true,
		callback : function(e){
			var type = e.attr('ectype');
			$('.J_BtnConfirm').find('.'+type).show().siblings().hide();
		}
	});
	
	<?php if ($this->_var['signPackage']): ?>
	wxshare({signPackage: <?php echo $this->_var['signPackage']; ?>, content: {desc: '<?php echo $this->_var['site_title']; ?>', imgUrl:'<?php echo $this->_var['site_url']; ?>/<?php echo ($this->_var['goods']['default_image'] == '') ? $this->_var['default_image'] : $this->_var['goods']['default_image']; ?>'}});
	<?php endif; ?>
	
});

//]]>
</script>

<div class="goods-detail">
  <div class="col-img relative" style="max-width:640px; margin:0 auto;">
    <div class="scroll-wrapper">
      <div id="slides" class="scroller">
        <ul class="bd clearfix">
          <?php if ($this->_var['goods']['_images']): ?> 
          <?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_image');$this->_foreach['fe_goods_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods_image']['total'] > 0):
    foreach ($_from AS $this->_var['goods_image']):
        $this->_foreach['fe_goods_image']['iteration']++;
?>
          <li><img src="<?php echo $this->_var['site_url']; ?>/<?php echo ($this->_var['goods_image']['image_url'] == '') ? $this->_var['default_image'] : $this->_var['goods_image']['image_url']; ?>" /></li>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
          <?php else: ?>
          <li><img src="<?php echo $this->_var['site_url']; ?>/<?php echo ($this->_var['goods']['default_image'] == '') ? $this->_var['default_image'] : $this->_var['goods']['default_image']; ?>" /></li>
          <?php endif; ?>
        </ul>
        <ul class="hd">
        </ul>
      </div>
    </div>
  </div>
  <div class="J_IsPro is-pro hidden">
    <div class="wraper">
      <div class="lp clearfix">
        <div class="pro-price" ectype="goods_pro_price"></div>
        <div class="extra-info"> <em><del ectype="goods_price"><?php echo price_format($this->_var['goods']['price']); ?></del></em> <span><ins><?php echo ($this->_var['goods']['sales'] == '') ? '0' : $this->_var['goods']['sales']; ?></ins>件已售</span> </div>
      </div>
      <?php if ($this->_var['goods']['lefttime']): ?>
      <div class="rp">
        <p class="t">距结束仅剩</p>
        <p class="J_CountDown countdown clearfix"><span class="tm NumDays"><?php echo $this->_var['goods']['lefttime']['d']; ?></span><em>:</em> <span class="tm NumHours"><?php echo $this->_var['goods']['lefttime']['h']; ?></span><em>:</em><span class="tm NumMins"><?php echo $this->_var['goods']['lefttime']['m']; ?></span><em>:</em><span class="tm NumSeconds"><?php echo $this->_var['goods']['lefttime']['s']; ?></span></p>
      </div>
      <?php endif; ?>
      <?php if (! $this->_var['goods']['lefttime']): ?>
      <div class="rp J_ProType-exclusive protype-exclusive hidden"> 手机下<Br />单立享</div>
      <?php endif; ?>
      
    </div>
  </div>
  <div class="col-title">
    <div class="title"> <span class="fs13"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></span> <font class="gray"><?php $_from = $this->_var['goods']['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?><?php echo $this->_var['tag']; ?>&nbsp;&nbsp;&nbsp;<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></font> </div>
  </div>
  <div class="col-price clearfix">
    <div class="J_IsNotPro is-no-pro"  style="padding:0px 10px 0 10px;">
      <div class="yahei normal-price" ectype="goods_price"><?php echo price_format($this->_var['goods']['price']); ?></div>
    </div>
    <p class="padding10 extra clearfix col-title"> <span> <?php if ($this->_var['goods']['default_logist']): ?>
      <?php echo $this->_var['goods']['default_logist']['name']; ?>：<?php if ($this->_var['goods']['default_logist']['start_fees'] > 0): ?><?php echo $this->_var['goods']['default_logist']['start_fees']; ?><?php else: ?>免运费<?php endif; ?>
      <?php endif; ?> </span> <span class="center">销量：<?php echo $this->_var['goods']['sales']; ?> 件</span> <span class="float-right" style="text-align:right"><?php echo htmlspecialchars($this->_var['store']['goodsAddress']); ?></span></p>
  </div>
  
  <?php if ($this->_var['coupons']): ?>
  <div class="line-background"></div>
  <div class="col-title col-coupon" data-PopLayer="{popLayer:'.J_GetCouponPopLayer',top:'20%'}">
    <div class="title clearfix webkit-box">
      <p class="lp">优惠券</p>
      <em class="flex1 fs12 ml10">领取优惠券</em> <a class="btn2 btn-getcoupon" href="javascript:;" ectype="dialog" dialog_id="coupon" dialog_width="400" dialog_title="领取优惠券" uri="<?php echo url('app=coupon&act=search&store_id=' . $this->_var['goods']['store_id']. '&ajax='); ?>" id="coupon" dialog_class="simple-blue" dialog_opacity="0.5" dialog_position="bottom">领取</a></div>
  </div>
  <?php endif; ?> 
  
  <?php if ($this->_var['goods']['exchange_price'] || $this->_var['promotool']['storeFullfreeInfo'] || $this->_var['promotool']['storeFullPreferInfo'] || $this->_var['promotool']['storeFullGiftList'] || $this->_var['promotool']['goodsGrowbuyList']): ?>
  <div class="col-title col-promotool webkit-box J_PopLayer" <?php if ($this->_var['buyIntegral']['price']): ?>style="border-bottom:1px #eee solid"<?php endif; ?> data-PopLayer="{popLayer:'.J_PromotoolPopLayer',top:'35%'}">
    <div class="title padding10 flex1 clearfix"> 
      <?php if ($this->_var['goods']['exchange_price']): ?> 
      <span><i class="psmb-icon-font f60 mr5">&#xe614;</i>积分抵扣</span> 
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullfreeInfo']): ?> 
      <span><i class="psmb-icon-font f60 mr5">&#xe614;</i>满包邮</span> 
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullPreferInfo']): ?> 
      <span><i class="psmb-icon-font f60 mr5">&#xe614;</i>满折满减</span> 
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullGiftList']): ?> 
      <span><i class="psmb-icon-font f60 mr5">&#xe614;</i>赠品</span> 
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['goodsGrowbuyList']): ?> 
      <span><i class="psmb-icon-font f60 mr5">&#xe614;</i>加价购</span> 
      <?php endif; ?> 
    </div>
    <div class="dotted box-align-center mr10"><i class="psmb-icon-font f99">&#xe634;</i></div>
  </div>
  <?php endif; ?> 
  <?php if ($this->_var['buyIntegral']['price']): ?>
  <div class="col-title col-promotool webkit-box J_PopLayer" data-PopLayer="{popLayer:'.J_GetIntegralPopLayer',top:'35%'}">
    <div class="title padding10 flex1 clearfix getintegral"> <span><s class="f60 mr5">积分</s>购买可得 <i class="J_BuyIntegralNum f60" data-value="<?php echo $this->_var['buyIntegral']['radio']; ?>"></i> 积分</span> </div>
    <div class="dotted box-align-center mr10"><i class="psmb-icon-font f99">&#xe634;</i></div>
  </div>
  <?php endif; ?> 
  
  <?php if ($this->_var['goods']['spec_name_1'] || $this->_var['goods']['spec_name_2']): ?>
  <div class="line-background"></div>
  <div class="col-title webkit-box J_GoBuy"  ectype="buy">
    <p class="padding10 flex1 fs13">请您选择：<?php echo htmlspecialchars($this->_var['goods']['spec_name_1']); ?> <?php echo htmlspecialchars($this->_var['goods']['spec_name_2']); ?></p>
    <div class="dotted box-align-center mr10"> <i class="psmb-icon-font f99">&#xe634;</i> </div>
  </div>
  <?php endif; ?>
  <?php if ($this->_var['props']): ?>
  <div class="col-title webkit-box border-top J_PopLayer" data-PopLayer="{popLayer:'.J_GoodsPropsPopLayer',top:'20%'}">
    <p class="padding10 flex1 fs13">产品参数</p>
    <div class="dotted box-align-center mr10"><i class="psmb-icon-font f99">&#xe634;</i> </div>
  </div>
  <?php endif; ?>
  <?php if ($this->_var['goods']['has_meal']): ?>
  <div class="col-title webkit-box border-top">
    <p class="padding10 flex1 fs13"><a style="display:block" href="<?php echo url('app=meal&goods_id=' . $this->_var['goods']['goods_id']. ''); ?>">搭配购买</a></p>
    <div class="dotted box-align-center mr10"><i class="psmb-icon-font f99">&#xe634;</i> </div>
  </div>
  <?php endif; ?>
  <?php if ($this->_var['goods_qas']['list']): ?>
  <div class="floor qas">
    <div class="mt"> <em class="vline vleft"></em> <span class="fs12"><i class="psmb-icon-font mr5 fs14">&#xe6e0;</i>问答</span> <em class="vline vright"></em> </div>
    <div class="mc pt10 pr10 pl10"> 
      <?php $_from = $this->_var['goods_qas']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qa');$this->_foreach['fe_qa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_qa']['total'] > 0):
    foreach ($_from AS $this->_var['qa']):
        $this->_foreach['fe_qa']['iteration']++;
?>
      <div class="item mb20">
        <div class="us-name clearfix">
          <p><ins class="green">问</ins><?php echo $this->_var['qa']['question_content']; ?></p>
        </div>
        <div class="us-content mt10">
          <ins class="f60">答</ins><span class="mt10 mb10"><?php echo $this->_var['qa']['reply_content']; ?></span>
          <p class="gray mt10 ml10 pl10"><?php echo local_date("Y-m-d",$this->_var['comment']['time_reply']); ?></p>
        </div>
      </div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
    </div>
    <div class="mb center pb20"><a href="<?php echo url('app=goods&act=qa&id=' . $this->_var['goods']['goods_id']. ''); ?>">查看全部问答</a></div>
  </div>
  <?php endif; ?>
  
  <?php if ($this->_var['goods_comments']['list']): ?>
  <div class="floor comments">
    <div class="mt"> <em class="vline vleft"></em> <span class="fs12"><i class="psmb-icon-font mr5 fs14">&#xe6e0;</i>评价</span> <em class="vline vright"></em> </div>
    <div class="mc pt10 pr10 pl10">
      <p class="tips webkit-box flex-wrap pb5">
      	 <a class="gray"><span>全部(<?php echo $this->_var['statistics']['total_count']; ?>)</span></a>
         <a class="gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=4'); ?>"><span>有图(<?php echo $this->_var['statistics']['share_count']; ?>)</span></a>
         <a class="gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=3'); ?>"><span>好评(<?php echo $this->_var['statistics']['good_count']; ?>)</span></a>
         <a class="gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=2'); ?>"><span>中评(<?php echo $this->_var['statistics']['middle_count']; ?>)</span></a>
         <a  class="gray" href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=1'); ?>"><span>差评(<?php echo $this->_var['statistics']['bad_count']; ?>)</span></a>
        <?php $_from = $this->_var['eval_tips']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tip');if (count($_from)):
    foreach ($_from AS $this->_var['tip']):
?>
        <a href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&tip='); ?><?php echo urlencode($this->_var['tip']['tip']); ?>#module"><?php echo htmlspecialchars($this->_var['tip']['tip']); ?>(<?php echo $this->_var['tip']['count']; ?>)</a>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </p>
      <div class="list"> 
          <?php $_from = $this->_var['goods_comments']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment');$this->_foreach['fe_comment'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_comment']['total'] > 0):
    foreach ($_from AS $this->_var['comment']):
        $this->_foreach['fe_comment']['iteration']++;
?>
          <div class="item mb20" <?php if (($this->_foreach['fe_comment']['iteration'] == $this->_foreach['fe_comment']['total'])): ?>style="border-bottom:0;"<?php endif; ?>>
          	<div class="tp">
                <div class="us-name clearfix">
                  <p><img src="<?php echo $this->_var['comment']['portrait']; ?>" width="25" height="25" /></p>
                  <p class="font">
                    <?php if ($this->_var['comment']['anonymous']): ?>anonymous<?php else: ?><?php echo htmlspecialchars($this->_var['comment']['buyer_name']); ?><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] > 0): ?><i class="psmb-icon-font f60">&#xe651;</i><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] > 1): ?><i class="psmb-icon-font f60">&#xe651;</i><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] > 2): ?><i class="psmb-icon-font f60">&#xe651;</i><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] < 3): ?><i class="psmb-icon-font gray">&#xe651;</i><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] < 2): ?><i class="psmb-icon-font gray">&#xe651;</i><?php endif; ?>
                    <?php if ($this->_var['comment']['evaluation'] < 1): ?><i class="psmb-icon-font gray">&#xe651;</i><?php endif; ?>
                 </p>
                </div>
                <div class="us-content"> <span class="mt10 mb10 block line-clamp-2"><?php echo $this->_var['comment']['comment']; ?></span> </div>
            </div>
            <div class="bp">
                <div class="col-size webkit-box pt10">
                    <p class="flex1"><?php echo $this->_var['comment']['specification']; ?></p>
                    <p><?php echo local_date("Y-m-d",$this->_var['comment']['evaluation_time']); ?></p>
                 </div>
             </div>
          </div>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
      </div>
    </div>
    <div class="mb center pb20"><a href="<?php echo url('app=goods&act=comments&id=' . $this->_var['goods']['goods_id']. ''); ?>">查看全部评价</a></div>
  </div>
  <?php endif; ?>
  <div class="line-background"></div>
  <div class="store-info margin10">
    	<div class="store-to clearfix">
        	<a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><img width="50" height="50" src="<?php echo $this->_var['store']['store_logo']; ?>" /></a>
            <div class="col-size">
            	<p class="fs14"><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><?php echo $this->_var['store']['store_name']; ?></a></p>
            	<p><?php if ($this->_var['store']['credit_value'] >= 0): ?><img src="<?php echo $this->_var['store']['credit_image']; ?>" alt="" /><?php endif; ?></p>
            </div>
        </div>
        
        <div class="store_bo">
        	<ul class="webkit-box">
            	<li>
                	<p><?php echo ($this->_var['store']['goods_count'] == '') ? '0' : $this->_var['store']['goods_count']; ?></p>
                    <span class="padd-top">全部宝贝</span>
                </li>
                <li>
                	<p><?php echo ($this->_var['store']['be_collect'] == '') ? '0' : $this->_var['store']['be_collect']; ?></p>
                    <span class="padd-top">关注人数</span>
                </li>
                <li>
                    <div class="service">商品评分<span class="<?php echo $this->_var['store']['industy_compare']['goods_compare']['class']; ?>"><?php echo $this->_var['store']['avg_goods_evaluation']; ?><i><?php echo $this->_var['lang'][$this->_var['store']['industy_compare']['goods_compare']['class']]; ?></i></span></div>
                    <div class="service">服务评分<span class="<?php echo $this->_var['store']['industy_compare']['service_compare']['class']; ?>"><?php echo $this->_var['store']['avg_service_evaluation']; ?><i><?php echo $this->_var['lang'][$this->_var['store']['industy_compare']['service_compare']['class']]; ?></i></span></div>
                    <div class="service">发货评分<span class="<?php echo $this->_var['store']['industy_compare']['service_compare']['class']; ?>"><?php echo $this->_var['store']['avg_shipped_evaluation']; ?><i><?php echo $this->_var['lang'][$this->_var['store']['industy_compare']['service_compare']['class']]; ?></i></span></div>
                </li>
            </ul>
            
            <div class="get-into">
            	<a href="<?php echo url('app=store&act=category&id=' . $this->_var['store']['store_id']. ''); ?>" class="fs12">查看分类</a>
                <a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" class="fs12">进店逛逛</a>
            </div>
        </div>
    </div>
</div>
<div class="pop-layer-common pop-wrap-b pop-select-spec J_SelectSpecLayer">
  <div class="wraper">
    <div class="bd">
      <div class="info clearfix"> <img src="<?php echo $this->_var['goods']['default_image']; ?>" height="80" width="80" />
        <div class="goods-attr flex1">
          <p class="clearfix J_IsPro"> <span class="promo-price mr10 float-left yahei pri" ectype="goods_pro_price"></span> <del ectype="goods_price" class="float-left fff yahei"><?php echo price_format($this->_var['goods']['price']); ?></del> </p>
          <p class="J_IsNotPro"> <span class="yahei fw-normal pri" ectype="goods_price"><?php echo price_format($this->_var['goods']['price']); ?></span> </p>
          <p><i class="gray">库存 <span class="stock gray" ectype="goods_stock"><?php echo $this->_var['goods']['_specs']['0']['stock']; ?></span>件</i></p>
          <p>您已选择:<span class="aggregate" ectype="current_spec"></span></p>
        </div>
      </div>
      <div class="close-pop popClosed">&#xe670;</div>
      <div class="handle"> 
        <?php if ($this->_var['goods']['spec_qty'] > 0): ?>
        <ul class="clearfix w-full J_hidden mb5">
          <li class="handle_title"><?php echo htmlspecialchars($this->_var['goods']['spec_name_1']); ?> </li>
        </ul>
        <?php endif; ?> 
        <?php if ($this->_var['goods']['spec_qty'] > 1): ?>
        <ul class="clearfix w-full J_hidden mb5">
          <li class="handle_title"><?php echo htmlspecialchars($this->_var['goods']['spec_name_2']); ?></li>
        </ul>
        <?php endif; ?>
        <ul class="clearfix w-full mb10">
          <li class="handle_title mr5">购买数量 </li>
          <li class="change-quality"> <em class="plus"><b><i class="psmb-icon-font">&#xe633;</i></b></em>
            <input type="text" class="text width1" name="quantity" id="quantity" value="1" />
            <em class="add"><b><i class="psmb-icon-font">&#xe632;</i></b></em> </li>
        </ul>
      </div>
    </div>
    <div class="ft">
      <div class="confirm-btn J_BtnConfirm"> <a href="javascript:;" onclick="buy_now();" class="buy-now pop-btn">确定</a> <a href="javascript:;" onclick="buy();" class="buy pop-btn">确定</a> </div>
    </div>
  </div>
</div>

<?php if ($this->_var['goods']['exchange_price'] || $this->_var['promotool']['storeFullfreeInfo'] || $this->_var['promotool']['storeFullPreferInfo'] || $this->_var['promotool']['storeFullGiftList'] || $this->_var['promotool']['goodsGrowbuyList']): ?>
<div class="pop-layer-common promotool-pop-layer J_PromotoolPopLayer">
  <div class="wraper">
    <div class="bd"> 
      <?php if ($this->_var['goods']['exchange_price']): ?>
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>积分抵扣</dt>
        <dd class="flex1 overflow-ellipsis">可使用 <?php echo $this->_var['goods']['max_exchange']; ?> 积分抵 <?php echo $this->_var['goods']['exchange_price']; ?> 元</dd>
      </dl>
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullfreeInfo']): ?>
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>满包邮</dt>
        <dd class="flex1 overflow-ellipsis"><?php echo $this->_var['promotool']['storeFullfreeInfo']; ?></dd>
      </dl>
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullPreferInfo']): ?>
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>满折满减</dt>
        <dd class="flex1 overflow-ellipsis"><?php echo $this->_var['promotool']['storeFullPreferInfo']; ?></dd>
      </dl>
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['storeFullGiftList']): ?>
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>赠品</dt>
        <dd class="flex1 overflow-ellipsis"> 
          <?php $_from = $this->_var['promotool']['storeFullGiftList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'fullgift');$this->_foreach['fe_fullgift'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_fullgift']['total'] > 0):
    foreach ($_from AS $this->_var['fullgift']):
        $this->_foreach['fe_fullgift']['iteration']++;
?>
          <div class="ditem">购物满<?php echo $this->_var['fullgift']['amount']; ?>元获赠：<?php $_from = $this->_var['fullgift']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?> <a href="<?php echo url('app=gift&id=' . $this->_var['item']['goods_id']. ''); ?>" class="inline-block"><?php echo $this->_var['item']['goods_name']; ?></a><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </dd>
      </dl>
      <?php endif; ?> 
      <?php if ($this->_var['promotool']['goodsGrowbuyList']): ?>
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>加价购</dt>
        <dd class="flex1 overflow-ellipsis"> 
          <?php $_from = $this->_var['promotool']['goodsGrowbuyList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'growbuy');$this->_foreach['fe_growbuy'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_growbuy']['total'] > 0):
    foreach ($_from AS $this->_var['growbuy']):
        $this->_foreach['fe_growbuy']['iteration']++;
?>
          <div class="ditem">加<?php echo $this->_var['growbuy']['money']; ?> 元可购买<?php $_from = $this->_var['growbuy']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?><a href="<?php echo url('app=goods&id=' . $this->_var['item']['goods_id']. ''); ?>"><?php echo $this->_var['item']['goods_name']; ?></a><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </div>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </dd>
      </dl>
      <?php endif; ?> 
    </div>
    <div class="ft pop-btn popClosed">完成</div>
  </div>
</div>
<?php endif; ?> 
<?php if ($this->_var['buyIntegral']['price']): ?>
<div class="pop-layer-common promotool-pop-layer J_GetIntegralPopLayer">
  <div class="wraper">
    <div class="bd">
      <dl class="clearfix">
        <dt><i class="psmb-icon-font f60 mr5">&#xe614;</i>购物送积分</dt>
        <dd class="flex1 overflow-ellipsis">购买商品可获得 <i class="J_BuyIntegralNum f60" data-value="<?php echo $this->_var['buyIntegralRadio']; ?>"></i> 积分</dd>
      </dl>
    </div>
    <div class="ft pop-btn popClosed">完成</div>
  </div>
</div>
<?php endif; ?>

<?php if ($this->_var['props']): ?>
<div class="pop-layer-common goodsprops-pop-layer J_GoodsPropsPopLayer">
  <div class="wraper has-title">
    <div class="hd"><i class="closed popClosed"></i>产品参数</div>
    <div class="bd padding10">
      <?php $_from = $this->_var['props']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');$this->_foreach['fe_prop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_prop']['total'] > 0):
    foreach ($_from AS $this->_var['prop']):
        $this->_foreach['fe_prop']['iteration']++;
?>
      <dl class="<?php if (! ($this->_foreach['fe_prop']['iteration'] == $this->_foreach['fe_prop']['total'])): ?>border-bottom<?php endif; ?> pt10 pb10 clearfix">
        <dt class="float-left"><?php echo $this->_var['prop']['name']; ?>：</dt>
        <dd class="float-left"><?php echo $this->_var['prop']['value']; ?></dd>
      </dl>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
    <div class="ft pop-btn popClosed">完成</div>
  </div>
</div>
<?php endif; ?>

<div class="btn-fixed clearfix iphonex">
  <div class="small-ico clearfix">
    <div class="ico-it"> <a  href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" class="btn-to-cart yahei"> <em style="color:#DD2727;">&#xe656;</em><br />
      进店 </a> </div>
    <div class="ico-it"> <a href="javascript:;" class="float-webim J_StartLayim" data-toid="<?php echo $this->_var['store']['store_id']; ?>"> <em>&#xe642;</em><br />
      客服 </a> </div>
    <div class="ico-it"> <a  href="javascript:;" class="J_AjaxRequest btn-to-cart yahei <?php if ($this->_var['goods']['collected']): ?>collected<?php endif; ?>" action="<?php if ($this->_var['goods']['collected']): ?><?php echo url('app=my_favorite&act=drop&type=goods&item_id=' . $this->_var['goods']['goods_id']. '&ajax=1'); ?><?php else: ?><?php echo url('app=my_favorite&act=add&type=goods&item_id=' . $this->_var['goods']['goods_id']. '&ajax=1'); ?><?php endif; ?>"> <?php if ($this->_var['goods']['collected']): ?><em>&#xe615;</em><br />
      已收藏
      <?php else: ?><em>&#xe616;</em><br />
      收藏<?php endif; ?> </a> </div>
  </div>
  <div class="large-btn clearfix">
    <div class="btn-it it1"><a ectype="buy-now" href="javascript:;" <?php if ($this->_var['goods']['spec_name_1'] || $this->_var['goods']['spec_name_2']): ?>class="btn-buy yahei J_GoBuy"<?php else: ?>class="btn-buy yahei" onclick="buy_now();"<?php endif; ?>>立刻购买</a></div>
    <div class="btn-it it2"><a ectype="buy" href="javascript:;" <?php if ($this->_var['goods']['spec_name_1'] || $this->_var['goods']['spec_name_2']): ?>class="J_GoBuy btn-cart yahei"<?php else: ?>class="btn-cart yahei" onclick="buy();"<?php endif; ?>>加入购物车</a></div>
  </div>
</div>