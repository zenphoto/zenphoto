<?php

/**
 *
 * You will be presented a list un-published albums and a list of not visible images. You can select albums and
 * images from these lists to be published. (
 * So you can freely upload albums and images then on a periodic basis review which ones to make available
 * to visitors of your gallery.
 * Only images that are older than specific date and time will be shown. (You can select that benchmark making it easy
 * to allow new images to <i>age</i> before you decide to publish them.)
 * There is no record of when albums were first encountered, so all un-published
 * albums are show.
 *
 * If you have the <var>Zenpage</var> content management plugin enabled you will also have lists of
 * unpublished <i>categories</i>, <i>news articles</i>, and <i>pages</i>.
 *
 * <b>NOTE:</b>  The <var>fieldsets</var> for each of these displays is <i>collapsed</i> by default. Click on the <span style="color:green;font-size: large;">&dArr;</span> icon to
 * <i>open</i> the view. When the view is open, <span style="color:green;font-size: large;">&uArr;</span> will close it.
 *
 * You can also change the default setting of the albums <i>published</i> and
 * the images <i>visible</i> fields. (These are the same options provided in the <i>Gallery behavior</i> section of
 * the gallery options tab.)
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 500 | ADMIN_PLUGIN;
$plugin_description = gettext('A single place to quickly review your unpublished content.');
$plugin_author = "Stephen Billard (sbillard)";

if (zp_loggedin(ADMIN_RIGHTS)) {
	zp_register_filter('admin_tabs', 'publishContent::admin_tabs', -1600);
}

class publishContent {

	static function admin_tabs($tabs) {
		$tabs['admin']['subtabs'][gettext('Publish content')] = '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/publishContent/publishContent.php?tab=content';
		return $tabs;
	}

}

?>