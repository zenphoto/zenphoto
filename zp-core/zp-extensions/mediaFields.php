<?php
/*
 * This plugin is used to provide the optional media  database table fields. The
 * administrative tabs for the objects will have input items for these  fields.
 * They will be placed in the proximate location of the "custom data" field on the page.
 *
 * Fields added to searchable objects will be included in the list of selectable search
 * fields. They will be enabled in the list by default. The standard search
 * form allows a visitor to choose to disable the field for a particular search.
 *
 * Note that the image and album objects will still have the methods for getting and
 * setting these fields. But if this plugin is not enabled, these fields will <b>NOT</b> be preserved
 * in the database.
 *
 * Fields are defined in the class as a multi-dimensional array, one row per
 * object/field. The elements of each row are:
 *
 * "table" is the database table name (without prefix) of the object to which the field is to be added.
 * "name" is the MySQL field name for the new field
 * "desc" is the "display name" of the field
 * "type" is the database field type: int, varchar, tinytext, text, mediumtext, and longtext.
 * "size" is the byte size of the varchar or int field (it is not needed for other types)
 * "edit" is is how the content is show on the edit tab. Values: multilingual, normal, function:<i>editor function</i>
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
 * You should copy this script to the user plugin folder if you wish to customize it.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage media
 * @category package
 *
 */
$plugin_is_filter = defaultExtension(5 | CLASS_PLUGIN); //	if you have such a plugin you probably want to use it
$plugin_description = gettext('Adds user defined fields to database tables');
$plugin_notice = gettext('This plugin attaches the "custom data" filters. The raw custom data field is not editable when the plugin has fields defined for the object.');
$plugin_author = "Stephen Billard (sbillard)";

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');

class mediaFields extends fieldExtender {

	static $fields = array(
					array('table' => 'albums', 'name' => 'owner', 'desc' => 'Owner:', 'type' => 'varchar', 'size' => 50, 'edit' => 'function', 'function' => 'mediaFields::owner'),
					array('table' => 'albums', 'name' => 'location', 'desc' => 'Location:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual'),
					array('table' => 'images', 'name' => 'image_watermark', 'desc' => 'Image watermark:', 'type' => 'varchar', 'size' => 50, 'edit' => 'function', 'function' => 'mediaFields::watermark'),
					array('table' => 'images', 'name' => 'owner', 'desc' => 'Owner:', 'type' => 'varchar', 'size' => 50, 'edit' => 'function', 'function' => 'mediaFields::owner'),
					array('table' => 'images', 'name' => 'location', 'desc' => 'Location:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual'),
					array('table' => 'images', 'name' => 'city', 'desc' => 'City:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual'),
					array('table' => 'images', 'name' => 'state', 'desc' => 'State:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual'),
					array('table' => 'images', 'name' => 'country', 'desc' => 'Country:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual'),
					array('table' => 'images', 'name' => 'copyright', 'desc' => 'Copyright:', 'type' => 'varchar', 'size' => 50, 'edit' => 'multilingual')
	);

	function __construct() {
		parent::constructor('mediaFields', self::$fields);
		//  for translations need to define the display names
		gettext('Owner:');
		gettext("Location:");
		gettext("City:");
		gettext("State:");
		gettext("Country:");
		gettext("Credit:");
		gettext("Copyright:");
		gettext("Image watermark:");
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
		parent::_register('mediaFields', self::$fields);
	}

	static function adminNotice($tab, $subtab) {
		parent::_adminNotice($tab, $subtab, 'mediaFields');
	}

	static function owner($obj, $instance, $field, $type) {
		if ($type == 'save') {
			return sanitize($instance . '-' . $_POST[$field['name']]);
		} else {
			$item = NULL;
			if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
				ob_start();
				?>
				<select name="<?php echo $instance . '-' . $field['name']; ?>">
					<?php echo admin_album_list($obj->getOwner()); ?>
				</select>
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
	}

	static function watermark($image, $currentimage, $field, $type) {
		if ($type == 'save') {
			return sanitize($instance . '-' . $_POST[$field['name']]);
		} else {
			$item = NULL;
			if ($image->isMyItem($image->manage_some_rights)) {
				$current = $image->getWatermark();
				ob_start();
				?>
				<select id="image_watermark-<?php echo $currentimage; ?>" name="<?php echo $currentimage; ?>-image_watermark" onclick="toggleWMUse(<?php echo $currentimage; ?>);">
					<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
					<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
					<?php
					$watermarks = getWatermarks();
					generateListFromArray(array($current), $watermarks, false, false);
					?>
				</select>
				<?php
				if ($current == '')
					$displaystyle = 'none';
				else
					$displaystyle = 'inline';
				?>
				<span id="WMUSE_<?php echo $currentimage; ?>" style="display:<?php echo $displaystyle; ?>">
					<?php $wmuse = $image->getWMUse(); ?>
					<label><input type="checkbox" value="1" id="wm_image-<?php echo $currentimage; ?>" name="wm_image-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_IMAGE) echo 'checked="checked"'; ?> /><?php echo gettext('image'); ?></label>
					<label><input type="checkbox" value="1" id="wm_thumb-<?php echo $currentimage; ?>" name="wm_thumb-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_THUMB) echo 'checked="checked"'; ?> /><?php echo gettext('thumb'); ?></label>
					<label><input type="checkbox" value="1" id="wm_full-<?php echo $currentimage; ?>" name="wm_full-<?php echo $currentimage; ?>" <?php if ($wmuse & WATERMARK_FULL) echo 'checked="checked"'; ?> /><?php echo gettext('full image'); ?></label>
				</span>
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
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
	new mediaFields;
} else {
	mediaFields::register();
}
?>
