<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen " .
				"insertdatetime media table contextmenu paste pasteobj tinyzenpage directionality ";
$MCEtoolbars[1] = "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image pasteobj tinyzenpage searchreplace visualchars | ltr rtl";
$MCEstatusbar = true;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
