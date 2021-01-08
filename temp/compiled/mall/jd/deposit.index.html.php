<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public table deposit"> 
          <?php if ($this->_var['deposit_account']): ?>
          <div class="deposit-account">
            <div class="account-info">
              <h1>预存款账户余额</h1>
              <div class="explain">余额支付[<em>?<span></span></em>]： 
                <?php if ($this->_var['deposit_account']['pay_status'] == 'ON'): ?> 
                <a href="<?php echo url('app=deposit&act=pay_status&status=off'); ?>" onclick="return confirm('点击后关闭余额支付功能，要确定么？')">已开启>></a> 
                <?php else: ?> 
                <a href="<?php echo url('app=deposit&act=pay_status&status=on'); ?>" onclick="return confirm('点击后开启余额支付功能，要确定么？')">已关闭>></a> 
                <?php endif; ?> 
              </div>
              <div class="action">
                <h2>账户名：<?php echo $this->_var['deposit_account']['account']; ?></h2>
                <a href="<?php echo url('app=deposit&act=recordlist'); ?>">账户收支明细</a> </div>
              <div class="balanceNum"> <em><?php echo ($this->_var['deposit_account']['money'] == '') ? '0' : $this->_var['deposit_account']['money']; ?></em>元可用<?php if ($this->_var['deposit_account']['frozen'] > 0): ?>，<b><a href="<?php echo url('app=deposit&act=frozenlist'); ?>"><?php echo $this->_var['deposit_account']['frozen']; ?></a></b> 元不可用<?php endif; ?>
                <div class="balanceBtn"> <a href="<?php echo url('app=deposit&act=recharge'); ?>" class="btn-alipay"><span>充值</span></a> <a href="<?php echo url('app=deposit&act=withdraw'); ?>" class="btn-alipay btn-alipay-white"><span>提现</span></a> <a href="<?php echo url('app=deposit&act=transfer'); ?>" class="btn-alipay btn-alipay-green"><span>转账</span></a> </div>
              </div>
            </div>
            <div class="tradelist">
              <div class="title clearfix">
                <h1>最近交易记录</h1>
              </div>
              <div class="subtit">
                <ul class="clearfix">
                  <li class="time">创建日期</li>
                  <li class="info">名称 | 备注</li>
                  <li class="tradeNo">商户订单号 | 交易号</li>
                  <li class="party">对方</li>
                  <li class="amount">金额</li>
                  <li class="status">状态</li>
                  <li class="detail">操作</li>
                </ul>
              </div>
              <div class="list clearfix"> 
                <?php if ($this->_var['recordlist']): ?> 
                <?php $_from = $this->_var['recordlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'record');if (count($_from)):
    foreach ($_from AS $this->_var['record']):
?>
                <ul class="clearfix" style="<?php if ($this->_var['record']['refund']): ?> border-bottom:1px #ddd dotted;<?php endif; ?><?php if (in_array ( $this->_var['record']['refund']['status'] , array ( 'CLOSED' ) )): ?>color:gray<?php endif; ?>">
                  <li class="time"><?php echo local_date("Y.m.d H:i:s",$this->_var['record']['add_time']); ?></li>
                  <li class="info break-word"><?php echo $this->_var['record']['title']; ?></li>
                  <li class="tradeNo break-word"><?php echo $this->_var['record']['bizOrderId']; ?> | <?php echo $this->_var['record']['tradeNo']; ?></li>
                  <li class="party"><?php echo $this->_var['record']['partyInfo']['name']; ?></li>
                  <li class="amount"> <?php if ($this->_var['record']['flow'] == 'income'): ?><strong class="price green">+<?php echo $this->_var['record']['amount']; ?></strong> <?php else: ?> <strong class="price f60">-<?php echo $this->_var['record']['amount']; ?></strong> <?php endif; ?> </li>
                  <li class="status"> <span class="<?php if (in_array ( $this->_var['record']['status'] , array ( 'CLOSED' ) ) || in_array ( $this->_var['record']['refund']['status'] , array ( 'CLOSED' ) )): ?>gray <?php elseif (! in_array ( $this->_var['record']['status'] , array ( 'SUCCESS' ) )): ?>f60<?php endif; ?>"><?php echo $this->_var['record']['status_label']; ?></span> </li>
                  <li class="detail"><a href="<?php echo url('app=deposit&act=record&tradeNo=' . $this->_var['record']['tradeNo']. ''); ?>">详情</a></li>
                </ul>
                <?php if (in_array ( $this->_var['record']['refund']['status'] , array ( 'SUCCESS' ) )): ?>
                <ul class="refund gray clearfix">
                  <li class="time"></li>
                  <li class="info"> 
                    <?php if ($this->_var['record']['buyer_id'] == $this->_var['visitor']['user_id']): ?> 
                    退款 
                    <?php else: ?> 
                    退款给买家 
                    <?php endif; ?> 
                  </li>
                  <li class="tradeNo break-word"></li>
                  <li class="party"></li>
                  <li class="amount"> <strong class="gray"> 
                    <?php if ($this->_var['record']['buyer_id'] == $this->_var['visitor']['user_id']): ?>+<?php else: ?>-<?php endif; ?><?php echo $this->_var['record']['refund']['amount']; ?> </strong> </li>
                </ul>
                <?php endif; ?> 
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <div class="list-more"><a href="<?php echo url('app=deposit&act=tradelist'); ?>">查看所有交易记录</a></div>
                <?php else: ?>
                <ul class="no-data">
                  <li>还没有任何记录</li>
                </ul>
                <?php endif; ?> 
              </div>
            </div>
            <div class="bank-info">
              <h1>提现账户</h1>
              <div class="explain">已绑定<span><?php echo ($this->_var['bank_list']['count'] == '') ? '0' : $this->_var['bank_list']['count']; ?></span>个</div>
              <div class="action"> <a href="<?php echo url('app=bank&act=add'); ?>">+提现账户</a> </div>
              <ul class="cards clearfix">
                <?php $_from = $this->_var['bank_list']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'bank');$this->_foreach['fe_bank'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_bank']['total'] > 0):
    foreach ($_from AS $this->_var['bank']):
        $this->_foreach['fe_bank']['iteration']++;
?>
                <li class="card">
                  <h2><?php echo $this->_var['bank']['bank_name']; ?></h2>
                  <div class="number"><?php echo $this->_var['bank']['num']; ?></div>
                  <div class="handle clearfix">
                    <!--<div class="card-type <?php echo $this->_var['bank']['type']; ?>"></div>-->
                    <div class="card-handle"> <a href="<?php echo url('app=bank&act=edit&bid=' . $this->_var['bank']['bid']. ''); ?>">编辑</a> <a href="<?php echo url('app=bank&act=drop&bid=' . $this->_var['bank']['bid']. ''); ?>" onclick="return confirm('删除后不可恢复，您确认吗？')">删除</a> </div>
                  </div>
                </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <!--<li class="card add-card"> <a href="<?php echo url('app=bank&act=add&type=debit'); ?>" title="添加银行卡（储蓄卡）"></a> </li>
                <li class="card add-card"> <a href="<?php echo url('app=bank&act=add&type=credit'); ?>" title="添加银行卡（信用卡）"></a> </li>-->
              </ul>
            </div>
          </div>
          <?php else: ?>
          <div class="notice-word">
            <p>请先配置帐户，点击 <a href="<?php echo url('app=deposit&act=config'); ?>">立即配置</a></p>
          </div>
          <?php endif; ?> 
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 