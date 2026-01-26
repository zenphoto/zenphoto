<?php

/**
 * Class for modifying the config file
 * 
 * @since 1.7 Reworked as class replacing functions-config.php
 * 
 * @package zpcore\classes\helpers
 */
class config {

	/**
	 * Updates an item in the configuration file
	 * 
	 * @since 1.7 moved from functions-config.php
	 * 
	 * @param unknown_type $item
	 * @param unknown_type $value
	 * @param unknown_type $quote
	 */
	static function updateConfigItem($item, $value, $zp_cfg, $quote = true) {
		if ($quote) {
			$value = '"' . $value . '"';
		}
		$i = strpos($zp_cfg, $item);
		if ($i === false) {
			$parts = preg_split('~\/\*.*Do not edit below this line.*\*\/~', $zp_cfg);
			if (isset($parts[1])) {
				$zp_cfg = $parts[0] . "\$conf['" . $item . "'] = " . $value . ";\n/** Do not edit below this line. **/" . $parts[1];
			} else {
				zp_error(gettext('The Zenphoto configuration file is corrupt. You will need to restore it from a backup.'));
			}
		} else {
			$i = strpos($zp_cfg, '=', $i);
			$j = strpos($zp_cfg, "\n", $i);
			$zp_cfg = substr($zp_cfg, 0, $i) . '= ' . $value . ';' . substr($zp_cfg, $j);
		}
		return $zp_cfg;
	}

	/**
	 * backs-up and updates the Zenphoto configuration file
	 * 
	 * @since 1.7 moved from functions-config.php
	 * 
	 * @param string $zp_cfg
	 */
	static function storeConfig($zp_cfg) {
		$mod = fileperms(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE) & 0777;
		$configfile_nosuffix = str_replace(strrchr(CONFIGFILE, "."), '', CONFIGFILE);
		$backup = SERVERPATH . '/' . DATA_FOLDER . '/' . $configfile_nosuffix . '.bak.php';
		@rename(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $backup);
		@chmod($backup, $mod);
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $zp_cfg);
		@chmod(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE, $mod);
	}
}
