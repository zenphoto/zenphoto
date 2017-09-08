<?php

/*
 * This plugin is a migration tool to move <var>TEXT</var> and <var>LONGTEXT</var> database fields to
 * <i>utf8mb4</i> encoding and collation. All other <i>text</i> fields are left as <i>utf8</i>.
 *
 * <i>utf8</i> encoding supports only <i>Basic Multilingual Plane</i> (BMP) characters. Many
 * recently defined Emoji characters are coded in with <i>trans-BMP</i> codes. For MySql to
 * handle these the data field character set must be set to <i>utf8mb4</i>. Existing ZenPhoto20
 * installations encode all text fields as <i>utf8</i> so trying to store a <i>trans-BMP</i>
 * character will result in data truncation at that character since it is not valid
 * in <i>utf8</i>.
 *
 * You should always backup your database before attempting this migration. <i>utf8</i>
 * is a subset of <i>utf8mb4</i> so all data should migrate successfully. <b>HOWEVER</b>
 * it is possible that the database contains characters which are invalid in <i>utf8mb4</i>.
 * This could cause the migration to fail or to lose data.
 *
 * Your MySQL software version MUST be 5.5.3 or greater to support utf8mb4 encodings. The migration
 * tool will be disabled if your MySQL version is less than this.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage development
 * @category package
 *
 * Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */

// force UTF-8 Ø

$plugin_is_filter = defaultExtension(5 | ADMIN_PLUGIN);
$plugin_description = gettext("Migrate database to utf8mb4 encodings.");
$plugin_disable = zpFunctions::pluginDisable(array(array(version_compare(MySQL_VERSION, '5.5.3', '<'), gettext('MySQL versuib 5.5.3 or greter is required to support trans-BMP character encodings..')), array($_zp_conf_vars['UTF-8'] == 'utf8mb4', gettext('<em>utf8mb4</em> migration is complete. '))));

zp_register_filter('admin_utilities_buttons', 'utf8mb4Migration::buttons');

class utf8mb4Migration {

	static function buttons($buttons) {
		global $_zp_conf_vars;

		if ($_zp_conf_vars['UTF-8'] == 'utf8') {
			if (version_compare(MySQL_VERSION, '5.5.3', '>=')) {
				$buttons[] = array(
						'category' => gettext('Development'),
						'enable' => true,
						'button_text' => gettext('Migrate to utf8mb4'),
						'formname' => 'utf8button',
						'action' => FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/utf8mb4Migration/migrate.php',
						'icon' => ZP_BLUE,
						'title' => gettext('A utility to migrate TEXT and LONGTEXT database fields to utf8mb4 so as to allow 4-byte unicode characters.'),
						'alt' => '',
						'hidden' => '',
						'rights' => ADMIN_RIGHTS,
						'XSRFTag' => 'utf8mb4Migration'
				);
			}
		}
		return $buttons;
	}

}
