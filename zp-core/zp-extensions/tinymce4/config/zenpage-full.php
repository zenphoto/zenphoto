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

$MCEtoolbars[1] = "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";
$MCEtoolbars[2] = "print preview media | emoticons";
if (extensionEnabled(' tinyZenpage'))
	$MCEtoolbars[2] .= " tinyzenpage";
$MCEtoolbars[2] .= ' | code fullscreen';
$MCEstatusbar = true;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
