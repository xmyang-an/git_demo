function fileQueued(file, progress_id)
{
	var $list = $( '#'+progress_id);
	$list.append( '<div id="' + file.id + '" class="item webuploader-upload-item">' +
        '<h4 class="info">' + file.name + '</h4>' +
        '<p class="state">等待上传...</p>' +
    '</div>' );
}

function uploadProgress(file, percentage)
{
	var $li = $( '#'+file.id ),
        $percent = $li.find('.progress .progress-bar');

    // 避免重复创建
    if ( !$percent.length ) {
        $percent = $('<div class="progress progress-striped active">' +
          '<div class="progress-bar" role="progressbar" style="width: 0%">' +
          '</div>' +
        '</div>').appendTo( $li ).find('.progress-bar');
    }

    $li.find('p.state').text(percentage * 100 + '% 上传中');

    $percent.css( 'width', percentage * 100 + '%' );
}

function uploadSuccess(file, serverData)
{
	add_uploadedfile(serverData.retval);//console.log(serverData);
	$( '#'+file.id ).addClass('upload-state-done');
	$( '#'+file.id ).find('p.state').text('完成');
}
function uploadError(file)
{
	var $li = $( '#'+file.id ),
        $error = $li.find('div.error');

    // 避免重复创建
    if ( !$error.length ) {
        $error = $('<div class="error"></div>').appendTo( $li );
    }

    $error.text('上传失败');
}
function uploadComplete(file) 
{
	$( '#'+file.id ).find('.progress').remove();
}
function uploadFinished(progress_id)
{
	var interval = setInterval(function(){
		var result = removeUploadProgress(progress_id);
        if(result){
			clearInterval(interval);
		}
	}, 2000);
}

function removeUploadProgress(progress_id)
{
	$('#'+progress_id).find('.webuploader-upload-item:visible:last').fadeOut();
	
	var allHide = true;
	$('#'+progress_id).find('.webuploader-upload-item').each(function(index, element) {
        if($(this).css('display') == 'block') {
			allHide = false;
		}
    });
	
	return allHide;
}