<?php
/**
 * Initialize globals for Admin
 * @package admin
 */

// force UTF-8 Ø

require_once(dirname(__FILE__).'/functions-basic.php');
if (session_id() == '') {
	// force session cookie to be secure when in https
	if(secureServer()) {
		$CookieInfo=session_get_cookie_params();
		session_set_cookie_params($CookieInfo['lifetime'],$CookieInfo['path'], $CookieInfo['domain'],TRUE);
	}
	session_start();
}
if (SERVER_PROTOCOL == 'https_admin') {
	// force https login
	if (!isset($_SERVER["HTTPS"])) {
		$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location:$redirect");
		exit();
	}
}
require_once(dirname(__FILE__).'/admin-functions.php');

$sortby = array(gettext('Filename') => 'filename',
								gettext('Date') => 'date',
								gettext('Title') => 'title',
								gettext('ID') => 'id',
								gettext('Filemtime') => 'mtime',
								gettext('Owner') => 'owner'
								);

$_thumb_field_text =	array('ID'=>gettext('most recent'),
														'mtime'=>gettext('oldest'),
														'title'=>gettext('first alphabetically'),
														'hitcounter'=>gettext('most viewed')
											);

// setup sub-tab arrays for use in dropdown
$zenphoto_tabs = array();
if (zp_loggedin(OVERVIEW_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['overview'] = array('text'=>gettext("overview"),
						'link'=>WEBPATH."/".ZENFOLDER.'/admin.php',
						'subtabs'=>NULL);
}
if ((zp_loggedin(UPLOAD_RIGHTS) || zp_loggedin(FILES_RIGHTS))  && !$_zp_null_account) {
	$zenphoto_tabs['upload'] = array('text'=>gettext("upload"),
							'subtabs'=>NULL);
	if (zp_loggedin(UPLOAD_RIGHTS) && zp_loggedin(FILES_RIGHTS)) {
		$locale = substr(getOption("locale"),0,2);
		if (empty($locale)) $locale = 'en';
		$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-upload.php';
		$zenphoto_tabs['upload']['subtabs'] = array(gettext('images')=>'admin-upload.php?page=upload&amp;tab=albums',
																								gettext('files')=>'admin-filemanager.php?page=upload&amp;tab=files');
		$zenphoto_tabs['upload']['default'] = 'albums';
	} else if (zp_loggedin(UPLOAD_RIGHTS)) {
		$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-upload.php';
	} else {
		$zenphoto_tabs['upload']['link'] = WEBPATH."/".ZENFOLDER.'/admin-filemanager.php';
	}
}

if (zp_loggedin(ALBUM_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['edit'] = array('text'=>gettext("albums"),
							'link'=>WEBPATH."/".ZENFOLDER.'/admin-edit.php',
							'subtabs'=>NULL);
}

if ((getOption('zp_plugin_zenpage') && (zp_loggedin(ZENPAGE_PAGES_RIGHTS))) && !$_zp_null_account) {
	$zenphoto_tabs['pages'] = array('text'=>gettext("pages"),
							'link'=>WEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/admin-pages.php',
							'subtabs'=>NULL);
}
if ((getOption('zp_plugin_zenpage') && (zp_loggedin(ZENPAGE_NEWS_RIGHTS))) && !$_zp_null_account) {
	$zenphoto_tabs['news'] = array('text'=>gettext("news"),
							'link'=>WEBPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/admin-news-articles.php',
							'subtabs'=>array(	gettext('articles')=>PLUGIN_FOLDER.'/zenpage/admin-news-articles.php?page=news&amp;tab=articles',
																gettext('categories')=>PLUGIN_FOLDER.'/zenpage/admin-categories.php?page=news&amp;tab=categories'),
																'default'=>'articles');
}

if (zp_loggedin(TAGS_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['tags'] = array('text'=>gettext("tags"),
							'link'=>WEBPATH."/".ZENFOLDER.'/admin-tags.php',
							'subtabs'=>NULL);
}

if (zp_loggedin(COMMENT_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['comments'] = array('text'=>gettext("comments"),
							'link'=>WEBPATH."/".ZENFOLDER.'/admin-comments.php',
							'subtabs'=>NULL);
}

$zenphoto_tabs['users'] = array('text'=>gettext("users"),
						'link'=>WEBPATH."/".ZENFOLDER.'/admin-users.php?page=users',
						'subtabs'=>NULL);

if (!$_zp_null_account) {
	$subtabs = array();
	$optiondefault='';
	if (zp_loggedin(OPTIONS_RIGHTS)) {
		if (zp_loggedin(ADMIN_RIGHTS)) {
			$optiondefault='&amp;tab=general';
			$subtabs[gettext("general")] = 'admin-options.php?page=options&amp;tab=general';
		} else {
			$optiondefault='&amp;tab=gallery';
		}
		$subtabs[gettext("gallery")] = 'admin-options.php?page=options&amp;tab=gallery';
		if (zp_loggedin(ADMIN_RIGHTS)) {
			$subtabs[gettext("security")] = 'admin-options.php?page=options&amp;tab=security';
		}
		$subtabs[gettext("image")] = 'admin-options.php?page=options&amp;tab=image';
		$subtabs[gettext("comment")] = 'admin-options.php?page=options&amp;tab=comments';
	}
	if (zp_loggedin(ADMIN_RIGHTS)) {
		if (empty($optiondefault)) $optiondefault='&amp;tab=plugin';
		$subtabs[gettext("plugin")] = 'admin-options.php?page=options&amp;tab=plugin';
	}
	if (zp_loggedin(OPTIONS_RIGHTS)) {
		$subtabs[gettext("search")] = 'admin-options.php?page=options&amp;tab=search';
	}
	if (zp_loggedin(THEMES_RIGHTS)) {
		if (empty($optiondefault)) $optiondefault='&amp;tab=theme';
		$subtabs[gettext("theme")] = 'admin-options.php?page=options&amp;tab=theme';
	}
	if (zp_loggedin(OPTIONS_RIGHTS)) {
		$subtabs[gettext("RSS")] = 'admin-options.php?page=options&amp;tab=rss';
	}
	if (!empty($subtabs)) {
		$zenphoto_tabs['options'] = array('text'=>gettext("options"),
				'link'=>WEBPATH."/".ZENFOLDER.'/admin-options.php?page=options'.$optiondefault,
				'subtabs'=>$subtabs,
				'default'=>'gallery');
	}
}
if (zp_loggedin(THEMES_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['themes'] = array('text'=>gettext("themes"),
						'link'=>WEBPATH."/".ZENFOLDER.'/admin-themes.php',
						'subtabs'=>NULL);
}

if (zp_loggedin(ADMIN_RIGHTS) && !$_zp_null_account) {
	$zenphoto_tabs['plugins'] = array('text'=>gettext("plugins"),
							'link'=>WEBPATH."/".ZENFOLDER.'/admin-plugins.php',
							'subtabs'=>NULL);
}

if (zp_loggedin(ADMIN_RIGHTS) && !$_zp_null_account) {
	list($subtabs,$default)  = getLogTabs();
	$zenphoto_tabs['logs'] = array(	'text'=>gettext("logs"),
												'link'=>WEBPATH."/".ZENFOLDER.'/admin-logs.php?page=logs',
												'subtabs'=>$subtabs,
												'default'=>$default);
}

$zenphoto_tabs = zp_apply_filter('admin_tabs', $zenphoto_tabs);

//	so as to make it generally available as we make much use of it
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/colorbox.php');

?>
