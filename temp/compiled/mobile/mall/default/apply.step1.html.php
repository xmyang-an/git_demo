<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
$(function(){
	$(".J_Btn").click(function(){
		var agreement = $(".J_Agreement").prop('checked');
		if(agreement){
			location.href = REAL_SITE_URL + '/index.php?app=apply&step=2';
			return;
		}else{
			layer.open({content: "请阅读并同意入驻协议", className:'layer-popup', time: 3});
			return false;
		}
	});
});
</script>
<div id="main" class="w-full">
  <div class="page-actions"><i>&nbsp;</i></div>
  <div class="page-apply">
    <div class="content clearfix">
      <div class="apply-agreement" style="margin-bottom:70px">
        <div class="agreement-content"><?php echo $this->_var['setup_store']['content']; ?></div>
        <div class="agreement-btn">
          <label class="switch-checkbox-radio block J_SwtcherInput webkit-box" for="switcher"> <em class="block flex1">我已阅读并同意以上协议</em> <span class="switcher-style block"></span> </label>
          <input name="agreement" class="J_Agreement hidden" id="switcher" value="1" type="checkbox">
        </div>
        <div class="J_Btn"><a href="javascript:;" class="btn-alipay">下一步，填写商家信息</a></div>
      </div>
    </div>
  </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>