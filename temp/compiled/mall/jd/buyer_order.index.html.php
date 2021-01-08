<?php echo $this->fetch('member.header.html'); ?> 
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
	
	$('a[ectype="batchcancel"]').click(function(){
		var items = getCheckItemIds();
		if(items)
		{
        	var uri = $(this).attr('uri');
       	 	uri = uri + '&' + $(this).attr('name') + '=' + items;
        	var id = 'buyer_order_cancel_order';
        	var title = $(this).attr('dialog_title') ? $(this).attr('dialog_title') : '取消订单';
        	//var url = $(this).attr('uri');
        	var width = '500';
        	ajax_form(id, title, uri, width);
		}
		else {
			layer.open({content:'没有选任何项'})
		}
    });
	$('.J_MergePay').click(function(){
		var items = getCheckItemIds();
		if(items)
		{
			var uri = $(this).attr('uri');
        	uri = uri + '&' + $(this).attr('name') + '=' + items;
			location.href = uri;
		}
	});
});
function getCheckItemIds()
{
	if($('.checkitem:checked').length == 0){
		return false;
	}
	if($(this).attr('presubmit')){
		if(!eval($(this).attr('presubmit'))){
			return false;
		}
	}
	var items = '';
	$('.checkitem:checked').each(function(){
		items += this.value + ',';
	});
	items = items.substr(0, (items.length - 1));
		
	return items;
}
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_index table">
          <table>
            <tr class="line_bold">
              <th colspan="7"> <div class="search_div clearfix align1">
                  <form method="get" class="float-left clearfix">
                    <span>下单时间: </span>
                    <input type="text" class="text1 width2" name="add_time_from" id="add_time_from" value="<?php echo $this->_var['query']['add_time_from']; ?>"/>
                    &#8211;
                    <input type="text" class="text1 width2" name="add_time_to" id="add_time_to" value="<?php echo $this->_var['query']['add_time_to']; ?>"/>
                    <span>订单号:</span>
                    <input type="text" class="text1" name="order_sn" value="<?php echo htmlspecialchars($this->_var['query']['order_sn']); ?>">
                    <span>订单状态</span>
                    <select name="type">
                      
                    					<?php echo $this->html_options(array('options'=>$this->_var['types'],'selected'=>$this->_var['type'])); ?>
									
                    </select>
                    <input type="hidden" name="app" value="buyer_order" />
                    <input type="hidden" name="act" value="index" />
                    <input type="submit" class="btn" value="搜索" />
                  </form>
                  <?php if ($this->_var['query']['seller_name'] || $this->_var['query']['add_time_from'] || $this->_var['query']['add_time_to'] || $this->_var['query']['order_sn'] || $this->_var['query']['type']): ?> 
                  <a class="detlink" href="<?php echo url('app=buyer_order'); ?>">取消检索</a> 
                  <?php endif; ?> 
                </div>
              </th>
            </tr>
            <tr class="sep-row" height="20">
              <td colspan="8"></td>
            </tr>
            <tr class="line gray">
              <th class="align1">
              	<label class="mr20"><input type="checkbox" id="all" class="checkall mr5" />全选</label>
                <span class="ml10">商品名称</span>
              </th>
              <th>价格</th>
              <th>数量</th>
              <th>支付方式</th>
              <th>订单总价</th>
              <th>订单状态</th>
              <th>评价</th>
            </tr>
            <tr class="sep-row">
              <td colspan="7"></td>
            </tr>
            
            <?php if ($this->_var['orders']): ?>
            
            
            <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
            <tr class="sep-row">
              <td colspan="7"></td>
            </tr>
            <tr class="line-hd">
              <th colspan="7"> <p> <input type="checkbox" value="<?php echo $this->_var['order']['order_id']; ?>" class="checkitem" <?php if ($this->_var['order']['status'] != ORDER_PENDING && $this->_var['order']['status'] != ORDER_SUBMITTED): ?>disabled="disabled"<?php endif; ?> />
                  <label>订单号：</label>
                  <?php echo $this->_var['order']['order_sn']; ?>
                  <label>下单时间：</label>
                  <?php echo local_date("Y-m-d H:i:s",$this->_var['order']['add_time']); ?> <a href="<?php echo url('app=store&id=' . $this->_var['order']['seller_id']. ''); ?>" target="_blank" style="margin-left:15px;"><?php echo htmlspecialchars($this->_var['order']['seller_name']); ?></a> <a href="javascript:;" class="J_StartLayim" data-toid="<?php echo $this->_var['order']['seller_id']; ?>"><img src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" style="vertical-align:middle" /></a> <a target="_blank" href="<?php echo url('app=message&act=send&to_id=' . $this->_var['order']['seller_id']. ''); ?>" class="email"></a> </p>
              </th>
            </tr>
            
            <?php $_from = $this->_var['order']['order_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
            <tr class="line line-blue <?php if (! $this->_var['order']['order_gift'] && ($this->_foreach['fe_goods']['iteration'] == $this->_foreach['fe_goods']['total'])): ?>last_line<?php endif; ?>">
              <td valign="top" class="first clearfix"><div class="pic-info float-left"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['goods_image']; ?>" width="50" height="50" /></a> </div>
                <div class="txt-info float-left">
                  <div class="txt"> <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['goods']['goods_name']; ?></a> </div>
                  <?php if ($this->_var['goods']['specification']): ?>
                  <p class="gray-color mt5"><?php echo $this->_var['goods']['specification']; ?></p>
                  <?php endif; ?> 
                </div></td>
              <td class="align2"><?php echo price_format($this->_var['goods']['price']); ?></td>
              <td class="align2"><?php echo $this->_var['goods']['quantity']; ?></td>
              <?php if (($this->_foreach['fe_goods']['iteration'] <= 1)): ?>
              <td valign="top" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><div class="mt15"><?php if ($this->_var['order']['payment_name']): ?><?php echo htmlspecialchars($this->_var['order']['payment_name']); ?><?php endif; ?></div></td>
              <td valign="top" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><div class="mt15"><b id="order<?php echo $this->_var['order']['order_id']; ?>_order_amount"><?php echo price_format($this->_var['order']['order_amount']); ?></b></div></td>
              <td valign="top" width="100" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><div class="btn-order-status">
                  <p><span class="<?php if ($this->_var['order']['status'] == 0): ?>gray-color<?php else: ?>color4<?php endif; ?>"><?php echo call_user_func("order_status",$this->_var['order']['status']); ?></span></p>
                   
                  <a href="<?php echo url('app=buyer_order&act=view&order_id=' . $this->_var['order']['order_id']. ''); ?>" target="_blank">查看订单</a> 
                   
                  <a href="<?php echo url('app=cashier&order_id=' . $this->_var['order']['order_id']. ''); ?>" target="_blank" id="order<?php echo $this->_var['order']['order_id']; ?>_action_pay"<?php if ($this->_var['order']['status'] != ORDER_PENDING): ?> style="display:none"<?php endif; ?> class="btn-order-status-pay">付款</a> 
                   
                  <a href="javascript:;" ectype="dialog" dialog_id="buyer_order_confirm_order" dialog_width="400" dialog_title="确认收货" uri="index.php?app=buyer_order&amp;act=confirm_order&order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax"  id="order<?php echo $this->_var['order']['order_id']; ?>_action_confirm"<?php if ($this->_var['order']['status'] != ORDER_SHIPPED): ?> style="display:none"<?php endif; ?> />确认收货</a> 
                   
                  <a href="javascript:;" ectype="dialog" dialog_width="400" dialog_title="取消订单" dialog_id="buyer_order_cancel_order" uri="index.php?app=buyer_order&amp;act=cancel_order&order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax"  id="order<?php echo $this->_var['order']['order_id']; ?>_action_cancel" <?php if ($this->_var['order']['status'] != ORDER_PENDING && $this->_var['order']['status'] != ORDER_SUBMITTED): ?>style="display:none"<?php endif; ?>>取消订单</a>
                   
                  <?php if ($this->_var['enable_express']): ?> 
                  <a target="_blank" class="btn1" href="<?php echo url('app=order_express&order_id=' . $this->_var['order']['order_id']. ''); ?>" <?php if ($this->_var['order']['status'] != ORDER_SHIPPED && $this->_var['order']['status'] != ORDER_FINISHED): ?>style="display:none"<?php endif; ?>>查看物流</a>
                  <?php endif; ?> 
                  
                  <?php if ($this->_var['order']['payment_code'] != 'cod'): ?> 
                  <?php if ($this->_var['order']['refund_status'] == 'SUCCESS'): ?> 
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" style="color:#096">退款成功</a> 
                  <?php elseif ($this->_var['order']['refund_status'] == 'CLOSED'): ?> 
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" class="gray">退款关闭</a> 
                  <?php elseif ($this->_var['order']['refund_status']): ?> 
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" style="color:#ff6600">退款中</a> 
                  <?php elseif (( in_array ( $this->_var['order']['status'] , array ( 20 , 30 ) ) )): ?> 
                  <?php if ($this->_var['order']['can_refund']): ?>
                  <a href="<?php echo url('app=refund&act=add&order_id=' . $this->_var['order']['order_id']. ''); ?>">退款/退货</a> 
                  <?php endif; ?> 
                  <?php endif; ?> 
                  <?php endif; ?> 
                  
                </div></td>
              <td width="60" class="align2 bottom-blue last" valign="top" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>">
              	<?php if ($this->_var['order']['evaluation_status']): ?>
                <p class="gray-color">我已评价</p>
                <?php endif; ?> 
                <a class="btn1" href="<?php echo url('app=buyer_order&act=evaluate&order_id=' . $this->_var['order']['order_id']. ''); ?>" target="_blank" id="order<?php echo $this->_var['order']['order_id']; ?>_evaluate" <?php if ($this->_var['order']['status'] != ORDER_FINISHED || $this->_var['order']['evaluation_status'] != 0): ?>style="display:none"<?php endif; ?>> 我要评价</a>
               
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php $_from = $this->_var['order']['order_gift']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['fe_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['fe_goods']['iteration']++;
?>
            <tr class="line line-blue <?php if (($this->_foreach['fe_goods']['iteration'] == $this->_foreach['fe_goods']['total'])): ?>last_line<?php endif; ?>">
              <td valign="top" class="first clearfix relative"><div class="pic-info float-left"> <a href="<?php echo url('app=gift&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['goods']['default_image']; ?>" width="50" height="50" /></a> </div>
                <div class="txt-info float-left">
                  <div class="txt"> <a href="<?php echo url('app=gift&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><?php echo $this->_var['goods']['goods_name']; ?></a> </div>
                  <em class="label-gift">赠品</em> </div></td>
              <td class="align2"><?php echo price_format($this->_var['goods']['price']); ?></td>
              <td class="align2"><?php echo $this->_var['goods']['quantity']; ?></td>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            
            <tr class="sep-row">
              <td colspan="7"></td>
            </tr>
            <tr class="operations btion">
              <th colspan="7"> <p class="position1 clearfix">
                  <input type="checkbox" id="all2" class="checkall float-left mt5" />
                  <label for="all2" class="float-left mr20 ml5" style="margin-top:2px;">全选</label>
                  <a href="javascript:;" class="delete" ectype="batchcancel" uri="index.php?app=buyer_order&act=cancel_order" name="order_id">取消订单</a> <a href="javascript:;" class="mergepay J_MergePay" uri="index.php?app=cashier" name="order_id">合并付款</a> </p>
                <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
              </th>
            </tr>
            
            <?php else: ?>
            <tr class="sep-row">
              <td colspan="7"><div class="notice-word">
                  <p>没有符合条件的订单</p>
                </div></td>
            </tr>
            <?php endif; ?>
            
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe id='iframe_post' name="iframe_post" src="about:blank" frameborder="0" width="0" height="0" style="display:none"></iframe>
<?php echo $this->fetch('member.footer.html'); ?> 