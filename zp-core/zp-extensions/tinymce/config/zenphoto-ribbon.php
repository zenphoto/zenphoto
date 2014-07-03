<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap hr anchor pagebreak " .
				"searchreplace visualchars wordcount visualblocks  code fullscreen " .
				"insertdatetime media nonbreaking save contextmenu " .
				"emoticons template paste ";

$MCEtoolbars = array();
$MCEstatusbar = false;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
