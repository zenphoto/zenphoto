<?php

/*
 * provide functions, methods, and such that are used by legacy zenphoto
 *
 * This plugin should be enabled if you are using themes or plugins developed
 * for zenphoto 1.4.6 or later.
 *
 * You should udate the theme/plugin you wish to use. Use the LegacyConverter
 * development subtab to alter your scripts to use the appropriate ZenPhoto20
 * methods and properties.
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins
 * @subpackage development
 * @category package
 */

$plugin_is_filter = defaultExtension(1 | CLASS_PLUGIN);
$plugin_description = gettext("Zenphoto compatibility.");
$plugin_author = "Stephen Billard (sbillard)";

class zenPhotoCompatibilityPack {

	static function filter($param = NULL) {
		global $_zp_current_article, $_zp_current_page;

		//zenphoto variables
		global $_zp_current_zenpage_news, $_zp_current_zenpage_page;
		if (is_object($_zp_current_page)) {
			$_zp_current_zenpage_page = clone $_zp_current_page;
		}
		if (is_object($_zp_current_article)) {
			$_zp_current_zenpage_news = clone $_zp_current_article;
		}
		return $param;
	}

	static function admin_tabs($tabs) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			if (!isset($tabs['development'])) {
				$tabs['development'] = array('text' => gettext("development"),
						'link' => WEBPATH . '/' . USER_PLUGIN_FOLDER . '/zenphotoCompatibilityPack/legacyConverter.php?page=development&tab=legacyConverter',
						'subtabs' => NULL);
			}
			$tabs['development']['subtabs'][gettext("legacy Converter")] = '/' . USER_PLUGIN_FOLDER . '/zenphotoCompatibilityPack/legacyConverter.php?page=development&tab=legacyConverter';
		}
		return $tabs;
	}

}

switch (OFFSET_PATH) {
	case 2:
		break;

	default:
		if (class_exists('CMS')) {

			class Zenpage extends CMS {

			}

			class ZenpagePage extends Page {

			}

			class ZenpageNews extends Article {

			}

			class ZenpageCategory extends Category {

			}

			$_zp_zenpage = clone $_zp_CMS;

			//define the useless legacy definitions
			define('ZP_NEWS_ENABLED', $_zp_CMS->news_enabled);
			define('ZP_PAGES_ENABLED', $_zp_CMS->pages_enabled);
		}
}



zp_register_filter('load_theme_script', 'zenphotoCompatibilityPack::filter');
zp_register_filter('next_object_loop', 'zenphotoCompatibilityPack::filter');
zp_register_filter('admin_tabs', 'zenphotoCompatibilityPack::admin_tabs');
?>