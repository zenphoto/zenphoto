<?php

// force UTF-8 Ø

if($zenpage = getOption('zp_plugin_zenpage')) { // check if Zenpage is enabled or not
	if (checkForPage(getOption("zenpage_homepage"))) { // switch to a news page
		$ishomepage = true;
		include ('pages.php');
	} else {
		include ('gallery.php');
	}
} else {
	include ('gallery.php');
}
?>