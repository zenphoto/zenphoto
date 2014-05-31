<?php
/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * base configuration file
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
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
<?php
if (isset($MCEcss)) {
	?>
						content_css: "<?php echo $MCEcss; ?>",
	<?php
}
?>
					setup: function(editor) {
					editor.on('blur', function(ed, e) {
					if (editor.isDirty()) {
					$('.dirty-check').addClass('dirty');
					}
					});
					}
					});
					// ]]> -->
</script>
