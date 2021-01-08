<script type="text/javascript">
$(function(){
    $('#cancel_button').click(function(){
        DialogManager.close('seller_order_cancel_order');
    });
    $("input[name='cancel_reason']").click(function(){
        if ($(this).attr('flag') == 'other_reason')
        {
            $('#other_reason').show();
        }
        else
        {
            $('#other_reason').hide();
        }


    });
});
</script>
<div class="content1">
<div id="warning"></div>
<form method="post" action="index.php?app=buyer_order&act=cancel_order&order_id=<?php echo $this->_var['order_id']; ?>" target="iframe_post">
    <h1>您是否确定要取消以下订单？</h1>
    <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');if (count($_from)):
    foreach ($_from AS $this->_var['order']):
?>
    <p>订单号&nbsp;&nbsp;&nbsp;&nbsp;:<span><?php echo $this->_var['order']['order_sn']; ?></span></p>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <dl>
        <dt>取消原因:</dt>
        <dd>
            <div class="li"><input type="radio" checked name="cancel_reason" id="d1" value="改选其他商品" /> <label for="d1">改选其他商品</label></div>
            <div class="li"><input type="radio" name="cancel_reason" id="d2" value="改选其他配送方式" /> <label for="d2">改选其他配送方式</label></div>
            <div class="li"><input type="radio" name="cancel_reason" id="d3" value="改选其他卖家" /> <label for="d3">改选其他卖家</label></div>
            <div class="li"><input type="radio" name="cancel_reason" flag="other_reason" id="d4" value="其他原因" /> <label for="d4">其他原因</label></div>
        </dd>
        <dd id="other_reason" style="display:none">
            <textarea class="text" id="other_reason_input" style="width:200px;" name="remark"></textarea>
        </dd>
    </dl>
    <div class="btn">
        <input type="submit" id="confirm_button" class="btn1" value="确认" />
    </div>
    </form>
</div>

