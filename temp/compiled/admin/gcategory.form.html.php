<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#gcategory_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('right').text('OK!');
        },
        onfocusout : false,
        onkeyup    : false,
        rules : {
            cate_name : {
                required : true,
                remote   : {                
                url :'index.php?app=gcategory&act=check_gcategory',
                type:'get',
                data:{
                    cate_name : function(){
                        return $('#cate_name').val();
                    },
                    parent_id : function() {
                        return $('#parent_id').val();
                    },
                    id : '<?php echo $this->_var['gcategory']['cate_id']; ?>'
                  }
                }
            },
            sort_order : {
                number   : true
            }
        },
        messages : {
            cate_name : {
                required : '分类名称不能为空',
                remote   : '该分类名称已经存在了，请您换一个'
            },
            sort_order  : {
                number   : '分类排序仅能为数字'
            }
        }
    });
});
</script>
<div id="rightTop">
    <p>商品分类</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=gcategory">管理</a></li>
        <li><span><?php if ($this->_var['gcategory']['cate_id']): ?>编辑<?php else: ?>新增<?php endif; ?></span></li>
    </ul>
</div>
<div class="info">
    <form method="post" enctype="multipart/form-data" id="gcategory_form">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    分类名称:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" id="cate_name" type="text" name="cate_name" value="<?php echo htmlspecialchars($this->_var['gcategory']['cate_name']); ?>" /> <label class="field_notice">分类名称</label>               </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    <label for="parent_id">上级分类:</label></th>
                <td class="paddingT15 wordSpacing5">
                    <select id="parent_id" name="parent_id"><option value="0">请选择...</option><?php echo $this->html_options(array('options'=>$this->_var['parents'],'selected'=>$this->_var['gcategory']['parent_id'])); ?></select> <label class="field_notice">上级分类</label>               </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    排序:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="sort_order" id="sort_order" type="text" name="sort_order" value="<?php echo $this->_var['gcategory']['sort_order']; ?>" />  <label class="field_notice">更新排序</label>              </td>
            </tr>
            <tr>
              <th class="paddingT15">显示:</th>
              <td class="paddingT15 wordSpacing5"><p>
                <label>
                  <input type="radio" name="if_show" value="1" <?php if ($this->_var['gcategory']['if_show']): ?>checked="checked"<?php endif; ?> />
                  是</label>
                <label>
                  <input type="radio" name="if_show" value="0" <?php if (! $this->_var['gcategory']['if_show']): ?>checked="checked"<?php endif; ?> />
                  否</label> <label class="field_notice">新增的分类名称是否显示</label>
              </p></td>
            </tr>
			<tr>
				<th class="paddingT15">图标:</th>
				<td class="paddingT15 wordSpacing5">
				  <div class="input-file-show">
						<span class="show"><a href="javascript:;" class="show_image"><i class="fa fa-image"></i></a></span>
						<span class="type-file-box">
							<input type="text" name="textfield" class="type-file-text" />
							<input type="button" name="button" value="选择上传..." class="type-file-button" />
							<input class="type-file-file" name="category_image" id="category_image" type="file" size="30" hidefocus="true" value="<?php echo $this->_var['gcategory']['category_image']; ?>">
						</span>
						<?php if ($this->_var['gcategory']['category_image']): ?>
						<div class="show_img"><img src="../<?php echo $this->_var['gcategory']['category_image']; ?>" alt="" width="100" height="100" /></div>
						<?php endif; ?>
					</div>
				</td>
      		</tr>
            <?php if ($this->_var['gcategory']['parent_id'] == 0): ?>
            <tr>
				<th class="paddingT15"> <label for="eval_tips">评论标签:</label></th>
				<td class="paddingT15 wordSpacing5"><textarea name="eval_tips" id="eval_tips"><?php echo htmlspecialchars($this->_var['gcategory']['eval_tips']); ?></textarea><label class="field_notice">多个用,号隔开, 列如: 外观美观,性能强大,照相清晰,视频资源丰富,画面清晰,性价比高</label></td>
			  </tr>
			  <tr>
				<th class="paddingT15"> <label for="eval_template">评论内容模板:</label></th>
				<td class="paddingT15 wordSpacing5"><textarea name="eval_templates" id="eval_templates"><?php echo htmlspecialchars($this->_var['gcategory']['eval_templates']); ?></textarea><label class="field_notice">多个用,号隔开, 列如: 外观美观,超喜欢,性能强大很赞,照相清晰非常好,视频资源丰富，大爱,画面清晰，非常流畅,性价比高</label></textarea></td>
			  </tr>
			  <?php endif; ?>

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
