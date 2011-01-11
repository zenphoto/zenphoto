<?php
$button_text = gettext("Check for update");
$button_action = WEBPATH.'/'.ZENFOLDER.'/admin.php?action=check_for_update';
$button_icon = 'images/accept.png'; 
$button_title = gettext("Queries the Zenphoto web site for the latest version and compares that with the one that is running.");
$button_alt = gettext('Check for update');
$button_hidden =  '<input type="hidden" name="action" value="check_for_update" />';
$button_rights = ADMIN_RIGHTS;
$button_enable = is_connected();
?>