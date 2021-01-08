<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
//<!CDATA[
$(function(){	
	$('.J_Region').ajaxSwitcher({
		model: '.switcher-region',
		url: REAL_SITE_URL+'/index.php?app=mlselection&type=region',
		title:'请选择地区',
		startId: 2
	});
	
	$('.filePicker_1').compressUpload({
		server: REAL_SITE_URL + '/index.php?app=apply&act=upload&type=1',
		pick: '.filePicker_1',
		fileVal: 'image_1',
		crop: true,
		callback: function(file, response, pick, target) {
			$(pick).find('img').remove();
			$(pick).append('<img src="'+response+"?"+Math.random()+'"/>');
			$('input[name="image_1"]').val(response);
		}
	});
	
	$('.filePicker_2').compressUpload({
		server: REAL_SITE_URL + '/index.php?app=apply&act=upload&type=2',
		pick: '.filePicker_2',
		fileVal: 'image_2',
		crop: true,
		callback: function(file, response, pick, target) {
			$(pick).find('img').remove();
			$(pick).append('<img src="'+response+"?"+Math.random()+'"/>');
			$('input[name="image_2"]').val(response);
		}
	});
});
//]]>
</script>
<div id="main" class="w-full">
  <div class="page-actions"><i></i></div>
  <div class="page-apply">
    <div class="apply-submit">
      <form class="fun-form-style" method="post" enctype="multipart/form-data">
        <div class="form">
        <dl class="clearfix">
          <dd class="webkit-box"> <span>店铺名称</span>
            <input type="text" name="store_name" value="<?php echo htmlspecialchars($this->_var['store']['store_name']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="店铺名称" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl class="J_Category J_PopLayer J_PopLayer__INIT" data-PopLayer="{popLayer:'.J_CategoryPopLayer',top:'35%', fixedBody: true}">
          <dd class="webkit-box"> <span>所属分类</span>
            <p class="flex1"></p>
            <i class="psmb-icon-font mr10 f99 box-align-center gray">&#xe629;</i> </dd>
        </dl>
        <div class="pop-layer-common category-pop-layer J_CategoryPopLayer">
          <div class="wraper has-title no-ft">
            <div class="hd"><i class="closed popClosed"></i>所属分类</div>
            <ul class="bd radioUiWraper">
              <?php $_from = $this->_var['scategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'category');$this->_foreach['fe_category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_category']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['category']):
        $this->_foreach['fe_category']['iteration']++;
?>
              <li class="webkit-box radioUiStyle radioUiStyle1 border-bottom popClosed <?php if (($this->_foreach['fe_category']['iteration'] <= 1)): ?>active<?php endif; ?> <?php if (($this->_foreach['fe_category']['iteration'] == $this->_foreach['fe_category']['total'])): ?>border-b-0<?php endif; ?>">
                <div class="lp flex1 webkit-box"><span class="pl10"><?php echo $this->_var['category']; ?></span></div>
                <div class="input rp box-align-center pr10" style="margin-right:10px;">
                  <input type="radio" name="cate_id" value="<?php echo $this->_var['key']; ?>" <?php if (($this->_foreach['fe_category']['iteration'] <= 1)): ?> checked="checked" <?php endif; ?>>
                </div>
              </li>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
          </div>
        </div>
        <dl class="J_Sgrade J_PopLayer J_PopLayer__INIT" data-PopLayer="{popLayer:'.J_SgradePopLayer',top:'35%', fixedBody: true}">
          <dd class="webkit-box"> <span>店铺等级</span>
            <p class="input flex1"></p>
            <i class="psmb-icon-font mr10 f99 box-align-center gray">&#xe629;</i> </dd>
        </dl>
        <div class="pop-layer-common sgrade-pop-layer J_SgradePopLayer">
          <div class="wraper has-title no-ft">
            <div class="hd"><i class="closed popClosed"></i>店铺等级</div>
            <ul class="bd radioUiWraper">
              <?php $_from = $this->_var['sgrades']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sgrade');$this->_foreach['fe_sgrade'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_sgrade']['total'] > 0):
    foreach ($_from AS $this->_var['sgrade']):
        $this->_foreach['fe_sgrade']['iteration']++;
?>
              <li class="webkit-box radioUiStyle radioUiStyle1 border-bottom popClosed <?php if ($this->_var['store']['sgrade'] == $this->_var['sgrade']['grade_id'] || ( ! $this->_var['store'] && ($this->_foreach['fe_sgrade']['iteration'] <= 1) )): ?>active<?php endif; ?> <?php if (($this->_foreach['fe_sgrade']['iteration'] == $this->_foreach['fe_sgrade']['total'])): ?>border-b-0<?php endif; ?>">
                <div class="lp flex1 webkit-box"><span class="pl10"><?php echo $this->_var['sgrade']['grade_name']; ?></span></div>
                <div class="input rp box-align-center pr10" style="margin-right:10px;">
                  <input type="radio" name="sgrade_id" value="<?php echo $this->_var['sgrade']['grade_id']; ?>" <?php if ($this->_var['store']['sgrade'] == $this->_var['sgrade']['grade_id'] || ( ! $this->_var['store'] && ($this->_foreach['fe_sgrade']['iteration'] <= 1) )): ?> checked="checked" <?php endif; ?>>
                </div>
              </li>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
          </div>
        </div>
        <dl class="mt10 clearfix">
          <dd class="webkit-box"> <span>店主姓名</span>
            <input type="text" name="owner_name" value="<?php echo htmlspecialchars($this->_var['store']['owner_name']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="店主姓名" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl class="clearfix">
          <dd class="webkit-box"> <span>身份证号</span>
            <input type="text" name="owner_card" value="<?php echo htmlspecialchars($this->_var['store']['owner_card']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="身份证号" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl class="clearfix">
          <dd class="webkit-box"> <span>联系电话</span>
            <input type="text" name="tel" value="<?php echo htmlspecialchars($this->_var['store']['tel']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="联系电话" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl class="mt10 edit-region J_Region">
          <dd class="webkit-box"> <span>所在地区</span>
            <input type="hidden" name="region_id" value="<?php echo $this->_var['store']['region_id']; ?>" class="mls_id" />
            <input type="hidden" name="region_name" value="<?php echo htmlspecialchars($this->_var['store']['region_name']); ?>" class="mls_names" />
            <p class="flex1 mls_names box-align-center gray"><?php echo htmlspecialchars($this->_var['store']['region_name']); ?></p>
            <i class="psmb-icon-font box-align-center mr10 gray">&#xe629;</i> </dd>
        </dl>
        <dl class="clearfix">
          <dd class="webkit-box"> <span>详细地址</span>
            <input type="text" name="address" value="<?php echo htmlspecialchars($this->_var['store']['address']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="详细地址" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl class="clearfix">
          <dd class="webkit-box"> <span>邮政编码</span>
            <input type="text" name="zipcode" value="<?php echo htmlspecialchars($this->_var['store']['zipcode']); ?>" class="input clearInput flex1" oninput="javascript:clearInput(this)" placeholder="邮政编码" />
            <i class="input-del J_InputDel psmb-icon-font hidden">&#xe66e;</i> </dd>
        </dl>
        <dl>
          <dt><span>上传证件</span></dt>
          <dd class="webkit-box add-image">
            <div class="mr10">
              <div class="add-image-btn filePicker_1"> <i class="psmb-icon-font">&#xe6e5;</i> 
                <?php if ($this->_var['store']['image_1']): ?><img src="<?php echo $this->_var['store']['image_1']; ?>" /><?php endif; ?>
                <input type="hidden" name="image_1" />
              </div>
            </div>
            <p class="flex1 mr10">身份证正反面，大小不超过400KB</p>
            <div class="mr10">
              <div class="add-image-btn filePicker_2"> <i class="psmb-icon-font">&#xe6e5;</i> 
                <?php if ($this->_var['store']['image_2']): ?><img src="<?php echo $this->_var['store']['image_2']; ?>" /><?php endif; ?>
                <input type="hidden" name="image_2"/>
              </div>
            </div>
            <p class="flex1">企业营业执照，大小不超过400KB</p>
          </dd>
        </dl>
        <div class="extra">
          <input class="btn-alipay J_AjaxFormSubmit" type="submit" value="下一步，店铺审核或开通" />
        </div>
      </form>
    </div>
  </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>