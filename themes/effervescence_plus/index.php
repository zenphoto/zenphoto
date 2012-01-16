<?php
// force UTF-8 Ø
if (getOption('custom_index_page') == 'gallery') {
	if ($zenpage = getOption('zp_plugin_zenpage')) {
		$imagereduction = 1/2;
	} else {
		$imagereduction = 1;
	}
	require('indexpage.php');
} else {
	require('gallery.php');
}
?>