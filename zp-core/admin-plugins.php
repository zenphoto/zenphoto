<?php
/**
 * provides the Plugins tab of admin
 *
 * @author Stephen Billard (sbillard)
 *
 * @package admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

define('PLUGINS_PER_PAGE', max(1, getOption('plugins_per_page')));
if (isset($_GET['subpage'])) {
	$subpage = sanitize_numeric($_GET['subpage']);
} else {
	if (isset($_POST['subpage'])) {
		$subpage = sanitize_numeric($_POST['subpage']);
	} else {
		$subpage = 0;
	}
}

$_GET['page'] = 'plugins';
list($tabs, $subtab, $pluginlist, $paths, $member, $classXlate) = getPluginTabs();

/* handle posts */
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'saveplugins') {
		if (isset($_POST['checkForPostTruncation'])) {
			XSRFdefender('saveplugins');
			$plugins = array();
			foreach ($_POST as $plugin => $value) {
				preg_match('/^present_zp_plugin_(.*)$/xis', $plugin, $matches);
				if ($matches) {
					$is = (int) isset($_POST['zp_plugin_' . $matches[1]]);
					if ($is) {
						$nv = sanitize_numeric($_POST['zp_plugin_' . $matches[1]]);
					} else {
						$nv = NULL;
					}
					$was = (int) ($value && true);
					if ($was == $is) {
						$action = 1;
					} else if ($was) {
						$action = 2;
					} else {
						$action = 3;
					}
					$plugins[$matches[1]] = array('action' => $action, 'is' => $nv);
				}
			}
			foreach ($plugins as $_plugin_extension => $data) {
				$f = str_replace('-', '_', $_plugin_extension) . '_enable';
				$p = getPlugin($_plugin_extension . '.php');
				switch ($data['action']) {
					case 1:
						//no change
						break;
					case 2:
						//going from enabled to disabled
						require_once($p);
						if (function_exists($f)) {
							$f(false);
						}
						setOption('zp_plugin_' . $_plugin_extension, 0);
						break;
					case 3:
						//going from disabled to enabled
						setOption('zp_plugin_' . $_plugin_extension, $data['is']);
						$option_interface = NULL;
						require_once($p);

						if ($option_interface && is_string($option_interface)) {
							$if = new $option_interface; //	prime the default options
						}

						if (function_exists($f)) {
							$f(true);
						}
						break;
				}
			}
			$notify = '&saved';
		} else {
			$notify = '&post_error';
		}

		header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-plugins.php?page=plugins&tab=" . html_encode($subtab) . "&subpage=" . html_encode($subpage) . $notify);
		exitZP();
	}
}
$saved = isset($_GET['saved']);
printAdminHeader('plugins');

natcasesort($pluginlist);
$rangeset = getPageSelector($pluginlist, PLUGINS_PER_PAGE);
$filelist = array_slice($pluginlist, $subpage * PLUGINS_PER_PAGE, PLUGINS_PER_PAGE);
?>
<script type="text/javascript">
	<!--
	function toggleDetails(plugin) {
		toggle(plugin + '_show');
		toggle(plugin + '_hide');
	}

	var pluginsToPage = ['<?php echo implode("','", $pluginlist); ?>'];
	function gotoPlugin(plugin) {
		i = Math.floor(jQuery.inArray(plugin, pluginsToPage) / <?php echo PLUGINS_PER_PAGE; ?>);
		window.location = '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-plugins.php?page=plugins&tab=<?php echo html_encode($subtab); ?>&subpage=' + i + '&show=' + plugin + '#' + plugin;
	}
-->
</script>
<?php
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';

printTabs();
echo "\n" . '<div id="content">';

/* Page code */

if ($saved) {
	echo '<div class="messagebox fade-message">';
	echo "<h2>" . gettext("Applied") . "</h2>";
	echo '</div>';
}
?>
<h1>
	<?php
	$subtab = getCurrentTab();
	printf(gettext('%1$s plugins'), ucfirst(@$classXlate[$subtab]));
	?>
</h1>

<div class="tabbox">
	<?php
	zp_apply_filter('admin_note', 'plugins', '');
	if (isset($_GET['post_error'])) {
		echo '<div class="errorbox">';
		echo "<h2>" . gettext('Error') . "</h2>";
		echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
		echo '</div>';
	}
	?>
	<p>
		<?php
		echo gettext("Plugins provide optional functionality.") . ' ';
		echo gettext("They may be provided as part of the distribution or as offerings from third parties.") . ' ';
		echo sprintf(gettext("Third party plugins are placed in the <code>%s</code> folder and are automatically discovered."), USER_PLUGIN_FOLDER) . ' ';
		echo gettext("If the plugin checkbox is checked, the plugin will be loaded and its functions made available. If the checkbox is not checked the plugin is disabled and occupies no resources.");
		?>
		<a href="http://www.zenphoto.org/news/category/extensions" alt="Extensions section"> <?php echo gettext('Find more plugins'); ?></a>
	</p>
	<p class='notebox'><?php echo gettext("<strong>Note:</strong> Support for a particular plugin may be theme dependent! You may need to add the plugin theme functions if the theme does not currently provide support."); ?>
	</p>
	<form class="dirtylistening" onReset="setClean('form_plugins');" id="form_plugins" action="?action=saveplugins&amp;page=plugins&amp;tab=<?php echo html_encode($subtab); ?>" method="post" autocomplete="off" >
		<?php XSRFToken('saveplugins'); ?>
		<input type="hidden" name="saveplugins" value="yes" />
		<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
		<p class="buttons">
			<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
			<button type="reset" value="<?php echo gettext('Reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
		</p><br class="clearall" /><br /><br />
		<table class="bordered options">
			<tr>
				<th id="imagenav" colspan="3">
					<?php printPageSelector($subpage, $rangeset, 'admin-plugins.php', array('page' => 'plugins', 'tab' => $subtab)); ?>
				</th>
			</tr>
			<tr>
				<th colspan="2"><span class="displayleft"><?php echo gettext("Available Plugins"); ?></span></th>
				<th colspan="1">
					<span class="displayleft"><?php echo gettext("Description"); ?></span>
				</th>

			</tr>
			<?php
			foreach ($filelist as $extension) {
				$opt = 'zp_plugin_' . $extension;
				$pluginStream = file_get_contents($paths[$extension]);
				$parserr = 0;
				$plugin_URL = FULLWEBPATH . '/' . ZENFOLDER . '/pluginDoc.php?extension=' . $extension;
				if ($third_party_plugin = strpos($paths[$extension], ZENFOLDER) === false) {
					if ($str = isolate('@category', $pluginStream)) {
						preg_match('|@category\s+(.*)\s|', $str, $matches);
						if (isset($matches[1]) && $matches[1] == 'package') {
							$third_party_plugin = false;
							$ico = 'images/zp.png';
							$whose = gettext('Supplemental plugin');
							$plugin_URL .= '&amp;type=supplemental';
						}
					}
					if ($third_party_plugin) {
						$whose = gettext('Third party plugin');
						$plugin_URL .= '&amp;type=thirdparty';
					}
				} else {
					$whose = gettext('Official plugin');
					$ico = 'images/zp_gold.png';
				}
				if ($str = isolate('$plugin_description', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 1;
						$plugin_description = gettext('<strong>Error parsing <em>plugin_description</em> string!</strong>.');
					}
				} else {
					$plugin_description = '';
				}
				if ($str = isolate('$plugin_notice', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 1;
						$plugin_notice = gettext('<strong>Error parsing <em>plugin_notice</em> string!</strong>.');
					}
				} else {
					$plugin_notice = '';
				}
				if ($str = isolate('$plugin_author', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 2;
						$plugin_author = gettext('<strong>Error parsing <em>plugin_author</em> string!</strong>.');
					}
				} else {
					$plugin_author = '';
				}
				if ($str = isolate('$plugin_version', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 4;
						$plugin_version = ' ' . gettext('<strong>Error parsing <em>plugin_version</em> string!</strong>.');
					}
				} else {
					$plugin_version = '';
				}
				if ($str = isolate('$plugin_disable', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 8;
						$plugin_URL = gettext('<strong>Error parsing <em>plugin_disable</em> string!</strong>.');
					} else {
						if ($plugin_disable) {
							setOption($opt, 0);
						}
					}
				} else {
					$plugin_disable = false;
				}
				$currentsetting = getOption($opt);
				$plugin_is_filter = 1 | THEME_PLUGIN;
				if ($str = isolate('$plugin_is_filter', $pluginStream)) {
					eval($str);
				}
				$optionlink = NULL;
				if ($str = isolate('$option_interface', $pluginStream)) {
					if (preg_match('/\s*=\s*new\s(.*)\(/i', $str)) {
						$plugin_notice .= '<br /><br />' . gettext('<strong>Note:</strong> Instantiating the option interface within the plugin may cause performance issues. You should instead set <code>$option_interface</code> to the name of the class as a string.');
					} else {
						$option_interface = NULL;
						eval($str);
						if ($option_interface) {
							$optionlink = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&amp;tab=plugin&amp;single=' . $extension;
						}
					}
				}
				$selected_style = '';
				if ($currentsetting > THEME_PLUGIN) {
					$selected_style = ' class="currentselection"';
				}
				if (isset($_GET['show']) && $_GET['show'] == $extension) {
					$selected_style = ' class="highlightselection"';
				}
				?>
				<tr<?php echo $selected_style; ?>>
					<td width="30%">
						<input type="hidden" name="present_<?php echo $opt; ?>" id="present_<?php echo $opt; ?>" value="<?php echo $currentsetting; ?>" />
						<label id="<?php echo $extension; ?>">
							<?php
							if ($third_party_plugin) {
								$path = stripSuffix($paths[$extension]) . '/logo.png';
								if (file_exists($path)) {
									$ico = str_replace(SERVERPATH, WEBPATH, $path);
								} else {
									$ico = 'images/place_holder_icon.png';
								}
							}
							?>
							<img class="zp_logoicon" src="<?php echo $ico; ?>" alt="<?php echo gettext('logo'); ?>" title="<?php echo $whose; ?>" />
							<?php
							if ($plugin_is_filter & CLASS_PLUGIN) {
								$iconA = '<img class="zp_logoicon" width="8px" src="images/place_holder_icon.png" /><a title="' . gettext('class plugin') . '"><img class="zp_logoicon" src="images/folder_picture.png" /></a><img class="zp_logoicon" width="8px" src="images/place_holder_icon.png" />';
								$iconT = '';
							} else {
								if ($plugin_is_filter & ADMIN_PLUGIN) {
									$iconA = '<a title="' . gettext('admin plugin') . '"><img class="zp_logoicon" src="images/folder.png" /></a>';
								} else {
									$iconA = '<img class="zp_logoicon" src="images/place_holder_icon.png" />';
								}
								if ($plugin_is_filter & FEATURE_PLUGIN) {
									$iconT = '<a title="' . gettext('feature plugin') . '"><img class="zp_logoicon" src="images/pictures.png" /></a>';
								} else if ($plugin_is_filter & THEME_PLUGIN) {
									$iconT = '<a title="' . gettext('theme plugin') . '"><img class="zp_logoicon" src="images/pictures_dn.png" /></a>';
								} else {
									$iconT = '<img class="zp_logoicon" src="images/place_holder_icon.png" />';
								}
							}
							echo $iconT;
							echo $iconA;

							$attributes = '';
							if ($parserr) {
								$optionlink = false;
								$attributes .= ' disabled="disabled"';
							} else {
								if ($currentsetting > THEME_PLUGIN) {
									$attributes .= ' checked="checked"';
								}
							}
							if ($plugin_disable) {
								?>
								<span class="icons" id="<?php echo $extension; ?>_checkbox">
									<a onclick="toggle('showdisable_<?php echo $extension; ?>');" title="<?php echo gettext('This plugin is disabled. Click for details.'); ?>" >
										<img src="images/action.png" alt="" class="zp_logoicon" />
									</a>
									<input type="hidden" name="<?php echo $opt; ?>" id="<?php echo $opt; ?>" value="0" />
								</span>
								<?php
							} else {
								?>
								<input type="checkbox" name="<?php echo $opt; ?>" id="<?php echo $opt; ?>" value="<?php echo $plugin_is_filter; ?>"<?php echo $attributes; ?> />
								<?php
							}
							echo $extension;
							if (!empty($plugin_version)) {
								echo ' v' . $plugin_version;
							}
							?>
						</label>
						<?php
						if ($subtab == 'all') {
							$tab = $member[$extension];
							echo '<span class="displayrightsmall"><a href="' . html_encode($tabs[$tab]) . '"><em>' . $tab . '</em></a></span>';
						}
						?>
					</td>
					<td width="60">
						<span class="icons"><a onclick="$.colorbox({
										close: '<?php echo gettext("close"); ?>',
										maxHeight: '80%',
										maxWidth: '80%',
										innerWidth: '560px',
										href: '<?php echo $plugin_URL; ?>'
									});"><img class="icon-position-top3" src="images/info.png" title="<?php printf(gettext('More information on %s'), $extension); ?>" alt=""></a></span>
																	 <?php
																	 if ($optionlink) {
																		 ?>
							<span class="icons"><a href="<?php echo $optionlink; ?>" title="<?php printf(gettext("Change %s options"), $extension); ?>"><img class="icon-position-top3" src="images/options.png" alt="" /></a></span>
							<?php
						} else {
							?>
							<span class="icons"><img class="icon-position-top3" src="images/place_holder_icon.png" alt="" /></span>
							<?php
						}
						if ($plugin_notice) {
							?>
							<span class="icons"><a onclick="toggle('show_<?php echo $extension; ?>');" title ="<?php echo gettext('Plugin warnings'); ?>" ><img class="icon-position-top3" src="images/warn.png" alt="" /></a></span>
							<?php
						}
						?>
					</td>
					<td colspan="2">
						<?php
						echo $plugin_description;
						if ($plugin_disable) {
							?>
							<div id="showdisable_<?php echo $extension; ?>" style="display: none" class="warningbox">
								<?php
								if ($plugin_disable) {
									preg_match('/\<a href="#(.*)">/', $plugin_disable, $matches);
									if ($matches) {
										$plugin_disable = str_replace($matches[0], '<a onclick="gotoPlugin(\'' . $matches[1] . '\');">', $plugin_disable);
									}
									echo $plugin_disable;
								}
								?>
							</div>

							<?php
						}
						if ($plugin_notice) {
							?>
							<div id="show_<?php echo $extension; ?>" style="display:none" class="notebox">
								<?php
								if ($plugin_notice) {
									echo $plugin_notice;
								}
								?>
							</div>
							<?php
						}
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="4" id="imagenavb">
					<?php printPageSelector($subpage, $rangeset, 'admin-plugins.php', array('page' => 'plugins', 'tab' => $subtab)); ?>
				</td>
			</tr>
		</table>
		<br />
		<ul class="iconlegend">
			<li><img src="images/zp_gold.png" alt=""><?php echo gettext('Official plugin'); ?></li>
			<li><img src="images/zp.png" alt=""><?php echo gettext('Supplemental plugin'); ?></li>
			<li><img src="images/folder_picture.png" alt=""><?php echo gettext('Class plugin'); ?></li>
			<li><img src="images/folder.png" alt=""><?php echo gettext('Admin plugin'); ?></li>
			<li><img src="images/pictures.png" alt=""><?php echo gettext('Feature plugin'); ?></li>
			<li><img src="images/pictures_dn.png" alt=""><?php echo gettext('Theme plugin'); ?></li>
			<li><img src="images/info.png" alt=""><?php echo gettext('Usage info'); ?></li>
			<li><img src="images/options.png" alt=""><?php echo gettext('Options'); ?></li>
			<li><img src="images/action.png" alt=""><img src="images/warn.png" alt=""><?php echo gettext('Warning note'); ?></li>
		</ul>
		<p class="buttons">
			<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
			<button type="reset" value="<?php echo gettext('Reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
		</p><br /><br />
		<input type="hidden" name="checkForPostTruncation" value="1" />
	</form>
</div>
<?php
echo "\n" . '</div>'; //content
printAdminFooter();
echo "\n" . '</div>'; //main
echo "\n</body>";
echo "\n</html>";
?>



