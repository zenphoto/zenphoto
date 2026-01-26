<?php

/*
 * isolated so that the back end knows....
 */

filter::registerFilter('theme_head', 'printZenJavascripts', 9999);
filter::registerFilter('theme_body_close', 'adminToolbox');
filter::registerFilter('zenphoto_information', 'exposeZenPhotoInformations');
?>