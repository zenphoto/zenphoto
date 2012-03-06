<?php
/**
 * "Clones" the Zenphoto to a new location using symlinks. The zp-core, themes, and user plugins
 * folders are symlinked. Setup will create the other needed folders.
 *
 * The new location should be an empty folder.
 *
 * Setup will be run on the new installation once it is cloned.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Makes a new Zenphoto installation with symlinks pointing to the current installation.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'cloneZenphoto::button');

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
		return $buttons;
	}

}

?>