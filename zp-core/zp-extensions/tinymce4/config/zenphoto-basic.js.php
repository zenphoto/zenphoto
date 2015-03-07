<?php
/**
 * The configuration functions for TinyMCE 4.x.
 *
 * Zenphoto plugin default light configuration
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
global $_zp_RTL_css;
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
// <!-- <![CDATA[
					tinymce.init({
					selector: "textarea.texteditor",
									language: "<?php echo $locale; ?>",
									directionality: "<?php echo $_zp_RTL_css ? 'rtl' : 'ltr'; ?>",
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
					plugins: [
									"advlist autolink lists link image charmap print preview anchor",
									"searchreplace visualblocks code fullscreen directionality",
									"insertdatetime media table contextmenu paste tinyzenpage"
					],
									toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | tinyzenpage | ltr rtl",
									setup: function(ed) {
									ed.on('change', function(e) {
									$('.dirty-check').addClass('dirty');
									});
									}
					});
// ]]> -->
</script>
