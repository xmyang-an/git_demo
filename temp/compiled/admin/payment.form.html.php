<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
<!--//<![CDATA[
$(function(){
    <?php echo $this->_var['payment']['onconfig']; ?>
});
//]]>-->
</script>
<div id="rightTop">
    <p><strong>支付方式管理</strong></p>
    <ul class="subnav">
      <li><a href="<?php echo url('app=payment'); ?>">支付方式管理</a></li>
  	　 <li><span>配置</span></li>
  </ul>
</div>
<div class="info">
    <form method="post" enctype="multipart/form-data" id="payment_form">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    支付方式名称:</th>
                <td class="paddingT15 wordSpacing5">
                    <p class="new_style"><?php echo htmlspecialchars($this->_var['payment']['name']); ?></p>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    简介</th>
                <td class="paddingT15 wordSpacing5">
                    <textarea class="text" name="payment_desc"><?php echo htmlspecialchars($this->_var['payment']['payment_desc']); ?></textarea>
                    <label class="field_notice">该信息将在用户下单时被看到</label>
                </td>
            </tr>
            <?php $_from = $this->_var['payment']['config']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('conf', 'info');if (count($_from)):
    foreach ($_from AS $this->_var['conf'] => $this->_var['info']):
?>
            <tr>
                <th class="paddingT15">
                	<?php echo $this->_var['info']['text']; ?>:</th>
                <td class="paddingT15 wordSpacing5">
                	<?php if ($this->_var['info']['type'] == 'text'): ?> 
                    	<input type="text" name="config[<?php echo $this->_var['conf']; ?>]" id="ctrl_<?php echo $this->_var['conf']; ?>" value="<?php echo $this->_var['config'][$this->_var['conf']]; ?>" size="<?php echo $this->_var['info']['size']; ?>" onfocus="<?php echo $this->_var['info']['onfocus']; ?>" onchange="<?php echo $this->_var['info']['onchange']; ?>" onblur="<?php echo $this->_var['info']['onblur']; ?>" class="text"/>
                	<?php elseif ($this->_var['info']['type'] == 'select'): ?>
               			<select name="config[<?php echo $this->_var['conf']; ?>]" id="ctrl_<?php echo $this->_var['conf']; ?>" onchange="<?php echo $this->_var['info']['onchange']; ?>" class="width8 padding4">
                       		<?php echo $this->html_options(array('options'=>$this->_var['info']['items'],'selected'=>$this->_var['config'][$this->_var['conf']])); ?>
                 		</select>
                 	<?php elseif ($this->_var['info']['type'] == 'textarea'): ?>
                 		<textarea cols="<?php echo $this->_var['info']['cols']; ?>" rows="<?php echo $this->_var['info']['rows']; ?>" name="config[<?php echo $this->_var['conf']; ?>]" id="ctrl_<?php echo $this->_var['conf']; ?>" onfocus="<?php echo $this->_var['info']['onfocus']; ?>" onchange="<?php echo $this->_var['info']['onchange']; ?>" onblur="<?php echo $this->_var['info']['onblur']; ?>" class="text" ><?php echo $this->_var['config'][$this->_var['conf']]; ?></textarea>
                 	<?php elseif ($this->_var['info']['type'] == 'radio'): ?>
                       <?php echo $this->html_radios(array('options'=>$this->_var['info']['items'],'checked'=>$this->_var['config'][$this->_var['conf']],'name'=>$this->_var['info']['name'])); ?>
                 	<?php elseif ($this->_var['info']['type'] == 'checkbox'): ?>
                    	<?php echo $this->html_checkbox(array('options'=>$this->_var['info']['items'],'checked'=>$this->_var['config'][$this->_var['conf']],'name'=>$this->_var['info']['name'])); ?>
                 	<?php endif; ?>
                 	<label class="field_notice"><?php echo $this->_var['info']['desc']; ?></label>
                 </td>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <tr>
                <th class="paddingT15">排序:</th>
                <td class="paddingT15 wordSpacing5">
                	<input type="text" class="text width2" value="<?php echo $this->_var['payment']['sort_order']; ?>" name="sort_order"/>
                </td>
            </tr>
            <?php if ($this->_var['payment']['is_online']): ?>
             <tr>
                <th class="paddingT15">区别码:</th>
                <td class="paddingT15 wordSpacing5">
                	<input type="text" name="config[pcode]" value="<?php echo $this->_var['config']['pcode']; ?>" size="3" class="text" />
                    <label class="field_notice">正常情况下可留空，仅当支付时频繁出错时使用</label>
                </td>
            </tr>
            <?php endif; ?>
            
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
