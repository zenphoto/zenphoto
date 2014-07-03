<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin default light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap hr anchor pagebreak " .
				"searchreplace visualchars wordcount visualblocks  code fullscreen " .
				"insertdatetime media nonbreaking save table contextmenu " .
				"emoticons template paste pasteobj tinyzenpage ";
$MCEtoolbars = array();
$MCEstatusbar = true;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
