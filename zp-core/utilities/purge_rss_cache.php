<?php
$buttonlist[] = array(
								'XSRFTag'=>'clear_cache',
								'category'=>gettext('cache'),
								'enable'=>'1',
								'button_text'=>gettext('Purge RSS cache'),
								'formname'=>'purge_rss_cache.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=clear_rss_cache',
								'icon'=>'images/edit-delete.png',
								'title'=>'',
								'alt'=>gettext('Purge RSS cache'),
								'hidden'=>'<input type="hidden" name="action" value="clear_rss_cache" />',
								'rights'=> ADMIN_RIGHTS
);
?>