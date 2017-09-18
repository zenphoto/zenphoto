<?php
/*
 * Guts of the plugin options tab
 */
$optionRights = ADMIN_RIGHTS;

function saveOptions() {
	global $_zp_gallery;

	$notify = $returntab = NULL;
	if (isset($_POST['checkForPostTruncation'])) {
		// all plugin options are handled by the custom option code.
		if (isset($_GET['single'])) {
			$returntab = "&tab=plugin&single=" . sanitize($_GET['single']);
		} else {
			$returntab = "&tab=plugin&subpage=$subpage";
		}
	} else {
		$notify = '?post_error';
	}

	return array($returntab, $notify, NULL, NULL, NULL);
}

function getOptionContent() {
	global $_zp_gallery;

	if (isset($_GET['subpage'])) {
		$subpage = sanitize_numeric($_GET['subpage']);
	} else {
		if (isset($_POST['subpage'])) {
			$subpage = sanitize_numeric($_POST['subpage']);
		} else {
			$subpage = 0;
		}
	}

	if (zp_loggedin(ADMIN_RIGHTS)) {
		if (isset($_GET['single'])) {
			$showExtension = sanitize($_GET['single']);
			$_GET['show-' . $showExtension] = true;
		} else {
			$showExtension = NULL;
		}

		$_zp_plugin_count = 0;

		$plugins = array();
		$list = array_keys(getPluginFiles('*.php'));
		natcasesort($list);
		foreach ($list as $extension) {
			$option_interface = NULL;
			$path = getPlugin($extension . '.php');
			$pluginStream = file_get_contents($path);
			$str = isolate('$option_interface', $pluginStream);
			if (false !== $str) {
				$plugins[] = $extension;
			}
		}

		if (isset($_GET['single'])) {
			$single = sanitize($_GET['single']);
			$list = $plugins;
			$plugins = array($showExtension);
		} else {
			$single = false;
		}
		$rangeset = getPageSelector($plugins, PLUGINS_PER_PAGE);
		$plugins = array_slice($plugins, $subpage * PLUGINS_PER_PAGE, PLUGINS_PER_PAGE);
		?>
		<div id="tab_plugin" class="tabbox">
			<script type="text/javascript">
				// <!-- <![CDATA[
				var optionholder = new Array();
				// ]]> -->
			</script>
			<form class="dirtylistening" onReset="setClean('form_options');" id="form_options" action="?action=saveoptions<?php if (isset($_GET['single'])) echo '&amp;single=' . $showExtension; ?>" method="post" autocomplete="off" >
				<?php XSRFToken('saveoptions'); ?>
				<input type="hidden" name="saveoptions" value="plugin" />
				<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
				<table>
					<?php
					if ($single) {
						?>
						<tr>
							<td colspan="100%">
								<p class="buttons">
									<button type="submit" value="<?php echo gettext('save') ?>">
										<?php echo CHECKMARK_GREEN; ?>
										<strong><?php echo gettext("Apply"); ?></strong>
									</button>
									<button type="reset" value="<?php echo gettext('reset') ?>">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Reset"); ?>
										</strong></button>
								</p>
							</td>
						</tr>
						<?php
					}
					if (!$showExtension) {
						?>
						<tr>
							<th style="text-align:left">
							</th>
							<th style="text-align:right; padding-right: 10px;">
								<?php printPageSelector($subpage, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
							</th>
							<th></th>
						</tr>
						<?php
					}
					foreach ($plugins as $extension) {
						$option_interface = NULL;
						$enabled = extensionEnabled($extension);
						$path = getPlugin($extension . '.php');
						$pluginStream = file_get_contents($path);
						if ($str = isolate('$plugin_description', $pluginStream)) {
							if (false === eval($str)) {
								$plugin_description = '';
							}
						} else {
							$plugin_description = '';
						}

						$str = isolate('$option_interface', $pluginStream);
						if (false !== $str) {
							require_once($path);
							if (preg_match('/\s*=\s*new\s(.*)\(/i', $str)) {
								eval($str);
								$warn = gettext('<strong>Note:</strong> Instantiating the option interface within the plugin may cause performance issues. You should instead set <code>$option_interface</code> to the name of the class as a string.');
							} else {
								eval($str);
								$option_interface = new $option_interface;
								$warn = '';
							}
						}
						if (!empty($option_interface)) {
							$_zp_plugin_count++;
							?>
							<!-- <?php echo $extension; ?> -->
							<tr>
								<td class="option_name<?php if ($showExtension) echo ' option_shaded'; ?>">
									<span id="<?php echo $extension; ?>">
										<?php
										if ($showExtension) {
											?>

											<?php echo $extension; ?>

											<?php
											if (!$enabled) {
												?>

												<a title="<?php echo gettext('The plugin is not enabled'); ?>">
													<?php echo WARNING_SIGN_ORANGE; ?>
												</a>

												<?php
											}
											?>

											<?php
										} else {
											$optionlink = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&amp;tab=plugin&amp;single=' . html_encode($extension);
											?>
											<span class="icons">
												<a href="<?php echo $optionlink; ?>" title="<?php printf(gettext("Change %s options"), html_encode($extension)); ?>">
													<span<?php if (!$enabled) echo ' style="color: orange"'; ?>>
														<?php echo OPTIONS_ICON; ?>
													</span>
													<?php echo $extension; ?>
												</a>
											</span>
											<?php
										}
										if ($warn) {
											?>
											<?php echo EXCLAMATION_RED; ?>
											<?php
										}
										?>
									</span>
								</td>
								<td class="option_value<?php if ($showExtension) echo ' option_shaded'; ?>" colspan="100%">
									<?php echo $plugin_description; ?>
								</td>
							</tr>
							<?php
							if ($warn) {
								?>
								<tr style="display:none" class="pluginextrahide">
									<td colspan="100%">
										<p class="notebox" ><?php echo $warn; ?></p>
									</td>
								</tr>
								<?php
							}
							if ($showExtension) {
								$supportedOptions = $option_interface->getOptionsSupported();
								if (count($supportedOptions) > 0) {
									customOptions($option_interface, '', NULL, false, $supportedOptions, NULL, NULL, $extension);
								}
								$key = array_search($extension, $list);
								if ($key > 0) {
									$prev = $list[$key - 1];
								} else {
									$prev = NULL;
								}
								if ($key + 1 >= count($list)) {
									$next = NULL;
								} else {
									$next = $list[$key + 1];
								}
							}
						}
					}
					if ($_zp_plugin_count == 0) {
						?>
						<tr>
							<td style="padding: 0;margin:0" colspan="100%">
								<?php
								echo gettext("There are no plugin options to administer.");
								?>
							</td>
						</tr>
						<?php
					} else {
						if ($single) {
							?>
							<tr>
								<td colspan="100%">
									<p class="buttons">
										<button type="submit" value="<?php echo gettext('save') ?>">
											<?php echo CHECKMARK_GREEN; ?>
											<strong><?php echo gettext("Apply"); ?></strong>
										</button>
										<button type="reset" value="<?php echo gettext('reset') ?>">
											<?php echo CROSS_MARK_RED; ?>
											<strong><?php echo gettext("Reset"); ?></strong>
										</button>
									</p>
								</td>
							</tr>
							<?php
						} else {
							?>
							<tr>
								<th></th>
								<th style="text-align:right; padding-right: 10px;">
									<?php printPageSelector($subpage, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
								</th>
								<th></th>
							</tr>
							<?php
						}
						?>
					</table>
					<?php
					if ($single) {
						?>
						<p class="padded">
							<a href="?page=options&amp;tab=plugin&amp;single=<?php echo urlencode($prev); ?>"><?php echo $prev; ?></a>
							<span class="floatright" >
								<a href="?page=options&amp;tab=plugin&amp;single=<?php echo urlencode($next); ?>"><?php echo $next; ?></a>
							</span>
						</p>
						<?php
					}
					?>

					<input type="hidden" name="checkForPostTruncation" value="1" />
					<?php
				}
				?>
			</form>

		</div>
		<!-- end of tab_plugin div -->
		<?php
	}
}
