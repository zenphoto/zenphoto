<?php

/**
 * This plugin monitors front-end access and shuts down responses when a particular
 * IP sub-network tries to flood the gallery with requests.
 *
 * The sensitivity of the check can be changed by changing the <code>SENSITIVITY>/code> definition.
 * 	4 will resolve to the Host
 *  3 will resolve to the Sub-net
 *  2 will resolve to the Network
 *
 * This definition is used rather than an option to avoid database access as one ot the
 * flooding attacks it to excede the query limit of the database.
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

if (!OFFSET_PATH) {
	define('accessThreshold_IP_RETENTION', 500);
	define('accessThreshold_IP_THRESHOLD', 5000);
	define('accessThreshold_IP_ACCESS_WINDOW', 10 * 60);
	define('SENSITIVITY', 3);

	$recentIP = getSerializedArray(@file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/recentIP'));
	$x = explode('.', getUserIP());
	$x = array_slice($x, 0, SENSITIVITY);
	$ip = implode(":", $x);

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
}
?>