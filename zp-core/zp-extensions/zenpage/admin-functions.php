<?php
/**
 * Zenpage admin functions loader
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */
global $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;

require_once dirname(__FILE__) . '/admin-functions/admin-functions-general.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-news.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-newscategories.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-pages.php';
require_once dirname(__FILE__) . '/admin-functions/admin-functions-stats.php';

Zenpage::expiry();




