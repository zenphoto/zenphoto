<?php
/**
 * Displays database info
 * 
 * @package zpcore\admin\utilities
 */

$buttonlist[] = array(
		'XSRFTag' => 'refresh',
		'category' => gettext('Database'),
		'enable' => true,
		'button_text' => gettext('Refresh Metadata'),
		'formname' => 'refresh_metadata',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-refresh-metadata.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/refresh.png',
		'alt' => '',
		'title' => gettext('Forces a refresh of the metadata for all images and albums.'),
		'hidden' => '',
		'rights' => MANAGE_ALL_ALBUM_RIGHTS | ADMIN_RIGHTS,
		'confirmclick' => gettext('Refreshing metadata will overwrite existing data. This cannot be undone!')
);