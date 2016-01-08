<?php

/**
 * root script for Zenphoto
 * @package core
 *
 */
if (!defined('OFFSET_PATH'))
	die(); //	no direct linking

$_zp_script_timer['start'] = microtime();
// force UTF-8 Ø
require_once(dirname(__FILE__) . '/global-definitions.php');
require_once(dirname(__FILE__) . '/functions.php');
zp_apply_filter('feature_plugin_load');
if (DEBUG_PLUGINS) {
	debugLog('Loading the "feature" plugins.');
}
foreach (getEnabledPlugins() as $extension => $plugin) {
	$loadtype = $plugin['priority'];
	if ($loadtype & FEATURE_PLUGIN) {
		if (DEBUG_PLUGINS) {
			list($usec, $sec) = explode(" ", microtime());
			$start = (float) $usec + (float) $sec;
		}
		require_once($plugin['path']);
		if (DEBUG_PLUGINS) {
			zpFunctions::pluginDebug($extension, $priority, $start);
		}
		$_zp_loaded_plugins[$extension] = $extension;
	}
}

require_once(SERVERPATH . "/" . ZENFOLDER . '/rewrite.php');
require_once(dirname(__FILE__) . '/template-functions.php');
checkInstall();
if (MOD_REWRITE || isset($_GET['z']))
	rewriteHandler();

//$_zp_script_timer['require'] = microtime();
/**
 * Invoke the controller to handle requests
 */
require_once(SERVERPATH . "/" . ZENFOLDER . '/functions-controller.php');
require_once(SERVERPATH . "/" . ZENFOLDER . '/controller.php');

$_index_theme = $_zp_script = '';
$_zp_page_check = 'checkPageValidity';
//$_zp_script_timer['controller'] = microtime();
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

//$_zp_script_timer['theme setup'] = microtime();
$_zp_script = zp_apply_filter('load_theme_script', $_zp_script, $zp_request);

$custom = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($_index_theme) . '/functions.php';
if (file_exists($custom)) {
	require_once($custom);
} else {
	$custom = false;
}

//	Load the THEME plugins
if (!preg_match('~' . ZENFOLDER . '~', $_zp_script)) {
	if (DEBUG_PLUGINS) {
		debugLog('Loading the "theme" plugins.');
	}
	foreach (getEnabledPlugins() as $extension => $plugin) {
		$loadtype = $plugin['priority'];
		if ($loadtype & THEME_PLUGIN) {
			if (DEBUG_PLUGINS) {
				list($usec, $sec) = explode(" ", microtime());
				$start = (float) $usec + (float) $sec;
			}
			require_once($plugin['path']);
			if (DEBUG_PLUGINS) {
				zpFunctions::pluginDebug($extension, $priority, $start);
			}
			$_zp_loaded_plugins[$extension] = $extension;
			//		$_zp_script_timer['load '.$extension] = microtime();
		}
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
}

//$_zp_script_timer['theme scripts'] = microtime();
if ($zp_request && $_zp_script && file_exists($_zp_script = SERVERPATH . "/" . internalToFilesystem($_zp_script))) {
	if (checkAccess($hint, $show)) { // ok to view
	} else {
		//	don't cache the logon page or you can never see the real one
		$_zp_HTML_cache->abortHTMLCache();
		$_zp_gallery_page = 'password.php';
		$_zp_script = SERVERPATH . '/' . THEMEFOLDER . '/' . $_index_theme . '/password.php';
		if (!file_exists(internalToFilesystem($_zp_script))) {
			$_zp_script = SERVERPATH . '/' . ZENFOLDER . '/password.php';
		}
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
	$_zp_HTML_cache->abortHTMLCache();
	include(SERVERPATH . "/" . ZENFOLDER . '/404.php');
}
//$_zp_script_timer['theme script load'] = microtime();
zp_apply_filter('zenphoto_information', $_zp_script, $_zp_loaded_plugins, $_index_theme);
//$_zp_script_timer['expose information'] = microtime();
db_close(); // close the database as we are done
echo "\n";
list($usec, $sec) = explode(' ', array_shift($_zp_script_timer));
$first = $last = (float) $usec + (float) $sec;
$_zp_script_timer['end'] = microtime();
foreach ($_zp_script_timer as $step => $time) {
	list($usec, $sec) = explode(" ", $time);
	$cur = (float) $usec + (float) $sec;
	printf("<!-- " . gettext('Zenphoto script processing %1$s:%2$.4f seconds') . " -->\n", $step, $cur - $last);
	$last = $cur;
}
if (count($_zp_script_timer) > 1)
	printf("<!-- " . gettext('Zenphoto script processing total:%.4f seconds') . " -->\n", $last - $first);
$_zp_HTML_cache->endHTMLCache();
?>
