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
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.3';

zp_register_filter('admin_utilities_buttons', 'pluginEnabler::buttons');

class pluginEnabler {

	static function buttons($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Plugins » Standard'),
						'formname'		 => 'enablebutton',
						'action'			 => FULLWEBPATH . '/plugins/pluginEnabler/handler.php',
						'icon'				 => 'images/zp.png',
						'title'				 => gettext('Enables all standard plugins (except <em>show_not_logged-in</em>!) Third party plugins are disabled.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="pluginsEnable" value="1" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'pluginEnabler'
		);
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Plugins » all'),
						'formname'		 => 'enablebutton',
						'action'			 => FULLWEBPATH . '/plugins/pluginEnabler/handler.php',
						'icon'				 => 'images/pass.png',
						'title'				 => gettext('Enables all plugins.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="pluginsEnable" value="3" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'pluginEnabler'
		);
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Plugins » remembered'),
						'formname'		 => 'enablebutton',
						'action'			 => FULLWEBPATH . '/plugins/pluginEnabler/handler.php',
						'icon'				 => 'images/redo.png',
						'title'				 => gettext('Restores the plugin states to what was remembered.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="pluginsEnable" value="2" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'pluginEnabler'
		);
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Plugins ¤ current'),
						'formname'		 => 'enablebutton',
						'action'			 => FULLWEBPATH . '/plugins/pluginEnabler/handler.php',
						'icon'				 => 'images/arrow_down.png',
						'title'				 => gettext('Remembers current plugin states.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="pluginsRemember" value="1" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'pluginEnabler'
		);
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Plugins × all'),
						'formname'		 => 'disablebutton',
						'action'			 => FULLWEBPATH . '/plugins/pluginEnabler/handler.php',
						'icon'				 => 'images/reset.png',
						'title'				 => gettext('Disables all plugins except pluginEnabler.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="pluginsEnable" value="0" />',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'pluginEnabler'
		);
		return $buttons;
	}

}

if (is_null(getOption('pluginEnabler_currentset'))) { // remember what things look like when we first arrive
	setOption('pluginEnabler_currentset', serialize(array_keys(getEnabledPlugins())));
}
?>