<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>小程序管理</p>
    <ul class="subnav">
        <li><span>基本信息</span></li>
        </ul>
</div>

<div class="info">
    <form method="post" enctype="multipart/form-data">
        <table class="infoTable">
           <tr>
                <th class="paddingT15"> <label for="hot_search">热门关键字:</label></th>
                <td class="paddingT15 wordSpacing5"><input id="hot_search" type="text" name="hot_search" value="<?php echo $this->_var['setting']['hot_search']; ?>" class="infoTableInput"/>
                <label class="field_notice">多个关键字之间用空格隔开</label></td>
            </tr>
            <tr>
            <th class="paddingT15">显示模块:</th>
            <td class="paddingT15">
              <span class="onoff">
                <label class="cb-enable <?php if ($this->_var['setting']['hide_module']): ?>selected<?php endif; ?>">开启</label>
                <label class="cb-disable <?php if (! $this->_var['setting']['hide_module']): ?>selected<?php endif; ?>">关闭</label>
                <input name="hide_module" value="1" type="radio" <?php if ($this->_var['setting']['hide_module']): ?>checked<?php endif; ?>>
                <input name="hide_module" value="0" type="radio" <?php if (! $this->_var['setting']['hide_module']): ?>checked<?php endif; ?>>
              </span>
              <span class="grey notice">为了通过审核暂时隐藏某些小程序模块，审核通过后即可显示。</span>      
              </td>
          </tr>
            <tr>
            <th class="paddingT15">开启多地区:</th>
            <td class="paddingT15">
              <span class="onoff">
                <label class="cb-enable <?php if ($this->_var['setting']['enable_city']): ?>selected<?php endif; ?>">开启</label>
                <label class="cb-disable <?php if (! $this->_var['setting']['enable_city']): ?>selected<?php endif; ?>">关闭</label>
                <input name="enable_city" value="1" type="radio" <?php if ($this->_var['setting']['enable_city']): ?>checked<?php endif; ?>>
                <input name="enable_city" value="0" type="radio" <?php if (! $this->_var['setting']['enable_city']): ?>checked<?php endif; ?>>
              </span>
              <span class="grey notice">开启多地区后，将会对商品和店铺进行地区筛选</span>      
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
