<?php
/**
 * The configuration functions for TinyMCE 4.x.
 *
 * Comment form plugin default light configuration
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
		menubar: false,
		relative_urls: false,
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code directionality",
			"insertdatetime media table contextmenu paste"
		],
		statusbar: false,
		content_css: "<?php echo getPlugin('tinymce4/config/content.css', true, FULLWEBPATH); ?>",
		toolbar: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code | ltr rtl"
	});
// ]]> -->
</script>
