<?php
/**
 *
 * Hackers often use "probing" or "password guessing" to attempt to breach your site
 * This plugin can help to throttle these attacks. It works by monitoring failed logon attempts.
 * If a defined threashold is exceeded by requests from a particular IP
 * address, further access attempts from that IP accress will be ignored until a timeout has expired.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 8|CLASS_PLUGIN;
$plugin_description = gettext("Blocks access from an IP address which has had multiple failed logon attempts.");
$plugin_author = "Stephen Billard (sbillard)";


$option_interface = 'failed_access_blocker';
zp_register_filter('admin_allow_access', 'failed_access_blocker_adminGate');
zp_register_filter('admin_login_attempt', 'failed_access_blocker_login');
zp_register_filter('federated_login_attempt', 'failed_access_blocker_login');
zp_register_filter('guest_login_attempt', 'failed_access_blocker_login');

/**
 * Option handler class
 *
 */
class failed_access_blocker {
	/**
	 * class instantiation function
	 *
	 * @return security_logger
	 */
	function failed_access_blocker() {
		setOptionDefault('failed_access_blocker_attempt_threshold', 10);
		setOptionDefault('failed_access_blocker_timeout', 60);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Attempt threshold') => array('key' => 'failed_access_blocker_attempt_threshold', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('Admin page requests will be ignored after this many failed tries.')),
									gettext('Minutes to cool off') =>array('key' => 'failed_access_blocker_timeout', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The block will be removed after this waiting period.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

}

/**
 * Monitors Login attempts
 * @param bit $loggedin will be "false" if the login failed
 * @param string $user ignored
 * @param string $pass ignored
 */
function failed_access_blocker_login($loggedin, $user, $pass) {
	if (!$loggedin) {
		failed_access_blocker_adminGate('', '');
	}
	return $loggedin;
}

/**
 * Monitors blocked accesses to Admin pages
 * @param bool $allow ignored
 * @param string $page ignored
 */
function failed_access_blocker_adminGate($allow, $page) {
	//	clean out expired attempts
	$sql = 'DELETE FROM '.prefix('plugin_storage').' WHERE `type`="failed_access" AND `aux` < "'.(time()-getOption('failed_access_blocker_timeout')*60).'"';
	query($sql);
	//	add this attempt
	$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("failed_access", "'.time().'",'.db_quote(getUserIP()).')';
	query($sql);
	//	check how many times this has happened recently
	$count = db_count('plugin_storage','WHERE `type`="failed_access" AND `data`="'.getUserIP().'"');
	if ($count >= getOption('failed_access_blocker_attempt_threshold')) {
		$block = getOption('failed_access_blocker_forbidden');
		if ($block) {
			$block = unserialize($block);
		} else {
			$block = array();
		}
		$block[getUserIP()] = time();
		setOption('failed_access_blocker_forbidden',serialize($block));
	}
	return $allow;
}

if ($block = getOption('failed_access_blocker_forbidden')) {
	$block = unserialize($block);
	if (array_key_exists($ip = getUserIP(),$block)) {
		if ($block[$ip] < (time()-getOption('failed_access_blocker_timeout')*60)) {	// cooloff period passed
			unset($block[$ip]);
			if (count($block) > 0) {
				setOption('failed_access_blocker_forbidden', serialize($block));
			} else {
				setOption('failed_access_blocker_forbidden',NULL);
			}
		} else {
			header("HTTP/1.0 403 ".gettext("Forbidden"));
			header("Status: 403 ".gettext("Forbidden"));
			exitZP();	//	terminate the script with no output
		}
	}
}


?>