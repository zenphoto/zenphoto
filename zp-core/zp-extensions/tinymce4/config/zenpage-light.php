<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/content.css';
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";

$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste";
if (extensionEnabled('tinyZenpage'))
	$MCEplugins .= "tinyzenpage";

$MCEtoolbars[1] = "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";
if (extensionEnabled(' tinyZenpage'))
	$MCEtoolbars[2] .= " tinyzenpage";
$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
