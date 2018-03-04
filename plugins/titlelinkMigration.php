<?php

/*
 * Migrates old titlelinks to append the <em>mod_rewrite_suffix</em> so they are consistent
 * with newly created titlelinks.
 *
 * This migration will not add the <em>mod_rewrite_suffix</em> if it is already present.
 * Otherwise the new titlelink will be <var>old_titlelink</var>%RW_SUFFIX%.
 *
 * <b>NOTE</b>: migration may result in duplicated titlelinks. If that would be the case,
 * the titlelink will not be changed. This occurrence will be logged in the debug log.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/utf8mb4Migration
 * @pluginCategory development
 *
 * Copyright 2018 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */

$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext("Migrate titlelinks to include the <em>mod_rewrite_suffix</em>.");
$plugin_disable = zpFunctions::pluginDisable(array(array(!RW_SUFFIX, gettext('No <em>mod_rewrite_suffix</em> has been set.'))));

zp_register_filter('admin_utilities_buttons', 'titlelinkMigration::buttons');

class titlelinkMigration {

	static function buttons($buttons) {
		global $_zp_conf_vars;

		$buttons[] = array(
				'category' => gettext('Database'),
				'enable' => true,
				'button_text' => gettext('Migrate titlelinks'),
				'formname' => 'titlelink',
				'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/titlelinkMigration/migrate.php',
				'icon' => ZP_BLUE,
				'title' => gettext('A utility to append the mod_rewrite_suffix to zenpage titlelinks.'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'titlelinkMigration'
		);

		return $buttons;
	}

}
