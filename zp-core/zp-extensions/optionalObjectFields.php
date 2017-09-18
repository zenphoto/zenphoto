<?php
/**
 * This plugin is used to provide for <i>object</i> optional database table fields. The
 * administrative tabs for the objects will have input items for these fields.
 * They will be placed in the proximate location of the "custom data" field on the page.
 *
 * Fields added to searchable objects will be included in the list of selectable search
 * fields. They will be enabled in the list by default. The standard search
 * form allows a visitor to choose to disable the field for a particular search.
 *
 * The zenPhoto20 objects will still have the methods for getting and
 * setting these fields. But if this plugin is not enabled, these fields will <b>NOT</b> be preserved
 * in the database.
 *
 * <b>NOTE:</b> you must run setup to cause changes to be made to the database.
 * (Database changes should not be made on an active site. You should close the site
 * when you run setup.)
 *
 * If you disable the plugin and run setup, fields defined will be removed
 * from the database.
 *
 * This plugin provides for the following fields:
 *
 * 	<dl>
 * 		<dt><b>albums table</b></dt>
 * 			<dd>owner</dd> <dd>date</dd> <dd>location</dd> <dd>tags</dd> <dd>codeblock</dd>
 *
 * 		<dt><b>images table</b></dt>
 * 			<dd>owner</dd> <dd>date</dd> <dd>location</dd> <dd>album_thumb</dd> <dd>watermark</dd>
 * 			<dd>watermark_use</dd> <dd>location</dd> <dd>city</dd> <dd>state</dd> <dd>country</dd>
 * 			<dd>credit</dd> <dd>copyright</dd> <dd>tags</dd> <dd>codeblock</dd>
 *
 * 		<dt><b>pages table</b></dt>
 * 			<dd>extracontent</dd> <dd>tags</dd> <dd>codeblock</dd>
 *
 *    <dt><b>news table</b></dt>
 * 			<dd>extracontent</dd> <dd>tags</dd> <dd>codeblock</dd>
 * 	</dl>
 *
 * You should copy this script to the user plugin folder if you wish to customize which fields are provided.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 * @category package
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
$plugin_is_filter = defaultExtension(1 | CLASS_PLUGIN); //	we want this done last so the codeblocks go at the end
$plugin_description = gettext('Handles the "optional" object fields');
$plugin_notice = (extensionEnabled('optionalObjectFields')) ? '' : gettext('<strong>IMPORTANT</strong>: This plugin enables the "tags" database fields. If disabled the admin <em>tags</em> tab will not be present. Click on the <em>More information</em> icon for details.');
$plugin_author = "Stephen Billard (sbillard)";

require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/fieldExtender.php');

class optionalObjectFields extends fieldExtender {

	static function fields() {
		/*
		 * For definition of this array see fieldExtender.php in the extensions/common folder
		 */
		return array(
				/*
				 * album fields
				 */
				array(
						'table' => 'albums',
						'name' => 'owner',
						'desc' => gettext('Owner'),
						'type' => 'varchar', 'size' => 64,
						'edit' => 'function',
						'function' => 'optionalObjectFields::owner',
						'default' => 'NULL',
						'bulkAction' => array(
								gettext('Change owner') => array('name' => 'changeowner', 'action' => 'mass_owner_data')
						)
				),
				array(
						'table' => 'albums',
						'name' => 'date',
						'desc' => gettext('Date'),
						'type' => 'datetime',
						'edit' => 'function',
						'function' => 'optionalObjectFields::date'
				),
				array(
						'table' => 'albums',
						'name' => 'location',
						'desc' => gettext('Location'),
						'type' => 'text',
						'searchDefault' => 1,
						'edit' => 'multilingual'
				),
				array(
						'table' => 'albums',
						'name' => 'tags',
						'desc' => gettext('Tags'),
						'type' => NULL,
						'searchDefault' => 1,
						'edit' => 'function',
						'function' => 'optionalObjectFields::tags',
						'bulkAction' => array(
								gettext('Add tags') => array('name' => 'addtags', 'action' => 'mass_tags_data'),
								gettext('Clear tags') => 'cleartags',
								gettext('Add tags to images') => array('name' => 'alltags', 'action' => 'mass_tags_data'),
								gettext('Clear tags of images') => 'clearalltags'
						)
				),
				array(
						'table' => 'albums',
						'name' => 'codeblock',
						'desc' => gettext('Codeblocks'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::codeblocks'
				),
				/*
				 * image fields
				 */
				array(
						'table' => 'images',
						'name' => 'owner',
						'desc' => gettext('Owner'),
						'type' => 'varchar', 'size' => 64,
						'edit' => 'function',
						'function' => 'optionalObjectFields::owner', 'default' => 'NULL',
						'bulkAction' => array(
								gettext('Change owner') => array('name' => 'changeowner', 'action' => 'mass_owner_data')
						)
				),
				array(
						'table' => 'images',
						'name' => 'album_thumb',
						'desc' => gettext('Set as thumbnail for'),
						'type' => NULL, 'edit' => 'function',
						'function' => 'optionalObjectFields::thumb'
				),
				array(
						'table' => 'images',
						'name' => 'date',
						'desc' => gettext('Date'),
						'type' => 'datetime',
						'edit' => 'function',
						'function' => 'optionalObjectFields::date'
				),
				array(
						'table' => 'images',
						'name' => 'watermark',
						'desc' => gettext('Image watermark'),
						'type' => 'varchar', 'size' => 255,
						'edit' => 'function',
						'function' => 'optionalObjectFields::watermark',
						'default' => NULL
				),
				array(
						'table' => 'images',
						'name' => 'watermark_use',
						'desc' => NULL,
						'type' => 'int', 'size' => 1,
						'edit' => NULL,
						'attribute' => 'UNSIGNED',
						'default' => 7
				),
				array(
						'table' => 'images',
						'name' => 'location',
						'desc' => gettext('Location'),
						'type' => 'text',
						'searchDefault' => 1,
						'edit' => 'multilingual'
				),
				array(
						'table' => 'images',
						'name' => 'city',
						'desc' => gettext('City'),
						'type' => 'tinytext',
						'searchDefault' => 1,
						'size' => 50,
						'edit' => 'multilingual'
				),
				array(
						'table' => 'images',
						'name' => 'state',
						'desc' => gettext('State'),
						'type' => 'tinytext',
						'searchDefault' => 1,
						'size' => 50,
						'edit' => 'multilingual'
				),
				array(
						'table' => 'images',
						'name' => 'country',
						'desc' => gettext('Country'),
						'type' => 'tinytext', 'size' => 50,
						'searchDefault' => 1,
						'edit' => 'multilingual'
				),
				array(
						'table' => 'images',
						'name' => 'credit',
						'desc' => gettext('Credit'),
						'type' => 'text',
						'edit' => 'multilingual'
				),
				array('table' => 'images',
						'name' => 'copyright',
						'desc' => gettext('Copyright'),
						'type' => 'text',
						'edit' => 'multilingual'
				),
				array('table' => 'images',
						'name' => 'tags',
						'desc' => gettext('Tags'),
						'type' => NULL,
						'searchDefault' => 1,
						'edit' => 'function',
						'function' => 'optionalObjectFields::tags',
						'bulkAction' => array(
								gettext('Add tags') => array('name' => 'addtags', 'action' => 'mass_tags_data'),
								gettext('Clear tags') => 'cleartags'
						)
				),
				array(
						'table' => 'images',
						'name' => 'codeblock',
						'desc' => gettext('Codeblocks'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::codeblocks'
				),
				/*
				 * category fielsds
				 */
				array(
						'table' => 'news_categories',
						'name' => 'tags',
						'desc' => gettext('Tags'),
						'type' => NULL,
						'searchDefault' => 1,
						'edit' => 'function',
						'function' => 'optionalObjectFields::tags',
						'bulkAction' => array(
								gettext('Add tags to articles') => array('name' => 'alltags', 'action' => 'mass_tags_data'),
								gettext('Clear tags of articles') => 'clearalltags'
						)
				),
				/*
				 * page fields
				 */
				array(
						'table' => 'pages',
						'name' => 'extracontent',
						'desc' => gettext('Extra Content'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::extracontent'
				),
				array('table' => 'pages',
						'name' => 'codeblock',
						'desc' => gettext('Codeblocks'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::codeblocks'
				),
				array(
						'table' => 'pages',
						'name' => 'tags',
						'desc' => gettext('Tags'),
						'type' => NULL, 'searchDefault' => 1,
						'edit' => 'function',
						'function' => 'optionalObjectFields::tags',
						'bulkAction' => array(
								gettext('Add tags') => array('name' => 'addtags', 'action' => 'mass_tags_data'),
								gettext('Clear tags') => 'clearalltags'
						)
				),
				/*
				 * news article fields
				 */
				array(
						'table' => 'news',
						'name' => 'extracontent',
						'desc' => gettext('Extra Content'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::extracontent'
				),
				array(
						'table' => 'news',
						'name' => 'codeblock',
						'desc' => gettext('Codeblocks'),
						'type' => 'text',
						'edit' => 'function',
						'function' => 'optionalObjectFields::codeblocks'
				),
				array(
						'table' => 'news',
						'name' => 'tags',
						'desc' => gettext('Tags'),
						'type' => NULL,
						'searchDefault' => 1,
						'edit' => 'function',
						'function' => 'optionalObjectFields::tags',
						'bulkAction' => array(
								gettext('Add tags') => array('name' => 'addtags', 'action' => 'mass_tags_data'),
								gettext('Clear tags') => 'clearalltags'
						)
				)
		);
	}

	function __construct() {
		$protected = array('date', 'owner');
		$fields = self::fields();
		//do not add/remove some critical DB fields
		foreach ($fields as $key => $field) {
			if (in_array($field['name'], $protected))
				unset($fields[$key]);
		}
		parent::constructor('optionalObjectFields', $fields);
//  for translations need to define the display names
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
		if ($i) {
//	only tags on bulk edit tabs
			return parent::_mediaItemEdit($html, $object, $i, array(array('table' => $object->table, 'name' => 'tags', 'desc' => gettext('Tags'), 'type' => NULL, 'edit' => 'function', 'function' => 'optionalObjectFields::tags')));
		} else {
			return parent::_mediaItemEdit($html, $object, $i, self::fields());
		}
	}

	static function cmsItemSave($custom, $object) {
		return parent::_cmsItemSave($custom, $object, self::fields());
	}

	static function cmsItemEdit($html, $object) {
		return parent::_cmsItemEdit($html, $object, self::fields());
	}

	static function register() {
		parent::_register(__CLASS__, self::fields());
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

	static function owner($obj, $instance, $field, $type) {
		if ($type == 'save') {
			if (isset($_POST[$instance . '-' . $field['name']])) {
				return sanitize($_POST[$instance . '-' . $field['name']]);
			} else {
				return NULL;
			}
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

	static function thumb($image, $i, $field, $type) {
		global $albumHeritage;
		if ($type == 'save') {
			if (isset($_POST[$i . '-' . $field['name']])) {
				if ($thumbnail = $_POST[$i . '-' . $field['name']]) {
					$talbum = newAlbum($thumbnail);
					if ($image->imagefolder == $thumbnail) {
						$talbum->setThumb($image->filename);
					} else {
						$talbum->setThumb('/' . $image->imagefolder . '/' . $image->filename);
					}
					$talbum->save();
				}
			}
			return NULL;
		} else {
			$item = NULL;
			if ($image->album->subRights() & MANAGED_OBJECT_RIGHTS_EDIT) {
				ob_start();
				?>
				<select name="<?php echo $i . '-' . $field['name']; ?>" >
					<option value=""></option>
					<?php generateListFromArray(array(), $albumHeritage, false, true); ?>
				</select>
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
	}

	static function date($obj, $instance, $field, $type) {
		global $albumHeritage;
		if ($type == 'save') {
			if (isset($_POST[$instance . '-' . $field['name']])) {
				return sanitize($_POST[$instance . '-' . $field['name']]);
			} else {
				return NULL;
			}
		} else {
			$item = NULL;
			if ($obj->isMyItem($obj->manage_some_rights)) {
				$d = $obj->getDateTime();
				ob_start();
				?>
				<script type="text/javascript">
					// <!-- <![CDATA[
					$(function () {
						$("#datepicker_<?php echo $instance; ?>").datepicker({
							dateFormat: 'yy-mm-dd',
							showOn: 'button',
							buttonImage: 'images/calendar.png',
							buttonText: '<?php echo gettext('calendar'); ?>',
							buttonImageOnly: true
						});
					});
					// ]]> -->
				</script>
				<input type="text" id="datepicker_<?php echo $instance; ?>" size="20" name="<?php echo $instance; ?>-date" value="<?php echo $d; ?>" />
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
	}

	static function watermark($image, $i, $field, $type) {
		if ($type == 'save') {
			if (isset($_POST[$i . '-' . $field['name']])) {
				$wmt = sanitize($_POST[$i . '-' . $field['name']], 3);
				$image->setWatermark($wmt);
				$wmuse = 0;
				if (isset($_POST['wm_image-' . $i]))
					$wmuse = $wmuse | WATERMARK_IMAGE;
				if (isset($_POST['wm_thumb-' . $i]))
					$wmuse = $wmuse | WATERMARK_THUMB;
				if (isset($_POST['wm_full-' . $i]))
					$wmuse = $wmuse | WATERMARK_FULL;
				$image->setWMUse($wmuse);
				$image->save();
			}
			return NULL;
		} else {
			$item = NULL;
			if ($image->isMyItem($image->manage_some_rights)) {
				$current = $image->getWatermark();
				ob_start();
				?>
				<select id="image_watermark-<?php echo $i; ?>" name="<?php echo $i . '-' . $field['name']; ?>" onclick="toggleWMUse(<?php echo $i; ?>);">
					<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected = "selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
					<option value="" <?php if (empty($current)) echo ' selected = "selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
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
				<span id="WMUSE_<?php echo $i; ?>" style="display:<?php echo $displaystyle; ?>">
					<?php $wmuse = $image->getWMUse(); ?>
					<label><input type="checkbox" value="1" id="wm_image-<?php echo $i; ?>" name="wm_image-<?php echo $i; ?>" <?php if ($wmuse & WATERMARK_IMAGE) echo 'checked="checked"'; ?> /><?php echo gettext('image'); ?></label>
					<label><input type="checkbox" value="1" id="wm_thumb-<?php echo $i; ?>" name="wm_thumb-<?php echo $i; ?>" <?php if ($wmuse & WATERMARK_THUMB) echo 'checked="checked"'; ?> /><?php echo gettext('thumb'); ?></label>
					<label><input type="checkbox" value="1" id="wm_full-<?php echo $i; ?>" name="wm_full-<?php echo $i; ?>" <?php if ($wmuse & WATERMARK_FULL) echo 'checked="checked"'; ?> /><?php echo gettext('full image'); ?></label>
				</span>
				<?php
				$item = ob_get_contents();
				ob_end_clean();
			}
			return $item;
		}
	}

	static function tags($object, $i, $field, $type) {
		global $tagsort;
		$i = trim($i, '-');
		if ($type == 'save') {
			$tagsprefix = 'tag_list_tags_' . $i;
			if (isset($_POST[$tagsprefix])) {
				$tags = sanitize($_POST[$tagsprefix]);
			} else {
				$tags = array();
			}
			$found = false;
			$l = strlen($tagsprefix);
			$found = isset($_POST['newtag_tags_' . $i]);
			if ($found) {
				if (isset($_POST['additive_tags_' . $i]) && $_POST['additive_tags_' . $i]) {
					$tags = array_merge($tags, $object->getTags());
				}
				$tags = array_unique($tags);
				$object->setTags($tags);
				$object->save();
			}
			return NULL;
		} else {
			ob_start();
			if ($i) {
				$add = 2;
				$obj = NULL;
				$tags = $object->getTags(false);
				if (count($tags) == 0) {
					echo gettext('No tags assigned');
				} else {
					?>
					<span id = "existing_tags_<?php echo $i; ?>"><?php echo trim(implode(', ', $tags)); ?></span>
					<a id="tag_clear_link_tags_<?php echo $i; ?>" onclick="clearOldTags('tags_<?php echo $i; ?>');">
						<?php echo WASTEBASKET; ?>
					</a>
					<a id="tag_restore_link_tags_<?php echo $i; ?>" onclick="restoreOldTags('tags_<?php echo $i; ?>');" style="display:none;">
						<?php echo PLUS_ICON; ?>
					</a>
					<?php
				}
				echo '<br /><br />' . gettext('Add') . '<br />';
			} else {
				$add = true;
				$obj = $object;
			}
			?>
			<div class="box-edit-unpadded">
				<?php tagSelector($obj, 'tags_' . $i, false, $tagsort, $add, 1); ?>
			</div>
			<?php
			$item = ob_get_contents();
			ob_end_clean();
			return $item;
		}
	}

	static function codeblocks($obj, $instance, $field, $type) {
		if ($type == 'save') {
			if (zp_loggedin(CODEBLOCK_RIGHTS)) {
				processCodeblockSave((int) $instance, $obj);
				$obj->save();
			}
			return NULL;
		} else {
			ob_start();
			printCodeblockEdit($obj, (int) $instance);
			$item = ob_get_contents();
			ob_end_clean();
			return $item;
		}
	}

	static function extracontent($obj, $instance, $field, $type) {
		if ($type == 'save') {
			$extracontent = zpFunctions::updateImageProcessorLink(process_language_string_save("extracontent", EDITOR_SANITIZE_LEVEL));
			$obj->setExtracontent($extracontent);
			$obj->save();
			return NULL;
		} else {
			ob_start();
			print_language_string_list($obj->getExtraContent('all'), 'extracontent', true, NULL, 'extracontent', '100%', 13);
			$item = ob_get_contents();
			ob_end_clean();
			return $item;
		}
	}

}

function optionalObjectFields_enable($enabled) {
	if (!$enabled) {
		requestSetup('optionalObjectFields');
	}
}

if (OFFSET_PATH == 2) { // setup call: add the fields into the database
	new optionalObjectFields;
} else {
	optionalObjectFields::register();
}
?>
