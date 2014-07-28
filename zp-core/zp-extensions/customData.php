<?php
/*
 *
 *
 * Use of the <var>custom_data</var> field is not recommended.
 * This is because the field is <b>shared</b> by all users so conflicts in use
 * are unavoidable.
 *
 * Instead create a pluhin based on the <var>customFieldExtender</var> plugin
 * which allows you to extend the database with fields unique to your application.
 *
 * <b>Note:</b> You must enable the option for each object for which you wish to expose the <var>custom_data</var>
 * field.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 *
 */


$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('This plugin exposes the "custom_data" field on objects.');
$plugin_notice = gettext('This plugin is for legacy use. You should make a custom field extender plugin ');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'customData';

if (getOption('customDataAlbums')) {
	zp_register_filter("edit_album_custom_data", "customData::mediaItemEdit");
}
if (getOption('customDataImages')) {
	zp_register_filter("edit_image_custom_data", "customData::mediaItemEdit");
}
if (getOption('customDataNews')) {
	zp_register_filter("edit_article_custom_data", "customData::cmsItemEdit");
}
if (getOption('customDataCategories')) {
	zp_register_filter("edit_category_custom_data", "customData::cmsItemEdit");
}
if (getOption('customDatapages')) {
	zp_register_filter("edit_page_custom_data", "customData::cmsItemEdit");
}

class customData {

	function __construct() {
		setOptionDefault('customDataAlbums', 1);
		setOptionDefault('customDataImages', 1);
		setOptionDefault('customDataNews', 1);
		setOptionDefault('customDataPages', 1);
		setOptionDefault('customDataCategories', 1);
	}

	function getOptionsSupported() {
		return array(gettext('Enabled custom_data') => array('key'				 => 'customDataAlbums', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'checkboxes' => array(// The definition of the checkboxes
														gettext('Albums')					 => 'customDataAlbums',
														gettext('Images')					 => 'customDataImages',
														gettext('News articles')	 => 'customDataNews',
														gettext('News categories') => 'customDataCategories',
														gettext('Pages')					 => 'customDataPages'
										),
										'desc'			 => gettext('Expose the <code>custom_data</code> field on the admin pages for these objects.'))
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
			<td class="topalign-nopadding"><?php echo gettext("Custom:"); ?></td>
			<td class="middlecolumn">
				<?php
				print_language_string_list($object->getCustomData('all'), 'custom_data', true, NULL, 'custom_data', '100%', 'zenpage_language_string_list', 10);
				?>

			</td>
		</tr>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}

}
