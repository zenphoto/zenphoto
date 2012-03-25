<?php
/**
 * The configuration functions for TinyMCE with Ajax File Manager.
 *
 * Zenpage plugin default light configuration
 */
?>
	<script type="text/javascript" src="../tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript">
	// <!-- <![CDATA[
		tinyMCE.init({
			mode : "textareas",
			editor_selector: /(content|extracontent|desc)/,
			language: "<?php echo $locale; ?>",
			elements : "ajaxfilemanager",
			file_browser_callback : "ajaxfilemanager",
			theme : "advanced",
			plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,tinyzenpage",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "save,newdocument,|,visualchars,nonbreaking,xhtmlxtras,templatecut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,fullscreen",
			theme_advanced_buttons3 : "insertdate,inserttime,preview,|,forecolor,backcolor,|,tablecontrols,|,hr,removeformat",
			theme_advanced_buttons4 : "visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreeninsertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,insertlayer,tinyzenpage",
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
			document_base_url : "<?php echo WEBPATH."/"; ?>",
			convert_urls : false,
			entity_encoding: "raw",
			extended_valid_elements : "iframe[src|width|height|class|id|type|frameborder]",
			content_css: "<?php echo FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/tiny_mce/config/content.css",
			setup : function(ed) {
				ed.onInit.add(function(ed){
				$('#mce_fullscreen_container').css('background','#FAFAFA');
				});
			}
		});

		function ajaxfilemanager(field_name, url, type, win) {
			<?php	echo 'var ajaxfilemanagerurl="'.FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php?editor=tinymce&XSRFToken='.getXSRFToken('ajaxfilemanager').'";'; ?>
			switch (type) {
				case "image":
					ajaxfilemanagerurl += "&amp;type=img&amp;language=<?php echo $locale; ?>";
					break;
				case "media":
					ajaxfilemanagerurl += "&amp;type=media&amp;language=<?php echo $locale; ?>";
					break;
				case "flash": //for older versions of tinymce
					ajaxfilemanagerurl += "&amp;type=media&amp;language=<?php echo $locale; ?>";
					break;
				case "file":
					ajaxfilemanagerurl += "&amp;type=files&amp;language=<?php echo $locale; ?>";
					break;
				default:
					return false;
			}
				tinyMCE.activeEditor.windowManager.open({
			  file : ajaxfilemanagerurl,
			  input : field_name,
			  width : 750,
			  height : 500,
			  resizable : "yes",
			  inline : "yes",
			  close_previous: "yes"
			},{
			  window: win,
			  input: field_name
		 	});
		}

 function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}
 	// ]]> -->
	</script>