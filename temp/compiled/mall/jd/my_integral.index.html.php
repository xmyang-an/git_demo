<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript">
$(function(){
	$('.J_SignIntegral').click(function(){
		var url = SITE_URL + '/index.php?app=my_integral&act=sign';
		$.getJSON(url,{},function(data){
			layer.open({content:data.msg, end: function(){
				window.location.reload();
			}});
		});
	});
});
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_index table1">
          <div class="user-integral-info mb10 clearfix">
            <div class="valid user-integral"> <b class="des">可用的积分</b> <b class="piont"><?php echo ($this->_var['integral']['amount'] == '') ? '0' : $this->_var['integral']['amount']; ?></b> </div>
            <div class="invalid user-integral"> <b class="des">冻结的积分</b> <b class="piont decr"><?php echo ($this->_var['integral']['frozen_integral'] == '') ? '0' : $this->_var['integral']['frozen_integral']; ?></b> </div>
            <div style="border:0px;" class="invalid user-integral"> 
            	<a class="desl J_SignIntegral">签到领积分</a>
            </div>
          </div>
          <?php if ($this->_var['integral_log']): ?>
          <div class="user-integral-detail mb10">
            <table>
              <tr>
                <th>来源/用途</th>
                <th>积分变化</th>
                <th>余额</th>
                <th width="80">状态</th>
                <th>日期</th>
                <th width="300">备注</th>
              </tr>
              <?php $_from = $this->_var['integral_log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'log');$this->_foreach['log'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['log']['total'] > 0):
    foreach ($_from AS $this->_var['log']):
        $this->_foreach['log']['iteration']++;
?>
              <tr>
                <td><?php echo $this->_var['log']['name']; ?></td>
                <td class="change"><?php if ($this->_var['log']['changes'] > 0): ?> 
                  <span class="plus">+<?php echo $this->_var['log']['changes']; ?></span> 
                  <?php else: ?> 
                  <span class="minus"><?php echo $this->_var['log']['changes']; ?></span> 
                  <?php endif; ?></td>
                <td class="balance"><span><?php echo $this->_var['log']['balance']; ?></span></td>
                <td><?php echo $this->_var['log']['state']; ?></td>
                <td><?php echo local_date("Y年m月d日 H:i:s",$this->_var['log']['add_time']); ?></td>
                <td width="300"><div style="padding-left:5px; text-align:left"> <?php echo $this->_var['log']['flag']; ?> </div></td>
              </tr>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </table>
          </div>
          <?php else: ?>
          <div class="notice-word">
            <p>没有符合条件的记录</p>
          </div>
          <?php endif; ?> 
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 