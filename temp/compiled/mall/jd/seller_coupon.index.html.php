<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public table">
          <table>
            <div class="eject_btn_two eject_pos1" style="width:120px;left:113px;" title="添加"><b class="ico3" ectype="dialog" dialog_title="添加" dialog_id="coupon_add" dialog_width="480" uri="index.php?app=seller_coupon&amp;act=add">新增优惠券</b></div>
            <?php if ($this->_var['coupons']): ?>
            
            <tr class="gray">
              <th><input id="all" type="checkbox" class="checkall" /></th>
              <th>优惠券名称</th>
              <th>优惠金额</th>
              <th>使用次数</th>
              <th>使用期限</th>
              <th>使用条件</th>
              <th class="align1">操作</th>
            </tr>
            <?php endif; ?> 
            <?php if ($this->_var['coupons']): ?>
            <tbody>
              <?php endif; ?> 
              <?php $_from = $this->_var['coupons']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'coupon');$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from AS $this->_var['coupon']):
        $this->_foreach['v']['iteration']++;
?>
              <tr class="line<?php if (($this->_foreach['v']['iteration'] == $this->_foreach['v']['total'])): ?> last_line<?php endif; ?>">
                <td class="align2"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['coupon']['coupon_id']; ?>" <?php if ($this->_var['coupon']['if_issue'] && $this->_var['coupon']['end_time'] > $this->_var['today']): ?>disabled="disabled"<?php endif; ?> /></td>
                <td class="align2"><?php echo $this->_var['coupon']['coupon_name']; ?></td>
                <td class="align2"><?php if ($this->_var['coupon']['coupon_value']): ?><?php echo $this->_var['coupon']['coupon_value']; ?><?php else: ?>不限制<?php endif; ?></td>
                <td class="align2"><?php if ($this->_var['coupon']['use_times']): ?><?php echo $this->_var['coupon']['use_times']; ?><?php else: ?>不限制<?php endif; ?></td>
                <td class="align2"><?php echo local_date("Y-m-d",$this->_var['coupon']['start_time']); ?> 至 <?php if ($this->_var['coupon']['end_time']): ?><?php echo local_date("Y-m-d",$this->_var['coupon']['end_time']); ?><?php else: ?>不限制<?php endif; ?></td>
                <td class="align2"><?php if ($this->_var['coupon']['min_amount']): ?><?php echo sprintf('一次购物满 %s', $this->_var['coupon']['min_amount']); ?><?php else: ?>不限制<?php endif; ?></td>
                <td class="align1"><?php if ($this->_var['coupon']['if_issue']): ?><a class="view" href="index.php?app=seller_coupon&act=view&id=<?php echo $this->_var['coupon']['coupon_id']; ?>">领取记录</a><?php endif; ?><?php if ($this->_var['coupon']['if_issue'] && $this->_var['coupon']['end_time'] > $this->_var['today']): ?><a href="javascript:void(0);" class="export" uri="index.php?app=seller_coupon&act=export&id=<?php echo $this->_var['coupon']['coupon_id']; ?>" dialog_id="coupon_export" dialog_title="导出" dialog_width="320" ectype="dialog">导出</a> <a href="javascript:void(0);" class="extend" uri="index.php?app=seller_coupon&act=extend&id=<?php echo $this->_var['coupon']['coupon_id']; ?>" dialog_id="coupon_extend" dialog_title="发放" dialog_width="320" ectype="dialog">发放</a><?php elseif ($this->_var['coupon']['if_issue'] && $this->_var['coupon']['end_time'] <= $this->_var['today']): ?><a class="delete" href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=seller_coupon&act=drop&id=<?php echo $this->_var['coupon']['coupon_id']; ?>');">删除</a><?php else: ?><a class="start" href="javascript:drop_confirm('一旦发布将不能修改优惠券信息', 'index.php?app=seller_coupon&act=issue&id=<?php echo $this->_var['coupon']['coupon_id']; ?>');">发布</a> <a href="javascript:void(0);" class="edit1" uri="index.php?app=seller_coupon&amp;act=edit&id=<?php echo $this->_var['coupon']['coupon_id']; ?>" dialog_id="coupon_edit" dialog_title="编辑" dialog_width="460" ectype="dialog">编辑</a> <a class="delete" href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=seller_coupon&act=drop&id=<?php echo $this->_var['coupon']['coupon_id']; ?>');">删除</a><?php endif; ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="8" class="padding6"><div class="notice-word">
                    <p>没有符合条件的记录</p>
                  </div></td>
              </tr>
              <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
              <?php if ($this->_var['coupons']): ?>
            </tbody>
            <?php endif; ?> 
            <?php if ($this->_var['coupons']): ?>
            <tr class="btion">
              <th><input id="all2" type="checkbox" class="checkall" /></th>
              <th colspan="7"><p class="position1">
                  <label for="all2"><span class="all">全选</span></label>
                  <a href="javascript:void(0);" ectype="batchbutton" class="delete" uri="index.php?app=seller_coupon&act=drop" name="id" presubmit="confirm('您确定要删除它吗？')">删除</a></p>
                <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
              </th>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe name="seller_coupon" style="display:none;"></iframe>
<?php echo $this->fetch('member.footer.html'); ?>