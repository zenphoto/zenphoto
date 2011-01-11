<?php
$button_text = gettext("Refresh the Database");
$button_action = WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php?prune';
$button_icon = 'images/refresh.png'; 
$button_title = gettext("Cleans the database and removes any orphan entries for comments, images, and albums.");
$button_alt = gettext("Refresh the Database");
$button_hidden = '<input type="hidden" name="prune" value="true" />';
$button_rights = ADMIN_RIGHTS;
$button_XSRFTag = 'refresh';
?>