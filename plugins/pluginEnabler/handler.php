<?php

/**
 * Bulk enable/disable of plugins
 * @package core
 */
// force UTF-8 Ø

define("OFFSET_PATH", 3);
require('../../zp-core/admin-globals.php');

admin_securityChecks(ADMIN_RIGHTS, $return = currentRelativeURL());
XSRFdefender('pluginEnabler');

if (isset($_REQUEST['pluginsEnable'])) {

	$paths = getPluginFiles('*.php');
	$pluginlist = array_keys($paths);

	switch ($setting = sanitize($_GET['pluginsEnable'])) {
		case 0:
			$report = gettext('Plugins disabled');
			break;
		case 1:
			$report = gettext('Standard plugins enabled');
			break;
		case 2:
			$report = gettext('Remembered plugins enabled');
			$savedlist = getSerializedArray(getOption('pluginEnabler_currentset'));
			break;
		case 3:
			$report = gettext('All plugins enabled');
			break;
		case 4:
			setOption('pluginEnabler_currentset ', serialize(array_keys(getEnabledPlugins())));
			$report = gettext('Current enabled plugins remembered');
			break;
	}

	if ($setting <= 3) {
		foreach ($pluginlist as $extension) {
			if ($extension != 'pluginEnabler') {
				$opt = 'zp_plugin_' . $extension;
				$was = (int) (getOption($opt) && true);

				switch ($setting) {
					case 0:
						$is = 0;
						break;
					case 1:
						$is = (int) (strpos($paths[$extension], ZENFOLDER) !== false && $extension != 'show_not_logged-in');
						break;
					case 2:
						$is = in_array($extension, $savedlist);
						break;
					case 3:
						$is = 1;
						break;
					case 4:
						die('cant be here');
				}

				if ($was == $is) {
					$action = 1;
				} else if ($was) {
					$action = 2;
				} else {
					$action = 3;
				}

				$f = str_replace('-', '_', $extension) . '_enable';
				switch ($action) {
					case 1:
						//no change
						break;
					case 2:
						//going from enabled to disabled
						require_once($paths[$extension]);
						if (function_exists($f)) {
							$f(false);
						}
						setOption($opt, 0);
						break;
					case 3:
						//going from disabled to enabled
						$pluginStream = file_get_contents($paths[$extension]);
						if ($setting != 2) {
							if ($str = isolate('$plugin_disable', $pluginStream)) {
								eval($str);
								if ($plugin_disable) {
									continue;
								}
							}
						}
						$plugin_is_filter = 1 | THEME_PLUGIN;
						$str = isolate('$plugin_is_filter', $pluginStream);
						if ($str) {
							eval($str);
							if ($plugin_is_filter < THEME_PLUGIN) {
								if ($plugin_is_filter < 0) {
									$plugin_is_filter = abs($plugin_is_filter) | THEME_PLUGIN | ADMIN_PLUGIN;
								} else {
									if ($plugin_is_filter == 1) {
										$plugin_is_filter = 1 | THEME_PLUGIN;
									} else {
										$plugin_is_filter = $plugin_is_filter | CLASS_PLUGIN;
									}
								}
							}
						}
						$option_interface = NULL;
						require_once($paths[$extension]);
						if ($option_interface && is_string($option_interface)) {
							$if = new $option_interface; //	prime the default options
						}
						if (function_exists($f)) {
							$f(true);
						}
						setOption($opt, $plugin_is_filter);
						break;
				}
			}
		}
	}
}
header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report=' . $report);
?>