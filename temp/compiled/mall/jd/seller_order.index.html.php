<?php echo $this->fetch('member.header.html'); ?> 
<script type="text/javascript">
$(function(){
    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});

    $('a[ectype="batchopt"]').click(function(){
        var items = getCheckItemIds();
		if(items)
		{
        	var uri = $(this).attr('uri');
       	 	uri = uri + '&' + $(this).attr('name') + '=' + items;
        	var id = $(this).attr('dialog_id') ? $(this).attr('dialog_id') : 'seller_order_cancel_order';
        	var title = $(this).attr('dialog_title') ? $(this).attr('dialog_title') : '取消订单';
        	var width = '500';
        	ajax_form(id, title, uri, width);
		}
		else {
			layer.open({content:'没有选任何项'})
		}
		return false;
    });
	
	$('.J_Memo').hover(function(){
		$(this).children('.pop').show('fast');
	},function(){
		$(this).children('.pop').hide('fast');
	});
});
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_index table">
          <table>
            <tr class="line_bold">
              <th colspan="8"> <div class="search_div clearfix align1">
                  <form method="get" class="float-left clearfix">
                    <div class="float-left"> <span class="title">订单号:</span>
                      <input class="text_normal" type="text" name="order_sn" value="<?php echo htmlspecialchars($this->_var['query']['order_sn']); ?>" />
                      <span class="title">下单时间:</span>
                      <input class="text_normal width2" type="text" name="add_time_from" id="add_time_from" value="<?php echo $this->_var['query']['add_time_from']; ?>" />
                      &#8211;
                      <input class="text_normal width2" id="add_time_to" type="text" name="add_time_to" value="<?php echo $this->_var['query']['add_time_to']; ?>" />
                      <span class="title">买家:</span>
                      <input class="text_normal" type="text" name="buyer_name" value="<?php echo htmlspecialchars($this->_var['query']['buyer_name']); ?>" />
                      <input type="hidden" name="app" value="seller_order" />
                      <input type="hidden" name="act" value="index" />
                      <input type="hidden" name="type" value="<?php echo $this->_var['type']; ?>" />
                      <input type="submit" class="btn" value="搜索" />
                    </div>
                    <?php if ($this->_var['query']['buyer_name'] || $this->_var['query']['add_time_from'] || $this->_var['query']['add_time_to'] || $this->_var['query']['order_sn']): ?> 
                    <a class="detlink" href="<?php echo url('app=seller_order&type=' . $this->_var['query']['type']. ''); ?>">取消检索</a> 
                    <?php endif; ?>
                  </form>
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
              <th>买家</th>
              <th>订单总价</th>
              <th>订单状态</th>
              <th>评价</th>
            </tr>
            
            <?php if ($this->_var['orders']): ?>
            
            
             
            <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');$this->_foreach['fe_order'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_order']['total'] > 0):
    foreach ($_from AS $this->_var['order']):
        $this->_foreach['fe_order']['iteration']++;
?>
            <tr class="sep-row" height="10">
              <td colspan="8"></td>
            </tr>
            <tr class="line-hd">
              <th colspan="8" class="clearfix"> <p class="float-left">
                  <input type="checkbox" value="<?php echo $this->_var['order']['order_id']; ?>" class="checkitem J_CheckItem"/>
                  <label>订单号：</label>
                  <?php echo $this->_var['order']['order_sn']; ?>
                  <label>成交时间：</label>
                  <?php echo local_date("Y-m-d H:i:s",$this->_var['order']['add_time']); ?> </p>
                <div class="memo float-right J_Memo"> <a uri="index.php?app=seller_order&amp;act=add_memo&amp;order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax" dialog_width="450" dialog_title="备忘" ectype="dialog"  dialog_id="seller_order_add_memo" id="order<?php echo $this->_var['order']['order_id']; ?>_action_add_memo" class="flag flag<?php echo $this->_var['order']['flag']; ?>" /></a>
                  <p class="pop"><?php echo $this->_var['order']['memo']; ?></p>
                </div>
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
              <td valign="top" class="align2"><?php echo $this->_var['goods']['price']; ?></td>
              <td valign="top" class="align2"><strong><?php echo $this->_var['goods']['quantity']; ?></strong></td>
              <?php if (($this->_foreach['fe_goods']['iteration'] <= 1)): ?>
              <td valign="top" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"> <?php echo htmlspecialchars($this->_var['order']['payment_name']); ?> </td>
              <td valign="top" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><a href="<?php echo url('app=message&act=send&to_id=' . $this->_var['order']['buyer_id']. ''); ?>" target="_blank"><?php echo htmlspecialchars($this->_var['order']['buyer_name']); ?></a> <br />
                
                <?php if ($this->_var['order']['buyer_info']['real_name']): ?><?php echo sub_str(htmlspecialchars($this->_var['order']['buyer_info']['real_name']),14); ?><?php else: ?>----<?php endif; ?> 
                <br />
                <a href="javascript:;" class="J_StartLayim" data-toid="<?php echo $this->_var['order']['buyer_id']; ?>"><img src="<?php echo $this->_var['site_url']; ?>/static/images/myim2.png" width="17" height="17" style="vertical-align:middle" /></a> <a target="_blank" href="<?php echo url('app=message&act=send&to_id=' . $this->_var['order']['buyer_id']. ''); ?>" class="email"></a></td>
              <td valign="top" class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><strong><?php echo $this->_var['order']['order_amount']; ?></strong><br />
                <span class="gray-color">(含运费:<?php echo $this->_var['order']['shipping_fee']; ?>)</span>
                <div class="btn-order-status"> 
                   
                  <a href="javascript:;" uri="index.php?app=seller_order&amp;act=adjust_fee&amp;order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax" dialog_width="450" dialog_title="调整费用" ectype="dialog"  dialog_id="seller_order_adjust_fee" id="order<?php echo $this->_var['order']['order_id']; ?>_action_adjust_fee"<?php if ($this->_var['order']['status'] != ORDER_PENDING && $this->_var['order']['status'] != ORDER_SUBMITTED): ?> style="display:none"<?php endif; ?> >调整费用</a> 
                  
                  <?php if ($this->_var['order']['refund_status'] == 'SUCCESS'): ?> 
                  
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" style="color:#096">退款成功</a> 
                  
                  <?php elseif ($this->_var['order']['refund_status'] == 'CLOSED'): ?> 
                  
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" class="gray">退款关闭</a> 
                  
                  <?php elseif ($this->_var['order']['refund_status']): ?> 
                  
                  <a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['order']['refund_id']. ''); ?>" style="color:#ff6600">退款中</a> 
                  
                  <?php endif; ?> 
                </div></td>
              <td valign="top"class="align2 bottom-blue" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><div class="btn-order-status">
                  <p><span class="<?php if ($this->_var['order']['status'] == 0): ?>gray-color<?php else: ?>color4<?php endif; ?>"><?php echo call_user_func("order_status",$this->_var['order']['status']); ?></span></p>
                   
                  <a href="<?php echo url('app=seller_order&act=view&order_id=' . $this->_var['order']['order_id']. ''); ?>" target="_blank">查看订单</a> 
                   
                  <?php if ($this->_var['order']['can_ship']): ?>
                  <a href="javascript:;" class="btn-order-status-shipped" ectype="dialog" dialog_title="发货" dialog_id="seller_order_shipped" uri="index.php?app=seller_order&amp;act=shipped&amp;order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax"  dialog_width="400" id="order<?php echo $this->_var['order']['order_id']; ?>_action_shipped"<?php if ($this->_var['order']['status'] != ORDER_ACCEPTED && ( $this->_var['order']['status'] != ORDER_SUBMITTED || $this->_var['order']['payment_code'] != 'cod' )): ?> style="display:none"<?php endif; ?> />发货</a> 
                  <?php endif; ?>
                   
                  <a href="javascript:;" ectype="dialog" uri="index.php?app=seller_order&amp;act=cancel_order&order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax" dialog_title="取消订单" dialog_id="seller_order_cancel_order" dialog_width="400" id="order<?php echo $this->_var['order']['order_id']; ?>_action_cancel"<?php if ($this->_var['order']['status'] != ORDER_SUBMITTED && $this->_var['order']['status'] != ORDER_PENDING): ?> style="display:none"<?php endif; ?> />取消订单</a> 
                   
                  <a href="javascript:;" ectype="dialog" dialog_title="修改单号" uri="index.php?app=seller_order&amp;act=shipped&amp;order_id=<?php echo $this->_var['order']['order_id']; ?>&ajax" dialog_id="seller_order_shipped" dialog_width="400" id="order<?php echo $this->_var['order']['order_id']; ?>_action_edit_invoice_no"<?php if ($this->_var['order']['status'] != ORDER_SHIPPED): ?> style="display:none" <?php endif; ?> />修改单号</a> 
                   
                  <?php if ($this->_var['enable_express']): ?> 
                  <a target="_blank" class="btn1" href="<?php echo url('app=order_express&order_id=' . $this->_var['order']['order_id']. ''); ?>" <?php if ($this->_var['order']['status'] != ORDER_SHIPPED && $this->_var['order']['status'] != ORDER_FINISHED): ?> style="display:none"<?php endif; ?>>查看物流</a> 
                  <?php endif; ?> 
                </div></td>
              <td valign="top" width="54" class="align2 bottom-blue last" rowspan="<?php echo $this->_var['order']['goods_quantities']; ?>"><?php if ($this->_var['order']['evaluation_status']): ?>
                
                <p class="gray-color">已评价</p>
                
                <?php endif; ?></td>
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
              <td colspan="8"></td>
            </tr>
            <tr class="operations btion">
              
              <th colspan="8"> <p class="position1 clearfix">
                  <input type="checkbox" id="all2" class="checkall float-left mt5" />
                  <label for="all2" class="float-left mr20 ml5" style="margin-top:2px;">全选</label>
                  <a href="javascript:;" class="delete" ectype="batchopt" uri="index.php?app=seller_order&act=cancel_order" name="order_id">取消订单</a> <a href="javascript:;" class="printed" dialog_title="打印订单" ectype="batchopt" uri="index.php?app=seller_order&act=printed&ajax" dialog_id="seller_order_printed" name="order_id">打印订单</a></p>
                <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
              </th>
            </tr>
            
            <?php else: ?>
            <tr class="sep-row">
              <td colspan="8"><div class="notice-word">
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
<iframe id="seller_order" name="seller_order" src="about:blank" frameborder="0" width="0" height="0"></iframe>
<?php echo $this->fetch('member.footer.html'); ?> 