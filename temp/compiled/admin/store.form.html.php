<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript">
//<!CDATA[
$(function(){
    regionInit("region");

    $("#tr_close_reason").hide();
<?php if ($_GET['act'] == "edit"): ?>
    $(":radio[name='state']").click(function(){
        if (this.value == 2)
        {
            $("#tr_close_reason").show();
        }
        else
        {
            $("#tr_close_reason").hide();
        }
    });
    $(":radio[name='state']:checked").click();
<?php endif; ?>

    $('#store_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('right').text('OK!');
        },
        onkeyup    : false,
        rules : {
            owner_name: {
                required: true
            },
            store_name: {
                required : true,
                remote : {
                    url  : 'index.php?app=store&act=check_name',
                    type : 'get',
                    data : {
                        store_name : function(){
                            return $('#store_name').val();
                        },
                        id : '<?php echo $this->_var['store']['store_id']; ?>'
                    }
                }
            },
            end_time : {
                dateISO : true
            }
        },
        messages : {
            owner_name: {
                required: '请输入店主姓名'
            },
            store_name: {
                required: '请输入店铺名称',
                remote: '该店铺名称已经存在，请您换一个'
            },
            end_time : {
                dateISO : '格式：2009-4-30，留空表示不限时间'
            }
        }
    });
});
//]]>
</script>
<div id="rightTop">
  <p>店铺管理</p>
  <ul class="subnav">
    <li><a class="btn1" href="index.php?app=store">管理</a></li>
    <li><span><?php if ($this->_var['store']['store_id']): ?>编辑<?php else: ?>新增<?php endif; ?></span></li>
    <li><a class="btn1" href="index.php?app=store&amp;wait_verify=0">待审核</a></li>
	<li><a class="btn1" href="index.php?app=store&amp;wait_verify=3">已拒绝</a></li>
  </ul>
</div>

<div class="explanation" id="explanation">
  <div class="title" id="checkZoom">
  	<i class="fa fa-lightbulb-o"></i>
    <h4 title="操作提示">操作提示</h4>
    <span id="explanationZoom" title="收起提示"></span>
  </div>
  <ul>
    <li><i class="fa fa-angle-double-right"></i> 请填写店铺信息,提交完成操作。</li>
  </ul>
</div>
<div class="info">
  <form method="post" enctype="multipart/form-data" id="store_form">
    <table class="infoTable">
      <?php if ($_GET['act'] == "add"): ?>
      <tr>
        <th class="paddingT15">店主用户名:</th>
        <td class="paddingT15 wordSpacing5"><?php echo $this->_var['user']['user_name']; ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <th class="paddingT15">店主姓名:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="owner_name" type="text" id="owner_name" value="<?php echo htmlspecialchars($this->_var['store']['owner_name']); ?>" /></td>
      </tr>
      <tr>
        <th class="paddingT15">店主身份证号:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput" name="owner_card" type="text" id="owner_card" value="<?php echo htmlspecialchars($this->_var['store']['owner_card']); ?>" /></td>
      </tr>
      <tr>
        <th class="paddingT15"> 店铺名称:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput" name="store_name" type="text" id="store_name" value="<?php echo htmlspecialchars($this->_var['store']['store_name']); ?>"/>        </td>
      </tr>
      <?php if ($this->_var['enabled_subdomain']): ?>
      <tr>
        <th class="paddingT15"> 二级域名:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput" name="domain" type="text" id="domain" value="<?php echo htmlspecialchars($this->_var['store']['domain']); ?>"/></td>
      </tr>
      <?php endif; ?>
      <tr>
        <th class="paddingT15">所属分类:</th>
        <td class="paddingT15 wordSpacing5" ><select name="cate_id">
          <option value="0">请选择...</option>
                <?php echo $this->html_options(array('options'=>$this->_var['scategories'],'selected'=>$this->_var['scates']['0']['cate_id'])); ?>
        </select></td>
      </tr>
      <tr>
        <th class="paddingT15"> 所在地:</th>
        <td class="paddingT15 wordSpacing5" ><div id="region">
            <input type="hidden" name="region_id" value="<?php echo $this->_var['store']['region_id']; ?>" class="mls_id" />
            <input type="hidden" name="region_name" value="<?php echo htmlspecialchars($this->_var['store']['region_name']); ?>" class="mls_names" />
            <?php if ($this->_var['store']['store_id']): ?>
            <span><?php echo htmlspecialchars($this->_var['store']['region_name']); ?></span>
            <input type="button" value="编辑" class="edit_region default-btn" />
            <select style="display:none">
              <option>请选择...</option>
              <?php echo $this->html_options(array('options'=>$this->_var['regions'])); ?>
            </select>
            <?php else: ?>
            <select>
              <option>请选择...</option>
              <?php echo $this->html_options(array('options'=>$this->_var['regions'])); ?>
            </select>
            <?php endif; ?>
          </div></td>
      </tr>
      <tr>
        <th class="paddingT15">详细地址:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput" name="address" type="text" id="address" value="<?php echo htmlspecialchars($this->_var['store']['address']); ?>"/></td>
      </tr>
      <tr>
        <th class="paddingT15">邮政编码:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="zipcode" type="text" id="zipcode" value="<?php echo htmlspecialchars($this->_var['store']['zipcode']); ?>" /></td>
      </tr>
      <tr>
        <th class="paddingT15">联系电话:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="tel" type="text" id="tel" value="<?php echo htmlspecialchars($this->_var['store']['tel']); ?>" /></td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="sgrade"> 所属等级: </label>        </th>
        <td class="paddingT15 wordSpacing5"><select name="sgrade" id="sgrade">
          <?php echo $this->html_options(array('options'=>$this->_var['sgrades'],'selected'=>$this->_var['store']['sgrade'])); ?>
          </select>        </td>
      </tr>
      <tr>
          <th class="paddingT15">有效期至:</th>
          <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="end_time" type="text" id="end_time" value="<?php echo local_date("Y-m-d",$this->_var['store']['end_time']); ?>" /> <label class="field_notice">格式：2009-4-30，留空表示不限时间</label></td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="state">状态:</label></th>
        <td class="paddingT15">
            <span class="onoff">
          	<label class="cb-enable <?php if ($this->_var['store']['state'] == 1): ?>selected<?php endif; ?>">开启</label>
          	<label class="cb-disable <?php if ($this->_var['store']['state'] == 2): ?>selected<?php endif; ?>">关闭</label>
            <input name="state" value="1" type="radio" <?php if ($this->_var['store']['state'] == 1): ?>checked<?php endif; ?>>
            <input name="state" value="2" type="radio" <?php if ($this->_var['store']['state'] == 2): ?>checked<?php endif; ?>>
          </span>
        </td>
      </tr>
      <tr id="tr_close_reason">
          <th class="paddingT15" valign="top">关闭原因:</th>
          <td class="paddingT15 wordSpacing5"><label for="close_reason"></label>
              <textarea name="close_reason" id="close_reason"></textarea></td>
      </tr>
      <tr>
        <th class="paddingT15"> 认证:</th>
        <td class="paddingT15 wordSpacing5"><label for="autonym">
          <input name="autonym" type="checkbox" id="autonym" value="1" <?php if ($this->_var['store']['cert_autonym']): ?>checked="checked"<?php endif; ?> />
          实名认证</label>
          <label for="material">
          <input type="checkbox" name="material" value="1" id="material" <?php if ($this->_var['store']['cert_material']): ?>checked="checked"<?php endif; ?> />
          实体店铺认证</label></td>
      </tr>
      <tr>
        <th class="paddingT15">推荐:</th>
        <td class="paddingT15">
        	<span class="onoff">
          	<label class="cb-enable <?php if ($this->_var['store']['recommended']): ?>selected<?php endif; ?>">是</label>
          	<label class="cb-disable <?php if (! $this->_var['store']['recommended']): ?>selected<?php endif; ?>">否</label>
            <input name="recommended" value="1" type="radio" <?php if ($this->_var['store']['recommended']): ?>checked<?php endif; ?>>
            <input name="recommended" value="0" type="radio" <?php if (! $this->_var['store']['recommended']): ?>checked<?php endif; ?>>
          </span>
        </td>
      </tr>
      <tr>
        <th class="paddingT15">排序:</th>
        <td class="paddingT15 wordSpacing5"><input class="sort_order" name="sort_order" type="text" id="sort_order" value="<?php echo $this->_var['store']['sort_order']; ?>" /></td>
      </tr>
      <?php if ($this->_var['store']['store_id']): ?>
      <tr>
        <th class="paddingT15">上传的图片:</th>
        <td class="paddingT15 wordSpacing5">
          <?php if ($this->_var['store']['image_1']): ?><a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['image_1']; ?>" target="_blank">查看</a><?php endif; ?>
          <?php if ($this->_var['store']['image_2']): ?><a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['image_2']; ?>" target="_blank">查看</a><?php endif; ?>
          <?php if ($this->_var['store']['image_3']): ?><a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['store']['image_3']; ?>" target="_blank">查看</a><?php endif; ?>        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn" type="reset" name="Reset" value="重置" /></td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>