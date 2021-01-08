<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div class="page-store page J_page">
    <div class="page-body search-goods relative">
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
          <li class="flex1"><a class="block center <?php if (! $_GET['new']): ?>active<?php endif; ?> fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. ''); ?>"><span>全部商品</span></a></li>
          <li class="flex1"><a class="block center <?php if ($_GET['new']): ?>active<?php endif; ?> fs14" href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. '&new=1'); ?>"><span>上新</span></a></li>
          <li class="flex1"><a class="block center fs14" href="<?php echo url('app=store&act=limitbuy&id=' . $this->_var['store']['store_id']. ''); ?>"><span>促销</span></a></li>
          
        </ul>
      </div>
      
      
      <div class="listTab">
        <div class="fixed-gap"></div>
        <div class="fixed-bd clearfix">
          <ul class="items clearfix">
            <li><a href="javascript:void(0);" class="active J_ActiveSort"><span>综合排序</span><i class="psmb-icon-font">&#xe61f;</i></a></li>
            <li><a href="javascript:void(0);" id="sales-desc" ectype="sort"><span>销量优先</span></a></li>
            <li><a href="javascript:;" class="goodsFilter J_PopLayer" data-PopLayer="{popLayer:'.J_GoodsFilterPopLayer', fixedBody: true, left:80, direction:'right'}">筛选<i class="psmb-icon-font">&#xe6cc;</i></a></li>
          </ul>
          <div class="list-style display-mode"> <span class="psmb-icon-font list clearfix J_ChangeDisplayMode" id="storeGoodsDisplayMode"></span> </div>
        </div>
      </div>
      
      
      <div class="sort-eject hidden J_SortEject move" onclick="$(this).slideUp();"> <span><a href="javascript:void(0);" class="active block webkit-box" ectype="sort"><ins class="flex1">综合排序</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="price-desc" ectype="sort" class="block webkit-box"><ins class="flex1">价格从高到低</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="price-asc" ectype="sort" class="block webkit-box"><ins class="flex1">价格从低到高</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="views-desc" ectype="sort" class="block webkit-box"><ins class="flex1">人气排序</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span> <span><a href="javascript:void(0);" id="add_time-desc" ectype="sort" class="block webkit-box"><ins class="flex1">上架从新到旧</ins><i class="psmb-icon-font hidden">&#xe694;</i></a></span></div>
      
      
      <div class="<?php echo $this->_var['display_mode']; ?> goods-list clearfix J_InfiniteList" ectype="current_display_mode" data-cookie="storeGoodsDisplayMode">
        <ul class="infinite-result clearfix hidden">
        </ul>
        <div class="infinite-template">
          <li> <a href="{1}" class="webkit-box block">
            <div class="pic ml10 pt10"><img src="<?php echo $this->res_base . "/" . 'images/empty.gif'; ?>"  class="lazyload" initial-url="{2}"></div>
            <div class="info flex1">
              <h2 class="goods-name line-clamp-2">{3}</h2>
              <p class="store-name">{4}条评论</p>
              <div class="pri-sales clearfix"> <span class="price float-left">{5}</span> <ins class="sales float-right">已售{6}</ins> </div>
            </div>
            </a> </li>
        </div>
        <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
        <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>我是有底线的<ins class="vline vright"></ins></div>
        <div class="infinite-empty notice-empty hidden"><i>&#xe715;</i>
          <p>很抱歉! 没有找到相关商品</p>
        </div>
      </div>
      
    </div>
  </div>
  <div class="pop-layer-common pop-wrap-r search-filter J_GoodsFilterPopLayer">
  <div class="wraper">
    <div class="bd">
      <div class="attrs">
        <div class="attr"> 
          <?php if ($this->_var['categories']): ?>
          <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
          <div class="attrk"><?php echo $this->_var['gcategory']['value']; ?></div>
          <div class="attrv">
            <ul class="clearfix"  ectype="ul_cate">
              <?php $_from = $this->_var['gcategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?>
              <li class="<?php if ($this->_foreach['fe_item']['iteration'] > 6): ?>hidden<?php endif; ?> <?php if ($_GET['cate_id'] == $this->_var['item']['id']): ?>active<?php endif; ?>"><a href="javascript:void(0);" id="<?php echo $this->_var['item']['id']; ?>"><span><?php echo $this->_var['item']['value']; ?></span></a></li>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
            <div class="options"> <a class="more" href="javascript:void(0);"><span>查看更多</span><i></i></a> </div>
          </div>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="ft webkit-box clearfix"> <span class="pop-btn flex1"> <a uri="<?php echo url('app=store&act=search&id=2&keyword=' . $_GET['keyword']. ''); ?>" href="javascript:;" class="popReset">重置</a></span> <span class="pop-btn flex1"><i class="popClosed">关闭</i></span> </div>
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
	var maxshow = "<?php if ($_GET['new']): ?>20<?php else: ?>''<?php endif; ?>";
	$('.J_InfiniteList').infinite({pageper: 10, params: <?php echo $this->_var['infiniteParams']; ?>, maxshow:maxshow, callback: function(data, page, target, TEMP){
			var html = '';
			var template = TEMP.clone(true);
			$.each(data, function(k, goods) {
				html += sprintf(template.html(), "<?php echo url('app=goods&id="+goods.goods_id+"'); ?>", goods.default_image, goods.goods_name, goods.comments, price_format(goods.price), goods.sales);
			});
			target.find('.infinite-result').append(html).show();
		}
	});
	
});
</script> 
<?php echo $this->fetch('footer.html'); ?>