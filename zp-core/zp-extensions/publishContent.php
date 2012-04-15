<?php
/**
 *
 * There you can also change the default setting of the albums: published and
 * the images: visible fields.
 *
 * You will be presented a list un-published albums and  a list of not visible images from before a
 * specific data and time. You can select albums and images from these lists to be published.
 * <b>NOTE:</b> there is no record of when albums were first encountered, so all un-published
 * albums are show.
 *
 * So you can freely upload albums and images then on a periodic basis review which ones to make available
 * to visitors of your gallery.
 *
 * @package admin
 */

$plugin_is_filter = 9|ADMIN_PLUGIN;
$plugin_description = gettext('Allows bulk management of image and album content.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.3';

zp_register_filter('admin_utilities_buttons', 'publishContent::button');

class publishContent {
	static function button($buttons) {
		$buttons[] = array(
										'category'=>gettext('admin'),
										'enable'=>true,
										'button_text'=>gettext('Publish content'),
										'formname'=>'publishContent_button',
										'action'=>getPlugin('publishContent/publishContent.php', false, true),
										'icon'=>'images/calendar.png',
										'title'=>gettext('Manage published state of content in your gallery.'),
										'alt'=>'',
										'hidden'=> '',
										'rights'=> ALBUM_RIGHTS
		);
		return $buttons;
	}
}

?>