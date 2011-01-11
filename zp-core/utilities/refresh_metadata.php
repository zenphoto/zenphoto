<?php
$button_text = gettext("Refresh Metadata");
$button_action = WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php';
$button_icon = 'images/refresh.png';
$button_title = gettext("Forces a refresh of the metadata for all images and albums.");
$button_alt = gettext("Forces a refresh of the metadata for all images and albums.");
$button_rights = MANAGE_ALL_ALBUM_RIGHTS;
$button_XSRFTag = 'refresh';
?>