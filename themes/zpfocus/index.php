<?php

if (function_exists("checkForPage")) { // check if Zenpage is enabled or not
	if (checkForPage(getOption("zpfocus_homepage"))) { // switch to a news page
		$ishomepage = true;
		include ('pages.php');
	} else {
		include ('gallery.php');
	}
} else {
	include ('gallery.php');
}
?>