<?php
/**
 * Provides a check for more recent Zenphoto Versions.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Checks if there is a Zenphoto versions that is newer than the installed version.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'check_for_update_button');

function check_for_update_button($buttons) {
	if (is_connected()) {
		$enable = true;
		$title = gettext("Queries the Zenphoto web site for the latest version and compares that with the one that is running.");
	} else {
		$enable = false;
		$title = gettext("There appears to be no internet connection.");
	}
	$buttons[] = array(
										'category'=>gettext('Admin'),
										'enable'=>$enable,
										'button_text'=>gettext('Check for update'),
										'formname'=>'check_for_update.php',
										'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=check_for_update',
										'icon'=>'images/pass.png',
										'title'=>$title,
										'alt'=>gettext('Check for update'),
										'hidden'=>'<input type="hidden" name="action" value="check_for_update" />',
										'rights'=> ADMIN_RIGHTS
										);
	return $buttons;
}

/** check for update ***********************************************************/
/********************************************************************************/
if (isset($_GET['action'])) {
	if (sanitize($_GET['action'])=='check_for_update') {
		$v = checkForUpdate();
		if (empty($v)) {
			$msg = gettext("You are running the latest zenphoto version.");
		} else {
			if ($v == 'X') {
				$class = 'errorbox';
				$msg = gettext("Could not connect to <a href=\"http://www.zenphoto.org\">zenphoto.org</a>");
			} else {
				$class = 'notebox';
				$msg =  "<a href=\"http://www.zenphoto.org\">".sprintf(gettext("zenphoto version %s is available."), $v)."</a>";
				setOption('last_update_check',$msg);
			}
			$_GET['error'] = $class;
		}
		$_GET['msg'] = $msg;
		$_GET['action'] = 'external';
	}
}
?>