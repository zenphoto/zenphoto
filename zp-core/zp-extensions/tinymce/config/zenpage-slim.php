<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * Zenpage plugin slim-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.texteditor,textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste pasteobj directionality ";
$MCEtoolbars[1] = "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image pasteobj | ltr rtl code fullscreen";
$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
