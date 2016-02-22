<?php

/**
 * This plugin monitors front-end access and shuts down responses when a particular
 * IP tries to flood the gallery with requests.
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 990 | FEATURE_PLUGIN;
$plugin_description = gettext("Tools to block denial of service attacks.");
$plugin_author = "Stephen Billard (sbillard)";

define('accessThreshold_IP_RETENTION', 500);
define('accessThreshold_IP_THRESHOLD', 5000);
define('accessThreshold_IP_ACCESS_WINDOW', 10 * 60);

$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
$ip = getUserIP();

if (array_key_exists($ip, $recentIP) && $recentIP[$ip]['accessTime'] > time() - accessThreshold_IP_ACCESS_WINDOW) {
	$recentIP[$ip]['counter'] ++;
} else {
	$recentIP[$ip] = array('accessTime' => time(), 'counter' => 1);
	if (count($recentIP) > accessThreshold_IP_RETENTION) {
		array_shift($recentIP);
	}
}
file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP', serialize($recentIP));
if ($recentIP[$ip]['counter'] > accessThreshold_IP_THRESHOLD) {
	zp_error(gettext('Access threshold exceeded.'), E_USER_NOTICE);
	exitZP();
}
?>