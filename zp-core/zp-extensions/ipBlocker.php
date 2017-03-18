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
 * This does not block access to validated users, only anonymous visitors. But
 * a user will have to log on via the admin pages if out of the IP ranges as
 * he will get a Forbidden error on any front-end page including a logon form
 *
 * <b>Password probing:</b>
 *
 * Hackers often use <i>probing</i> or <i>password guessing</i> to attempt to breach your site
 * This plugin can help to throttle these attacks. It works by monitoring failed logon attempts.
 * If a defined threashold is exceeded by requests from a particular IP
 * address, further access attempts from that IP accress will be ignored until a timeout has expired.
 *
 * <b>IP list importing</b>
 *
 * IP addresses may be supplied in a text file, one IP per line. Upload the text file to the <i>%UPLOAD_FOLDER%</i> folder.

 * @author Stephen Billard (sbillard)
 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 10 | CLASS_PLUGIN;
$plugin_description = gettext("Tools to block hacker access to your site.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'ipBlocker';

zp_register_filter('admin_login_attempt', 'ipBlocker::login', 9999);
zp_register_filter('federated_login_attempt', 'ipBlocker::login', 9999);
zp_register_filter('guest_login_attempt', 'ipBlocker::login', 9999);

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
		if (OFFSET_PATH == 2) {
			setOptionDefault('ipBlocker_list', serialize(array()));
			setOptionDefault('ipBlocker_type', 'block');
			setOptionDefault('ipBlocker_threshold', 10);
			setOptionDefault('ipBlocker_404_threshold', 10);
			setOptionDefault('ipBlocker_timeout', 60);
			setOptionDefault('ipBlocker_forbidden', NULL);

			$sql = 'UPDATE ' . prefix('plugin_storage') . ' SET `type`="ipBlocker", `subtype`="404" WHERE `type`="ipBlocker_404"';
			query($sql);
			$sql = 'UPDATE ' . prefix('plugin_storage') . ' SET `type`="ipBlocker", `subtype`="logon" WHERE `type`="ipBlocker_logon"';
			query($sql);
		}
	}

	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$buttons = array(gettext('Allow') => 'allow', gettext('Block') => 'block');
		$text = array_flip($buttons);
		$cwd = getcwd();
		chdir(SERVERPATH . '/' . UPLOAD_FOLDER);
		$list = safe_glob('*.txt');
		chdir($cwd);
		$files = array('' => '');
		foreach ($list as $file) {
			$files[$file] = $file;
		}
		$options = array(gettext('IP list') => array('key' => 'ipBlocker_IP', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 5,
						'desc' => sprintf(gettext('List of IP ranges to %s.'), $text[getOption('ipBlocker_type')])),
				gettext('Import list') => array('key' => 'ipBlocker_import', 'type' => OPTION_TYPE_SELECTOR,
						'order' => 6,
						'selections' => $files,
						'nullselection' => '',
						'disabled' => !extensionEnabled('ipBlocker'),
						'desc' => sprintf(gettext('Import an external IP list. <p class="notebox"><strong>NOTE:</strong> If this list is large it may exceed the capacity of zenphoto and %s to process and store the results.'), DATABASE_SOFTWARE)),
				gettext('Action') => array('key' => 'ipBlocker_type', 'type' => OPTION_TYPE_RADIO,
						'order' => 4,
						'buttons' => $buttons,
						'desc' => gettext('How the plugin will interpret the IP list.')),
				gettext('Logon threshold') => array('key' => 'ipBlocker_threshold', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => gettext('Admin page requests will be ignored after this many failed tries.')),
				gettext('404 threshold') => array('key' => 'ipBlocker_404_threshold', 'type' => OPTION_TYPE_NUMBER,
						'order' => 1,
						'desc' => gettext('Access will be suspended after this many 404 errors.')),
				gettext('Cool off') => array('key' => 'ipBlocker_timeout', 'type' => OPTION_TYPE_NUMBER,
						'order' => 3,
						'desc' => gettext('The block will be removed after this many minutes.'))
		);
		if (!extensionEnabled('ipBlocker')) {
			$options['note'] = array('key' => 'ipBlocker_note', 'type' => OPTION_TYPE_NOTE,
					'order' => 0,
					'desc' => '<p class="notebox">' . gettext('IP list ranges cannot be managed with the plugin disabled') . '</p>');
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		$list = getSerializedArray(getOption('ipBlocker_list'));
		if (extensionEnabled('ipBlocker')) {
			$disabled = '';
		} else {
			$disabled = ' disabled="disabled"';
		}

		switch ($option) {
			case 'ipBlocker_IP':
				$key = 0;
				foreach ($list as $key => $range) {
					?>
					<input id="ipholder_<?php echo $key; ?>a" type="textbox" size="15" name="ipBlocker_ip_start_<?php echo $key; ?>"
								 value="<?php echo html_encode($range['start']); ?>" <?php echo $disabled; ?> />
					-
					<input id="ipholder_<?php echo $key; ?>b" type="textbox" size="15" name="ipBlocker_ip_end_<?php echo $key; ?>"
								 value="<?php echo html_encode($range['end']); ?>" <?php echo $disabled; ?> />
					<br />
					<?php
				}
				$i = $key;
				while ($i < $key + 4) {
					$i++;
					?>
					<input id="ipholder_<?php echo $i; ?>a" type="textbox" size="15" name="ipBlocker_ip_start_<?php echo $i; ?>"
								 value="" <?php echo $disabled; ?> />
					-
					<input id="ipholder_<?php echo $i; ?>b" type="textbox" size="15" name="ipBlocker_ip_end_<?php echo $i; ?>"
								 value="" <?php echo $disabled; ?> />
					<br />
					<?php
				}
				?>
				<script type="text/javascript">
					<!--
					function clearips() {
				<?php
				for ($i = 0; $i <= $key + 4; $i++) {
					?>
							$('#ipholder_<?php echo $i; ?>a').val('');
							$('#ipholder_<?php echo $i; ?>b').val('');
					<?php
				}
				?>
					}
					//-->
				</script>
				<p class="buttons">
					<a onclick="clearips();"><?php echo gettext('clear list'); ?></a>
				</p>
				<?php
				break;
		}
	}

	static function handleOptionSave($themename, $themealbum) {
		$notify = '';
		$list = array();
		foreach ($_POST as $key => $param) {
			if ($param) {
				if (strpos($key, 'ipBlocker_ip_') !== false) {
					if (preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $param)) {
						$p = explode('_', substr($key, 13));
						$list[$p[1]][$p[0]] = $param;
					}
				}
			}
		}
		foreach ($list as $key => $range) {
			if (!array_key_exists('start', $range) || !array_key_exists('end', $range)) {
				unset($list[$key]);
				$notify .= gettext('IP address format error') . '<br />';
			}
		}
		setOption('ipBlocker_list', serialize($list));
		purgeOption('ipBlocker_import');
		if (!empty($_POST['ipBlocker_import'])) {
			$file = SERVERPATH . '/' . UPLOAD_FOLDER . '/' . sanitize_path($_POST['ipBlocker_import']);
			if (file_exists($file)) {
				$import_list = array();
				// insert current list into import list for posterity
				foreach ($list as $range) {
					$ipa = explode('.', $range['end']);
					$ipend = sprintf('%03u.%03u.%03u.%03u', @$ipa[0], @$ipa[1], @$ipa[2], @$ipa[3]);
					$ipa = explode('.', $range['start']);
					do {
						$current = sprintf('%03u.%03u.%03u.%03u', @$ipa[0], @$ipa[1], @$ipa[2], @$ipa[3]);
						$ipa[3] ++;
						if ($ipa[3] > 255) {
							$ipa[3] = 0;
							$ipa[2] ++;
							if ($ipa[2] > 255) {
								$ipa[2] = 0;
								$ipa[2] ++;
								if ($ipa[1] > 255) {
									$ipa[1] = 0;
									$ipa[0] ++;
									if ($ipa[0] > 255) {
										break;
									}
								}
							}
						}
						$import_list[] = $current;
					} while ($current < $ipend);
				}


				$import = explode("\n", file_get_contents($file));
				foreach ($import as $ip) {
					$ip = trim($ip);
					if ($ip) {
						$ipa = explode('.', $ip);
						$import_list[] = sprintf('%03u.%03u.%03u.%03u', @$ipa[0], @$ipa[1], @$ipa[2], @$ipa[3]);
					}
				}


				$list = array();
				if (!empty($import_list)) {
					$import_list = array_unique($import_list); //	remove duplicates
					sort($import_list);
					//now make a range pair list for the storage.
					$current = $start = array_shift($import_list);
					$end = $start;
					$clean = false;
					while (!empty($import_list)) {
						$try = trim(array_shift($import_list));
						if ($try) { //	ignore empty lines
							$ipa = explode('.', $current);
							$ipa[3] ++;
							if ($ipa[3] > 255) {
								$ipa[3] = 0;
								$ipa[2] ++;
								if ($ipa[2] > 255) {
									$ipa[2] = 0;
									$ipa[2] ++;
									if ($ipa[1] > 255) {
										$ipa[1] = 0;
										$ipa[0] ++;
										if ($ipa[0] > 255) {
											break;
										}
									}
								}
							}
							$next = sprintf('%03u.%03u.%03u.%03u', @$ipa[0], @$ipa[1], @$ipa[2], @$ipa[3]);
							$current = $try;
							if ($clean = $current != $next) {
								$list[] = array('start' => $start, 'end' => $end);
								$start = $end = $current;
							} else {
								$end = $next;
							}
						}
					}
					if (!$clean) {
						$list[] = array('start' => $start, 'end' => $end);
					}
					setOption('ipBlocker_list', serialize($list));
				}
			}
		}
		if ($notify)
			return '&custom=' . $notify;
		else
			return false;
	}

	/**
	 * Monitors Login attempts and suspends of past failure threshold
	 * @param bit true if login is successful
	 * @param string $user ignored
	 * @param string $pass ignored
	 */
	static function login($loggedin, $user, $pass = NULL, $auth = NULL) {
		if (!$loggedin)
			self::ipGate('logon');
		return $loggedin;
	}

	/**
	 * monitors 404 results and suspends if past threshold
	 * @return type
	 */
	static function notFound() {
		self::ipGate('404');
	}

	/**
	 * Checks if ip should be suspended
	 * @param bool $allow ignored
	 * @param string $page ignored
	 */
	static function ipGate($type) {
		//	clean out expired attempts
		$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="ipBlocker" AND `aux` < "' . (time() - getOption('ipBlocker_timeout') * 60) . '"';
		query($sql);
		//	add this attempt
		$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `subtype`, `aux`,`data`) VALUES ("ipBlocker",' . db_quote($type) . ', ' . db_quote(time()) . ',' . db_quote(getUserIP()) . ')';
		query($sql);
		//	check how many times this has happened recently
		$count = db_count('plugin_storage', 'WHERE `type`="ipBlocker" AND `subtype`=' . db_quote($type) . ' AND `data`="' . getUserIP() . '"');
		if ($count >= ($threshold = getOption('ipBlocker_threshold'))) {
			$ip = getUserIP();
			zp_apply_filter('security_misc', 2, $type, 'ipBlocker', gettext('Suspended'));

			$block = getOption('ipBlocker_forbidden');
			if ($block) {
				$block = getSerializedArray($block);
			} else {
				$block = array();
			}
			$block[$ip] = time();
			setOption('ipBlocker_forbidden', serialize($block));
			$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type` ="ipBlocker" AND `data`=' . db_quote($ip);
			query($sql);
		}
	}

	/**
	 * Checks if ip is in the ipBlocker_list list and responds as per ipBlocker_type
	 * @return type
	 */
	static function blocked() {
		$list = getSerializedArray(getOption('ipBlocker_list'));
		$allow = getOption('ipBlocker_type') == 'allow';
		$gate = $allow;
		if (!empty($list)) {
			$ipa = explode('.', getUserIP());
			$ip = sprintf('%03u.%03u.%03u.%03u', @$ipa[0], @$ipa[1], @$ipa[2], @$ipa[3]);
			foreach ($list as $range) {
				if ($ip >= $range['start'] && $ip <= $range['end']) {
					$gate = !$allow;
					break;
				}
			}
		}
		return $gate;
	}

	/**
	 * Checks the suspension list for the ip
	 * @return boolean
	 */
	static function suspended() {
		if ($block = getOption('ipBlocker_forbidden')) {
			$block = getSerializedArray($block);
			if (array_key_exists($ip = getUserIP(), $block)) {
				if ($block[$ip] < (time() - getOption('ipBlocker_timeout') * 60)) {
					// cooloff period passed
					unset($block[$ip]);
					if (count($block) > 0) {
						setOption('ipBlocker_forbidden', serialize($block));
					} else {
						setOption('ipBlocker_forbidden', NULL);
					}
				} else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Monitors front end access and excludes access if appropriate
	 * @param string $path
	 * @return string
	 */
	static function load() {
		if (self::blocked() || self::suspended()) {
			sleep(30);
			header("HTTP/1.0 403 " . gettext("Forbidden"));
			header("Status: 403 " . gettext("Forbidden"));
			exitZP(); //	terminate the script with no output
		}
	}

}
?>
