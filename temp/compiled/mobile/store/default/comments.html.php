<div class="comments">
<div class="clearfix"> 
  <p class="tips webkit-box flex-wrap pl5 pt10">
  	 <a class="<?php if (! $_GET['eval'] && ! $_GET['tip']): ?>active<?php endif; ?> gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. ''); ?>"><span>全部(<?php echo $this->_var['statistics']['total_count']; ?>)</span></a>
     <a class="<?php if ($_GET['eval'] == 4): ?>active<?php endif; ?> gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=4'); ?>"><span>有图(<?php echo $this->_var['statistics']['share_count']; ?>)</span></a>
     <a class="<?php if ($_GET['eval'] == 3): ?>active<?php endif; ?> gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=3'); ?>"><span>好评(<?php echo $this->_var['statistics']['good_count']; ?>)</span></a>
     <a class="<?php if ($_GET['eval'] == 2): ?>active<?php endif; ?> gray"  href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=2'); ?>"><span>中评(<?php echo $this->_var['statistics']['middle_count']; ?>)</span></a>
     <a  class="<?php if ($_GET['eval'] == 1): ?>active<?php endif; ?> gray" href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&eval=1'); ?>"><span>差评(<?php echo $this->_var['statistics']['bad_count']; ?>)</span></a>
     <?php $_from = $this->_var['eval_tips']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tip');if (count($_from)):
    foreach ($_from AS $this->_var['tip']):
?>
     <a class="<?php if ($_GET['tip'] == $this->_var['tip']['tip']): ?>active<?php endif; ?>" href="<?php echo url('app=goods&act=comments&id=' . $_GET['id']. '&tip='); ?><?php echo urlencode($this->_var['tip']['tip']); ?>"><span><?php echo htmlspecialchars($this->_var['tip']['tip']); ?>(<?php echo $this->_var['tip']['count']; ?>)</span></a>
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </p>
  
  <div class="list clearfix J_InfiniteList">
    <div class="infinite-result clearfix hidden"> </div>
    <div class="infinite-template">
      <div class="item">
      	<div class="tp">
            <div class="us-name clearfix">
              <p><img src="{1}" width="25" height="25" /></p>
              <p class="font">{2}  {3}{4}{5}{6}{7}{8}</p>
            </div>
            <div class="us-content"> 
            	<span class="mt10 mb10 block">{9}</span>
            </div>
			<div class="us-content"> 
            	{13}
            </div>
        </div>
        <div class="mp images">
        	<div class="imageList webkit-box flex-wrap" data-pswp-uid="{10}">
            	<figure class="each">
            		<div class="wrap"><a href="{1}"  data-size="{2}"><img src="{1}" /></a></div>
                    <figcaption style="display:none;">{9}</figcaption>
                </figure>
            </div>
        </div>
        <div class="bp">
        	<div class="col-size webkit-box pt10">
                <p class="flex1">{11}</p>
                <p>{12}</p>
             </div>
         </div>
      </div>
    </div>
    <div class="infinite-loading hidden"><ins class="vline vleft"></ins><span class="loading clearfix"><i></i><em>加载中...</em></span><ins class="vline vright"></ins></div>
    <div class="infinite-bottom f99 fs12 hidden"><ins class="vline vleft"></ins>已经到底了<ins class="vline vright"></ins></div>
    <div class="infinite-empty notice-empty hidden" style="background:#fff;"><i>&#xe715;</i>
      <p>没有符合条件的记录</p>
    </div>
  </div>
  
  <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){
	$('.J_InfiniteList').infinite({pageper: 8, params: <?php echo $this->_var['infiniteParams']; ?>, callback: function(data, page, target, TEMP){
			var html = '';
			$.each(data, function(k, comment) {
				var template = TEMP.clone(true);
				var items = '';

				$.each(comment.images, function(k1, image) {
					items += sprintf(template.find('.imageList').html(),image.url,image.data_size);
				});
				if(items) {
					template.find('.imageList').html(items);
					template.find('.imageList').addClass('gallery');
				} else template.find('.imageList').parents('.images').remove();
				
				html += sprintf(template.html(), comment.portrait, comment.anonymous > 0 ? "anonymous" : comment.buyer_name, comment.evaluation > 0 ? '<i class="psmb-icon-font f60">&#xe651;</i>' : '', comment.evaluation > 1 ? '<i class="psmb-icon-font f60">&#xe651;</i>' : '', comment.evaluation > 2 ? '<i class="psmb-icon-font f60">&#xe651;</i>' : '', comment.evaluation < 3 ? '<i class="psmb-icon-font gray">&#xe651;</i>' : '', comment.evaluation < 2 ? '<i class="psmb-icon-font gray">&#xe651;</i>' : '', comment.evaluation < 1 ? '<i class="psmb-icon-font gray">&#xe651;</i>' : '', comment.comment?comment.comment:'该用户未评价', comment.rec_id,comment.specification ? comment.specification : '',comment.evaluation_time,comment.reply_content?'<span class="mt10 mb10 block" style="color:#666;padding:10px;background: #f4f4f4;border-radius: 5px;"><span style="font-weight:600;">商家回复：</span>'+comment.reply_content+'<span/>':'');
			});
			target.find('.infinite-result').append(html).show();
			
			initPhotoSwipeFromDOM('.gallery');
		}
	});
});
</script> 
