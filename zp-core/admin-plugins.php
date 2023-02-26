<?php
/**
 * provides the Plugins tab of admin
 * @package zpcore\admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__) . '/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

define('PLUGINS_PER_PAGE', max(1, getOption('plugins_per_page')));
if (isset($_GET['pagenumber'])) {
	$pagenumber = sanitize_numeric($_GET['pagenumber']);
} else {
	if (isset($_POST['pagenumber'])) {
		$pagenumber = sanitize_numeric($_POST['pagenumber']);
	} else {
		$pagenumber = 0;
	}
}

$_GET['page'] = 'plugins';
list($tabs, $subtab, $pluginlist, $paths, $member) = getPluginTabs();

/* handle posts */
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'saveplugins') {
		if (isset($_POST['checkForPostTruncation'])) {
			XSRFdefender('saveplugins');
			$filelist = array();
			foreach ($_POST as $plugin => $value) {
				preg_match('/^present_zp_plugin_(.*)$/xis', $plugin, $matches);
				if ($matches) {
					$filelist[] = $matches[1];
				}
			}
			foreach ($filelist as $extension) {
				$extension = filesystemToInternal($extension);
				$opt = 'zp_plugin_' . $extension;
				if (isset($_POST[$opt])) {
					$value = sanitize_numeric($_POST[$opt]);
					if (!getOption($opt)) {
						$option_interface = NULL;
						require_once(getPlugin($extension . '.php'));
						if ($option_interface && is_string($option_interface)) {
							$if = new $option_interface; //	prime the default options
						}
					}
					setOption($opt, $value);
				} else {
					setOption($opt, 0);
				}
			}
			$notify = '&saved';
		} else {
			$notify = '&post_error';
		}
		redirectURL(FULLWEBPATH . "/" . ZENFOLDER . "/admin-plugins.php?page=plugins&tab=" . html_encode($subtab) . "&pagenumber=" . html_encode($pagenumber) . $notify);
	}
}
$saved = isset($_GET['saved']);
printAdminHeader('plugins');
zp_apply_filter('texteditor_config', 'zenphoto');

sortArray($pluginlist);
$rangeset = getPageSelector($pluginlist, PLUGINS_PER_PAGE);
$filelist = array_slice($pluginlist, $pagenumber * PLUGINS_PER_PAGE, PLUGINS_PER_PAGE);
?>
<script>
	<!--
	function toggleDetails(plugin) {
		toggle(plugin + '_show');
		toggle(plugin + '_hide');
	}

	$(document).ready(function() {
		$(".plugin_doc").colorbox({
			close: '<?php echo gettext("close"); ?>',
			maxHeight: "98%",
			innerWidth: '560px'
		});
	});
	var pluginsToPage = ['<?php echo implode("','", $pluginlist); ?>'];
	function gotoPlugin(plugin) {
		i = Math.floor(jQuery.inArray(plugin, pluginsToPage) / <?php echo PLUGINS_PER_PAGE; ?>);
		window.location = '<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/admin-plugins.php?page=plugins&tab=<?php echo html_encode($subtab); ?>&pagenumber=' + i + '&show=' + plugin + '#' + plugin;
	}
//-->
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
<h1><?php echo gettext('Plugins'); ?></h1>
<?php
$subtab = printSubtabs();
?>
<div class="tabbox">
	<?php
	zp_apply_filter('admin_note', 'users', 'users');
	if (isset($_GET['post_error'])) {
		echo '<div class="errorbox">';
		echo "<h2>" . gettext('Error') . "</h2>";
		echo gettext('The form submission is incomplete. Perhaps the form size exceeds configured server or browser limits.');
		echo '</div>';
	}
	?>
	<p>
		<?php
		echo gettext("Plugins provide optional functionality for Zenphoto.") . ' ';
		echo gettext("They may be provided as part of the Zenphoto distribution or as offerings from third parties.") . ' ';
		echo sprintf(gettext("Third party plugins are placed in the <code>%s</code> folder and are automatically discovered."), USER_PLUGIN_FOLDER) . ' ';
		echo gettext("If the plugin checkbox is checked, the plugin will be loaded and its functions made available. If the checkbox is not checked the plugin is disabled and occupies no resources.");
		?>
		<a href="https://www.zenphoto.org/news/category/extensions" alt="Zenphoto extensions section"> <?php echo gettext('Find more plugins'); ?></a>
	</p>
	<p class='notebox'><?php echo gettext("<strong>Note:</strong> Support for a particular plugin may be theme dependent! You may need to add the plugin theme functions if the theme does not currently provide support."); ?>
	</p>
	<form class="dirty-check" id="form_plugins" action="?action=saveplugins&amp;page=plugins&amp;tab=<?php echo html_encode($subtab); ?>" method="post" autocomplete="off">
		<?php XSRFToken('saveplugins'); ?>
		<input type="hidden" name="saveplugins" value="yes" />
		<input type="hidden" name="pagenumber" value="<?php echo $pagenumber; ?>" />
		<p class="buttons">
			<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
			<button type="reset" value="<?php echo gettext('Reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
		</p><br class="clearall" /><br /><br />
		<table class="bordered options">
			<tr>
				<th id="imagenav" colspan="3">
					<?php printPageSelector($pagenumber, $rangeset, 'admin-plugins.php', array('page' => 'plugins', 'tab' => $subtab)); ?>
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
				$third_party_plugin = strpos($paths[$extension], ZENFOLDER) === false;
				$pluginStream = file_get_contents($paths[$extension]);
				$parserr = 0;
				$plugin_name = '';
				if ($str = isolate('$plugin_name', $pluginStream)) {
					if (false === eval($str)) {
						$plugin_name = ''; // silent fallback on failure
					}
				} 
				if(empty($plugin_name)) {
					$plugin_name = $extension;
				}
				$plugin_description = '';
				if ($str = isolate('$plugin_description', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 1;
						$plugin_description = gettext('<strong>Error parsing <em>plugin_description</em> string!</strong>');
					} else {
						$plugin_description  = processExtensionVariable($plugin_description);
					}
				} 
				$plugin_deprecated = '';
				if ($str = isolate('$plugin_deprecated', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 2;
						$plugin_deprecated = gettext('<strong>Error parsing <em>plugin_deprecated</em> string!</strong>');
					} else {
						$plugin_deprecated  = processExtensionVariable($plugin_deprecated);
						if (is_bool($plugin_deprecated) || empty($plugin_deprecated)) {
							$plugin_deprecated = gettext('This plugin will be removed in future versions.');
						}
					}
				} 
				$plugin_notice = '';
				if ($str = isolate('$plugin_notice', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 3;
						$plugin_notice = gettext('<strong>Error parsing <em>plugin_notice</em> string!</strong>');
					} else {
						$plugin_notice = processExtensionVariable($plugin_notice);
					}
				} 
				$plugin_author = '';
				if ($str = isolate('$plugin_author', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 4;
						$plugin_author = gettext('<strong>Error parsing <em>plugin_author</em> string!</strong>');
					}
				} 
				$plugin_version = '';
				if ($str = isolate('$plugin_version', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 5;
						$plugin_version = ' ' . gettext('<strong>Error parsing <em>plugin_version</em> string!</strong>');
					}
				} 
				$plugin_disable = false;
				if ($str = isolate('$plugin_disable', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 6;
						$plugin_disable = gettext('<strong>Error parsing <em>plugin_disable</em> string!</strong>');
					} 
				} 
				$plugin_disable = isIncompatibleExtension($plugin_disable);
				if ($plugin_disable) {
					disableExtension($extension);
				}
				$plugin_siteurl = '';
				if ($str = isolate('$plugin_siteurl', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 7;
						$plugin_siteurl = gettext('<strong>Error parsing <em>plugin_siteurl</em> string!</strong>');
					}
				} 
				$plugin_date = '';
				if ($str = isolate('$plugin_date', $pluginStream)) {
					if (false === eval($str)) {
						$parserr = $parserr | 8;
						$plugin_date = gettext('<strong>Error parsing <em>plugin_date</em> string!</strong>');
					}
				} 
				$plugin_URL = FULLWEBPATH . '/' . ZENFOLDER . '/pluginDoc.php?extension=' . $extension;
				if ($third_party_plugin) {
					$plugin_URL .= '&amp;thirdparty';
				}
				$currentsetting = getOption($opt);
				$plugin_is_filter = 1 | THEME_PLUGIN;
				if ($str = isolate('$plugin_is_filter', $pluginStream)) {
					eval($str);
					if ($plugin_is_filter < THEME_PLUGIN) {
						if ($plugin_is_filter < 0) {
							$plugin_is_filter = abs($plugin_is_filter) | THEME_PLUGIN | ADMIN_PLUGIN;
						} else {
							if ($plugin_is_filter == 1) {
								$plugin_is_filter = 1 | THEME_PLUGIN;
							} else {
								$plugin_is_filter = $plugin_is_filter | CLASS_PLUGIN;
							}
						}
					}
					if ($currentsetting && $currentsetting != $plugin_is_filter) {
						setOption($opt, $plugin_is_filter); //	the script has changed its setting!
					}
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
						<input type="hidden" name="present_<?php echo $opt; ?>" id="present_<?php echo $opt; ?>" value="1" />
						<label id="<?php echo $extension; ?>">
							<?php
							if ($third_party_plugin) {
								$whose = gettext('third party plugin');
								$path = stripSuffix($paths[$extension]) . '/logo.png';
								if (file_exists($path)) {
									$ico = str_replace(SERVERPATH, WEBPATH, $path);
								} else {
									$ico = 'images/place_holder_icon.png';
								}
							} else {
								$whose = 'Zenphoto official plugin';
								$ico = 'images/zp_gold.png';
							}
							?>
							<img class="zp_logoicon" src="<?php echo $ico; ?>" alt="<?php echo gettext('logo'); ?>" title="<?php echo $whose; ?>" />
							<?php
							if ($plugin_is_filter & CLASS_PLUGIN) {
								$icon = $plugin_is_filter | THEME_PLUGIN | ADMIN_PLUGIN;
							} else {
								$icon = $plugin_is_filter;
							}
							if ($icon & THEME_PLUGIN | FEATURE_PLUGIN) {
								?>
								<a title="<?php echo gettext('theme plugin'); ?>"><img class="zp_logoicon" src="images/pictures.png" /></a>
								<?php
							} else {
								?>
								<img src="images/place_holder_icon.png" />
								<?php
							}
							if ($icon & ADMIN_PLUGIN) {
								?>
								<a title="<?php echo gettext('admin plugin'); ?>"><img class="zp_logoicon" src="images/cache.png" /></a>
								<?php
							} else {
								?>
								<img src="images/place_holder_icon.png" />
								<?php
							}
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
									<img src="images/action.png" alt="" class="zp_logoicon" />
									<input type="hidden" name="<?php echo $opt; ?>" id="<?php echo $opt; ?>" value="0" />
								</span>
								<?php
							} else {
								?>
								<input type="checkbox" name="<?php echo $opt; ?>" id="<?php echo $opt; ?>" value="<?php echo $plugin_is_filter; ?>"<?php echo $attributes; ?> />
								<?php
							}
							echo '<strong>' . html_encode($plugin_name) . '</strong>';
							if (!empty($plugin_version)) {
								echo ' v' . $plugin_version;
							}
							if(!empty($plugin_date)) {
								echo ' <small>(' . html_encode($plugin_date) .')</small>';
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
						<span class="icons"><a class="plugin_doc" href="<?php echo $plugin_URL; ?>"><img class="icon-position-top3" src="images/info.png" title="<?php printf(gettext('More information on %s'), $extension); ?>" alt=""></a></span>
						<?php
						if ($optionlink) {
							?>
							<span class="icons"><a href="<?php echo $optionlink; ?>" title="<?php printf(gettext("Change %s options"), $extension); ?>"><img class="icon-position-top3" src="images/options.png" alt="" /></a></span>
							<?php
						}
						?>
					</td>
					<td colspan="2">
						<?php
						echo $plugin_description;
						if ($plugin_deprecated) {
							echo '<p class="warningbox"><strong>' . gettext('Deprecated').  ':</strong> ' . $plugin_deprecated . '</p>';
						}
						if ($plugin_disable) {
							?>
							<div id="showdisable_<?php echo $extension; ?>" class="warningbox">
								<?php
								if ($plugin_disable) {
									echo $plugin_disable;
								}
								?>
							</div>
							<?php
						}
						if ($plugin_notice) {
							?>
							<div class="notebox">
								<?php echo $plugin_notice;?>
							</div>
							<?php
						}
						echo '<p><small><strong>' . sprintf(gettext('by %s'), $plugin_author);
						if(!empty($plugin_siteurl)) {
							echo ' | <a href="' . html_encode($plugin_siteurl) . '" rel="noopener" target="_blank" title="'. html_encode($plugin_siteurl).'">' . gettext('Visit plugin site') . '</a>';
						}
						echo '</strong></small></p>';
						?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="4" id="imagenavb">
					<?php printPageSelector($pagenumber, $rangeset, 'admin-plugins.php', array('page' => 'plugins', 'tab' => $subtab)); ?>
				</td>
			</tr>
		</table>
		<br />
		<ul class="iconlegend">
			<li><img src="images/zp_gold.png" alt=""><?php echo gettext('Official plugin'); ?></li>
			<li><img src="images/info.png" alt=""><?php echo gettext('Usage info'); ?></li>
			<li><img src="images/options.png" alt=""><?php echo gettext('Options'); ?></li>
			<li><img src="images/warn.png" alt=""><?php echo gettext('Warning note'); ?></li>
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



