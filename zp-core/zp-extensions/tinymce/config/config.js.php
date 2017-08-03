<?php
/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * base configuration file, included by all TinyMCE configuration files
 *
 * Note:
 *
 * The following variables are presumed to have been set up by the specific configuration
 * file before including this script:
 *
 * <ul>
 * 	<li>$MCEselector: the class(es) upon which tinyMCE will activate</li>
 * 	<li>$MCEplugins: the list of plugins to include in the configuration</li>
 * 	<li>$MCEtoolbars: toolbar(s) for the configuration</li>
 * 	<li>$MCEstatusbar: Status to true for a status bar, false for none</li>
 * 	<li>$MCEmenubar: Status to true for a status bar, false for none</li>
 * </ul>
 *
 * And the following variables are optional, if set they will be used, otherwise default
 * settings will be selected:
 *
 * <ul>
 * 	<li>$MCEdirection: set to "rtl" for right-to-left text flow</li>
 * 	<li>$MCEspecial: used to insert arbitrary initialization parameters such as styles </li>
 * 	<li>$MCEskin: set to the override the default tinyMCE skin</li>
 * 	<li>$MCEcss: css file to be used by tinyMce</li>
 * 	<li>$MCEimage_advtab: set to <var>false</var> to disable the advanced image tab on the image insert popup (<i>style</i>, <i>borders</i>, etc.)</li>
 * </ul>
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
$filehandler = zp_apply_filter('tinymce_config', NULL);

if ($MCEcss) {
	$MCEcss = getPlugin('tinymce/config/' . $MCEcss, true, true);
} else {
	$MCEcss = getPlugin('tinymce/config/content.css', true, true);
}
global $_zp_RTL_css;
if ($MCEdirection == NULL) {
	if ($_zp_RTL_css) {
		$MCEdirection = 'rtl';
	} else {
		if (getOption('tiny_mce_rtl_override')) {
			$MCEdirection = 'rtl';
		}
	}
}
$MCEplugins = preg_replace('|\stinyzenpage|', '', $MCEplugins);
?>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="<?php echo WEBPATH . "/" . ZENFOLDER . "/" . PLUGIN_FOLDER; ?>/tinymce/jquery.tinymce.min.js"></script>
<?php
if (OFFSET_PATH && getOption('dirtyform_enable') > 1) {
	?>
	<script src="<?php echo WEBPATH . "/" . ZENFOLDER; ?>/js/dirtyforms/jquery.dirtyforms.helpers.tinymce.min.js" type="text/javascript"></script>
	<?php
}
?>
<script type="text/javascript">
// <!-- <![CDATA[
	tinymce.init({
	entity_encoding : "<?php echo getOption('tiny_mce_entity_encoding'); ?>",
					selector: "<?php echo $MCEselector; ?>",
					language: "<?php echo $MCElocale; ?>",
					relative_urls: false,
					flash_video_player_url: false,
<?php
if ($MCEimage_advtab == NULL || $MCEimage_advtab) {
	?>
		image_advtab: true,
	<?php
}
if ($MCEdirection) {
	?>
		directionality : '<?php echo $MCEdirection; ?>',
	<?php
}
?>
	content_css: "<?php echo $MCEcss; ?>",
<?php
if ($filehandler) {
	?>
		elements : "<?php echo $filehandler; ?>", file_browser_callback : <?php echo $filehandler; ?>,
	<?php
}
?>
	plugins: ["<?php echo $MCEplugins; ?>"],
<?php
if ($MCEspecial) {
	echo $MCEspecial . ",\n";
}
if ($MCEskin) {
	?>
		skin: "<?php echo $MCEskin; ?>",
	<?php
}
if (empty($MCEtoolbars)) {
	?>
		toolbar: false,
	<?php
} else {
	foreach ($MCEtoolbars as $key => $toolbar) {
		?>
			toolbar<?php if (count($MCEtoolbars) > 1) echo $key; ?>: "<?php echo $toolbar; ?>",
		<?php
	}
}
if ($MCEmenubar) {
	if (!is_string($MCEmenubar)) {
		$MCEmenubar = "file edit insert view format table tools ";
	}
} else {
	$MCEmenubar = "false";
}
?>

	statusbar: <?php echo ($MCEstatusbar) ? 'true' : 'false'; ?>,
					menubar: '<?php echo $MCEmenubar; ?>',
					setup: function(editor) {
					editor.on('blur', function(ed, e) {
					form = $(editor.getContainer()).closest('form');
					if (editor.isDirty()) {
					$(form).addClass('tinyDirty');
					} else {
					$(form).removeClass('tinyDirty');
					}
					});
<?php
if (getOption('dirtyform_enable') > 1) {
	?>
						editor.on('postRender', function(e) {
						//	clear the form from any tinyMCE dirtying once it has loaded
						form = $(editor.getContainer()).closest('form');
						$(form).trigger("reset");
						});
	<?php
}
?>
					}


	});
	// ]]> -->
</script>
