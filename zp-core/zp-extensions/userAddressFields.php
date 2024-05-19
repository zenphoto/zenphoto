<?php

/**
 * This plugin is used to extend the administrator table to add user address fields.
 *
 * <b>NOTE:</b> you must run setup after enabling or disabling this plugin to cause changes to
 * be made to the database. (Database changes should not be made on an active site.
 * You should close the site when you run setup.) If you disable the plugin all data
 * contained in the fields will be discarded.
 * @deprecated 2.0 
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\useraddressfields
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Adds user address fields');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_category = gettext('Users');
$plugin_deprecated = true;

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');

/**
 * @deprecated 2.0 
 * @package zpcore\plugins\useraddressfields
 */
class userAddressFields extends fieldExtender {

	/**
	 * @deprecated 2.0 
	 * @global type $_userAddressFields
	 * @global type $_zp_db
	 */
	function __construct() {
		global $_userAddressFields, $_zp_db;
		$firstTime = extensionEnabled('userAddressFields') && is_null(getOption('userAddressFields_addedFields'));
		parent::constructor('userAddressFields', self::fields());
		if ($firstTime) { //	migrate the custom data user data
			$result = $_zp_db->query('SELECT * FROM ' . $_zp_db->prefix('administrators') . ' WHERE `valid`!=0');
			if ($result) {
				while ($row = $_zp_db->fetchAssoc($result)) {
					$custom = getSerializedArray($row['custom_data']);
					if (!empty($custom)) {
						$sql = 'UPDATE ' . $_zp_db->prefix('administrators') . ' SET ';
						foreach ($custom as $field => $val) {
							$sql .= '`' . $field . '`=' . $_zp_db->quote($val) . ',';
						}
						$sql .= '`custom_data`=NULL WHERE `id`=' . $row['id'];
						$_zp_db->query($sql);
					}
				}
				$_zp_db->freeResult($result);
			}
		}
	}

	/**
	 * @deprecated 2.0 
	 * @return type
	 */
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

	/**
	 * @deprecated 2.0 
	 * @param type $list
	 * @return type
	 */
	static function addToSearch($list) {
		return parent::_addToSearch($list, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $updated
	 * @param type $userobj
	 * @param type $i
	 * @param type $alter
	 */
	static function adminSave($updated, $userobj, $i, $alter) {
		parent::_adminSave($updated, $userobj, $i, $alter, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $html
	 * @param type $userobj
	 * @param type $i
	 * @param type $background
	 * @param type $current
	 * @return type
	 */
	static function adminEdit($html, $userobj, $i, $background, $current) {
		return parent::_adminEdit($html, $userobj, $i, $background, $current, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $object
	 * @param type $i
	 * @return type
	 */
	static function mediaItemSave($object, $i) {
		return parent::_mediaItemSave($object, $i, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $html
	 * @param type $object
	 * @param type $i
	 * @return type
	 */
	static function mediaItemEdit($html, $object, $i) {
		return parent::_mediaItemEdit($html, $object, $i, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $custom
	 * @param type $object
	 * @return type
	 */
	static function zenpageItemSave($custom, $object) {
		return parent::_zenpageItemSave($custom, $object, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $html
	 * @param type $object
	 * @return type
	 */
	static function zenpageItemEdit($html, $object) {
		return parent::_zenpageItemEdit($html, $object, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 */
	static function register() {
		parent::_register('userAddressFields', self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $tab
	 * @param type $subtab
	 */
	static function adminNotice($tab, $subtab) {
		parent::_adminNotice($tab, $subtab, 'userAddressFields');
	}

	/**
	 * @deprecated 2.0 
	 * @param type $obj
	 * @return type
	 */
	static function getCustomData($obj) {
		return parent::_getCustomData($obj, self::fields());
	}

	/**
	 * @deprecated 2.0 
	 * @param type $obj
	 * @param type $values
	 */
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
