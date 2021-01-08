<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
var clipboardText = new Clipboard('.J-CopyLink', {
    text: function() {
       return REAL_SITE_URL+'/index.php?app=member&act=register&r=<?php echo $this->_var['visitor']['user_id']; ?>';
    }
});

clipboardText.on('success', function(e) {
	layer.confirm('复制成功', {
	  btn: ['确认']
	}, function(index){
	  layer.close(index);
	});
});

clipboardText.on('error', function(e) {
   layer.confirm('复制失败', {
	  btn: ['确认']
	}, function(index){
	  layer.close(index);
	});
});

var clipboardQrode = new Clipboard('.J-CopyQrcode', {
    target: function() {
       return document.querySelector('.J-Qrcode');
    }
});

clipboardQrode.on('success', function(e) {
	layer.confirm('复制成功', {
	  btn: ['确认']
	}, function(index){
	  layer.close(index);
	});
});

clipboardQrode.on('error', function(e) {
   layer.confirm('复制失败', {
	  btn: ['确认']
	}, function(index){
	  layer.close(index);
	});
});
</script>
<div id="main" class="clearfix"> 
  <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
            <div class="public">
                <div class="information">
                    <div class="info">
                        <table>
                            <tr>
                                <th width="20"></th>
                                <td>我的推荐人:<font color="#f00" class="ml5"><?php if ($this->_var['parent_refers']): ?><?php echo $this->_var['parent_refers']['user_name']; ?><?php else: ?>自行注册<?php endif; ?></font><span class="ml20">我的团队人数：<strong><font color="#f00" class="fs14 ml5"><?php echo ($this->_var['my_refer_count'] == '') ? '0' : $this->_var['my_refer_count']; ?></font></strong></span><span class="ml20">我的推广奖励：<strong><font color="#f00" class="fs14 ml5"><?php echo price_format($this->_var['my_refer_amount']); ?></font></strong></span></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                	<div class="rebateLink mt20">
                                        <div class="qr J-Qrcode">
                                            <img width="150" src="<?php echo $this->_var['refer_qrcode']; ?>" />
                                        </div>
                                        <div class="btns mt20">
                                            <button class="J-CopyQrcode mr20">复制二维码</button>
                                            <button class="J-CopyLink">复制链接</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>
