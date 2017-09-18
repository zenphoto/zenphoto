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
 * you are uploading a new release so that users will not catch the site in an unstable state.
 *
 * After you have uploaded the new release and run Setup you place the site in <i>test mode</i>. In this mode
 * only logged in <i>Administrators</i> can access the <i>front end</i>. You can then, as the administrator, view the
 * site to be sure that all your changes are as you wish them to be.
 *
 * Once your testing is completed you <i>open</i> your site to all visitors.
 *
 * Change the files in <var>plugins/site_upgrade</var> to meet your needs. (<b>Note</b> these files will
 * be copied to that folder during setup the first time you do an install. Setup will not overrite any existing
 * versions of these files, so if a change is made to the distributed versions of the files you will have to update
 * your copies either by removing them before running setup or by manually applying the distributed file changes to your
 * files.)
 *
 *
 * The plugin works best if <var>mod_rewrite</var> is active and the <var>.htaccess</var> file exists. If this is not the case
 * the plugin will still work in most cases. However if you the release you are upgrading to has significant changes involving
 * plugin loading of the front-end site there may be PHP failures due if the site is accessed while the files
 * being uploaded are in a mixed release state.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = defaultExtension(1000 | ADMIN_PLUGIN | FEATURE_PLUGIN);
$plugin_description = gettext('Utility to divert access to the gallery to a screen saying the site is upgrading.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_notice = (MOD_REWRITE) ? false : gettext('<em>mod_rewrite</em> is not enabled. This plugin may not work without rewrite redirection if the upgrade is significantly different than the running release.');

if (OFFSET_PATH) {
	$_site_filelist = array(
			'closed.htm' => '+', // copy and update
			'closed.php' => '*', // just copy
// "feed" plugins. The convention is that the file name is plugin prefix-closed.xml
			'rss-closed.xml' => 'RSS', // create from RSS class
			'externalFeed-closed.xml' => 'externalFeed' // create from externamFeed class
	);
}

switch (OFFSET_PATH) {
	case 0:

		function site_upgrade_notice($html) {
			?>
			<div style="width: 100%; position: fixed; top: 0px; left: 0px; z-index: 1000;" >
				<p style="text-align: center;">
					<strong style="background-color: #FFEFB7; color:black; padding: 5px;">
						<?php echo gettext('Site is available for testing only.'); ?>
					</strong>
				</p>
			</div>
			<?php
		}

		$state = @$_zp_conf_vars['site_upgrade_state'];
		if ((!zp_loggedin(ADMIN_RIGHTS | DEBUG_RIGHTS) && $state == 'closed_for_test') || $state == 'closed') {
			if (isset($_zp_conf_vars['special_pages']['page']['rewrite'])) {
				$page = $_zp_conf_vars['special_pages']['page']['rewrite'];
			} else {
				$page = 'page';
			}
			if (!preg_match('~' . preg_quote($page) . '/setup_set-mod_rewrite\?z=setup$~', $_SERVER['REQUEST_URI'])) {
				header('location: ' . WEBPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/closed.php');
				exit();
			}
		} else if ($state == 'closed_for_test') {
			zp_register_filter('theme_body_open', 'site_upgrade_notice');
		}
		break;
	default:
		zp_register_filter('admin_utilities_buttons', 'site_upgrade_button');
		zp_register_filter('installation_information', 'site_upgrade_status');
		zp_register_filter('admin_note', 'site_upgrade_note');

		function site_upgrade_note($where) {
			global $_zp_conf_vars;
			switch (@$_zp_conf_vars['site_upgrade_state']) {
				case 'closed':
					if ($where == 'Overview') {
						?>
						<form class="dirtylistening" name="site_upgrade_form" id="site_upgrade_form">
						</form>
						<script type="text/javascript">
							window.addEventListener('load', function () {
								$('#site_upgrade_form').dirtyForms('setDirty');
								$.DirtyForms.message = '<?php echo gettext('The site is closed!'); ?>';
							}, false);
						</script>
						<?php
					}
					?>
					<p class="errorbox">
						<strong><?php echo gettext('The site is closed!'); ?></strong>
					</p>
					<?php
					break;
				case 'closed_for_test';
					?>
					<p class="notebox">
						<strong><?php echo gettext('Site is available for testing only.');
					?></strong>
					</p>
					<?php
					break;
			}
		}

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
			global $_zp_conf_vars, $_site_filelist;
			$state = @$_zp_conf_vars['site_upgrade_state'];

			$hash = '';
			foreach ($_site_filelist as $name => $source) {
				if (file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/' . $name)) {
					$hash .= md5(file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/' . $name));
				}
			}

			if ($hash !== getOption('site_upgrade_hash')) {
				$buttons[] = array(
						'XSRFTag' => 'site_upgrade_refresh',
						'category' => gettext('Admin'),
						'enable' => true,
						'button_text' => gettext('Restore site_upgrade files'),
						'formname' => 'refreshHTML',
						'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php',
						'icon' => CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN,
						'title' => gettext('Restores the files in the "plugins/site_upgrade" folder to their default state. Note: this will overwrite any custom edits you may have made.'),
						'alt' => '',
						'hidden' => '<input type="hidden" name="refreshHTML" value="1" />',
						'rights' => ADMIN_RIGHTS
				);
			}
			switch ($state) {
				case 'closed':
					$buttons[] = array(
							'XSRFTag' => 'site_upgrade',
							'category' => gettext('Admin'),
							'enable' => 3,
							'button_text' => gettext('Site » test mode'),
							'formname' => 'site_upgrade',
							'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
							'icon' => LOCK_OPEN,
							'title' => gettext('Make the site available for viewing administrators only.'),
							'onclick' => "$('#site_upgrade_form').dirtyForms('setClean');this.form.submit();",
							'alt' => '',
							'hidden' => '<input type="hidden" name="siteState" value="closed_for_test" />',
							'rights' => ADMIN_RIGHTS
					);
					break;
				case 'closed_for_test':
					$buttons[] = array(
							'XSRFTag' => 'site_upgrade',
							'category' => gettext('Admin'),
							'enable' => 2,
							'button_text' => gettext('Site » open'),
							'formname' => 'site_upgrade',
							'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
							'icon' => LOCK_OPEN,
							'title' => gettext('Make site available for viewing.'),
							'alt' => '',
							'hidden' => '<input type="hidden" name="siteState" value="open" />',
							'rights' => ADMIN_RIGHTS
					);
					list($diff, $needs) = checkSignature(0);
					if (zpFunctions::hasPrimaryScripts() && empty($needs)) {
						?>
						<script type="text/javascript">
							window.addEventListener('load', function () {
								$('#site_upgrade').submit(function () {
									return confirm('<?php echo gettext('Your setup scripts are not protected!'); ?>');
								})
							}, false);
						</script>
						<?php
					}
					break;
				default:
					$buttons[] = array(
							'XSRFTag' => 'site_upgrade',
							'category' => gettext('Admin'),
							'enable' => true,
							'button_text' => gettext('Site » close'),
							'formname' => 'site_upgrade.php',
							'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/site_upgrade.php',
							'icon' => LOCK,
							'title' => gettext('Make site unavailable for viewing by redirecting to the "closed.html" page.'),
							'alt' => '',
							'hidden' => '<input type="hidden" name="siteState" value="closed" />',
							'rights' => ADMIN_RIGHTS
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
		setOptionDefault('site_upgrade_hash', NULL);
		$hash = '';
		foreach ($_site_filelist as $name => $source) {
			if (isset($_REQUEST['refreshHTML']) || !file_exists(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/' . $name)) {
				switch ($source) {
					case '+':
					case '*':
						$data = file_get_contents(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/' . $name);
						if ($source != '*') {
							$data = sprintf($data, sprintf(gettext('%s upgrade'), $_zp_gallery->getTitle()), FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/site_upgrade/closed.png', sprintf(gettext('<strong><em>%s</em></strong> is undergoing an upgrade'), $_zp_gallery->getTitle()), '<a href="' . FULLWEBPATH . '/index.php">' . gettext('Please return later') . '</a>', FULLWEBPATH . '/index.php');
						}
						break;
					default:
// Feed plugin
						$plugin = substr($name, 0, strpos($name, '-')) . '.php';
						$items = array(
								array(
										'title' => sprintf(gettext('%s suspended'), $source),
										'link' => '',
										'enclosure' => '',
										'category' => '',
										'media_content' => '',
										'media_thumbnail' => '',
										'pubdate' => date("r", time()),
										'desc' => sprintf(gettext('The %s feed is currently not available.'), $source)
								)
						);
						require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/' . $plugin);
						$obj = new $source(array(strtolower($source) => 'null'));
						ob_start();
						$obj->printFeed($items);
						$data = ob_get_contents();
						ob_end_clean();
						break;
				}
				file_put_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/' . $name, $data);
				$hash .= md5(file_get_contents(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/site_upgrade/' . $name));
			}
		}
		if ($hash) {
			setOption('site_upgrade_hash', $hash);
		}
		break;
}
?>
