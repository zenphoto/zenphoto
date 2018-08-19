<?php

/**
 * root script for for the sote
 * @package core
 *
 */
// force UTF-8 Ø
if (!defined('OFFSET_PATH'))
	die(); //	no direct linking

$_zp_script_timer['start'] = microtime();
require_once(dirname(__FILE__) . '/global-definitions.php');
require_once(dirname(__FILE__) . '/functions.php');

if (GALLERY_SESSION || zp_loggedin(UPLOAD_RIGHTS | ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS)) {
	zp_session_start();
}
if (function_exists('openssl_encrypt')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/class.ncrypt.php');
	$_themeCript = new mukto90\Ncrypt;
	$_themeCript->set_secret_key(HASH_SEED);
	$_themeCript->set_secret_iv(SECRET_IV);
	$_themeCript->set_cipher(INCRIPTION_METHOD);
}
$_zp_script_timer['basic requirements'] = microtime();

zp_apply_filter('feature_plugin_load');
if (DEBUG_PLUGINS) {
	debugLog('Loading the "feature" plugins.');
}

foreach (getEnabledPlugins() as $extension => $plugin) {
	$loadtype = $plugin['priority'];
	if ($loadtype & FEATURE_PLUGIN) {
		$start = microtime();
		require_once($plugin['path']);
		if (DEBUG_PLUGINS) {
			zpFunctions::pluginDebug($extension, $priority, $start);
		}
		$_zp_loaded_plugins[$extension] = $extension;
	}
}
$_zp_script_timer['feature plugins'] = microtime();

require_once(SERVERPATH . "/" . ZENFOLDER . '/rewrite.php');
require_once(dirname(__FILE__) . '/template-functions.php');
if (!defined('SEO_FULLWEBPATH')) {
	define('SEO_FULLWEBPATH', FULLWEBPATH);
	define('SEO_WEBPATH', WEBPATH);
}
checkInstall();
// who cares if MOD_REWRITE is set. If we somehow got redirected here, handle the rewrite
rewriteHandler();
recordPolicyACK();
$_zp_script_timer['general functions'] = microtime();

/**
 * Invoke the controller to handle requests
 */
require_once(SERVERPATH . "/" . ZENFOLDER . '/functions-controller.php');
require_once(SERVERPATH . "/" . ZENFOLDER . '/controller.php');

$_index_theme = $_zp_script = '';
$_zp_page_check = 'checkPageValidity';

// Display an arbitrary theme-included PHP page
if (isset($_GET['p'])) {
	$_index_theme = prepareCustomPage();
	// Display an Image page.
} else if (in_context(ZP_IMAGE)) {
	$_index_theme = prepareImagePage();
	// Display an Album page.
} else if (in_context(ZP_ALBUM)) {
	$_index_theme = prepareAlbumPage();
	// Display the Index page.
} else if (in_context(ZP_INDEX)) {
	$_index_theme = prepareIndexPage();
} else {
	$_index_theme = setupTheme();
}
$_zp_script_timer['controller'] = microtime();

//	Load the THEME plugins
if (preg_match('~' . ZENFOLDER . '~', $_zp_script)) {
	$custom = false;
} else {
	if (DEBUG_PLUGINS) {
		debugLog('Loading the "theme" plugins.');
	}
	foreach (getEnabledPlugins() as $extension => $plugin) {
		$loadtype = $plugin['priority'];
		if ($loadtype & THEME_PLUGIN) {
			$start = microtime();
			require_once($plugin['path']);
			if (DEBUG_PLUGINS) {
				zpFunctions::pluginDebug($extension, $priority, $start);
			}
			$_zp_loaded_plugins[$extension] = $extension;
		}
	}
	$_zp_script_timer['theme plugins'] = microtime();
	$_zp_script = zp_apply_filter('load_theme_script', $_zp_script, $zp_request);
	$custom = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($_index_theme) . '/functions.php';
	if (file_exists($custom)) {
		require_once($custom);
	}
}

//	HTML caching?
if ($zp_request) {
	$_zp_HTML_cache->startHTMLCache();
}

setThemeColumns();

//check for valid page number (may be theme dependent!)
if ($_zp_page < 0) {
	$zp_request = false;
} else if ($zp_request && $_zp_page > 1) {
	$zp_request = $_zp_page_check($zp_request, $_zp_gallery_page, $_zp_page);
	if (!$zp_request && extensionEnabled('themeSwitcher') && isset($_GET['themeSwitcher'])) {
		//might just be a switched-to theme that does not have the same pagination,
		//set page to 1 and procede
		$zp_request = $_zp_page = 1;
	}
}

if ($zp_request && $_zp_script && file_exists($_zp_script = SERVERPATH . "/" . internalToFilesystem($_zp_script))) {
	if (!checkAccess($hint, $show)) { // not ok to view
		//	don't cache the logon page or you can never see the real one
		$_zp_HTML_cache->abortHTMLCache(true);
		$_zp_gallery_page = 'password.php';
		$_zp_script = SERVERPATH . '/' . THEMEFOLDER . '/' . $_index_theme . '/password.php';
		if (!file_exists(internalToFilesystem($_zp_script))) {
			$_zp_script = SERVERPATH . '/' . ZENFOLDER . '/password.php';
		}
	} else {
		unset($hint);
		unset($show);
	}

	//update publish state, but only on static cache expiry intervals
	$lastupdate = (int) @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/lastPublishCheck');
	if (time() - $lastupdate > getOption('static_cache_expire')) {
		$tables = array('albums', 'images');
		if (extensionEnabled('zenpage')) {
			$tables = array_merge($tables, array('news', 'pages'));
		}
		foreach ($tables as $table) {
			updatePublished($table);
		}
		file_put_contents(SERVERPATH . '/' . DATA_FOLDER . '/lastPublishCheck', time());
	}

	// Include the appropriate page for the requested object, and a 200 OK header.
	header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
	header("HTTP/1.0 200 OK");
	header("Status: 200 OK");
	header('Last-Modified: ' . ZP_LAST_MODIFIED);
	zp_apply_filter('theme_headers');
	include(internalToFilesystem($_zp_script));
} else {
	// If the requested object does not exist, issue a 404 and redirect to the 404.php
	// in the zp-core folder. This script will load the theme 404 page if it exists.
	$_zp_HTML_cache->abortHTMLCache(false);
	include(SERVERPATH . "/" . ZENFOLDER . '/404.php');
}

$_zp_script_timer['theme load'] = microtime();
zp_apply_filter('zenphoto_information', $_zp_script, $_zp_loaded_plugins, $_index_theme);
db_close(); // close the database as we are done
if (TEST_RELEASE) {
	echo "\n";
	list($usec, $sec) = explode(' ', array_shift($_zp_script_timer));
	$first = $last = (float) $usec + (float) $sec;

	foreach ($_zp_script_timer as $step => $time) {
		list($usec, $sec) = explode(" ", $time);
		$cur = (float) $usec + (float) $sec;
		printf("<!-- " . gettext('Script processing %1$s:%2$.4f seconds') . " -->\n", $step, $cur - $last);
		$last = $cur;
	}
	if (count($_zp_script_timer) > 1)
		printf("<!-- " . gettext('Script processing total:%.4f seconds') . " -->\n", $last - $first);
}
$_zp_HTML_cache->endHTMLCache();
?>
