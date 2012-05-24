<?php
function upload_head() {
	return NULL;
}
function upload_extra($uploadlimit, $passedalbum) {
}

function upload_form($uploadlimit, $passedalbum) {
	?>
	<p class="errorbox">
		<?php echo gettext("No uploader plugin has been enabled."); ?>
	</p>
	<?php
}

?>
