<?php

/**
 * Initialize globals for Admin
 * @package zpcore\admin
 */
// force UTF-8 Ø

define('UPLOAD_ERR_QUOTA', -1);
define('UPLOAD_ERR_BLOCKED', -2);

require_once(dirname(__FILE__) . '/functions/functions-basic.php');

zp_session_start();
require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');
httpsRedirect();
$_SESSION['adminRequest'] = @$_COOKIE['zpcms_auth_user']; //	Allow "unprotected" i.php if the request came from an admin session
$_zp_admin_menu = array();

require_once(SERVERPATH . "/" . ZENFOLDER . '/functions/functions-rewrite.php');

if (OFFSET_PATH != 2 && !getOption('license_accepted')) {
	require_once(dirname(__FILE__) . '/license.php');
}

// setup sub-tab arrays for use in dropdown
if ($_zp_loggedin) {
	if ($_zp_current_admin_obj->reset) {
		$_zp_loggedin = USER_RIGHTS;
	} else {
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$_zp_loggedin = ALL_RIGHTS;
		} else {
			if ($_zp_loggedin & MANAGE_ALL_ALBUM_RIGHTS) {
				// these are lock-step linked!
				$_zp_loggedin = $_zp_loggedin | ALBUM_RIGHTS;
			}
			if ($_zp_loggedin & MANAGE_ALL_NEWS_RIGHTS) {
				// these are lock-step linked!
				$_zp_loggedin = $_zp_loggedin | ZENPAGE_NEWS_RIGHTS;
			}
			if ($_zp_loggedin & MANAGE_ALL_PAGES_RIGHTS) {
				// these are lock-step linked!
				$_zp_loggedin = $_zp_loggedin | ZENPAGE_PAGES_RIGHTS;
			}
		}
	}

	if ($_zp_loggedin & OVERVIEW_RIGHTS) {
		$_zp_admin_menu['overview'] = array(
				'text' => gettext("overview"),
				'link' => FULLWEBPATH . "/" . ZENFOLDER . '/admin.php',
				'subtabs' => NULL);
	}
	$_zp_admin_menu['upload'] = NULL;

	if ($_zp_loggedin & ALBUM_RIGHTS) {
		$_zp_admin_menu['edit'] = array(
				'text' => gettext("albums"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php',
				'subtabs' => NULL);
	}
	if (extensionEnabled('zenpage')) {
		if ($_zp_loggedin & ZENPAGE_PAGES_RIGHTS && (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'pages')) {
			$_zp_admin_menu['pages'] = array(
					'text' => gettext("pages"),
					'link' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-pages.php',
					'subtabs' => NULL);
		}
		if ($_zp_loggedin & ZENPAGE_NEWS_RIGHTS && (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'news')) {
			$_zp_admin_menu['news'] = array(
					'text' => gettext("news"),
					'link' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php',
					'subtabs' => array(
							gettext('articles') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php?page=news&tab=articles',
							gettext('categories') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-categories.php?page=news&tab=categories'),
					'default' => 'articles');
		}
	}

	if ($_zp_loggedin & TAGS_RIGHTS) {
		$_zp_admin_menu['tags'] = array(
				'text' => gettext("tags"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-tags.php',
				'subtabs' => NULL);
	}

	if ($_zp_loggedin & USER_RIGHTS) {
		$_zp_admin_menu['users'] = array(
				'text' => gettext("users"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users',
				'subtabs' => NULL);
	}


	$_zp_admin_submenu = array();
	$optiondefault = '';
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$optiondefault = '&tab=general';
			$_zp_admin_submenu[gettext("general")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=general';
		} else {
			$optiondefault = '&tab=gallery';
		}
		$_zp_admin_submenu[gettext("gallery")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=gallery';
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$_zp_admin_submenu[gettext("security")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=security';
		}
		$_zp_admin_submenu[gettext("image")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=image';
	}
	if ($_zp_loggedin & ADMIN_RIGHTS) {
		if (empty($optiondefault))
			$optiondefault = '&tab=plugin';
		$_zp_admin_submenu[gettext("plugin")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=plugin';
	}
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		$_zp_admin_submenu[gettext("search")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=search';
		if ($_zp_loggedin & THEMES_RIGHTS) {
			if (empty($optiondefault))
				$optiondefault = '&tab=theme';
			$_zp_admin_submenu[gettext("theme")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=theme';
		}
		$_zp_admin_menu['options'] = array(
				'text' => gettext("options"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options' . $optiondefault,
				'subtabs' => $_zp_admin_submenu,
				'default' => 'gallery');
	}

	if ($_zp_loggedin & THEMES_RIGHTS) {
		$_zp_admin_menu['themes'] = array(
				'text' => gettext("themes"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-themes.php',
				'subtabs' => NULL);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		list($_zp_admin_submenu, $default) = getPluginTabs();
		$_zp_admin_menu['plugins'] = array(
				'text' => gettext("plugins"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-plugins.php',
				'subtabs' => $_zp_admin_submenu,
				'default' => $default);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		list($_zp_admin_submenu, $default) = getLogTabs();
		$_zp_admin_menu['logs'] = array(
				'text' => gettext("logs"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-logs.php?page=logs',
				'subtabs' => $_zp_admin_submenu,
				'default' => $default);
	}
	if (!$_zp_current_admin_obj->getID()) {
		$filelist = safe_glob(getBackupFolder(SERVERPATH) . '*.zdb');
		if (count($filelist) > 0) {
			$_zp_admin_menu['restore'] = array(
					'text' => gettext("Restore"),
					'link' => FULLWEBPATH . "/" . ZENFOLDER . '/utilities/backup_restore.php?page=backup',
					'subtabs' => NULL);
		}
	}

	$_zp_admin_menu = zp_apply_filter('admin_tabs', $_zp_admin_menu);
	foreach ($_zp_admin_menu as $tab => $value) {
		if (is_null($value)) {
			unset($_zp_admin_menu[$tab]);
		}
	}

	//echo "<pre>"; print_r($_zp_admin_menu); echo "</pre>";
	//	so as to make it generally available as we make much use of it
	if (OFFSET_PATH != 2) {
		require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');
	}

	loadLocalOptions(false, $_zp_gallery->getCurrentTheme());
}
