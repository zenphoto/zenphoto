<?php
/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
// <!-- <![CDATA[
	tinymce.init({
		selector: "textarea.textarea_inputbox",
		language: "<?php echo $locale; ?>",
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
		skin: "tundora",
		content_css: "<?php echo FULLWEBPATH . '/' . THEMEFOLDER . '/' . basename(dirname(dirname(dirname(__FILE__)))); ?>/tinymce4/config/dark_content.css",
	});
// ]]> -->
</script>