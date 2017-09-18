<?php
/*
 *
 *
 * Use of the <var>custom_data</var> field is not recommended.
 * This is because the field is <b>shared</b> by all users so conflicts in use
 * are unavoidable.
 *
 * Instead create a plugin based on the <var>fieldExtender</var> class
 * which allows you to extend the database with fields unique to your application.
 * examples are the <var>optionalObjectFields</var> and <var>customFieldExtender</var> plugins.
 *
 * <b>Note:</b> You must enable the option for each object for which you wish to expose the <var>custom_data</var>
 * field. If you an option is not enabled the customdata field for that object will be removed.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */


$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('This plugin exposes the "custom_data" field on objects.');
$plugin_notice = gettext('This plugin is for legacy use. You should make a custom field extender plugin.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'customData';

if (getOption('customDataAlbums')) {
	zp_register_filter("edit_album_custom_data", "customData::mediaItemEdit");
	zp_register_filter("searchable_fields", "customData::searches");
	zp_register_filter("save_album_custom_data", "customData::custom_data");
}
if (getOption('customDataImages')) {
	zp_register_filter("edit_image_custom_data", "customData::mediaItemEdit");
	zp_register_filter("searchable_fields", "customData::searches");
	zp_register_filter("save_image_custom_data", "customData::custom_data");
}
if (getOption('customDataNews')) {
	zp_register_filter("edit_article_custom_data", "customData::cmsItemEdit");
	zp_register_filter("searchable_fields", "customData::searches");
	zp_register_filter("save_article_custom_data", "customData::custom_data");
}
if (getOption('customDataCategories')) {
	zp_register_filter("edit_category_custom_data", "customData::cmsItemEdit");
	zp_register_filter("searchable_fields", "customData::searches");
	zp_register_filter("save_category_custom_data", "customData::custom_data");
}
if (getOption('customDatapages')) {
	zp_register_filter("edit_page_custom_data", "customData::cmsItemEdit");
	zp_register_filter("searchable_fields", "customData::searches");
	zp_register_filter("save_page_custom_data", "customData::custom_data");
}

class customData {

	function __construct() {

		if (OFFSET_PATH == 2) {
			$present = array('albums' => 0, 'images' => 0, 'news' => 0, 'pages' => 0, 'news_categories' => 0);
			foreach ($present as $table => $v) {
				$tablecols = db_list_fields($table);
				foreach ($tablecols as $key => $datum) {
					if ($datum['Field'] == 'custom_data') {
						$present[$table] = 1 + (int) !empty($datum['Comment']);
					}
				}
			}
			if (extensionEnabled('customdata')) {
				setOptionDefault('customDataAlbums', $present['albums']);
				setOptionDefault('customDataImages', $present['images']);
				setOptionDefault('customDataNews', $present['news']);
				setOptionDefault('customDataPages', $present['pages']);
				setOptionDefault('customDataCategories', $present['news_categories']);
			} else {
				purgeOption('customDataAlbums');
				purgeOption('customDataImages');
				purgeOption('customDataNews');
				purgeOption('customDataPages');
				purgeOption('customDataCategories');
			}

			if (getOption('customDataAlbums')) {
				if (!$present['albums'])
					setupQuery('ALTER TABLE ' . prefix('albums') . " ADD COLUMN `custom_data` TEXT COMMENT 'optional_customData'");
			} else {
				if ($present['albums'])
					setupQuery('ALTER TABLE ' . prefix('albums') . ' DROP `custom_data`');
			}
			if (getOption('customDataImages')) {
				if (!$present['images'])
					setupQuery('ALTER TABLE ' . prefix('images') . " ADD COLUMN `custom_data` TEXT COMMENT 'optional_customData'");
			} else {
				if ($present['images'])
					setupQuery('ALTER TABLE ' . prefix('images') . ' DROP `custom_data`');
			}
			if (getOption('customDataNews')) {
				if (!$present['news'])
					setupQuery('ALTER TABLE ' . prefix('news') . " ADD COLUMN `custom_data` TEXT COMMENT 'optional_customData'");
			} else {
				if ($present['news'])
					setupQuery('ALTER TABLE ' . prefix('news') . ' DROP `custom_data`');
			}
			if (getOption('customDataPages')) {
				if (!$present['pages'])
					setupQuery('ALTER TABLE ' . prefix('pages') . " ADD COLUMN `custom_data` TEXT COMMENT 'optional_customData'");
			} else {
				if ($present['pages'])
					setupQuery('ALTER TABLE ' . prefix('pages') . ' DROP `custom_data`');
			}
			if (getOption('customDataCategories')) {
				if (!$present['news_categories'])
					setupQuery('ALTER TABLE ' . prefix('news_categories') . " ADD COLUMN `custom_data` TEXT COMMENT 'optional_customData'");
			} else {
				if ($present['news_categories'])
					setupQuery('ALTER TABLE ' . prefix('news_categories') . ' DROP `custom_data`');
			}
		}
	}

	function getOptionsSupported() {
		return array(gettext('Enabled custom_data') => array('key' => 'customDataAlbums', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'checkboxes' => array(// The definition of the checkboxes
								gettext('Albums') => 'customDataAlbums',
								gettext('Images') => 'customDataImages',
								gettext('News articles') => 'customDataNews',
								gettext('News categories') => 'customDataCategories',
								gettext('Pages') => 'customDataPages'
						),
						'desc' => gettext('Expose the <code>custom_data</code> field on the admin pages for these objects.'))
		);
	}

	static function mediaItemEdit($html, $object, $i) {
		ob_start();
		?>
		<tr>
			<td class="leftcolumn"><?php echo gettext("Custom data:"); ?></td>
			<td>
				<?php print_language_string_list($object->getCustomData('all'), $i . "-custom_data", true, NULL, 'texteditor_customdata', '100%'); ?>
			</td>
		</tr>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}

	static function cmsItemEdit($html, $object) {
		ob_start();
		?>
		<tr>
			<td class="topalign-nopadding nowrap"><?php echo gettext("Custom:"); ?></td>
			<td class="middlecolumn">
				<?php
				print_language_string_list($object->getCustomData('all'), 'custom_data', true, NULL, 'custom_data', '100%', 10);
				?>
			</td>
		</tr>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}

	static function searches($list) {
		$list['custom_data'] = gettext('Custom data');
		return $list;
	}

	static function custom_data($custom, $i, $obj = NULL) {
		if (is_object($i)) {
			$obj = $i;
			$i = NULL;
		} else {
			$i = $i . '-';
		}
		$custom = process_language_string_save($i . "custom_data", 1);
		$obj->setCustomData($custom);
		return $custom;
	}

}

function customData_enable($enabled) {
	requestSetup('customData');
}
