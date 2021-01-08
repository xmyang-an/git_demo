<?php echo $this->fetch('top.html'); ?>
<script type="text/javascript">
//<!CDATA[
$(function(){
    regionInit("region");
    $("#apply_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
			error_td.find('label').remove();
            error_td.siblings('dd').remove();
            error_td.append(error);
        },
        success: function(label){
            label.addClass('validate_right').removeClass('error');
        },
        onkeyup: false,
        rules: {
            owner_name: {
                required: true
            },
            store_name: {
                required: true,
                remote : {
                    url  : 'index.php?app=apply&act=check_name&ajax=1',
                    type : 'get',
                    data : {
                        store_name : function(){
                            return $('#store_name').val();
                        },
                        store_id : '<?php echo $this->_var['store']['store_id']; ?>'
                    }
                },
                maxlength: 20
            },
            tel: {
                required: true,
                minlength:6
            },
            image_1: {
                //accept: "jpg|jpeg|png|gif"//高版本jquery不支持
            },
            image_2: {
                //accept: "jpg|jpeg|png|gif"//高版本jquery不支持
            },
            notice: {
                required : true
            }
        },
        messages: {
            owner_name: {
                required: '请输入店主姓名'
            },
            store_name: {
                required: '请输入店铺名称',
                remote: '该店铺名称已存在，请您换一个',
                maxlength: '请控制在20个字以内'
            },
            tel: {
                required: '请输入联系电话',
                minlength: '请填写正确的电话号码'
            },
            image_1: {
                accept: '请上传格式为 jpg,jpeg,png,gif 的文件'
            },
            image_2: {
                accept: '请上传格式为 jpg,jpeg,png,gif 的文件'
            },
            notice: {
                required: '请阅读并同意入驻协议'
            }
        }
    });

	<?php if ($this->_var['store'] && $this->_var['store']['sgrade']): ?>
	$(".apply-submit li[sgid='<?php echo $this->_var['store']['sgrade']; ?>']").addClass('selected');
	<?php else: ?>
	$(".apply-submit .each:eq(0)").addClass('selected');
	$('input[name="sgrade_id"]').val($(".apply-submit .each:eq(0)").attr('sgid'));
	<?php endif; ?>

	$(".apply-submit .each").click(function(){
		$(this).addClass('selected');
		$(this).siblings().removeClass('selected');
		$('input[name="sgrade_id"]').val($(this).attr('sgid'));
	});
});
//]]>
</script>
<style type="text/css">
.w{width:1000px;}
</style>
<div id="main" class="w-full">
	<div class="page-apply">
		<div class="w logo mt10">
			<p><a href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a></p>
		</div>
		<div class="w content clearfix">
			<div class="left">
            	<div class="steps">
                    <dl class="setpbox">
                        <dt>申请步骤</dt>
                        <dd>
                            <ul>
                                <li class="succeed">入驻指南</li>
                                <li class="succeed">签订入驻协议</li>
                                <li class="current">填写商家信息</li>
                                <li>平台审核</li>
                                <li>店铺开通</li>
                            </ul>
                        </dd>
                    </dl>
                    <dl class="setpbox contact-mall mt10">
                        <dt>平台联系方式</dt>
                        <dd>
                            <p class="tel"><span>电话：</span><?php echo $this->_var['setting']['phone']; ?></p>
                            <p class="email mt10"><span>邮箱：</span><?php echo $this->_var['setting']['email']; ?></p>
                        </dd>
                    </dl>
                </div>
			</div>
			<div class="right">
				<div class="apply-submit">
				  <form method="post" enctype="multipart/form-data" action="<?php echo url('app=apply&step=2'); ?>" id="apply_form">
						<div class="sgrade clearfix">
							<div class="dt">店铺等级：</div>
							<ul class="clearfix">
								<?php $_from = $this->_var['sgrades']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sgrade');$this->_foreach['fe_sgrade'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_sgrade']['total'] > 0):
    foreach ($_from AS $this->_var['sgrade']):
        $this->_foreach['fe_sgrade']['iteration']++;
?>
								<li class="each" sgid="<?php echo $this->_var['sgrade']['grade_id']; ?>" <?php if ($this->_foreach['fe_sgrade']['iteration'] % 3 == 0): ?>style="margin-right:0"<?php endif; ?>>
									<h2><?php echo $this->_var['sgrade']['grade_name']; ?></h2>
									<p>
										<span>商品数：<em><?php echo $this->_var['sgrade']['goods_limit']; ?></em></span><br />
										<span>上传空间(MB)：<em><?php echo $this->_var['sgrade']['space_limit']; ?></em></span>
									</p>
									<p>
										<span>模板数：<em><?php echo $this->_var['sgrade']['skin_limit']; ?></em></span><br />
										<span>收费标准：<em><?php echo $this->_var['sgrade']['charge']; ?></em></span>
									</p>
									<p>附加功能：
									   <?php $_from = $this->_var['sgrade']['functions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'functions');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['k'] => $this->_var['functions']):
        $this->_foreach['v']['iteration']++;
?>
									   <?php if ($this->_var['domain'] && $this->_var['k'] == 'subdomain'): ?>
									   <span>二级域名</span>
									   <?php else: ?>
									   <span><?php echo $this->_var['lang'][$this->_var['k']]; ?></span>
									   <?php endif; ?>
									   <?php if (! ($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?>
									   <?php endif; ?>
									   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
									</p>
								</li>
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							</ul>
							<input name="sgrade_id" type="hidden" value="<?php echo $this->_var['store']['sgrade']; ?>" />
						</div>
					   <dl>
						  <dt><span class="f60">*</span>店主姓名：</dt>
						  <dd class="widdt7"><input type="text" class="input border" name="owner_name" value="<?php echo htmlspecialchars($this->_var['store']['owner_name']); ?>"/></dd>
					   </dl>
					   <dl>
						  <dt>身份证号：</dt>
						  <dd><input type="text" class="input border" name="owner_card" value="<?php echo htmlspecialchars($this->_var['store']['owner_card']); ?>" /></dd>
					   </dl>
					   <dl>
						  <dt><span class="f60">*</span>店铺名称：</dt>
						  <dd><input type="text" class="input border" name="store_name" id="store_name" value="<?php echo htmlspecialchars($this->_var['store']['store_name']); ?>"/></dd>
						  
					   </dl>
					   <dl>
						  <dt>所属分类：</dt>
						  <dd>
							 <div class="select_add">
								<select name="cate_id">
								   <option value="0">请选择...</option>
								   <?php echo $this->html_options(array('options'=>$this->_var['scategories'],'selected'=>$this->_var['scategory']['cate_id'])); ?>
								</select>
							 </div>
						  </dd>
						  <dd></dd>
					   </dl>
					   <dl>
						  <dt>所在地区：</dt>
						  <dd>
							  <div class="select_add" id="region" style="widdt:500px;">
								  <input type="hidden" name="region_id" value="<?php echo $this->_var['store']['region_id']; ?>" class="mls_id" />
								  <input type="hidden" name="region_name" value="<?php echo $this->_var['store']['region_name']; ?>" class="mls_names" />
								  <?php if ($this->_var['store']['region_name']): ?>
								  <span><?php echo htmlspecialchars($this->_var['store']['region_name']); ?></span>
								  <input type="button" value="编辑" class="edit_region" />
								  <?php endif; ?>
								  <select class="d_inline"<?php if ($this->_var['store']['region_name']): ?> style="display:none;"<?php endif; ?>>
									 <option value="0">请选择...</option>
									 <?php echo $this->html_options(array('options'=>$this->_var['regions'])); ?>
								  </select>
							   </div>
						   </dd>
						   <dd></dd>
						</dl>
						<dl>
							<dt>详细地址：</dt>
							<dd><input type="text" class="input border" name="address" value="<?php echo htmlspecialchars($this->_var['store']['address']); ?>"/></dd>
						</dl>
						<dl>
							<dt>邮政编码：</dt>
							<dd><input type="text" class="input border" name="zipcode" value="<?php echo htmlspecialchars($this->_var['store']['zipcode']); ?>"/></dd>
							
						 </dl>
						 <dl>
							 <dt><span class="f60">*</span>联系电话：</dt>
							 <dd>
								 <input type="text" class="input border" name="tel"  value="<?php echo htmlspecialchars($this->_var['store']['tel']); ?>"/>
							 </dd>
							
						  </dl>
						  <dl class="clearboth">
							 <dt>上传证件：</dt>
							 <dd><input type="file" name="image_1" />
								   <?php if ($this->_var['store']['image_1']): ?>
									<p class="d_inline"><img src="<?php echo $this->_var['store']['image_1']; ?>" width="50" style="vertical-align:middle;" /> <a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['image_1']; ?>" target="_blank">查看</a></p>
									<?php endif; ?> </dd>
							 </dd>
							 <dd><span class="field_notice">支持格式jpg,jpeg,png,gif，请保证图片清晰且文件大小不超过400KB</span></dd>
						  </dl>
						  <dl class="clearboth">
							 <dt>上传执照：</dt>
							 <dd><input type="file" name="image_2" />
								 <?php if ($this->_var['store']['image_2']): ?><p class="d_inline"><img src="<?php echo $this->_var['store']['image_2']; ?>" width="50" style="vertical-align:middle;" /> <a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['image_2']; ?>" target="_blank">查看</a></p><?php endif; ?>
							 </dd>
							 <dd><span class="field_notice">支持格式jpg,jpeg,png,gif，请保证图片清晰且文件大小不超过400KB</span></dd>
						  </dl>
						  <dl class="clearboth">
						  	<dt>&nbsp;</dt>
						  	<dd><input class="btn-apply border0 sdlong fff pointer" type="submit" value="提交" /></dd>
						 </dl>
				   </form>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>