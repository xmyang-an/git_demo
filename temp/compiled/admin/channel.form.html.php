<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
//<!CDATA[
$(function(){
    // multi-select mall_gcategory
    //$('#gcategory').length>0 && gcategoryInit("gcategory");
	
	$('input[name="style"]').click(function(){
		<?php if ($this->_var['channel']['style']): ?>
		if(<?php echo $this->_var['channel']['style']; ?> != $(this).val() && confirm('切换风格后，原频道的页面文件及配置文件将删除，你确定么？')){
			$(this).attr('checked',true);
		} else {
			$('#style<?php echo $this->_var['channel']['style']; ?>').attr('checked',true);
		}
		<?php endif; ?>
	});
	
});
//]]>
</script>
<style>
.subnav li span{*top:1px;}
a{text-decoration:none;color:#234F8D}
</style>

<div id="rightTop">
    <p>模板编辑</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=template">页面列表</a></li>
        <?php if ($_GET['act'] == 'add'): ?>
        <li><span>添加新频道</span></li>
        <?php else: ?>
        <li><a class="btn1" href="index.php?app=channel&amp;act=add">添加新频道</a></li>
        <li><span>编辑频道</span></li>
        <?php endif; ?>
    </ul>
</div>
<div class="info">
    <form method="post" id="channel_form">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    频道页面名称:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" type="text" name="title" value="<?php echo htmlspecialchars($this->_var['channel']['title']); ?>" /> <label class="field_notice">6个汉字以内</label>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    <label for="parent_id">对应的商品分类:</label>
                </th>
                <td class="paddingT15 wordSpacing5">
          			<!--<div id="gcategory" style="display:inline;">
                    	<input type="hidden" name="cate_id" value="0" class="mls_id" />-->
            			<select class="querySelect" name="cate_id">
             				<option>请选择...</option>
              				<?php echo $this->html_options(array('options'=>$this->_var['gcategories'],'selected'=>$this->_var['channel']['cate_id'])); ?>
            			</select>
          			<!--</div>-->
                    <label class="field_notice">可以为空，如果设置对应，则在前台点击该分类页时，将会显示该频道页面。</label>
                </td>
            </tr>
            <tr>
              <th class="paddingT15">频道页风格:</th>
              <td class="paddingT15 wordSpacing5 channel_style">
                <input type="radio" id="style1" name="style" value="1" <?php if ($this->_var['channel']['style'] == '1' || $_GET['act'] == 'add'): ?>checked="checked"<?php endif; ?> />
                <label for="style1">风格1</label> [<a href="<?php echo $this->_var['tpl_url']; ?>/screenshot_channel_style1.jpg" target="_blank">预览</a>]
                
                <input type="radio" id="style2" name="style" value="2" <?php if ($this->_var['channel']['style'] == '2'): ?>checked="checked"<?php endif; ?> />
                <label for="style1">风格2</label> [<a href="<?php echo $this->_var['tpl_url']; ?>/screenshot_channel_style2.jpg" target="_blank">预览</a>]           
              </td>
            </tr>
            
            <tr>
              <th class="paddingT15">状态:</th>
              <td class="paddingT15 wordSpacing5">
                  <input type="radio" id="status1" name="status" value="1" <?php if ($this->_var['channel']['status'] == '1' || $_GET['act'] == 'add'): ?>checked="checked"<?php endif; ?> />
                  <label for="status1">启用</label>
                  <input type="radio" id="status0" name="status" value="0" <?php if ($this->_var['channel']['status'] == '0'): ?>checked="checked"<?php endif; ?> />
                  <label for="status0">禁用</label>
                  <label class="field_notice">是否启用</label>
              </td>
            </tr>

          <tr>
            <th></th>
            <td class="ptb20">
                <input class="formbtn" type="submit" value="提交" />
                <input class="formbtn" type="reset" name="reset" value="重置" />
            </td>
        </tr>
        </table>
    </form>
</div>
<?php echo $this->fetch('footer.html'); ?>
