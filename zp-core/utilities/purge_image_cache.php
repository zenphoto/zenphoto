<?php
$button_text = gettext("Purge Image cache");
$button_action = WEBPATH.'/'.ZENFOLDER.'/admin.php?action=action=clear_cache';
$button_icon = 'images/edit-delete.png'; 
$button_title = gettext("Clears the image cache. Images will be re-cached as they are viewed.");
$button_alt = gettext("Purge Image cache");
$button_hidden =  '<input type="hidden" name="action" value="clear_cache" />';
$button_rights =  ADMIN_RIGHTS;
$button_XSRFTag = 'clear_cache';
?>