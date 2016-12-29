<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * Zenpage plugin basic-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste pasteobj directionality ";
$MCEtoolbars[1] = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image pasteobj | ltr rtl code fullscreen";
$MCEstatusbar = true;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
