<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/content.css';
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap print preview hr anchor pagebreak " .
				"searchreplace wordcount visualblocks visualchars code fullscreen " .
				"insertdatetime media nonbreaking save table contextmenu directionality " .
				"emoticons template paste";
if (extensionEnabled('tinyZenpage'))
	$MCEplugins .= " tinyzenpage";

$MCEtoolbars = array();
$MCEstatusbar = true;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
