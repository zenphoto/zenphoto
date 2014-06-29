<?php

/*
 * provide functions and such that are used by legacy zenphoto
 *
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = defaultExtension(1 | CLASS_PLUGIN);
$plugin_description = gettext("Zenphoto compatibility .");
$plugin_author = "Stephen Billard (sbillard)";

$_zp_CMS = new CMS();

if (OFFSET_PATH != 2) {
	if (extensionEnabled('zenpage')) {

		class Zenpage extends CMS {

		}

		class ZenpagePage extends Page {

		}

		class ZenpageNews extends News {

		}

		class ZenpageCategory extends Category {

		}

	}
}

$_zp_zenpage = clone $_zp_CMS;
?>