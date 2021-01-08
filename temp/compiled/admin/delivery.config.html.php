<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>运费模板</p>
    <ul class="subnav">
        <li><span>物流配置</span></li>
    </ul>
</div>
<div class="info">
    <form method="post" id="coupon_form">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                   物流名称</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" type="text" name="delivery[express]" value="<?php echo $this->_var['delivery']['express']; ?>" />
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                     物流名称</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" type="text" name="delivery[post]" value="<?php echo $this->_var['delivery']['post']; ?>" />
                </td>
            </tr>
             <tr>
                <th class="paddingT15">
                     物流名称</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" type="text" name="delivery[ems]" value="<?php echo $this->_var['delivery']['ems']; ?>" />
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