<?php
/*
 * Guts of the gallery options tab
 */

$optionRights = OPTIONS_RIGHTS;

function saveOptions() {
	global $_zp_gallery;

	$notify = $returntab = NULL;
	$_zp_gallery->setAlbumPublish((int) isset($_POST['album_default']));
	$_zp_gallery->setImagePublish((int) isset($_POST['image_default']));

	setOption('AlbumThumbSelect', sanitize_numeric($_POST['thumbselector']));
	$_zp_gallery->setThumbSelectImages((int) isset($_POST['thumb_select_images']));
	$_zp_gallery->setSecondLevelThumbs((int) isset($_POST['multilevel_thumb_select_images']));
	$_zp_gallery->setTitle(process_language_string_save('gallery_title', 2));
	$_zp_gallery->setDesc(process_language_string_save('Gallery_description', EDITOR_SANITIZE_LEVEL));
	$_zp_gallery->setWebsiteTitle(process_language_string_save('website_title', 2));
	$web = sanitize($_POST['website_url'], 3);
	$_zp_gallery->setWebsiteURL($web);
	$_zp_gallery->setAlbumUseImagedate((int) isset($_POST['album_use_new_image_date']));
	$st = strtolower(sanitize($_POST['gallery_sorttype'], 3));
	if ($st == 'custom')
		$st = strtolower(sanitize($_POST['customalbumsort'], 3));
	$_zp_gallery->setSortType($st);
	if (($st == 'manual') || ($st == 'random')) {
		$_zp_gallery->setSortDirection(false);
	} else {
		$_zp_gallery->setSortDirection(isset($_POST['gallery_sortdirection']));
	}
	foreach ($_POST as $item => $value) {
		if (strpos($item, 'gallery-page_') === 0) {
			$encoded = substr($item, 13);
			$item = sanitize(postIndexDecode($encoded));
			$_zp_gallery->setUnprotectedPage($item, (int) isset($_POST['gallery_page_unprotected_' . $encoded]));
		}
	}
	$_zp_gallery->setSecurity(sanitize($_POST['gallery_security'], 3));
	$notify = processCredentials($_zp_gallery);
	if (zp_loggedin(CODEBLOCK_RIGHTS)) {
		processCodeblockSave(0, $_zp_gallery);
	}
	$_zp_gallery->save();
	$returntab = "&tab=gallery";

	return array($returntab, $notify, NULL, NULL, NULL);
}

function getOptionContent() {
	global $_zp_gallery, $_zp_albumthumb_selector, $_zp_sortby;

	codeblocktabsJS();
	?>
	<div id="tab_gallery" class="tabbox">
		<form class="dirtylistening" onReset="toggle_passwords('', false);
					setClean('form_options');" id="form_options" action="?action=saveoptions" method="post" autocomplete="off" >
					<?php XSRFToken('saveoptions'); ?>
			<input	type="hidden" name="saveoptions" value="gallery" />
			<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
			<table>
				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('Apply') ?>"><?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong></button>
							<button type="reset" value="<?php echo gettext('reset') ?>">
								<?php echo CROSS_MARK_RED; ?>
								<strong><?php echo gettext("Reset"); ?></strong>
							</button>
						</p>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Gallery title"); ?></td>
					<td class="option_value">
						<?php print_language_string_list($_zp_gallery->getTitle('all'), 'gallery_title', false, null, '', '100%'); ?>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("What you want to call your site."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Gallery description"); ?></td>
					<td class="option_value">
						<?php print_language_string_list($_zp_gallery->getDesc('all'), 'Gallery_description', true, NULL, 'texteditor', '100%'); ?>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("A brief description of your gallery. Some themes may display this text."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext('Gallery type'); ?></td>
					<td class="option_value">
						<label><input type="radio" name="gallery_security" value="public" alt="<?php echo gettext('public'); ?>"<?php if (GALLERY_SECURITY == 'public') echo ' checked="checked"' ?> onclick="$('.public_gallery').show();" /><?php echo gettext('public'); ?></label>
						<label><input type="radio" name="gallery_security" value="private" alt="<?php echo gettext('private'); ?>"<?php if (GALLERY_SECURITY != 'public') echo 'checked="checked"' ?> onclick="$('.public_gallery').hide();" /><?php echo gettext('private'); ?></label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Private galleries are viewable only by registered users.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<?php
				if (GALLERY_SECURITY == 'public') {
					?>
					<tr class="passwordextrashow public_gallery">
						<td class="option_name">
							<a onclick="toggle_passwords('', true);">
								<?php echo gettext("Gallery password"); ?>
							</a>
						</td>
						<td class="option_value">
							<?php
							$x = $_zp_gallery->getPassword();
							if (empty($x)) {
								?>
								<?php echo LOCK_OPEN; ?>
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
									<p>
										<?php echo gettext("Master password for the gallery. Click on <em>Gallery password</em> to change."); ?>
									</p>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none">
						<td class="option_name">
							<a onclick="toggle_passwords('', false);">
								<?php echo gettext("Gallery guest user"); ?>
							</a>
						</td>
						<td class="option_value">
							<input type="text"
										 class="passignore ignoredirty" autocomplete="off"
										 size="<?php echo TEXT_INPUT_SIZE; ?>"
										 onkeydown="passwordClear('');"
										 id="user_name"  name="user"
										 value="<?php echo html_encode($_zp_gallery->getUser()); ?>" />
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext("User ID for the gallery guest user") ?>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none" >
						<td class="option_name">
							<span id="strength">
								<?php echo gettext("Gallery password"); ?>
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
									<?php echo gettext("Master password for the gallery. If this is set, visitors must know this password to view the gallery."); ?>
								</div>
							</span>
						</td>
					</tr>
					<tr class="passwordextrahide" style="display:none" >
						<td class="option_name">
							<?php echo gettext("Gallery password hint"); ?>
						</td>
						<td class="option_value">
							<?php print_language_string_list($_zp_gallery->getPasswordHint('all'), 'hint', false, NULL, 'hint'); ?>
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
					<td class="option_name"><?php echo gettext('Unprotected pages'); ?></td>
					<td class="option_value">
						<?php
						$curdir = getcwd();
						$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $_zp_gallery->getCurrentTheme() . '/';
						chdir($root);
						$filelist = safe_glob('*.php');
						$list = array();
						foreach ($filelist as $file) {
							$file = filesystemToInternal($file);
							$list[$file] = str_replace('.php', '', $file);
						}
						chdir($curdir);
						$list = array_diff($list, standardScripts());
						$list['index.php'] = 'index';
						$current = array();
						foreach ($list as $page) {
							?>
							<input type="hidden" name="gallery-page_<?php echo postIndexEncode($page); ?>" value="0" />
							<?php
							if ($_zp_gallery->isUnprotectedPage($page)) {
								$current[] = $page;
							}
						}
						?>
						<ul class="shortchecklist">
							<?php generateUnorderedListFromArray($current, $list, 'gallery_page_unprotected_', false, true, true); ?>
						</ul>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Place a checkmark on any page scripts which should not be protected by the gallery password.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Website title"); ?></td>
					<td class="option_value">
						<?php print_language_string_list($_zp_gallery->getWebsiteTitle('all'), 'website_title', false, null, '', '100%'); ?>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("Your web site title."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Website url"); ?></td>
					<td class="option_value"><input type="text" name="website_url" style="width:100%;"
																					value="<?php echo html_encode($_zp_gallery->getWebsiteURL()); ?>" /></td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("This is used to link back to your main site, but your theme must support it."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Album thumbnails"); ?></td>
					<td class="option_value">
						<?php
						$selections = array();
						foreach ($_zp_albumthumb_selector as $key => $selection) {
							$selections[$selection['desc']] = $key;
						}
						?>
						<select id="thumbselector" name="thumbselector">
							<?php
							generateListFromArray(array(getOption('AlbumThumbSelect')), $selections, false, true);
							?>
						</select>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext("Default thumbnail selection for albums."); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Sort gallery by"); ?></td>
					<td class="option_value">
						<?php
						$sort = $_zp_sortby;
						$sort[gettext('Manual')] = 'manual';
						$sort[gettext('Custom')] = 'custom';
						/*
						 * not recommended--screws with peoples minds during pagination!

						  $sort[gettext('Random')] = 'random';
						 */
						$cvt = $cv = strtolower($_zp_gallery->getSortType());
						ksort($sort, SORT_LOCALE_STRING);
						$flip = array_flip($sort);
						if (isset($flip[$cv])) {
							$dspc = 'none';
						} else {
							$dspc = 'block';
						}
						if (($cv == 'manual') || ($cv == 'random') || ($cv == '')) {
							$dspd = 'none';
						} else {
							$dspd = 'block';
						}
						?>
						<table>
							<tr>
								<td>
									<select id="gallerysortselect" name="gallery_sorttype" onchange="update_direction(this, 'gallery_sortdirection', 'customTextBox2')">
										<?php
										if (array_search($cv, $sort) === false)
											$cv = 'custom';
										generateListFromArray(array($cv), $sort, false, true);
										?>
									</select>
								</td>
								<td>
									<span id="gallery_sortdirection" style="display:<?php echo $dspd; ?>">
										<label>
											<input type="checkbox" name="gallery_sortdirection"	value="1" <?php checked('1', $_zp_gallery->getSortDirection()); ?> />
											<?php echo gettext("descending"); ?>
										</label>
									</span>
								</td>
							</tr>
							<tr>
								<td colspan="100%">
									<span id="customTextBox2" class="customText" style="display:<?php echo $dspc; ?>">
										<?php echo gettext('custom fields') ?>
										<span class="tagSuggestContainer">
											<input id="customalbumsort" name="customalbumsort" type="text" value="<?php echo html_encode($cvt); ?>" />
										</span>
									</span>
								</td>
							</tr>
						</table>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php
								echo gettext('Sort order for the albums on the index of the gallery. Custom sort values must be database field names. You can have multiple fields separated by commas. This option is also the default sort for albums and subalbums.');
								?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Gallery behavior"); ?></td>
					<td class="option_value">
						<label>
							<input type="checkbox" name="album_default"	value="1"<?php if ($_zp_gallery->getAlbumPublish()) echo ' checked="checked"'; ?> />
							<?php echo gettext("publish albums by default"); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="image_default"	value="1"<?php if ($_zp_gallery->getImagePublish()) echo ' checked="checked"'; ?> />
							<?php echo gettext("publish images by default"); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="album_use_new_image_date" id="album_use_new_image_date"
										 value="1" <?php checked('1', $_zp_gallery->getAlbumUseImagedate()); ?> />
										 <?php echo gettext("use latest image date as album date"); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="thumb_select_images" id="thumb_select_images"
										 value="1" <?php checked('1', $_zp_gallery->getThumbSelectImages()); ?> />
										 <?php echo gettext("visual thumb selection"); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="multilevel_thumb_select_images" id="thumb_select_images"
										 value="1" <?php checked('1', $_zp_gallery->getSecondLevelThumbs()); ?> />
										 <?php echo gettext("show subalbum thumbs"); ?>
						</label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">

								<p><?php echo gettext("<em>publish albums by default</em> sets the default behavior for when an album is discovered. If checked, the album will be published, if unchecked it will be unpublished.") ?></p>

								<p><?php echo gettext("<em>publish images by default</em> sets the default behavior for when an image is discovered. If checked, the image will be published, if unchecked it will be unpublished.") ?></p>

								<p>
									<?php echo gettext("If you wish your album date to reflect the date of the latest image uploaded set <em>use latest image date as album date</em>. Otherwise the date will be set initially to the date the album was created.") ?>
								</p>
								<p class="notebox">
									<?php echo gettext('<strong>NOTE</strong>: The album date will be updated only if an image is discovered which is newer than the current date of the album.'); ?>
								</p>

								<p><?php echo gettext("Setting <em>visual thumb selection</em> places thumbnails in the album thumbnail selection list (the dropdown list on each album’s edit page). In Firefox the dropdown shows the thumbs, but in IE and Safari only the names are displayed (even if the thumbs are loaded!). In albums with many images loading these thumbs takes much time and is unnecessary when the browser will not display them. Uncheck this option and the images will not be loaded. "); ?></p>

								<p><?php echo gettext("Setting <em>subalbum thumb selection</em> allows selecting images from subalbums as well as from the album. Naturally populating these images adds overhead. If your album edit tabs load too slowly, do not select this option."); ?></p>

							</div>
						</span>
					</td>
				</tr>

				<tr valign="top">
					<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks"); ?></td>
					<td>
						<?php printCodeblockEdit($_zp_gallery, 0); ?>
					</td>
					<td>
					</td>
				</tr>

				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('Apply') ?>"><?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong></button>
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
	<!-- end of tab-gallery div -->
	<?php
}
