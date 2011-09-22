<?php
$buttonlist[] = array(
								'XSRFTag'=>'refresh',
								'category'=>gettext('database'),
								'enable'=>'1',
								'button_text'=>gettext('Refresh Metadata'),
								'formname'=>'refresh_metadata.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php',
								'icon'=>'images/refresh.png',
								'title'=>'',
								'alt'=>gettext('Forces a refresh of the metadata for all images and albums.'),
								'hidden'=>'',
								'rights'=> MANAGE_ALL_ALBUM_RIGHTS  | ADMIN_RIGHTS
								);
?>