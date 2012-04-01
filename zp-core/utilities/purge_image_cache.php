<?php
$buttonlist[] = array(
								'XSRFTag'=>'clear_cache',
								'category'=>gettext('cache'),
								'enable'=>true,
								'button_text'=>gettext('Purge Image cache'),
								'formname'=>'purge_image_cache.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=action=clear_cache',
								'icon'=>'images/edit-delete.png',
								'alt'=>'',
								'title'=>gettext('Delete all files from the Image cache'),
								'hidden'=>'<input type="hidden" name="action" value="clear_cache" />',
								'rights'=> ADMIN_RIGHTS
);
?>