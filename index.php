<?php

define('OFFSET_PATH', 0);
$_contents = @file_get_contents(dirname(__FILE__) . '/zp-data/zenphoto.cfg');
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
		if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
			header('location: plugins/site_upgrade/closed.php');
			exit();
		}
	}
}
unset($_contents);
include (dirname(__FILE__) . '/zp-core/index.php');
?>