<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/content.css';
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code fullscreen " .
				"insertdatetime media contextmenu paste";

$MCEtoolbars[1] = "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code";
$MCEstatusbar = false;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
