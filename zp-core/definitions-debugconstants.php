<?php

/**
 * Enables test release mode
 */
if (!defined('PRE_RELEASE') && defined('ZENPHOTO_VERSION')) {
	if (defined('ZENPHOTO_VERSION')) {
		define('PRE_RELEASE', preg_match("/(RC|a|b)$/", ZENPHOTO_VERSION));
	} else {
		define('PRE_RELEASE', false);
	}
}
$is_testrelease = false;
if (isset($_zp_conf_vars['test_release'])) {
	if ($_zp_conf_vars['test_release']) {
		$is_testrelease = true;
	} else {
		$is_testrelease = false;
	}
} else {
	$is_testrelease = PRE_RELEASE;
}
if (!defined('TEST_RELEASE')) {
	define('TEST_RELEASE', $is_testrelease);
}
unset($is_prerelease);
unset($is_testrelease);

/**
 * set to true to log admin saves and login attempts
 */
if(!defined('DEBUG_LOGIN')) {
	define('DEBUG_LOGIN', (isset($_zp_conf_vars['debug_login']) && $_zp_conf_vars['debug_login']));
}

/**
 * set to true to supplies the calling sequence with zp_error messages
 */
if (!defined('DEBUG_ERROR') && defined('TEST_RELEASE')) {
	define('DEBUG_ERROR', (isset($_zp_conf_vars['debug_error']) && $_zp_conf_vars['debug_error']) || TEST_RELEASE);
}
/**
 * set to true to log image processing debug information.
 */
if (!defined('DEBUG_IMAGE')) {
	define('DEBUG_IMAGE', isset($_zp_conf_vars['debug_image']) && $_zp_conf_vars['debug_image']);
}

/**
 * set to true to flag image processing errors.
 */
if (!defined('DEBUG_IMAGE_ERR') && defined('TEST_RELEASE')) {
	define('DEBUG_IMAGE_ERR', (isset($_zp_conf_vars['debug_image_err']) && $_zp_conf_vars['debug_image_err']) || TEST_RELEASE);
}

/**
 * set to true to log 404 error processing debug information.
 */
if (!defined('DEBUG_404') && defined('TEST_RELEASE')) {
	define('DEBUG_404', (isset($_zp_conf_vars['debug_404']) && $_zp_conf_vars['debug_404']) || TEST_RELEASE);
}

/**
 * set to true to log start/finish of exif processing. Useful to find problematic images.
 */
if (!defined('DEBUG_EXIF')) {
	define('DEBUG_EXIF', isset($_zp_conf_vars['debug_exif']) && $_zp_conf_vars['debug_exif']);
}
/**
 * set to true to log plugin load sequence.
 */
if (!defined('DEBUG_PLUGINS')) {
	define('DEBUG_PLUGINS', isset($_zp_conf_vars['debug_plugins']) && $_zp_conf_vars['debug_plugins']);
}
/**
 * set to true to log filter application sequence.
 */
if (!defined('DEBUG_FILTERS')) {
	define('DEBUG_FILTERS', isset($_zp_conf_vars['debug_filters']) && $_zp_conf_vars['debug_filters']);
}

/**
 * 	set to true to log the "EXPLAIN" of SELECT queries in the debug log
 */
if (!defined('EXPLAIN_SELECTS')) {
	define('EXPLAIN_SELECTS', isset($_zp_conf_vars['explain_selects']) && $_zp_conf_vars['explain_selects']);
}

/**
 * used for examining language selection problems
 */
if (!defined('DEBUG_LOCALE')) {
	define('DEBUG_LOCALE', isset($_zp_conf_vars['debug_locale']) && $_zp_conf_vars['debug_locale']);
}
