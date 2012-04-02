<?php
/**
 *
 * This plugin controls the Ajax Filemanager utility for Zenphoto.
 *
 * This utility is used by the <i>image insert</i> feature of <var>tinyMCE</var> and as the handler for
 * the <i>Files</i> subtab on the <i>Upload</i> tab.
 *
 * We believe that the filemanager is now completely secure. However we understand that
 * the damage caused by the previous vulnerabilities may lead some sites to decide that
 * the benifits of the filemanager are not worth the risks. Thus this plugin gives you the
 * choice of enabling the feature or not. If disabled <b>no</b> access is allowed to the Ajax FIlemanager scripts.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Provides file handling in tinyMCE and the "files" upload tab.');
$plugin_author = "Stephen Billard (sbillard)";

$htaccess = SERVERPATH.'/'.DATA_FOLDER.'/ajaxfilemanager/.htaccess';
if (!file_exists($htaccess)) {
	@mkdir(SERVERPATH.'/'.DATA_FOLDER.'/ajaxfilemanager/');
	file_put_contents($htaccess, "deny from all\n");
}
@chmod($htaccess,0444);
unset($htaccess);
if (OFFSET_PATH!=99 && session_id() != '') {
	unset($_SESSION['XSRFToken']);
}
?>