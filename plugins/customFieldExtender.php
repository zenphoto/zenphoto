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
 * The <i>editor function</i> will be passed three parameters: the object, the $_POST instance, the field array,
 * and the action: "edit" or "save". The function must return the processed data to be displayed or saved.
 *
 * Database
 * fields must conform to {@link http://dev.mysql.com/doc/refman/5.0/en/identifiers.html MySQL field naming rules}.
 * If fields are subsequently removed from this array, they will be dropped from the database.
 *
 * <b>NOTE:</b> you must run setup to cause changes to be made to the database.
 * (Database changes should not be made on an active site. You should close the site
 * when you run setup.)
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

$plugin_author = "Stephen Billard (sbillard)";

if (file_exists(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');
} else {
	require_once(stripSuffix(__FILE__) . '/fieldExtender.php');
}

//NOTE: you should choose a unique class name to be sure not to conflict with another custom field extender plugin
class customFieldExtender extends fieldExtender {
	/*
	 * For definition of this array see fieldExtender.php in the extensions/common folder
	 */

	static $fields = array(
			array(
					'table' => 'albums',
					'name' => 'Album_Custom',
					'desc' => 'Custom album field',
					'type' => 'varchar', 'size' => 50,
					'edit' => 'multilingual'),
			array(
					'table' => 'albums',
					'name' => 'custom_field2',
					'desc' => 'Custom field 2',
					'type' => 'varchar', 'size' => 75,
					'searchDefault' => 1,
					'bulkAction' => array(
							'Custom field 2' => 'mass_customText_data'
					)
			),
			array(
					'table' => 'images',
					'name' => 'custom_field1',
					'desc' => 'Custom field 1',
					'type' => 'varchar', 'size' => 75,
					'searchDefault' => 1,
					'edit' => 'function',
					'function' => 'customFieldExtender::custom_option'
			),
			array('table' => 'images',
					'name' => 'custom_field2',
					'desc' => 'Custom field 2',
					'type' => 'varchar', 'size' => 75,
					'searchDefault' => 1,
					'bulkAction' => array(
							'Custom field 2' => 'mass_customText_data'
					)
			),
			array('table' => 'news',
					'name' => 'News_Custom',
					'desc' => 'Custom News field',
					'type' => 'varchar', 'size' => 50
			),
			array('table' => 'news',
					'name' => 'custom_field2',
					'desc' => 'Custom field 2',
					'type' => 'varchar', 'size' => 75,
					'searchDefault' => 1,
					'bulkAction' => array(
							'Custom field 2' => 'mass_customText_data'
					)
			),
			array('table' => 'pages',
					'name' => 'Page_custom',
					'desc' => 'Custom Page field',
					'type' => 'varchar', 'size' => 50
			),
			array('table' => 'pages',
					'name' => 'custom_field2',
					'desc' => 'Custom field 2',
					'type' => 'text',
					'searchDefault' => 1,
					'bulkAction' => array(
							'Custom field 2' => 'mass_customTextarea_data'
					)
			)
	);

	function __construct() {
		parent::constructor('customFieldExtender', self::$fields);
	}

	static function fields() {
		return self::$fields;
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
		parent::_register(__CLASS__, self::$fields);
	}

	static function bulkAdmin($checkarray) {
		return parent::bulkActions($checkarray, 'administrators', self::fields());
	}

	static function bulkAlbum($checkarray) {
		return parent::bulkActions($checkarray, 'albums', self::fields());
	}

	static function bulkImage($checkarray) {
		return parent::bulkActions($checkarray, 'images', self::fields());
	}

	static function bulkArticle($checkarray) {
		return parent::bulkActions($checkarray, 'news', self::fields());
	}

	static function bulkPage($checkarray) {
		return parent::bulkActions($checkarray, 'pages', self::fields());
	}

	static function bulkAlbumSave($result, $action) {
		return parent::bulkSave($result, $action, 'albums', NULL, self::fields());
	}

	static function bulkImageSave($result, $action, $album) {
		return parent::bulkSave($result, $action, 'images', $album, self::fields());
	}

	static function bulkCMSSave($result, $action, $type) {
		return parent::bulkSave($result, $action, $type, NULL, self::fields());
	}

	static function custom_option($obj, $instance, $field, $type) {
		if ($type == 'save') {
			return sanitize($_POST[$instance . '-' . $field['name']]);
		} else {
			$item = $obj->get($field['name']);
			if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
				ob_start();
				?>
				<select name="<?php echo $instance . '-' . $field['name']; ?>">
					<?php echo admin_album_list($item); ?>
				</select>
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
	}

}

function customFieldExtender_enable($enabled) {
	if (!$enabled) {
		requestSetup('customFieldExtender');
	}
}

function getCustomField($field, $object = NULL, &$detail = NULL) {
	$detail = NULL;
	return fieldExtender::getField($field, $object, $detail, customFieldExtender::$fields);
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
