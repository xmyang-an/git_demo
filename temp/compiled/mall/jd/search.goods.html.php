<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'search_goods.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">

$(function(){

	var order = '<?php echo $_GET['order']; ?>';
	var css = '';
	
	<?php if ($_GET['order']): ?>
	order_arr = order.split(' ');
	switch (order_arr[1]){
		case 'desc' : 
			css = 'order-down btn-order-cur';
		break;
		case 'asc' :
			css = 'order-up btn-order-cur';
		break;
		default : 
			css = 'order-down-gray';
	}
	$('.btn-order a[ectype='+order_arr[0]+']').attr('class','btn-order-click '+css);
	<?php endif; ?>
	
	$(".btn-order a").click(function(){
		if(this.id==''){
			dropParam('order');// default order
			return false;
		}
		else
		{
			dd = " desc";
			if(order != '') {
				order_arr = order.split(' ');
				if(order_arr[0]==this.id && order_arr[1]=="desc")
					dd = " asc";
				else dd = " desc";
			}
			replaceParam('order', this.id+dd);
			return false;
		}
	});
	
	<?php if ($_GET['price']): ?>
	var filter_price = '<?php echo $_GET['price']; ?>';
	filter_price = filter_price.split('-');
	$('input[name="start_price"]').val(number_format(filter_price[0],0));
	$('input[name="end_price"]').val(number_format(filter_price[1],0));
	<?php endif; ?>
	$('.attr-bottom .show-more').click(function(){
		$(this).parent().parent().find('.toggle').toggle(200);
		if($(this).find('span').html()=='展开'){
			$(this).find('span').html('收起');
			$(this).attr('class', 'hide-more');
		} else {
			$(this).find('span').html('展开');
			$(this).attr('class', '');
		}
	});
	$('.each .pv .more-it').click(function(){
		$(this).parent('.pv').find('.hidden').toggle();
		if($(this).find('em').html() == '更多' )
		{
			$(this).find('em').html('收起');
			$(this).find('i').addClass('foldUp');
		}
		else
		{
			$(this).find('em').html('更多');
			$(this).find('i').removeClass('foldUp');
		}
	});
	$('.J_FilterArea').hover(function(){
		$(this).children('.fa-list').show();
	}, function(){
		$(this).children('.fa-list').hide();
	});
	
	
	// 初始化
	getcity('<?php echo $_GET['region_id']; ?>');
	
	$('.J_FilterArea .province li a').click(function(){
		region_id = $(this).attr('id');
		$('.J_FilterArea .province li').find('a').each(function(){
			$(this).removeClass('selected');
		});
		$(this).addClass('selected');
		getcity(region_id);
	});
	$('.J_FilterArea .city').on('click', 'li a', function(){
		//$('.J_FilterArea .city li').find('a').each(function(){
			//$(this).removeClass('selected');
		//});
		//$(this).addClass('selected');
		replaceParam('region_id', this.id);
        return false;
	});
	$('.J_AllArea').click(function(){
		dropParam('region_id');
		return false;
	});
	$('.J_SelProvince').click(function(){
		addr_id = $('.J_FilterArea .province li a.selected').attr('id');
		if(addr_id) {
			replaceParam('region_id', addr_id);
		}
		return false;
	});
	function getcity(region_id)
	{
		// 初始化显示第一个省的城市
		if(region_id==0 || region_id=='') region_id = $('.J_FilterArea .province li:first').children('a').attr('id');

		$('.J_GetCity').html('');
		$.getJSON('index.php?app=search&act=getcity', {'region_id':region_id},function(data){
			if(data.done){
				$.each(data.retval, function(i, item){
					if(item.selected==1) style="class='selected'"; else style="";
					$('.J_GetCity').append('<li><a '+style+' href="javascript:;" id="'+item.region_id+'">'+item.region_name+'</a></li>');
				});
			}
		});
	}
});

</script>
<div id="main" class="w-full">
	<div id="page-search-goods" class="w mb20 mt20">
		<div class="w mb10">
       			<?php echo $this->fetch('curlocal.html'); ?>
            
                <?php $_from = $this->_var['ultimate_store']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');$this->_foreach['fe_store'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_store']['total'] > 0):
    foreach ($_from AS $this->_var['store']):
        $this->_foreach['fe_store']['iteration']++;
?>
        		<div class="ultimate-store mb10">
                	<div class="item">
            			<div class="content clearfix">
                			<a class="float-left store-logo" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><img height="50" src="<?php echo $this->_var['store']['store_logo']; ?>" /></a>
                    		<div class="float-left middleside">
                    			<a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></a>
                    			<span class="block"><?php echo htmlspecialchars($this->_var['store']['description']); ?></span>
                    		</div>
                    		<div class="float-right rightside">
                    			<b></b><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></a>
                    		</div>
            			</div>
                    </div>
           		</div>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                
               	<?php if ($this->_var['props'] || $this->_var['brands'] || $this->_var['price_intervals'] || $this->_var['gcategories']): ?>
      			<div class="attribute">
            		<div class="selected-attr title">
             			<?php if ($this->_var['filters']): ?>
             			<strong>您已选择：</strong>
             			<?php $_from = $this->_var['filters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'filter');if (count($_from)):
    foreach ($_from AS $this->_var['filter']):
?>
             			<a href="javascript:;" id="<?php echo $this->_var['filter']['key']; ?>"><b><?php echo $this->_var['filter']['name']; ?>：</b><?php echo $this->_var['filter']['value']; ?><span>x</span></a>
             			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
             			<?php else: ?>
             			<strong>按条件筛选</strong>
             			<?php endif; ?>
          			</div>
          			<div class="content">
                    	
             			<?php if ($this->_var['brands'] && ! $this->_var['filters']['brand']): ?>
             			<div class="each brand-list clearfix">
                			<h4>按品牌：</h4>
                			<div class="pv" ectype="ul_brand">
                                <div class="wrap-brand clearfix">
                                	<?php $_from = $this->_var['brands']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                   					<a  <?php if ($this->_foreach['fe_row']['iteration'] > 18): ?>class="hidden"<?php endif; ?> href="javascript:void(0);" title="<?php echo $this->_var['row']['brand']; ?>" id="<?php echo urlencode($this->_var['row']['brand']); ?>" ><img width="80" height="40" src="<?php echo $this->_var['row']['brand_logo']; ?>" alt="<?php echo htmlspecialchars($this->_var['row']['brand']); ?>" /><span><?php echo htmlspecialchars($this->_var['row']['brand']); ?></span></a>
                                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                </div>
                                <span class="more-it clearfix"><em>更多</em><i></i></span>
                			</div>
             			</div>
            			<?php endif; ?>
             
             			<?php if ($this->_var['price_intervals'] && ! $this->_var['filters']['price']): ?>
             			<div class="each clearfix">
                			<h4>按价格：</h4>
               				<div class="pv" ectype="ul_price">
                   				<?php $_from = $this->_var['price_intervals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                   				<a <?php if ($this->_foreach['fe_row']['iteration'] > 10): ?>class="hidden"<?php endif; ?> href="javascript:void(0);" id="<?php echo $this->_var['row']['min']; ?> - <?php echo $this->_var['row']['max']; ?>" ><?php echo price_format($this->_var['row']['min']); ?> - <?php echo price_format($this->_var['row']['max']); ?><span class="count">(<?php echo $this->_var['row']['count']; ?>)</span></a>
                   				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                <span class="more-it clearfix"><em>更多</em><i></i></span>
                			</div>
             			</div>
             			<?php endif; ?>
           				<?php $_from = $this->_var['props']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');$this->_foreach['fe_prop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_prop']['total'] > 0):
    foreach ($_from AS $this->_var['prop']):
        $this->_foreach['fe_prop']['iteration']++;
?>
             			<div class="<?php if ($this->_foreach['fe_prop']['iteration'] > 5): ?>hidden toggle<?php endif; ?> each clearfix">
                			<h4><?php echo $this->_var['prop']['name']; ?>：</h4>
                			<div class="pv" ectype="dl_props" >
                   				<?php $_from = $this->_var['prop']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                   				<a <?php if ($this->_foreach['fe_row']['iteration'] > 10): ?>class="hidden"<?php endif; ?> href="javascript:void(0);" id="<?php echo $this->_var['row']['pid']; ?>:<?php echo $this->_var['row']['vid']; ?>" selected_props="<?php echo $this->_var['props_selected']; ?>">
								<?php if ($this->_var['prop']['is_color_prop']): ?>
								<i <?php if ($this->_var['row']['color_value']): ?> class="color" style="background:<?php echo $this->_var['row']['color_value']; ?>"<?php else: ?>class="color duocai"<?php endif; ?> title="<?php echo $this->_var['row']['prop_value']; ?>"></i>
								<?php else: ?>
								<?php echo htmlspecialchars($this->_var['row']['prop_value']); ?><span class="count">(<?php echo $this->_var['row']['count']; ?>)</span>
								<?php endif; ?>
								</a>
                   				
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                <span class="more-it clearfix"><em>更多</em><i></i></span>
                			</div>
             			</div>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
             
              			<?php if ($this->_var['gcategories']): ?>
                        <div class="each clearfix">
                			<h4>商品分类：</h4>
                			<div class="pv" ectype="ul_cate" >
                   				<?php $_from = $this->_var['gcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
                   				<a <?php if ($_GET['cate_id'] == $this->_var['gcategory']['cate_id']): ?>style="color:#BF1B30"<?php endif; ?> <?php if ($this->_foreach['fe_row']['iteration'] > 10): ?>class="hidden"<?php endif; ?> href="javascript:void(0);" title="<?php echo $this->_var['gcategory']['cate_name']; ?>" id="<?php echo $this->_var['gcategory']['cate_id']; ?>"><?php echo $this->_var['gcategory']['cate_name']; ?><span class="count">(<?php echo $this->_var['gcategory']['count']; ?>)</span></a>
                   				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                <span class="more-it clearfix"><em>更多</em><i></i></span>
                			</div>
             			</div>
						<?php endif; ?>
                       
          			</div>
          			<div class="attr-bottom">
             			<ins></ins><b class="show-more"><span>展开</span>条件<i></i></b>
          			</div>
       			</div>
             	<?php endif; ?>
            
       			<div class="glist">
          			<div class="clearfix">
                		<div class="list-sort J_ListSort ">
                        	<form>
                            	<div class="J_FilterArea float-left filter-area">
                                	<span>
										<?php if ($this->_var['provinces']['selected']['city']): ?>
										<?php echo $this->_var['provinces']['selected']['city']; ?>
                                        <?php elseif ($this->_var['provinces']['selected']['province']): ?>
                                        <?php echo $this->_var['provinces']['selected']['province']; ?>
                                        <?php else: ?>所在地<?php endif; ?>
                                    </span><b></b>
                                    <div class="fa-list hidden">
                                    	<div class="fa-hd clearfix">
                                        	<a class="J_AllArea" href="javascript:;">所有地区</a>
                                            <span><?php echo $this->_var['provinces']['selected']['province']; ?> <?php echo $this->_var['provinces']['selected']['city']; ?></span>
                                            <button class="J_SelProvince">确定</button>
                                        </div>
                                    	<ul class="fa-loc province clearfix">
                                        	<?php $_from = $this->_var['provinces']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?>
                        					<li><a <?php if ($this->_var['province']['selected']): ?> class="selected"<?php endif; ?> href="javascript:;" id="<?php echo $this->_var['province']['region_id']; ?>"><?php echo $this->_var['province']['region_name']; ?></a></li>
                           					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                        </ul>
                                        <ul class="J_GetCity fa-loc city clearfix"></ul>
                                    </div>
                                </div>
                            	<div class="display_mod float-left clearfix">
                                	<a class="qh-list" hidefocus="true" id="list"  href="javascript:;"><i></i>列表</a>
                                	<a class="qh-squares" hidefocus="true" id="squares"  href="javascript:;"><i></i>大图</a>
                            	</div>
                            	<div class="float-left btn-order clearfix">
                                	<!--<span>排序：</span>-->
                                	<?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'order');$this->_foreach['fe_order'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_order']['total'] > 0):
    foreach ($_from AS $this->_var['k'] => $this->_var['order']):
        $this->_foreach['fe_order']['iteration']++;
?>
                                	<?php if (! $this->_var['k']): ?>
                                	<a class="btn-order-click default-sort" id="<?php echo $this->_var['k']; ?>" href="javascript:;"><?php echo $this->_var['order']; ?></a>
                                	<?php else: ?>
                                	<a class="btn-order-click order-down-gray" ectype="<?php echo $this->_var['k']; ?>" id="<?php echo $this->_var['k']; ?>" href="javascript:;"><?php echo $this->_var['order']; ?><b></b></a>
                                	<?php endif; ?>
                                	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            	</div>
                            	<div class="float-left filter-price">
                                	<div class="filter-price-box">
                                    	<b class="fp-input">
                                        	<i class="ui-price-plain">&yen;</i>
                                        	<input type="text" name="start_price" maxlength="6" value="" />
                                    	</b>
                                    	<i class="fp-split">-</i>
                                    	<b class="fp-input">
                                        	<i class="ui-price-plain">&yen;</i>
                                        	<input type="text" name="end_price" maxlength="6" value="" />
                                    	</b>
                                    	<a class="ui-btn-s-primary">提交</a>
                                	</div>
                            	</div>
                                <?php if (! $this->_var['goods_list_order']): ?><div class="mt5"><?php echo $this->fetch('page.top.html'); ?></div><?php endif; ?>
                        	</form>
                    	</div>
                        <?php if ($this->_var['goods_list_order']): ?>
                        <div class="notice-word"><p class="yellow-big">很抱歉！没有找到该搜索条件下的相关商品，我们为您推荐了以下您可能感兴趣的宝贝！</p></div>
                        <?php endif; ?>
            			<div class="<?php echo $this->_var['display_mode']; ?> clearfix w" ectype="current_display_mode">
             				<?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
                            <div <?php if ($this->_foreach['fe_goods']['iteration'] % 5 == 0): ?>style="margin-right:0px;"<?php endif; ?> class="item">
                                <dl class="clearfix dl-<?php echo $this->_var['goods']['goods_id']; ?>">
                                    <dt><a class="inline-block" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img class="lazyload inline-block" initial-url="<?php echo $this->_var['goods']['default_image']; ?>" /></a></dt>
                                    <dd class="sub-images sub-images-<?php echo $this->_var['goods']['goods_id']; ?>">
                                        <?php $_from = $this->_var['goods']['_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'image');$this->_foreach['fe_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_image']['total'] > 0):
    foreach ($_from AS $this->_var['image']):
        $this->_foreach['fe_image']['iteration']++;
?>
                                        <img class="lazyload" initial-url="<?php echo $this->_var['image']['thumbnail']; ?>" goods_id="<?php echo $this->_var['goods']['goods_id']; ?>" image_url="<?php echo $this->_var['image']['thumbnail']; ?>" width="30" height="30" />
                                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                    </dd>
                                    <dd class="price clearfix"><em><b>&yen;</b><?php echo $this->_var['goods']['price']; ?></em></dd>
                                    <dd class="desc"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank" title="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></a></dd>
                                    <dd class="store-info"><a href="<?php echo url('app=store&id=' . $this->_var['goods']['store_id']. ''); ?>" target="_blank"><?php echo $this->_var['goods']['store_name']; ?></a></dd>
                                    <dd class="delivery twofloat clearfix hidden"><em></em><span></span></dd>
                                    <dd class="static clearfix">
                                    	 <span class="sales">成交量 <em><?php echo $this->_var['goods']['sales']; ?></em></span>
                                         <span class="valuation">评价 <b><?php echo $this->_var['goods']['comments']; ?></b></span>
                                    	 <span style="margin-right:0px;border:0px; text-align:center">
                                         	<a href="javascript:;" class="J_StartLayim" data-toid="<?php echo $this->_var['goods']['store_id']; ?>"><img src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" /></a>
                                         
                                        </span>
                                    </dd>
                                </dl> 
                            </div>
            				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          				</div>
             			<?php if (! $this->_var['goods_list_order']): ?><div class="mt20 clearfix center page-big"><?php echo $this->fetch('page.bottom.html'); ?></div><?php endif; ?>
          			</div>
       			</div>
    	</div>
   		<?php if ($this->_var['recommend_goods']): ?>
   		<div class="recommend">
			<div class="title"><span></span>推荐商品</div>
			<div class="content clearfix">
				<?php $_from = $this->_var['recommend_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
				<dl class="mb20" style="<?php if (($this->_foreach['fe_goods']['iteration'] <= 1) || $this->_foreach['fe_goods']['iteration'] % 5 == 1): ?>padding-left:0;<?php endif; ?><?php if ($this->_foreach['fe_goods']['iteration'] % 5 == 0): ?>padding-right:0<?php endif; ?>">
					<dt><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img width="214" height="214"  src="<?php echo $this->_var['goods']['default_image']; ?>" /></a></dt>
					<dd class="desc"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php echo sub_str(htmlspecialchars($this->_var['goods']['goods_name']),48); ?></a></dd>
					<dd class="price twofloat clearfix mt10"><em><b>&yen;</b><?php echo $this->_var['goods']['price']; ?></em><span>最新成交<?php echo $this->_var['goods']['sales']; ?>笔</span></dd>
					<dd class="service"></dd>
				</dl>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</div>
		</div>
		<?php endif; ?>
    </div>
</div>

<?php echo $this->fetch('footer.html'); ?>
