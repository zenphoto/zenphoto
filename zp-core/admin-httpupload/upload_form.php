<?php
function upload_head() {
	$myfolder = WEBPATH.'/'.ZENFOLDER.'/'.basename(dirname(__FILE__));
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $myfolder; ?>/jquery.fileupload-ui.css">
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.fileupload.js"></script>
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.fileupload-ui.js"></script>
	<?php
}

function upload_form($uploadlimit) {
	global $upload_extensions, $_zp_current_admin_obj;
	$navigator_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
	if (strpos($navigator_user_agent, 'opera') !== false && strpos($navigator_user_agent, 'windows') !== false) {
		// Opera: Windows
		$sel = 0;
	} else if (strpos($navigator_user_agent, 'applewebki') !== false && strpos($navigator_user_agent, 'windows') !== false) {
		// Safari: Windows
		$sel = 1;
	} else if (strpos($navigator_user_agent, 'msie') !== false) {
		// Internet Explorer
		$sel = 0;
	} else {
		//	fully compliant browser
		$sel = 3;;
	}

	switch ($sel) {
		case 0:	//	no drag, no multiple select
			$usage = gettext('Click on the <em>Select files</em> button to use the file browser to select each file.');
			break;
		case 1:	//	drag but no multiple select
			$usage = gettext('You may choose a file by dragging it onto <em>Select files</em> button. Or you can click on the button and use your file browser.');
			break;
		case 2:	//	no drag, but multiple select
			$useage = gettext('Click on the <em>Select files</em> button to use the file browser to select files.');
			break;
		case 3:	//	drag and multiple select
			$usage = gettext('You may choose files by dragging them onto <em>Select files</em> button. Or you can click on the button and use your file browser.');
			break;
	}


	?>

	<form id="file_upload" action="uploader.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="file" multiple />
    <button>Upload</button>
    <div><?php echo gettext("Select files"); ?></div>
		<?php XSRFToken('upload');?>
		<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
		<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
		<input type="hidden" name="http_publishalbum" id="http_publishalbum" value="1" />
		<input type="hidden" name="http_albumtitle" id="http_albumtitle" value="" />
		<input type="hidden" name="http_folder" id="http_folder" value="/" />
	</form>
	<table id="files"></table>
	<p class="buttons" id="fileUploadbuttons" style="display: none;">
		<button id="start_uploads"><img src="images/pass.png" alt="" /><?php echo gettext("Upload"); ?></button>
		<button id="cancel_uploads" ><img src="images/fail.png" alt="" /><?php echo gettext("Cancel"); ?></button>
	</p>
	<br clear="all">
	<script type="text/javascript">
		var filecount = 0;
		var beforesendcount = 0;
		var uploadcount = 0;
		var uploaderror = false;

		$('#start_uploads').click(function () {
			$('#http_publishalbum').val($('#publishalbum').val());
			$('#http_albumtitle').val($('#albumtitle').val());
			$('#http_folder').val($('#folderdisplay').val());
			$('.file_upload_start button').click();
		});

		$('#cancel_uploads').click(function () {
			$('.file_upload_cancel button').click();
			filecount = 0;
			beforesendcount = 0;
			uploadcount = 0;
			uploaderror = false;
			$('#files').html('');
		});
		$(function () {
		    $('#file_upload').fileUploadUI({
						url: '<?php echo WEBPATH.'/'.ZENFOLDER.'/'.basename(dirname(__FILE__)); ?>/uploader.php',
		        uploadTable: $('#files'),
		        downloadTable: $('#files'),
		        buildUploadRow: function (files, index) {
							var rowCount = $('#files').attr('rows').length;
							if (filecount == 0 && rowCount > 0) {	//	clear out any error indicators
								$('#files').html('');
							}
							if ( typeof( window[ 'uploadfilelimit' ] ) != "undefined" ) {
								if ($('#files').attr('rows').length >= uploadfilelimit) {
									if (rowCount = uploadfilelimit) {
										alert('<?php echo gettext('You have exceeded the file upload limit'); ?>');
									}
									return null;
								}
							}
							filecount ++;
							return $('<tr><td class="file_upload_preview"><\/td>' +
		                '<td>' + files[index].name + '<\/td>' +
		                '<td class="file_upload_progress"><div><\/div><\/td>' +
		                '<td class="file_upload_start">' +
		                '<button class="ui-state-default ui-corner-all" title="Start Upload" style="display:none">' +
		                '<span class="ui-icon ui-icon-circle-arrow-e">Start Upload<\/span>' +
		                '<\/button><\/td>' +
		                '<td class="file_upload_cancel">' +
		                '<button class="ui-state-default ui-corner-all" title="Cancel">' +
		                '<span class="ui-icon ui-icon-cancel">Cancel<\/span>' +
		                '<\/button><\/td><\/tr>');
		        },
				    beforeSend: function (event, files, index, xhr, handler, callBack) {
							// Filter the filename extension for our test,
							var regexp = <?php
												$list = implode(')|(',$upload_extensions);
												echo '/\.('.$list.')$/i';
												?>;
							if (!regexp.test(files[index].name)) {
								handler.uploadRow.find('.file_upload_progress').html('<span style="color:red"><?php echo gettext('ONLY SUPPORTED FILETYPES ALLOWED!'); ?></span>');
								return;
							}

			        if (files[index].size > <?php echo $uploadlimit; ?>) {
								handler.uploadRow.find('.file_upload_progress').html('<span style="color:red"><?php echo gettext('FILE TOO BIG!'); ?></span>');
								return;
			        }
							handler.uploadRow.find('.file_upload_start button').click(callBack)
				    },
						onComplete: function (event, files, index, xhr, handler) {
							uploadcount++;
							if (uploadcount >= filecount && !uploaderror) {
								<?php
								if (zp_loggedin(ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									launchScript('admin-edit.php',['page=edit','subpage=1','tab=imageinfo','album='+encodeURIComponent($('#folderdisplay').val()),'uploaded=1','albumimagesort=id_desc']);
									<?php
								} else {
									?>
									launchScript('admin-upload.php',['uploaded=1']);
									<?php
								}
								?>
							}
							filecount = 0;
							beforesendcount = 0;
							uploadcount = 0;
							uploaderror = false;
						},
		        buildDownloadRow: function (file) {
							if (file.error) {
								uploaderror = true;
								return $('<tr><td><img src="images/fail.png" alt="" />' + file.name + '<\/td><\/tr>');
							} else {
								return $('<tr><td><img src="images/pass.png" alt="" />' + file.name + '<\/td><\/tr>');
							}
		        }
		    });
		});
	</script>
	<p class="notebox"><?php echo $usage; ?></p>
	<p id="uploadswitch"><?php echo gettext('Try the <a href="javascript:switchUploader(\'admin-upload.php?uploadtype=uploadify\');" >flash file upload</a>'); ?></p>
	<?php
}
?>