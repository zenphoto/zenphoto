<?php

/*
 * This plugin is used to extend the gallery database table fields. The
 * administrative tabs for the objects will have input items for these new fields.
 * They will be placed in the proximate location of the "custom data" field on the page.
 *
 * Fields added to searchable objects will be included in the list of selectable search
 * fields. They will be enabled in the list by default. The standard search
 * form allows a visitor to choose to disable the field for a particular search.
 *
 * Since the objects are not directly aware of these new fields, themes
 * must use the "get()" methods to retrieve the content for display. E.g.
 * <code>echo $_zp_current_album->get('new_field');</code>
 *
 * Fields are defined in the class as a multi-dimensional array, one row per
 * object/field. The elements of each row are:
 *
 * "table" is the database table name (without prefix) of the object to which the field is to be added.
 * "name" is the MySQL field name for the new field
 * "desc" is the "display name" of the field
 * "type" is the database field type: int, varchar, tinytext, text, mediumtext, and longtext.
 * "size" is the byte size of the varchar or int field (it is not needed for other types)
 *
 * Database
 * fields must conform to {@link http://dev.mysql.com/doc/refman/5.0/en/identifiers.html MySQL field naming rules}.
 * If fields are subsequently removed from this array, they will be dropped from the database.
 *
 * <b>NOTE:</b> you must run setup to cause changes to be made to the database.
 * (Database changes should not be made on an active site. You should close the site
 * when you run setup.)
 *
 * If a field already exists in the database the database definition will stand.
 * If you need to change the type or size of a field you must remove it from the
 * array (commenting it out works), run setup, add back the field with the new
 * definition, and run setup again. This process does delete any stored data, so
 * you may want to use the backup/restore facility to save and later restore
 * existing field data.
 *
 * If you disable the plugin and run setup, all fields defined will be removed
 * from the database.
 *
 * Instructions for cloning this plugin:
 *
 * You should copy this script and rename it to whatever you want to call your custom
 * version--say xyzzyCustomFieldExtender.php
 *
 * Be sure to change the class name to something unique--say class xyzzyCustomFieldExtender.
 * Also rename the two functions <code>getCustomField</code> and <code>printCustomField</code>.
 * For instance <code>getXyzzyField</code> and <code>printXyzzyField</code>. These changes
 * allow your plugin to co-exist with other custom field extender plugins.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage example
 * @category package
 *
 */
$plugin_is_filter = /* defaultExtension( */ 5 | CLASS_PLUGIN /* ) */; //	if you have such a plugin you probably want to use it
$plugin_description = gettext('Adds user defined fields to database tables');
$plugin_notice = gettext('This plugin attaches the "custom data" filters. The raw custom data field is not editable when the plugin has fields defined for the object.');
$plugin_author = "Stephen Billard (sbillard)";

if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');
} else {
	require_once(stripSuffix(__FILE__) . '/fieldExtender.php');
}

//NOTE: you should choose a unique class name to be sure not to conflict with another custom field extender plugin
class customFieldExtender extends fieldExtender {

	static $fields = array(
					array('table' => 'albums', 'name' => 'Finish_Disc', 'desc' => 'Finish Disc', 'type' => 'varchar', 'size' => 50),
					array('table' => 'news', 'name' => 'Finish_Lip', 'desc' => 'Finish Lip', 'type' => 'varchar', 'size' => 50),
					array('table' => 'images', 'name' => 'Option', 'desc' => 'Option', 'type' => 'varchar', 'size' => 50),
					array('table' => 'news_categories', 'name' => 'Rear_Size', 'desc' => 'Front Size', 'type' => 'varchar', 'size' => 50),
					array('table' => 'pages', 'name' => 'Rear_Size', 'desc' => 'Rear Size', 'type' => 'varchar', 'size' => 50)
	);

	function __construct() {
		parent::constructor('customFieldExtender', self::$fields);
	}

	static function addToSearch($list) {
		return parent::_addToSearch($list, self::$fields);
	}

	static function adminSave($updated, $userobj, $i, $alter) {
		parent::_adminSave($updated, $userobj, $i, $alter, self::$fields);
	}

	static function adminEdit($html, $userobj, $i, $background, $current) {
		return parent::_adminEdit($html, $userobj, $i, $background, $current, self::$fields);
	}

	static function mediaItemSave($object, $i) {
		return parent::_mediaItemSave($object, $i, self::$fields);
	}

	static function mediaItemEdit($html, $object, $i) {
		return parent::_mediaItemEdit($html, $object, $i, self::$fields);
	}

	static function cmsItemSave($custom, $object) {
		return parent::_cmsItemSave($custom, $object, self::$fields);
	}

	static function cmsItemEdit($html, $object) {
		return parent::_cmsItemEdit($html, $object, self::$fields);
	}

	static function register() {
		parent::_register('customFieldExtender', self::$fields);
	}

	static function adminNotice($tab, $subtab) {
		parent::_adminNotice($tab, $subtab, 'customFieldExtender');
	}

}

function getCustomField($field, $object = NULL, &$detail = NULL) {
	global $_zp_current_admin_obj, $_zp_current_album, $_zp_current_image
	, $_zp_current_article, $_zp_current_page, $_zp_current_category;

	$objects = $tables = array();
	if (is_null($object)) {
		if (in_context(ZP_IMAGE)) {
			$object = $_zp_current_image;
			$objects[$tables[] = 'albums'] = $_zp_current_album;
		} else if (in_context(ZP_ALBUM)) {
			$object = $_zp_current_album;
		} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
			$object = $_zp_current_article;
			if ($_zp_current_category)
				$objects[$tables[] = 'news_categories'] = $_zp_current_category;
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			$object = $_zp_current_page;
		} else if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
			$object = $_zp_current_category;
		} else {
			zp_error(gettext('There is no defined context, you must pass a comment object.'));
		}
	}
	$tables[] = $object->table;
	$objects[$object->table] = $object;
	$field = strtolower($field);

	var_dump($field);
	var_dump($tables);

	foreach (customFieldExtender::$fields as $try) {
		if ($field == strtolower($try['name']) && in_array($try['table'], $tables)) {
			$detail = $try;
			$object = $objects[$try['table']];
			break;
		}
	}
	if (isset($detail)) {

		var_dump($detail);

		return get_language_string($object->get($detail['name']));
	} else {
		zp_error(gettext('Field not defined.'));
	}
}

function printCustomField($field, $label = NULL, $object = NULL) {
	$detail = NULL;
	$text = getCustomField($field, $object, $detail);
	if (is_null($label)) {
		$label = $detail['desc'] . ': ';
	}
	if (!empty($text)) {
		echo html_encodeTagged($label . $text);
	}
}

if (OFFSET_PATH == 2) { // setup call: add the fields into the database
	new customFieldExtender;
} else {
	customFieldExtender::register();
}
?>
