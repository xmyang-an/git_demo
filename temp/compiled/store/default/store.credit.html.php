<?php echo $this->fetch('header.html'); ?>
<div id="page-credit">
	<?php echo $this->fetch('curlocal.html'); ?>
    <div class="w-shop clearfix">
        <div class="col-sub w210">
            <?php echo $this->fetch('left.html'); ?>
        </div>
        <div class="col-main ml10 w980">
            <div class="border mb10">
            	<a name="module"></a>
                <div class="title border-b">
                    <h3>
                        好评率：
                        <b style="<?php if ($this->_var['store']['praise_rate'] <= 50): ?>color:#28B779;<?php else: ?>color:#DA542E ;<?php endif; ?>">
                            <?php echo $this->_var['store']['praise_rate']; ?>%
                        </b>
                    </h3>
                </div>
                <div class="credit-recorder">
                    <table class="w-full">
                        <tr>
                            <th>
                            </th>
                            <th>
                                最近1周
                            </th>
                            <th>
                                最近1个月
                            </th>
                            <th>
                                最近6个月
                            </th>
                            <th>
                                6个月前
                            </th>
                            <th class="border-r-0">
                                总计
                            </th>
                        </tr>
                        <tr style="color:#DA542E;font-weight:bold;">
                            <th>
                                <div>
                                    好评
                                </div>
                            </th>
                            <td>
                                <?php echo $this->_var['stats']['3']['in_a_week']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['3']['in_a_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['3']['in_six_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['3']['six_month_before']; ?>
                            </td>
                            <td class="border-r-0">
                                <?php echo $this->_var['stats']['3']['total']; ?>
                            </td>
                        </tr>
                        <tr style="font-weight:bold;">
                            <th>
                                <div>
                                    中评
                                </div>
                            </th>
                            <td>
                                <?php echo $this->_var['stats']['2']['in_a_week']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['2']['in_a_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['2']['in_six_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['2']['six_month_before']; ?>
                            </td>
                            <td class="border-r-0">
                                <?php echo $this->_var['stats']['2']['total']; ?>
                            </td>
                        </tr>
                        <tr style="color: #28B779;font-weight:bold;">
                            <th>
                                <div>
                                    差评
                                </div>
                            </th>
                            <td>
                                <?php echo $this->_var['stats']['1']['in_a_week']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['1']['in_a_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['1']['in_six_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['1']['six_month_before']; ?>
                            </td>
                            <td class="border-r-0">
                                <?php echo $this->_var['stats']['1']['total']; ?>
                            </td>
                        </tr>
                        <tr style="font-weight:bold;color: #2953a6;" class="border-b-0">
                            <th>
                                <div>
                                    总计
                                </div>
                            </th>
                            <td>
                                <?php echo $this->_var['stats']['0']['in_a_week']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['0']['in_a_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['0']['in_six_month']; ?>
                            </td>
                            <td>
                                <?php echo $this->_var['stats']['0']['six_month_before']; ?>
                            </td>
                            <td class="border-r-0">
                                <?php echo $this->_var['stats']['0']['total']; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="credit-detail mb10">
              <ul class="user-menu">
                <li class="<?php if (! $_GET['eval']): ?>active<?php endif; ?>">
                  <a style="border-left:1px solid #ddd;" href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. ''); ?>#module">
                    <span>
                      全部评价
                    </span>
                  </a>
                </li>
                <li class="<?php if ($_GET['eval'] == 3): ?>active<?php endif; ?>">
                  <a href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. '&eval=3'); ?>#module">
                    <span>
                      好评
                    </span>
                  </a>
                </li>
                <li class="<?php if ($_GET['eval'] == 2): ?>active<?php endif; ?>">
                  <a href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. '&eval=2'); ?>#module">
                    <span>
                      中评
                    </span>
                  </a>
                </li>
                <li class="<?php if ($_GET['eval'] == 1): ?>active<?php endif; ?>">
                  <a href="<?php echo url('app=store&act=credit&id=' . $this->_var['store']['store_id']. '&eval=1'); ?>#module">
                    <span>
                      差评
                    </span>
                  </a>
                </li>
              </ul>
              <div class="detail">
                <table>
                  <tr class="table-header">
                    <th width="10%">
                      评价
                    </th>
                    <th width="25%" class="width2">
                      内容
                    </th>
                    <th width="25%" class="width3">
                      商品
                    </th>
                    <th width="10%">
                      金额
                    </th>
                    <th width="10%">
                      买家
                    </th>
                    <th width="20%">
                      时间
                    </th>
                  </tr>
                  <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
                  <tr>
                    <th>
                      <?php if ($this->_var['goods']['evaluation'] == 1): ?>
                      <span style="color: #28B779;font-weight:bold;">
                        差评
                      </span>
                      <?php elseif ($this->_var['goods']['evaluation'] == 2): ?>
                      <span>
                        中评
                      </span>
                      <?php else: ?>
                      <span style="color:#DA542E;font-weight:bold;">
                        好评
                      </span>
                      <?php endif; ?>
                    </th>
                    <td>
                      <div>
                        <?php echo nl2br(htmlspecialchars($this->_var['goods']['comment'])); ?>
                      </div>
                    </td>
                    <td>
                      <a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>">
                        <?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>
                      </a>
                    </td>
                    <td class="price">
                      <?php echo price_format($this->_var['goods']['price']); ?>
                    </td>
                    <td>
                      <?php echo htmlspecialchars($this->_var['goods']['buyer_name']); ?>
                    </td>
                    <td>
                      <?php echo local_date("Y-m-d H:i:s",$this->_var['goods']['evaluation_time']); ?>
                    </td>
                  </tr>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </table>
              </div>
              <?php echo $this->fetch('page.bottom.html'); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->fetch('footer.html'); ?>