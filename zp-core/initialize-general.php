<?php

/*
 * Standard initialization code
 */

global $_zp_current_context_stack, $_zp_HTML_cache;

if (!function_exists("json_encode")) {
// load the drop-in replacement library
	require_once(dirname(__FILE__) . '/lib-json.php');
}

require_once(dirname(__FILE__) . '/functions-filter.php');
require_once(dirname(__FILE__) . '/lib-kses.php');


$_zp_captcha = new _zp_captcha(); // this will be overridden by the plugin if enabled.
$_zp_HTML_cache = new _zp_HTML_cache(); // this will be overridden by the plugin if enabled.
require_once(dirname(__FILE__) . '/functions-i18n.php');

define('ZENPHOTO_LOCALE', setMainDomain());

require_once(dirname(__FILE__) . '/load_objectClasses.php');

$_zp_current_context_stack = array();

$_zp_albumthumb_selector = array(array('field' => '', 'direction' => '', 'desc' => 'random'),
		array('field' => 'id', 'direction' => 'DESC', 'desc' => gettext('most recent')),
		array('field' => 'mtime', 'direction' => '', 'desc' => gettext('oldest')),
		array('field' => 'title', 'direction' => '', 'desc' => gettext('first alphabetically')),
		array('field' => 'hitcounter', 'direction' => 'DESC', 'desc' => gettext('most viewed'))
);

$_zp_missing_album = new TransientAlbum(gettext('missing'));
$_zp_missing_image = new Transientimage($_zp_missing_album, SERVERPATH . '/' . ZENFOLDER . '/images/err-imagenotfound.png');

define('SELECT_IMAGES', 1);
define('SELECT_ALBUMS', 2);
define('SELECT_PAGES', 4);
define('SELECT_ARTICLES', 8);

$_zp_exifvars = zpFunctions::exifvars();
$_locale_Subdomains = zpFunctions::LanguageSubdomains();
