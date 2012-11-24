<?php // This file contains version info only and is automatically updated. DO NOT EDIT.
define('ZENPHOTO_VERSION', '1.4.4-DEV');
list($_release, $_full_release) = explode("\n",file_get_contents(dirname(__FILE__).'/githead'));
define('ZENPHOTO_RELEASE', trim($_release));
define('ZENPHOTO_FULL_RELEASE', trim($_full_release));
unset($_release);
unset($_full_release);
?>
