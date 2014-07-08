<?php

/**
 * receiver for "pick" ajax
 *
 * @author Stephen Billard (sbillard)
 *
 * copyright © 2014 Stephen L Billard
 * 
 * @package admin
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . "/admin-globals.php");
admin_securityChecks(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, NULL);

unset($_SESSION['pick']);
if (isset($_POST['pick'])) {
	$pick = sanitize($_POST['pick']);
	if (array_key_exists('picture', $pick)) {
		$pick['picture'] = str_replace(':', '&', $pick['picture']);
	}
	$_SESSION['pick'] = $pick;
}