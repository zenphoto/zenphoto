<?php
if (extensionEnabled('zenpage')) {
	if (checkForPage(getOption('garland_customHome'))) {
		require_once('pages.php');
	} else {
		require_once('main.php');
	}
} else {
	require_once('gallery.php');
}
?>