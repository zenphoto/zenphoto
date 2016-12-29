<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto basic-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen directionality " .
				"insertdatetime media contextmenu paste ";
$MCEtoolbars[1] = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image } ltr rtl";
$MCEstatusbar = false;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
