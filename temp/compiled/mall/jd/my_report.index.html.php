<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
$(function(){
	$('.J_del').click(function(){
		if(confirm('您确定要删除它吗？')){
			var id = $(this).attr('data-id');
			var url = SITE_URL + '/index.php?app=my_report&act=ajaxDrop';
			var row = $(this).parents('.item');
			$.getJSON(url,{'id':id},function(data){
				if(data.done){
					row.remove();
				}else{
					alert(data.msg);
				}
			});
		}
	})
})
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_select table">
          <table>
					<tr class="line gray">
						<th width="280">举报商品</th>
						<th width="80">被举报人</th>
						<th>举报内容</th>
						<th width="280">上传证明</th>
                        <th width="50">状态</th>
					</tr>
					<tr class="sep-row">
						<td colspan="5"></td>
					</tr>
					<?php if ($this->_var['reports']): ?>
					<tr class="operations">
						<th colspan="5"> <p class="position1 clearfix">
								<input type="checkbox" id="all" class="checkall"/>
								<label for="all">全选</label>
                                <a href="javascript:;" class="delete" ectype="batchbutton" uri="index.php?app=my_report&act=drop" name="id" presubmit="confirm('您确定要删除它吗？')">删除</a>
							</p>
							<p class="position2 clearfix"> <?php echo $this->fetch('member.page.top.html'); ?> </p>
						</th>
					</tr>
					
					<?php $_from = $this->_var['reports']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['fe_val'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_val']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['fe_val']['iteration']++;
?>
                    <tbody class="item">
					<tr class="sep-row">
						<td colspan="5"></td>
					</tr>
					<tr class="line-hd">
						<th colspan="5"> <p> <input type="checkbox" value="<?php echo $this->_var['val']['report_id']; ?>" class="checkitem" <?php if ($this->_var['val']['status']): ?> disabled="disabled" <?php endif; ?>/>
								<label>添加时间：</label>
								<?php echo local_date("Y-m-d H:i:s",$this->_var['val']['add_time']); ?> <a href="javascript:;" class="J_del" style="margin-left:15px;" data-id="<?php echo $this->_var['val']['report_id']; ?>">删除</a> 
							</p>
						</th>
					</tr>
					<tr class="line">
						<td valign="top" class="first clearfix"><div class="pic-info float-left"> <a href="<?php echo url('app=goods&id=' . $this->_var['val']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['val']['goods_image']; ?>" width="50" height="50" /></a> </div>
							<div class="txt-info float-left" style="width:180px;">
								<div class="txt"> <a href="<?php echo url('app=goods&id=' . $this->_var['val']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['val']['goods_name']; ?></a> </div> 
							</div>
                        </td>
						<td class="align2"><a href="<?php echo url('app=store&id=' . $this->_var['val']['store_id']. ''); ?>" target="_blank"><?php echo $this->_var['val']['store_name']; ?></a></td>
						<td class="align2"><?php echo htmlspecialchars($this->_var['val']['content']); ?></td>
						<td>
                        	<?php $_from = $this->_var['val']['images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'image');if (count($_from)):
    foreach ($_from AS $this->_var['image']):
?>
                        	<a href="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['image']; ?>" target="_blank"><img src="<?php echo $this->_var['image']; ?>" width="50" height="50" /></a>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        </td>
                        <td class="align2"><?php if ($this->_var['val']['status'] == 1): ?>已处理<?php else: ?>待审核<?php endif; ?></td>
					</tr>
                    </tbody>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
					
					<tr class="sep-row">
						<td colspan="5"></td>
					</tr>
					<tr class="operations">
						<th colspan="5"> <p class="position1 clearfix">
								<input type="checkbox" id="all2" class="checkall"/>
								<label for="all2">全选</label>
                                <a href="javascript:;" class="delete" ectype="batchbutton" uri="index.php?app=my_report&act=drop" name="id" presubmit="confirm('您确定要删除它吗？')">删除</a>
							</p>
							<p class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </p>
						</th>
					</tr>
					
					<?php else: ?>
					<tr class="sep-row">
						<td colspan="5"><div class="notice-word">
								<p>没有符合条件的记录</p>
							</div></td>
					</tr>
					<?php endif; ?>
					
				</table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>
