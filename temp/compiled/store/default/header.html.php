<?php echo $this->fetch('top.html'); ?>
<div id="header" class="w-full">
	<div class="shop-t w-shop clearfix pb10 mb10 mt5">
      <div class="logo mt10">
         <a href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a>
      </div>
      <div class="top-search">
      	
         
         <div class="top-search-box clearfix">
				<div class="form-fields">
					<form method="GET" action="<?php echo url('app=search'); ?>" class="clearfix">
						<input type="hidden" name="app" value="search" />
						<input type="hidden" name="act" value="<?php if ($_GET['act'] == 'store'): ?>store<?php else: ?>index<?php endif; ?>" />
                        <ul class="select-act float-left J_SearchType">
                        	<li class="<?php if ($_GET['act'] == 'index' || ! $_GET['act']): ?>current<?php endif; ?>"><span value="index">商品</span></li>
                            <li class="<?php if ($_GET['act'] == 'store'): ?>current<?php endif; ?>"><span value="store">店铺</span></li>
                        </ul>
                        
						<input type="text"   name="keyword" value="<?php echo $_GET['keyword']; ?>" class="float-left keyword" placeholder="<?php echo $this->_var['hot_keywords']['0']; ?>" />
						<input type="submit" value="搜索" class="submit" hidefocus="true" />
					</form>
				</div>
			</div>
         <div class="top-search-keywords">
         	<span>热门搜索：</span>
         	<?php $_from = $this->_var['hot_keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');$this->_foreach['fe_keyword'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_keyword']['total'] > 0):
    foreach ($_from AS $this->_var['keyword']):
        $this->_foreach['fe_keyword']['iteration']++;
?>
    		<a <?php if (($this->_foreach['fe_keyword']['iteration'] <= 1) == 1): ?>style="color:#ff0000;"<?php endif; ?> href="<?php echo url('app=search&keyword=' . urlencode($this->_var['keyword']). ''); ?>"><?php echo $this->_var['keyword']; ?></a>
    		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
         </div>
      </div>
      
      <div class="header_cart J_GlobalPop">
        	<div class="item">
			<div class="title clearfix"> <b></b><a href="<?php echo url('app=cart'); ?>">去购物车结算</a><em>></em> </div>
			<div class="shoping"><span class="count-cart J_C_T_GoodsKinds"><?php echo $this->_var['cart_goods_kinds']; ?></span></div>
			<div class="shadow"></div>
			<div class="cart-list eject-box J_GlobalPopSub J_HasGoods"> 
				<?php if ($this->_var['carts_top']['cart_items']): ?>
				<div class="goods-list">
					<h4>最新加入的商品</h4>
					<?php $_from = $this->_var['carts_top']['cart_items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cart');$this->_foreach['fe_cart'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_cart']['total'] > 0):
    foreach ($_from AS $this->_var['cart']):
        $this->_foreach['fe_cart']['iteration']++;
?>
					<div <?php if (($this->_foreach['fe_cart']['iteration'] == $this->_foreach['fe_cart']['total'])): ?>style="border:0px;"<?php endif; ?> class="clearfix list J_CartItem-<?php echo $this->_var['cart']['rec_id']; ?>">
						<div class="goods-img"> <a href="<?php echo url('app=goods&id=' . $this->_var['cart']['goods_id']. ''); ?>" target="_top"><img alt="<?php echo $this->_var['cart']['goods_name']; ?>" src="<?php echo $this->_var['cart']['goods_image']; ?>" width="40" height="40"></a> </div>
						<div class="goods-title"> <a title="<?php echo $this->_var['cart']['goods_name']; ?>" href="<?php echo url('app=goods&id=' . $this->_var['cart']['goods_id']. ''); ?>" target="_top"><?php echo $this->_var['cart']['goods_name']; ?></a> </div>
						<div class="goods-admin">
							<div class="mini-cart-count"><strong class="mini-cart-price J_ItemPrice-<?php echo $this->_var['cart']['rec_id']; ?>"><?php echo price_format($this->_var['cart']['price']); ?></strong> ×<span class="J_ItemQuantity-<?php echo $this->_var['cart']['rec_id']; ?>"><?php echo $this->_var['cart']['quantity']; ?></span></div>
							<div class="mini-cart-del"><a href="javascript:;" onclick="drop_cart_item(<?php echo $this->_var['cart']['store_id']; ?>, <?php echo $this->_var['cart']['rec_id']; ?>);">删除</a></div>
						</div>
					</div>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
				</div>
				<div class="total"> <span>共<strong class="J_C_T_GoodsKinds"><?php echo $this->_var['cart_goods_kinds']; ?></strong>件商品</span><span>共计<strong class="J_C_T_Amount"><?php echo price_format($this->_var['carts_top']['total_amount']); ?></strong></span><br />
					<a href="<?php echo url('app=cart'); ?>">去购物车结算</a> </div>
				<?php else: ?>
				<div class="nogoods clearfix J_NoGoods"> <b></b>购物车中还没有商品，赶紧选购吧！ </div>
				<?php endif; ?> 
			</div>
            </div>
		</div>
   </div>
    <div  class="w-full mall-nav relative <?php if (! $this->_var['index']): ?>not-index J_ShowCategory<?php endif; ?>">
		<div class="mall-bg w">
			<ul class="w clearfix">
				<li class="allcategory float-left"> <a class="allsort">所有商品分类<b></b></a> 
					<div class="allcategory-list <?php if (! $this->_var['index']): ?>hidden<?php endif; ?>">
						<div class="content clearfix"> 
							<?php $_from = $this->_var['header_gcategories']['gcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'categories');$this->_foreach['fe_categories'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_categories']['total'] > 0):
    foreach ($_from AS $this->_var['categories']):
        $this->_foreach['fe_categories']['iteration']++;
?>
							<div class="item">
								<div class="pborder">
									<p> 
										<?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?> 
										<a href="<?php echo url('app=search&cate_id=' . $this->_var['category']['id']. ''); ?>" target="_blank"><?php echo htmlspecialchars($this->_var['category']['value']); ?></a> <?php if (! ($this->_foreach['fe_category']['iteration'] == $this->_foreach['fe_category']['total'])): ?><a href="javascript:;">、</a><?php endif; ?> 
										<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
										<i></i><b class="line"></b></p>
								</div>
								<div class="pop" <?php if ($this->_var['category']['top']): ?> style="top:<?php echo $this->_var['category']['top']; ?>"<?php endif; ?>>
									<div class="catlist float-left"> 
                                    	<ul class="clearfix">
											<?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?> 
                                    		<?php $_from = $this->_var['category']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_child']['iteration']++;
?>
                                        	<li class="clearfix"><a href="<?php echo url('app=search&cate_id=' . $this->_var['child']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['child']['value']); ?><i>></i></a></li>
                                        	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
											<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                                        </ul>
										<?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?> 
										<?php $_from = $this->_var['category']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_child']['iteration']++;
?>
										<dl class="clearfix" <?php if (($this->_foreach['fe_child']['iteration'] == $this->_foreach['fe_child']['total'])): ?> style="border-bottom:0"<?php endif; ?>>
											<dt class="float-left"><a href="<?php echo url('app=search&cate_id=' . $this->_var['child']['id']. ''); ?>"><strong><?php echo htmlspecialchars($this->_var['child']['value']); ?></strong></a></dt>
											<dd class="float-left"> 
												<?php $_from = $this->_var['child']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child2');$this->_foreach['fe_child2'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child2']['total'] > 0):
    foreach ($_from AS $this->_var['child2']):
        $this->_foreach['fe_child2']['iteration']++;
?> 
												<a href="<?php echo url('app=search&cate_id=' . $this->_var['child2']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['child2']['value']); ?></a> 
												<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
											</dd>
										</dl>
										<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
										<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
									</div>
									<div class="catbrand float-left"> 
										<ul class="clearfix mb10">
											<?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?>
											<?php $_from = $this->_var['category']['brands']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'brand');$this->_foreach['fe_brand'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_brand']['total'] > 0):
    foreach ($_from AS $this->_var['brand']):
        $this->_foreach['fe_brand']['iteration']++;
?>
											<li class="float-left"><a href="<?php echo url('app=search&brand=' . urlencode($this->_var['brand']['brand_name']). ''); ?>" title="<?php echo $this->_var['brand']['brand_name']; ?>"><img src="<?php echo $this->_var['brand']['brand_logo']; ?>" alt="<?php echo htmlspecialchars($this->_var['brand']['brand_name']); ?>"/></a></li>
											<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
											<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
										</ul>
										<p> 
											<?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?>
											<?php $_from = $this->_var['category']['gads']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ads');$this->_foreach['fe_ads'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_ads']['total'] > 0):
    foreach ($_from AS $this->_var['ads']):
        $this->_foreach['fe_ads']['iteration']++;
?> 
											<a href="<?php echo $this->_var['ads']['link_url']; ?>" target="_blank" class="mb10"><img src="<?php echo $this->_var['ads']['file_path']; ?>" width="180"/></a> 
											<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
											<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
										</p>
									</div>
								</div>
							</div>
							<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
						</div>
					</div>
					 
				</li>
				<li class="each float-left inline-block"><a class="<?php if ($this->_var['index']): ?>current<?php endif; ?>" href="<?php echo $this->_var['site_url']; ?>">首页</a></li>
				<li class="each float-left inline-block"><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>current<?php endif; ?>" href="https://shop.yatdim.com/index.php?app=brand&act=index">品牌中心</a></li>
				<li class="each float-left inline-block"><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>current<?php endif; ?>" href="https://shop.yatdim.com/index.php?app=xunjia&act=index">在线询价</a></li>
				<li class="each float-left inline-block"><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>current<?php endif; ?>" href="https://shop.yatdim.com/index.php?app=member&act=register">商家入驻</a></li>
				<li class="each float-left inline-block"><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>current<?php endif; ?>" href="https://shop.yatdim.com/index.php?app=instructions&act=index">操作指南</a></li>
				<?php $_from = $this->_var['navs']['middle']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');$this->_foreach['fe_nav'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_nav']['total'] > 0):
    foreach ($_from AS $this->_var['nav']):
        $this->_foreach['fe_nav']['iteration']++;
?>
				<li class="each float-left inline-block"><a class="<?php if (! $this->_var['index'] && $this->_var['nav']['link'] == $this->_var['current_url']): ?>current<?php endif; ?>" href="<?php echo $this->_var['nav']['link']; ?>"<?php if ($this->_var['nav']['open_new']): ?> target="_blank"<?php endif; ?>><?php echo htmlspecialchars($this->_var['nav']['title']); ?><?php if ($this->_foreach['fe_nav']['iteration'] == 1): ?><span class="absolute block">HOT</span><?php endif; ?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
		</div>
	</div>
</div>
<div id="nav" class="w-full">
    <div class="banner">
    	<a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" class="w-full block" style=" background:url(
        	<?php if ($this->_var['store']['store_banner']): ?>
            <?php echo $this->_var['store']['store_banner']; ?>
            <?php else: ?>
            <?php echo $this->res_base . "/" . 'images/store_banner.png'; ?>
            <?php endif; ?>
        ) no-repeat center center; height:119px;"></a>
    </div>
	<div class="shop-nav w-full" <?php if ($this->_var['store']['nav_color']): ?> style="background:<?php echo $this->_var['store']['nav_color']; ?>"<?php endif; ?>>
        <ul class="w J_ShopNav">
            <li><a  href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><span>店铺首页</span></a></li>
           
            <?php $_from = $this->_var['store']['store_gcates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');if (count($_from)):
    foreach ($_from AS $this->_var['gcategory']):
?>
            <li><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&cate_id=' . $this->_var['gcategory']['id']. '&from=nav'); ?>"><span><?php echo htmlspecialchars($this->_var['gcategory']['value']); ?></span></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            
            <?php $_from = $this->_var['store']['store_navs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store_nav');if (count($_from)):
    foreach ($_from AS $this->_var['store_nav']):
?>
            <li><a href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=store&act=article&id=' . $this->_var['store_nav']['article_id']. ''); ?>"><span><?php echo htmlspecialchars($this->_var['store_nav']['title']); ?></span></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <li><a href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>"><span>信用评价</span></a></li>
            
            <?php if ($_GET['app'] == 'store' && $_GET['act'] == 'search' && $_GET['id'] && ! $_GET['cate_id']): ?>
            <li><a class="current" href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=store&act=search&id=' . $_GET['id']. ''); ?>"><span>全部商品</span></a></li>
            <?php endif; ?>
            <?php if ($_GET['app'] == 'store' && $_GET['act'] == 'search' && $_GET['cate_id'] && ! $_GET['from']): ?>
            <li><a class="current" href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=store&act=search&cate_id=' . $_GET['cate_id']. ''); ?>"><span>goods_search</span></a></li>
            <?php endif; ?>
            <?php if ($_GET['app'] == 'goods' && $_GET['id']): ?>
            <li><a class="current" href="<?php echo $this->_var['site_url']; ?>/<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>"><span>商品详情</span></a></li>
            <?php endif; ?> 
        </ul>
    </div>
</div>