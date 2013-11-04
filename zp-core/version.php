<?php

define('ZENPHOTO_VERSION', '1.4.5.7');
define('ZENPHOTO_FULL_RELEASE', trim(file_get_contents(dirname(__FILE__) . '/githead')));
define('ZENPHOTO_RELEASE', substr(ZENPHOTO_FULL_RELEASE, 0, 10));
?>
