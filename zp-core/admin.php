<?php
/**
 * admin.php is the main script for administrative functions.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

/* Don't put anything before this line! */
define('OFFSET_PATH', 1);

require_once(dirname(__FILE__) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/reconfigure.php');

if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
	require_once( SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/common/gitHubAPI/github-api.php');
}

use Milo\Github;

if (isset($_GET['_zp_login_error'])) {
	$_zp_login_error = sanitize($_GET['_zp_login_error']);
}

checkInstall();
if (time() > getOption('last_garbage_collect') + 864000) {
	$_zp_gallery->garbageCollect();
}
if (isset($_GET['report'])) {
	$class = 'messagebox';
	$msg = sanitize($_GET['report']);
} else {
	$msg = '';
}
if (extensionEnabled('zenpage')) {
	require_once(dirname(__FILE__) . '/' . PLUGIN_FOLDER . '/zenpage/admin-functions.php');
}
if (zp_loggedin()) { /* Display the admin pages. Do action handling first. */
	if (isset($_GET['action'])) {
		$action = sanitize($_GET['action']);
		if ($action == 'external') {
			$needs = ALL_RIGHTS;
		} else {
			$needs = ADMIN_RIGHTS;
		}
		if (zp_loggedin($needs)) {
			switch ($action) {
				/** clear the image cache **************************************************** */
				case "clear_cache":
					XSRFdefender('clear_cache');
					Gallery::clearCache();
					$class = 'messagebox';
					$msg = gettext('Image cache cleared.');
					break;

				/** clear the RSScache ********************************************************** */
				case "clear_rss_cache":
					if (class_exists('RSS')) {
						XSRFdefender('clear_cache');
						$RSS = new RSS(array('rss' => 'null'));
						$RSS->clearCache();
						$class = 'messagebox';
						$msg = gettext('RSS cache cleared.');
					}
					break;

				/** clear the HTMLcache ****************************************************** */
				case 'clear_html_cache':
					XSRFdefender('ClearHTMLCache');
					require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/static_html_cache.php');
					static_html_cache::clearHTMLCache();
					$class = 'messagebox';
					$msg = gettext('HTML cache cleared.');
					break;
				/** clear the search cache ****************************************************** */
				case 'clear_search_cache':
					XSRFdefender('ClearSearchCache');
					SearchEngine::clearSearchCache(NULL);
					$class = 'messagebox';
					$msg = gettext('Search cache cleared.');
					break;
				/** restore the setup files ************************************************** */
				case 'restore_setup':
					XSRFdefender('restore_setup');
					list($diff, $needs) = checkSignature(2);
					if (empty($needs)) {
						$class = 'messagebox';
						$msg = gettext('Setup files restored.');
					} else {
						zp_apply_filter('log_setup', false, 'restore', implode(', ', $needs));
						$class = 'errorbox';
						$msg = gettext('Setup files restore failed.');
					}
					break;

				/** protect the setup files ************************************************** */
				case 'protect_setup':
					XSRFdefender('protect_setup');
					chdir(SERVERPATH . '/' . ZENFOLDER . '/setup/');
					$list = safe_glob('*.php');

					$rslt = array();
					foreach ($list as $component) {
						if ($component == 'setup-functions.php') { // some plugins may need these.
							continue;
						}
						@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, 0777);
						if (@rename(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, SERVERPATH . '/' . ZENFOLDER . '/setup/' . stripSuffix($component) . '.xxx')) {
							@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . stripSuffix($component) . '.xxx', FILE_MOD);
						} else {
							@chmod(SERVERPATH . '/' . ZENFOLDER . '/setup/' . $component, FILE_MOD);
							$rslt[] = $component;
						}
					}
					if (empty($rslt)) {
						zp_apply_filter('log_setup', true, 'protect', gettext('protected'));
						$class = 'messagebox';
						$msg = gettext('Setup files protected.');
					} else {
						zp_apply_filter('log_setup', false, 'protect', implode(', ', $rslt));
						$class = 'errorbox';
						$msg = gettext('Protecting setup files failed.');
					}
					break;

				/** external script return *************************************************** */
				case 'external':
					if (isset($_GET['error'])) {
						$class = sanitize($_GET['error']);
						if (empty($class)) {
							$class = 'errorbox';
						}
					} else {
						$class = 'messagebox';
					}
					if (isset($_GET['msg'])) {
						$msg = sanitize($_GET['msg']);
					} else {
						$msg = '';
					}
					break;

				/** default ****************************************************************** */
				default:
					$func = preg_replace('~\(.*\);*~', '', $action);
					if (in_array($func, $_zp_button_actions)) {
						call_user_func($action);
					}
					break;
			}
		} else {
			$class = 'errorbox';
			$actions = array(
					'clear_cache' => gettext('purge Image cache'),
					'clear_rss_cache' => gettext('purge RSS cache'),
					'reset_hitcounters' => gettext('reset all hitcounters'),
					'clear_search_cache' => gettext('purge search cache')
			);
			if (array_key_exists($action, $actions)) {
				$msg = $actions[$action];
			} else {
				$msg = '<em>' . html_encode($action) . '</em>';
			}
			$msg = sprintf(gettext('You do not have proper rights to %s.'), $msg);
		}
	} else {
		if (isset($_GET['from'])) {
			$class = 'errorbox';
			$msg = sprintf(gettext('You do not have proper rights to access %s.'), html_encode(sanitize($_GET['from'])));
		}
	}

	/*	 * ********************************************************************************* */
	/** End Action Handling ************************************************************ */
	/*	 * ********************************************************************************* */
}
$from = NULL;
if (zp_loggedin() && !empty($zenphoto_tabs)) {
	if (!$_zp_current_admin_obj->getID() || empty($msg) && !zp_loggedin(OVERVIEW_RIGHTS)) {
		// admin access without overview rights, redirect to first tab
		$tab = array_shift($zenphoto_tabs);
		$link = $tab['link'];
		header('location:' . $link);
		exitZP();
	}
} else {
	if (isset($_GET['from'])) {
		$from = sanitize($_GET['from']);
		$from = urldecode($from);
	} else {
		$from = urldecode(currentRelativeURL());
	}
}

// Print our header
printAdminHeader('overview');
?>
<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.masonry.min.js"></script>
<script type="text/javascript">
	// <!-- <![CDATA[
	$(function () {
		$('#overviewboxes').masonry({
			// options
			itemSelector: '.overview-section',
			columnWidth: 560
		});
	});
	// ]]> -->
</script>
<?php
echo "\n</head>";

if (!zp_loggedin()) {
	// If they are not logged in, display the login form and exit
	?>
	<body style="background-image: none">
		<?php $_zp_authority->printLoginForm($from); ?>
	</body>
	<?php
	echo "\n</html>";
	exitZP();
}
$buttonlist = array();
?>
<body>
	<?php
	/* Admin-only content safe from here on. */
	printLogoAndLinks();
	?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php
			/*			 * * HOME ************************************************************************** */
			/*			 * ********************************************************************************* */
			$setupUnprotected = printSetupWarning();
			if (zp_loggedin(ADMIN_RIGHTS)) {
				if (class_exists('Milo\Github\Api') && zpFunctions::hasPrimaryScripts()) {
					/*
					 * Update check Copyright 2017 by Stephen L Billard for use in https://github.com/ZenPhoto20/ZenPhoto20
					 */
					if (getOption('getUpdates_lastCheck') + 8640 < time()) {

						$api = new Github\Api;
						$fullRepoResponse = $api->get('/repos/:owner/:repo/releases/latest', array('owner' => 'ZenPhoto20', 'repo' => 'ZenPhoto20'));
						$fullRepoData = $api->decode($fullRepoResponse);
						$assets = $fullRepoData->assets;
						if (!empty($assets)) {
							$item = array_pop($assets);
							setOption('getUpdates_latest', $item->browser_download_url);
						}

						setOption('getUpdates_lastCheck', time());
					}
					$newestVersionURI = getOption('getUpdates_latest');
					$newestVersion = str_replace('setup-', '', stripSuffix(basename($newestVersionURI)));

					$zenphoto_version = explode('-', ZENPHOTO_VERSION);
					$zenphoto_version = array_shift($zenphoto_version);

					if (version_compare($newestVersion, $zenphoto_version, '>')) {
						if (!isset($_SESSION['new_version_available'])) {
							$_SESSION['new_version_available'] = $newestVersion;
							?>
							<div class="notebox">
								<h2><?php echo gettext('There is a new version of ZenPhoto20 available.'); ?></h2>
								<?php
								printf(gettext('ZenPhoto20 version %s can be downloaded by the utility button.'), $newestVersion);
								?>
							</div>
							<?php
						}
						$buttonlist[] = array(
								'category' => gettext('Admin'),
								'enable' => 2,
								'button_text' => 'ZenPhoto20 ' . $newestVersion,
								'formname' => 'getUpdates_button',
								'action' => $newestVersionURI,
								'icon' => ARROW_DOWN_GREEN,
								'title' => sprintf(gettext('Download ZenPhoto20 version %s.'), $newestVersion),
								'alt' => '',
								'hidden' => '',
								'rights' => ADMIN_RIGHTS
						);
					}
				}
			}
			zp_apply_filter('admin_note', 'overview', '');

			if (!empty($msg)) {
				?>
				<div class="<?php echo html_encode($class); ?> fade-message">
					<h2><?php echo html_encode($msg); ?></h2>
				</div>
				<?php
			}


			$curdir = getcwd();
			chdir(SERVERPATH . "/" . ZENFOLDER . '/' . UTILITIES_FOLDER . '/');
			$filelist = safe_glob('*' . 'php');
			natcasesort($filelist);
			foreach ($filelist as $utility) {
				$utilityStream = file_get_contents($utility);
				$s = strpos($utilityStream, '$buttonlist');
				if ($s !== false) {
					$e = strpos($utilityStream, ';', $s);
					if ($e) {
						$str = substr($utilityStream, $s, $e - $s) . ';';
						eval($str);
					}
				}
			}
			$buttonlist = zp_apply_filter('admin_utilities_buttons', $buttonlist);

			foreach ($buttonlist as $key => $button) {
				if (zp_loggedin($button['rights'])) {
					if (!array_key_exists('category', $button)) {
						$buttonlist[$key]['category'] = gettext('Misc');
					}
				} else {
					unset($buttonlist[$key]);
				}
			}
			if (zp_loggedin(ADMIN_RIGHTS)) {
				//	button to restore setup files if needed
				switch ($setupUnprotected) {
					case 2:
						$buttonlist[] = array(
								'category' => gettext('Admin'),
								'enable' => true,
								'button_text' => gettext('Run setup'),
								'formname' => 'run_setup',
								'action' => FULLWEBPATH . '/' . ZENFOLDER . '/setup.php',
								'icon' => ZP_GOLD,
								'alt' => '',
								'title' => gettext('Run the setup script.'),
								'hidden' => '',
								'rights' => ADMIN_RIGHTS
						);
						if (zpFunctions::hasPrimaryScripts()) {
							$buttonlist[] = array(
									'XSRFTag' => 'protect_setup',
									'category' => gettext('Admin'),
									'enable' => 3,
									'button_text' => gettext('Setup » protect scripts'),
									'formname' => 'restore_setup',
									'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=protect_setup',
									'icon' => KEY_RED,
									'alt' => '',
									'title' => gettext('Protects setup files so setup cannot be run.'),
									'hidden' => '<input type="hidden" name="action" value="protect_setup" />',
									'rights' => ADMIN_RIGHTS
							);
						}
						break;
					case 1:
						$buttonlist[] = array(
								'XSRFTag' => 'restore_setup',
								'category' => gettext('Admin'),
								'enable' => true,
								'button_text' => gettext('Setup » restore scripts'),
								'formname' => 'restore_setup',
								'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=restore_setup',
								'icon' => LOCK_OPEN,
								'alt' => '',
								'title' => gettext('Restores setup files so setup can be run.'),
								'hidden' => '<input type="hidden" name="action" value="restore_setup" />',
								'rights' => ADMIN_RIGHTS
						);
						break;
					default:
				}
			}

			$buttonlist = sortMultiArray($buttonlist, array('category', 'button_text'), false, true, true);

			if (zp_loggedin(OVERVIEW_RIGHTS)) {
				?>
				<div id="overviewboxes">

					<?php
					if (zp_loggedin(ADMIN_RIGHTS)) {
						?>
						<div class="box overview-section overview-install-info">
							<h2 class="h2_bordered"><?php echo gettext("Installation information"); ?></h2>
							<ul>
								<?php
								if (TEST_RELEASE) {
									$official = gettext('<em>Debug build</em>');
								} else {
									$official = gettext('Official build');
								}
								if (zpFunctions::hasPrimaryScripts()) {
									$source = '';
								} else {
									$clone = clonedFrom();
									$official .= ' <em>' . gettext('clone') . '<em>';
									$base = substr(SERVERPATH, 0, -strlen(WEBPATH));
									if (strpos($base, $clone) == 0) {
										$base = substr($clone, strlen($base));
										$link = $base . '/' . ZENFOLDER . '/admin.php';
										$source = '<a href="' . $link . '">' . $clone . '</a>';
									} else {
										$source = $clone;
									}
									$source = '<br />&nbsp;&nbsp;&nbsp;' . sprintf(gettext('source: %s'), $source);
								}

								$graphics_lib = zp_graphicsLibInfo();
								?>
								<li>
									<?php
									if (file_exists(SERVERPATH . '/docs/release notes.htm')) {
										?>
										<script type="text/javascript">
											<!--
											$(document).ready(function () {
												$(".doc").colorbox({
													close: '<?php echo gettext("close"); ?>',
													maxHeight: "98%",
													innerWidth: '560px'
												});
											});
											//-->
										</script>
										<?php
										$notes = ' <a href="' . WEBPATH . '/docs/release%20notes.htm" class="doc" title="' . gettext('release notes') . '">' . gettext('notes') . '</a>';
									} else {
										$notes = '';
									}
									printf(gettext('ZenPhoto20 version <strong>%1$s (%2$s)</strong>'), ZENPHOTO_VERSION, $official);
									echo $notes . $source;
									?>
								</li>
								<li>
									<?php
									if (ZENPHOTO_LOCALE) {
										printf(gettext('Current locale setting: <strong>%1$s</strong>'), ZENPHOTO_LOCALE);
									} else {
										echo gettext('<strong>Locale setting has failed</strong>');
									}
									?>
								</li>
								<li>
									<?php echo gettext('Server path:') . ' <strong>' . SERVERPATH . '</strong>' ?>
								</li>
								<li>
									<?php echo gettext('WEB path:') . ' <strong>' . WEBPATH . '</strong>' ?>
								</li>
								<li>
									<?php echo gettext('PHP Session path:') . ' <strong>' . session_save_path() . '</strong>' ?>
								</li>
								<li>
									<?php
									$themes = $_zp_gallery->getThemes();
									$currenttheme = $_zp_gallery->getCurrentTheme();
									if (array_key_exists($currenttheme, $themes) && isset($themes[$currenttheme]['name'])) {
										$currenttheme = $themes[$currenttheme]['name'];
									}
									printf(gettext('Current gallery theme: <strong>%1$s</strong>'), $currenttheme);
									?>
								</li>
								<li><?php printf(gettext('PHP version: <strong>%1$s</strong>'), phpversion()); ?></li>
								<?php
								if (TEST_RELEASE) {
									?>
									<li>
										<?php
										$erToTxt = array(E_ERROR => 'E_ERROR',
												E_WARNING => 'E_WARNING',
												E_PARSE => 'E_PARSE',
												E_NOTICE => 'E_NOTICE',
												E_CORE_ERROR => 'E_CORE_ERROR',
												E_CORE_WARNING => 'E_CORE_WARNING',
												E_COMPILE_ERROR => 'E_COMPILE_ERROR',
												E_COMPILE_WARNING => 'E_COMPILE_WARNING',
												E_USER_ERROR => 'E_USER_ERROR',
												E_USER_NOTICE => 'E_USER_NOTICE',
												E_USER_WARNING => 'E_USER_WARNING',
												E_STRICT => 'E_STRICT'
										);
										if (version_compare(PHP_VERSION, '5.2.0') == 1) {
											$erToTxt[E_RECOVERABLE_ERROR] = 'E_RECOVERABLE_ERROR';
										}
										if (version_compare(PHP_VERSION, '5.3.0') == 1) {
											$erToTxt[E_DEPRECATED] = 'E_DEPRECATED';
											$erToTxt[E_USER_DEPRECATED] = 'E_USER_DEPRECATED';
										}
										$reporting = error_reporting();
										$text = array();

										if ((($reporting | E_NOTICE | E_STRICT) & E_ALL) == E_ALL) {
											$t = 'E_ALL';
											$reporting = $reporting ^ E_ALL;
											if ($reporting & E_STRICT) {
												$t .= ' ^ E_STRICT';
												$reporting = $reporting ^ E_STRICT;
											}
											if ($reporting & E_NOTICE) {
												$t .= ' ^ E_NOTICE';
												$reporting = $reporting ^ E_NOTICE;
											}
											$text[] = $t;
										} else {
											if (($reporting & E_ALL) == E_ALL) {
												$text[] = 'E_ALL';
												$reporting = $reporting ^ E_ALL;
											}
										}

										foreach ($erToTxt as $er => $name) {
											if ($reporting & $er) {
												$text[] = $name;
											}
										}
										printf(gettext('PHP Error reporting: <strong>%s</strong>'), implode(' | ', $text));
										?>
									</li>
									<?php
									if (@ini_get('display_errors')) {
										?>
										<li><a title="<?php echo gettext('PHP error messages may be displayed on WEB pages. This may disclose site sensitive information.'); ?>"><?php echo gettext('<em>display_errors</em> is <strong>On</strong>') ?></a></li>
										<?php
									} else {
										?>
										<li><?php echo gettext('<em>display_errors</em> is <strong>Off</strong>') ?></li>
										<?php
									}
								}
								?>
								<li>
									<?php printf(gettext("Graphics support: <strong>%s</strong>"), $graphics_lib['Library_desc']); ?>
									<br />&nbsp;&nbsp;&nbsp;
									<?php
									unset($graphics_lib['Library']);
									unset($graphics_lib['Library_desc']);
									foreach ($graphics_lib as $key => $type) {
										if (!$type) {
											unset($graphics_lib[$key]);
										}
									}
									printf(gettext('supporting: %s'), '<em>' . strtolower(implode(', ', array_keys($graphics_lib))) . '</em>');
									?>
								</li>
								<li><?php printf(gettext('PHP memory limit: <strong>%1$s</strong> (Note: Your server might allocate less!)'), INI_GET('memory_limit')); ?></li>
								<li>
									<?php
									$dbsoftware = db_software();
									printf(gettext('%1$s version: <strong>%2$s</strong>'), $dbsoftware['application'], $dbsoftware['version']);
									?>

								</li>
								<li><?php printf(gettext('Database name: <strong>%1$s</strong>'), db_name()); ?></li>
								<li>
									<?php
									$prefix = trim(prefix(), '`');
									if (!empty($prefix)) {
										echo sprintf(gettext('Table prefix: <strong>%1$s</strong>'), $prefix);
									}
									?>
								</li>
								<li>
									<?php
									if (isset($_zp_spamFilter)) {
										$filter = $_zp_spamFilter->displayName();
									} else {
										$filter = gettext('No spam filter configured');
									}
									printf(gettext('Spam filter: <strong>%s</strong>'), $filter)
									?>
								</li>
								<?php
								if ($_zp_captcha) {
									?>
									<li><?php printf(gettext('CAPTCHA generator: <strong>%s</strong>'), ($_zp_captcha->name) ? $_zp_captcha->name : gettext('none')) ?></li>
									<?php
								}
								zp_apply_filter('installation_information');
								if (!zp_has_filter('sendmail')) {
									?>
									<li style="color:RED"><?php echo gettext('There is no mail handler configured!'); ?></li>
									<?php
								}
								?>
							</ul>

							<?php
							require_once(SERVERPATH . '/' . ZENFOLDER . '/template-filters.php');
							$plugins = array_keys(getEnabledPlugins());
							$filters = $_zp_filters;
							$c = count($plugins);
							?>
							<h3><a onclick="$('#plugins_hide').toggle();
									$('#plugins_show').toggle();" ><?php printf(ngettext("%u active plugin:", "%u active plugins:", $c), $c); ?></a></h3>
							<div id="plugins_hide" style="display:none">
								<ul class="plugins">
									<?php
									if ($c > 0) {
										natcasesort($plugins);
										foreach ($plugins as $extension) {
											$pluginStream = file_get_contents(getPlugin($extension . '.php'));
											$plugin_version = '';
											if ($str = isolate('$plugin_version', $pluginStream)) {
												@eval($str);
											}
											if ($plugin_version) {
												$version = ' v' . $plugin_version;
											} else {
												$version = '';
											}
											$plugin_is_filter = 1;
											if ($str = isolate('$plugin_is_filter', $pluginStream)) {
												@eval($str);
											}
											echo "<li>" . $extension . $version . "</li>";
											preg_match_all('|zp_register_filter\s*\((.+?)\)\s*?;|', $pluginStream, $matches);
											foreach ($matches[1] as $paramsstr) {
												$params = explode(',', $paramsstr);
												if (array_key_exists(2, $params)) {
													$priority = (int) $params[2];
												} else {
													$priority = $plugin_is_filter & PLUGIN_PRIORITY;
												}
												$filter = unQuote(trim($params[0]));
												$function = unQuote(trim($params[1]));
												$filters[$filter][$priority][$function] = array('function' => $function, 'script' => $extension . '.php');
											}
										}
									} else {
										echo '<li>' . gettext('<em>none</em>') . '</li>';
									}
									?>
								</ul>
							</div><!-- plugins_hide -->
							<div id="plugins_show">
								<br />
							</div><!-- plugins_show -->
							<?php
							$c = count($filters);
							?>
							<h3><a onclick="$('#filters_hide').toggle();
									$('#filters_show').toggle();" ><?php printf(ngettext("%u active filter:", "%u active filters:", $c), $c); ?></a></h3>
							<div id="filters_hide" style="display:none">
								<ul class="plugins">
									<?php
									if ($c > 0) {
										ksort($filters, SORT_LOCALE_STRING);
										foreach ($filters as $filter => $array_of_priority) {
											krsort($array_of_priority);
											?>
											<li>
												<em><?php echo $filter; ?></em>
												<ul class="filters">
													<?php
													foreach ($array_of_priority as $priority => $array_of_filters) {
														foreach ($array_of_filters as $data) {
															?>
															<li><em><?php echo $priority; ?></em>: <?php echo $data['script'] ?> =&gt; <?php echo $data['function'] ?></li>
															<?php
														}
													}
													?>
												</ul>
											</li>
											<?php
										}
									} else {
										?>
										<li><?php echo gettext('<em>none</em>'); ?></li>
										<?php
									}
									?>
								</ul>
							</div><!-- filters_hide -->
							<div id="filters_show">
								<br />
							</div><!-- filters_show -->

						</div><!-- overview-info -->
						<?php
					}
					if (!empty($buttonlist)) {
						?>
						<div class="box overview-section overview_utilities">
							<h2 class="h2_bordered"><?php echo gettext("Utility functions"); ?></h2>
							<?php
							$category = '';
							foreach ($buttonlist as $button) {
								$button_category = $button['category'];
								$button_icon = $button['icon'];

								$color = $disable = '';
								switch ((int) $button['enable']) {
									case 0:
										$disable = ' disabled="disabled"';
										break;
									case 2:
										$color = ' style="color:orange"';
										break;
									case 3:
										$color = ' style="color:red"';
										break;
								}
								if ($category != $button_category) {
									if ($category) {
										?>
										</fieldset>
										<?php
									}
									$category = $button_category;
									?>
									<fieldset class="overview_utility_buttons_field"><legend><?php echo $category; ?></legend>
										<?php
									}
									?>
									<form name="<?php echo $button['formname']; ?>"	id="<?php echo $button['formname']; ?>" action="<?php echo $button['action']; ?>" class="overview_utility_buttons">
										<?php
										if (isset($button['XSRFTag']) && $button['XSRFTag'])
											XSRFToken($button['XSRFTag']);
										echo $button['hidden'];
										if (isset($button['onclick'])) {
											$type = 'type="button" onclick="' . $button['onclick'] . '"';
										} else {
											$type = 'type="submit"';
										}
										?>
										<div class="buttons tooltip" title="<?php echo html_encode($button['title']); ?>">
											<button class="fixedwidth<?php if ($disable) echo ' disabled_button'; ?>" <?php echo $type . $disable; ?>>
												<?php
												if (!empty($button_icon)) {
													if (strpos($button_icon, 'images/') === 0) {
														// old style icon image
														?>
														<img src="<?php echo $button_icon; ?>" alt="<?php echo html_encode($button['alt']); ?>" />
														<?php
													} else {
														echo $button_icon . ' ';
													}
												}
												echo '<span' . $color . '>' . html_encode($button['button_text']) . '</span>';
												?>
											</button>
										</div><!--buttons -->
									</form>
									<?php
								}
								if ($category) {
									?>
								</fieldset>
								<?php
							}
							?>
						</div><!-- overview-section -->
						<?php
					}
					?>
					<div class="box overview-section overiew-gallery-stats">
						<h2 class="h2_bordered"><?php echo gettext("Gallery Stats"); ?></h2>
						<ul>
							<li>
								<?php
								$t = $_zp_gallery->getNumImages();
								$c = $t - $_zp_gallery->getNumImages(true);
								if ($c > 0) {
									printf(ngettext('<strong>%1$u</strong> Image (%2$u un-published)', '<strong>%1$u</strong> Images (%2$u un-published)', $t), $t, $c);
								} else {
									printf(ngettext('<strong>%u</strong> Image', '<strong>%u</strong> Images', $t), $t);
								}
								?>
							</li>
							<li>
								<?php
								$t = $_zp_gallery->getNumAlbums(true);
								$c = $t - $_zp_gallery->getNumAlbums(true, true);
								if ($c > 0) {
									printf(ngettext('<strong>%1$u</strong> Album (%2$u un-published)', '<strong>%1$u</strong> Albums (%2$u un-published)', $t), $t, $c);
								} else {
									printf(ngettext('<strong>%u</strong> Album', '<strong>%u</strong> Albums', $t), $t);
								}
								?>
							</li>
							<li>
								<?php
								$t = $_zp_gallery->getNumComments(true);
								$c = $t - $_zp_gallery->getNumComments(false);
								if ($c > 0) {
									printf(ngettext('<strong>%1$u</strong> Comment (%2$u in moderation)', '<strong>%1$u</strong> Comments (%2$u in moderation)', $t), $t, $c);
								} else {
									printf(ngettext('<strong>%u</strong> Comment', '<strong>%u</strong> Comments', $t), $t);
								}
								?>
							</li>
							<?php
							if (extensionEnabled('zenpage')) {
								?>
								<li>
									<?php printPagesStatistic(); ?>
								</li>
								<li>
									<?php printNewsStatistic(); ?>
								</li>
								<li>
									<?php printCategoriesStatistic(); ?>
								</li>
								<?php
							}
							?>
						</ul>
					</div><!-- overview-gallerystats -->

					<?php
					zp_apply_filter('admin_overview');
					?>
				</div><!-- boxouter -->
			</div><!-- content -->
			<?php
		} else {
			?>
			<div class="errorbox">
				<?php echo gettext('Your user rights do not allow access to administrative functions.'); ?>
			</div>
			<?php
		}
		printAdminFooter();
		/* No admin-only content allowed after point! */
		?>
	</div>
	<!-- main -->
</body>
<?php
// to fool the validator
echo "\n</html>";
?>
