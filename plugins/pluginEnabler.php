<?php

/**
 *
 * Provides buttons to:
 * <ul>
 * 		<li>Remember the currently set plugins</li>
 * 		<li>Disable all plugins</li>
 * 		<li>Enable all plugins</li>
 * 		<li>Enable all <em>Standard</em> plugins</li>
 * 		<li>Enable the above <i>remembered</i> set of plugins</li>
 * </ul>
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Mass enable/disable for plugins.");

zp_register_filter('admin_utilities_buttons', 'pluginEnabler::buttons');

class pluginEnabler {

	static function buttons($buttons) {

		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Plugins » standard'),
				'formname' => 'enablebutton',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/pluginEnabler/handler.php',
				'icon' => ZP_BLUE,
				'title' => gettext('Enables all standard plugins (except <em>show_not_logged-in</em>!) Third party plugins are disabled.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="pluginsEnable" value="1" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'pluginEnabler'
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Plugins » all'),
				'formname' => 'enablebutton',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/pluginEnabler/handler.php',
				'icon' => CHECKMARK_GREEN,
				'title' => gettext('Enables all plugins.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="pluginsEnable" value="3" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'pluginEnabler'
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Plugins » remembered'),
				'formname' => 'enablebutton',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/pluginEnabler/handler.php',
				'icon' => CURVED_UPWARDS_AND_RIGHTWARDS_ARROW_BLUE,
				'title' => gettext('Restores the plugin states to what was remembered.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="pluginsEnable" value="2" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'pluginEnabler'
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Plugins ¤ current'),
				'formname' => 'enablebutton',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/pluginEnabler/handler.php',
				'icon' => ARROW_DOWN_GREEN,
				'title' => gettext('Remembers current plugin states.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="pluginsEnable" value="4" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'pluginEnabler'
		);
		$buttons[] = array(
				'category' => gettext('Development'),
				'enable' => true,
				'button_text' => gettext('Plugins × all'),
				'formname' => 'disablebutton',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/pluginEnabler/handler.php',
				'icon' => CROSS_MARK_RED,
				'title' => gettext('Disables all plugins except pluginEnabler.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="pluginsEnable" value="0" />',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'pluginEnabler'
		);
		return $buttons;
	}

}

if (OFFSET_PATH == 2) {
// remember what things look like when we first arrive
	setOptionDefault('pluginEnabler_currentset', serialize(array_keys(getEnabledPlugins())));
}
?>