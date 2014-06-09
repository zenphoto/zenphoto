<?php

/**
 * The configuration parameters for TinyMCE 4.x.
 *
 * zenphoto plugin default light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.texteditor";
$MCEplugins = "advlist autolink lists link image charmap print preview anchor " .
				"searchreplace visualblocks code fullscreen " .
				"insertdatetime media contextmenu paste";
$MCEtoolbars[1] = "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";
$MCEstatusbar = false;
$MCEmenubar = true;
include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
