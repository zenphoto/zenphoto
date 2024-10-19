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
<script src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce/tinymce.min.js"></script>
<script>
	tinymce.init({
		license_key: 'gpl',
		selector: "textarea.content,textarea.desc,textarea.extracontent,textarea.texteditor",
		promotion: false,
		language: "<?php echo $locale; ?>",
		entity_encoding: '<?php echo getOption('tinymce_entityencoding'); ?>',
		resize: true,
		<?php if(!empty(trim(strval(getOption('tinymce_entities'))))) { ?>
			entities: '<?php echo getOption('tinymce_entities'); ?>',
		<?php } ?>
		<?php if (getOption('tinymce_textfield-height')) { ?>
			min_height: <?php echo getOption('tinymce_textfield-height'); ?>,
		<?php } ?>
		<?php if (getOption('tinymce_browser-spellcheck')) { ?>
			browser_spellcheck: true,
		<?php } ?>
		<?php if (getOption('tinymce_browser-menu')) { ?>
			contextmenu: false,
		<?php } ?>
		directionality: "<?php echo $_zp_rtl_css ? 'rtl' : 'ltr'; ?>",
		relative_urls: false,
		image_advtab: true,
		image_caption: true,
		content_css: "<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tinymce/config/content.css",
		importcss_append: true,
		<?php if ($filehandler) { ?>
			file_picker_callback: <?php echo $filehandler; ?>,
		<?php } ?>
		toolbar_mode: 'sliding',
		plugins: [
			'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
			'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
			'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample', 'pagebreak', 'anchor', 'tinyzenpage'
		],
		menubar: false,
		toolbar: 'bold italic underline strikethrough | undo redo | accordion accordionremove | blocks | align numlist bullist | outdent indent | link image tinyzenpage | code fullscreen preview | pagebreak anchor codesample | charmap emoticons',
		setup: function(ed) {
			ed.on('change', function(e) {
				$('.dirty-check').addClass('dirty');
			});
		}
	});
</script>