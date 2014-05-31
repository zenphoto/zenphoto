<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/content.css';
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap print preview hr anchor pagebreak " .
				"searchreplace wordcount visualblocks visualchars code fullscreen " .
				"insertdatetime media nonbreaking save contextmenu directionality " .
				"emoticons template paste";

$MCEtoolbars = array();
$MCEstatusbar = false;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
