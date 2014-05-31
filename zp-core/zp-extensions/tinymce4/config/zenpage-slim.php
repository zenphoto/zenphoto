<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/content.css';
$MCEselector = "textarea.texteditor,textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste tinyzenpage";
$MCEtoolbars[1] = "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";
if (extensionEnabled('tinyZenpage'))
	$MCEtoolbars[1] .= " tinyzenpage ";
$MCEtoolbars[1] .= " | code fullscreen";
$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
