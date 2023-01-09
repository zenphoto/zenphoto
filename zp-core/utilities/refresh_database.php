<?php
/**
 * Refreshes the database with the albmums on the filesystems 
 * 
 * @package zpcore\admin\utilities
 */

$buttonlist[] = array(
		'XSRFTag' => 'refresh',
		'category' => gettext('Database'),
		'enable' => true,
		'button_text' => gettext('Refresh the Database'),
		'formname' => 'refresh_database.php',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin-refresh-metadata.php?prune',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/refresh.png',
		'alt' => '',
		'title' => gettext('Perform a garbage collection of the Database'),
		'hidden' => '<input type="hidden" name="prune" value="true" />',
		'rights' => ADMIN_RIGHTS
);
?>