<?php
/**
 * Switches site into <i>update</i> mode for Zenphoto upgrades. When the site is in <i>update</i> mode, links
 * to the gallery pages will be redirected to a single page that indicates the site is undergoing
 * an upgrade.
 *
 * Requires mod_rewrite to be active and that the <var>.htaccess</var> file exists
 *
 * This plugin will place a file in <var>plugins/site_upgrade</var> to handle the redirection when the
 * site is closed. Change this file to meet your needs.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */

switch (OFFSET_PATH) {
	case 0:
		break;
	case 2:
		if (!file_exists(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/')) {
			mkdir_recursive(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/', FOLDER_MOD);
			$html = file_get_contents(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/closed.php');
			$html = sprintf($html, sprintf(gettext('%s upgrade'),$_zp_gallery->getTitle()),FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/closed.png',sprintf(gettext('<strong><em>%s</em></strong> is undergoing an upgrade'),$_zp_gallery->getTitle()), gettext('Please return later'));
																			file_put_contents(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.php', $html);
		} else {
			if (file_exists(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.html') && !file_exists(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.php')) {
				@copy(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.html', SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/site_upgrade/closed.php');
			}
		}
		break;
	default:
		$plugin_is_filter = 5|ADMIN_PLUGIN;
		$plugin_description = gettext('Utility to divert access to the gallery to a screen saying the site is upgrading.');
		$plugin_author = "Stephen Billard (sbillard)";
		$plugin_disable = (MOD_REWRITE) ? false : gettext('The <em>mod_rewrite</em> must be enabled');

		zp_register_filter('admin_utilities_buttons', 'site_upgrade_button');
		zp_register_filter('installation_information', 'site_upgrade_status');

		function site_upgrade_status() {
			$ht = @file_get_contents(SERVERPATH.'/.htaccess');
			if ($ht) {
				preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|',$ht,$matches);
				if (strpos($matches[0],'#')!==0) {
					?>
					<li><?php echo gettext('Site status:');?> <span style="color:RED"><strong><?php echo gettext('The site is closed!'); ?></strong></span></li>
					<?php
				} else {
					?>
					<li><?php echo gettext('Site status:');?> <strong><?php echo gettext('The site is opened'); ?></strong></li>
					<?php
				}
			}
		}

		function site_upgrade_button($buttons) {
			$ht = @file_get_contents(SERVERPATH.'/.htaccess');
			if (empty($ht)) {
				$buttons[] = array(
						'XSRFTag'=>'site_upgrade',
						'category'=>gettext('Admin'),
						'enable'=>false,
						'button_text'=>gettext('Close the site.'),
						'formname'=>'site_upgrade.php',
						'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade.php',
						'icon'=>'images/action.png',
						'title'=>gettext('There is no .htaccess file'),
						'alt'=>'',
						'hidden'=>'',
						'rights'=> ADMIN_RIGHTS
				);
			} else {
				preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|',$ht,$matches);
				if (strpos($matches[0],'#')===0) {
					$buttons[] = array(
							'XSRFTag'=>'site_upgrade',
							'category'=>gettext('Admin'),
							'enable'=>true,
							'button_text'=>gettext('Close site'),
							'formname'=>'site_upgrade.php',
							'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/site_upgrade.php',
							'icon'=>'images/lock.png',
							'title'=>gettext('Make site unavailable for viewing by redirecting to the "closed.html" page.'),
							'alt'=>'',
							'hidden'=>'',
							'rights'=> ADMIN_RIGHTS
					);
				} else {
					$buttons[] = array(
							'XSRFTag'=>'site_upgrade',
							'category'=>gettext('Admin'),
							'enable'=>true,
							'button_text'=>gettext('Open the site'),
							'formname'=>'site_upgrade.php',
							'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/site_upgrade/site_upgrade.php',
							'icon'=>'images/lock_open.png',
							'title'=>gettext('Make the site available for viewing by removing the redirection.'),
							'alt'=>'',
							'hidden'=>'',
							'rights'=> ADMIN_RIGHTS
				);
				}
			}
			return $buttons;
		}
		break;
}


?>
