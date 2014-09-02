<?php

/*
 * isolated so that the back end knows....
 */

zp_register_filter('theme_head', 'printZenJavascripts', 9999);
zp_register_filter('theme_head', 'adminToolbox');
zp_register_filter('zenphoto_information', 'exposeZenPhotoInformations');
?>