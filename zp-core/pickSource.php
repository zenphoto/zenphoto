<?php

/**
 * receiver for "pick" ajax
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package admin
 */
define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . "/admin-globals.php");
admin_securityChecks(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, NULL);


if (isset($_POST['pick'])) {
	unset($_SESSION['pick']);
	$pick = sanitize($_POST['pick']);
	if (array_key_exists('picture', $pick)) {
		$pick['picture'] = str_replace(':', '&', $pick['picture']);
	}
	$_SESSION['pick'] = $pick;
}
if (isset($_POST['pasteImageSize'])) {
	setOption('pasteImageSize', sanitize_numeric($_POST['pasteImageSize']));
}