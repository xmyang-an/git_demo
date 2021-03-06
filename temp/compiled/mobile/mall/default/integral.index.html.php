<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div id="page-search-goods">
    <div class="page-body search-goods search-integral"> 
      
      <div class="listTab">
        <div class="fixed-gap"></div>
        <div class="fixed-bd active-line clearfix">
          <ul class="items clearfix" app="integral">
            <li><a href="javascript:void(0);" class="active" ectype="sort"><span>综合排序</span></a></li>
            <li><a href="javascript:void(0);" id="add_time-desc" ectype="sort"><span>最新</span></a></li>
            <li><a href="javascript:void(0);" id="sales-desc" ectype="sort"><span>销量</span></a></li>
            <li><a href="javascript:void(0);" id="price-desc" ectype="sort"><span>价格</span></a></li>
          </ul>
        </div>
      </div>
      
      
      <div class="squares goods-list clearfix J_InfiniteList">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <div class="infinite-template">
          <li> <a href="{1}" class="webkit-box block">
            <div class="pic ml10 pt10"><img src="<?php echo $this->res_base . "/" . 'images/empty.gif'; ?>"  class="lazyload" initial-url="{2}"></div>
            <div class="info flex1">
              <h2 class="goods-name line-clamp-2">{3}</h2>
              <p class="store-name">{4}<ins class="sales float-right">已售{5}</ins></p>
              <div class="pri-sales"> <span class="price">{6} <em class="fs10 f60">+{7}积分</em></span> </div>
            </div>
            </a> </li>
        </div>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>已经到底了<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i>
          <p>没有符合条件的记录</p>
        </div>
      </div>
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
	}
	
	$('.J_InfiniteList').infinite({pageper: 10, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			var template = TEMP.clone(true);
			$.each(data, function(k, goods) {
				html += sprintf(template.html(), "<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, goods.goods_name, goods.store_name, goods.sales, price_format(goods.price), goods.exchange);
			});
			target.find('.infinite-result').append(html).show();
		}
	});
});
</script> 
<?php echo $this->fetch('footer.html'); ?> 