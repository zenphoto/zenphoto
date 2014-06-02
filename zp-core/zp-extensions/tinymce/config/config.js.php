<?php
/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * base configuration file
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);

if (isset($MCEcss)) {
	$MCEcss = getPlugin('tinymce/config/' . $MCEcss, true, true);
} else {
	$MCEcss = getPlugin('tinymce/config/content.css', true, true);
}

if (!getOption('tinymce_tinyzenpage')) {
	$MCEplugins = preg_replace('|\stinyzenpage|', '', $MCEplugins);
}
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce/jquery.tinymce.min.js"></script>
<script src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/dirtyforms/tinymce.js" type="text/javascript"></script>

<script type="text/javascript">
// <!-- <![CDATA[
					tinymce.init({
					selector: "<?php echo $MCEselector; ?>",
									language: "<?php echo $locale; ?>",
									relative_urls: false,
									content_css: "<?php echo $MCEcss; ?>",
<?php
if ($filehandler) {
	?>
						elements : "<?php echo $filehandler; ?>",
										file_browser_callback : <?php echo $filehandler; ?>,
	<?php
}
?>
					plugins: ["<?php echo $MCEplugins; ?>"],
<?php
if (isset($MCEspecial)) {
	echo $MCEspecial . ',';
}
if (isset($MCEskin)) {
	?>
						skin: "<?php echo $MCEskin; ?>",
	<?php
}
if (empty($MCEtoolbars)) {
	?>
						toolbar: false,
	<?php
} else {
	foreach ($MCEtoolbars as $key => $toolbar) {
		?>
							toolbar<?php if (count($MCEtoolbars) > 1) echo $key; ?>: "<?php echo $toolbar; ?>",
		<?php
	}
}
?>

					statusbar: <?php echo ($MCEstatusbar) ? 'true' : 'false'; ?>,
									menubar: <?php echo ($MCEmenubar) ? 'true' : 'false'; ?>,
									setup: function(editor) {
									editor.on('blur', function(ed, e) {
									form = $(editor.getContainer()).closest('form');
													if (editor.isDirty()) {
									$(form).addClass('tinyDirty');
													$('.dirtylistening').addClass('dirty'); //dirtyForms has problems with "dirtyignore"
									} else {
									$(form).removeClass('tinyDirty');
									}
									});
									}


					});
					// ]]> -->
</script>
