<div class="table_salelog">
    <table class="w980">
        <tr class="theader">
            <th>
                买家
            </th>
            <th>
                购买价
            </th>
            <th>
                购买数量
            </th>
            <th>
                成交时间
            </th>
            <th>
                评价
            </th>
        </tr>
        <?php $_from = $this->_var['sales_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sales');$this->_foreach['fe_sales'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_sales']['total'] > 0):
    foreach ($_from AS $this->_var['sales']):
        $this->_foreach['fe_sales']['iteration']++;
?>
        <script type="text/javascript">
            $(function() {
                $('#ev_<?php echo $this->_foreach['fe_sales']['iteration']; ?>').raty({
                    readOnly: true,
                    score: '<?php echo $this->_var['sales']['goods_evaluation']; ?>'
                });
            })
        </script>
        <tr>
            <td>
                <?php if ($this->_var['sales']['anonymous']): ?>***<?php else: ?><?php echo htmlspecialchars($this->_var['sales']['buyer_name']); ?><?php endif; ?>
            </td>
            <td>
                <?php echo price_format($this->_var['sales']['price']); ?>
            </td>
            <td>
                <?php echo $this->_var['sales']['quantity']; ?>
                <span class="fontColor5">
                    <?php if ($this->_var['sales']['specification']): ?>（<?php echo htmlspecialchars($this->_var['sales']['specification']); ?>）<?php endif; ?>
                </span>
            </td>
            <td>
                <?php echo local_date("Y-m-d",$this->_var['sales']['add_time']); ?>
            </td>
            <td id="ev_<?php echo $this->_foreach['fe_sales']['iteration']; ?>">
            </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
            <td colspan="6">
                <span class="light">
                    没有符合条件的记录
                </span>
            </td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
</div>
