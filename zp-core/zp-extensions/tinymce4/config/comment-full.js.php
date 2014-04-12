<?php
/**
 * The configuration functions for TinyMCE 4.x.
 *
 * Comment form plugin default light configuration
 */
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
// <!-- <![CDATA[
	tinymce.init({
		selector: "textarea.textarea_inputbox",
		language: "<?php echo $locale; ?>",
		menubar: false,
		relative_urls: false,
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace visualblocks code",
			"insertdatetime media table contextmenu",
			"emoticons paste"
		],
		content_css: "<?php echo getPlugin('tinymce4/config/content.css', true, FULLWEBPATH); ?>",
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview | forecolor backcolor emoticons | code"
	});
// ]]> -->
</script>
