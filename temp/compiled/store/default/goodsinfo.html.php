<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.plugins/fresco/fresco.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'zoom/mzp-packed.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.plugins/raty/jquery.raty.js'; ?>" charset="utf-8"></script>
<link href="<?php echo $this->lib_base . "/" . 'jquery.plugins/fresco/fresco.css'; ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'goodsinfo.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
//<!CDATA[
/* buy */
function buy()
{
    if (goodsspec.getSpec() == null)
    {
        alert(lang.select_specs);
        return;
    }
    var spec_id = goodsspec.getSpec().id;

    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        alert(lang.input_quantity);
        return;
    }
    if (parseInt(quantity) < 1 || isNaN(quantity))
    {
        alert(lang.invalid_quantity);
        return;
    }
    add_to_cart(spec_id, quantity);
}

/* add cart */
function add_to_cart(spec_id, quantity)
{
    var url = SITE_URL + '/index.php?app=cart&act=add';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
        if (data.done)
        {
			var cartItem=$(".header_cart");
			var img = $(".main_img");
			var newImg = img.clone().addClass('img-clone').css({"top": $('.add-to-cart i').offset().top, "left": $('.add-to-cart i').offset().left}).show();
			newImg.appendTo("body").animate({top:cartItem.offset().top, left: cartItem.offset().left, width: 55, height:55}, {duration: 1500,  complete: setInterval(function(){newImg.remove();},2000)});
			setTimeout(function(){
				$('.J_C_T_GoodsKinds').text(data.retval.cart.kinds);
				$('.J_C_T_Amount').html(price_format(data.retval.cart.amount));
				$('.J_NoGoods').hide();
				var html = '';
				$.each(data.retval.cart_goods,function(a,b){
					$.each(b.goods,function(k,v){
						html = html + '<div  class="clearfix list J_CartItem-'+v.rec_id+'">'+
							 '<div class="goods-img">'+
							 "<a href='<?php echo url('app=goods&id="+v.goods_id+"'); ?>' target='_blank'><img alt='"+v.goods_name+"' src='"+v.goods_image+"' width='40' height='40'></a>"+
							'</div>'+
							'<div class="goods-title">'+
								"<a title='"+v.goods_name+"' href='<?php echo url('app=goods&id="+v.goods_id+"'); ?>' target='_blank'>"+v.goods_name+"</a>"+
							'</div>'+
							'<div class="goods-admin">'+
								'<div class="mini-cart-count"><strong class="mini-cart-price">&yen;'+v.price+'</strong> x'+v.quantity+'</div>'+
								'<div class="mini-cart-del"><a href="javascript:;" onclick="drop_cart_item('+v.store_id+', '+v.rec_id+');">删除</a></div>'+
							'</div>'+
						'</div>';
					});
				});
				$('.J_HasGoods').html('<div class="goods-list"><h4>最新加入的商品</h4>'+html+'<div class="total"> <span>共<strong class="J_C_T_GoodsKinds">'+data.retval.cart.kinds+'</strong>件商品</span><span>共计<strong class="J_C_T_Amount">'+price_format(data.retval.cart.amount)+'</strong></span><br /><a href="<?php echo url('app=cart'); ?>">去购物车结算</a> </div></div>');
				
			},2800);
			
        }
        else
        {
            alert(data.msg);
        }
    });
}
/*buy_now*/
function buy_now()
{
    //验证数据
	if (goodsspec.getSpec() == null)
    {
        alert(lang.select_specs);
        return;
    }
    var spec_id = goodsspec.getSpec().id;
 
    var quantity = $("#quantity").val();
    if (quantity == '')
    {
        alert(lang.input_quantity);
        return;
    }
    if (parseInt(quantity) < 1 || isNaN(quantity))
    {
        alert(lang.invalid_quantity);
        return;
    }
    buy_now_add_cart(spec_id, quantity);
}

/* add buy_now_add_cart */
function buy_now_add_cart(spec_id, quantity)
{
    var url = SITE_URL + '/index.php?app=cart&act=add&selected=1';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity}, function(data){
		if (data.done)
        {
			location.href= SITE_URL + '/index.php?app=order&goods=cart';
        }else{
            alert(data.msg);
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
//]]>
$(function() {
	$.fn.raty.defaults.path = SITE_URL + '/static/images/';
	$('#evaluation').raty({ readOnly: true, score:<?php echo ($this->_var['goods']['avg_g_eva'] == '') ? '0' : $this->_var['goods']['avg_g_eva']; ?>});
	
	$('.buy-quantity a').click(function(){
		var type = $(this).attr('change');
		var _v = Number($('#quantity').val());
		var stock = Number($('*[ectype="goods_stock"]').text());
		if(type == 'reduce')
		{
			if(_v > 1)
			{
				$('#quantity').val(_v-1);
			}
		}
		else if(_v < stock) {
			$('#quantity').val(_v+1);
		}else{
			alert('没有足够的商品');
		}
	});
	
	$('.buy-quantity #quantity').keyup(function(){
		var _v = Number($('#quantity').val());
		var stock = Number($('*[ectype="goods_stock"]').text());
		if(_v > stock){ 
			alert('没有足够的商品');
			$(this).val(stock);
		}
		if(_v < 1 || isNaN(_v)) {
			alert(lang.invalid_quantity);
			$(this).val(1);
		}
	});
	
	/* 促销倒计时 */
	$.each($('.countdown'),function(){
		var theDaysBox  = $(this).find('.NumDays');
		var theHoursBox = $(this).find('.NumHours');
		var theMinsBox  = $(this).find('.NumMins');
		var theSecsBox  = $(this).find('.NumSeconds');
			
		countdown(theDaysBox, theHoursBox, theMinsBox, theSecsBox)
	});
	
	/* 加载运费情况 */
	load_city_logist(<?php echo $this->_var['goods']['delivery_template_id']; ?>,<?php echo $this->_var['goods']['store_id']; ?>);
	
	$('.J_PromotoolMoreLink').click(function(){
		$(this).parent().parent().find('.toggle').toggle();
		$(this).toggleClass('active');
	});
})
</script>
<div class="w-shop clearfix">
	<div class="zoom-pics col-sub">
		<div class="big_pic border  mb5"> <a href="<?php echo $this->_var['goods']['_images']['0']['image_url']; ?>" id="zoom" class="MagicZoom MagicThumb"> <img src="<?php echo ($this->_var['goods']['_images']['0']['thumbnail'] == '') ? $this->_var['default_image'] : $this->_var['goods']['_images']['0']['thumbnail']; ?>" width="350"
                height="350" id="main_img" class="main_img" /> </a> </div>
		<div class="tiny-pics"> <a href="javascript:;" id="forword" class="controler"> </a> <a href="javascript:;" id="backword" class="controler"> </a>
			<ul class="list clearfix">
				<?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_image');$this->_foreach['fe_goods_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods_image']['total'] > 0):
    foreach ($_from AS $this->_var['goods_image']):
        $this->_foreach['fe_goods_image']['iteration']++;
?>
				<li <?php if (($this->_foreach['fe_goods_image']['iteration'] <= 1)): ?>class="pic_hover" <?php endif; ?>> <a href="<?php echo $this->_var['goods_image']['image_url']; ?>" rel="zoom" rev="<?php echo $this->_var['goods_image']['thumbnail']; ?>"> <img src="<?php echo $this->_var['goods_image']['thumbnail']; ?>" /> </a> </li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
		</div>
		<div class="share w-full clearfix mb10">
			<div class="view-big-imgs">
				<?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_image');$this->_foreach['fe_goods_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods_image']['total'] > 0):
    foreach ($_from AS $this->_var['goods_image']):
        $this->_foreach['fe_goods_image']['iteration']++;
?> 
				<a href="<?php echo $this->_var['goods_image']['image_url']; ?>" data-fresco-group="goods_info" target="_blank" class="fresco <?php if ($this->_foreach['fe_goods_image']['iteration'] > 1): ?>hidden<?php endif; ?>">查看大图</a> 
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
			</div>
			<div class="collect-goods"> <a href="javascript:collect_goods(<?php echo $this->_var['goods']['goods_id']; ?>);">加入收藏</a> </div>
			<div class="share-list"> <em>分享到：</em> 
				<div class="bdsharebuttonbox float-left">
                    <a href="#" class="bds_tsina" data-cmd="tsina" style="margin: 3px 6px 0 0;"></a>
                    <a href="#" class="bds_tqq" data-cmd="tqq" style="margin: 3px 6px 0 0;"></a>
                    <a href="#" class="bds_weixin" data-cmd="weixin" style="margin: 3px 6px 0 0;"></a>
                    <a href="#" class="bds_more" data-cmd="more" style="margin: 3px 6px 0 0;"></a>
                 </div>
				<script type="text/javascript">
					window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdPic":"","bdStyle":"0","bdSize":"16"},"share":{},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='https://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
				</script>
			</div>
		</div>
	</div>
	<div class="col-main ml20 goods-attr ">
		<div class="goods-name mb10 ml10"> <?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?> </div>
		<div class="attribute">
			<div class="attr-detail float-left">
				<div class="rate pb10 relative">
					<p class="J_IsPro" <?php if (! $this->_var['goods']['_specs']['0']['pro_price']): ?>style="display:none"<?php endif; ?>> <span class="t"> 商<ins style="margin:0 6px;">城</ins>价： </span> <span class="price" ectype="goods_price"> <del><?php echo price_format($this->_var['goods']['_specs']['0']['price']); ?></del> </span><br />
						<span class="t"> 促销活动： </span> <span class="promo-price-type"  title="<?php echo $this->_var['goods']['pro_desc']; ?>" ><i ><?php echo $this->_var['goods']['pro_name']; ?></i></span> <span class="price" ectype="goods_pro_price"> <?php echo price_format($this->_var['goods']['_specs']['0']['pro_price']); ?> </span> 
						<?php if ($this->_var['goods']['lefttime']): ?>
						<div class="J_CountDown countdown hidden"> <span><ins class="lefttime">还剩</ins></span> <span class="tm NumDays"> <?php echo $this->_var['goods']['lefttime']['d']; ?> </span> <em> 天 </em> <span class="tm NumHours"> <?php echo $this->_var['goods']['lefttime']['h']; ?> </span> <em> 小时 </em> <span class="tm NumMins"> <?php echo $this->_var['goods']['lefttime']['m']; ?> </span> <em> 分 </em> <span class="tm NumSeconds"> <?php echo $this->_var['goods']['lefttime']['s']; ?> </span> <em> 秒 </em> </div>
						<?php endif; ?>
					</p>
					<p class="J_IsNotPro" <?php if ($this->_var['goods']['_specs']['0']['pro_price']): ?> style="display:none"<?php endif; ?>> <span class="t"> 商<ins style="margin:0 6px;">城</ins>价： </span> <span class="price" ectype="goods_price"> <?php echo price_format($this->_var['goods']['_specs']['0']['price']); ?> </span> </p>
                    <a style="position:absolute;right:0;top:0;border:1px solid #f60;color:#f60;padding: 0 5px;line-height: 20px;border-radius: 2px;" href="<?php echo url('app=report&id=' . $_GET['id']. ''); ?>"><img src="<?php echo $this->res_base . "/" . 'images/report.png'; ?>" />举报虚假</a>
					<?php if ($this->_var['integral_enabled'] && $this->_var['goods']['exchange_price']): ?>
					<p> <span class="t">积分抵扣：</span> <span class="discount-info"> <b class="d-name">可使用<?php echo $this->_var['goods']['max_exchange']; ?> 积分 </b> <b class="d-price">抵 <?php echo price_format($this->_var['goods']['exchange_price']); ?> 元</b> </span> </p>
					<?php endif; ?>
					<div class="logist"> <span class="t">配送费用：</span> <span class="postage clearfix">
						<div class="postage-cont mr10"> <ins id="selected_city"><b></b></ins>
							<div class="postage-area" style="display:none">
								<div class="province clearfix"> 
									<?php $_from = $this->_var['area']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?> 
									<a href="javascript:;" id="<?php echo $this->_var['province']['region_id']; ?>"><?php echo $this->_var['province']['region_name']; ?></a> 
									<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
								</div>
								<div class="cities"> 
									<?php $_from = $this->_var['area']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');$this->_foreach['fe_province'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_province']['total'] > 0):
    foreach ($_from AS $this->_var['province']):
        $this->_foreach['fe_province']['iteration']++;
?>
									<div class="city_<?php echo $this->_var['province']['region_id']; ?> <?php if (! ($this->_foreach['fe_province']['iteration'] <= 1)): ?>hidden<?php endif; ?>"> 
										<?php $_from = $this->_var['province']['cities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?> 
										<a href="javascript:;" delivery_template_id="<?php echo $this->_var['goods']['delivery_template_id']; ?>" store_id="<?php echo $this->_var['goods']['store_id']; ?>" city_id="<?php echo $this->_var['city']['region_id']; ?>"><?php echo $this->_var['city']['region_name']; ?></a> 
										<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
									</div>
									<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
								</div>
							</div>
						</div>
						<div class="postage-info"></div>
						</span> 
                    
                    
                    </div>
                    <?php if ($this->_var['promotool']['storeFullfreeInfo']): ?>
                    <p>
                    	<span class="t">包邮条件：</span>
                    	<span><?php echo $this->_var['promotool']['storeFullfreeInfo']; ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($this->_var['promotool']['storeFullPreferInfo']): ?>
                    <p>
                    	<span class="t">满折满减：</span>
                    	<span><?php echo $this->_var['promotool']['storeFullPreferInfo']; ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($this->_var['promotool']['storeFullGiftList']): ?>
                    <div class="promotool clearfix">
                    	<span class="first float-left">赠<i style="margin:0 12px; font-style:normal"></i>品：</span>
                    	<span class="float-left">
                        	<?php $_from = $this->_var['promotool']['storeFullGiftList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'fullgift');$this->_foreach['fe_fullgift'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_fullgift']['total'] > 0):
    foreach ($_from AS $this->_var['fullgift']):
        $this->_foreach['fe_fullgift']['iteration']++;
?>
                            <ins class="mr10 each <?php if (! ($this->_foreach['fe_fullgift']['iteration'] <= 1)): ?> toggle hidden <?php endif; ?>">
                                购物满 <b class="f60"><?php echo $this->_var['fullgift']['amount']; ?></b> 元获赠：
                                <?php $_from = $this->_var['fullgift']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?>
                                [<a href="<?php echo url('app=gift&id=' . $this->_var['item']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['item']['goods_name']; ?></a>]
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            </ins>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            <a href="javascript:;" class="J_PromotoolMoreLink morelink"><b></b></a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($this->_var['promotool']['goodsGrowbuyList']): ?>
                    <div class="promotool clearfix">
                    	<span class="first float-left">加 价 够：</span>
                    	<span class="float-left">
                        	<?php $_from = $this->_var['promotool']['goodsGrowbuyList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'growbuy');$this->_foreach['fe_growbuy'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_growbuy']['total'] > 0):
    foreach ($_from AS $this->_var['growbuy']):
        $this->_foreach['fe_growbuy']['iteration']++;
?>
                            <ins class="mr10 each <?php if (! ($this->_foreach['fe_growbuy']['iteration'] <= 1)): ?> toggle hidden <?php endif; ?>">
                            	加 <b class="f60"><?php echo $this->_var['growbuy']['money']; ?></b> 元可购买：
                                <?php $_from = $this->_var['growbuy']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                                [<a href="<?php echo url('app=goods&id=' . $this->_var['item']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['item']['goods_name']; ?></a>]
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                             </ins>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            <a href="javascript:;" class="J_PromotoolMoreLink morelink"><b></b></a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
					<?php if ($this->_var['goods']['brand']): ?>
					<p> <span class="t"> 所属品牌： </span> <span> <?php echo htmlspecialchars($this->_var['goods']['brand']); ?> </span> </p>
					<?php endif; ?>
					<?php if ($this->_var['goods']['tags']): ?>
					<p> <span class="t"> 商品标签： </span> <span> &nbsp;&nbsp; 
						<?php $_from = $this->_var['goods']['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?> 
						<?php echo $this->_var['tag']; ?>&nbsp;&nbsp;&nbsp; 
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
						</span> </p>
					<?php endif; ?>
					<p> <span class="t"> 商品评分： </span> <span id="evaluation"> </span> <span class="c"> <a href="<?php echo url('app=goods&act=comments&id=' . $this->_var['goods']['goods_id']. ''); ?>#module"> (<?php echo $this->_var['goods']['sys_comment']; ?>条评价信息) </a> </span> </p>
					<p> <span class="t"> 销售情况： </span> <span> <?php echo $this->_var['sales_info']; ?> </span> </p>
					<p> <span class="t"> 所在地区： </span> <span> <?php echo htmlspecialchars($this->_var['store']['region_name']); ?> </span> </p>
				</div>
				<div class="handle"> 
					<?php if ($this->_var['goods']['spec_qty'] > 0): ?>
					<ul class="spec1 clearfix w-full sale-attr" style="overflow:visible">
						<li class="handle_title"> <?php echo htmlspecialchars($this->_var['goods']['spec_name_1']); ?>： </li>
					</ul>
					<?php endif; ?> 
					<?php if ($this->_var['goods']['spec_qty'] > 1): ?>
					<ul class="clearfix w-full sale-attr" style="overflow:visible">
						<li class="handle_title"> <?php echo htmlspecialchars($this->_var['goods']['spec_name_2']); ?>： </li>
					</ul>
					<?php endif; ?>
					<ul class="clearfix w-full quantity-select pt10">
						<li style="margin-top:2px;" class="handle_title"> 购买数量： </li>
						<li>
							<div class="buy-quantity"> <a class="reduce-btn" href="javascript:;" change="reduce"> </a> <a class="add-btn" href="javascript:;"  change="increase"> </a>
								<input type="text" class="text" name="quantity" id="quantity" value="1" />
							</div>
							（库存 <span class="stock" ectype="goods_stock"> <?php echo $this->_var['goods']['_specs']['0']['stock']; ?> </span> 件） </li>
					</ul>
					<?php if ($this->_var['goods']['spec_qty'] > 0): ?>
					<ul class="clearfix w-full pt10">
						<li style="margin-top:1px;" class="handle_title"> 您已选择： </li>
						<li class="aggregate" ectype="current_spec"> </li>
					</ul>
					<?php endif; ?> 
				</div>
				<div class="buy-btn mb20"> <a href="javascript:buy_now();" class="buy-now btn"> 立刻购买 </a> <a href="javascript:buy();" class="add-to-cart btn relative"> 加入购物车<i style="display:block;height:0px;width:0px;right:0px;top:-30px;position:absolute;"></i> </a> </div>
			</div>
			<div class="store-info-g w210 float-right mt10">
				<div class="store-info border mb10">
					<h3 class="border-b"> <span> <?php echo htmlspecialchars($this->_var['store']['store_name']); ?> </span> </h3>
					<div class="content">
						<dl class="border-b total_evaluation w-full clearfix">
							<dt> 综合评分： </dt>
							<dd>
								<div class="raty"> <span style="width:<?php echo ($this->_var['store']['evaluation_rate'] == '') ? '0' : $this->_var['store']['evaluation_rate']; ?>;"> </span> </div>
								<b> <?php echo ($this->_var['store']['avg_evaluation'] == '') ? '0' : $this->_var['store']['avg_evaluation']; ?> </b> 分 </dd>
						</dl>
						<div class="rate-info">
							<p> <strong> 店铺动态评分 </strong> 与行业相比 </p>
							<ul>
								<li> 商品评分 <span class="credit"> <?php echo $this->_var['store']['avg_goods_evaluation']; ?> </span> <span class="<?php echo $this->_var['store']['industy_compare']['goods_compare']['class']; ?>"> <i> </i> <?php echo $this->_var['store']['industy_compare']['goods_compare']['name']; ?> <em> 
									<?php if ($this->_var['store']['industy_compare']['goods_compare']['value'] == 0): ?> 
									---- 
									<?php else: ?> 
									<?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>% 
									<?php endif; ?> 
									</em> </span> </li>
								<li> 服务评分 <span class="credit"> <?php echo $this->_var['store']['avg_service_evaluation']; ?> </span> <span class="<?php echo $this->_var['store']['industy_compare']['service_compare']['class']; ?>"> <i> </i> <?php echo $this->_var['store']['industy_compare']['service_compare']['name']; ?> <em> 
									<?php if ($this->_var['store']['industy_compare']['service_compare']['value'] == 0): ?> 
									---- 
									<?php else: ?> 
									<?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>% 
									<?php endif; ?> 
									</em> </span> </li>
								<li> 发货评分 <span class="credit"> <?php echo $this->_var['store']['avg_shipped_evaluation']; ?> </span> <span class="<?php echo $this->_var['store']['industy_compare']['shipped_compare']['class']; ?>"> <i> </i> <?php echo $this->_var['store']['industy_compare']['shipped_compare']['name']; ?> <em> 
									<?php if ($this->_var['store']['industy_compare']['shipped_compare']['value'] == 0): ?> 
									---- 
									<?php else: ?> 
									<?php echo $this->_var['store']['industy_compare']['shipped_compare']['value']; ?>% 
									<?php endif; ?> 
									</em> </span> </li>
							</ul>
						</div>
						<dl class="border-b contact_us dl-c-s clearfix">
							<dt> 联系方式： </dt>
							<dd> 
								 <a href="javascript:;" class="J_StartLayim" data-toid="<?php echo $this->_var['store']['store_id']; ?>"><img src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" /></a> 
							</dd>
						</dl>
						<dl class="dl-c-s w-full clearfix">
							<dt> 店铺名称： </dt>
							<dd> <?php echo htmlspecialchars($this->_var['store']['store_name']); ?> </dd>
						</dl>
						<dl style="padding-top:2px;" class="dl-c-s w-full clearfix">
							<dt> 信&nbsp;&nbsp;用&nbsp;&nbsp;度： </dt>
							<dd> 
								<?php if ($this->_var['store']['credit_value'] >= 0): ?> 
								<img src="<?php echo $this->_var['store']['credit_image']; ?>" alt="" align="absmiddle" /> 
								<?php endif; ?> 
							</dd>
						</dl>
						<?php if ($this->_var['store']['certifications']): ?>
						<dl style="padding-top:1px;" class="dl-c-s w-full clearfix">
							<dt style="margin-top:2px;"> 认证： </dt>
							<dd> 
								<?php $_from = $this->_var['store']['certifications']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cert');if (count($_from)):
    foreach ($_from AS $this->_var['cert']):
?> 
								<?php if ($this->_var['cert'] == "autonym"): ?> 
								<a href="<?php echo url('app=article&act=system&code=cert_autonym'); ?>" target="_blank"
                                title="实名认证"> <img src="<?php echo $this->res_base . "/" . 'images/cert_autonym.gif'; ?>" /> </a> 
								<?php elseif ($this->_var['cert'] == "material"): ?> 
								<a href="<?php echo url('app=article&act=system&code=cert_material'); ?>" target="_blank"
                                title="实体店铺"> <img src="<?php echo $this->res_base . "/" . 'images/cert_material.gif'; ?>" /> </a> 
								<?php endif; ?> 
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
							</dd>
						</dl>
						<?php endif; ?> 
						<?php if ($this->_var['store']['tel']): ?>
						<dl style="padding-top:1px;" class="dl-c-s w-full clearfix">
							<dt> 创店时间： </dt>
							<dd> <?php echo local_date("Y-m-d",$this->_var['store']['add_time']); ?> </dd>
						</dl>
						<?php endif; ?>
						<dl style="padding-top:2px;" class="dl-c-s w-full clearfix">
							<dt> 联系电话： </dt>
							<dd> <?php echo htmlspecialchars($this->_var['store']['tel']); ?> </dd>
						</dl>
						<dl style="padding-top:2px;padding-bottom:10px;" class="dl-c-s border-b w-full clearfix">
							<dt> 详细地址： </dt>
							<dd> <?php echo htmlspecialchars($this->_var['store']['address']); ?> </dd>
						</dl>
						<div class="go2store"> <a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"> 进入商家店铺 </a> <a href="javascript:collect_store(<?php echo $this->_var['store']['store_id']; ?>)"> 收藏该店铺 </a> </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<a name="module"></a> 