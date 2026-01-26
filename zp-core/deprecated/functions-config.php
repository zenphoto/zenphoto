<?php
/**
 * Config functions
 * 
 * @deprecated 2.0 See class-rewrite.php instead
 * 
 * @package core
 * @subpackage functions\functions-config
 */
 
/**
 * Updates an item in the configuration file
 * 
* @deprecated 2.0 Use config::updateConfigFile() instead
 * 
 * @param unknown_type $item
 * @param unknown_type $value
 * @param unknown_type $quote
 */
function updateConfigItem($item, $value, $zp_cfg, $quote = true) {
	deprecationNotice(gettext('Use config::updateConfigFile() instead'));
	return config::updateConfigFile();
}

/**
 * backs-up and updates the Zenphoto configuration file
 * 
 * @deprecated 2.0 Use config::storeConfig() instead
 *
 * @param string $zp_cfg
 */
function storeConfig($zp_cfg) {
	deprecationNotice(gettext('Use config::storeConfig() instead'));
	config::storeConfig();
}