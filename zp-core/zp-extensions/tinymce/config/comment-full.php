<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * Comment form full-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.textarea_inputbox, textarea.texteditor_comments";
$MCEplugins = "advlist autolink lists link image charmap hr anchor pagebreak " .
				"searchreplace visualblocks visualchars code " .
				"insertdatetime media contextmenu directionality " .
				"emoticons paste";
$MCEtoolbars[1] = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | searchreplace visualchars | emoticons | ltr rtl code";
$MCEstatusbar = false;
$MCEmenubar = false;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
