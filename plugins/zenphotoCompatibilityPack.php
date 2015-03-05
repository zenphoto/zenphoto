<?php

/*
 * provide functions, methods, and such that are used by legacy zenphoto
 *
 * This plugin should be enabled if you are using themes or plugins developed
 * for zenphoto 1.4.6 or later.
 *
 * Should you wish to udate the theme/plugin the following changes should be made
 *
 * Use functions instead of object instantiations:
 * <ul>
 * <li>newArticle() instead of new ZenpageNews()</li>
 * <li>newPage() instead of new ZenpagePage()</li>
 * <li>newCategory() instead of new ZenpageCategory()</li>
 * </ul>
 *
 * Use the following global "current" objects:
 * <ul>
 * <li>$_zp_current_article</li>
 * <li>$_zp_current_page</li>
 * </ul>
 *
 * To check if the zenpage CMS plugin is enabled use <code>extensionEnabled('zenpage')</code>
 * or <code>class_exists('CMS')</code>
 *
 * The actual CMS objects are"
 * <ul>
 * <li>CMS</li>
 * <li>Page</li>
 * <li>Article</li>
 * <li>Category</li>
 * </ul>
 *
 * The definitions <var>ZP_PAGES_ENABLED</var> and <var>ZP_NEWS_ENABLED</var> are redundant
 * since the classes will return no items if not enabled. Your theme should be sensitive to
 * having no content anyway so the check for enabled is not needed.
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

switch (OFFSET_PATH) {
	case 2:
		break;

	case 0:
		//load up the deprecated functions from Zenphoto
		foreach (getPluginFiles('*.php', 'zenphotoCompatibilityPack') as $deprecated) {
			require_once($deprecated);
		}

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

function zenphotoCompatibility($param = NULL) {
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

zp_register_filter('load_theme_script', 'zenphotoCompatibility');
zp_register_filter('next_object_loop', 'zenphotoCompatibility');
?>