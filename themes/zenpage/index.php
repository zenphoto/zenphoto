<?php

// force UTF-8 Ø

if($zenpage = extensionEnabled('zenpage')) { // check if Zenpage is enabled or not
	if (checkForPage(getOption("zenpage_homepage")) && $_zp_zenpage->pages_enabled) { // switch to a news page
		$ishomepage = true;
		include ('pages.php');
	} else {
		include ('gallery.php');
	}
} else {
	include ('gallery.php');
}
?>