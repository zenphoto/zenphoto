<?php
define('OFFSET_PATH', 0);
$_zp_script = __FILE__;
if (isset($_SERVER['SCRIPT_FILENAME'])) {
	$_zp_script = $_SERVER['SCRIPT_FILENAME'];
}
$configfile = dirname($_zp_script) . '/zp-data/zenphoto.cfg.php';
if (file_exists($configfile)) {
	require_once $configfile;
}
if (isset($_zp_conf_vars)) {
	if (isset($_zp_conf_vars['site_upgrade_state']) && $_zp_conf_vars['site_upgrade_state'] == 'closed') {
		$protocol = 'http';
		if (isset($_zp_conf_vars['server_protocol']) && $_zp_conf_vars['server_protocol'] == 'https') {
			$protocol = 'https';
		} else {
			if (isset($_SERVER['HTTPS'])) {
				if ('on' == strtolower($_SERVER['HTTPS']) || '1' == $_SERVER['HTTPS']) {
					$protocol = 'https';
				}
			} elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
				$protocol = 'https';
			}
		}
		if (isset($_zp_conf_vars['special_pages']['page']['rewrite'])) {
			$page = $_zp_conf_vars['special_pages']['page']['rewrite'];
		} else {
			$page = 'page';
		}
		if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
			header("HTTP/1.1 503 Service Unavailable");
			header("Status: 503 Service Unavailable");
			header('Pragma: no-cache');
			header('Retry-After: 3600');
			header('Cache-Control: no-cache, must-revalidate, max-age=0');
			if (isset($_GET['rss'])) {
				if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/rss-closed.xml')) {
					header('Content-Type: application/xml');
					include (dirname($_zp_script) . '/plugins/site_upgrade/rss-closed.xml');
				} else {
					?>
					<?xml version="1.0" encoding="utf-8"?>
					<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
						<channel>
							<title><![CDATA[RSS temprarily suspended for maintenance]]></title>
							<link><?php echo $_SERVER['REQUEST_URI']; ?></link>
							<description></description>
							<item>
								<title><![CDATA[Closed for maintenance]]></title>
								<description><![CDATA[The site is currently undergoing an upgrade]]></description>
							</item>
						</channel>
					</rss>
					<?php
				}
			} else {
				if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/closed.htm')) {
					include (dirname($_zp_script) . '/plugins/site_upgrade/closed.htm');
				} else {
					?>
					<!DOCTYPE html>
					<html>
						<head>
							<meta charset="UTF-8" />
							<title>Site closed for upgrade</title>
						</head>
						<body>
							<p>The site is undergoing an upgrade.</p>
							<p>Please return later.</p>
						</body>
					</html>
					<?php
				}
			}
			exit();
		}
	}
}
unset($_contents);
include (dirname(__FILE__) . '/zp-core/index.php');
