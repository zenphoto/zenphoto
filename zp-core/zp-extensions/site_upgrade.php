<?php
/**
 * Switches site into "update" mode for Zenphoto upgrades. When the site is in "update" mode, links
 * to the gallery pages will be redirected to a single page that indicates the site is undergoing
 * an upgrade.
 *
 * Requires mod_rewrite to be active and that the .htaccess file exists
 *
 * Change the files in <code>plugins/site_upgrade</code> to meet your needs. (Note these files will
 * be copied to that folder the first time the plugin runs.)
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

if (defined('OFFSET_PATH')) {

	$plugin_is_filter = 5|ADMIN_PLUGIN;
	$plugin_description = gettext('Utility to divert access to the gallery to a screen saying the site is upgrading.');
	$plugin_author = "Stephen Billard (sbillard)";
	$plugin_disable = (MOD_REWRITE) ? false : gettext('The <em>mod_rewrite</em> must be enabled');

	zp_register_filter('admin_utilities_buttons', 'site_upgrade_button');

	if (!file_exists(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/close.html')) {
		mkdir_recursive(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/', FOLDER_MOD);
		$html = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/closed.html');
		$html = sprintf($html, sprintf(gettext('%s upgrade'),$_zp_gallery->getTitle()),sprintf(gettext('<strong><em>%s</em></strong> is undergoing an upgrade'),$_zp_gallery->getTitle()), gettext('Please return later'));
		file_put_contents(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.html', $html);
		copy(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/closed.png', SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.png');
	}

	function site_upgrade_button($buttons) {
		$enable = 1;
		$hidden = '';
		$ht = @file_get_contents(SERVERPATH.'/.htaccess');
		if (empty($ht)) {
			$title = gettext('There is no .htaccess file');
			$enable = 0;
			$button_text = gettext('Close the site.');
			$image = 'images/action.png';
		} else {
			preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|',$ht,$matches);
			if (strpos($matches[0],'#')===0) {
				$button_text = gettext('Close site');
				$title = gettext('Make site unavialable for viewing, redirect to closed sign.');
				$image = 'images/lock.png';
			} else {
				$button_text = gettext('Open the site');
				$title = gettext('Mark site available for viewing.');
				$image = 'images/lock_open.png';
			}
		}
		$buttons[] = array(
											'XSRFTag'=>'site_upgrade',
											'category'=>gettext('admin'),
											'enable'=>$enable,
											'button_text'=>$button_text,
											'formname'=>'site_upgrade.php',
											'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade.php',
											'icon'=>$image,
											'title'=>$title,
											'alt'=>$title,
											'hidden'=>$hidden,
											'rights'=> ADMIN_RIGHTS
		);
		return $buttons;
	}

} else {

	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)).'/admin-globals.php');

	admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL(__FILE__));

	$htpath = SERVERPATH.'/.htaccess';
	$ht = file_get_contents($htpath);

	preg_match_all('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|',$ht,$matches);
	if (strpos($matches[0][1],'#')===0) {
		foreach ($matches[0] as $match) {
			$ht = str_replace($match, ' '.substr($match,1), $ht);
		}
		@chmod($htpath, 0777);
		file_put_contents($htpath, $ht);
		@chmod($htpath,0444);
		$report = gettext('Site is now marked in upgrade.');
	} else {
		foreach ($matches[0] as $match) {
			$ht = str_replace($match, preg_replace('/^ /','# ',$match), $ht);
		}
		@chmod($htpath, 0777);
		file_put_contents($htpath, $ht);
		@chmod($htpath, 0444);
		$report = gettext('Site is viewable.');
	}
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?report='.$report);
}

?>
