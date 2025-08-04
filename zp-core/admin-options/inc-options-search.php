<div id="tab_search" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savesearchoptions" value="yes" />
							<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									?>
									<tr class="passwordextrashow">
										<td width="175">
											<p>
												<a href="javascript:toggle_passwords('',true);">
													<?php echo gettext("Search password:"); ?>
												</a>
											</p>
										</td>
										<td>
											<?php
											$x = getOption('search_password');
											if (empty($x)) {
												?>
												<img src="images/lock_open.png" />
												<?php
											} else {
												$x = '          ';
												?>
												<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>"><img src="images/lock.png" /></a>
												<?php
											}
											?>
										</td>
										<td>
											<p><?php echo gettext("Password for the search guest user. click on <em>Search password</em> to change."); ?></p>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none">
										<td width="175">
											<a href="javascript:toggle_passwords('',false);">
												<?php echo gettext("Search guest user:"); ?>
											</a>
										</td>
										<td>
											<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 onkeydown="passwordClear('');"
														 id="user_name" name="user"
														 value="<?php echo html_encode(getOption('search_user')); ?>" autocomplete="off" />
											<br />

										</td>
										<td>
											<?php echo gettext("User ID for the search guest user") ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<span id="strength">
												<?php echo gettext("Search password:"); ?>
											</span>
											<br />
											<span id="match" class="password_field_">
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext("(repeat)"); ?>
											</span>
										</td>
										<td>
											<?php
											// Autofill honeypot hack (hidden password input),
											// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
											// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
											?>
											<input class="dirtyignore" type="password" name="pass" style="display:none;" />
											<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 id="pass" name="pass"
														 onkeydown="passwordClear('');"
														 onkeyup="passwordStrength('');"
														 value="<?php echo $x; ?>" autocomplete="off" />
											<br />
											<span class="password_field_">
												<input type="password" size="<?php echo TEXT_INPUT_SIZE; ?>"
															 class="dirtyignore"
															 id="pass_r" name="pass_r" disabled="disabled"
															 onkeydown="passwordClear('');"
															 onkeyup="passwordMatch('');"
															 value="<?php echo $x; ?>" autocomplete="off" />
											</span>
											<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear(''); togglePassword('');" /><?php echo gettext('Show password'); ?></label>
										</td>
										<td>
											<?php echo gettext("Password for the search guest user. If this is set, visitors must know this password to view search results."); ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<?php echo gettext("Search password hint:"); ?>
										</td>
										<td>
											<?php print_language_string_list(getOption('search_hint'), 'hint', false, NULL, 'hint'); ?>
										</td>
										<td>
											<?php echo gettext("A reminder hint for the password."); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td><?php echo gettext("Search behavior settings:"); ?></td>
									<?php
									$engine = new SearchEngine();
									$fields = $engine->getSearchFieldList();
									$set_fields = $engine->allowedSearchFields();
									$fields = array_diff($fields, $set_fields);
									?>
								<td>
									<?php echo gettext('Fields list:'); ?>
									<div id="resizable">
										<ul class="searchchecklist" id="searchchecklist">
											<?php
											generateUnorderedListFromArray($set_fields, $set_fields, 'SEARCH_', false, true, true, 'search_fields');
											generateUnorderedListFromArray(array(), $fields, 'SEARCH_', false, true, true, 'search_fields');
											?>
										</ul>
										<div class="floatright">
											<label id="autocheck">
												<input type="checkbox" name="checkAllAuto" id="checkAllAuto" onclick="$('.search_fields').prop('checked', checked);" />
												<span id="autotext"><?php echo gettext('all'); ?></span>
											</label>
										</div>
									</div>
									<br />
									<p>
										<?php
										echo gettext('String matching:');
										generateRadiobuttonsFromArray((int) getOption('exact_string_match'), array(gettext('<em>pattern</em>') => 0, gettext('<em>partial word</em>') => 1, gettext('<em>word</em>') => 2), 'string_match', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Tag matching:');
										generateRadiobuttonsFromArray((int) getOption('exact_tag_match'), array(gettext('<em>partial</em>') => 0, gettext('<em>word</em>') => 2, gettext('<em>exact</em>') => 1), 'tag_match', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Treat spaces as');
										generateRadiobuttonsFromArray(getOption('search_space_is'), array(gettext('<em>space</em>') => '', gettext('<em>OR</em>') => 'OR', gettext('<em>AND</em>') => 'AND'), 'search_space_is', false, false);
										?>
									</p>
									<p>
										<?php
										echo gettext('Default search');
										generateRadiobuttonsFromArray(getOption('search_within'), array(gettext('<em>New</em>') => '0', gettext('<em>Within</em>') => '1'), 'search_within', false, false);
										?>
									</p>
									<p>
										<label>
											<input type="checkbox" name="search_no_albums" value="1" <?php checked('1', getOption('search_no_albums')); ?> />
											<?php echo gettext('Do not return <em>album</em> matches') ?>
										</label>
									</p>
									<p>
										<label>
											<input type="checkbox" name="search_no_images" value="1" <?php checked('1', getOption('search_no_images')); ?> />
											<?php echo gettext('Do not return <em>image</em> matches') ?>
										</label>
									</p>
									<?php
									if (extensionEnabled('zenpage')) {
										?>
										<p>
											<label>
												<input type="checkbox" name="search_no_news" value="1" <?php checked('1', getOption('search_no_news')); ?> />
												<?php echo gettext('Do not return <em>news</em> matches') ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="search_no_pages" value="1" <?php checked('1', getOption('search_no_pages')); ?> />
												<?php echo gettext('Do not return <em>page</em> matches') ?>
											</label>
										</p>
										<?php
									}
									?>
								</td>
								<td>
									<p><?php echo gettext("<em>Field list</em> is the set of fields on which searches may be performed."); ?></p>
									<p>
										<?php
										echo gettext("Search does matches on all fields chosen based on the matching criteria selected. The <em>string matching</em> criteria is used for all fields except <em>tags</em>. The <em>tag matching</em> criteria is used for them.");
										?>
									<ul>
										<li><?php echo gettext('<code>pattern</code>: match the target anywhere within the field'); ?></li>
										<li><?php echo gettext('<code>exact</code>: match the target with the whole field'); ?></li>
										<li><?php echo gettext('<code>word</code>: match the target with whole words in the field'); ?></li>
										<li><?php echo gettext('<code>partial word</code>: match the target with the start of words in the field'); ?></li>
									</ul>
									</p>
									<p><?php echo gettext('Setting <code>Treat spaces as</code> to <em>OR</em> will cause search to trigger on any of the words in a string separated by spaces. Setting it to <em>AND</em> will cause the search to trigger only when all strings are present. Leaving the option unchecked will treat the whole string as a search target.') ?></p>
									<p><?php echo gettext('<code>Default search</code> sets how searches from search page results behave. The search will either be from <em>within</em> the results of the previous search or will be a fresh <em>new</em> search.') ?></p>
									<p><?php echo gettext('Setting <code>Do not return <em>{item}</em> matches</code> will cause search to ignore <em>{items}</em> when looking for matches.') ?></p>
								</td>

								<tr>
									<td><?php echo gettext('Search fields selector'); ?></td>
									<td>
										<p>
											<label>
												<input type="checkbox" name="search_fieldsselector_enabled" value="1" <?php checked('1', getOption('search_fieldsselector_enabled')); ?> />
												<?php echo gettext('Enable selector') ?>
											</label>
										</p>
									</td>
									<td>
										<?php echo gettext('If enabled the search form will feature a selector for all currently enabled search fields above.'); ?>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Cache expiry'); ?></td>
									<td>
										<?php printf(gettext('Redo search after %s minutes.'), '<input type="textbox" size="4" name="search_cache_duration" value="' . getOption('search_cache_duration') . '" />'); ?>
									</td>
									<td>
										<?php echo gettext('Search will remember the results of particular searches so that it can quickly serve multiple pages, etc. Over time this remembered result can become obsolete, so it should be refreshed. This option lets you decide how long before a search will be considered obsolete and thus re-executed. Setting the option to <em>zero</em> disables caching of searches.'); ?>
									</td>
								</tr>

								<tr>
									<td class="leftcolumn"><?php echo gettext("Sort albums by"); ?> </td>
									<td colspan="2">
										<span class="nowrap">
											<select id="album_sort_select" name="search_album_sort_type" onchange="update_direction(this, 'album_direction_div', 'album_custom_div');">
												<?php
												$sort = getSortByOptions('albums-search');
												$cvt = $type = strtolower(getOption('search_album_sort_type'));
												if ($type && !in_array($type, $sort)) {
													$cv = array('custom');
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												} else {
													$cv = array($type);
												}
												generateListFromArray($cv, $sort, false, true);
												?>
											</select>
											<?php
											if (($type == 'random') || ($type == '')) {
												$dsp = 'none';
											} else {
												$dsp = 'inline';
											}
											?>
											<label id="album_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
												<?php echo gettext("Descending"); ?>
												<input type="checkbox" name="search_album_sort_direction" value="1"
												<?php
												if (getOption('search_album_sort_direction')) {
													echo "CHECKED";
												};
												?> />
											</label>
										</span>
										<?php
										$flip = array_flip($sort);
										if (empty($type) || isset($flip[$type])) {
											$dsp = 'none';
										} else {
											$dsp = 'block';
										}
										?>

									</td>

								</tr>

								<tr>
									<td class="leftcolumn"><?php echo gettext("Sort images by"); ?> </td>
									<td colspan="2">
										<span class="nowrap">
											<select id="image_sort_select" name="search_image_sort_type" onchange="update_direction(this, 'image_direction_div', 'image_custom_div')">
												<?php
												$sort = getSortByOptions('images-search');
												$cvt = $type = strtolower(getOption('search_image_sort_type'));
												if ($type && !in_array($type, $sort)) {
													$cv = array('custom');
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												} else {
													$cv = array($type);
												}
												generateListFromArray($cv, $sort, false, true);
												?>
											</select>
											<?php
											if (($type == 'random') || ($type == '')) {
												$dsp = 'none';
											} else {
												$dsp = 'inline';
											}
											?>
											<label id="image_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
												<?php echo gettext("Descending"); ?>
												<input type="checkbox" name="search_image_sort_direction" value="1"
												<?php
												if (getOption('search_image_sort_direction')) {
													echo ' checked="checked"';
												}
												?> />
											</label>
										</span>
										<?php
										$flip = array_flip($sort);
										if (empty($type) || isset($flip[$type])) {
											$dsp = 'none';
										} else {
											$dsp = 'block';
										}
										?>

									</td>
								</tr>
								<?php
								if (ZP_NEWS_ENABLED) {
								?>
									<tr>
										<td class="leftcolumn"><?php echo gettext("Sort news articles by"); ?> </td>
										<td colspan="2">
											<span class="nowrap">
												<select id="newsarticle_sort_select" name="search_newsarticle_sort_type" onchange="update_direction(this, 'newsarticle_direction_div', 'newsarticle_custom_div')">
													<?php
													$zenpage_sort_news = getSortByOptions('news');
													$cvt = $type = strtolower(getOption('search_newsarticle_sort_type'));
													if ($type && !in_array($type, $zenpage_sort_news)) {
														$cv = array('custom');
														$zenpage_sort_news[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
													} else {
														$cv = array($type);
													}
													generateListFromArray($cv, $zenpage_sort_news, false, true);
													?>
												</select>
												<?php
												if (($type == 'random') || ($type == '')) {
													$dsp = 'none';
												} else {
													$dsp = 'inline';
												}
												?>
												<label id="newsarticle_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
													<?php echo gettext("Descending"); ?>
													<input type="checkbox" name="search_newsarticle_sort_direction" value="1"
													<?php
													if (getOption('search_newsarticle_sort_direction')) {
														echo ' checked="checked"';
													}
													?> />
												</label>
											</span>
											<?php
											$flip = array_flip($zenpage_sort_news);
											if (empty($type) || isset($flip[$type])) {
												$dsp = 'none';
											} else {
												$dsp = 'block';
											}
											?>

										</td>
									</tr>
								<?php
								}
								if (ZP_PAGES_ENABLED) {
									$zenpage_sort_pages = getSortByOptions('pages-search');
								?>
									<tr>
										<td class="leftcolumn"><?php echo gettext("Sort pages by"); ?> </td>
										<td colspan="2">
											<span class="nowrap">
												<select id="page_sort_select" name="search_page_sort_type" onchange="update_direction(this, 'page_direction_div', 'page_custom_div')">
													<?php
													$cvt = $type = strtolower(getOption('search_page_sort_type'));
													if ($type && !in_array($type, $zenpage_sort_pages)) {
														$cv = array('custom');
														$zenpage_sort_pages[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
													} else {
														$cv = array($type);
													}
													generateListFromArray($cv, $zenpage_sort_pages, false, true);
													?>
												</select>
												<?php
												if (($type == 'random') || ($type == '')) {
													$dsp = 'none';
												} else {
													$dsp = 'inline';
												}
												?>
												<label id="page_direction_div" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
													<?php echo gettext("Descending"); ?>
													<input type="checkbox" name="search_page_sort_direction" value="1"
													<?php
													if (getOption('search_page_sort_direction')) {
														echo ' checked="checked"';
													}
													?> />
												</label>
											</span>
											<?php
											$flip = array_flip($zenpage_sort_pages);
											if (empty($type) || isset($flip[$type])) {
												$dsp = 'none';
											} else {
												$dsp = 'block';
											}
											?>

										</td>
									</tr>
								<?php } ?>

								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table>
						</form>
					</div>