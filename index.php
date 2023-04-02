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
		if (isset($_zp_conf_vars['server_protocol']) && ($_zp_conf_vars['server_protocol'] == 'https' || $_zp_conf_vars['server_protocol'] == 'https_admin')) { 
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
			if (isset($_GET['rss'])) {
				header('Content-Type: application/xml');
				if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/rss-closed.xml')) {	
					include (dirname($_zp_script) . '/plugins/site_upgrade/rss-closed.xml');
				} else {
					echo '<?xml version="1.0" encoding="utf-8"?>
					<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
						<channel>
							<title><![CDATA[RSS temporarily suspended for maintenance]]></title>
							<link>' . $protocol . "://" . htmlspecialchars($_SERVER['HTTP_HOST']).'</link>
							<description></description>
							<item>
								<title><![CDATA[Closed for maintenance]]></title>
								<description><![CDATA[The site is currently undergoing an upgrade]]></description>
							</item>
						</channel>
					</rss>
					';
				}
			} else {
				header("HTTP/1.1 503 Service Unavailable");
				header("Status: 503 Service Unavailable");
				header('Pragma: no-cache');
				header('Retry-After: 3600');
				header('Cache-Control: no-cache, must-revalidate, max-age=0');
				if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/closed.htm')) {
					include (dirname($_zp_script) . '/plugins/site_upgrade/closed.htm');
				} else {
					echo '
					<!DOCTYPE html>
					<html>
						<head>
							<title>Site closed for upgrade</title>
							<meta charset="UTF-8" />
						</head>
						<body>
							<p>The site is undergoing an upgrade.</p>
							<p>Please return later.</p>
						</body>
					</html>
					';
				}
			}
			exit();
		}
	}
}
unset($_contents);
include (dirname(__FILE__) . '/zp-core/index.php');