<?php
/**
 *  Function wrapper for theme functions
 * @package zpcore\functions\template
 */
// force UTF-8 Ø

require_once(dirname(__FILE__) . '/functions/functions.php');
if (!defined('SEO_FULLWEBPATH')) {
	define('SEO_FULLWEBPATH', FULLWEBPATH);
	define('SEO_WEBPATH', WEBPATH);
}
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-functions-general.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-functions-gallery.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-functions-album.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-functions-image.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-functions-search.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions/template-filters.php');