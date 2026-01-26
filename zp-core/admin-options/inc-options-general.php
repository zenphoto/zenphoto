<div id="tab_gallery" class="tabbox">
						<?php
						if (isset($_GET['local_failed'])) {
							$languages = array_flip(generateLanguageList('all'));
							$locale = sanitize($_GET['local_failed']);
							echo '<div class="errorbox">';
							echo "<h2>" .
							sprintf(gettext("<em>%s</em> is not available."), html_encode($languages[$locale])) .
							' ' . sprintf(gettext("The locale %s is not supported on your server."), html_encode($locale)) .
							'<br />' . gettext('See the troubleshooting guide on zenphoto.org for details.') .
							"</h2>";
							echo '</div>';
						}
						?>
						<?php filter::applyFilter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savegeneraloptions" value="yes" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<tr>
								<?php
									$offset = timezoneDiff($_zp_server_timezone, $tz = getOption('time_zone'));
									?>
									<td width="175"><?php echo gettext("Time zone:"); ?></td>
									<td width="350">
										<?php
										$zones = getTimezones();
										?>
										<select id="time_zone" name="time_zone">
											<option value="" style="background-color:LightGray"><?php echo gettext('*not specified'); ?></option>
											<?php generateListFromArray(array($tz), $zones, false, false); ?>
										</select>
									</td>
									<td>
										<p><?php printf(gettext('Your server reports its time zone as: <code>%s</code>.'), $_zp_server_timezone); ?></p>
										<p><?php printf(gettext('Your time zone offset in hours is: %d. If your time zone is different from the servers, select the correct time zone here.'), $offset); ?></p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("URL options:"); ?></td>
									<td width="350">
										<p>
											<label>
												<?php
												if (MOD_REWRITE) {
													$state = ' checked="checked"';
												} else {
													$state = '';
												}
												?>
												<input type="checkbox" name="mod_rewrite" value="1"<?php echo $state; ?> />
												<?php echo gettext('mod rewrite'); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="UTF8_image_URI" value="1"<?php checked('1', UTF8_IMAGE_URI); ?> />
												<?php echo gettext('UTF8 image URIs'); ?>
											</label>
										</p>
										<p><?php echo gettext("mod_rewrite suffix:"); ?> <input type="text" size="10" name="mod_rewrite_image_suffix" value="<?php echo html_encode(getOption('mod_rewrite_image_suffix')); ?>" /></p>
									</td>
									<td>
										<p>
											<?php echo gettext("If you have Apache <em>mod rewrite</em> (or equivalent), put a checkmark on the <em>mod rewrite</em> option and you will get nice cruft-free URLs."); ?>
											<?php echo sprintf(gettext('The <em>tokens</em> used in rewritten URIs may be altered to your taste. See the <a href="%s">plugin options</a> for <code>rewriteTokens</code>.'), WEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=plugin&single=rewriteTokens'); ?>
											<?php
											if (!getOption('mod_rewrite_detected'))
												echo '<p class="notebox">' . gettext('Setup did not detect a working <em>mod_rewrite</em> facility. Since this test is not 100% reliable this may be a false report though.'), '</p>';
											?>
										</p>
										<p><?php echo gettext("If you are having problems with images whose names contain characters with diacritical marks try changing the <em>UTF8 image URIs</em> setting."); ?></p>
										<p><?php echo gettext("If <em>mod_rewrite</em> is checked above, zenphoto will append the <em>mod_rewrite suffix</em> to the end of image URLs. (This helps search engines.) Examples: <em>.html, .php</em>, etc."); ?></p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Language:"); ?></td>
									<td width="350">
										<?php
										$currentValue = SITE_LOCALE;
										?>
										<br />
										<ul class="languagelist">
											<?php
											$systemlocales = getSystemLocales(true);
											$locales = generateLanguageList('all');
											$locales[gettext("HTTP_Accept_Language")] = '';
											ksort($locales, SORT_LOCALE_STRING);
											$vers = explode('-', ZENPHOTO_VERSION);
											$vers = explode('.', $vers[0]);
											while (count($vers) < 3) {
												$vers[] = 0;
											}
											$zpversion = $vers[0] . '.' . $vers[1] . '.' . $vers[2];
											$c = 0;
											foreach ($locales as $language => $dirname) {
												$languageAlt = $language;
												$class = '';
												if(!empty($systemlocales) && !in_array($dirname, $systemlocales) && $language != 'HTTP_Accept_Language') {
													$language = '<img src="'. WEBPATH . '/'. ZENFOLDER .'/images/action.png" alt="'. gettext('Locale not installed'). '"> ' . $language;
												}
												if (!empty($dirname) && $dirname != 'en_US') {
													$version = '';
													$po = file_get_contents(SERVERPATH . "/" . ZENFOLDER . "/locale/" . $dirname . '/LC_MESSAGES/zenphoto.po');
													$i = strpos($po, 'Project-Id-Version:');
													if ($i !== false) {
														$j = strpos($po, '\n', $i);
														if ($j !== false) {
															$pversion = strtolower(substr($po, $i + 19, $j - $i - 19));
															$vers = explode('.', trim(str_replace('zenphoto', '', $pversion)));
															while (count($vers) < 3) {
																$vers[] = 0;
															}
															$version = (int) $vers[0] . '.' . (int) $vers[1] . '.' . (int) $vers[2];
														}
													}
													if (version_compare($version, $zpversion) < 0) {
														if (empty($version)) {
															$version = '0.0.0';
														}
														$language .= ' <small>{v' . $version . '}</small>';
														$languageAlt .= ' {v' . $version . '}';
														$class = ' style="background-color:#FFEFB7;"';
													}
												} else {
													$version = $zpversion;
												}
												if (empty($dirname)) {
													$flag = WEBPATH . '/' . ZENFOLDER . '/locale/auto.png';
												} else {
													$flag = getLanguageFlag($dirname);
												}
												if (getOption('unsupported_' . $dirname)) {
													$c_attrs = $r_attrs = ' disabled="disabled"';
												} else {
													if (getOption('disallow_' . $dirname)) {
														$c_attrs = '';
														$r_attrs = ' disabled="disabled"';
													} else {
														$c_attrs = ' checked="checked"';
														$r_attrs = '';
													}
												}

												if ($dirname == $currentValue) {
													$r_attrs = ' checked="checked"';
													$c_attrs = ' checked="checked" disabled="disabled"';
													$ci = $c;
												}
												$c++;
												?>
												<li<?php echo $class; ?>>
													<label class="displayinline" >
														<input type="radio" name="locale" id="r_<?php echo $dirname; ?>" value="<?php echo $dirname; ?>"
																	 onclick="javascript:radio_click('<?php echo $dirname; ?>');" <?php echo $r_attrs; ?>/>
													</label>
													<label class="displayinline flags">
														<input id="language_allow_<?php echo $dirname; ?>" name="language_allow_<?php echo $dirname; ?>" type="checkbox"
																	 value="<?php echo $dirname; ?>"<?php echo $c_attrs; ?>
																	 onclick="javascript:enable_click('<?php echo $dirname; ?>');" />
														<img src="<?php echo $flag; ?>" alt="<?php echo $languageAlt; ?>" width="24" height="16" />
														<?php echo $language; ?>
													</label>
												</li>
												<?php
											}
											?>
										</ul>
										<script>
																			var oldselect = '<?php echo $currentValue; ?>';
																			function radio_click(id) {
																			if ($('#r_' + id).prop('checked')) {
																			$('#language_allow_' + oldselect).removeAttr('disabled');
																							oldselect = id;
																							$('#language_allow_' + id).attr('disabled', 'disabled');
																			}
																			}
															function enable_click(id) {
															if ($('#language_allow_' + id).prop('checked')) {
															$('#r_' + id).removeAttr('disabled');
															} else {
															$('#r_' + id).attr('disabled', 'disabled');
															}
															}
															$(document).ready(function(){
															$('ul.languagelist').scrollTo('li:eq(<?php echo ($ci - 2); ?>)');
															});</script>
										<br class="clearall" />
										<p class="notebox"><?php printf(gettext('Highlighted languages are not current with Zenphoto Version %1$s. (The version Zenphoto of the out-of-date language is shown in braces.) Please check the <a href="%2$s">translation repository</a> for new and updated language translations.'), $zpversion, 'https://github.com/zenphoto/zenphoto/tree/master/zp-core/locale'); ?></p>
										<?php if(!empty($systemlocales)) { // if class ResourceBundle does not exist this has no meaning ?>
											<p class="notebox"><?php printf(gettext('Languages marked with the %1$s icon have no matching locale installed on the server and therefore will not work. This is a technical requirement by native <a href="%2$s">PHP gettext</a>.'), '<img src="'. WEBPATH . '/'. ZENFOLDER .'/images/action.png" alt="'. gettext('Locale not installed'). '">', 'https://www.php.net/manual/en/book.gettext.php'); ?></p>
										<?php } ?>

										<label class="checkboxlabel">
											<input type="checkbox" name="multi_lingual" value="1"	<?php checked('1', getOption('multi_lingual')); ?> /><?php echo gettext('Multi-lingual'); ?>
										</label>
									</td>
									<td>
										<p><?php echo gettext("You can disable languages by unchecking their checkboxes. Only checked languages will be available to the installation."); ?></p>
										<p><?php echo gettext("Select the preferred language to display text in. (Set to <em>HTTP_Accept_Language</em> to use the language preference specified by the viewer’s browser.)"); ?></p>
										<p><?php echo gettext("Set <em>Multi-lingual</em> to enable multiple language input for options that provide theme text."); ?></p>

									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Date and time formats:"); ?></td>
									<td width="350">
											<?php
											printDatetimeFormatSelector();
											$use_localized_date = getOption('date_format_localized');
											$time_display_disabled = getOption('time_display_disabled');
										?>
										<p>
											<label class="checkboxlabel">
												<input type="checkbox" name="date_format_localized" value="1"	<?php checked('1', $use_localized_date); ?> /><?php echo gettext('Use localized dates'); ?>
											</label>
										</p>
										<p>	
											<label class="checkboxlabel">
												<input type="checkbox" name="time_display_disabled" value="1"	<?php checked('1', $time_display_disabled); ?> /><?php echo gettext('Disable time for display'); ?>
											</label>
										</p>
									</td>
									<td>
										<p><?php echo gettext('Formats for date and time. Select from the lists or set to <code>custom</code> and provide a <a href="https://www.php.net/manual/en/datetime.format.php">datetime</a> format string for date and time in the custom boxes.'); ?></p>
										<p><?php echo gettext('If time is disabled for display standard theme and admin functions will not display it.'); ?></p>
									<?php if (extension_loaded('intl')) { ?>
										<p class="notebox">
										<?php echo gettext('NOTE: If localized dates are enabled and you are using a custom date format you need to provide an <a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/">ICU dateformat string</a>.'); ?>
									</p>
								<?php } else { ?>
									<p class="warningbox">
										<?php echo gettext('The intl PHP extension is not installed and localized dates are not available on your system.'); ?>
									</p>
								<?php } ?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Charset:"); ?></td>
									<td width="350">
										<select id="charset" name="charset">
											<?php
											$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
											$totalsets = $_zp_utf8->charsets;
											asort($totalsets);
											foreach ($totalsets as $key => $char) {
												?>
												<option value="<?php echo $key; ?>" <?php
												if ($key == LOCAL_CHARSET)
													echo 'selected="selected"';
												if (!array_key_exists($key, $sets))
													echo 'style="color: gray"';
												?>><?php echo $char; ?></option>
																<?php
															}
															?>
										</select>
									</td>
									<td>
										<?php
										echo gettext('The character encoding to use internally. Leave at <em>Unicode (UTF-8)</em> if you are unsure.');
										if (!function_exists('mb_list_encodings')) {
											echo ' ' . gettext('Character sets <span style="color:gray">shown in gray</span> have no character translation support.');
										}
										?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Filesystem Charset:"); ?></td>
									<td width="350">
										<select id="filesystem_charset" name="filesystem_charset">
											<?php
											$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
											$totalsets = $_zp_utf8->charsets;
											asort($totalsets);
											foreach ($totalsets as $key => $char) {
												?>
												<option value="<?php echo $key; ?>" <?php
												if ($key == FILESYSTEM_CHARSET)
													echo 'selected="selected"';
												if (!array_key_exists($key, $sets))
													echo 'style="color: gray"';
												?>><?php echo $char; ?></option>
																<?php
															}
															?>
										</select>
									</td>
									<td>
										<?php
										echo gettext('The character encoding to use for the filesystem. Leave at <em>Unicode (UTF-8)</em> if you are unsure.');
										if (!function_exists('mb_list_encodings')) {
											echo ' ' . gettext('Character sets <span style="color:gray">shown in gray</span> have no character translation support.');
										}
										?>
									</td>
								</tr>

								<tr>
									<td width="175"><?php echo gettext("Allowed tags:"); ?></td>
									<td width="350">
										<p><textarea name="allowed_tags" id="allowed_tags" style="width: 340px" rows="10" cols="35"><?php echo html_encode(getOption('allowed_tags')); ?></textarea></p>
									</td>
									<td>
										<script>
																							function resetallowedtags() {
																							$('#allowed_tags').val(<?php
									$t = getOption('allowed_tags_default');
									$tags = explode("\n", $t);
									$c = 0;
									foreach ($tags as $t) {
										$t = trim($t);
										if (!empty($t)) {
											if ($c > 0) {
												echo '+';
												echo "\n";
												?>
				<?php
			}
			$c++;
			echo "'" . $t . '\'+"\n"';
		}
	}
	?>);
																							}
										</script>
										<p><?php echo gettext("Tags and attributes allowed in comments, descriptions, and other fields."); ?><p>
										<p><?php echo gettext("Follow the form: <em>tag</em> =&gt; (<em>attribute</em> =&gt;() <em>attribute</em>=&gt;() <em>attribute</em> =&gt;()....etc.)"); ?></p>
										<?php if (EDITOR_SANITIZE_LEVEL == 4) { ?>
											<p class="notebox"><?php echo gettext('<strong>Note:</strong> visual editing is enabled so the editor overrides these settings on tags where it is active.'); ?></p>
										<?php } ?>
										<p class="buttons">
											<a href="javascript:resetallowedtags()" ><?php echo gettext('reset to default'); ?></a>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175">
										<?php echo gettext("Cookies:"); ?>
									</td>
									<td width="350">
										<?php
										if (!GALLERY_SESSION) {
											echo gettext('Path');
											?>
											<input type="text" size="48" id="zenphoto_cookie_path" name="zenphoto_cookie_path" value="<?php echo getOption('zenphoto_cookie_path'); ?>" />
											<p>
												<?php
												echo gettext('Duration');
												?>
												<input type="text" name="cookie_persistence" value="<?php echo COOKIE_PERSISTENCE; ?>" />
											</p>
											<?php
										}
										?>
										<p>
											<label>
												<input type="checkbox" name="album_session" id="album_session" value="1" <?php checked('1', GALLERY_SESSION); ?> />
												<?php echo gettext("enable gallery sessions"); ?>
											</label>
										</p>
									</td>
									<td>
										<?php
										if (!GALLERY_SESSION) {
											?>
											<p><?php printf(gettext('The <em>path</em> Zenphoto will use when storing cookies. (Leave empty to default to <em>%s</em>)'), WEBPATH); ?></p>
											<p><?php echo gettext("Set to the time in seconds that cookies should be kept by browsers."); ?></p>
											<?php
										}
										?>
										<p><?php echo gettext('If this option is selected Zenphoto will use <a href="https://www.php.net/manual/en/intro.session.php">PHP sessions</a> instead of cookies to make visitor settings persistent.'); ?></p>
										<p class="notebox"><?php echo gettext('<strong>NOTE</strong>: Sessions will normally close when the browser closes causing all password and other data to be discarded. They may close more frequently depending on the runtime configuration. Longer <em>lifetime</em> of sessions is generally more conducive to a pleasant user experience. Cookies are the prefered storage option since their duration is determined by the <em>Cookie duration</em> option. ') ?>
									</td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext("Name:"); ?></p>
										<p><?php echo gettext("Email:"); ?></p>
									</td>
									<td width="350">
										<p><?php print_language_string_list(getOption('site_email_name'), 'site_email_name'); ?></p>
										<p><input type="text" size="48" id="site_email" name="site_email" value="<?php echo getOption('site_email'); ?>" /></p>
									</td>
									<td><?php echo gettext("This email name and address will be used as the <em>From</em> address for all mails sent by Zenphoto."); ?></td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext("Users per page:"); ?></p>
										<p><?php echo gettext("Plugins per page:"); ?></p>
										<?php
										if (extensionEnabled('zenpage')) {
											?>
											<p><?php echo gettext("Articles per page:"); ?></p>
											<?php
										}
										?>
									</td>
									<td width="350">
										<input type="text" size="5" id="users_per_page" name="users_per_page" value="<?php echo getOption('users_per_page'); ?>" />
										<br />
										<input type="text" size="5" id="plugins_per_page" name="plugins_per_page" value="<?php echo getOption('plugins_per_page'); ?>" />
										<?php
										if (extensionEnabled('zenpage')) {
											?>
											<br />
											<input type="text" size="5" id="articles_per_page" name="articles_per_page" value="<?php echo getOption('articles_per_page'); ?>" />
											<?php
										}
										?>
									</td>
									<td><?php echo gettext('These options control the number of items displayed on their paginated admin pages. If you exerience problems saving these pages, reduce the number shown here.'); ?></td>
								</tr>
								<?php
								$subtabs = array('security' => gettext('security'), 'debug' => gettext('debug'));
								?>
								<tr>
									<td width="175">
										<?php
										foreach ($subtabs as $subtab => $log) {
											if (!is_null(getOption($subtab . '_log_size'))) {
												printf(gettext('<p>%s log limit</p>'), $log);
											}
										}
										?>
									</td>
									<td width="350">
										<?php
										foreach ($subtabs as $subtab => $log) {
											if (!is_null($size = getOption($subtab . '_log_size'))) {
												?>
												<p>
													<input type="text" size="4" id="<?php echo $log ?>_log" name="log_size_<?php echo $subtab; ?>" value="<?php echo $size; ?>" />
													<input type="checkbox" id="<?php echo $log ?>_log" name="log_mail_<?php echo $subtab; ?>" value="1" <?php checked('1', getOption($subtab . '_log_mail')); ?> /> <?php echo gettext('e-mail when exceeded'); ?>
												</p>
												<?php
											}
										}
										?>
									</td>
									<td><?php echo gettext('Logs will be "rolled" over when they exceed the specified size. If checked, the administrator will be e-mailed when this occurs.') ?></td>
								</tr>

								<tr>
									<td width="175">
										<?php echo gettext("Daily logs:"); ?></p

									</td>
									<td width="350">
										<label>
											<input type="checkbox" id="daily_logs" name="daily_logs" value="1" <?php checked('1', getOption('daily_logs')); ?> /> <?php echo gettext('Enable daily logs'); ?>
										</label>
									</td>
									<td><?php echo gettext('If checked logs will be created daily by appending the dateformat YYYY-mm-dd to the filename.'); ?></td>
								</tr>

								<?php filter::applyFilter('admin_general_data'); ?>
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>

							</table>
						</form>
					</div>
					<!-- end of tab-general div -->
					<?php