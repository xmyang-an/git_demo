$(function(){
	
	$('.J_Cart').submit(function(){
		if($('.J_SelectGoods:checked').length == 0)
		{
			alert('您尚未选中任何商品');
		}
		else
		{
			$(this).submit();
		}
		return false;
	});
	
	// 页面加载完就执行（只在购物车页面需要）
	if($('#page-cart').length > 0) {
		
		// 设置新的总金额
		showCartAmountBySelected();
	}
	
	// 全选
	$('.J_SelectAll').click(function()
	{ 
		$('.J_SelectAll').prop("checked", $(this).prop('checked'));
		$('.J_SelectStoreAll').prop("checked", $(this).prop('checked'));
		$('.J_GoodsEach').find('.J_SelectGoods').prop('checked', $(this).prop('checked'));
		
		// 设置新的总金额
		showCartAmountBySelected();
	});
	
	// 店铺全选
	$('.J_SelectStoreAll').click(function() {
		$('.J_Store-' + $(this).val()).find('.J_SelectGoods').prop('checked', $(this).prop('checked'));
		
		if($(this).prop('checked') == true)
		{
			// 全选判断
			if($('.J_SelectStoreAll').not('input:checked').length == 0) {
				$('.J_SelectAll').prop("checked", true);
			}
		}
		else
		{
			// 全选判断
			if($('.J_SelectStoreAll').not('input:checked').length != 0) {
				$('.J_SelectAll').prop("checked", false);
			}
		}
		
		// 设置新的总金额
		showCartAmountBySelected();
	});
	
	// 点击某个商品，改变全选和店铺全选的选中状态
	$('.J_SelectGoods').click(function(){
		$(this).prop("checked", $(this).prop("checked"));
		
		// 店铺全选判断
		if($('.J_Store-' + $(this).attr('store_id')).find('.J_SelectGoods').not('input:checked').length == 0) {
			$('.J_Store-' + $(this).attr('store_id')).find('.J_SelectStoreAll').prop('checked', true);
		} else {
			$('.J_Store-' + $(this).attr('store_id')).find('.J_SelectStoreAll').prop('checked', false);
		}
		
		// 全选判断
		if($('.J_SelectGoods').not("input:checked").length == 0) {
			$('.J_SelectAll').prop("checked", true);
		}
		else
		{
			$('.J_SelectAll').prop("checked", false);
		}
		
		// 设置新的总金额
		showCartAmountBySelected();
	});
	
	
	$(".J_Batch a").click(function(){
		var name = this.name;
		var checked = 0;
		$('.J_SelectGoods').each(function(){
			if($(this).prop("checked")==true){
				srg = $(this).val().split(":");
				if(name=="batch_del"){
					drop_cart_item(srg[0], srg[1]);
				} else {
					batch_move_favorite(srg[0], srg[1], srg[2],checked==0);
				}
				checked++;
			}
		});
		if(!checked) {
			alert('你未选择任何项');
		}
		
		// 设置新的总金额
		showCartAmountBySelected();
	});
});

// 显示满折满减的差额
function showFullPerferPlusBySelected()
{
	var allDecrease = 0;
	var cartAllAmount = $('.J_CartAllAmount').html().replace(PRICE_FORMAT.replace('%s',''), '');

	$('.J_SelectStoreAll').each(function(index, element) {
		var o = $(this).parents('.J_Store-'+$(this).attr('id'));
		if(o.find('.J_FullPerferAmount').length > 0) {
			var fullPerferAmount = parseFloat(o.find('.J_FullPerferAmount').attr('data-value'));
			var selectStoreAmount = 0;
			
			o.find('.J_GoodsEach').each(function(index, element) {
				if($(this).find('.J_SelectGoods').prop('checked') == true) {
					selectStoreAmount += parseFloat($(this).find('.J_GetSubtotal').attr('price'));
				}
			});
			//alert(fullPerferAmount + "-" + selectStoreAmount + "=" + (fullPerferAmount - selectStoreAmount));
			if(selectStoreAmount < fullPerferAmount) {
				var plus = number_format((fullPerferAmount - selectStoreAmount).toFixed(2), 2);
				o.find('.J_FullPerferPlus').html(',还差'+plus+'元');
			} else {
				o.find('.J_FullPerferPlus').html('');
				
				// 在订单总价后显示节省的满优惠金额
				var fullPerferDetail = eval("("+(o.find('.J_FullPerferAmount').attr('data-detail'))+")");
				if(fullPerferDetail.discount != undefined && (fullPerferDetail.discount >= 0 && fullPerferDetail.discount <=10)) {
					decrease = (selectStoreAmount * (10 - fullPerferDetail.discount) * 0.1).toFixed(2);
				}
				else if(fullPerferDetail.decrease != undefined && (fullPerferDetail.decrease <= selectStoreAmount)) {
					decrease = fullPerferDetail.decrease;
				}
				allDecrease += parseFloat(decrease);
				
				$('.J_CartAllAmount').html(price_format((cartAllAmount-allDecrease).toFixed(2)) + "<s class='fs12'>（已优惠"+number_format(allDecrease, 2)+"）</s>");
			}
		}
	});
}

function showCartAmountBySelected()
{
	var cartAllAmount = 0;
	$('.J_SelectGoods').each(function(){
		if($(this).prop('checked') == true) {
			// 某个规格商品的小计
			cartAllAmount += parseFloat($(this).parents('.J_GoodsEach').find('.J_GetSubtotal').attr('price'));	
		}
	});
	if(cartAllAmount > 0){ //购物车不为空的时候,否则报js错误
		$('.J_CartAllAmount').html(price_format(cartAllAmount.toFixed(2)));
		showFullPerferPlusBySelected();
	}
}

function drop_cart_item(store_id, rec_id){
    var tr = $('.J_CartItem-' + rec_id);
    $.getJSON(REAL_SITE_URL+'/index.php?app=cart&act=drop&rec_id=' + rec_id, function(result){
        if(result.done){
            //删除成功
            if(result.retval.cart.quantity == 0){
                window.location.reload();    //刷新
            }
            else{

				if(tr.parent('.store-each').find('.goods-each').length==1){
					tr.parent('.store-each').remove();
				}
				tr.remove();        //移除
				
				$('.J_C_T_GoodsKinds').html(result.retval.cart.kinds);
				$('.J_C_T_Amount').html(price_format(result.retval.cart.amount));
            }
			
			// 设置新的总金额
			showCartAmountBySelected();
        }
    });
}
// 批量收藏，为了避免弹出多个确认框
function batch_move_favorite(store_id,rec_id,goods_id,alt) {
	$.getJSON(REAL_SITE_URL+'/index.php?app=my_favorite&act=add&type=goods&item_id=' + goods_id + '&ajax=1', function(result){
        if(result.done){
           if(alt){ // 批量收藏的时候，只弹出一次确认对话框
			   alert(result.msg);
		   }
        }
        else{
            alert(result.msg);
        }

    });
}

function move_favorite(store_id, rec_id, goods_id){
    $.getJSON(REAL_SITE_URL+'/index.php?app=my_favorite&act=add&type=goods&item_id=' + goods_id + '&ajax=1', function(result){
        //没有做收藏后的处理，比如从购物车移除
        if(result.done){
            alert(result.msg);
        }
        else{
            alert(result.msg);
        }

    });
}
function change_quantity(store_id, rec_id, spec_id, input){
    //暂存为局部变量，否则如果用户输入过快有可能造成前后值不一致的问题
    var _v = input.value;
	if(_v < 1 || isNaN(_v)) {alert(lang.invalid_quantity); $(input).val($(input).attr('orig'));return false}
	
    $.getJSON(REAL_SITE_URL+'/index.php?app=cart&act=update&spec_id=' + spec_id + '&quantity=' + _v, function(result){
        if(result.done){
            //更新成功
            $(input).attr('changed', _v);
			
	    	$('.J_ItemPrice-' + rec_id).html(price_format(result.retval.price));				
	    	$('.J_ItemQuantity-' + rec_id).html(result.retval.quantity);
	    	$('.J_ItemSubtotal-' + rec_id).html(price_format(result.retval.subtotal));
			$('.J_ItemSubtotal-' + rec_id).attr('price', number_format(result.retval.subtotal, 2));
	    	$('.J_C_T_GoodsKinds').html(result.retval.cart.kinds);
			$('.J_C_T_Amount').html(price_format(result.retval.cart.amount));
			
			// 设置新的总金额
			showCartAmountBySelected();
        }
        else{
            //更新失败
            alert(result.msg);
            $(input).val($(input).attr('changed'));
        }
    });
}
function decrease_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    if(orig > 1){
        item.val(orig - 1);
        item.keyup();
    }
}
function add_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    item.val(orig + 1);
    item.keyup();
}
