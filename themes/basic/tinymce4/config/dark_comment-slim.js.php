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
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code",
			"insertdatetime media table contextmenu paste"
		],
		statusbar: false,
		toolbar: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code"
						skin: "tundora", content_css: "<?php echo FULLWEBPATH . '/' . THEMEFOLDER . '/' . basename(dirname(dirname(dirname(__FILE__)))); ?>/tinymce4/config/dark_content.css",
	});
// ]]> -->
</script>
