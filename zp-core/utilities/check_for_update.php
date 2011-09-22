<?php
$buttonlist[] = array(
								'category'=>gettext('admin'),
								'enable'=>is_connected(),
								'button_text'=>gettext('Check for update'),
								'formname'=>'check_for_update.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=check_for_update',
								'icon'=>'images/accept.png',
								'title'=>gettext("Queries the Zenphoto web site for the latest version and compares that with the one that is running."),
								'alt'=>gettext('Check for update'),
								'hidden'=>'<input type="hidden" name="action" value="check_for_update" />',
								'rights'=> ADMIN_RIGHTS
								);
?>