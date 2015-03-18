<?php
/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
 global $_zp_RTL_css;
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
// <!-- <![CDATA[
	tinymce.init({
		selector: "textarea.textarea_inputbox,textarea.texteditor_comments",
		language: "<?php echo $locale; ?>",
		directionality: "<?php echo $_zp_RTL_css ? 'rtl' : 'ltr'; ?>",
		relative_urls: false,
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime save table contextmenu directionality",
			"emoticons paste"
		],
		menubar: "edit insert view format tools",
		toolbar: false,
		statusbar: false,
		content_css: "<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tinymce4/config/content.css",
	});
// ]]> -->
</script>