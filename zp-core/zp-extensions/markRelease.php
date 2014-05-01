<?php

/**
 * Inserts or removes the qualifiers from the version file so that the install is switched between
 * a "debug" release and a normal release.
 *
 * @package plugins
 * @subpackage development
 *
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('Mark installation as “released”.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'markRelease_button');

if (isset($_REQUEST['markRelease'])) {
	XSRFdefender('markRelease');
	$v = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/version.php');
	preg_match_all("~(define\('ZENPHOTO_VERSION',\s*'([^']*)'\))~", $v, $matches);
	$currentVersion = $matches[2][0];
	if (isset($matches[2][1])) {
		$originalVersion = $matches[2][1];
	} else {
		$originalVersion = preg_replace("~-[^RC]~i", '', $currentVersion);
	}
	if ($_REQUEST['markRelease'] == 'released') {
		if (preg_match('~-[^RC]~i', $originalVersion)) {
			$originalVersion = preg_replace('~-.*~', '', $originalVersion);
		}
		$version = "define('ZENPHOTO_VERSION', '$originalVersion');";
	} else {
		preg_match_all('~([^-]*)~', $currentVersion, $matches);
		$mark = $matches[0][0] . '-DEBUG';
		$version = "define('ZENPHOTO_VERSION', '$mark'); //original: define('ZENPHOTO_VERSION', '$originalVersion');";
	}
	$v = preg_replace("~define\('ZENPHOTO_VERSION.*\n~", $version . "\n", $v);
	file_put_contents(SERVERPATH . '/' . ZENFOLDER . '/version.php', $v);
	header('location:' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');

	exitZP();
}

function markRelease_button($buttons) {
	$text = array('released' => gettext('released'), 'debug' => gettext('debug'));
	if (TEST_RELEASE) {
		$mark = '-DEBUG';
		$action = 'released';
	} else {
		$mark = '';
		$action = 'debug';
	}

	$buttons[] = array(
					'category'		 => gettext('Development'),
					'enable'			 => true,
					'button_text'	 => gettext('Mark release'),
					'formname'		 => 'markRelease_button',
					'action'			 => '?markRelease=' . $action,
					'icon'				 => $mark ? 'images/comments-on.png' : 'images/comments-off.png',
					'title'				 => sprintf(gettext('Edits the version.php file making a “%s” install.'), $text[$action]),
					'alt'					 => '',
					'hidden'			 => '<input type="hidden" name="markRelease" value="' . $action . '" />',
					'rights'			 => ADMIN_RIGHTS,
					'XSRFTag'			 => 'markRelease'
	);
	return $buttons;
}

?>