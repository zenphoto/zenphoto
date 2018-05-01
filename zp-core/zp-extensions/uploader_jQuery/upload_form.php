<?php

/**
 * @package plugins/uploader_jQuery
 */
function upload_head() {
	$myfolder = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery/';
	?>
	<!-- Force latest IE rendering engine or ChromeFrame if installed -->
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $myfolder; ?>css/blueimp-gallery.min.css">
	<link rel="stylesheet" href="<?php echo $myfolder; ?>css/jquery.fileupload.css">
	<link rel="stylesheet" href="<?php echo $myfolder; ?>css/jquery.fileupload-ui.css">

	<?php
	return $myfolder . '/uploader.php';
}

function upload_extra($uploadlimit, $passedalbum) {
	global $_zp_current_admin_obj;

	$myfolder = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_jQuery/';
	?>

	<div>
		<!-- The file upload form used as target for the file upload widget -->
		<form id="fileupload" action="<?php echo $myfolder; ?>xxserver/php/index.php" method="POST" enctype="multipart/form-data">

			<noscript><?php echo gettext('This uploader requires browser javaScript support.'); ?></noscript>

			<!-- ZenPhoto20 needed parameters -->
			<input type="hidden" name="existingfolder" id="existingfolder" value="false" />
			<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
			<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
			<input type="hidden" name="folder" id="folderslot" value="<?php echo html_encode($passedalbum); ?>" />
			<input type="hidden" name="albumtitle" id="albumtitleslot" value="" />
			<input type="hidden" name="publishalbum" id="publishalbumslot" value="" />

			<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
			<div class="row fileupload-buttonbar">
				<div class="col-lg-7">
					<!-- The fileinput-button span is used to style the file input field as button -->
					<span class="btn btn-success fileinput-button">
						<i class="glyphicon glyphicon-plus"></i>
						<span><?php echo gettext('Add files...'); ?></span>
						<input type="file" name="files[]" multiple>
					</span>
					<span class="fileUploadActions">
						<button type="submit" class="btn btn-primary start">
							<i class="glyphicon glyphicon-upload"></i>
							<span><?php echo gettext('Start upload'); ?></span>
						</button>
						<button type="reset" class="btn btn-warning cancel">
							<i class="glyphicon glyphicon-ban-circle"></i>
							<span><?php echo gettext('Cancel upload'); ?></span>
						</button>
						<!--
						<button type="button" class="btn btn-danger delete">
							<i class="glyphicon glyphicon-trash"></i>
							<span><?php echo gettext('Delete'); ?></span>
						</button>
						<input type="checkbox" class="toggle">
						-->
					</span>
					<!-- The global file processing state -->
					<span class="fileupload-process"></span>
				</div>
				<!-- The global progress state -->
				<div class="col-lg-5 fileupload-progress fade">
					<!-- The global progress bar -->
					<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<div class="progress-bar progress-bar-success" style="width:0%;"></div>
					</div>
					<!-- The extended global progress state -->
					<div class="progress-extended">&nbsp;</div>
				</div>
			</div>
			<!-- The table listing the files available for upload/download -->
			<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
		</form>

	</div>
	<!-- The blueimp Gallery widget -->
	<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">‹</a>
		<a class="next">›</a>
		<a class="close">×</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>
	</div>
	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
		<td>
		<span class="preview"></span>
		</td>
		<td>
		<p class="name">{%=file.name%}</p>
		<strong class="error text-danger"></strong>
		</td>
		<td>
		<p class="size"><?php echo gettext('Processing...'); ?></p>
		<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
		</td>
		<td>
		{% if (!i && !o.options.autoUpload) { %}
		<button class="btn btn-primary start" disabled>
		<i class="glyphicon glyphicon-upload"></i>
		<span>Start</span>
		</button>
		{% } %}
		{% if (!i) { %}
		<button class="btn btn-warning cancel">
		<i class="glyphicon glyphicon-ban-circle"></i>
		<span><?php echo gettext('Cancel'); ?></span>
		</button>
		{% } %}
		</td>
		</tr>
		{% } %}
	</script>
	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
		<td>
		<span class="preview">
		{% if (file.thumbnailUrl) { %}
		<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
		{% } %}
		</span>
		</td>
		<td>
		<p class="name">
		{% if (file.url) { %}
		<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
		{% } else { %}
		<span>{%=file.name%}</span>
		{% } %}
		</p>
		{% if (file.error) { %}
		<div><span class="label label-danger"><?php echo gettext('Error'); ?></span> {%=file.error%}</div>
		{% } %}
		</td>
		<td>
		<span class="size">{%=o.formatFileSize(file.size)%}</span>
		</td>
		<td>
		{% if (file.deleteUrl) { %}
		<button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
		<!--
		<i class="glyphicon glyphicon-trash"></i>
		<span><?php echo gettext('Delete'); ?></span>
		</button>
		<input type="checkbox" name="delete" value="1" class="toggle">
		-->
		{% } else { %}
		<button class="btn btn-warning cancel">
		<i class="glyphicon glyphicon-ban-circle"></i>
		<span><?php echo gettext('Cancel'); ?></span>
		</button>
		{% } %}
		</td>
		</tr>
		{% } %}
	</script>

	<script src ="<?php echo $myfolder; ?>js/tmpl.min.js"></script>
	<script src="<?php echo $myfolder; ?>js/load-image.all.min.js"></script>
	<script src="<?php echo $myfolder; ?>js/canvas-to-blob.min.js"></script>
	<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/bootstrap/bootstrap.min.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.blueimp-gallery.min.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.iframe-transport.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-process.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-image.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-audio.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-video.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-validate.js"></script>
	<script src="<?php echo $myfolder; ?>js/jquery.fileupload-ui.js"></script>
	<script src="<?php echo $myfolder; ?>js/main.js"></script>
	<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
	<!--[if (gte IE 8)&(lt IE 10)]>
	<script src="<?php echo $myfolder; ?>js/cors/jquery.xdr-transport.js"></script>
	<![endif]-->

	<script type="text/javascript">
		var upload_fail = false;
		$('#fileupload')
						.bind('fileuploadfail', function (e, data) {
							//alert('fail');
							upload_fail = true;
						})
						.bind('fileuploadstop', function (e, data) {
							//alert('stop');
							if (upload_fail) {
								//alert('upload failed');
								// clean up any globals since we are staying on the page
								upload_fail = false;
							} else {

	<?php
	if (zp_loggedin(ALBUM_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS)) {
		?>
									launchScript('admin-edit.php', ['page=edit', 'subpage=1', 'tab=imageinfo', 'album=' + encodeURIComponent($('#folderdisplay').val()), 'uploaded=1', 'albumimagesort=id_desc']);
		<?php
	} else {
		?>
									launchScript('admin-upload.php', ['uploaded=1']);
		<?php
	}
	?>
							}
						});

	</script>
	<?php
}

function upload_form($uploadlimit, $passedalbum) {

}
?>