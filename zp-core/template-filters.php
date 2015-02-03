<?php

/*
 * isolated so that the back end knows....
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */

zp_register_filter('theme_head', 'printThemeHeadItems', 9999);
zp_register_filter('theme_body_close', 'adminToolbox');
zp_register_filter('zenphoto_information', 'exposeZenPhotoInformations');
?>