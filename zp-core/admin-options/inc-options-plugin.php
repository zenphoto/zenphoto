<?php
if (isset($_GET['single'])) {
						$showExtension = sanitize($_GET['single']);
						$_GET['show-' . $showExtension] = true;
					} else {
						$showExtension = NULL;
					}

					$_zp_plugin_count = 0;

					$plugins = array();
					if (isset($_GET['single'])) {
						$plugins = array($showExtension);
					} else {
						$list = array_keys(getEnabledPlugins());
						foreach ($list as $extension) {
							$option_interface = NULL;
							$path = getPlugin($extension . '.php');
							$pluginStream = file_get_contents($path);
							$str = isolate('$option_interface', $pluginStream);
							if (false !== $str) {
								$plugins[] = $extension;
							}
						}
						sortArray($plugins);
					}
					$rangeset = getPageSelector($plugins, PLUGINS_PER_PAGE);
					$plugins = array_slice($plugins, $pagenumber * PLUGINS_PER_PAGE, PLUGINS_PER_PAGE);
					?>
					<div id="tab_plugin" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<script>
																							var optionholder = new Array();
						</script>
						<form class="dirty-check" id="form_options" action="?action=saveoptions<?php if (isset($_GET['single'])) echo '&amp;single=' . html_encode($showExtension); ?>" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="savepluginoptions" value="yes" />
							<input type="hidden" name="pagenumber" value="<?php echo $pagenumber; ?>" />
							<table class="bordered">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<?php
								if (!$showExtension) {
									?>
									<tr>
										<th style="text-align:left">
										</th>
										<th style="text-align:right; padding-right: 10px;">
											<?php printPageSelector($pagenumber, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
										</th>
										<th></th>
									</tr>
									<?php
								}
								foreach ($plugins as $extension) {
									$option_interface = NULL;
									$path = getPlugin($extension . '.php');
									$pluginStream = file_get_contents($path);
									$plugin_name = '';
									if ($str = isolate('$plugin_name', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_name = '';
										}
									}
									if(empty($plugin_name)) {
										$plugin_name = $extension;
									}
									$plugin_description = '';
									if ($str = isolate('$plugin_description', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_description = '';
										} else {
											$plugin_description = processExtensionVariable($plugin_description);
										}
									}
									$plugin_version = '';
									if ($str = isolate('$plugin_version', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_version = '';
										}
									}
									$plugin_deprecated = '';
									if ($str = isolate('$plugin_deprecated', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_deprecated = '';
										} else {
											$plugin_deprecated = processExtensionVariable($plugin_deprecated);
											if (is_bool($plugin_deprecated) || empty($plugin_deprecated)) {
												$plugin_deprecated = gettext('This plugin will be removed in future versions.');
											}
										}
									}
									$plugin_date = '';
									if ($str = isolate('$plugin_date', $pluginStream)) {
										if (false === eval($str)) {
											$plugin_date = '';
										}
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
											<td style="padding: 0;margin:0" colspan="3">
												<table class="options" style="border: 0" id="plugin-<?php echo $extension; ?>">
													<tr>
														<?php
														if ($showExtension) {
															$v = 1;
														} else {
															$v = 0;
														}
														?>
														<th style="text-align:left; width: 20%">
															<span id="<?php echo $extension; ?>" ></span>
															<?php
															if (!$showExtension) {
																$optionlink = FULLWEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&amp;tab=plugin&amp;single=' . html_encode($extension);
																?>
																<span class="icons"><a href="<?php echo $optionlink; ?>" title="<?php printf(gettext("Change %s options"), html_encode($extension)); ?>"><img class="icon-position-top3" src="images/options.png" alt="" />
																		<?php
																	}
																	echo html_encode($plugin_name);
																	if(!empty($plugin_version)) {
																		echo ' v'. html_encode($plugin_version);
																	}
																	if(!empty($plugin_date)) {
																		echo ' <small>('. html_encode($plugin_date).')</small>';
																	}
																	if (!$showExtension) {
																		?>
																	</a></span>
																<?php
															}
															if ($warn) {
																?>
																<img src="images/action.png" alt="<?php echo gettext('warning'); ?>" />
																<?php
															}
															?>
														</th>
														<th style="text-align:left; font-weight: normal;" colspan="2">
															<?php
															echo $plugin_description;
															if($plugin_deprecated) {
																echo '<p class="warningbox"><strong>' . gettext('Deprecated').  ':</strong> ' . $plugin_deprecated . '</p>';
															}
															?>
														</th>
													</tr>
													<?php
													if ($warn) {
														?>
														<tr style="display:none" class="pluginextrahide">
															<td colspan="3">
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
													}
													?>
												</table>
											</td>
										</tr>
										<?php
									}
								}
								if ($_zp_plugin_count == 0) {
									?>
									<tr>
										<td style="padding: 0;margin:0" colspan="3">
											<?php
											echo gettext("There are no plugin options to administer.");
											?>
										</td>
									</tr>
									<?php
								} else {
									?>
									<tr>
										<th></th>
										<th style="text-align:right; padding-right: 10px;">
											<?php printPageSelector($pagenumber, $rangeset, 'admin-options.php', array('page' => 'options', 'tab' => 'plugin')); ?>
										</th>
										<th></th>
									</tr>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
								</table> <!-- single plugin page table -->
								<input type="hidden" name="checkForPostTruncation" value="1" />
								<?php
							}
							?>
						</form>

					</div>