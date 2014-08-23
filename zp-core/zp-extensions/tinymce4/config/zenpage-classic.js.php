<?php
/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
global $_zp_RTL_css;
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
// <!-- <![CDATA[
					tinymce.init({
					selector: "textarea.content,textarea.desc,textarea.extracontent",
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
									"advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
									"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
									"table contextmenu directionality emoticons template textcolor paste textcolor tinyzenpage"
					],
									toolbar1: "newdocument | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
									toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | inserttime preview | forecolor backcolor",
									toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft tinyzenpage",
									menubar: false,
									toolbar_items_size: 'small',
									setup: function(ed) {
									ed.on('change', function(e) {
									$('.dirty-check').addClass('dirty');
									});
									}
					});
// ]]> -->
</script>