<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>网站设置</p>
  <ul class="subnav">
    <li><a class="btn1" href="index.php?app=setting&amp;act=base_setting">系统设置</a></li>
    <li><span>基本信息</span></li>
    <li><a class="btn1" href="index.php?app=setting&amp;act=email_setting">Email</a></li>
    <li><a class="btn1" href="index.php?app=setting&amp;act=captcha_setting">验证码</a></li>
    <li><a class="btn1" href="index.php?app=setting&amp;act=store_setting">开店设置</a></li>
    <li><a class="btn1" href="index.php?app=setting&amp;act=credit_setting">信用评价</a></li>
    <li><a class="btn1" href="index.php?app=setting&amp;act=subdomain_setting">二级域名</a></li>
  </ul>
</div>
<div class="info">
  <form method="post" enctype="multipart/form-data">
    <table class="infoTable">
      <tr>
        <th class="paddingT15"> <label for="site_name">网站名称:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="site_name" type="text" name="site_name" value="<?php echo $this->_var['setting']['site_name']; ?>" class="infoTableInput"/>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="site_title">网站标题:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="site_title" type="text" name="site_title" value="<?php echo $this->_var['setting']['site_title']; ?>" class="infoTableInput"/>        </td>
      </tr>
      <tr>
        <th class="paddingT15" valign="top"> <label for="site_description">网站描述:</label></th>
        <td class="paddingT15 wordSpacing5"><textarea name="site_description" id="site_description"><?php echo $this->_var['setting']['site_description']; ?></textarea>        </td>
      </tr>
      <tr>
        <th class="paddingT15">网站关键字:</th>
        <td class="paddingT15 wordSpacing5"><input id="site_keywords" type="text" name="site_keywords" value="<?php echo $this->_var['setting']['site_keywords']; ?>" class="infoTableInput"/></td>
      </tr>
<!--      <tr>
        <th class="paddingT15"> <label for="copyright">版权信息:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="copyright" type="text" name="copyright" value="<?php echo $this->_var['setting']['copyright']; ?>" class="infoTableInput"/>        </td>
      </tr> -->
      <tr>
        <th class="paddingT15"> <label for="site_logo">网站Logo:</label></th>
        <td class="paddingT15 wordSpacing5">
          <div class="input-file-show">
                <span class="show"><a href="javascript:;" class="show_image"><i class="fa fa-image"></i></a></span>
                <span class="type-file-box">
                    <input type="text" name="textfield" class="type-file-text" />
                    <input type="button" name="button" value="选择上传..." class="type-file-button" />
                    <input class="type-file-file" name="site_logo" type="file" size="30" hidefocus="true">
                    <label class="field_notice">默认网站LOGO,通用头部显示，最佳显示尺寸为240*60像素</label>
                </span>
                <?php if ($this->_var['setting']['site_logo']): ?>
              	<div class="show_img"><img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['setting']['site_logo']; ?>?<?php echo $this->_var['random_number']; ?>" /></div>
              	<?php endif; ?>
            </div>
        </td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="icp_number">ICP证书号:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="icp_number" type="text" name="icp_number" value="<?php echo $this->_var['setting']['icp_number']; ?>" class="infoTableInput"/>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="site_phone_tel">电话:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="site_phone_tel" type="text" name="site_phone_tel" value="<?php echo $this->_var['setting']['site_phone_tel']; ?>" class="infoTableInput"/>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="site_email">Email:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="site_email" type="text" name="site_email" value="<?php echo $this->_var['setting']['site_email']; ?>" class="infoTableInput"/>        </td>
      </tr>
      <tr>
        <th class="paddingT15">网站状态:</th>
        <td class="paddingT15">
          <span class="onoff">
          	<label class="cb-enable <?php if ($this->_var['setting']['site_status']): ?>selected<?php endif; ?>">开启</label>
          	<label class="cb-disable <?php if (! $this->_var['setting']['site_status']): ?>selected<?php endif; ?>">关闭</label>
            <input name="site_status" value="1" type="radio" <?php if ($this->_var['setting']['site_status']): ?>checked<?php endif; ?>>
            <input name="site_status" value="0" type="radio" <?php if (! $this->_var['setting']['site_status']): ?>checked<?php endif; ?>>
          </span>
          <span class="grey notice"></span>      
          </td>
      </tr>
      <tr>
        <th class="paddingT15" valign="top"> <label for="closed_reason">关闭原因:</label></th>
        <td class="paddingT15 wordSpacing5"><textarea name="closed_reason" id="closed_reason"><?php echo $this->_var['setting']['closed_reason']; ?></textarea>        </td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="hot_search">热门搜索:</label></th>
        <td class="paddingT15 wordSpacing5"><input id="hot_search" type="text" name="hot_search" value="<?php echo $this->_var['setting']['hot_search']; ?>" class="infoTableInput"/>
        <label class="field_notice">多个关键词之间请用逗号分隔</label></td>
      </tr>
	  <tr>
        <th class="paddingT15"> <label for="baidukey">百度地图KEY:</label></th>
        <td class="paddingT15 wordSpacing5">
		<label>浏览器端: </label><input id="baidukey" type="text" name="baidukey[browser]" value="<?php echo $this->_var['setting']['baidukey']['browser']; ?>" class="infoTableInput"/>
		<label>服务端: </label><input id="baidukey" type="text" name="baidukey[server]" value="<?php echo $this->_var['setting']['baidukey']['server']; ?>" class="infoTableInput"/>
        <label class="field_notice"><a href="http://lbsyun.baidu.com/apiconsole/key/create" target="_blank">立即申请KEY</a></label></td>
      </tr>
      <tr>
        <th class="paddingT15"> <label for="weixinkey">微信公众号:</label></th>
        <td class="paddingT15 wordSpacing5">
		<label>AppID: </label><input id="weixinkey" type="text" name="weixinkey[AppID]" value="<?php echo $this->_var['setting']['weixinkey']['AppID']; ?>" class="infoTableInput"/>
		<label>AppSecret: </label><input id="weixinkey" type="text" name="weixinkey[AppSecret]" value="<?php echo $this->_var['setting']['weixinkey']['AppSecret']; ?>" class="infoTableInput"/>
        <label class="field_notice">去微信公众平台（https://mp.weixin.qq.com）申请服务号</label></td>
      </tr>
	  <tr>
        <th class="paddingT15"> <label for="weixinkminey">微信小程序:</label></th>
        <td class="paddingT15 wordSpacing5">
		<label>AppID: </label><input id="weixinminkey" type="text" name="weixinminkey[AppID]" value="<?php echo $this->_var['setting']['weixinminkey']['AppID']; ?>" class="infoTableInput"/>
		<label>AppSecret: </label><input id="weixinminkey" type="text" name="weixinminkey[AppSecret]" value="<?php echo $this->_var['setting']['weixinminkey']['AppSecret']; ?>" class="infoTableInput"/>
		</td>
      </tr>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn" type="reset" name="Submit2" value="重置" />        </td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>