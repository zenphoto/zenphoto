<?php
/*
 * General options
 */
$optionRights = ADMIN_RIGHTS;

function saveOptions() {
	global $_zp_gallery;

	$notify = $returntab = NULL;
	$returntab = "&tab=general";
	$tags = strtolower(sanitize($_POST['allowed_tags'], 0));
	$test = "(" . $tags . ")";
	$a = parseAllowedTags($test);
	if ($a) {
		setOption('allowed_tags', $tags);
	} else {
		$notify = '?tag_parse_error=' . $a;
	}
	$oldloc = SITE_LOCALE; // get the option as stored in the database, not what might have been set by a cookie
	$newloc = sanitize($_POST['locale'], 3);
	$languages = generateLanguageList(true);
	$languages[''] = '';
	$disallow = array();
	foreach ($languages as $text => $lang) {
		if ($lang != $newloc && !isset($_POST['language_allow_' . $lang])) {
			$disallow[$lang] = $lang;
		}
	}
	if ($newloc != $oldloc) {
		$oldDisallow = getSerializedArray(getOption('locale_disallowed'));
		if (!empty($newloc) && isset($oldDisallow[$newloc])) {
			$notify = '?local_failed=' . $newloc;
		} else {
			zp_clearCookie('dynamic_locale'); // clear the language cookie
			$result = i18nSetLocale($newloc);
			if (!empty($newloc) && ($result === false)) {
				$notify = '?local_failed=' . $newloc;
			}
			setOption('locale', $newloc);
		}
	}
	setOption('locale_disallowed', serialize($disallow));

	setOption('mod_rewrite', (int) isset($_POST['mod_rewrite']));
	setOption('mod_rewrite_image_suffix', sanitize($_POST['mod_rewrite_image_suffix'], 3));
	setOption('unique_image_prefix', (int) isset($_POST['unique_image_prefix']));
	if (isset($_POST['time_zone'])) {
		setOption('time_zone', sanitize($_POST['time_zone'], 3));
		$offset = 0;
	} else {
		$offset = sanitize($_POST['time_offset'], 3);
	}
	setOption('time_offset', $offset);

	if (($new = sanitize($_POST['filesystem_charset'])) != FILESYSTEM_CHARSET) {
		$_configMutex->lock();
		$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
		$zp_cfg = updateConfigItem('FILESYSTEM_CHARSET', $new, $zp_cfg);
		storeConfig($zp_cfg);
		$_configMutex->unlock();
	}

	setOption('site_email', sanitize($_POST['site_email']), 3);
	$_zp_gallery->setGallerySession((int) isset($_POST['album_session']));
	$_zp_gallery->save();
	if (isset($_POST['zenphoto_cookie_path'])) {
		$p = sanitize($_POST['zenphoto_cookie_path']);
		if (empty($p)) {
			zp_clearCookie('zenphoto_cookie_path');
		} else {
			$p = '/' . trim($p, '/') . '/';
			if ($p == '//') {
				$p = '/';
			}
			//	save a cookie to see if change works
			$returntab .= '&cookiepath';
			zp_setCookie('zenphoto_cookie_path', $p, 600);
		}
		setOption('zenphoto_cookie_path', $p);
		if (isset($_POST['cookie_persistence'])) {
			setOption('cookie_persistence', sanitize_numeric($_POST['cookie_persistence']));
		}
	}

	setOption('site_email_name', process_language_string_save('site_email_name', 3));
	setOption('users_per_page', sanitize_numeric($_POST['users_per_page']));
	setOption('dirtyform_enable', sanitize_numeric($_POST['dirtyform_enable']));
	setOption('plugins_per_page', sanitize_numeric($_POST['plugins_per_page']));
	if (isset($_POST['articles_per_page'])) {
		setOption('articles_per_page', sanitize_numeric($_POST['articles_per_page']));
	}
	setOption('multi_lingual', (int) isset($_POST['multi_lingual']));
	$f = sanitize($_POST['date_format_list'], 3);
	if ($f == 'custom')
		$f = sanitize($_POST['date_format'], 3);
	setOption('date_format', $f);
	setOption('UTF8_image_URI', (int) !isset($_POST['UTF8_image_URI']));
	foreach ($_POST as $key => $value) {
		if (preg_match('/^log_size.*_(.*)$/', $key, $matches)) {
			setOption($matches[1] . '_log_size', $value);
			setOption($matches[1] . '_log_mail', (int) isset($_POST['log_mail_' . $matches[1]]));
		}
	}

	return array($returntab, $notify, NULL, NULL, NULL);
}

function getOptionContent() {
	global $_zp_gallery, $_zp_server_timezone, $_zp_UTF8, $_zp_authority;
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var oldselect = '<?php echo SITE_LOCALE; ?>';
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

		// ]]> -->
	</script>
	<div id="tab_gallery" class="tabbox">
		<?php
		if (isset($_GET['local_failed'])) {
			$languages = array_flip(generateLanguageList('all'));
			$locale = sanitize($_GET['local_failed']);
			echo '<div class="errorbox">';
			echo "<h2>" .
			sprintf(gettext("<em>%s</em> is not available."), html_encode($languages[$locale])) .
			' ' . sprintf(gettext("The locale %s is not supported on your server."), html_encode($locale)) .
			"</h2>";
			echo gettext('You can use the <em>debug</em> plugin to see which locales your server supports.');
			echo '</div>';
		}
		?>
		<form class="dirtylistening" onReset="setClean('form_options');" id="form_options" action="?action=saveoptions" method="post" autocomplete="off" >
			<?php XSRFToken('saveoptions'); ?>
			<input	type="hidden" name="saveoptions" value="general" />
			<table>
				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('Apply') ?>">
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
				<tr>
					<?php
					if (function_exists('date_default_timezone_get')) {
						$offset = timezoneDiff($_zp_server_timezone, $tz = getOption('time_zone'));
						setOption('time_offset', $offset);
						?>
						<td class="option_name"><?php echo gettext("Time zone"); ?></td>
						<td class="option_value">
							<?php
							$zones = getTimezones();
							?>
							<select id="time_zone" name="time_zone">
								<option value="" style="background-color:LightGray"><?php echo gettext('*not specified'); ?></option>
								<?php generateListFromArray(array($tz), $zones, false, false); ?>
							</select>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<p><?php printf(gettext('Your server reports its time zone as: <code>%s</code>.'), $_zp_server_timezone); ?></p>
									<p><?php printf(ngettext('Your time zone offset is %d hour. If your time zone is different from the servers, select the correct time zone here.', 'Your time zone offset is: %d hours. If your time zone is different from the servers, select the correct time zone here.', $offset), $offset); ?></p>
								</div>
							</span>
						</td>
						<?php
					} else {
						$offset = getOption('time_offset');
						?>
						<td class="option_name"><?php echo gettext("Time offset (hours)"); ?></td>
						<td class="option_value">
							<input type="text" size="3" name="time_offset" value="<?php echo html_encode($offset); ?>" />
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<p><?php echo gettext("If you are in a different time zone from your server, set the offset in hours of your time zone from that of the server. For instance if your server is on the US East Coast (<em>GMT</em> - 5) and you are on the Pacific Coast (<em>GMT</em> - 8), set the offset to 3 (-5 - (-8))."); ?></p>
								</div>
							</span>
						</td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("URL options"); ?></td>
					<td class="option_value">
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
						<?php
						if (FILESYSTEM_CHARSET != LOCAL_CHARSET) {
							?>
							<p>
								<label>
									<input type="checkbox" name="UTF8_image_URI" value="1"<?php checked('0', UTF8_IMAGE_URI); ?> />
									<?php echo gettext('<em>filesystem</em> image URIs'); ?>
								</label>
							</p>
							<?php
						}
						?>
						<p><?php echo gettext("mod_rewrite suffix"); ?> <input type="text" size="10" name="mod_rewrite_image_suffix" value="<?php echo html_encode(getOption('mod_rewrite_image_suffix')); ?>" /></p>
						<p>
							<label>
								<input type="checkbox" name="unique_image_prefix"<?php
								if (!MOD_REWRITE || !IM_SUFFIX)
									echo ' disabled="disabled"';
								if (UNIQUE_IMAGE)
									echo ' checked="checked";'
									?>><?php echo gettext("unique images"); ?>
							</label>
						</p>

					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<p>
									<?php
									echo gettext("If you have Apache <em>mod rewrite</em> (or equivalent), put a checkmark on the <em>mod rewrite</em> option and you will get nice cruft-free URLs.");
									echo sprintf(gettext('The <em>tokens</em> used in rewritten URIs may be altered to your taste. See the <a href="%s">plugin options</a> for <code>rewriteTokens</code>.'), WEBPATH . '/' . ZENFOLDER . '/admin-options.php?page=options&tab=plugin&single=rewriteTokens');
									if (!getOption('mod_rewrite_detected'))
										echo '<p class="notebox">' . gettext('Setup did not detect a working <em>mod_rewrite</em> facility.'), '</p>';
									?>
								</p>
								<?php
								if (FILESYSTEM_CHARSET != LOCAL_CHARSET) {
									echo '<p>' . gettext("If you are having problems with images whose names contain characters with diacritical marks try changing the <em>image URI</em> setting.");
									switch (getOption('UTF8_image_URI_found')) {
										case'unknown':
											echo '<p class="notebox">' . gettext('Setup could not determine a setting that allowed images with diacritical marks in the name.'), '</p>';
											break;
										case 'internal':
											if (!getOption('UTF8_image_URI')) {
												echo '<p class="notebox">' . sprintf(gettext('Setup detected <em>%s</em> image URIs.'), LOCAL_CHARSET), '</p>';
											}
											break;
										case 'filesystem':
											if (getOption('UTF8_image_URI')) {
												echo '<p class="notebox">' . gettext('Setup detected <em>file system</em> image URIs.'), '</p>';
											}
											break;
									}
									echo '</p>';
								}
								?>
								<p><?php echo gettext("If <em>mod_rewrite</em> is checked above, zenphoto will append the <em>mod_rewrite suffix</em> to the end of image URLs. (This helps search engines.) Examples: <em>.html, .php</em>, etc."); ?></p>
								<p>
									<?php
									printf(gettext('If <em>Unique images</em> is checked, image links will omit the image suffix. E.g. a link to the image page for <code>myalbum/myphoto.jpg</code> will appear as <code>myalbum/myphoto%s</code>'), IM_SUFFIX);
									echo '<p class="notebox">';
									echo gettext('<strong>Note:</strong> This option requires <em>mod rewrite</em> and <em>mod_rewrite suffix</em> both be set and the image prefixes must be unique within an album!');
									echo '</p>';
									?>
								</p>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Language"); ?></td>
					<td class="option_value">
						<br />
						<ul class="languagelist">
							<?php
							$unsupported = getSerializedArray(getOption('locale_unsupported'));
							$disallow = getSerializedArray(getOption('locale_disallowed'));
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
								if (empty($dirname)) {
									$languageP = '';
								} else if ($dirname == 'en_US') {
									$languageP = ' <small>[100%]</small>';
								} else {
									$stat = explode("\n", file_get_contents(SERVERPATH . "/" . ZENFOLDER . "/locale/" . $dirname . '/LC_MESSAGES/statistics.txt'));
									preg_match_all('~([\d]+)~', $stat[1], $matches);
									$translated = $matches[0][1];
									preg_match_all('~([\d]+)~', $stat[2], $matches);
									$needswork = $matches[0][1];
									$languageP = ' <span style="font-size:xx-small;">' . ($translated + $needswork) . '%</span>';
									if ($needswork) {
										$languageP .= ' <span style="font-size:xx-small;color: red;">[' . $needswork . '%]</span>';
									}
								}

								if (empty($dirname)) {
									$flag = WEBPATH . '/' . ZENFOLDER . '/locale/auto.png';
								} else {
									$flag = getLanguageFlag($dirname);
								}
								if (isset($unsupported[$dirname])) {
									$c_attrs = $r_attrs = ' disabled="disabled"';
								} else {
									if (isset($disallow[$dirname])) {
										$c_attrs = '';
										$r_attrs = ' disabled="disabled"';
									} else {
										$c_attrs = ' checked="checked"';
										$r_attrs = '';
									}
								}

								if ($dirname == SITE_LOCALE) {
									$r_attrs = ' checked="checked"';
									$c_attrs = ' checked="checked" disabled="disabled"';
									?>
									<input type="hidden" name="language_allow_<?php echo $dirname; ?>" value="1" />
									<script type="text/javascript">
										window.addEventListener('load', function () {
											$('ul.languagelist').scrollTo('li:eq(<?php echo ($c - 2); ?>)');
										}, false);</script>
									<?php
								}
								$c++;
								?>
								<li>

									<label class="displayinline">
										<input type="radio" name="locale" id="r_<?php echo $dirname; ?>" value="<?php echo $dirname; ?>"
													 onclick="radio_click('<?php echo $dirname; ?>');" <?php echo $r_attrs; ?>/>
									</label>
									<label class="flags">
										<span class="displayinline">
											<input id="language_allow_<?php echo $dirname; ?>" name="language_allow_<?php echo $dirname; ?>" type="checkbox"
														 value="<?php echo $dirname; ?>"<?php echo $c_attrs; ?>
														 onclick="enable_click('<?php echo $dirname; ?>');" />
											<img src="<?php echo $flag; ?>" alt="<?php echo $languageAlt; ?>" width="24" height="16" />
											<?php echo $language; ?>
										</span>
										<?php echo $languageP; ?>
									</label>
								</li>
								<?php
							}
							?>
						</ul>
						<?php echo '<span class="floatright" style="font-size:xx-small;">' . gettext('Percent mechanically translated in red.'); ?></span>
						<br class="clearall">
						<label class="checkboxlabel">
							<input type="checkbox" name="multi_lingual" value="1"	<?php checked('1', getOption('multi_lingual')); ?> /><?php echo gettext('multi-lingual'); ?>
						</label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<p><?php echo gettext("You can disable languages by unchecking their checkboxes. Only checked languages will be available to the installation."); ?></p>
								<p><?php echo gettext("Select the preferred language to display text in. (Set to <em>HTTP_Accept_Language</em> to use the language preference specified by the viewer’s browser.)"); ?></p>
								<p><?php echo gettext("Set <em>Multi-lingual</em> to enable multiple language input for options that provide theme text."); ?></p>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Date format"); ?></td>
					<td class="option_value">
						<select id="date_format_list" name="date_format_list" onchange="showfield(this, 'customTextBox')">
							<?php
							$formatlist = array(gettext('Custom') => 'custom',
									gettext('Preferred date representation') => '%x',
									gettext('02/25/08 15:30') => '%d/%m/%y %H:%M',
									gettext('02/25/08') => '%d/%m/%y',
									gettext('02/25/2008 15:30') => '%d/%m/%Y %H:%M',
									gettext('02/25/2008') => '%d/%m/%Y',
									gettext('02-25-08 15:30') => '%d-%m-%y %H:%M',
									gettext('02-25-08') => '%d-%m-%y',
									gettext('02-25-2008 15:30') => '%d-%m-%Y %H:%M',
									gettext('02-25-2008') => '%d-%m-%Y',
									gettext('2008. February 25. 15:30') => '%Y. %B %d. %H:%M',
									gettext('2008. February 25.') => '%Y. %B %d.',
									gettext('2008-02-25 15:30') => '%Y-%m-%d %H:%M',
									gettext('2008-02-25') => '%Y-%m-%d',
									gettext('25 Feb 2008 15:30') => '%d %B %Y %H:%M',
									gettext('25 Feb 2008') => '%d %B %Y',
									gettext('25 February 2008 15:30') => '%d %B %Y %H:%M',
									gettext('25 February 2008') => '%d %B %Y',
									gettext('25. Feb 2008 15:30') => '%d. %B %Y %H:%M',
									gettext('25. Feb 2008') => '%d. %B %Y',
									gettext('25. Feb. 08 15:30') => '%d. %b %y %H:%M',
									gettext('25. Feb. 08') => '%d. %b %y',
									gettext('25. February 2008 15:30') => '%d. %B %Y %H:%M',
									gettext('25. February 2008') => '%d. %B %Y',
									gettext('25.02.08 15:30') => '%d.%m.%y %H:%M',
									gettext('25.02.08') => '%d.%m.%y',
									gettext('25.02.2008 15:30') => '%d.%m.%Y %H:%M',
									gettext('25.02.2008') => '%d.%m.%Y',
									gettext('25-02-08 15:30') => '%d-%m-%y %H:%M',
									gettext('25-02-08') => '%d-%m-%y',
									gettext('25-02-2008 15:30') => '%d-%m-%Y %H:%M',
									gettext('25-02-2008') => '%d-%m-%Y',
									gettext('25-Feb-08 15:30') => '%d-%b-%y %H:%M',
									gettext('25-Feb-08') => '%d-%b-%y',
									gettext('25-Feb-2008 15:30') => '%d-%b-%Y %H:%M',
									gettext('25-Feb-2008') => '%d-%b-%Y',
									gettext('Feb 25, 2008 15:30') => '%b %d, %Y %H:%M',
									gettext('Feb 25, 2008') => '%b %d, %Y',
									gettext('February 25, 2008 15:30') => '%B %d, %Y %H:%M',
									gettext('February 25, 2008') => '%B %d, %Y');
							$cv = DATE_FORMAT;
							$flip = array_flip($formatlist);
							if (isset($flip[$cv])) {
								$dsp = 'none';
							} else {
								$dsp = 'block';
							}
							if (array_search($cv, $formatlist) === false)
								$cv = 'custom';
							generateListFromArray(array($cv), $formatlist, false, true);
							?>
						</select>
						<div id="customTextBox" class="customText" style="display:<?php echo $dsp; ?>">
							<br />
							<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" name="date_format" value="<?php echo html_encode(DATE_FORMAT); ?>" />
						</div>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Format for dates. Select from the list or set to <code>custom</code> and provide a <a href="http://us2.php.net/manual/en/function.strftime.php"><span class="nowrap"><code>strftime()</code></span></a> format string in the text box.'); ?>
							</div>
						</span>
					</td>
				</tr>

				<tr>
					<td class="option_name"><?php echo gettext("Filesystem Charset"); ?></td>
					<td class="option_value">
						<select id="filesystem_charset" name="filesystem_charset">
							<?php
							foreach ($_zp_UTF8->charsets as $key => $char) {
								if ($key == FILESYSTEM_CHARSET) {
									$selected = ' selected="selected"';
								} else {
									$selected = '';
								}
								?>
								<option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $char; ?></option>
								<?php
							}
							?>
						</select>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('The character encoding to use for the filesystem.'); ?>
							</div>
						</span>
					</td>
				</tr>

				<tr>
					<td class="option_name"><?php echo gettext("Allowed tags"); ?></td>
					<td class="option_value">
						<p><textarea name="allowed_tags" id="allowed_tags" style="width: 340px" rows="10" cols="35"><?php echo html_encode(getOption('allowed_tags')); ?></textarea>
							<span class="buttons">
								<a onclick="resetallowedtags()" >
									<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
									<?php echo gettext('Reset to default'); ?>
								</a>
							</span>
						</p>
					</td>
					<td class="option_desc">
						<script type="text/javascript">
							// <!-- <![CDATA[
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
							// ]]> -->
						</script>
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<p><?php echo gettext("Tags and attributes allowed in comments, descriptions, and other fields."); ?></p>
								<p><?php echo gettext("Follow the form <em>tag</em> =&gt; (<em>attribute</em> =&gt; (<em>attribute</em> =&gt; (), <em>attribute</em> =&gt; ()...)))"); ?></p>
								<?php if (EDITOR_SANITIZE_LEVEL == 4) { ?>
									<p class="notebox"><?php echo gettext('<strong>Note:</strong> visual editing is enabled so the editor overrides these settings on tags where it is active.'); ?></p>
								<?php } ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name">
						<?php echo gettext("Cookies"); ?>
					</td>
					<td class="option_value">
						<?php
						if (!GALLERY_SESSION) {
							echo gettext('path');
							?>
							<input type="text" size="48" id="zenphoto_cookie_path" name="zenphoto_cookie_path"  value="<?php echo getOption('zenphoto_cookie_path'); ?>" />
							<p>
								<?php
								echo gettext('duration');
								?>
								<input type="text" name="cookie_persistence" value="<?php echo COOKIE_PESISTENCE; ?>" />
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
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php
								if (!GALLERY_SESSION) {
									?>
									<p><?php printf(gettext('The <em>path</em> to use when storing cookies. (Leave empty to default to <em>%s</em>)'), WEBPATH); ?></p>
									<p><?php echo gettext("Set to the time in seconds that cookies should be kept by browsers."); ?></p>
									<?php
								}
								?>
								<p><?php echo gettext('If the gallery sessions option is selected <a href="http://www.w3schools.com/php/php_sessions.asp">PHP sessions</a> will be used instead of cookies to make visitor settings persistent.'); ?></p>
								<p class="notebox"><?php echo gettext('<strong>NOTE</strong>: Sessions will normally close when the browser closes causing all password and other data to be discarded. They may close more frequently depending on the runtime configuration. Longer <em>lifetime</em> of sessions is generally more conducive to a pleasant user experience. Cookies are the prefered storage option since their duration is determined by the <em>Cookie duration</em> option. ') ?>
								</p>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name">
						<p><?php echo gettext("Name"); ?></p>
						<p><?php echo gettext("Email"); ?></p>
					</td>
					<td class="option_value">
						<p><input type="text" size="48" name="site_email_name" value="<?php echo get_language_string(getOption('site_email_name')) ?>" /></p>
						<p><input type="text" size="48" id="site_email" name="site_email"  value="<?php echo getOption('site_email'); ?>" /></p>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("This email name and address will be used as the <em>From</em> address for all mails sent by the gallery."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name">
						<p><?php echo gettext('Registration'); ?></p>
						<p><?php echo gettext('Text'); ?></p>
					</td>
					<td class="option_value">
						<?php
						$mailinglist = $_zp_authority->getAdminEmail(ADMIN_RIGHTS);
						?>
						<p><input type="checkbox" size="48" id="site_email" name="register_user_notify"  value="1" <?php checked('1', getOption('register_user_notify')); ?> <?php if (!$mailinglist) echo ' disabled="disabled"'; ?> /><?php echo gettext('notify'); ?></p>
						<p>
							<textarea name="register_user_text" cols="<?php echo TEXTAREA_COLUMNS; ?>" rows="6" ><?php echo get_language_string(getOption('register_user_text')); ?></textarea>
						</p>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php
								echo gettext('If checked, an e-mail will be sent to the gallery admin when a new user has registered on the site.');
								if (count($mailinglist) == 0) { //	no one to send the notice to!
									echo ' ' . gettext('Of course there must be some Administrator with an e-mail address for this option to make sense!');
								}
								?>
							</div>
						</span>
				</tr>
				<tr>
					<td class="option_name">
						<span style="line-height:190%">
							<?php echo gettext("Users per page"); ?><br />
							<?php echo gettext("Plugins per page"); ?><br />
							<?php
							if (extensionEnabled('zenpage')) {
								?>
								<?php echo gettext("Articles per page"); ?><br />
								<?php
							}
							?>
						</span>
					</td>
					<td class="option_value">
						<input type="text" size="5" id="users_per_page" name="users_per_page"  value="<?php echo getOption('users_per_page'); ?>" />
						<br />
						<input type="text" size="5" id="plugins_per_page" name="plugins_per_page"  value="<?php echo getOption('plugins_per_page'); ?>" />
						<?php
						if (extensionEnabled('zenpage')) {
							?>
							<br />
							<input type="text" size="5" id="articles_per_page" name="articles_per_page"  value="<?php echo getOption('articles_per_page'); ?>" />
							<?php
						}
						?>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('These options control the number of items displayed on their tabs. If you have problems using these tabs, reduce the number shown here.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name">
						<?php echo gettext('Dirty form check'); ?>
					</td>
					<td class="option_value">
						<label>
							<input type="radio" name="dirtyform_enable" value="0"<?php checked('0', getOption('dirtyform_enable')); ?> />
							<?php echo gettext("ignore"); ?>
						</label>
						<label>
							<input type="radio" name="dirtyform_enable" value="1"<?php checked('1', getOption('dirtyform_enable')); ?> />
							<?php echo gettext("exclude tinyMCE"); ?>
						</label>
						<label>
							<input type="radio" name="dirtyform_enable" value="2"<?php checked('2', getOption('dirtyform_enable')); ?> />
							<?php echo gettext("detect all changes"); ?>
						</label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("Enable checking for form changes before leaving pages."); ?>
							</div>
						</span>
					</td>
				</tr>
				<?php
				$subtabs = array('security' => gettext('security'), 'debug' => gettext('debug'));
				?>
				<tr>
					<td class="option_name">
						<?php
						foreach ($subtabs as $subtab => $log) {
							if (!is_null(getOption($subtab . '_log_size'))) {
								printf(gettext('<p>%s log limit</p>'), ucfirst($log));
							}
						}
						?>
					</td>
					<td class="option_value">
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
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Logs will be "rolled" over when they exceed the specified size. If checked, the administrator will be e-mailed when this occurs.') ?>
							</div>
						</span>
					</td>
				</tr>
				<?php zp_apply_filter('admin_general_data'); ?>
				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('save') ?>"><?php echo CHECKMARK_GREEN; ?>
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<button type="reset" value="<?php echo gettext('reset') ?>">
								<?php echo CROSS_MARK_RED; ?>
								<strong><?php echo gettext("Reset"); ?></strong>
							</button>
						</p>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<!-- end of tab-general div -->
	<?php
}
