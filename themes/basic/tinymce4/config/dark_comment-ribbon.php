<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 */
$MCEcss = FULLWEBPATH . '/' . THEMEFOLDER . '/' . basename(dirname(dirname(dirname(__FILE__)))) . '/tinymce4/config/dark_content.css';
$MCEskin = "tundora";
$MCEselector = "textarea.textarea_inputbox";
$MCEplugins = "advlist autolink lists link image charmap print preview hr anchor pagebreak " .
				"searchreplace wordcount visualblocks visualchars code fullscreen " .
				"insertdatetime save contextmenu directionality " .
				"emoticons paste";

$MCEmenubar = "edit insert view format tools";
$MCEtoolbars = array();
$MCEstatusbar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce4/config/config.js.php');
