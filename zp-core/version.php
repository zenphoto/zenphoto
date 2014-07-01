<?php

// This file contains version info only and is automatically updated. DO NOT EDIT.
define('ZENPHOTO_VERSION', '1.0.0-RC5');
define('ZENPHOTO_FULL_RELEASE', trim(file_get_contents(dirname(__FILE__) . '/githead')));
define('ZENPHOTO_RELEASE', substr(ZENPHOTO_FULL_RELEASE, 0, 10));
?>
