<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div id="page-search-goods">
    <div class="page-body search-goods search-limitbuy">
    	
        
        <div class="listTab">
        	<div class="fixed-gap"></div>
            <div class="fixed-bd active-line clearfix">
            	<ul class="items clearfix" app="limitbuy">
                	<li><a href="javascript:void(0);" class="active" ectype="sort"><span>综合排序</span></a></li>
                	<li><a href="javascript:void(0);" id="start_time-asc" ectype="sort"><span>开始时间</span></a></li>
                    <li><a href="javascript:void(0);" id="end_time-asc" ectype="sort"><span>结束时间</span></a></li>
            	</ul>
            	<div class="list-style display-mode">
            		<span class="psmb-icon-font squares clearfix J_ChangeDisplayMode" id="limitbuyDisplayMode"></span>
            	</div>
           </div>
       </div>
    
    	
        <div class="<?php echo $this->_var['display_mode']; ?> goods-list clearfix J_InfiniteList" ectype="current_display_mode" data-cookie="limitbuyDisplayMode">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <ul class="infinite-template">
			<li>
        		<a href="{1}" class="webkit-box block">
          			<div class="pic ml10 pt10"><img src="<?php echo $this->res_base . "/" . 'images/empty.gif'; ?>"  class="lazyload" initial-url="{2}"></div>
            		<div class="info flex1">
                 		<h2 class="goods-name line-clamp-2">{3}</h2>
                		<p class="store-name">{4}<ins class="sales float-right">已售{5}</ins></p>
                        
                        <div class="countdown waiting mt5 fs12 gray"> <span class="time NumDays" >{6}</span><em>天</em> <span class="time NumHours">{7}</span><em>小时</em> <span class="time NumMins" >{8}</span><em>分</em> <span class="time NumSeconds">{9}</span><em>秒</em> </div>
                        
                    	<div class="pri-sales">
                        	<span class="price">{10}<del class="gray ml5 fs12">{11}</del></span> 
                        </div>
            		</div>
     			</a> 
			</li>
        </ul>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>已经到底了<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i><p>没有符合条件的记录</p></div>
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
	
	$('.J_InfiniteList').infinite({pageper: 15, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			var template = TEMP.clone(true);
			$.each(data, function(k, goods) {
				html += sprintf(template.html(), "<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, goods.goods_name, goods.store_name, goods.sales, goods.lefttime.d, goods.lefttime.h, goods.lefttime.m, goods.lefttime.s, price_format(goods.pro_price), price_format(goods.price));
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