<?php echo $this->fetch('member.header.html'); ?>
<style type="text/css">
#refund-list {}
#refund-list a{text-decoration:none;color:#285BCC}
#refund-list a:hover{color:#FF6600}
#refund-list .thead td{background:#f1f1f1; height:35px; text-align:center}
#refund-list .tbody td{border-bottom:1px #f4f4f4 solid; text-align:center; line-height:18px;padding-top:5px;padding-bottom:5px;}
</style>
<div id="main" class="clearfix">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
    	<?php echo $this->fetch('member.curlocal.html'); ?>
        <div class="wrapful">
        <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
            <div class="public">
            	<?php if ($this->_var['refunds']): ?>
                <table id="refund-list" border="0" cellpadding="0" cellspacing="0">
                	<tr class="thead">
                    	<td width="80">退款编号</td>
                        <td width="160">订单号/宝贝信息</td>
                        <td width="110">买家</td>
                        <td width="80">交易总额</td>
                        <td width="80">退款总额</td>
                        <td width="100">申请时间</td>
                        <td width="100">退款状态</td>
                        <td width="75">操作</td>
                    </tr>
                	<?php $_from = $this->_var['refunds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'refund');if (count($_from)):
    foreach ($_from AS $this->_var['refund']):
?>
                	<tr class="tbody">
                		<td><?php echo $this->_var['refund']['refund_sn']; ?></td>
                        <td><?php echo $this->_var['refund']['order_sn']; ?><br /><a href="<?php echo url('app=seller_order&act=view&order_id=' . $this->_var['refund']['order_id']. ''); ?>" target="_blank"><?php echo $this->_var['refund']['title']; ?></a></td>
                        <td><?php echo $this->_var['refund']['buyer_name']; ?></td>
                        <td><?php echo $this->_var['refund']['total_fee']; ?></td>
                        <td><?php echo $this->_var['refund']['refund_total_fee']; ?></td>
                        <td style="color:gray"><?php echo local_date("Y-m-d H:i:s",$this->_var['refund']['created']); ?></td>
                        <td style="color:#FF4F01">
                        	<?php if ($this->_var['refund']['status'] == 'CLOSED'): ?>
                        	<span style="color:gray">退款关闭</span>
                        	<?php elseif ($this->_var['refund']['status'] == 'SUCCESS'): ?>
                        	<span style="color:#62B44B">退款成功</span>
                        	<?php elseif ($this->_var['refund']['status'] == 'WAIT_SELLER_AGREE'): ?>
                        	买家已经申请退款，等待卖家同意
                        	<?php elseif ($this->_var['refund']['status'] == 'SELLER_REFUSE_BUYER'): ?>
                        	卖家拒绝退款，等待买家修改中
                            <?php elseif ($this->_var['refund']['status'] == 'WAIT_ADMIN_AGREE'): ?>
                            卖家已经同意退款，等待管理员审核
                        	<?php elseif ($this->_var['refund']['status'] == 'WAIT_SELLER_CONFIRM'): ?>
                        	退款申请等待卖家确认中
                        <?php endif; ?>	
                        <?php if ($this->_var['refund']['status'] != 'CLOSED' && $this->_var['refund']['status'] != 'SUCCESS' && $this->_var['refund']['status'] != 'WAIT_ADMIN_AGREE' && $this->_var['refund']['ask_customer'] == 1): ?><div style="color:#62B44B">（客服已介入处理）</div><?php endif; ?>
                        </td>
                        <td><a href="<?php echo url('app=refund&act=view&refund_id=' . $this->_var['refund']['refund_id']. ''); ?>" target="_blank">查看</a></td>
                    </tr>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                 </table>
                 
                 <div class="order_form_page mt10">
                    <div class="page">
                        <?php echo $this->fetch('member.page.bottom.html'); ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="notice-word"><p>没有符合条件的记录</p></div>
                <?php endif; ?>
            </div>
        </div>
        </div>
        </div>
        </div>
<?php echo $this->fetch('member.footer.html'); ?>
