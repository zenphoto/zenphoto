<?php
$buttonlist[] = array(
								'XSRFTag'=>'refresh',
								'category'=>gettext('Database'),
								'enable'=>true,
								'button_text'=>gettext('Refresh the Database'),
								'formname'=>'refresh_database.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php?prune',
								'icon'=>'images/refresh.png',
								'alt'=>'',
								'title'=>gettext('Perform a garbage collection of the Database'),
								'hidden'=>'<input type="hidden" name="prune" value="true" />',
								'rights'=> ADMIN_RIGHTS
);
?>