<?php
/**
 *
 * Displays the Installation information
 *
 * @author Stephen Billard (sbillard)
 * @package plugins/search_statistics
 */
define('OFFSET_PATH', 4);
require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());


printAdminHeader('overview', 'Installation');

echo '</head>';
?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'albums', ''); ?>
			<h1><?php echo gettext("Installation information"); ?></h1>

			<div class="overviewboxes">
				<?php
				if (zp_loggedin(ADMIN_RIGHTS)) {
					?>
					<div id="overview_left" class="box overview-section overview-install-info">

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
								printf(gettext('ZenPhotoGraphics version <strong>%1$s (%2$s)</strong>'), ZENPHOTO_VERSION, $official);
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
								<?php printf(gettext('PHP openSSL is %s'), function_exists('openssl_encrypt') ? gettext('enabled') : gettext('disabled')); ?>
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
					</div>
					<div class="box overview-section overview-install-info">
						<div class="overview-list-h3">
							<h3 class="overview-list-h3">
								<?php printf(ngettext("%u active plugin:", "%u active plugins:", $c), $c); ?>
							</h3>
						</div>
						<div class="overview_list">
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
						</div><!-- plugins -->
					</div>
					<div class="box overview-section overview-install-info">
						<?php
						$c = count($filters);
						?>
						<div class="overview-list-h3">
							<h3>
								<?php printf(ngettext("%u active filter:", "%u active filters:", $c), $c); ?>
							</h3>
						</div>
						<div class="overview_list">
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
						</div><!-- filters -->

					</div><!-- overview-info -->
					<br class="clearall">
					<?php
				}
				?>

			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>
</body>
<script type="text/javascript">
										var height = Math.floor(($('#overview_left').height() - $('.overview-list-h3').height() * 2) / 2 - 8);
										$('.overview_list').height(height);
</script>

<?php
echo "</html>";
?>
