<?php
/**
 * "Clones" the currrent Zenphoto installation to a new location using symlinks. The <i>zp-core</i>, <i>themes</i>, <i>user plugins</i>
 * folders and the root <i>index.php</i> file are symlinked. Setup will create the other needed folders.
 *
 * Two buttons are placed in the Admin Utility functions box on the overview tab.
 * 	<ul>
 * 		<li>Clone installation</li>
 * 		<li>Delete setup files</li>
 * 	</ul>
 *
 * The <i>Clone installation</i> button will take you to the cloning page where you can select a folder destination
 * for the cloned installation. Upon successful cloning there will be a link to <code>setup</code> for the new
 * installation. (This presumes the <code>setup</code> files are present. If not you will be told which files
 * need to be reloaded.)
 *
 * The <i>Delete setup files</i> button will remove the <code>setup</code> files from the current installation. This is
 * the same function provided by <i>Setu</i> after a successful install. It is provided here because you will likely not want to
 * remove the setup files until you have cloned all desired destinations.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Makes a new Zenphoto installation with symlinks pointing to the current installation.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (zpFunctions::hasPrimaryScripts())?false:gettext('Only the primary installation may clone offspring installations.');

require_once(SERVERPATH.'/'.ZENFOLDER.'/reconfigure.php');
if (!$plugin_disable) {
	zp_register_filter('admin_utilities_buttons', 'cloneZenphoto::button');
}

class cloneZenphoto {

	static function button($buttons) {
		$buttons[] = array(
											'category'=>gettext('admin'),
											'enable'=>'1',
											'button_text'=>gettext('Clone installation'),
											'formname'=>'cloneZenphoto',
											'action'=>PLUGIN_FOLDER.'/cloneZenphoto/cloneTab.php',
											'icon'=>'images/folder.png',
											'title'=>'',
											'alt'=>gettext('Clone'),
											'hidden'=>'',
											'rights'=> ADMIN_RIGHTS
											);
		list($diff, $needs) = checkSignature();
		if (empty($needs)) {
			$buttons[] = array(
												'XSRFTag'=>'removeSetup',
												'category'=>gettext('admin'),
												'enable'=>zpFunctions::hasPrimaryScripts(),
												'button_text'=>gettext('Delete setup files'),
												'formname'=>'removeSetup',
												'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=removeSetup',
												'icon'=>'images/folder.png',
												'title'=>gettext('Removes setup files.'),
												'alt'=>'',
												'hidden'=>'<input type="hidden" name="action" value="removeSetup" />	',
												'rights'=> ADMIN_RIGHTS
												);
		}
		return $buttons;
	}

}

/**
 * process remove action
 */
function removeSetup() {
	global $msg, $class;
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/security-logger.php');

	$curdir = getcwd();
	chdir(SERVERPATH.'/'.ZENFOLDER.'/setup');
	$list = safe_glob('*.*');
	chdir($curdir);
	$rslt = array();
	foreach ($list as $component) {
		if ($component != '..' && $component != '.') {
			@chmod(SERVERPATH.'/'.ZENFOLDER.'/setup/'.$component, 0666);
			if(!@unlink(SERVERPATH.'/'.ZENFOLDER.'/setup/'.$component)) {
				$rslt[] = '../setup/'.$component;
			}
		}
	}
	@chmod(SERVERPATH.'/'.ZENFOLDER.'/setup.php', 0666);
	if (!@unlink(SERVERPATH.'/'.ZENFOLDER.'/setup.php')) {
		$rslt[] = '../setup.php';
	}
	@chmod(SERVERPATH.'/'.ZENFOLDER.'/setup/', 0766);
	if (!@rmdir(SERVERPATH.'/'.ZENFOLDER.'/setup/')) {
		$rslt[] = '../setup/';
	}
	if (empty($rslt)) {
		zp_apply_filter('log_setup', true, 'delete', '');
		$class = 'messagebox';
		$msg = gettext('Setup files deleted.');
	} else {
		$rslt = implode(', ', $rslt);
		zp_apply_filter('log_setup', false, 'delete', $rslt);
		$class = 'errorbox';
		$msg =  sprintf(gettext('Failed to delete: %s'), $rslt);
	}
}
?>