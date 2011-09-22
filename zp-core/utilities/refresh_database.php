<?php
$buttonlist[] = array(
								'XSRFTag'=>'refresh',
								'category'=>gettext('database'),
								'enable'=>'1',
								'button_text'=>gettext('Refresh the Database'),
								'formname'=>'refresh_database.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php?prune',
								'icon'=>'images/refresh.png',
								'title'=>'',
								'alt'=>gettext('Refresh the Database'),
								'hidden'=>'<input type="hidden" name="prune" value="true" />',
								'rights'=> ADMIN_RIGHTS
);
?>