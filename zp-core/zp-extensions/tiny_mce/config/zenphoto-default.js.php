<?php
/**
 * The configuration functions for TinyMCE with Ajax File Manager.
 *
 * Zenphoto plugin default light configuration 
 */ 
?> 
	<script language="javascript" type="text/javascript" src="<?php echo WEBPATH ."/" . ZENFOLDER .'/'. PLUGIN_FOLDER; ?>/tiny_mce/tiny_mce.js"></script>
	<script language="javascript" type="text/javascript">
		// <!-- <![CDATA[
		tinyMCE.init({
			mode : "specific_textareas",
			theme : "advanced",
			language: "<?php echo $locale; ?>",
		  editor_selector: "texteditor", // to enable TinyMCE on album and image customdata set to /(texteditor|texteditor_albumcustomdata|texteditor_imagecustomdata)/
		  plugins : "fullscreen,inlinepopups",
		  theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist",
		  theme_advanced_buttons2 : "undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
			theme_advanced_buttons3 : "",
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
			forced_root_block: "",
			force_p_newlines : true,	
			relative_urls : false,
			document_base_url : "<?php echo WEBPATH."/"; ?>",
			convert_urls : false,
			entity_encoding: "raw",
			content_css: "<?php echo FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/tiny_mce/config/content.css"
		});
	// ]]> -->
	</script>
