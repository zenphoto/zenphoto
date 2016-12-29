<?php

/**
 * The configuration functions for TinyMCE
 *
 * Zenpage plugin full-light configuration
 * @author Stephen Billard (sbillard)
 */
$MCEselector = "textarea.content,textarea.desc,textarea.extracontent";
$MCEplugins = "advlist autolink lists link image charmap hr anchor pagebreak " .
				"searchreplace visualchars wordcount visualblocks code fullscreen " .
				"insertdatetime media nonbreaking save table contextmenu " .
				"emoticons template paste pasteobj directionality ";
$MCEtoolbars[1] = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";
$MCEtoolbars[2] = "media | emoticons pasteobj | ltr rtl code fullscreen";
$MCEstatusbar = true;
$MCEmenubar = true;

include(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/tinymce/config/config.js.php');
