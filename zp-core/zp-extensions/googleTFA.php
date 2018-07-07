<?php

/**
 * Provides 2 Phase authentication via GoogleAuthenticator
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/googleAuthenticator
 * @pluginCategory admin
 *
 * Copyright 2018 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Two ohase authentication.');

$option_interface = 'googleTFA';

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/Secret.php');
require_once (SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/SecretFactory.php');

zp_register_filter('admin_login_attempt', 'googleTFA::check');
zp_register_filter('save_admin_custom_data', 'googleTFA::save');
zp_register_filter('edit_admin_custom_data', 'googleTFA::edit', 999);

class googleTFA extends fieldExtender {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('googleTFA_issuer', $_SERVER['HTTP_HOST'] . WEBPATH);

			parent::constructor('googleTFA', self::fields());
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Issuer name') => array('key' => 'googleTFA_issuer', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('This is the name the Google Authenticator app associate with the one time pin code.'))
		);
	}

	static function fields() {
		return array(
				array('table' => 'administrators', 'name' => 'OTAsecret', 'desc' => gettext('secret for googleAuthenticator'), 'type' => 'tinytext'),
				array('table' => 'administrators', 'name' => 'QRuri', 'desc' => gettext('googleAuthenticator QR code data'), 'type' => 'tinytext')
		);
	}

	static function check($loggedin, $post_user, $post_pass, $userobj) {
		if ($userobj->getOTAsecret()) {
			$_SESSION['OTA'] = array('user' => $post_user, 'userID' => $userobj->getID(), 'redirect' => $_POST['redirect']);
			header('Location: ' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/googleTFA/auth_code.php');
			exitZP();
		}
		// redirect to form to have the user provide the googleAuth key
		return $loggedin;
	}

	static function save($updated, $userobj, $i, $alter) {
		if (isset($_POST['user'][$i]['otp']) && $alter) {
			if (!$userobj->getOTAsecret()) {
				$secretFactory = new \Dolondro\GoogleAuthenticator\SecretFactory();
				$secret = $secretFactory->create(WEBPATH, $userobj->getUser());
				$userobj->setOTAsecret($secret->getSecretKey());
				$userobj->setQRuri($secret->getUri());
				$updated = true;
			}
		} else {
			if ($userobj->getOTAsecret()) {
				$userobj->setOTAsecret(NULL);
				$updated = true;
			}
		}
		return $updated;
	}

	static function edit($html, $userobj, $id, $background, $current, $local_alterrights) {
		if ($userobj->getOTAsecret()) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$result = '<div class="user_left">' . "\n"
						. '<input type="checkbox" name="user[' . $id . '][otp]" value="1" ' . $local_alterrights . $checked . ' />&nbsp;'
						. gettext("2 factor authentication") . "\n";

		if ($checked) {
			$result .= "<br />\n"
							. "<fieldset>\n"
							. '<legend>' . gettext('Provide to GoogleAuthenticator') . "</legend>\n"
							. '<div style="display: flex; justify-content: center;">'
							. '<img src="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/qrcode/image.php?content=' . html_encode($userobj->getQRuri()) . '" />'
							. '</div>'
							. '<div style="display: flex; justify-content: center;">' . $userobj->getOTAsecret() . '</div>'
							. "</fieldset>\n"
			;
		}
		$result .= '</div>' . "\n"
						. '<br class="clearall">' . "\n";
		return $html . $result;
	}

}

function googleTFA_enable($enabled) {
	if ($enabled) {
		$report = gettext('<em>OTAsecret</em> field will be added to the Administrator object.');
	} else {
		$report = gettext('<em>OTAsecret</em> field will be <span style="color:red;font-weight:bold;">dropped</span> from the Administrator object.');
	}
	requestSetup('googleTFA', $report);
}
