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
		skin: "tundora",
		selector: "textarea.textarea_inputbox",
		language: "<?php echo $locale; ?>",
		relative_urls: false,
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"emoticons template paste tinyzenpage"
		],
		content_css: "<?php echo FULLWEBPATH . '/' . THEMEFOLDER; ?>/default/tinymce4/config/dark_content.css",
		toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "print preview media | forecolor backcolor emoticons | code | fullscreen",
	});
// ]]> -->
</script>