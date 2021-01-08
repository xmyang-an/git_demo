<?php if ($_GET['app'] != 'goods'): ?>
<div class="store-info border mb10">
	<h3 class="border-b"><span><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></span></h3>
    <div class="content">
    	<dl class="border-b total_evaluation w-full clearfix">
        	<dt>综合评分：</dt>
            <dd>
            	<div class="raty">
                	<span style="width:<?php echo ($this->_var['store']['evaluation_rate'] == '') ? '0' : $this->_var['store']['evaluation_rate']; ?>;"></span>
                </div>
                <b><?php echo ($this->_var['store']['avg_evaluation'] == '') ? '0' : $this->_var['store']['avg_evaluation']; ?></b>
                分
            </dd>
        </dl>
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
        <dl class="border-b contact_us dl-c-s clearfix">
        	<dt>联系方式：</dt>
            <dd>
                <a href="javascript:;" class="J_StartLayim" data-toid="<?php echo $this->_var['store']['store_id']; ?>"><img src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" /></a>
            </dd>
        </dl>
        <dl class="dl-c-s w-full clearfix">
        	<dt>卖家名称：</dt>
            <dd><a style="color: #005EA6;font-family:Georgia, 'Times New Roman', Times, serif;" target="_blank" href="<?php echo url('app=message&act=send&to_id=' . htmlspecialchars($this->_var['store']['store_owner']['user_id']). ''); ?>" title="给他（她）发站内信？"><?php echo htmlspecialchars($this->_var['store']['store_owner']['user_name']); ?></a></dd>
        </dl>
         <dl style="padding-top:2px;" class="dl-c-s w-full clearfix">
        	<dt>信&nbsp;&nbsp;用&nbsp;&nbsp;度：</dt>
            <dd><?php if ($this->_var['store']['credit_value'] >= 0): ?><img src="<?php echo $this->_var['store']['credit_image']; ?>" alt="" align="absmiddle"/><?php endif; ?></dd>
        </dl>
        <?php if ($this->_var['store']['certifications']): ?>
        <dl style="padding-top:1px;" class="dl-c-s w-full clearfix">
        	<dt style="margin-top:2px;">认证：</dt>
            <dd>
            	<?php $_from = $this->_var['store']['certifications']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cert');if (count($_from)):
    foreach ($_from AS $this->_var['cert']):
?>
                <?php if ($this->_var['cert'] == "autonym"): ?>
                <a href="<?php echo url('app=article&act=system&code=cert_autonym'); ?>" target="_blank" title="实名认证"><img src="<?php echo $this->res_base . "/" . 'images/cert_autonym.gif'; ?>" /></a>
                <?php elseif ($this->_var['cert'] == "material"): ?>
                <a href="<?php echo url('app=article&act=system&code=cert_material'); ?>" target="_blank" title="实体店铺"><img src="<?php echo $this->res_base . "/" . 'images/cert_material.gif'; ?>" /></a>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </dd>
        </dl>
        <?php endif; ?>
        <?php if ($this->_var['store']['tel']): ?>
        <dl style="padding-top:1px;" class="dl-c-s w-full clearfix">
        	<dt>创店时间：</dt>
            <dd><?php echo local_date("Y-m-d",$this->_var['store']['add_time']); ?></dd>
        </dl>
        <dl style="padding-top:2px;" class="dl-c-s w-full clearfix">
        	<dt>联系电话：</dt>
            <dd><?php echo htmlspecialchars($this->_var['store']['tel']); ?></dd>
        </dl>
        <?php endif; ?>
        <?php if ($this->_var['store']['address']): ?>
        <dl style="padding-top:2px;padding-bottom:10px;" class="dl-c-s border-b w-full clearfix">
        	<dt>详细地址：</dt>
            <dd><?php echo htmlspecialchars($this->_var['store']['address']); ?></dd>
        </dl>
        <?php endif; ?>
        <div class="go2store">
        	<a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>">进入商家店铺</a>
            <a href="javascript:collect_store(<?php echo $this->_var['store']['store_id']; ?>)">收藏该店铺</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($this->_var['goods']['qrcode']): ?>
<div class="store-search goods-qrcode border mb10">
	<h3 class="border-b title"><span>商品二维码</span></h3>
	<div class="content">
		<img width="188" src="<?php echo $this->_var['goods']['qrcode']; ?>" />
	</div>
</div>
<?php endif; ?>

<div class="store-search mb10 border">
	<h3 class="border-b"><span>搜索</span></h3>
	<div class="content">
    	<form id="" name="" method="get" action="index.php">
             <input type="hidden" name="app" value="store" />
             <input type="hidden" name="act" value="search" />
             <input type="hidden" name="id" value="<?php echo $this->_var['store']['store_id']; ?>" />
             <input class="text border" type="text" name="keyword"  placeholder="输入搜索关键字"/>
             <input class="btn" type="submit" value="搜索" />
       </form>
    </div>
</div>

<div class="gcategroy mb10 border">
	<h3 class="border-b title"><span>商品分类</span></h3>
    <div class="content">
         <div class="sort-by">
         	 <p><a class="icon" href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search'); ?>">查看全部商品</a></p>
             <div class="order_type"><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&order=sales desc'); ?>">按销量</a><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&order=add_time desc'); ?>">按新品</a><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&order=price asc'); ?>">按价格</a><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&order=views asc'); ?>" style="margin-right:0px;">按人气</a></div>
         </div>     
         <ul class="list">
             <?php $_from = $this->_var['store']['store_gcates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');if (count($_from)):
    foreach ($_from AS $this->_var['gcategory']):
?>
             <li class="each">
                <h3><a  href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&cate_id=' . $this->_var['gcategory']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['gcategory']['value']); ?></a><i class="<?php if ($this->_var['gcategory']['children']): ?>expand<?php else: ?>close<?php endif; ?>"></i></h3>
                <ul class="sub_gcate <?php if ($_GET['cate_id'] != $this->_var['gcategory']['id']): ?>hidden<?php endif; ?>">
                <?php $_from = $this->_var['gcategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child_gcategory');if (count($_from)):
    foreach ($_from AS $this->_var['child_gcategory']):
?>
                	<li><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. '&act=search&cate_id=' . $this->_var['child_gcategory']['id']. ''); ?>"><?php echo htmlspecialchars($this->_var['child_gcategory']['value']); ?></a></li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          		</ul>
             </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php if ($this->_var['store']['store_gcates']): ?>
                   <script type="text/javascript">
					   $(function(){
					   		$('.gcategroy .content .list li h3 i').click(function(){
								var c=$(this).attr('class');
								if(c == 'expand')
								{
									$(this).attr('class','close');
								}else{
									$(this).attr('class','expand');
								}
								$(this).parent('h3').parent('li').children('.sub_gcate').toggle();
							})
							$('.current').parent('.sub_gcate').show();
					   });
				   </script>
                   <?php endif; ?>
       </ul>
    </div>
 </div>

<div class="store-search store-qrcode mb10">
	<ul class="title border clearfix">
		<li class="float-left" type="map">店铺地图</li>
		<li class="float-left" type="qrcode">店铺二维码</li>
    </ul>
	<div class="wrap">
		<div class="content border" style="padding:0;">
			<div id="allmap" class="allmap"></div>
		</div>
		<div class="content border hidden">
			<img width="188" src="<?php echo $this->_var['store']['qrcode']; ?>" />
		</div>
	</div>
</div>
<?php if ($this->_var['baidukey']['browser']): ?>
<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=<?php echo $this->_var['baidukey']['browser']; ?>"></script>
<?php endif; ?>
<script type="text/javascript">
$(function(){
	$(".store-qrcode .title li").hover(function(){
		$(".store-qrcode").find(".content").hide();
		$(".store-qrcode").find(".content:eq("+$(this).index()+")").show();
		
	});
	<?php if ($this->_var['baidukey']['browser']): ?>
	// 百度地图API功能
	var map = new BMap.Map("allmap");
	<?php if ($this->_var['store']['lat']): ?>
	var point = new BMap.Point(<?php echo $this->_var['store']['lng']; ?>,<?php echo $this->_var['store']['lat']; ?>);
	<?php else: ?>
	var point = new BMap.Point(116.400244,39.92556);
	<?php endif; ?>
	map.centerAndZoom(point, 15);
	map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
	map.addControl(new BMap.NavigationControl());// 左上角，添加比例尺   
	var marker = new BMap.Marker(point);  // 创建标注
	map.addOverlay(marker);               // 将标注添加到地图中
	marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
	<?php endif; ?>
});
</script>
<?php if ($this->_var['store']['hot_saleslist'] || $this->_var['store']['collect_goodslist']): ?>
<div class="hotsale border mb10 J_StoreLeftHotsale">
 	<h3 class="border-b"><span>商品排行榜</span></h3>
    <div class="content">
        <ul class="rank-nav">
            <li class="curr"><a><span>热门销售排行</span></a></li>
	        <li><a><span>热门收藏排行</span></a></li>
        </ul>
        <ul class="rank-c clearfix">
            <?php $_from = $this->_var['store']['hot_saleslist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'h_goods');$this->_foreach['fe_saleslist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_saleslist']['total'] > 0):
    foreach ($_from AS $this->_var['h_goods']):
        $this->_foreach['fe_saleslist']['iteration']++;
?>
            <li <?php if (($this->_foreach['fe_saleslist']['iteration'] == $this->_foreach['fe_saleslist']['total'])): ?>style="border:0;"<?php endif; ?> class="clearfix">
                <div class="pic border"><a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['h_goods']['goods_id']. ''); ?>">
                    <img width="40" height="40"  src="<?php echo $this->_var['h_goods']['default_image']; ?>"alt="<?php echo htmlspecialchars(sub_str($this->_var['h_goods']['goods_name'],20)); ?>" title="<?php echo htmlspecialchars($this->_var['h_goods']['goods_name']); ?>" /></a>
                </div>
		   	    <div class="desc">
                	<a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['h_goods']['goods_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['h_goods']['goods_name']); ?></a>
                </div>
		   		<div class="price"><strong><?php echo $this->_var['h_goods']['price']; ?>元</strong></div>
		   		<div class="sale">已售出 <strong><?php echo $this->_var['h_goods']['sales']; ?></strong> 件</div>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       </ul>
       <ul class="rank-c clearfix" style="display:none;">
            <?php $_from = $this->_var['store']['collect_goodslist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'c_goods');$this->_foreach['fe_collectlist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_collectlist']['total'] > 0):
    foreach ($_from AS $this->_var['c_goods']):
        $this->_foreach['fe_collectlist']['iteration']++;
?>
            <li <?php if (($this->_foreach['fe_collectlist']['iteration'] == $this->_foreach['fe_collectlist']['total'])): ?>style="border:0;"<?php endif; ?> class="clearfix">
                <div class="pic border"><a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['c_goods']['goods_id']. ''); ?>">
                      <img width="40" height="40"  src="<?php echo $this->_var['c_goods']['default_image']; ?>" alt="<?php echo htmlspecialchars(sub_str($this->_var['c_goods']['goods_name'],20)); ?>" title="<?php echo htmlspecialchars($this->_var['c_goods']['goods_name']); ?>" /></a>
                </div>
		   		<div class="desc"><a target="_blank" href="<?php echo url('app=goods&id=' . $this->_var['c_goods']['goods_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['c_goods']['goods_name']); ?></a></div>
		   		<div class="price"><strong><?php echo $this->_var['c_goods']['price']; ?>元</strong></div>
		   		<div class="collecter">收藏人气&nbsp;&nbsp;<?php echo $this->_var['c_goods']['collects']; ?></div>
          </li>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
  </div>
</div>
<script type="text/javascript">
$(function(){
	$('.J_StoreLeftHotsale .rank-nav li').hover(function(){
		var index= $(this).parent().children("li").index(this);
		$(this).parent().find('li').removeClass('curr');
		$(this).parent().find('li:eq('+index+')').addClass('curr');
		
		$('.J_StoreLeftHotsale .rank-c').hide();
		$('.J_StoreLeftHotsale').find('.rank-c:eq('+index+')').show();
	});
});
</script>
<?php endif; ?>

<?php if ($_GET['app'] == "store" && $_GET['act'] == "index" && $this->_var['partners']): ?>
<div class="partner mb10 border">
	<h3 class="border-b"><span>partner</span></h3>
	<div class="content">
		<ul class="w-full clearfix">
           <?php $_from = $this->_var['partners']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'partner');if (count($_from)):
    foreach ($_from AS $this->_var['partner']):
?>
           <li><a href="<?php echo $this->_var['partner']['link']; ?>" target="_blank"><?php echo htmlspecialchars($this->_var['partner']['title']); ?></a></li>
           <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       </ul>
    </div>
</div>
<?php endif; ?>

<?php if ($this->_var['goods_history'] && $_GET['app'] == 'goods'): ?>
<div class="goods-history mb10 border">
	<h3 class="border-b"><span>浏览历史</span></h3>
	<div class="content">
	 	<ul class="clearfix">
            <?php $_from = $this->_var['goods_history']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gh_goods');if (count($_from)):
    foreach ($_from AS $this->_var['gh_goods']):
?>
            <li><a href="<?php echo url('app=goods&id=' . $this->_var['gh_goods']['goods_id']. ''); ?>"><img src="<?php echo $this->_var['gh_goods']['default_image']; ?>" alt="<?php echo htmlspecialchars(sub_str($this->_var['gh_goods']['goods_name'],20)); ?>" title="<?php echo htmlspecialchars($this->_var['gh_goods']['goods_name']); ?>" /></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
</div>
<?php endif; ?>