<?php
$buttonlist[] = array(
								'XSRFTag'=>'clear_cache',
								'category'=>gettext('cache'),
								'enable'=>true,
								'button_text'=>gettext('Purge RSS cache'),
								'formname'=>'purge_rss_cache.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=clear_rss_cache',
								'icon'=>'images/edit-delete.png',
								'alt'=>'',
								'title'=>gettext('Delete all files from the RSS cache'),
								'hidden'=>'<input type="hidden" name="action" value="clear_rss_cache" />',
								'rights'=> ADMIN_RIGHTS
);
?>