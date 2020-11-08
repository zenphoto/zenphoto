<?php

/**
 * Initialize globals for Admin
 * @package admin
 */
// force UTF-8 Ø

define('UPLOAD_ERR_QUOTA', -1);
define('UPLOAD_ERR_BLOCKED', -2);

require_once(dirname(__FILE__) . '/functions-basic.php');

zp_session_start();
require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');
httpsRedirect();
$_SESSION['adminRequest'] = @$_COOKIE['zpcms_auth_user']; //	Allow "unprotected" i.php if the request came from an admin session
$zenphoto_tabs = array();

require_once(SERVERPATH . "/" . ZENFOLDER . '/rewrite.php');
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
		$zenphoto_tabs['overview'] = array(
				'text' => gettext("overview"),
				'link' => FULLWEBPATH . "/" . ZENFOLDER . '/admin.php',
				'subtabs' => NULL);
	}
	$zenphoto_tabs['upload'] = NULL;

	if ($_zp_loggedin & ALBUM_RIGHTS) {
		$zenphoto_tabs['edit'] = array(
				'text' => gettext("albums"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-edit.php',
				'subtabs' => NULL);
	}
	if (extensionEnabled('zenpage')) {
		if ($_zp_loggedin & ZENPAGE_PAGES_RIGHTS && (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'pages')) {
			$zenphoto_tabs['pages'] = array(
					'text' => gettext("pages"),
					'link' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-pages.php',
					'subtabs' => NULL);
		}
		if ($_zp_loggedin & ZENPAGE_NEWS_RIGHTS && (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'news')) {
			$zenphoto_tabs['news'] = array(
					'text' => gettext("news"),
					'link' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php',
					'subtabs' => array(
							gettext('articles') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-news-articles.php?page=news&tab=articles',
							gettext('categories') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage/admin-categories.php?page=news&tab=categories'),
					'default' => 'articles');
		}
	}

	if ($_zp_loggedin & TAGS_RIGHTS) {
		$zenphoto_tabs['tags'] = array(
				'text' => gettext("tags"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-tags.php',
				'subtabs' => NULL);
	}

	if ($_zp_loggedin & USER_RIGHTS) {
		$zenphoto_tabs['users'] = array(
				'text' => gettext("users"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-users.php?page=users',
				'subtabs' => NULL);
	}


	$subtabs = array();
	$optiondefault = '';
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$optiondefault = '&tab=general';
			$subtabs[gettext("general")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=general';
		} else {
			$optiondefault = '&tab=gallery';
		}
		$subtabs[gettext("gallery")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=gallery';
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$subtabs[gettext("security")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=security';
		}
		$subtabs[gettext("image")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=image';
	}
	if ($_zp_loggedin & ADMIN_RIGHTS) {
		if (empty($optiondefault))
			$optiondefault = '&tab=plugin';
		$subtabs[gettext("plugin")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=plugin';
	}
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		$subtabs[gettext("search")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=search';
		if ($_zp_loggedin & THEMES_RIGHTS) {
			if (empty($optiondefault))
				$optiondefault = '&tab=theme';
			$subtabs[gettext("theme")] = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=theme';
		}
		$zenphoto_tabs['options'] = array(
				'text' => gettext("options"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options' . $optiondefault,
				'subtabs' => $subtabs,
				'default' => 'gallery');
	}

	if ($_zp_loggedin & THEMES_RIGHTS) {
		$zenphoto_tabs['themes'] = array(
				'text' => gettext("themes"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-themes.php',
				'subtabs' => NULL);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		list($subtabs, $default) = getPluginTabs();
		$zenphoto_tabs['plugins'] = array(
				'text' => gettext("plugins"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-plugins.php',
				'subtabs' => $subtabs,
				'default' => $default);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		list($subtabs, $default) = getLogTabs();
		$zenphoto_tabs['logs'] = array(
				'text' => gettext("logs"),
				'link' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-logs.php?page=logs',
				'subtabs' => $subtabs,
				'default' => $default);
	}
	if (!$_zp_current_admin_obj->getID()) {
		$filelist = safe_glob(SERVERPATH . '/' . BACKUPFOLDER . '/*.zdb');
		if (count($filelist) > 0) {
			$zenphoto_tabs['restore'] = array(
					'text' => gettext("Restore"),
					'link' => FULLWEBPATH . "/" . ZENFOLDER . '/utilities/backup_restore.php?page=backup',
					'subtabs' => NULL);
		}
	}

	$zenphoto_tabs = zp_apply_filter('admin_tabs', $zenphoto_tabs);
	foreach ($zenphoto_tabs as $tab => $value) {
		if (is_null($value)) {
			unset($zenphoto_tabs[$tab]);
		}
	}

	//echo "<pre>"; print_r($zenphoto_tabs); echo "</pre>";
	//	so as to make it generally available as we make much use of it
	if (OFFSET_PATH != 2) {
		require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/colorbox_js.php');
	}

	loadLocalOptions(false, $_zp_gallery->getCurrentTheme());
}
?>
