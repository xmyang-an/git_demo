<?php echo $this->fetch('header.html'); ?> 
<script type="text/javascript">
function add_uploadedfile(file_data)
{
    var newImg = '<tr id="' + file_data.file_id + '" class="tatr2" ectype="handle_pic" file_name="'+file_data.file_name+'" file_path="'+file_data.file_path+'" file_id="'+file_data.file_id+'"><input type="hidden" name="file_id[]" value="' + file_data.file_id + '" /><td><img width="40px" height="40px" src="' + SITE_URL + '/' + file_data.file_path + '" /></td><td>' + file_data.file_name + '</td><td><a href="javascript:drop_uploadedfile(' + file_data.file_id + ');">删除</a></td></tr>';
    $('#thumbnails').prepend(newImg);
}

function drop_uploadedfile(file_id)
{
    if(!window.confirm(lang.uploadedfile_drop_confirm)){
        return;
    }
    $.getJSON('index.php?app=gcategory&act=drop_uploadedfile&file_id=' + file_id, function(result){
        if(result.done){
            $('#' + file_id).remove();
        }else{
            alert('drop_error');
        }
    });
}
</script> 
<?php echo $this->_var['build_upload']; ?>
<div id="rightTop">
    <p>商品分类</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=gcategory">管理</a></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=add">新增</a></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=export">导出</a></li>
        <li><a class="btn1" href="index.php?app=gcategory&amp;act=import">导入</a></li>
		<li><span>广告图</span></li>
    </ul>
</div>
<div class="info">
		<table class="infoTable">
			<tr>
				<th class="paddingT15"> 商品分类:</th>
				<td class="paddingT15 wordSpacing5"><?php echo $this->_var['gcategory']['cate_name']; ?></td>
			</tr>
			<tr>
				<th class="paddingT15">图片上传:</th>
				<td class="paddingT15 wordSpacing5">
					<iframe id="divComUploadContainer" src="index.php?app=comupload&act=view_iframe&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>" width="500" height="46" scrolling="no" frameborder="0"> </iframe></td>
			</tr>
			<tr>
				<th>已传图片:</th>
				<td><div class="tdare">
						<table  width="700px" cellspacing="0" class="dataTable">
							<tbody id="thumbnails" class="J_contenteditor">
								<?php $_from = $this->_var['files_belong_gcategory']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'file');if (count($_from)):
    foreach ($_from AS $this->_var['file']):
?>
								<tr class="tatr2" id="<?php echo $this->_var['file']['file_id']; ?>" ectype="handle_pic" file_name="<?php echo htmlspecialchars($this->_var['file']['file_name']); ?>" file_path="<?php echo $this->_var['file']['file_path']; ?>" file_id="<?php echo $this->_var['file']['file_id']; ?>">
									<input type="hidden" name="file_id[]" value="<?php echo $this->_var['file']['file_id']; ?>" />
									<td><img alt="<?php echo $this->_var['file']['file_name']; ?>" src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['file']['file_path']; ?>" width="40px" height="40px" /></td>
									<td><?php echo $this->_var['file']['file_name']; ?></td>
									<td>编辑链接:<span class="editable" ectype="inline_edit" fieldname="link_url" fieldid="<?php echo $this->_var['file']['file_id']; ?>" required="1" title="可编辑"><?php if ($this->_var['file']['link_url']): ?><?php echo $this->_var['file']['link_url']; ?><?php else: ?>http://<?php endif; ?></span></td>
									<td><a href="javascript:drop_uploadedfile(<?php echo $this->_var['file']['file_id']; ?>);">删除</a></td>
								</tr>
								<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							</tbody>
						</table>
					</div></td>
			</tr>
		</table>
</div>
<?php echo $this->fetch('footer.html'); ?> 