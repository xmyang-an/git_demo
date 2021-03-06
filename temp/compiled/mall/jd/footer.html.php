<div id="footer" class="w-full">
	<div class="foot-baozhang">
		<div class="flow w">
			<div class="item first"><i class="zp"></i><span>正品保障</span></div>
			<div class="item"><i class="qt"></i><span>型号齐全</span></div>
			<div class="item"><i class="hp"></i><span>好评如潮</span></div>
			<div class="item"><i class="sd"></i><span>发货迅速</span></div>
			<div class="item"><i class="ry"></i><span>增值专票</span></div>
		</div>
	</div>
	<div class="foot-service">
		<div class="w clearfix">
			<dl class="dl-first">
				<dt class="fts-1">购物保障</dt>
				<dd><a href="#">100%全场正品</a></dd>
				<dd><a href="#">优质品牌</a></dd>

			</dl>
			<dl>
				<dt class="fts-2">新手上路</dt>
				<dd><a href="#">新手指南</a></dd>
				<dd><a href="#">官方微博</a></dd>
			</dl>
			<dl>
				<dt class="fts-3">服务热线</dt>
				<dd><a href="#">客服专线：400-659-9859</a></dd>
				<dd><a href="#">商家入驻：400-659-9859</a></dd>
			</dl>
			<dl>
				<dt class="fts-4">商家入驻</dt>
				<dd><a href="#">优质商家入驻商城</a></dd>
				<dd><a href="#">了解壹点</a></dd>
			</dl>
		</div>
	</div>
	<div class="foot-group w clearfix">
		<div class="float-left tel">
			<dl class="clearfix">
				<dt class="float-left"><img src="static/images/call.png"></dt>
				<dd class="float-left ml20 mt10">
					<h3>400-659-9859</h3>
					<p>周一到周五 9:00 - 18:00</p>
				</dd>
			</dl>
		</div>
		<div class="float-left footnav">
			<ul class="clearfix">
				<li class="float-left"><a href="/index.php?app=about&act=index" target="_blank">关于我们</a><span>|</span></li>
				<li class="float-left"><a href="/index.php?app=contact&act=index" target="_blank">联系我们</a><span>|</span></li>
				<li class="float-left"><a href="/index.php?app=intro&act=index" target="_blank">公司简介</a><span>|</span></li>
				<li class="float-left"><a href="/index.php?app=payment&act=index" target="_blank">支付方式</a><span>|</span></li>
				<li class="float-left"><a href="/index.php?app=law&act=index" target="_blank">技术服务</a><span>|</span></li>
				<li class="float-left"><a href="/index.php?app=cooperation&act=index" target="_blank">服务流程</a><span>|</span></li>
				<li class="float-left last"><a href="/index.php?app=law2&act=index" target="_blank">法律条款</a></li>
			</ul>
			<div class="copy">
				<p style="font-size:13px;">Copyright &copy; 2015-2020 <a href="#" class="ml5 mr5"> yatdim.com 版权所有<br />
						<?php if ($this->_var['icp_number']): ?><a href="http://www.miibeian.gov.cn" target="_blank" style="margin-right:20px;"><?php echo $this->_var['icp_number']; ?></a>
						<?php endif; ?><?php echo $this->_var['statistics_code']; ?></p>
			</div>
		</div>
	</div>
	<div class="foot-fixed J_FootFixed">
		<div class="foot-fixed-box">
			<a class="myim j-icon J_StartLayim" href="javascript:;" data-toid="4"><i>myim</i></a>
			<a class="qq j-icon J_Icon"><i>qq</i></a>
			<a class="code j-icon J_Icon"><i>code</i></a>
			<a class="tel j-icon J_Icon"><i>tel</i></a>
			<a class="backtop j-icon J_Icon"><i>back_top</i></a>

			<div style="display: none;" class="myim j-box J_Box"></div>
			<div style="display: none;" class="tencent j-tencent j-box J_Box">
				<a href="tencent://message/?uin=&Site=<?php echo $this->_var['site_url']; ?>&Menu=yes"><i class="tencent-i">tencent</i><b class="tencent-b"><span
						 class="tencent-span"><img style="overflow: hidden; width: 39px; height: 45px; left: 2px;" class="tencent-qq" src="<?php echo $this->_var['site_url']; ?>/static/images/qq-tencent.jpg"
							 alt=""></span></b>
					<p class="tencent-p">在线客服<br>
						点击交谈</p>
				</a></div>
			<div style="display: none;" class="code j-box J_Box"><strong>微信公众号：</strong>
				<img src="themes/mall/jd/styles/default/images/yatdim_gong.jpg" style="width: 150px;height: 150px;left: 30px;" />
				<i></i>
			</div>
			<div style="display: none;" class="tel j-box J_Box"><strong>服务热线：</strong>
				<p>400-659-9859</p>
				<i></i>
			</div>
			
		</div>
	</div>
	<script type="text/ecmascript">
		$(function() {
			$(".J_FootFixed").find(".J_Icon").hover(function() {
				$(".J_FootFixed").find(".J_Box").eq($(this).index()).show().siblings(".J_Box").hide();
			});
			$(".J_FootFixed").hover(function() {}, function() {
				$(".J_FootFixed").find(".J_Box").hide();
			});
		});
	</script>
</div>

<link type="text/css" href="<?php echo $this->lib_base . "/" . 'layui/css/layui.css'; ?>" rel="stylesheet" />
<?php if (! $this->_var['visitor']['store_id']): ?>
<style>.layui-layim-close{display:none}</style>
<?php endif; ?>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layui/layui.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layui/webim.js'; ?>" charset="utf-8"></script>
</body>
</html>
