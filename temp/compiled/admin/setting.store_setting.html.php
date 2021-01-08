<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>网站设置</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=setting&amp;act=base_setting">系统设置</a></li>
        <li><a class="btn1" href="index.php?app=setting&amp;act=base_information">基本信息</a></li>
        <li><a class="btn1" href="index.php?app=setting&amp;act=email_setting">Email</a></li>
        <li><a class="btn1" href="index.php?app=setting&amp;act=captcha_setting">验证码</a></li>
        <li><span>开店设置</span></li>
        <li><a class="btn1" href="index.php?app=setting&amp;act=credit_setting">信用评价</a></li>
        <li><a class="btn1" href="index.php?app=setting&amp;act=subdomain_setting">二级域名</a></li>
        </ul>
</div>

<div class="info">
    <form method="post" enctype="multipart/form-data">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    允许申请开店:</th>
                <td class="paddingT15">
                    <span class="onoff">
                    <label class="cb-enable <?php if ($this->_var['setting']['store_allow']): ?>selected<?php endif; ?>">启用</label>
                    <label class="cb-disable <?php if (! $this->_var['setting']['store_allow']): ?>selected<?php endif; ?>">禁用</label>
                    <input name="store_allow" value="1" type="radio" <?php if ($this->_var['setting']['store_allow']): ?>checked<?php endif; ?>>
                    <input name="store_allow" value="0" type="radio" <?php if (! $this->_var['setting']['store_allow']): ?>checked<?php endif; ?>>
                  </span>
                  <span class="grey notice"></span>   
                </td>
            </tr>
            <tr>
            <th></th>
            <td class="ptb20">
                <input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
                <input class="formbtn" type="reset" name="Submit2" value="重置" />
            </td>
        </tr>
        </table>
    </form>
</div>
<?php echo $this->fetch('footer.html'); ?>
