<?php
if (getOption('zp_plugin_zenpage')) {
	require_once('main.php');
} else {
	require_once('gallery.php');
}
?>