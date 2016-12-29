<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * Comment form slim-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.textarea_inputbox, textarea.texteditor_comments";
$MCEplugins = "advlist autolink lists link image charmap anchor " .
				"searchreplace visualchars visualblocks code " .
				"insertdatetime media contextmenu paste ";
$MCEtoolbars[1] = "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ltr rtl code";
$MCEstatusbar = false;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
