<?php
/*
 * Guts of the search options tab
 */
$optionRights = OPTIONS_RIGHTS;

function saveOptions() {
	global $_zp_gallery;

	$notify = $returntab = NULL;
	$search = new SearchEngine();
	if (isset($_POST['SEARCH_list'])) {
		$searchfields = sanitize($_POST['SEARCH_list']);
	} else {
		$searchfields = array();
	}
	natcasesort($searchfields);
	setOption('search_fields', implode(',', $searchfields));
	setOption('search_cache_duration', sanitize_numeric($_POST['search_cache_duration']));
	$notify = processCredentials('search');
	setOption('exact_tag_match', sanitize($_POST['tag_match']));
	setOption('exact_string_match', sanitize($_POST['string_match']));
	setOption('search_space_is', sanitize($_POST['search_space_is']));
	setOption('search_no_albums', (int) isset($_POST['search_no_albums']));
	setOption('search_no_images', (int) isset($_POST['search_no_images']));
	setOption('search_no_pages', (int) isset($_POST['search_no_pages']));
	setOption('search_no_news', (int) isset($_POST['search_no_news']));
	setOption('search_within', (int) ($_POST['search_within'] && true));
	setOption('languageTagSearch', sanitize_numeric($_POST['languageTagSearch']));
	$sorttype = strtolower(sanitize($_POST['sortby'], 3));
	if ($sorttype == 'custom') {
		$sorttype = unquote(strtolower(sanitize($_POST['customimagesort'], 3)));
	}
	setOption('search_image_sort_type', $sorttype);
	if ($sorttype == 'random') {
		setOption('search_image_sort_direction', 0);
	} else {
		if (empty($sorttype)) {
			$direction = 0;
		} else {
			$direction = isset($_POST['image_sortdirection']);
		}
		setOption('search_image_sort_direction', $direction);
	}
	$sorttype = strtolower(sanitize($_POST['subalbumsortby'], 3));
	if ($sorttype == 'custom')
		$sorttype = strtolower(sanitize($_POST['customalbumsort'], 3));
	setOption('search_album_sort_type', $sorttype);
	if ($sorttype == 'random') {
		setOption('search_album_sort_direction', 0);
	} else {
		setOption('search_album_sort_direction', isset($_POST['album_sortdirection']));
	}
	$returntab = "&tab=search";

	return array($returntab, $notify, NULL, NULL, NULL);
}

function getOptionContent() {
	global $_zp_gallery, $_zp_sortby;
	?>
	<div id="tab_search" class="tabbox">
		<form class="dirtylistening" onReset="toggle_passwords('', false);
				setClean('form_options');" id="form_options" action="?action=saveoptions" method="post" autocomplete="off" >
					<?php XSRFToken('saveoptions'); ?>
			<input	type="hidden" name="saveoptions" value="search" />
			<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
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
				<?php
				if (GALLERY_SECURITY == 'public') {
					?>
					<tr class="passwordextrashow">
						<td class="option_name">
							<a onclick="toggle_passwords('', true);">
								<?php echo gettext("Search password"); ?>
							</a>
						</td>
						<td class="option_value">
							<?php
							$x = getOption('search_password');
							if (empty($x)) {
								?>
								<a onclick="toggle_passwords('', true);">
									<?php echo LOCK_OPEN; ?>
								</a>
								<?php
							} else {
								$x = '          ';
								?>
								<a onclick="resetPass('');" title="<?php echo gettext('clear password'); ?>">
									<?php echo LOCK; ?>
								</a>
								<?php
							}
							?>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">

									<p><?php echo gettext("Password for the search guest user. click on <em>Search password</em> to change."); ?></p>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none">
						<td class="option_name">
							<a onclick="toggle_passwords('', false);">
								<?php echo gettext("Search guest user"); ?>
							</a>
						</td>
						<td class="option_value">
							<input type="text"
										 class="passignore ignoredirty" autocomplete="off"
										 size="<?php echo TEXT_INPUT_SIZE; ?>"
										 onkeydown="passwordClear('');"
										 id="user_name"  name="user"
										 value="<?php echo html_encode(getOption('search_user')); ?>" />
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext("User ID for the search guest user") ?>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none" >
						<td class="option_name">
							<span id="strength">
								<?php echo gettext("Search password"); ?>
							</span>
							<br />
							<span id="match" class="password_field_">
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo gettext("(repeat)"); ?>
							</span>
						</td>
						<td class="option_value">
							<input type="password"
										 class="passignore ignoredirty" autocomplete="off"
										 size="<?php echo TEXT_INPUT_SIZE; ?>"
										 id="pass" name="pass"
										 onkeydown="passwordClear('');"
										 onkeyup="passwordStrength('');"
										 value="<?php echo $x; ?>" />
							<label>
								<input type="checkbox"
											 name="disclose_password"
											 id="disclose_password"
											 onclick="passwordClear('');
													 togglePassword('');" /><?php echo gettext('Show'); ?>
							</label>
							<br />
							<span class="password_field_">
								<input type="password"
											 class="passignore ignoredirty" autocomplete="off"
											 size="<?php echo TEXT_INPUT_SIZE; ?>"
											 id="pass_r" name="pass_r" disabled="disabled"
											 onkeydown="passwordClear('');"
											 onkeyup="passwordMatch('');"
											 value="<?php echo $x; ?>" />
							</span>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">

									<?php echo gettext("Password for the search guest user. If this is set, visitors must know this password to view search results."); ?>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none" >
						<td class="option_name">
							<?php echo gettext("Search password hint"); ?>
						</td>
						<td class="option_value">
							<?php print_language_string_list(getOption('search_hint'), 'hint', false, NULL, 'hint', '100%'); ?>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext("A reminder hint for the password."); ?>
								</div>
							</span>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td class="option_name"><?php echo gettext("Search behavior settings"); ?></td>
					<?php
					$engine = new SearchEngine();
					$fields = $engine->getSearchFieldList();
					$set_fields = $engine->allowedSearchFields();
					$fields = array_diff($fields, $set_fields);
					?>
				<script type="text/javascript">
					$(function () {
						$("#resizable").resizable({
							minHeight: 120,
							resize: function (event, ui) {
								$(this).css("width", '');
								$('#searchchecklist').height($('#resizable').height());
							}
						});
					});
				</script>
				<td class="option_value">
					<?php echo gettext('fields list'); ?>
					<div id="resizable">
						<ul class="searchchecklist" id="searchchecklist">
							<?php
							generateUnorderedListFromArray($set_fields, $set_fields, 'SEARCH_', false, true, true, 'search_fields', NULL, true);
							generateUnorderedListFromArray(array(), $fields, 'SEARCH_', false, true, true, 'search_fields', NULL, true);
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
						echo gettext('string matching');
						generateRadiobuttonsFromArray((int) getOption('exact_string_match'), array(gettext('<em>pattern</em>') => 0, gettext('<em>partial word</em>') => 1, gettext('<em>word</em>') => 2), 'string_match', 'string_match', false, false);
						?>
					</p>
					<p>
						<?php
						echo gettext('tag matching');
						generateRadiobuttonsFromArray((int) getOption('exact_tag_match'), array(gettext('<em>partial</em>') => 0, gettext('<em>word</em>') => 2, gettext('<em>exact</em>') => 1), 'tag_match', 'tag_match', false, false);
						?>
					</p>
					<p>
						<?php echo gettext('language specific tags'); ?>
						<label>
							<input type="radio" name="languageTagSearch"  value="" <?php if (getOption('languageTagSearch') == 1) echo ' checked="checked"'; ?> /><?php echo gettext('off'); ?>
						</label>
						<label>
							<input type="radio" name="languageTagSearch"  value="1" <?php if (getOption('languageTagSearch') == 1) echo ' checked="checked"'; ?> /><?php echo gettext('generic'); ?>
						</label>
						<label>
							<input type="radio" name="languageTagSearch"  value="2" <?php if (getOption('languageTagSearch') == 2) echo ' checked="checked"'; ?> /><?php echo gettext('specific'); ?>
						</label>
					</p>
					<p>
						<?php
						echo gettext('treat spaces as');
						generateRadiobuttonsFromArray(getOption('search_space_is'), array(gettext('<em>space</em>') => '', gettext('<em>OR</em>') => 'OR', gettext('<em>AND</em>') => 'AND'), 'search_space_is', 'search_space_is', false, false);
						?>
					</p>
					<p>
						<?php
						echo gettext('default search');


						generateRadiobuttonsFromArray(getOption('search_within'), array(gettext('<em>New</em>') => '0', gettext('<em>Within</em>') => '1'), 'search_within', 'search_within', false, false);
						?>
					</p>
					<p>
						<label>
							<input type="checkbox" name="search_no_albums" value="1" <?php checked('1', getOption('search_no_albums')); ?> />
							<?php echo gettext('do not return <em>album</em> matches') ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" name="search_no_images" value="1" <?php checked('1', getOption('search_no_images')); ?> />
							<?php echo gettext('do not return <em>image</em> matches') ?>
						</label>
					</p>
					<?php
					if (extensionEnabled('zenpage')) {
						?>
						<p>
							<label>
								<input type="checkbox" name="search_no_news" value="1" <?php checked('1', getOption('search_no_news')); ?> />
								<?php echo gettext('do not return <em>news</em> matches') ?>
							</label>
						</p>
						<p>
							<label>
								<input type="checkbox" name="search_no_pages" value="1" <?php checked('1', getOption('search_no_pages')); ?> />
								<?php echo gettext('do not return <em>page</em> matches') ?>
							</label>
						</p>
						<?php
					}
					?>
				</td>
				<td class="option_desc">
					<span class="option_info">
						<?php echo INFORMATION_BLUE; ?>
						<div class="option_desc_hidden">
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
							<p><?php echo gettext('If <code>Language specific tags</code> is set, searches will ignore tags assigned to languages other than the viewer’s locale. Selecting <code>specific</code> requires an exact match of the tag locale to the viewer locale. Otherwise generic matching is used, e.g. if the user is in the local <em>en_UK</em> he will view tags marked <em>en_US</em>.'); ?></p>
							<p><?php echo gettext('Setting <code>Treat spaces as</code> to <em>OR</em> will cause search to trigger on any of the words in a string separated by spaces. Setting it to <em>AND</em> will cause the search to trigger only when all strings are present. Leaving the option unchecked will treat the whole string as a search target.') ?></p>
							<p><?php echo gettext('<code>Default search</code> sets how searches from search page results behave. The search will either be from <em>within</em> the results of the previous search or will be a fresh <em>new</em> search.') ?></p>
							<p><?php echo gettext('Setting <code>do not return <em>{item}</em> matches</code> will cause search to ignore <em>{items}</em> when looking for matches.') ?></p>
						</div>
					</span>
				</td>
				<tr>
					<td class="option_name"><?php echo gettext('Cache expiry'); ?></td>
					<td class="option_value">
						<?php printf(gettext('redo search after %s minutes.'), '<input type="textbox" size="4" name="search_cache_duration" value="' . getOption('search_cache_duration') . '" />'); ?>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Search will remember the results of particular searches so that it can quickly serve multiple pages, etc. Over time this remembered result can become obsolete, so it should be refreshed. This option lets you decide how long before a search will be considered obsolete and thus re-executed. Setting the option to <em>zero</em> disables caching of searches.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<?php
				$sort = $_zp_sortby;
				$sort[gettext('Custom')] = 'custom';
				?>
				<tr>
					<td class="option_name"><?php echo gettext("Sort albums by"); ?> </td>
					<td colspan="100%">
						<span class="nowrap">
							<select id="albumsortselect" name="subalbumsortby" onchange="update_direction(this, 'album_direction_div', 'album_custom_div');">
								<?php
								$cvt = $type = strtolower(getOption('search_album_sort_type'));
								if ($type && !in_array($type, $sort)) {
									$cv = array('custom');
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
								<?php echo gettext("descending"); ?>
								<input type="checkbox" name="album_sortdirection" value="1"<?php
								if (getOption('search_album_sort_direction')) {
									echo ' checked="checked"';
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
						<span id="album_custom_div" class="customText" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
							<br />
							<?php echo gettext('custom fields') ?>
							<span class="tagSuggestContainer">
								<input id="customalbumsort" class="customalbumsort" name="customalbumsort" type="text" value="<?php echo html_encode($cvt); ?>" />
							</span>
						</span>
					</td>

				</tr>

				<tr>
					<td class="option_name"><?php echo gettext("Sort images by"); ?> </td>
					<td colspan="100%">
						<span class="nowrap">
							<select id="imagesortselect" name="sortby" onchange="update_direction(this, 'image_direction_div', 'image_custom_div')">
								<?php
								$cvt = $type = strtolower(getOption('search_image_sort_type'));
								if ($type && !in_array($type, $sort)) {
									$cv = array('custom');
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
								<?php echo gettext("descending"); ?>
								<input type="checkbox" name="image_sortdirection" value="1"
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
						<span id="image_custom_div" class="customText" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
							<br />
							<?php echo gettext('custom fields') ?>
							<span class="tagSuggestContainer">
								<input id="customimagesort" class="customimagesort" name="customimagesort" type="text" value="<?php echo html_encode($cvt); ?>" />
							</span>
						</span>
					</td>

				</tr>
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
			</table>
		</form>
	</div>
	<!-- end of tab-search div -->
	<?php
}
