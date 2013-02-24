<?php
eval(@file_get_contents(dirname(__FILE__).'/zp-data/zenphoto.cfg'));
if (@$_zp_conf_vars['site_upgrade_state']=='closed') {
	header('location: plugins/site_upgrade/closed.php');
	exit();
}
include (dirname(__FILE__).'/zp-core/index.php');
?>