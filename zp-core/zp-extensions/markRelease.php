<?php
/**
 * Inserts or removes the qualifiers from the version file so that the install is switched between
 * a "debug" release and a normal release.
 *
 * @package plugins
 * @subpackage development
 *
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Mark installation as "released".');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.4';

zp_register_filter('admin_utilities_buttons', 'markRelease_button');

function markRelease_button($buttons) {
	$text = array('released'=>gettext('released'),'debug'=>gettext('debug'));
	$v = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/version.php');
	$i = strpos($rawversion = ZENPHOTO_VERSION, '-');
	if (isset($_REQUEST['markRelease'])) {
		if ($_REQUEST['markRelease']=='released') {
			$mark = '';
			$action = 'debug';
		} else {
			$mark = '-DEBUG';
			$action = 'released';
		}
		XSRFdefender('markReleased');
		if ($i === false) {
			$rawversion = ZENPHOTO_VERSION;
		} else {
			$rawversion = substr(ZENPHOTO_VERSION, 0, $i);
		}
		$v = str_replace(ZENPHOTO_VERSION, $rawversion.$mark, $v);
		file_put_contents(SERVERPATH.'/'.ZENFOLDER.'/version.php', $v);
	} else {
		if ($i === false) {
			$action = 'debug';
		} else {
			$action = 'released';
		}
	}
	$buttons[] = array(
			'category'=>gettext('Development'),
								'enable'=>true,
								'button_text'=>gettext('Mark release'),
								'formname'=>'markRelease_button',
								'action'=>'?markRelease='.$action,
								'icon'=>'images/pass.png',
								'title'=>sprintf(gettext('Edits the version.php file making a %s install.'),$text[$action]),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="markRelease" value="'.$action.'" />',
								'rights'=> ADMIN_RIGHTS,
								'XSRFTag' => 'markReleased'
								);
	return $buttons;
}
?>