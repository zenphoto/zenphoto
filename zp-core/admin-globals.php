<?php
/**
 * Initialize globals for Admin
 * @package admin
 */

// force UTF-8 Ø

define('UPLOAD_ERR_QUOTA', -1);
define('UPLOAD_ERR_BLOCKED', -2);

require_once(dirname(__FILE__).'/functions-basic.php');

zp_session_start();
if (SERVER_PROTOCOL == 'https_admin') {
	// force https login
	if (!isset($_SERVER["HTTPS"])) {
		$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location:$redirect");
		exitZP();
	}
}
require_once(dirname(__FILE__).'/admin-functions.php');

$zenphoto_tabs = array();

if (!getOption('license_accepted')) {
	require_once(dirname(__FILE__).'/license.php');
}


$sortby = array(gettext('Filename') => 'filename',
								gettext('Date') => 'date',
								gettext('Title') => 'title',
								gettext('ID') => 'id',
								gettext('Filemtime') => 'mtime',
								gettext('Owner') => 'owner',
								gettext('Published') => 'show'
								);

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
		$zenphoto_tabs['overview'] = array(	'text'=>gettext("overview"),
																				'link'=>WEBPATH."/".ZENFOLDER.'/admin.php',
																				'subtabs'=>NULL);
	}
	if ($_zp_loggedin & UPLOAD_RIGHTS || $_zp_loggedin & FILES_RIGHTS) {
		$zenphoto_tabs['upload'] = array('text'=>gettext("upload"),
							'subtabs'=>NULL);
		if ($_zp_loggedin & UPLOAD_RIGHTS) {
			$locale = substr(getOption("locale"),0,2);
			if (empty($locale)) $locale = 'en';
			$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-upload.php';
			$zenphoto_tabs['upload']['subtabs'] = array(gettext('images')=>'admin-upload.php?page=upload&amp;tab=albums',
			gettext('files')=>'admin-filemanager.php?page=upload&amp;tab=files');
			$zenphoto_tabs['upload']['default'] = 'albums';
		} else if ($_zp_loggedin & UPLOAD_RIGHTS) {
			$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-upload.php';
		} else {
			$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-filemanager.php';
		}
	}

	if ($_zp_loggedin & ALBUM_RIGHTS) {
		$zenphoto_tabs['edit'] = array(	'text'=>gettext("albums"),
																		'link'=>WEBPATH."/".ZENFOLDER.'/admin-edit.php',
																		'subtabs'=>NULL);
	}
	if (getOption('zp_plugin_zenpage')) {
		if ($_zp_loggedin & ZENPAGE_PAGES_RIGHTS) {
			$zenphoto_tabs['pages'] = array('text'=>gettext("pages"),
																			'link'=>WEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/admin-pages.php',
																			'subtabs'=>NULL);
		}
		if ($_zp_loggedin & ZENPAGE_NEWS_RIGHTS) {
			$zenphoto_tabs['news'] = array(	'text'=>gettext("news"),
																			'link'=>WEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/admin-news-articles.php',
																			'subtabs'=>array(	gettext('articles')=>PLUGIN_FOLDER.'/zenpage/admin-news-articles.php?page=news&amp;tab=articles',
																			gettext('categories')=>PLUGIN_FOLDER.'/zenpage/admin-categories.php?page=news&amp;tab=categories'),
																			'default'=>'articles');
		}
	}

	if ($_zp_loggedin & TAGS_RIGHTS) {
		$zenphoto_tabs['tags'] = array(	'text'=>gettext("tags"),
																		'link'=>WEBPATH."/".ZENFOLDER.'/admin-tags.php',
																		'subtabs'=>NULL);
	}

	if ($_zp_loggedin & COMMENT_RIGHTS) {
		$zenphoto_tabs['comments'] = array(	'text'=>gettext("comments"),
																				'link'=>WEBPATH."/".ZENFOLDER.'/admin-comments.php',
																				'subtabs'=>NULL);
	}
	$zenphoto_tabs['users'] = array('text'=>gettext("users"),
																	'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users',
																	'subtabs'=>NULL);



	$subtabs = array();
	$optiondefault='';
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$optiondefault='&amp;tab=general';
			$subtabs[gettext("general")] = 'admin-options.php?page=options&amp;tab=general';
		} else {
			$optiondefault='&amp;tab=gallery';
		}
		$subtabs[gettext("gallery")] = 'admin-options.php?page=options&amp;tab=gallery';
		if ($_zp_loggedin & ADMIN_RIGHTS) {
			$subtabs[gettext("security")] = 'admin-options.php?page=options&amp;tab=security';
		}
		$subtabs[gettext("image")] = 'admin-options.php?page=options&amp;tab=image';
		$subtabs[gettext("comment")] = 'admin-options.php?page=options&amp;tab=comments';
	}
	if ($_zp_loggedin & ADMIN_RIGHTS) {
		if (empty($optiondefault)) $optiondefault='&amp;tab=plugin';
		$subtabs[gettext("plugin")] = 'admin-options.php?page=options&amp;tab=plugin';
	}
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		$subtabs[gettext("search")] = 'admin-options.php?page=options&amp;tab=search';
	}
	if ($_zp_loggedin & THEMES_RIGHTS) {
		if (empty($optiondefault)) $optiondefault='&amp;tab=theme';
		$subtabs[gettext("theme")] = 'admin-options.php?page=options&amp;tab=theme';
	}
	if ($_zp_loggedin & OPTIONS_RIGHTS) {
		$subtabs[gettext("RSS")] = 'admin-options.php?page=options&amp;tab=rss';
	}
	if (!empty($subtabs)) {
		$zenphoto_tabs['options'] = array('text'=>gettext("options"),
																			'link'=>WEBPATH."/".ZENFOLDER.'/admin-options.php?page=options'.$optiondefault,
																			'subtabs'=>$subtabs,
																			'default'=>'gallery');
	}

	if ($_zp_loggedin & THEMES_RIGHTS) {
		$zenphoto_tabs['themes'] = array(	'text'=>gettext("themes"),
																			'link'=>WEBPATH."/".ZENFOLDER.'/admin-themes.php',
																			'subtabs'=>NULL);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		$zenphoto_tabs['plugins'] = array('text'=>gettext("plugins"),
																			'link'=>WEBPATH."/".ZENFOLDER.'/admin-plugins.php',
																			'subtabs'=>NULL);
	}

	if ($_zp_loggedin & ADMIN_RIGHTS) {
		list($subtabs,$default)  = getLogTabs();
		$zenphoto_tabs['logs'] = array('text'=>gettext("logs"),
																	 'link'=>WEBPATH."/".ZENFOLDER.'/admin-logs.php?page=logs',
																	 'subtabs'=>$subtabs,
																	 'default'=>$default);
	}
	if (!$_zp_current_admin_obj->getID()) {
		$filelist = safe_glob(SERVERPATH . "/" . BACKUPFOLDER . '/*.zdb');
		if (count($filelist) > 0) {
			$zenphoto_tabs['restore'] = array('text'=>gettext("Restore"),
																				'link'=>WEBPATH."/".ZENFOLDER.'/utilities/backup_restore.php?page=backup',
																				'subtabs'=>NULL);
		}
	}

	$zenphoto_tabs = zp_apply_filter('admin_tabs', $zenphoto_tabs);

	//	so as to make it generally available as we make much use of it
	if (OFFSET_PATH != 2) {
		require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/colorbox_js.php');
	}
	$last = getOption('last_update_check');
	if (empty($last) || is_numeric($last)) {
		if (time() > $last+1728000) {
			//	check each 20 days
			setOption('last_update_check', time());
			$v = checkForUpdate();
			if (!empty($v)) {
				if ($v != 'X') {
					setOption('last_update_check','<a href="http://www.zenphoto.org" alt="'.gettext('Zenphoto download page').'">'.gettext("A new version of Zenphoto version is available.").'</a>');
				}
			}
		}
	} else {
		zp_register_filter('admin_note', 'admin_showupdate');
	}
	unset($last);

	loadLocalOptions(false, $_zp_gallery->getCurrentTheme());
}
?>
