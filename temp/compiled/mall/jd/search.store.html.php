<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'search_store.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
//<!CDATA[
$(function (){
	
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
	
	$('.list-fields li .row_3 a').click(function(){
		var cl=$(this).attr('class');
		if(cl=='expand'){
			$(this).attr('class','fold');	
			$(this).html('收起相关宝贝');
		}else{
			$(this).attr('class','expand');	
			$(this).html('展开相关宝贝');
		}
		$(this).parent().parent().parent('.store-info').next('.store-goods').toggle();
	});
	
	$('.attr-bottom .show-more').click(function(){
		$(this).parent().parent().children('.by-category').find('dl.hidden').toggle();
		if($(this).find('span').html()=='展开'){
			$(this).find('span').html('收起');
			$(this).attr('class', 'hide-more');
		} else {
			$(this).find('span').html('展开');
			$(this).attr('class', 'show-more');
			
		}
	});
	$('.search-by .more-it').click(function(){
		$(this).parent().parent().find('.hidden').toggle();
		if($(this).find('em').html() == '更多')
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
	
	
	$('.view-all-goods').click(function(){
		$(this).parent().parent().parent().children('.store-goods').toggle();
		var icon = $(this).children('i').attr('class');
		if(icon == 'put-icon')
		{
			$(this).children('i').attr('class','drop-icon');
		}
		else
		{
			$(this).children('i').attr('class','put-icon');
		}
	})
});

//]]>
</script>
<div id="main" class="w-full">
<div id="page-search-store" class="w mt10 mb10">  
	<?php echo $this->fetch('curlocal.html'); ?>
    <div class="w mb20 border relative wrap-by">
        <div class="search-by by-category relative">
			<?php $_from = $this->_var['scategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'scategory');$this->_foreach['fe_scategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_scategory']['total'] > 0):
    foreach ($_from AS $this->_var['scategory']):
        $this->_foreach['fe_scategory']['iteration']++;
?>
			<dl class="relative clearfix <?php if ($this->_foreach['fe_scategory']['iteration'] > 4): ?>hidden<?php endif; ?>">
				<dt class="float-left"><a href="<?php echo url('app=search&act=store&cate_id=' . $this->_var['scategory']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['scategory']['value']); ?></a></dt>
				<dd class="float-left">
					<?php if ($this->_var['scategory']['children']): ?>
					<?php $_from = $this->_var['scategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_child']['iteration']++;
?>
					<a href="<?php echo url('app=search&act=store&cate_id=' . $this->_var['child']['id']. ''); ?>" class="<?php if ($this->_foreach['fe_child']['iteration'] > 7): ?>toggle hidden<?php endif; ?>"><?php echo htmlspecialchars($this->_var['child']['value']); ?></a>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <?php else: ?>
                    &nbsp;
					<?php endif; ?>
				</dd>
                <dd class="float-left" style="position:absolute;top:3px; right:0;width:80px; height:30px;padding:0;"><span class="more-it clearfix"><em>更多</em><i></i></span></dd>
			</dl>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
		<div class="search-by by-region relative clearfix">
			<dl class="clearfix" style="border-bottom:0">
				<dt class="float-left"><a ectype="region" id="" href="javascript:;">所在地</a></dt>
				<dd class="float-left relative">
					<?php $_from = $this->_var['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'region');$this->_foreach['fe_region'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_region']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['region']):
        $this->_foreach['fe_region']['iteration']++;
?>
					<a href="javascript:;" ectype="region" id="<?php echo $this->_var['key']; ?>" class="<?php if ($this->_foreach['fe_region']['iteration'] >= 9): ?>toggle hidden<?php endif; ?>"><?php echo htmlspecialchars($this->_var['region']); ?></a>
					<?php endforeach; else: ?>
                    &nbsp;
					<?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</dd>
                <dd class="float-left" style="position:absolute;top:3px; right:0;"><span class="more-it clearfix"><em>更多</em><i></i></span></dd>
			</dl>
			
        </div>  
        <div class="attr-bottom">
            <ins></ins><b class="show-more"><span>展开</span>分类<i></i></b>
        </div>
    </div>
    <div class="shops-list w clearfix">
            <div  class="sort-type  mb10 clearfix">
               <div class="clearfix float-left btn-order">
                    <a class="btn-order-click default-sort" id="" href="javascript:;">默认排序</a>
                    <a class="btn-order-click order-down-gray" ectype="credit_value" id="credit_value" href="javascript:;">信用度<b></b></a>
                    <a class="btn-order-click order-down-gray" ectype="add_time" id="add_time" href="javascript:;">添加时间<b></b></a>
                    <a class="btn-order-click order-down-gray" ectype="praise_rate" id="praise_rate" href="javascript:;">好评率<b></b></a>
                    <a class="btn-order-click order-down-gray" ectype="region_name" id="region_name" href="javascript:;">所在地<b></b></a>
                </div>
               <div class="clearfix float-right mt5">
                    <a class="select-param"  href="javascript:;">
                    	信用度
                        <span><i></i></span>
                        <ul class="tan" ectype="credit_value">
                        	<li v="4">金冠店铺</li>
                        	<li v="3">皇冠店铺</li>
                            <li v="2">钻级店铺</li>
                            <li v="1">心级店铺</li>
                            <li v="">不限</li>
                        </ul>
                    </a>
                    <a class="select-param"  href="javascript:;">
                    	推荐
                        <span><i></i></span>
                        <ul class="tan" ectype="recommended">
                        	<li v="1">是</li>
                        	<li v="0">否</li>
                            <li v="">不限</li>
                        </ul>
                    </a>
                    <a class="select-param"  href="javascript:;">
                    	好评率
                        <span><i></i></span>
                        <ul class="tan" ectype="praise_rate">
                        	<li v="90">90%以上</li>
                        	<li v="80">80%以上</li>
                            <li v="70">70%以上</li>
                            <li v="60">60%以上</li>
                            <li v="50">50%以上</li>
                            <li v="">不限</li>
                        </ul>
                    </a>
                    <a class="select-param"  href="javascript:;">
                        店铺等级
                        <span><i></i></span>
                        <ul class="tan" ectype="sgrade">
                        	<?php $_from = $this->_var['sgrades']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'sgrade');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['sgrade']):
?>
                        	<li v="<?php echo $this->_var['key']; ?>"><?php echo $this->_var['sgrade']; ?></li>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            <li v="">不限</li>
                        </ul>
                    </a>
                </div>
            </div>
            <div class="list-fields w mb10">
				<ul>
					<?php $_from = $this->_var['stores']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');if (count($_from)):
    foreach ($_from AS $this->_var['store']):
?>
            		<li>
						<div class="store-info clearfix">
							<div class="row_1 float-left"><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" target="_blank"><img class="lazyload" initial-url="<?php echo $this->_var['store']['store_logo']; ?>" width="80" height="80" /></a></div>
                            <div class="row_2 float-left">
                                <h2><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" target="_blank"><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></a></h2>
                                <div class="d-info">
                                    <span>主营业务：</span><span><?php echo $this->_var['store']['business_scope']; ?></span>
                                </div>
                                <div class="d-info">
                                    <span>详细地址：</span><em><?php echo htmlspecialchars($this->_var['store']['address']); ?></em>
                                </div>
                                <div class="owner_info">
                                    <span>掌柜：</span>
                                    <a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['store']['user_name']); ?></a>
                                    <a href="javascript:;" style="background:none;padding-left:10px" class="J_StartLayim" data-toid="<?php echo $this->_var['store']['store_id']; ?>"><img style="vertical-align:middle" src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" /></a>
                                </div>
                            </div>
                            <div class="row_3 float-left">
                                <div class="rate-info">
                                    <p>
                                        <strong>店铺动态评分</strong>
                                        与行业相比
                                    </p>
                                    <ul>
                                        <li>
                                            商品评分
                                            <span class="credit"><?php echo $this->_var['store']['avg_goods_evaluation']; ?></span>
                                            <span class="<?php echo $this->_var['store']['industy_compare']['goods_compare']['class']; ?>">
                                                <i></i>
                                                <?php echo $this->_var['store']['industy_compare']['goods_compare']['name']; ?>
                                                <em><?php if ($this->_var['store']['industy_compare']['goods_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>%<?php endif; ?></em>
                                            </span>
                                        </li>
                                        <li>
                                            服务评分
                                            <span class="credit"><?php echo $this->_var['store']['avg_service_evaluation']; ?></span>
                                            <span class="<?php echo $this->_var['store']['industy_compare']['service_compare']['class']; ?>">
                                                <i></i>
                                                <?php echo $this->_var['store']['industy_compare']['service_compare']['name']; ?>
                                                <em><?php if ($this->_var['store']['industy_compare']['service_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['goods_compare']['value']; ?>%<?php endif; ?></em>
                                            </span>
                                        </li>
                                        <li>
                                            发货评分
                                            <span class="credit"><?php echo $this->_var['store']['avg_shipped_evaluation']; ?></span>
                                            <span class="<?php echo $this->_var['store']['industy_compare']['shipped_compare']['class']; ?>">
                                                <i></i>
                                                <?php echo $this->_var['store']['industy_compare']['shipped_compare']['name']; ?>
                                                <em><?php if ($this->_var['store']['industy_compare']['shipped_compare']['value'] == 0): ?>----<?php else: ?><?php echo $this->_var['store']['industy_compare']['shipped_compare']['value']; ?>%<?php endif; ?></em>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
        					</div>
                            <div class="row_4 float-left">
                            	<p>
                                    <?php if ($this->_var['store']['credit_value'] >= 0): ?>
                                    <img src="<?php echo $this->_var['store']['credit_image']; ?>" />
                                    <?php else: ?>
                                    <?php echo $this->_var['store']['credit_value']; ?>
                                    <?php endif; ?>
                                </p>
                                <p><?php echo $this->_var['store']['sgrade_name']; ?></p>
                                <p>好评率:<?php echo $this->_var['store']['praise_rate']; ?>%</p>
                                <p>店铺总共售出<b><?php echo $this->_var['store']['store_sold']; ?></b>件商品</p>
                            </div>
                            <div class="row_5 float-right">
                        	<a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" target="_blank" class="enter-shop">进入店铺<i> >></i></a>
 							<a href="javascript:;" class="view-all-goods"><em><?php echo $this->_var['store']['goods_count']; ?></em>件相关商品<i class="drop-icon"></i></a>
                        </div>
                        </div>
                        
                        <?php if ($this->_var['store']['goods_list']): ?>
						<div class="store-goods mt5 mb5 J_S<?php echo $this->_var['store']['store_id']; ?>">
                            <b></b>
							<a class="prev" href="javascript:;"></a>
							<a class="next" href="javascript:;"></a> 
                            <div class="clr"></div>
							<div class="scroller">
								<div class="ks-switchable-content">
									<?php $_from = $this->_var['store']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
                                    
                                    <?php $_from = $this->_var['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
									<dl>
										<dt><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img width="160" height="160" class="lazyload" initial-url="<?php echo $this->_var['goods']['default_image']; ?>" /></a></dt>
										<dd>
											<div class="desc"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['goods']['goods_name']; ?></a></div>
											<div class="price"><span><?php echo price_format($this->_var['goods']['price']); ?></span></div>
										</dd>
									</dl>
                                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                   
									<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
								</div>
							</div>
                            <div class="more-store-goods">
                            	<a href="<?php echo url('app=store&act=search&id=' . $this->_var['store']['store_id']. ''); ?>" target="_blank">更多相关商品 >></a>
                            </div>
						</div>
                        <script>
						$(function(){
							$(".J_S<?php echo $this->_var['store']['store_id']; ?>").slide({mainCell:".ks-switchable-content", titCell:".ks-switchable-nav span", effect:"leftLoop", scroll:1, vis:5, trigger:"click", prevCell:".prev", nextCell:".next", titOnClassName:"ks-active", autoPlay:false});
						});
						</script>
                        <?php endif; ?>
					</li>	
                    <?php endforeach; else: ?>
             		<div class="notice-word"><p class="yellow-big">很抱歉！没有找到相关店铺</p></div>
                    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
            	</ul>
            </div>
         
        	<div class="clearfix page-big center mt20"><?php echo $this->fetch('page.bottom.html'); ?></div>
    </div>
</div>
</div>						
<?php echo $this->fetch('footer.html'); ?>