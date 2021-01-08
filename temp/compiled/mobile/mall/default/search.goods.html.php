<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div id="page-search-goods" class="page J_page">
    <div class="page-body search-goods"> 
    
    <div class="listTab mall-menus">
    	<div class="fixed-gap"></div>
        <div class="fixed-bd">
        <ul class="items webkit-box">
          <li class="flex1"><a class="block center active fs14" href="javascript:;"><span>商品</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=search&act=store'); ?>"><span>店铺</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=limitbuy'); ?>"><span>促销</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=integral'); ?>"><span>积分</span></a></li>
        </ul>
        </div>
      </div>
      
      
      <div class="listTab">
        <div class="fixed-gap"></div>
        <div class="fixed-bd clearfix">
          <ul class="items clearfix">
            <li><a href="javascript:void(0);" class="active J_ActiveSort"><span>综合排序</span><i class="psmb-icon-font">&#xe61f;</i></a></li>
            <li><a href="javascript:void(0);" id="sales-desc" ectype="sort"><span>销量优先</span></a></li>
            <li><a href="javascript:;" class="goodsFilter J_PopLayer" data-PopLayer="{popLayer:'.J_GoodsFilterPopLayer', fixedBody: true, left:80, direction:'right'}"><span>筛选</span><i class="psmb-icon-font">&#xe6cc;</i></a></li>
          </ul>
          <div class="list-style display-mode"> <span class="psmb-icon-font squares clearfix J_ChangeDisplayMode" id="goodsDisplayMode"></span> </div>
        </div>
      </div>
      
      
      <div class="sort-eject hidden J_SortEject" onclick="$(this).slideUp();"> <span><a href="javascript:void(0);" class="active block webkit-box" ectype="sort"><ins class="flex1">综合排序</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="price-desc" ectype="sort" class="block webkit-box"><ins class="flex1">价格从高到低</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="price-asc" ectype="sort" class="block webkit-box"><ins class="flex1">价格从低到高</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="views-desc" ectype="sort" class="block webkit-box"><ins class="flex1">人气排序</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="add_time-desc" ectype="sort" class="block webkit-box"><ins class="flex1">上架从新到旧</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span></div>
      
      
      <div class="<?php echo $this->_var['display_mode']; ?> goods-list clearfix J_InfiniteList" ectype="current_display_mode" data-cookie="goodsDisplayMode">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <div class="infinite-template">
          <li> <a href="{1}" class="webkit-box block">
            <div class="pic padding5"><img src="<?php echo $this->res_base . "/" . 'images/empty.gif'; ?>"  class="lazyload" initial-url="{2}"></div>
            <div class="info flex1">
              <h2 class="goods-name line-clamp-2">{3}</h2>
              <p class="store-name">{4}</p>
              <div class="pri-sales clearfix"> <span class="price float-left">{5}</span> <ins class="sales float-right">已售{6}</ins> </div>
            </div>
            </a> </li>
        </div>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>已经到底了<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i>
          <p>没有数据了</p>
        </div>
      </div>
    </div>
  </div>
  <div class="pop-layer-common pop-wrap-r search-filter J_GoodsFilterPopLayer">
    <div class="wraper">
      <div class="bd">
        <div class="attrs"> 
          <?php if ($this->_var['filters']): ?>
          <div class="attr attr-extra">
            <div class="attrk">您已选择：</div>
            <div class="filter-list attrv">
              <ul class="clearfix selected-attr">
                <?php $_from = $this->_var['filters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'filter');if (count($_from)):
    foreach ($_from AS $this->_var['filter']):
?>
                <li><a href="javascript:void(0);" id="<?php echo $this->_var['filter']['key']; ?>" class="each-filter"><span><?php echo $this->_var['filter']['name']; ?>：<?php echo $this->_var['filter']['value']; ?></span></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
            </div>
          </div>
          <?php endif; ?>
          <div class="pri-filter attr">
            <div class="t attrk">价格区间</div>
            <div class="c clearfix mt10"> <span class="block-wrap">
              <input type="text" name="start_price" value="">
              </span> <span class="line"></span> <span class="block-wrap">
              <input  type="text"  name="end_price" value="">
              </span> <span class="psmb-icon-font search-btn J_SearchFilterPrice J_CloseFilterPop">&#xe662;</span> </div>
          </div>
          <div class="attr"> 
            <?php if ($this->_var['categories']): ?>
            <div class="attrk">商品分类</div>
            <div class="attrv">
              <ul class="clearfix"  ectype="ul_cate">
                <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
                <li <?php if ($this->_foreach['fe_gcategory']['iteration'] > 6): ?>class="hidden"<?php endif; ?>><a href="javascript:void(0);" id="<?php echo $this->_var['gcategory']['cate_id']; ?>"><span><?php echo $this->_var['gcategory']['cate_name']; ?>(<?php echo $this->_var['gcategory']['count']; ?>)</span></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
              <?php if ($this->_var['category_count'] > 6): ?>
              <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
              <?php endif; ?> 
            </div>
            <?php endif; ?> 
            <?php if ($this->_var['brands'] && ! $this->_var['filters']['brand']): ?>
            <div class="attrk">品牌</div>
            <div class="attrv">
              <ul  ectype="ul_brand" class="clearfix">
                <?php $_from = $this->_var['brands']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                <li <?php if ($this->_foreach['fe_row']['iteration'] > 6): ?>class="hidden"<?php endif; ?>><a href="javascript:void(0);" id="<?php echo htmlspecialchars($this->_var['row']['brand']); ?>"><img src="<?php echo $this->_var['row']['brand_logo']; ?>"/></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
              <?php if ($this->_var['brand_count'] > 6): ?>
              <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
              <?php endif; ?> 
            </div>
            <?php endif; ?> 
            <?php $_from = $this->_var['props']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');$this->_foreach['fe_prop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_prop']['total'] > 0):
    foreach ($_from AS $this->_var['prop']):
        $this->_foreach['fe_prop']['iteration']++;
?>
            <div class="attrk"><?php echo $this->_var['prop']['name']; ?></div>
            <div class="attrv">
              <ul  ectype="ul_prop" class="clearfix">
                <?php $_from = $this->_var['prop']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                <li <?php if ($this->_foreach['fe_row']['iteration'] > 6): ?>class="hidden"<?php endif; ?>> <a href="javascript:void(0);" id="<?php echo $this->_var['row']['pid']; ?>:<?php echo $this->_var['row']['vid']; ?>" selected_props="<?php echo $this->_var['props_selected']; ?>" title="<?php echo $this->_var['row']['prop_value']; ?>"> <span <?php if ($this->_var['prop']['is_color_prop']): ?>class="color"<?php endif; ?>> 
                  <?php if ($this->_var['prop']['is_color_prop']): ?> 
                  <i <?php if ($this->_var['row']['color_value']): ?>style="background:<?php echo $this->_var['row']['color_value']; ?>"<?php else: ?>class="duocai"<?php endif; ?> title="<?php echo $this->_var['row']['prop_value']; ?>"><?php echo htmlspecialchars($this->_var['row']['prop_value']); ?></i> 
                  <?php else: ?> 
                  <?php echo htmlspecialchars($this->_var['row']['prop_value']); ?> 
                  <?php endif; ?> 
                  </span> </a> </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
              <?php if ($this->_var['prop']['prop_count'] > 6): ?>
              <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
              <?php endif; ?> 
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            
            <?php if ($this->_var['price_intervals'] && ! $this->_var['filters']['price']): ?>
            <div class="attrk">价格</div>
            <div class="attrv">
              <ul class="clearfix" ectype="ul_price">
                <?php $_from = $this->_var['price_intervals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                <li <?php if ($this->_foreach['fe_row']['iteration'] > 6): ?>class="hidden"<?php endif; ?>><a href="javascript:void(0);" id="<?php echo $this->_var['row']['min']; ?> - <?php echo $this->_var['row']['max']; ?>"><span><?php echo price_format($this->_var['row']['min']); ?> - <?php echo price_format($this->_var['row']['max']); ?></span></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
              <?php if ($this->_var['price_count'] > 6): ?>
              <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
              <?php endif; ?> 
            </div>
            <?php endif; ?> 
            <?php if ($this->_var['regions'] && ! $this->_var['filters']['region_id']): ?>
            <div class="attrk">所在地区</div>
            <div class="attrv">
              <ul class="clearfix" ectype="ul_region">
                
                <?php $_from = $this->_var['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');$this->_foreach['fe_row'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_row']['total'] > 0):
    foreach ($_from AS $this->_var['row']):
        $this->_foreach['fe_row']['iteration']++;
?>
                <li <?php if ($this->_foreach['fe_row']['iteration'] > 6): ?>class="hidden"<?php endif; ?>><a href="javascript:void(0);" id="<?php echo $this->_var['row']['region_id']; ?>"><span><?php echo htmlspecialchars($this->_var['row']['region_name']); ?></span></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
              <?php if ($this->_var['region_count'] > 6): ?>
              <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
              <?php endif; ?> 
            </div>
            <?php endif; ?> 
          </div>
        </div>
      </div>
      <div class="ft webkit-box clearfix"> <span class="pop-btn flex1"> <a uri="<?php echo url('app=search&cate_id=' . $_GET['cate_id']. '&keyword=' . $_GET['keyword']. ''); ?>" href="javascript:;" class="popReset">重置</a></span> <span class="pop-btn flex1"><i class="popClosed">关闭</i></span> </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
	var order = '<?php echo $_GET['order']; ?>';
	if(order){
		var	order_arr = order.split(' ');
		var activeSort = order_arr[0]+'-'+order_arr[1];
		$("[ectype='sort']").removeClass('active');
		$('#'+activeSort).addClass('active');
		
		if(activeSort == 'sales-desc'){
			$(".J_ActiveSort").removeClass('active').find('span').text('综合排序');
		}else{
			$(".J_ActiveSort").find('span').text($('#'+activeSort).find('ins').text());
		}
	}
	
	var filter_price = '<?php echo $_GET['price']; ?>';
	if(filter_price){
		filter_price = filter_price.split('-');
		$('input[name="start_price"]').val(number_format(filter_price[0],0));
		$('input[name="end_price"]').val(number_format(filter_price[1],0));
	}

	$('.J_InfiniteList').infinite({pageper: 10, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			var template = TEMP.clone(true);
			$.each(data, function(k, goods) {
				html += sprintf(template.html(), "<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, goods.goods_name, goods.store_name, price_format(goods.price), goods.sales);
			});
			target.find('.infinite-result').append(html).show();
		}
	});
	
});
</script> 
<?php echo $this->fetch('footer.html'); ?>