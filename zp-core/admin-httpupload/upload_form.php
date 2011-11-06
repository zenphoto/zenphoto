<?php
function upload_head() {
	$myfolder = WEBPATH.'/'.ZENFOLDER.'/'.basename(dirname(__FILE__));
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo $myfolder; ?>/httpupload.css">
	<script type="text/javascript" src="<?php echo $myfolder; ?>/httpupload.js"></script>
		<script type="text/javascript">
			// <!-- <![CDATA[
			window.totalinputs = 5;
			// ]]> -->
		</script>
	<?php
	return 'action="'.$myfolder.'/uploader.php" enctype="multipart/form-data"';
}
function upload_extra($uploadlimit) {
}

function upload_form($uploadlimit) {
	global $_zp_current_admin_obj, $passedalbum;

	XSRFToken('upload');
	?>
	<input type="hidden" name="auth" id="auth" value="<?php echo $_zp_current_admin_obj->getPass(); ?>" />
	<input type="hidden" name="id" id="id" value="<?php echo $_zp_current_admin_obj->getID(); ?>" />
	<div id="uploadboxes" style="display: none;">
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>
		<div class="fileuploadbox"><input type="file" size="40" name="files[]" /></div>

		<div id="place" style="display: none;"></div>
		<!-- New boxes get inserted before this -->

		<div style="display:none">
		<!-- This is the template that others are copied from -->
		<div class="fileuploadbox" id="filetemplate" ><input type="file" size="40" name="files[]" value="x" /></div>
		</div>
		<p id="addUploadBoxes"><a href="javascript:addUploadBoxes('place','filetemplate',5)" title="<?php echo gettext("Doesn't reload!"); ?>">+ <?php echo gettext("Add more upload boxes"); ?></a> <small>
		<?php echo gettext("(won't reload the page, but remember your upload limits!)"); ?></small></p>
	</div>
		<p id="fileUploadbuttons" class="buttons"<?php if (!$passedalbum) echo ' style="display: none;"'; ?>>
			<button type="submit" value="<?php echo gettext('Upload'); ?>" onclick="this.form.folder.value = this.form.folderdisplay.value;" class="button">
				<img src="images/pass.png" alt="" /><?php echo gettext('Upload'); ?>
			</button>
		</p>
		<br /><br clear="all" />


	<?php
}

function showFields() {
	?>
	$('#uploadboxes').show();
	<?php
}
?>
