<?php echo $this->fetch('header.html'); ?> 
<div id="main">
  <div id="page-search-store" class="page J_page">
    <div class="page-body search-store">
      
		<div class="listTab mall-menus">
  			<div class="fixed-gap"></div>
        	<div class="fixed-bd">
        		<ul class="items webkit-box">
          			<li class="flex1"><a class="block center fs14" href="<?php echo url('app=search'); ?>"><span>商品</span></a></li>
          			<li class="flex1"><a class="block center active fs14" href="javascript:;"><span>店铺</span></a></li>
          			<li class="flex1"><a class="block center fs14" href="<?php echo url('app=limitbuy'); ?>"><span>促销</span></a></li>
          			<li class="flex1"><a class="block center fs14" href="<?php echo url('app=integral'); ?>"><span>积分</span></a></li>
        		</ul>
        	</div>
		</div>
		
      	<div class="listTab">
      		<div class="fixed-gap">
        	<div class="fixed-bd clearfix">
        	<ul class="items clearfix">
          		<li><a href="javascript:void(0);" class="active J_ActiveSort"><span>综合排序</span><i class="psmb-icon-font">&#xe61f;</i></a></li>
          		<li><a href="javascript:void(0);" id="praise_rate-desc" ectype="sort"><span>好评率</span></a></li>
          		<li><a href="javascript:;" class="goodsFilter J_PopLayer" data-PopLayer="{popLayer:'.J_StoreFilterPopLayer', fixedBody: true, left:80, direction:'right'}"><span>筛选</span><i class="psmb-icon-font">&#xe6cc;</i></a></li>
        	</ul>
        </div>
      </div>
      
      
      <div class="sort-eject hidden J_SortEject" onclick="$(this).slideUp();"> <span><a href="javascript:void(0);" class="active block webkit-box" ectype="sort"><ins class="flex1">综合排序</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="credit_value-desc" ectype="sort" class="block webkit-box"><ins class="flex1">信用度</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="add_time-desc" ectype="sort" class="block webkit-box"><ins class="flex1">开店时间</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="region_name-desc" ectype="sort" class="block webkit-box"><ins class="flex1">所在地</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> </div>
      
      
      <div class="shop-list clearfix J_InfiniteList">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <div class="infinite-template">
          <li>
            <dl>
              <dt> <a href="{1}" class="webkit-box"> <img src="{2}" class="lp" />
                <div class="mp flex1 ml10 mr10"> <span class="block mt5">{3}<img class="ml5" src="{4}" style="vertical-align:middle" /></span>
                  <p><ins class="fs12 f99 overflow-ellipsis">地址：{5}</ins></p>
                  <p><span class="flex1 f60">距离{6}</span></p>
                </div>
                <div class="rp psmb-icon-font">&#xe634;</div>
                </a> </dt>
              <dd>
                <div class="goods-list webkit-box"> <a href="{1}" class="block">
                  <div class="wraper relative"> <img src="{2}" />
                    <div class="price">{3}</div>
                  </div>
                  </a> </div>
              </dd>
            </dl>
          </li>
        </div>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>已经到底了<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i><p>没有数据了</p></div>
      </div>
      
    </div>
  </div>
</div>
<div class="pop-layer-common pop-wrap-r search-filter J_StoreFilterPopLayer">
  <div class="wraper">
    <div class="bd">
      <div class="attrs">
        <div class="attr"> 
          <?php if ($this->_var['scategories']): ?>
          <div class="attrk">店铺分类</div>
          <div class="attrv">
            <ul class="clearfix"  ectype="ul_cate">
              <?php $_from = $this->_var['scategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scategory');$this->_foreach['fe_scategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_scategory']['total'] > 0):
    foreach ($_from AS $this->_var['scategory']):
        $this->_foreach['fe_scategory']['iteration']++;
?>
              <li class="<?php if ($_GET['cate_id'] == $this->_var['scategory']['id']): ?>active<?php endif; ?> <?php if ($this->_foreach['fe_scategory']['iteration'] > 6): ?> hidden<?php endif; ?>"><a href="javascript:void(0);" id="<?php echo $this->_var['scategory']['id']; ?>"><span><?php echo $this->_var['scategory']['value']; ?></span></a></li>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
            <?php if ($this->_var['scategory']['count'] > 6): ?>
            <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
            <?php endif; ?> 
          </div>
          <?php endif; ?> 
          <?php if ($this->_var['regions']): ?>
          <div class="attrk">所在地区</div>
          <div class="attrv">
            <ul class="clearfix" ectype="ul_region">
              <?php $_from = $this->_var['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?>
              <li class="<?php if ($_GET['region_id'] == $this->_var['key']): ?>active<?php endif; ?> <?php if ($this->_foreach['fe_item']['iteration'] > 6): ?> hidden<?php endif; ?>"><a href="javascript:void(0);" id="<?php echo $this->_var['key']; ?>"><span><?php echo $this->_var['item']; ?></span></a></li>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
            <?php if ($this->_foreach['fe_item']['iteration'] > 6): ?>
            <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
            <?php endif; ?> 
          </div>
          <?php endif; ?> 
        </div>
      </div>
    </div>
    <div class="ft webkit-box clearfix"> <span class="pop-btn flex1"> <a uri="<?php echo url('app=search&act=store&keyword=' . $_GET['keyword']. ''); ?>" href="javascript:;" class="popReset">重置</a></span> <span class="pop-btn flex1"><i class="popClosed">关闭</i></span> </div>
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
		
		if(activeSort == 'praise_rate-desc'){
			$(".J_ActiveSort").removeClass('active').find('span').text('综合排序');
		}else{
			$(".J_ActiveSort").find('span').text($('#'+activeSort).find('ins').text());
		}
	}
	
	<?php if ($this->_var['baidukey']): ?>
	if($.getCookie('pointLng') && $.getCookie('pointLat')){
		getStoreList();
	}
	else
	{
		var geolocation = new BMap.Geolocation();
		geolocation.getCurrentPosition(function(r){
			if(this.getStatus() == BMAP_STATUS_SUCCESS){
				$.setCookie('pointLng', r.point.lng);
				$.setCookie('pointLat', r.point.lat);
				getStoreList();
			}
			else {
				layer.open({content: this.getStatus(), time: 3});
			}        
		},{enableHighAccuracy: true})
	}
	<?php else: ?>
	getStoreList();
	<?php endif; ?>
});

function getStoreList(){	
	$('.J_InfiniteList').infinite({pageper: 10, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			$.each(data, function(k, v) {
				var items = '';
				var template = TEMP.clone(true);
				$.each(v.goods_list, function(k1, goods) {
					items += sprintf(template.find('.goods-list').html(),
						"<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, price_format(goods.price));
				});
				if(items) {
					template.find('.goods-list').html(items);
				} else template.find('.goods-list').parent().remove();
				html += sprintf(template.html(), "<?php echo url('app=store&id="+v.store_id+"'); ?>", v.store_logo, v.store_name, v.credit_image, v.address, v.distance);
			});
			target.find('.infinite-result').append(html).show();
		}
	});
}
</script>
<?php echo $this->fetch('footer.html'); ?>