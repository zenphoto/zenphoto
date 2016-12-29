<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto slim-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code fullscreen " .
				"insertdatetime media contextmenu paste directionality ";
$MCEtoolbars[1] = "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | ltr rtl code";
$MCEstatusbar = false;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
