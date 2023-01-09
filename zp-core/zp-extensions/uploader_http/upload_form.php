<?php
/**
 * 
 * @package zpcore\plugins\uploaderhttp
 */

function upload_head() {
	$myfolder = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/uploader_http';
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $myfolder; ?>/httpupload.css">
	<script src="<?php echo $myfolder; ?>/httpupload.js"></script>
	<?php
	return $myfolder . '/uploader.php';
}

function upload_extra($uploadlimit, $passedalbum) {

}

function upload_form($uploadlimit, $passedalbum) {
	global $_zp_current_admin_obj;

	XSRFToken('upload');
	?>
	<script>
		window.totalinputs = 5;
		function addUploadBoxes(num) {
			for (i = 0; i < num; i++) {
				jQuery('#uploadboxes').append('<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>');
				window.totalinputs++;
				if (window.totalinputs >= 50) {
					jQuery('#addUploadBoxes').toggle('slow');
					return;
				}
			}
		}
		function resetBoxes() {
			$('#uploadboxes').empty();
			addUploadBoxes(5);
		}
	</script>

	<input type="hidden" name="existingfolder" id="existingfolder" value="false" />
	<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
	<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
	<input type="hidden" name="processed" id="processed" value="1" />
	<input type="hidden" name="folder" id="folderslot" value="<?php echo html_encode($passedalbum); ?>" />
	<input type="hidden" name="albumtitle" id="albumtitleslot" value="" />
	<input type="hidden" name="publishalbum" id="publishalbumslot" value="" />
	<div id="uploadboxes">
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
	</div>
	
	<p id="addUploadBoxes"><a href="javascript:addUploadBoxes(5)" title="<?php echo gettext("Does not reload!"); ?>">+ <?php echo gettext("Add more upload boxes"); ?></a> <small>
			<?php echo gettext("(will not reload the page, but remember your upload limits!)"); ?></small></p>

	<p id="fileUploadbuttons" class="buttons" style="display: none;">
		<button type="submit" value="<?php echo gettext('Upload'); ?>" onclick="this.form.folder.value = this.form.folderdisplay.value;" class="button">
			<img src="images/pass.png" alt="" /><?php echo gettext('Upload'); ?>
		</button>
		<button type="button" value="<?php echo gettext('Cancel'); ?>" onclick="resetBoxes();" class="button">
			<img src="images/pass.png" alt="" /><?php echo gettext('Cancel'); ?>
		</button>
	</p>
	<br /><br class="clearall" />


	<?php
}
?>
