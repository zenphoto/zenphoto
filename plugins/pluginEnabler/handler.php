<?php

/**
 * Bulk enable/disable of plugins
 * @package core
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/zp-core/admin-globals.php");

admin_securityChecks(ADMIN_RIGHTS, $return = currentRelativeURL());

XSRFdefender('pluginEnabler');
if (isset($_GET['pluginsRemember'])) {
	setOption('pluginEnabler_currentset', serialize(array_keys(getEnabledPlugins())));
	$report = gettext('Current enabled plugins remembered');
}
if (isset($_GET['pluginsEnable'])) {

	$paths = getPluginFiles('*.php');
	$pluginlist = array_keys($paths);

	switch ($setting = sanitize_numeric($_GET['pluginsEnable'])) {
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
	}
	foreach ($pluginlist as $extension) {
		if ($extension != 'pluginEnabler') {
			$opt = 'zp_plugin_' . $extension;
			switch ($setting) {
				case 1:
					if (strpos($paths[$extension], ZENFOLDER) !== false && $extension != 'show_not_logged-in') {
						$enable = true;
						break;
					}
				case 0:
					$enable = false;
					break;
				case 2:
					if (!in_array($extension, $savedlist)) {
						$enable = false;
						break;
					}
				case 3:
					$enable = true;
					break;
			}
			if ($enable) {
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
				if ($str = isolate('$plugin_is_filter', $pluginStream)) {
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
				setOption($opt, $plugin_is_filter);
				require_once($paths[$extension]);
			} else {
				setOption($opt, 0);
			}
		}
	}
}
header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report=' . $report);
?>