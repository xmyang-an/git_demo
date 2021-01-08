<?php echo $this->fetch('header.html'); ?>
<div id="page-credit">
    <?php echo $this->fetch('curlocal.html'); ?>
    <div class="w-shop clearfix">
        <div class="col-sub w210">
            <?php echo $this->fetch('left.html'); ?>
        </div>
        <div class="col-main ml10 w980">
            <div class="search-goods goods-list-shop mb10 border">
                <div class="title clearfix border-b">
                    <h3 class="float-left">
                        <?php echo htmlspecialchars($this->_var['search_name']); ?>
                    </h3>
                </div>
                <ul class="content w-full clearfix">
                    <?php $_from = $this->_var['searched_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sgoods');$this->_foreach['fe_s'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_s']['total'] > 0):
    foreach ($_from AS $this->_var['sgoods']):
        $this->_foreach['fe_s']['iteration']++;
?>
                    
                    <li class="float-left">
                        <dl>
                            <dt class="border">
                                <a href="<?php echo url('app=goods&id=' . $this->_var['sgoods']['goods_id']. ''); ?>" target="_blank">
                                    <img src="<?php echo $this->_var['sgoods']['default_image']; ?>" />
                                </a>
                            </dt>
                            <dd class="desc mt10">
                                <a href="<?php echo url('app=goods&id=' . $this->_var['sgoods']['goods_id']. ''); ?>" target="_blank">
                                    <?php echo sub_str(htmlspecialchars($this->_var['sgoods']['goods_name']),50); ?>
                                </a>
                            </dd>
                            <dd class="mt10 J_GoodsEvaluation" data-score="<?php echo $this->_var['sgoods']['goods_evaluation']; ?>">
                                <span>
                                </span>
                            </dd>
                            <dd class="price mt10 w-full clearfix">
                                <strong>
                                    <?php echo price_format($this->_var['sgoods']['price']); ?>
                                </strong>
                                <em>
                                    <a href="<?php echo url('app=goods&id=' . $this->_var['sgoods']['goods_id']. '&act=saleslog'); ?>#module" target="_blank">
                                        售出<?php echo $this->_var['sgoods']['sales']; ?>
                                    </a>
                                    &nbsp;|&nbsp;
                                    <a href="<?php echo url('app=goods&id=' . $this->_var['sgoods']['goods_id']. '&act=comments'); ?>#module" target="_blank">
                                        评论<?php echo $this->_var['sgoods']['comments']; ?>
                                    </a>
                                </em>
                            </dd>
                        </dl>
                    </li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
            <?php echo $this->fetch('page.bottom.html'); ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery.plugins/raty/jquery.raty.js'; ?>" charset="utf-8"></script>
<script type="text/javascript">
//<!CDATA[
$(function(){
	$('.J_GoodsEvaluation').each(function(index, element) {
        $(this).raty({
    		readOnly: true,
            score: $(this).attr('data-score')
		});
    });
	
    $("select[ectype='order_by']").change(function(){
        var params = location.search.substr(1).split('&');
        var key    = 'order';
        var value  = this.value;
        var found  = false;
        for (var i = 0; i < params.length; i++)
        {
            param = params[i];
            arr   = param.split('=');
            pKey  = arr[0];
            if (pKey == 'page')
            {
                params[i] = 'page=1';
            }
            if (pKey == key)
            {
                params[i] = key + '=' + value;
                found = true;
            }
        }
        if (!found)
        {
            params.push(key + '=' + value);
        }
        location.assign(SITE_URL + '/index.php?' + params.join('&'));
    });
});
//]]>
</script>
<?php echo $this->fetch('footer.html'); ?>