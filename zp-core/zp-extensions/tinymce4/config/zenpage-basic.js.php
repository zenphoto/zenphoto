<?php
/**
 * The configuration functions for TinyMCE 4.x.
 *
 * Zenphoto plugin default light configuration
 */
/**
 * Filter used by "file manager" plugins to attach themselves to tinyMCE.
 *
 * @package filters
 * @subpackage zenpage
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
global $_zp_rtl_css;
?>
<script src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script>
	tinymce.init({
		selector: "textarea.content,textarea.desc,textarea.extracontent,textarea.texteditor",
		language: "<?php echo $locale; ?>",
		entity_encoding: '<?php echo getOption('tinymce4_entityencoding'); ?>',
		<?php if(!empty(trim(strval(getOption('tinymce4_entities'))))) { ?>
			entities: '<?php echo getOption('tinymce4_entities'); ?>',
		<?php } ?>
		<?php if (getOption('tinymce4_textfield-height')) { ?>
			min_height: <?php echo getOption('tinymce4_textfield-height'); ?>,
		<?php } ?>
		<?php if (getOption('tinymce4_browser-spellcheck')) { ?>
		browser_spellcheck: true,
		<?php } ?>
		<?php if (getOption('tinymce4_browser-menu')) { ?>
		contextmenu: false,
		<?php } ?>
		directionality: "<?php echo $_zp_rtl_css ? 'rtl' : 'ltr'; ?>",
		relative_urls: false,
		image_advtab: true,
		content_css: "<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tinymce4/config/content.css",
<?php
if ($filehandler) {
	?>
		elements : "<?php echo $filehandler; ?>",
		file_browser_callback : <?php echo $filehandler; ?>,
	<?php
}
?>
<?php if (getOption('tinymce4_browser-menu')) { ?>
		plugins: [
			"advlist autolink lists link image charmap print preview anchor pagebreak",
			"searchreplace visualblocks code fullscreen directionality",
			"insertdatetime media table paste textpattern imagetools tinyzenpage"
		],
<?php } else { ?>
		plugins: [
			"advlist autolink lists link image charmap print preview anchor pagebreak",
			"searchreplace visualblocks code fullscreen directionality",
			"insertdatetime media table paste contextmenu textpattern imagetools tinyzenpage"
		],
<?php } ?>
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image tinyzenpage pagebreak | code fullscreen | ltr rtl",
		setup: function(ed) {
			ed.on('change', function(e) {
				$('.dirty-check').addClass('dirty');
			});
		}
	});
</script>
