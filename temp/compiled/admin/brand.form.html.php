<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#brand_form').validate({
        errorPlacement: function(error, element){
            $(element).next('.field_notice').hide();
            $(element).after(error);
        },
        success       : function(label){
            label.addClass('right').text('OK!');
        },
        onkeyup    : false,
        rules : {
            brand_name : {
                required : true,
                remote   : {                //唯一
                url :'index.php?app=brand&act=check_brand',
                type:'get',
                data:{
                    brand_name : function(){
                        return $('#brand_name').val();
                        },
                    id  : '<?php echo $this->_var['brand']['brand_id']; ?>'
                    }
                }
            },
            logo : {
                //accept  : 'gif|png|jpe?g'
            },
            sort_order : {
                number   : true
            }
        },
        messages : {
            brand_name : {
                required : '品牌名称不能为空',
                remote   : '该品牌名称已经存在了，请您换一个'
            },
            logo : {
                //accept : '支持格式gif,jpg,jpeg,png'
            },
            sort_order  : {
                number   : '排序仅可以为数字'
            }
        }
    });
});
</script>
<div id="rightTop">
    <p>商品品牌</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=brand">管理</a></li>
        <li><a class="btn1" href="index.php?app=brand&act=apply">待审核</a></li>
        <li><span><?php if ($this->_var['brand']['brand_id']): ?>编辑<?php else: ?>新增<?php endif; ?></span></li>
    </ul>
</div>

<div class="info">
    <form method="post" enctype="multipart/form-data" id="brand_form">
        <table class="infoTable">
            <tr>
                <th class="paddingT15">
                    品牌名称:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" id="brand_name" type="text" name="brand_name" value="<?php echo htmlspecialchars($this->_var['brand']['brand_name']); ?>" /> <label class="field_notice">品牌名称</label>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    类别:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="infoTableInput2" id="tag" type="text" name="tag" value="<?php echo htmlspecialchars($this->_var['brand']['tag']); ?>" /> <label class="field_notice">类别</label>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    图片标识:</th>
                <td class="paddingT15 wordSpacing5">
                    <div class="input-file-show">
                        <span class="show"><a href="javascript:;" class="show_image"><i class="fa fa-image"></i></a></span>
                        <span class="type-file-box">
                            <input type="text" name="textfield" class="type-file-text" />
                            <input type="button" name="button" value="选择上传..." class="type-file-button" />
                            <input class="type-file-file" name="logo" type="file" size="30" hidefocus="true">
                            <label class="field_notice">支持格式gif,jpg,jpeg,png</label>
                        </span>
                        <?php if ($this->_var['brand']['brand_logo']): ?>
                        <div class="show_img"><img src="<?php echo $this->_var['brand']['brand_logo']; ?>" /></div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    是否推荐:</th>
                <td class="paddingT15">
                	<span class="onoff">
                    <label class="cb-enable <?php if ($this->_var['brand']['recommended']): ?>selected<?php endif; ?>">是</label>
                    <label class="cb-disable <?php if (! $this->_var['brand']['recommended']): ?>selected<?php endif; ?>">否</label>
                    <input name="recommended" value="1" type="radio" <?php if ($this->_var['brand']['recommended']): ?>checked<?php endif; ?>>
                    <input name="recommended" value="0" type="radio" <?php if (! $this->_var['brand']['recommended']): ?>checked<?php endif; ?>>
                  </span>
                  <span class="grey notice"></span>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    排序:</th>
                <td class="paddingT15 wordSpacing5">
                    <input class="sort_order" id="sort_order" type="text" name="sort_order" value="<?php echo $this->_var['brand']['sort_order']; ?>" />
                    <label class="field_notice">更新排序</label>
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
