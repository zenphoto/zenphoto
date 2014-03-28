<?php

/*
 * This plugin is used to extend the administrator table to add user address fields.
 *
 * <b>NOTE:</b> you must run setup after enabling or disabling this plugin to cause changes to
 * be made to the database. (Database changes should not be made on an active site.
 * You should close the site when you run setup.) If you disable the plugin all data
 * contained in the fields will be discarded.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage users
 *
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Adds user address fields');
$plugin_author = "Stephen Billard (sbillard)";

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');

class userAddressFields extends fieldExtender {

	function __construct() {
		global $_userAddressFields;
		$firstTime = extensionEnabled('userAddressFields') && is_null(getOption('userAddressFields_addedFields'));
		parent::constructor('userAddressFields', self::fields());
		if ($firstTime) { //	migrate the custom data user data
			$result = query('SELECT * FROM ' . prefix('administrators') . ' WHERE `valid`!=0');
			if ($result) {
				while ($row = db_fetch_assoc($result)) {
					$custom = getSerializedArray($row['custom_data']);
					if (!empty($custom)) {
						$sql = 'UPDATE ' . prefix('administrators') . ' SET ';
						foreach ($custom as $field => $val) {
							$sql.= '`' . $field . '`=' . db_quote($val) . ',';
						}
						$sql .= '`custom_data`=NULL WHERE `id`=' . $row['id'];
						query($sql);
					}
				}
				db_free_result($result);
			}
		}
	}

	static function fields() {
		return array(
						array('table' => 'administrators', 'name' => 'street', 'desc' => gettext('Street'), 'type' => 'tinytext'),
						array('table' => 'administrators', 'name' => 'website', 'desc' => gettext('Website'), 'type' => 'tinytext'),
						array('table' => 'administrators', 'name' => 'city', 'desc' => gettext('City'), 'type' => 'tinytext'),
						array('table' => 'administrators', 'name' => 'country', 'desc' => gettext('Country'), 'type' => 'tinytext'),
						array('table' => 'administrators', 'name' => 'state', 'desc' => gettext('State'), 'type' => 'tinytext'),
						array('table' => 'administrators', 'name' => 'postal', 'desc' => gettext('Postal code'), 'type' => 'tinytext')
		);
	}

	static function addToSearch($list) {
		return parent::_addToSearch($list, self::fields());
	}

	static function adminSave($updated, $userobj, $i, $alter) {
		parent::_adminSave($updated, $userobj, $i, $alter, self::fields());
	}

	static function adminEdit($html, $userobj, $i, $background, $current) {
		return parent::_adminEdit($html, $userobj, $i, $background, $current, self::fields());
	}

	static function mediaItemSave($object, $i) {
		return parent::_mediaItemSave($object, $i, self::fields());
	}

	static function mediaItemEdit($html, $object, $i) {
		return parent::_mediaItemEdit($html, $object, $i, self::fields());
	}

	static function zenpageItemSave($custom, $object) {
		return parent::_zenpageItemSave($custom, $object, self::fields());
	}

	static function zenpageItemEdit($html, $object) {
		return parent::_zenpageItemEdit($html, $object, self::fields());
	}

	static function register() {
		parent::_register('userAddressFields', self::fields());
	}

	static function adminNotice($tab, $subtab) {
		parent::_adminNotice($tab, $subtab, 'userAddressFields');
	}

	static function getCustomData($obj) {
		return parent::_getCustomData($obj, self::fields());
	}

	static function setCustomData($obj, $values) {
		parent::_setCustomData($obj, $values);
	}

}

if (OFFSET_PATH == 2) { // setup call: add the fields into the database
	setOptionDefault('zp_plugin_userAddressFields', $plugin_is_filter);
	new userAddressFields;
} else {
	userAddressFields::register();
}
?>
