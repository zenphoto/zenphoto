<?php
$button_text = gettext("Reset all hitcounters");
$button_action = WEBPATH.'/'.ZENFOLDER.'/admin.php?action=reset_hitcounters=true';
$button_icon = 'images/reset1.png'; 
$button_title = gettext("Sets all hitcounters to zero.");
$button_alt = gettext('Reset hitcounters');
$button_hidden = '<input type="hidden" name="action" value="reset_hitcounters" />';
$button_rights = ADMIN_RIGHTS;
$button_XSRFTag = 'hitcounter';
?>