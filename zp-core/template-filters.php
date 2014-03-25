<?php

/*
 * isolated so that the back end knows....
 */

zp_register_filter('theme_head', 'printZenJavascripts', 9999);
zp_register_filter('theme_body_close', 'adminToolbox');
zp_register_filter('zenphoto_information', 'exposeZenPhotoInformations');
?>