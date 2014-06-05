<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 */
$MCEselector = "textarea.texteditor,textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste pastezen tinyzenpage";
$MCEtoolbars[1] = "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image tinyzenpage | code pastezen fullscreen";
$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
