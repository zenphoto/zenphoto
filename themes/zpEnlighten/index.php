<?php
if ( extensionEnabled('zenpage') ) { // check if Zenpage is enabled or not
	if (checkForPage(getOption("zenpage_homepage"))) { // switch to a news page
		$ishomepage = true;
		include ('pages.php');
	} else {
		include ('home.php');
	}
} else { 
	include ('gallery.php');
}
?>