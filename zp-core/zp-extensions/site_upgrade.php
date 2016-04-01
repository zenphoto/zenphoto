<?php
/**
 * Provides a means to close access to your site for upgrading and testing.
 *
 * A button is placed in the <i>Utility functions</i> section of the Admin overview page to allow you
 * to manage the state of your site. This button changes function depending on the state of the site. You may
 * <i>close</i> a site, move a closed site to <i>test mode</i>, and then <i>open</i> the site.
 *
 * <i>Closing</i> the site will cause links to the site <i>front end</i> to be redirected to a script in
 * the folder <var>plugins/site_upgrade</var>. Access to the admin pages remains available.
 * You should close the site while
 * you are uploading a new Zenphoto release so that users will not catch the site in an unstable state.
 *
 * After you have uploaded the new release and run Setup you place the site in <i>test mode</i>. In this mode
 * only logged in <i>Administrators</i> can access the <i>front end</i>. You can then, as the administrator, view the
 * site to be sure that all your changes are as you wish them to be.
 *
 * Once your testing is completed you <i>open</i> your site to all visitors.
 *
 * Change the files in <var>plugins/site_upgrade</var> to meet your needs. (<b>Note</b> these files will
 * be copied to that folder during setup the first time you do an install. Setup will not overrite any existing
 * versions of these files, so if a change is made to the Zenphoto versions of the files you will have to update
 * your copies either by removing them before running setup or by manually applying the Zenphoto changes to your
 * files.)
 *
 *
 * The plugin works best if <var>mod_rewrite</var> is active and the <var>.htaccess</var> file exists. If this is not the case
 * the plugin will still work in most cases. However if you the release you are upgrading to has significant changes involving
 * plugin loading of the front-end site there may be PHP failures due if the site is accessed while the files
 * being uploaded are in a mixed release state.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 1000 | ADMIN_PLUGIN | FEATURE_PLUGIN;
$plugin_description = gettext('Utility to divert access to the gallery to a screen saying the site is upgrading.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = (MOD_REWRITE) ? false : gettext('<em>mod_rewrite</em> is not enabled. This plugin may not work without rewrite redirection if the upgrade is significantly different than the running release.');

switch (OFFSET_PATH) {
	case 0:
		$state = @$_zp_conf_vars['site_upgrade_state'];
		if ((!zp_loggedin(ADMIN_RIGHTS) && $state == 'closed_for_test') || $state == 'closed') {
			if (isset($_zp_conf_vars['special_pages']['page']['rewrite'])) {
				$page = $_zp_conf_vars['special_pages']['page']['rewrite'];
			} else {
				$page = 'page';
			}
			if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
				header('location: ' . WEBPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.php');
				exit();
			}
		}
		break;
	default:
		zp_register_filter('admin_utilities_buttons', 'site_upgrade_button');
		zp_register_filter('installation_information', 'site_upgrade_status');

		function site_upgrade_status() {
			global $_zp_conf_vars;
			switch (@$_zp_conf_vars['site_upgrade_state']) {
				case 'closed':
					?>
					<li>
						<?php echo gettext('Site status:'); ?> <span style="color:RED"><strong><?php echo gettext('The site is closed!'); ?></strong></span>
					</li>
					<?php
					break;
				case 'closed_for_test';
					?>
					<li>
						<?php echo gettext('Site status:'); ?> <span style="color:RED"><strong><?php echo gettext('The site is in test mode!'); ?></strong></span>
					</li>
					<?php
					break;
				default:
					?>
					<li>
						<?php echo gettext('Site status:'); ?> <strong><?php echo gettext('The site is opened'); ?></strong>
					</li>
					<?php
					break;
			}
		}

		function site_upgrade_button($buttons) {
			global $_zp_conf_vars;
			$ht = @file_get_contents(SERVERPATH . '/.htaccess');
			preg_match('|[# ][ ]*RewriteRule(.*)plugins/site_upgrade/closed|', $ht, $matches);
			if (!$matches || strpos($matches[0], '#') === 0) {
				$state = @$_zp_conf_vars['site_upgrade_state'];
			} else {
				$state = 'closed';
			}
			$buttons[] = array(
							'XSRFTag'			 => 'site_upgrade_refresh',
							'category'		 => gettext('Admin'),
							'enable'			 => true,
							'button_text'	 => gettext('Restore site_upgrade files'),
							'formname'		 => 'refreshHTML',
							'action'			 => WEBPATH . '/' . ZENFOLDER . '/admin.php',
							'icon'				 => 'images/refresh.png',
							'title'				 => gettext('Restores the files in the "plugins/site_upgrade" folder to their default state. Note: this will overwrite any custom edits you may have made.'),
							'alt'					 => '',
							'hidden'			 => '<input type="hidden" name="refreshHTML" value="1" />',
							'rights'			 => ADMIN_RIGHTS
			);
			switch ($state) {
				case 'closed':
					$buttons[] = array(
									'XSRFTag'			 => 'site_upgrade',
									'category'		 => gettext('Admin'),
									'enable'			 => true,
									'button_text'	 => gettext('Site » test mode'),
									'formname'		 => 'site_upgrade.php',
									'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
									'icon'				 => 'images/lock_open.png',
									'title'				 => gettext('Make the site available for viewing administrators only.'),
									'alt'					 => '',
									'hidden'			 => '<input type="hidden" name="siteState" value="closed_for_test" />',
									'rights'			 => ADMIN_RIGHTS
					);
					break;
				case 'closed_for_test':
					$buttons[] = array(
									'XSRFTag'			 => 'site_upgrade',
									'category'		 => gettext('Admin'),
									'enable'			 => true,
									'button_text'	 => gettext('Site » open'),
									'formname'		 => 'site_upgrade.php',
									'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
									'icon'				 => 'images/lock.png',
									'title'				 => gettext('Make site available for viewing.'),
									'alt'					 => '',
									'hidden'			 => '<input type="hidden" name="siteState" value="open" />',
									'rights'			 => ADMIN_RIGHTS
					);
					break;
				default:
					$buttons[] = array(
									'XSRFTag'			 => 'site_upgrade',
									'category'		 => gettext('Admin'),
									'enable'			 => true,
									'button_text'	 => gettext('Site » close'),
									'formname'		 => 'site_upgrade.php',
									'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
									'icon'				 => 'images/lock.png',
									'title'				 => gettext('Make site unavailable for viewing by redirecting to the "closed.html" page.'),
									'alt'					 => '',
									'hidden'			 => '<input type="hidden" name="siteState" value="closed" />',
									'rights'			 => ADMIN_RIGHTS
					);
					break;
			}

			return $buttons;
		}

		if (isset($_REQUEST['refreshHTML'])) {
			XSRFdefender('site_upgrade_refresh');
			$_GET['report'] = gettext('site_upgrade files Restored to original.');
		} else {
			break;
		}
	case 2:
		mkdir_recursive(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/', FOLDER_MOD);
		copy(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/closed.php', SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.php');
		if (isset($_REQUEST['refreshHTML']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.htm')) {
			$html = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/closed.htm');
			$html = sprintf($html, sprintf(gettext('%s upgrade'), $_zp_gallery->getTitle()), FULLWEBPATH . '/' . ZENFOLDER .  '/images/zen-logo.png', sprintf(gettext('<strong><em>%s</em></strong> is undergoing an upgrade'), $_zp_gallery->getTitle()), '<a href="' . FULLWEBPATH . '/index.php">' . gettext('Please return later') . '</a>', FULLWEBPATH . '/index.php');
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.htm', $html);
		}
		if (isset($_REQUEST['refreshHTML']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss_closed.xml')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/rss.php');

			class setupRSS extends RSS {

				public function getitems() {
					$this->feedtype = 'setup';
					$items = array();
					$items[] = array('title'						 => gettext('RSS suspended'),
									'link'						 => '',
									'enclosure'				 => '',
									'category'				 => '',
									'media_content'		 => '',
									'media_thumbnail'	 => '',
									'pubdate'					 => date("r", time()),
									'desc'						 => gettext('The RSS feed is currently not available.'));
					return $items;
				}

				protected function startCache() {

				}

				protected function endCache() {

				}

			}

			$obj = new setupRSS(array('rss' => 'site_closed'));
			ob_start();
			$obj->printFeed();
			$xml = ob_get_contents();
			ob_end_clean();
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/rss-closed.xml', $xml);
		}
		if (isset($_REQUEST['refreshHTML']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/externalFeed_closed.xml')) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/externalFeed.php');

			class setupexternalFeed extends externalFeed {

				public function getitems() {
					$this->feedtype = 'setup';
					$items = array();
					$items[] = array('title'						 => gettext('externalFeed suspended'),
									'link'						 => '',
									'enclosure'				 => '',
									'category'				 => '',
									'media_content'		 => '',
									'media_thumbnail'	 => '',
									'pubdate'					 => date("r", time()),
									'desc'						 => gettext('The external feed is currently not available.'));
					return $items;
				}

				protected function startCache() {

				}

				protected function endCache() {

				}

			}

			$obj = new setupexternalFeed(array('external' => 'site_closed'));
			ob_start();
			$obj->printFeed();
			$xml = ob_get_contents();
			ob_end_clean();
			file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/externalFeed-closed.xml', $xml);
		}
		setOptionDefault('zp_plugin_site_upgrade', $plugin_is_filter);
		break;
}
?>
