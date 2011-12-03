<?php
/**
 *
 * This plugin controls whether the Ajax Filemanager utility is enabled for Zenphoto.
 *
 * This utility is used by the "image insert" feature of tinyMCE and as the handler for
 * the "Files" subtab on the "Upload" tab.
 *
 * We believe that the filemanager is now completely secure. However we understand that
 * the damage caused by the previous vulnerabilities may lead some sites to decide that
 * the benifits of the filemanager are not worht the risks. Thus this plugin gives you the
 * choice of enabling the feature or not.
 *
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Ajax Filemanager files handling in tinyMCE and the "files" upload tab');

$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.2';

?>