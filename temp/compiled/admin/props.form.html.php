<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p>商品属性</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=props">属性列表</a></li>
        <?php if ($_GET['act'] == 'add'): ?>
        <li><span>添加属性</span>
        <?php else: ?>
        <li><a class="btn1" href="index.php?app=props&amp;act=add">添加属性</a></li>
        <li><span>编辑属性</span></li>
        <?php endif; ?>
        <li><a class="btn1" href="index.php?app=gcategory">分配属性</a></li>     
    </ul>
</div>
<style>
.prop_input{border:1px #ddd solid; height:22px; line-height:22px;color:#3e3e3e;}
</style>
<div class="info">
    <form method="post">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    属性名:</th>
                <td class="paddingT15 wordSpacing5">
                    <input name="name" value="<?php echo $this->_var['props']['name']; ?>" class="prop_input" type="text" />
                </td>
            </tr>
            <?php if ($_GET['act'] == 'add'): ?>
            <tr>
                <th class="paddingT15">
                    属性值:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="prop_input" style="width:300px;" type="text" name="prop_value" value="<?php echo htmlspecialchars($this->_var['props']['prop_value']); ?>" />
                    <span class="grey notice">多个属性值请用半角逗号（,）隔开，如果是颜色属性，请不要再这里填写颜色值，这里只能填写属性值</span>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="paddingT15">属性显示类型:</th>
                <td class="paddingT15 wordSpacing5">
					<label><input type="radio" name="prop_type" value="select" <?php if ($this->_var['props']['prop_type'] == 'select'): ?> checked="checked"<?php endif; ?>/> 下拉框</label>
                    <label><input type="radio" name="prop_type" value="checkbox" <?php if ($this->_var['props']['prop_type'] == 'checkbox'): ?> checked="checked"<?php endif; ?>/> 复选框</label>
                    <label><input type="radio" name="prop_type" value="radio" <?php if ($this->_var['props']['prop_type'] == 'radio'): ?> checked="checked"<?php endif; ?>/> 单选框</label>
                    <span class="grey notice">复选框只针对发布商品的时候，一个属性可以选择多个值（复选框的形式），而在前台搜索页搜索的时候，还是只能单选。</span>
                </td>
            </tr>
            
            <tr>
                <th class="paddingT15">是否颜色属性:</th>
                <td class="paddingT15">
                  <span class="onoff">
                    <label class="cb-enable <?php if ($this->_var['props']['is_color_prop']): ?>selected<?php endif; ?>">是</label>
                    <label class="cb-disable <?php if (! $this->_var['props']['is_color_prop']): ?>selected<?php endif; ?>">否</label>
                    <input name="is_color_prop" value="1" type="radio" <?php if ($this->_var['props']['is_color_prop']): ?>checked<?php endif; ?>>
                    <input name="is_color_prop" value="0" type="radio" <?php if (! $this->_var['props']['is_color_prop']): ?>checked<?php endif; ?>>
                  </span>
                  <span class="grey notice">如果选择”是“，请在添加属性值的时候，添加颜色值</span>      
              </td>
            </tr>
            
            <tr>
                <th class="paddingT15">
                    排序:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="sort_order prop_input" id="sort_order" type="text" name="sort_order" value="<?php echo $this->_var['props']['sort_order']; ?>" />
                </td>
            </tr>
            <tr>
              <th class="paddingT15">启用:</th>
              <td class="paddingT15">
                  <span class="onoff">
                    <label class="cb-enable <?php if ($this->_var['props']['status']): ?>selected<?php endif; ?>">是</label>
                    <label class="cb-disable <?php if (! $this->_var['props']['status']): ?>selected<?php endif; ?>">否</label>
                    <input name="status" value="1" type="radio" <?php if ($this->_var['props']['status']): ?>checked<?php endif; ?>>
                    <input name="status" value="0" type="radio" <?php if (! $this->_var['props']['status']): ?>checked<?php endif; ?>>
                  </span>
                  <span class="grey notice"></span>      
              </td>
            </tr>

          <tr>
            <th></th>
            <td class="ptb20">
                <input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
                <input class="formbtn" type="reset" name="reset" value="重置" />            </td>
        </tr>
        </table>
    </form>
</div>
<?php echo $this->fetch('footer.html'); ?>
