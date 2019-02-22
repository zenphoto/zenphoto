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
		if (isset($_zp_conf_vars['special_pages']['page']['rewrite'])) {
			$page = $_zp_conf_vars['special_pages']['page']['rewrite'];
		} else {
			$page = 'page';
		}
		$protocol = explode('/', strtolower($_SERVER['SERVER_PROTOCOL']));
		$root = $protocol[0] . '://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
	
		if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
			if (file_exists(dirname($_zp_script) . '/plugins/site_upgrade/closed.php')) {
				header('location: ' . $root . 'plugins/site_upgrade/closed.php');
			}
			exit();
		}
	}
}
unset($_contents);
include (dirname(__FILE__) . '/zp-core/index.php');
?>