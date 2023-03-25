<?php

/**
 * Clears the searchcache
 * 
 * @package zpcore\admin\utilities
 */
$buttonlist[] = array(
		'category' => gettext('Cache'),
		'enable' => true,
		'button_text' => gettext('Purge search cache'),
		'formname' => 'clearcache_button',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_search_cache',
		'icon' => 'images/edit-delete.png',
		'title' => gettext('Clear the static search cache.'),
		'alt' => '',
		'hidden' => '<input type="hidden" name="action" value="clear_search_cache">',
		'rights' => ADMIN_RIGHTS,
		'XSRFTag' => 'ClearSearchCache'
);