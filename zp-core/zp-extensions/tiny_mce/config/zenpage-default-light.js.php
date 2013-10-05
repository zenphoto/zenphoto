<?php
/**
 * The configuration functions for TinyMCE with Ajax File Manager.
 *
 * Zenpage plugin default light configuration
 */
$filehandler = zp_apply_filter('tinymce_zenpage_config', NULL);
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	// <!-- <![CDATA[
	tinyMCE.init({
	mode : "textareas",
					editor_selector: /(content|extracontent|desc)/,
					language: "<?php echo $locale; ?>",
<?php
if ($filehandler) {
	?>
		elements : "<?php echo $filehandler; ?>",
						file_browser_callback : "<?php echo $filehandler; ?>",
	<?php
}
?>
	theme : "advanced",
					plugins : "pagebreak,style,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,tinyzenpage",
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,|,bullist,numlist,|,outdent,indent,blockquote",
					theme_advanced_buttons2 : "undo,redo,|,link,unlink,anchor,image,cleanup,help,code,fullscreen,|,pagebreak,tinyzenpage",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					theme_advanced_resize_horizontal : false,
					paste_use_dialog : true,
					paste_create_paragraphs : false,
					paste_create_linebreaks : false,
					paste_auto_cleanup_on_paste : true,
					apply_source_formatting : true,
					force_br_newlines : false,
					force_p_newlines : true,
					relative_urls : false,
					document_base_url : "<?php echo WEBPATH . "/"; ?>",
					convert_urls : false,
					entity_encoding: "raw",
					extended_valid_elements : "iframe[src|width|height|class|id|type|frameborder]",
					content_css: "<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/tiny_mce/config/content.css",
					setup : function(ed) {
	ed.onInit.add(function(ed){
	$('#mce_fullscreen_container').css('background', '#FAFAFA');
	});
	}
	});
					function toggleEditor(id) {
					if (!tinyMCE.get(id))
									tinyMCE.execCommand('mceAddControl', false, id);
									else
									tinyMCE.execCommand('mceRemoveControl', false, id);
					}
// ]]> -->
</script>