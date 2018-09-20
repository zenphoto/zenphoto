<?php

/*
 * The reset code for hitcounters
 */

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-functions.php');
if (isset($_GET['action'])) {
	if (sanitize($_GET['action']) == 'reset_all_hitcounters') {
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			// prevent nefarious access to this page.
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL());
			exitZP();
		}
		zp_session_start();
		XSRFdefender('hitcounter');
		$_zp_gallery->set('hitcounter', 0);
		$_zp_gallery->save();
		query('UPDATE ' . prefix('albums') . ' SET `hitcounter`= 0');
		query('UPDATE ' . prefix('images') . ' SET `hitcounter`= 0');
		query('UPDATE ' . prefix('news') . ' SET `hitcounter`= 0');
		query('UPDATE ' . prefix('pages') . ' SET `hitcounter`= 0');
		query('UPDATE ' . prefix('news_categories') . ' SET `hitcounter`= 0');
		purgeOption('page_hitcounters');
		query("DELETE FROM " . prefix('plugin_storage') . " WHERE `type` = 'hitcounter' AND `subtype`='rss'");
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg=' . gettext('All hitcounters have been set to zero.'));
		exitZP();
	}
}
