<?php
/**
 * The plugin provides two services:
 * <ul>
 * 	<li>IP address filtering</li>
 * 	<li>Detection of <i>password probing</i> attempts
 * </ul>
 *
 * <b>IP address filtering:</b>
 *
 * Allows/Denies access to the gallery to specified IP address ranges
 * Detects repeated failed login attempts and blocks access to the IP address used
 * in these attempts.
 *
 * This does not block access to validated users, only anonomous visitors. But
 * a user will have to log on via the admin pages if out of the IP ranges as
 * he will get a Forbidden error on any front-end page including a logon form
 *
 * <b>Password probing:</b>
 *
 * Hackers often use <i>probing</i> or <i>password guessing</i> to attempt to breach your site
 * This plugin can help to throttle these attacks. It works by monitoring failed logon attempts.
 * If a defined threashold is exceeded by requests from a particular IP
 * address, further access attempts from that IP accress will be ignored until a timeout has expired.

 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 8|CLASS_PLUGIN;
$plugin_description = gettext("Tools to block hacker access to your site.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'ipBlocker';

zp_register_filter('load_theme_script', 'ipBlocker::load');
zp_register_filter('admin_allow_access', 'ipBlocker::adminGate');
zp_register_filter('admin_login_attempt', 'ipBlocker::login');
zp_register_filter('federated_login_attempt', 'ipBlocker::login');
zp_register_filter('guest_login_attempt', 'ipBlocker::login');

/**
 * Option handler class
 *
 */
class ipBlocker {
	/**
	 * class instantiation function
	 *
	 * @return security_logger
	 */
	function __construct() {
		setOptionDefault('ipBlocker_list', serialize(array()));
		setOptionDefault('ipBlocker_type', 'block');
		setOptionDefault('ipBlocker_threshold', 10);
		setOptionDefault('ipBlocker_timeout', 60);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$buttons = array(	gettext('Allow')=>'allow',gettext('Block')=>'block');
		$text = array_flip($buttons);
		$options = array(	gettext('IP list') => array('key' => 'ipBlocker_IP', 'type' => OPTION_TYPE_CUSTOM,
																									'order'=>4,
																									'desc' => sprintf(gettext('List of IP ranges to %s.'), $text[getOption('ipBlocker_type')])),
											gettext('Action') =>array('key' => 'ipBlocker_type', 'type' => OPTION_TYPE_RADIO,
																								'order'=>3,
																								'buttons'=>$buttons,
																								'desc' => gettext('How the plugin will interpret the IP list.')),
											gettext('Logon threshold') => array('key' => 'ipBlocker_threshold', 'type' => OPTION_TYPE_TEXTBOX,
																													'order'=>1,
																													'desc' => gettext('Admin page requests will be ignored after this many failed tries.')),
											gettext('Logon cool off') =>array('key' => 'ipBlocker_timeout', 'type' => OPTION_TYPE_TEXTBOX,
																												'order'=>2,
																												'desc' => gettext('The block will be removed after this many minutes.'))
		);
			if (!getOption('zp_plugin_ipBlocker')) {
			$options['note'] = array('key'=>'ipBlocker_note', 'type'=>OPTION_TYPE_NOTE,
																'order'=>0,
																'desc'=>'<p class="notebox">'.gettext('IP list ranges cannot be saved with the plugin disabled').'</p>');
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$list = unserialize(getOption('ipBlocker_list'));
		if (getOption('zp_plugin_ipBlocker')) {
			$disabled = '';
		} else {
			$disabled = ' disabled="disabled"';
		}
		$key = 0;
		foreach ($list as $key=>$range) {
			?>
			<input type="textbox" size="20" name="ipBlocker_ip_start_<?php echo $key; ?>" value="<?php echo html_encode($range['start']); ?>"<?php echo $disabled; ?> />
			-
			<input type="textbox" size="20" name="ipBlocker_ip_end_<?php echo $key; ?>" value="<?php echo html_encode($range['end']); ?>"<?php echo $disabled; ?> />
			<br />
			<?php
		}
		$i = $key;
		while ($i < $key+4) {
			$i++;
			?>
			<input type="textbox" size="20" name="ipBlocker_ip_start_<?php echo $i; ?>" value=""<?php echo $disabled; ?> />
			-
			<input type="textbox" size="20" name="ipBlocker_ip_end_<?php echo $i; ?>" value=""<?php echo $disabled; ?> />
			<br />
			<?php
		}
	}

	static function handleOptionSave($themename,$themealbum) {
		$notify = '';
		$list = array();
		foreach ($_POST as $key=>$param) {
			if ($param) {
				if (strpos($key, 'ipBlocker_ip_') !== false) {
					if (preg_match( "/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $param)){
						$p = explode('_', substr($key,13));
						$list[$p[1]][$p[0]] = $param;
					}
				}
			}
		}
		foreach ($list as $key=>$range) {
			if (!array_key_exists('start', $range) || !array_key_exists('end', $range)) {
				unset($list[$key]);
				$notify .= gettext('IP address format error').'<br />';
			}
		}
		setOption('ipBlocker_list', serialize($list));
		return $notify;
	}

	/**
	 * Monitors Login attempts
	 * @param bit $loggedin will be "false" if the login failed
	 * @param string $user ignored
	 * @param string $pass ignored
	 */
	static function login($loggedin, $user, $pass) {
		if (!$loggedin) {
			self::adminGate('', '');
		}
		return $loggedin;
	}

	static function suspended() {
		if ($block = getOption('ipBlocker_forbidden')) {
			$block = unserialize($block);
			if (array_key_exists($ip = getUserIP(),$block)) {
				if ($block[$ip] < (time()-getOption('ipBlocker_timeout')*60)) {
					// cooloff period passed
					unset($block[$ip]);
					if (count($block) > 0) {
						setOption('ipBlocker_forbidden', serialize($block));
					} else {
						setOption('ipBlocker_forbidden',NULL);
					}
				} else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Monitors blocked accesses to Admin pages
	 * @param bool $allow ignored
	 * @param string $page ignored
	 */
	static function adminGate($allow, $page) {
		//	clean out expired attempts
		$sql = 'DELETE FROM '.prefix('plugin_storage').' WHERE `type`="ipBlocker" AND `aux` < "'.(time()-getOption('ipBlocker_timeout')*60).'"';
		query($sql);
		//	add this attempt
		$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("ipBlocker", "'.time().'","'.getUserIP().'")';
		query($sql);
		//	check how many times this has happened recently
		$count = db_count('plugin_storage','WHERE `type`="ipBlocker" AND `data`="'.getUserIP().'"');
		if ($count >= getOption('ipBlocker_threshold')) {
			$block = getOption('ipBlocker_forbidden');
			if ($block) {
				$block = unserialize($block);
			} else {
				$block = array();
			}
			$block[getUserIP()] = time();
			setOption('ipBlocker_forbidden',serialize($block));
		}
		return $allow;
	}

	/**
	 *
	 * Monitors front end access and excludes access as defined by the options
	 * @param string $path
	 * @return string
	 */
	static function load($path) {
		$list = unserialize(getOption('ipBlocker_list'));
		$allow = getOption('ipBlocker_type') == 'allow';
		$gate = $allow;
		if (!empty($list)) {
			$ip = getUserIP();
			foreach ($list as $range) {
				if (version_compare($ip, $range['start'], '=>') && version_compare($ip, $range['end'], '<=')) {
					$gate = !$allow;
					break;
				}
			}
		}
		if ($gate) {
			header("HTTP/1.0 403 ".gettext("Forbidden"));
			header("Status: 403 ".gettext("Forbidden"));
			exitZP();	//	terminate the script with no output
		} else {
			return $path;
		}
	}

}

if (ipBlocker::suspended()) {
	header("HTTP/1.0 403 ".gettext("Forbidden"));
	header("Status: 403 ".gettext("Forbidden"));
	exitZP();	//	terminate the script with no output
}
?>