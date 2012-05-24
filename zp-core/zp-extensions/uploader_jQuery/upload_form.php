<?php
function upload_head() {
	$myfolder = WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_jQuery';
	?>
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.tmpl.min.js"></script>
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.iframe-transport.js"></script>
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.fileupload.js"></script>
	<script type="text/javascript" src="<?php echo $myfolder; ?>/jquery.fileupload-ui.js"></script>
	<link rel="stylesheet" href="<?php echo $myfolder; ?>/jquery.fileupload-ui.css">
	<?php
	return $myfolder.'/uploader.php';
}
function upload_extra($uploadlimit, $passedalbum) {
	global $_zp_current_admin_obj;
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		// application
		var upload_fail = false;
		$(function () {
			'use strict';

			// Initialize the jQuery File Upload widget:
			$('#fileupload').fileupload({
																	url: '<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/uploader_jQuery/uploader.php'?>'<?php
																	if ($uploadlimit) echo ",
																	maxFileSize:".$uploadlimit; ?>
																	});

			// Open download dialogs via iframes,
			// to prevent aborting current uploads:
			$('#fileupload .files').delegate(
																			'a:not([target^=_blank])',
																			'click',
																			function (e) {
																										e.preventDefault();
																										$('<iframe style="display:none;"></iframe>')
																												.prop('src', this.href)
																												.appendTo('body');
																										}
																			);

/*
			$('#fileupload').bind('fileuploadadd', function (e, data) {
																																upload_fail = false;
																																});


			$('#fileupload').bind('fileuploaddone', function (e, data) {
																																	alert('done');
																																	upload_fail = false;
																																	});
*/

			$('#fileupload').bind('fileuploadfail', function (e, data) {
																																	//alert('fail');
																																	upload_fail = true;
																																	});

			$('#fileupload').bind('fileuploadstop', function (e, data) {
																																	if (upload_fail) {
																																		//alert('upload failed');
																																		// clean up any globals since we are staying on the page
																																		upload_fail = false;
																																	} else {
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
																																	});

		});
		// ]]> -->
	</script>
	<div id="fileupload">
		<form action="uploader.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="existingfolder" id="existingfolder" value="false" />
			<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
			<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
			<input type="hidden" name="folder" id="folderslot" value="<?php echo html_encode($passedalbum); ?>" />
			<input type="hidden" name="albumtitle" id="albumtitleslot" value="" />
			<input type="hidden" name="publishalbum" id="publishalbumslot" value="" />
			<div class="fileupload-buttonbar">
				<label class="fileinput-button"> <span><?php echo gettext('Add files...'); ?>
					</span> <input type="file" name="files[]" multiple>
				</label>
				<span id="fileUploadbuttons">
					<button type="submit" class="start"><?php echo gettext('Start upload'); ?></button>
					<button type="reset" class="cancel"><?php echo gettext('Cancel upload'); ?></button>
				</span>
			</div>
		</form>
		<div class="fileupload-content">
			<table class="files"></table>
			<div class="fileupload-progressbar"></div>
		</div>
	</div>
	<script id="template-upload" type="text/x-jquery-tmpl">
		<tr class="template-upload{{if error}} ui-state-error{{/if}}">
			<td class="preview"></td>
			<td class="name">{{if name}}${name}{{else}}Untitled{{/if}}</td>
			<td class="size">${sizef}</td>
				{{if error}}
					<td class="error" colspan="2"><?php echo gettext('Error:'); ?>
					{{if error === 'maxFileSize'}}<?php echo gettext('File is too big'); ?>
					{{else error === 'minFileSize'}}<?php echo gettext('File is too small'); ?>
					{{else error === 'acceptFileTypes'}}<?php echo gettext('Filetype not allowed'); ?>
					{{else error === 'maxNumberOfFiles'}}<?php echo gettext('Max number of files exceeded'); ?>
					{{else}}${error}
				{{/if}}
			</td>
			{{else}}
				<td class="progress"><div></div></td>
				<td class="start" style="display:none"><button><?php echo gettext('Start'); ?></button></td>
			{{/if}}
				<td class="cancel"><button><?php echo gettext('Cancel'); ?></button></td>
		</tr>
	</script>
	<script id="template-download" type="text/x-jquery-tmpl">
		{{if error !== 'emptyResult'}}
		<tr class="template-download{{if error}}} ui-state-error{{/if}}">
			{{if error}}
				<td></td>
				<td class="name">${name}</td>
				<td class="size">${sizef}</td>
				<td class="error" colspan="2"><?php echo gettext('Error:'); ?>
					{{if error === 1}}<?php echo gettext('File exceeds upload_max_filesize (php.ini directive)'); ?>
					{{else error === 2}}<?php echo gettext('File exceeds MAX_FILE_SIZE (HTML form directive)'); ?>
					{{else error === 3}}<?php echo gettext('File was only partially uploaded'); ?>
					{{else error === 4}}<?php echo gettext('No File was uploaded'); ?>
					{{else error === 5}}<?php echo gettext('Missing a temporary folder'); ?>
					{{else error === 6}}<?php echo gettext('Failed to write file to disk'); ?>
					{{else error === 7}}<?php echo gettext('File upload stopped by extension'); ?>
					{{else error === 'maxFileSize'}}<?php echo gettext('File is too big'); ?>
					{{else error === 'minFileSize'}}<?php echo gettext('File is too small'); ?>
					{{else error === 'acceptFileTypes'}}<?php echo gettext('Filetype not allowed'); ?>
					{{else error === 'maxNumberOfFiles'}}<?php echo gettext('Max number of files exceeded'); ?>
					{{else error === 'uploadedBytes'}}<?php echo gettext('Uploaded bytes exceed file size'); ?>
					{{else error === 'emptyResult'}}<?php echo gettext('Empty file upload result'); ?>
					{{else}}${error}
					{{/if}}
				</td>
				<td class="delete">
					<button data-type="${delete_type}" data-url="${delete_url}">Delete</button>
 				</td>
			{{else}}
				<td class="preview">
					{{if thumbnail_url}}
						<a href="${url}" target="_blank"><img src="${thumbnail_url}"></a>
					{{/if}}
				</td>
				<td class="name">
					<a href="${url}"{{if thumbnail_url}} target="_blank"{{/if}}>${name}</a>
				</td>
				<td class="size">${sizef}</td>
				<td colspan="2"></td>
				<td class="delete"><img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/pass.png" /></td>
			{{/if}}
		</tr>
	{{/if}}
	</script>
	<?php
}
function upload_form($uploadlimit, $passedalbum) {
}

?>