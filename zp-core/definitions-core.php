<?php
global $_zp_current_context_stack, $_zp_html_cache;

require_once dirname(__FILE__) . '/definitions-basic.php';
require_once SERVERPATH . '/' . ZENFOLDER . '/functions/functions.php';
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-filter.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/deprecated/functions-filter.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/functions-kses.php');
require_once SERVERPATH . '/' . ZENFOLDER . '/libs/functions-htmlawed.php';
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-_zp_captcha.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/classes/class-_zp_html_cache.php');

$_zp_captcha = new _zp_captcha(); // this will be overridden by the plugin if enabled.
$_zp_html_cache = new _zp_HTML_cache(); // this will be overridden by the plugin if enabled.
//setup session before checking for logon cookie
require_once(SERVERPATH . '/' . ZENFOLDER . '/definitions-i18n.php');

if (GALLERY_SESSION) {
	zp_session_start();
}

define('ZENPHOTO_LOCALE', i18n::setMainDomain());

require_once(SERVERPATH . '/' . ZENFOLDER . '/load_objectClasses.php');

$_zp_current_context_stack = array();

$_zp_albumthumb_selector = array(array('field' => '', 'direction' => '', 'desc' => 'random'),
		array('field' => 'id', 'direction' => 'DESC', 'desc' => gettext('most recent')),
		array('field' => 'mtime', 'direction' => '', 'desc' => gettext('oldest')),
		array('field' => 'title', 'direction' => '', 'desc' => gettext('first alphabetically')),
		array('field' => 'hitcounter', 'direction' => 'DESC', 'desc' => gettext('most viewed'))
);

$_zp_missing_album = new AlbumBase(gettext('missing'), false);
$_zp_missing_image = new Transientimage($_zp_missing_album, SERVERPATH . '/' . ZENFOLDER . '/images_errors/err-imagenotfound.png');

if (extensionEnabled('zenpage')) {
	if (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'news') {
		define('ZP_NEWS_ENABLED', true);
	} else {
		define('ZP_NEWS_ENABLED', false);
	}
	if (getOption('enabled-zenpage-items') == 'news-and-pages' || getOption('enabled-zenpage-items') == 'pages') {
		define('ZP_PAGES_ENABLED', true);
	} else {
		define('ZP_PAGES_ENABLED', false);
	}
} else {
	define('ZP_NEWS_ENABLED', false);
	define('ZP_PAGES_ENABLED', false);
}

filter::registerFilter('content_macro', 'getCookieInfoMacro');
setexifvars();