<?php
$buttonlist[] = array(
								'XSRFTag'=>'refresh',
								'category'=>gettext('Database'),
								'enable'=>true,
								'button_text'=>gettext('Refresh Metadata'),
								'formname'=>'refresh_metadata.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin-refresh-metadata.php',
								'icon'=>'images/refresh.png',
								'alt'=>'',
								'title'=>gettext('Forces a refresh of the metadata for all images and albums.'),
								'hidden'=>'',
								'rights'=> MANAGE_ALL_ALBUM_RIGHTS  | ADMIN_RIGHTS
								);
?>