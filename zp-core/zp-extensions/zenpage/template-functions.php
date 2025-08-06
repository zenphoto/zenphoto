<?php
/**
 * zenpage template functions loader
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\template
 */

require_once dirname(__FILE__) . '/template-functions/template-functions-general.php';
require_once dirname(__FILE__) . '/template-functions/template-functions-news.php';
require_once dirname(__FILE__) . '/template-functions/template-functions-newsarticle.php';
require_once dirname(__FILE__) . '/template-functions/template-functions-newscategories.php';
require_once dirname(__FILE__) . '/template-functions/template-functions-pages.php';

Zenpage::expiry();
?>