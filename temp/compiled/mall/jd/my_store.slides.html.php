<?php echo $this->fetch('member.header.html'); ?> 
<script>
$(function(){
	$('.J_DropSlides').click(function(){
		$.ajax({
        	type:"GET",
        	url:"index.php",
        	data:'app=my_store&act=drop_slides&file_id='+$(this).attr('file_id'),
        	dataType:"json",
        	success:function(data){
            	if(data.done){
            		alert(data.msg);
					window.location.reload();
            	}
            	else{
                	alert(data.msg);
            	}
        	},
        	error: function(){alert(lang.error);}
    	});
	});
});
</script>
<style>
.information .info table{width :auto;}
</style>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public">
          <div class="information">
            <form method="post" enctype="multipart/form-data" id="store_slides_form">
              <div class="setup info shop">
                <table style="width: 100%;">
                  <tr>
                    <th style="width:150px;">幻灯片1：(1920px * 510px)</th>
                    <td style="width:250px;"><p class="td_block">
                        <input type="file" class="text width_normal" name="store_slides_url_1" />
                      </p></td>
                    <td><?php if ($this->_var['slides']['store_slides_url_1']): ?><img align="absMiddle" src="<?php echo $this->_var['slides']['store_slides_url_1']; ?>"  height="25"/> <a href="javascript:;" class="J_DropSlides" file_id='1'>删除</a><?php endif; ?></td>
                  </tr>
                  <tr>
                    <th class="width2">幻灯片链接1：</th>
                    <td><p class="td_block">
                        <input type="text" class="text width_normal" name="store_slides_link_1" value="<?php echo $this->_var['slides']['store_slides_link_1']; ?>" />
                      </p></td>
                  </tr>
                  <tr>
                    <th style="width:150px;">幻灯片2：(1920px * 510px)</th>
                    <td><p class="td_block">
                        <input type="file" class="text width_normal" name="store_slides_url_2" />
                      </p></td>
                    <td><?php if ($this->_var['slides']['store_slides_url_2']): ?><img align="absMiddle" src="<?php echo $this->_var['slides']['store_slides_url_2']; ?>"  height="25"/> <a href="javascript:;" class="J_DropSlides" file_id='2'>删除</a><?php endif; ?></td>
                  </tr>
                  <tr>
                    <th class="width2">幻灯片链接2：</th>
                    <td><p class="td_block">
                        <input type="text" class="text width_normal" name="store_slides_link_2" value="<?php echo $this->_var['slides']['store_slides_link_2']; ?>"/>
                      </p></td>
                  </tr>
                  <tr>
                    <th style="width:150px;">幻灯片3：(1920px * 510px)</th>
                    <td><p class="td_block">
                        <input type="file" class="text width_normal" name="store_slides_url_3" />
                      </p></td>
                    <td><?php if ($this->_var['slides']['store_slides_url_3']): ?><img align="absMiddle" src="<?php echo $this->_var['slides']['store_slides_url_3']; ?>"  height="25"/> <a href="javascript:;" class="J_DropSlides" file_id='3'>删除</a><?php endif; ?></td>
                  </tr>
                  <tr>
                    <th class="width2">幻灯片链接3：</th>
                    <td><p class="td_block">
                        <input type="text" class="text width_normal" name="store_slides_link_3" value="<?php echo $this->_var['slides']['store_slides_link_3']; ?>"/>
                      </p></td>
                  </tr>
                  <tr>
                    <th></th>
                    <td><input type="submit" class="btn" value="提交" /></td>
                    <td></td>
                  </tr>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?>