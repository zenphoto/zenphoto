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

//encrypt/decrypt constants
define('SECRET_KEY', getOption('secret_key_text'));
define('SECRET_IV', getOption('secret_init_vector'));
define('INCRIPTION_METHOD', 'AES-256-CBC');

if (function_exists('openssl_encrypt')) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/class.ncrypt.php');
	$_adminCript = new mukto90\Ncrypt;
	$_adminCript->set_secret_key(SECRET_KEY);
	$_adminCript->set_secret_iv(SECRET_IV);
	$_adminCript->set_cipher(INCRIPTION_METHOD);
} else {
	$_adminCript = NULL;
}

require_once(dirname(__FILE__) . '/load_objectClasses.php');

$_zp_albumthumb_selector = array(array('field' => '', 'direction' => '', 'desc' => 'random'),
		array('field' => 'id', 'direction' => 'DESC', 'desc' => gettext('most recent')),
		array('field' => 'mtime', 'direction' => '', 'desc' => gettext('oldest')),
		array('field' => 'title', 'direction' => '', 'desc' => gettext('first alphabetically')),
		array('field' => 'hitcounter', 'direction' => 'DESC', 'desc' => gettext('most viewed'))
);

$_zp_current_context_stack = array();

$_zp_missing_album = new TransientAlbum(gettext('missing'));
$_zp_missing_image = new Transientimage($_zp_missing_album, SERVERPATH . '/' . ZENFOLDER . '/images/err-imagenotfound.png');

define('SELECT_IMAGES', 1);
define('SELECT_ALBUMS', 2);
define('SELECT_PAGES', 4);
define('SELECT_ARTICLES', 8);

$_zp_exifvars = zpFunctions::exifvars();
$_locale_Subdomains = zpFunctions::LanguageSubdomains();

//	use this for labeling "News" pages, etc.
define('NEWS_LABEL', get_language_string(getSerializedArray(getOption('zenpage_news_label'))));

$_tagURLs_tags = array('{*FULLWEBPATH*}', '{*WEBPATH*}', '{*ZENFOLDER*}', '{*PLUGIN_FOLDER*}', '{*USER_PLUGIN_FOLDER*}');
$_tagURLs_values = array(FULLWEBPATH, WEBPATH, ZENFOLDER, PLUGIN_FOLDER, USER_PLUGIN_FOLDER);
