<?php
/**
 * Part of the featured_image plugin for Zenphoto
 *
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @license: GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/zp-core/admin-globals.php");
admin_securityChecks(ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, '');
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . "/zenpage/zenpage-template-functions.php");
featuredImage::processSelection();

