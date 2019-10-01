<?php

define('OFFSET_PATH', 0);
if (!$_zp_script = @$_SERVER['SCRIPT_FILENAME']) {
	$_zp_script = __FILE__;
}
$_contents = @file_get_contents(dirname($_zp_script) . '/zp-data/zenphoto.cfg.php');

if ($_contents) {
	if (strpos($_contents, '<?php') !== false)
		$_contents = '?>' . $_contents;
	@eval($_contents);
	if (@$_zp_conf_vars['site_upgrade_state'] == 'closed') {
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
		//$protocol = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']));
		$root = $protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
		if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
			if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/closed.php')) {
				header("HTTP/1.1 503 Service Unavailable");
				header("Status: 503 Service Unavailable");
				header("Retry-After: 3600");
				header('location: ' . $root . 'plugins/site_upgrade/closed.php');
			}
			exit();
		}
	}
}
unset($_contents);
include (dirname(__FILE__) . '/zp-core/index.php');
?>