<?php 
/**
 * Album related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */

/**
	 * emits the html for editing album information
	 * called in edit album and mass edit
	 * @param string $index the index of the entry in mass edit or '0' if single album
	 * @param object $album the album object
	 * @param bool $buttons set true for "apply" buttons
	 * @since 1.1.3
	 */
	function printAlbumEditForm($index, $album, $buttons = true) {
		global $_zp_gallery, $_zp_admin_mcr_albumlist, $_zp_albumthumb_selector, $_zp_current_admin_obj;
		$isPrimaryAlbum = '';
		if (!zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
			$myalbum = $_zp_current_admin_obj->getAlbum();
			if ($myalbum && $album->getID() == $myalbum->getID()) {
				$isPrimaryAlbum = ' disabled="disabled"';
			}
		}
		$tagsort = getTagOrder();
		if ($index == 0) {
			$suffix = $prefix = '';
		} else {
			$prefix = "$index-";
			$suffix = "_$index";
			echo "<p><em><strong>" . $album->name . "</strong></em></p>";
		}
		?>
		<input type="hidden" name="<?php echo $prefix; ?>folder" value="<?php echo $album->name; ?>" />
		<input type="hidden" name="tagsort" value="<?php echo html_encode($tagsort); ?>" />
		<input	type="hidden" name="password_enabled<?php echo $suffix; ?>" id="password_enabled<?php echo $suffix; ?>" value="0" />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<?php
				$parent = dirname($album->name);
				if ($parent == '/' || $parent == '.' || empty($parent)) {
					$parent = '';
				} else {
					$parent = '&amp;album=' . $parent . '&tab=subalbuminfo';
				}
				?>
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<img	src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="javascript:$('.deletemsg').hide();" >
					<img	src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>', true);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<?php if (!$album->isDynamic()) { ?>
							<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="javascript:newDynAlbum('<?php echo pathurlencode($album->name); ?>', false);">
								<img src="images/folder.png" alt="" />
								<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
							</button>
							<?php
						}
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getName())); ?>">
						<img src="images/view.png" alt="" />
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>
			</span>
			<?php
		}
		?>
		<br class="clearall" /><br />
		<table class="formlayout">
			<tr>
				<td valign="top">
					<table class="width100percent">
						<tr>
							<td class="leftcolumn"><?php echo gettext("Owner"); ?></td>
							<td class="middlecolumn">
								<?php
								if (zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<select name="<?php echo $prefix; ?>owner">
										<?php echo admin_album_list($album->getOwner()); ?>
									</select>
									<?php
								} else {
									echo $album->getOwner();
								}
								?>
							</td>
						</tr>
						<tr>
							<td class="leftcolumn">
								<?php echo gettext("Album Title"); ?>:
							</td>
							<td class="middlecolumn">
								<?php print_language_string_list($album->getTitle('all'), $prefix . "albumtitle", false, null, '', '100%'); ?>
							</td>
						</tr>

						<tr>
							<td class="leftcolumn">
								<?php echo gettext("Album Description:"); ?>
							</td>
							<td>
								<?php print_language_string_list($album->getDesc('all'), $prefix . "albumdesc", true, NULL, 'texteditor', '100%'); ?>
							</td>
						</tr>
						<?php
						if (GALLERY_SECURITY == 'public') {
							?>
							<tr class="password<?php echo $suffix; ?>extrashow">
								<td class="leftcolumn">
									<p>
										<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',true);">
											<?php echo gettext("Album password:"); ?>
										</a>
									</p>
								</td>
								<td class="middlecolumn">
									<p>
										<?php
										$x = $album->getPassword();
										if (empty($x)) {
											?>
											<img src="images/lock_open.png" />
											<?php
										} else {
											$x = '          ';
											?>
											<a onclick="resetPass('<?php echo $suffix; ?>');" title="<?php echo addslashes(gettext('clear password')); ?>"><img src="images/lock.png" /></a>
											<?php
										}
										?>
									</p>
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<p>
										<a href="javascript:toggle_passwords('<?php echo $suffix; ?>',false);">
											<?php echo gettext("Album guest user:"); ?>
										</a>
									</p>
								</td>
								<td>
									<p>
										<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
													 class="dirtyignore"  
													 onkeydown="passwordClear('<?php echo $suffix; ?>');"
													 id="user_name<?php echo $suffix; ?>" name="user<?php echo $suffix; ?>"
													 value="<?php echo $album->getUser(); ?>" autocomplete="off" />
									</p>
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td class="leftcolumn">
									<p>
										<span id="strength<?php echo $suffix; ?>"><?php echo gettext("Album password:"); ?></span>
									</p>
									<p>
										<span id="match<?php echo $suffix; ?>" class="password_field_<?php echo $suffix; ?>">
											<?php echo gettext("Repeat password:"); ?>
										</span>
									</p>
								</td>
								<td>
									<p> <?php
											// Autofill honeypot hack (hidden password input),
											// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
											// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
											?>
										<input class="dirtyignore" type="password" name="pass" style="display:none;" />
										<input type="password" 
													 class="dirtyignore" 
													 id="pass<?php echo $suffix; ?>" name="pass<?php echo $suffix; ?>"
													 onkeydown="passwordClearZ('<?php echo $suffix; ?>');"
													 onkeyup="passwordStrength('<?php echo $suffix; ?>');"
													 value="<?php echo $x; ?>" autocomplete="off" />
										<label><input class="dirtyignore" type="checkbox" name="disclose_password<?php echo $suffix; ?>"
																	id="disclose_password<?php echo $suffix; ?>"
																	onclick="passwordClear('<?php echo $suffix; ?>');
																					togglePassword('<?php echo $suffix; ?>');" /><?php echo addslashes(gettext('Show password')); ?></label>
										<br />
										<span class="password_field_<?php echo $suffix; ?>">
											<input class="dirtyignore" type="password"
														 id="pass_r<?php echo $suffix; ?>" name="pass_r<?php echo $suffix; ?>" disabled="disabled"
														 onkeydown="passwordClear('<?php echo $suffix; ?>');"
														 onkeyup="passwordMatch('<?php echo $suffix; ?>');"
														 value="<?php echo $x; ?>" autocomplete="off" />
										</span>
									</p>
								</td>
							</tr>
							<tr class="password<?php echo $suffix; ?>extrahide" style="display:none" >
								<td>
									<p>
										<?php echo gettext("Password hint:"); ?>
									</p>
								</td>
								<td>
									<p>
										<?php print_language_string_list($album->getPasswordHint('all'), "hint" . $suffix, false, NULL, 'hint', '100%'); ?>
									</p>
								</td>
							</tr>
							<?php
						}
						$d = $album->getDateTime();
						if ($d == "0000-00-00 00:00:00") {
							$d = "";
						}
						?>

						<tr>
							<td class="leftcolumn"><?php echo gettext("Date:"); ?> </td>
							<td>
								<script>
									$(function () {
										$("#datepicker<?php echo $suffix; ?>").datepicker({
											dateFormat: 'yy-mm-dd',
											showOn: 'button',
											buttonImage: 'images/calendar.png',
											buttonText: '<?php echo addslashes(gettext('calendar')); ?>',
											buttonImageOnly: true
										});
									});
								</script>
								<input type="text" id="datepicker<?php echo $suffix; ?>" size="20" name="<?php echo $prefix; ?>albumdate" value="<?php echo $d; ?>" />
							</td>
						</tr>
						<tr>
							<td class="leftcolumn"><?php echo gettext("Location:"); ?> </td>
							<td class="middlecolumn">
								<?php print_language_string_list($album->getLocation(), $prefix . "albumlocation", false, NULL, 'hint', '100%'); ?>
							</td>
						</tr>
						<?php
						$custom = zp_apply_filter('edit_album_custom_data', '', $album, $prefix);
						if (empty($custom)) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Custom data:"); ?></td>
								<td><?php print_language_string_list($album->getCustomData('all'), $prefix . "album_custom_data", true, NULL, 'texteditor_albumcustomdata', '100%'); ?></td>
							</tr>
							<?php
						} else {
							echo $custom;
						}
						?>
						<tr>
							<td class="leftcolumn"><?php echo gettext("Sort subalbums by:"); ?> </td>
							<td>
								<span class="nowrap">
									<select id="albumsortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>subalbumsortby" onchange="update_direction(this, 'album_direction_div<?php echo $suffix; ?>', 'album_custom_div<?php echo $suffix; ?>');">
										<?php
										if ($album->isDynamic()) {
											$sort = getSortByOptions('albums-dynamic');
										} else {
											$sort = getSortByOptions('albums');
										}
										if (is_null($album->getParent())) {
											$globalsort = gettext("*gallery album sort order");
										} else {
											$globalsort = gettext("*parent album subalbum sort order");
										}
										echo "\n<option value =''>$globalsort</option>";
										$cvt = $type = strtolower(strval($album->get('subalbum_sort_type')));
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
									if (($type == 'manual') || ($type == 'random') || ($type == '')) {
										$dsp = 'none';
									} else {
										$dsp = 'inline';
									}
									?>
									<label id="album_direction_div<?php echo $suffix; ?>" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
										<?php echo gettext("Descending"); ?>
										<input type="checkbox" name="<?php echo $prefix; ?>album_sortdirection" value="1" <?php
									if ($album->getSortDirection('album')) {
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
							<td>
								<span class="nowrap">
									<select id="imagesortselect<?php echo $prefix; ?>" name="<?php echo $prefix; ?>sortby" onchange="update_direction(this, 'image_direction_div<?php echo $suffix; ?>', 'image_custom_div<?php echo $suffix; ?>')">
										<?php
										$sort = getSortByOptions('images');
										if (is_null($album->getParent())) {
											$globalsort = gettext("*gallery image sort order");
										} else {
											$globalsort = gettext("*parent album image sort order");
										}
										?>
										<option value =""><?php echo $globalsort; ?></option>
										<?php
										$cvt = $type = strtolower(strval($album->get('sort_type')));
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
									if (($type == 'manual') || ($type == 'random') || ($type == '')) {
										$dsp = 'none';
									} else {
										$dsp = 'inline';
									}
									?>
									<label id="image_direction_div<?php echo $suffix; ?>" style="display:<?php echo $dsp; ?>;white-space:nowrap;">
										<?php echo gettext("Descending"); ?>
										<input type="checkbox" name="<?php echo $prefix; ?>image_sortdirection" value="1"
										<?php
										if ($album->getSortDirection('image')) {
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
						if (is_null($album->getParent())) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Album theme:"); ?> </td>
								<td>
									<select id="album_theme" class="album_theme" name="<?php echo $prefix; ?>album_theme"	<?php if (!zp_loggedin(THEMES_RIGHTS)) echo 'disabled="disabled" '; ?>	>
										<?php
										$themes = $_zp_gallery->getThemes();
										$oldtheme = $album->getAlbumTheme();
										if (empty($oldtheme)) {
											$selected = 'selected="selected"';
										} else {
											$selected = '';
										}
										?>
										<option value="" style="background-color:LightGray" <?php echo $selected; ?> ><?php echo gettext('*gallery theme'); ?></option>
										<?php
										foreach ($themes as $theme => $themeinfo) {
											if ($oldtheme == $theme) {
												$selected = 'selected="selected"';
											} else {
												$selected = '';
											}
											?>
											<option value = "<?php echo $theme; ?>" <?php echo $selected; ?> ><?php echo $themeinfo['name']; ?></option>
											<?php
										}
										?>
									</select>
								</td>
							</tr>
							<?php
						}
						if (!$album->isDynamic()) {
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Album watermarks:"); ?> </td>
								<td>
									<?php $current = $album->getWatermark(); ?>
									<select id="album_watermark<?php echo $suffix; ?>" name="<?php echo $prefix; ?>album_watermark">
										<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
										<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
										<?php
										$watermarks = getWatermarks();
										generateListFromArray(array($current), $watermarks, false, false);
										?>
									</select>
									<em><?php echo gettext('Images'); ?></em>
								</td>
							</tr>
							<tr>
								<td class="leftcolumn"></td>
								<td>
									<?php $current = $album->getWatermarkThumb(); ?>
									<select id="album_watermark_thumb<?php echo $suffix; ?>" name="<?php echo $prefix; ?>album_watermark_thumb">
										<option value="<?php echo NO_WATERMARK; ?>" <?php if ($current == NO_WATERMARK) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*no watermark'); ?></option>
										<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('*default'); ?></option>
										<?php
										$watermarks = getWatermarks();
										generateListFromArray(array($current), $watermarks, false, false);
										?>
									</select>
									<em><?php echo gettext('Thumbs'); ?></em>
								</td>
							</tr>
							<?php
						}
						if ($index == 0) { // suppress for mass-edit
							$showThumb = $_zp_gallery->getThumbSelectImages();
							$album->getAlbumThumbImage(); //	prime the thumbnail since we will get the field below
							$thumb = $album->get('thumb');
							$selections = array();
							$selected = array();
							foreach ($_zp_albumthumb_selector as $key => $selection) {
								$selections[$selection['desc']] = $key;
								if ($key == $thumb) {
									$selected[] = $key;
								}
							}
							?>
							<tr>
								<td class="leftcolumn"><?php echo gettext("Thumbnail:"); ?> </td>
								<td>
									<?php
									if ($showThumb) {
										?>
										<script>
											updateThumbPreview(document.getElementById('thumbselect'));
										</script>
										<?php
									}
									?>
									<select style="width:320px" <?php if ($showThumb) { ?>class="thumbselect" onchange="updateThumbPreview(this);" <?php } ?> name="<?php echo $prefix; ?>thumb">
										<?php
										generateListFromArray($selected, $selections, false, true);
										$imagelist = $album->getImages(0);
										$subalbums = $album->getAlbums(0);
										foreach ($subalbums as $folder) {
											$newalbum = AlbumBase::newAlbum($folder);
											if ($_zp_gallery->getSecondLevelThumbs()) {
												$images = $newalbum->getImages(0);
												foreach ($images as $filename) {
													if (is_array($filename)) {
														$imagelist[] = $filename;
													} else {
														$imagelist[] = '/' . $folder . '/' . $filename;
													}
												}
											} else {
												$t = $newalbum->getAlbumThumbImage();
												if (strtolower(get_class($t)) !== 'transientimage' && $t->exists) {
													$imagelist[] = '/' . $t->getAlbumName() . '/' . $t->filename;
												}
											}
										}

										if ($thumb && !is_numeric($thumb)) {
											// check for current thumb being in the list. If not, add it
											$target = $thumb;
											$targetA = array('folder' => dirname($thumb), 'filename' => basename($thumb));
											if (!in_array($target, $imagelist) && !in_array($targetA, $imagelist)) {
												array_unshift($imagelist, $target);
											}
										}
										if (!empty($imagelist)) {
											// there are some images to choose from
											foreach ($imagelist as $imagename) {
												if (is_array($imagename)) {
													$image = Image::newImage(NULL, $imagename);
													$imagename = '/' . $imagename['folder'] . '/' . $imagename['filename'];
													$filename = basename($imagename);
												} else {
													$albumname = trim(dirname($imagename), '/');
													if (empty($albumname) || $albumname == '.') {
														$thumbalbum = $album;
													} else {
														$thumbalbum = AlbumBase::newAlbum($albumname);
													}
													$filename = basename($imagename);
													$image = Image::newImage($thumbalbum, $filename);
												}
												$selected = ($imagename == $thumb);
												if (Gallery::validImage($filename) || !is_null($image->objectsThumb)) {
													echo "\n<option";
													if ($_zp_gallery->getThumbSelectImages()) {
														echo " class=\"thumboption\"";
														echo " style=\"background-image: url(" . html_encode(pathurlencode(getAdminThumb($image, 'large'))) . "); background-repeat: no-repeat;\"";
													}
													echo " value=\"" . $imagename . "\"";
													if ($selected) {
														echo " selected=\"selected\"";
													}
													echo ">" . $image->getTitle();
													if ($filename != $image->getTitle()) {
														echo " ($filename)";
													}
													echo "</option>";
												}
											}
										}
										?>
									</select>
								</td>
							</tr>
							<?php
						}
						?>
						<tr valign="top">
							<td class="leftcolumn topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
							<td>
								<br />
								<?php printCodeblockEdit($album, (int) $suffix); ?>
							</td>
						</tr>
					</table>
				</td>
				<td class="rightcolumn" valign="top">
					<h2 class="h2_bordered_edit"><?php echo gettext("General"); ?></h2>
					<div class="box-edit">
						<?php
						if ($album->hasPublishSchedule()) {
							$publishlabel = '<span class="scheduledate">' . gettext('Publishing scheduled') . '</span>';
						} else {
							$publishlabel = gettext("Published");
						}
						?>
						<label class="checkboxlabel">
							<input type="checkbox" name="<?php echo $prefix; ?>Published" value="1" <?php if ($album->get('show', false)) echo ' checked="checked"'; ?> />
							<?php echo $publishlabel; ?>
						</label>
						<?php if (extensionEnabled('comment_form')) { ?>
							<label class="checkboxlabel">
								<input type="checkbox" name="<?php echo $prefix . 'allowcomments'; ?>" value="1" <?php
							if ($album->getCommentsAllowed()) {
								echo ' checked="checked"';
							}
							?> />
											 <?php echo gettext("Comments enabled"); ?>
							</label>
							<?php
						}
						if (extensionEnabled('hitcounter')) {
							$hc = $album->get('hitcounter');
							if (empty($hc)) {
								$hc = '0';
							}
							?>
							<label class="checkboxlabel">
								<input type="checkbox" name="reset_hitcounter<?php echo $prefix; ?>"<?php if (!$hc) echo ' disabled="disabled"'; ?> />
								<?php echo sprintf(ngettext("Reset hit counter (%u hit)", "Reset hit counter (%u hits)", $hc), $hc); ?>
							</label>
							<?php
						}
						if (extensionEnabled('rating')) {
							$tv = $album->get('total_value');
							$tc = $album->get('total_votes');

							if ($tc > 0) {
								$hc = $tv / $tc;
								?>
								<label class="checkboxlabel">
									<input type="checkbox" id="reset_rating<?php echo $suffix; ?>" name="<?php echo $prefix; ?>reset_rating" value="1" />
									<?php printf(gettext('Reset rating (%u stars)'), $hc); ?>
								</label>
								<?php
							} else {
								?>
								<label class="checkboxlabel">
									<input type="checkbox" name="<?php echo $prefix; ?>reset_rating" value="1" disabled="disabled"/>
									<?php echo gettext('Reset rating (unrated)'); ?>
								</label>
								<?php
							}
						}
						$publishdate = $album->getPublishDate();
						$expirationdate = $album->getExpireDate();
						?>
						<script>
							$(function () {
								$("#<?php echo $prefix; ?>publishdate,#<?php echo $prefix; ?>expirationdate").datepicker({
									dateFormat: 'yy-mm-dd',
									showOn: 'button',
									buttonImage: '../zp-core/images/calendar.png',
									buttonText: '<?php echo addslashes(gettext("calendar")); ?>',
									buttonImageOnly: true
								});
								$('#<?php echo $prefix; ?>publishdate').change(function () {
									var today = new Date();
									var pub = $('#<?php echo $prefix; ?>publishdate').datepicker('getDate');
									if (pub.getTime() > today.getTime()) {
										$(".<?php echo $prefix; ?>scheduledpublishing").html('<br /><?php echo addslashes(gettext('Future publishing date.')); ?>');
									} else {
										$(".<?php echo $prefix; ?>scheduledpublishing").html('');
									}
								});
								$('#<?php echo $prefix; ?>expirationdate').change(function () {
									var today = new Date();
									var expiry = $('#<?php echo $prefix; ?>expirationdate').datepicker('getDate');
									if (expiry.getTime() > today.getTime()) {
										$(".<?php echo $prefix; ?>expire").html('');
									} else {
										$(".<?php echo $prefix; ?>expire").html('<br /><?php echo addslashes(gettext('Expired!')); ?>');
									}
								});
							});
						</script>
						<br class="clearall" />
						<hr />
						<p>
							<label for="<?php echo $prefix; ?>publishdate"><?php echo gettext('Publish date'); ?> <small>(YYYY-MM-DD)</small></label>
							<br /><input value="<?php echo $publishdate; ?>" type="text" size="20" maxlength="30" name="publishdate-<?php echo $prefix; ?>" id="<?php echo $prefix; ?>publishdate" />
							<strong class="scheduledpublishing-<?php echo $prefix; ?>">
								<?php
								if ($album->hasPublishSchedule()) {
									echo '<br><span class="scheduledate">' . gettext('Future publishing date.') . '</span>';
								}
								?>
							</strong>
							<br /><br />
							<label for="<?php echo $prefix; ?>expirationdate"><?php echo gettext('Expiration date'); ?> <small>(YYYY-MM-DD)</small></label>
							<br /><input value="<?php echo $expirationdate; ?>" type="text" size="20" maxlength="30" name="expirationdate-<?php echo $prefix; ?>" id="<?php echo $prefix; ?>expirationdate" />
							<strong class="<?php echo $prefix; ?>expire">
								<?php
								if ($album->hasExpiration()) {
									echo '<br><span class="expiredate">' . gettext('Expiration set') . '</span>';
								}
								if ($album->hasExpired()) {
									echo '<br><span class="expired">' . gettext('Expired!') . '</span>';
								}
								?>
							</strong>
						</p>
						<?php printLastChangeInfo($album); ?>
					</div>
					<!-- **************** Move/Copy/Rename ****************** -->
					<h2 class="h2_bordered_edit"><?php echo gettext("Utilities"); ?></h2>
					<div class="box-edit">

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>move" name="a-<?php echo $prefix; ?>MoveCopyRename" value="move"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'move');"<?php echo $isPrimaryAlbum; ?> />
										 <?php echo gettext("Move"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>copy" name="a-<?php echo $prefix; ?>MoveCopyRename" value="copy"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'copy');"/>
										 <?php echo gettext("Copy"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="a-<?php echo $prefix; ?>rename" name="a-<?php echo $prefix; ?>MoveCopyRename" value="rename"
										 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', 'rename');" <?php echo $isPrimaryAlbum; ?> />
										 <?php echo gettext("Rename Folder"); ?>
						</label>

						<label class="checkboxlabel">
							<input type="radio" id="Delete-<?php echo $prefix; ?>" name="a-<?php echo $prefix; ?>MoveCopyRename" value="delete"
							<?php
							if ($isPrimaryAlbum) {
								?>
											 disabled="disabled"
											 <?php
										 } else {
											 ?>
											 onclick="toggleAlbumMCR('<?php echo $prefix; ?>', '');
															 deleteConfirm('Delete-<?php echo $prefix; ?>', '<?php echo $prefix; ?>', deleteAlbum1);"
											 <?php
										 }
										 ?> />
										 <?php echo gettext("Delete album"); ?>
						</label>

						<br class="clearall" />
						<div class="deletemsg" id="deletemsg<?php echo $prefix; ?>"	style="padding-top: .5em; padding-left: .5em; color: red; display: none">
							<?php echo gettext('Album will be deleted when changes are applied.'); ?>
							<br class="clearall" />
							<p class="buttons">
								<a	href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>movecopydiv" style="padding-top: .5em; padding-left: .5em; display: none;">
							<?php echo gettext("to:"); ?>
							<select id="a-<?php echo $prefix; ?>albumselectmenu" name="a-<?php echo $prefix; ?>albumselect" onchange="">
								<?php
								$exclude = $album->name;
								if (count(explode('/', $exclude)) > 1 && zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS)) {
									?>
									<option value="" selected="selected">/</option>
									<?php
								}
								foreach ($_zp_admin_mcr_albumlist as $fullfolder => $albumtitle) {
									// don't allow copy in place or to subalbums
									if ($fullfolder == dirname($exclude) || $fullfolder == $exclude || strpos($fullfolder, $exclude . '/') === 0) {
										$disabled = ' disabled="disabled"';
									} else {
										$disabled = '';
									}
									// Get rid of the slashes in the subalbum, while also making a subalbum prefix for the menu.
									$singlefolder = $fullfolder;
									$saprefix = '';
									while (strstr($singlefolder, '/') !== false) {
										$singlefolder = substr(strstr($singlefolder, '/'), 1);
										$saprefix = "&nbsp; &nbsp;&nbsp;" . $saprefix;
									}
									echo '<option value="' . $fullfolder . '"' . "$disabled>" . $saprefix . $singlefolder . "</option>\n";
								}
								?>
							</select>
							<br class="clearall" /><br />
							<p class="buttons">
								<a href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<div id="a-<?php echo $prefix; ?>renamediv" style="padding-top: .5em; padding-left: .5em; display: none;">
							<?php echo gettext("to:"); ?>
							<input name="a-<?php echo $prefix; ?>renameto" type="text" value="<?php echo basename($album->name); ?>"/><br />
							<br class="clearall" />
							<p class="buttons">
								<a href="javascript:toggleAlbumMCR('<?php echo $prefix; ?>', '');"><img src="images/reset.png" alt="" /><?php echo addslashes(gettext("Cancel")); ?></a>
							</p>
						</div>
						<span class="clearall" ></span>
						<?php
						echo zp_apply_filter('edit_album_utilities', '', $album, $prefix);
						printAlbumButtons($album);
						?>
						<span class="clearall" ></span>
					</div>
					<h2 class="h2_bordered_edit"><?php echo gettext("Tags"); ?></h2>
					<div class="box-edit-unpadded">
						<?php
						$tagsort = getTagOrder();
						tagSelector($album, 'tags_' . $prefix, false, $tagsort, true, true);
						?>
					</div>
				</td>
			</tr>
		</table>
		<?php
		if ($album->isDynamic()) {
			?>
			<table>
				<tr>
					<td align="left" valign="top" width="150"><?php echo gettext("Dynamic album search:"); ?></td>
					<td>
						<table class="noinput">
							<tr>
								<td><?php echo html_encode(urldecode($album->getSearchParams())); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<?php
		}
		?>

		<br class="clearall" />
		<?php
		if ($buttons) {
			?>
			<span class="buttons">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?page=edit' . $parent; ?>">
					<img	src="images/arrow_left_blue_round.png" alt="" />
					<strong><?php echo gettext("Back"); ?></strong>
				</a>
				<button type="submit">
					<img	src="images/pass.png" alt="" />
					<strong><?php echo gettext("Apply"); ?></strong>
				</button>
				<button type="reset" onclick="javascript:$('.deletemsg').hide();">
					<img	src="images/fail.png" alt="" />
					<strong><?php echo gettext("Reset"); ?></strong>
				</button>
				<div class="floatright">
					<?php
					if (!$album->isDynamic()) {
						?>
						<button type="button" title="<?php echo addslashes(gettext('New subalbum')); ?>" onclick="javascript:newAlbum('<?php echo pathurlencode($album->name); ?>', true);">
							<img src="images/folder.png" alt="" />
							<strong><?php echo gettext('New subalbum'); ?></strong>
						</button>
						<?php if (!$album->isDynamic()) { ?>
							<button type="button" title="<?php echo addslashes(gettext('New dynamic subalbum')); ?>" onclick="javascript:newDynAlbum('<?php echo pathurlencode($album->name); ?>', false);">
								<img src="images/folder.png" alt="" />
								<strong><?php echo gettext('New dynamic subalbum'); ?></strong>
							</button>
							<?php
						}
					}
					?>
					<a href="<?php echo WEBPATH . "/index.php?album=" . html_encode(pathurlencode($album->getName())); ?>">
						<img src="images/view.png" alt="" />
						<strong><?php echo gettext('View Album'); ?></strong>
					</a>
				</div>
			</span>
			<?php
		}
		?>
		<br class="clearall" />
		<?php
	}

	/**
	 * puts out the maintenance buttons for an album
	 *
	 * @param object $album is the album being emitted
	 */
	function printAlbumButtons($album) {
		if ($imagcount = $album->getNumImages() > 0) {
			if (!$album->isDynamic()) {
				?>
				<div class="button buttons tooltip" title="<?php echo addslashes(gettext("Clears the album’s cached images.")); ?>">
					<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=clear_cache&amp;album=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('clear_cache'); ?>">
						<img src="images/edit-delete.png" /><?php echo gettext('Clear album image cache'); ?></a>
					<br class="clearall" />
				</div>
			<?php } ?>
			<div class="button buttons tooltip" title="<?php echo gettext("Resets album’s hit counters."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-edit.php?action=reset_hitcounters&amp;album=' . html_encode($album->name) . '&amp;albumid=' . $album->getID(); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter'); ?>">
					<img src="images/reset.png" /><?php echo gettext('Reset album hit counters'); ?></a>
				<br class="clearall" />
			</div>
			<?php
		}
		if ($imagcount || (!$album->isDynamic() && $album->getNumAlbums())) {
			?>
			<div class="button buttons tooltip" title="<?php echo gettext("Refreshes the metadata for the album."); ?>">
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/admin-refresh-metadata.php?album=' . html_encode($album->name) . '&amp;return=' . html_encode($album->name); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh'); ?>" class="js_confirm_metadata_refresh_<?php echo $album->getID(); ?>">
					<img src="images/cache.png" /><?php echo gettext('Refresh album metadata'); ?></a>
				<br class="clearall" />
			</div>
			<script>
				$( document ).ready(function() {
					var element = '.js_confirm_metadata_refresh_<?php echo $album->getID(); ?>';
					var message = '<?php echo js_encode(gettext('Refreshing metadata will overwrite existing data. This cannot be undone!')); ?>';
					confirmClick(element, message);
				});
			</script>
			<?php
		}
	}

	function printAlbumLegend() {
		?>
		<ul class="iconlegend-l">
			<li><img src="images/folder_picture.png" alt="" /><?php echo gettext("Albums"); ?></li>
			<li><img src="images/pictures.png" alt="" /><?php echo gettext("Images"); ?></li>
			<li><img src="images/folder_picture_dn.png" alt="" /><?php echo gettext("Albums (dynamic)"); ?></li>
			<li><img src="images/pictures_dn.png" alt="I" /><?php echo gettext("Images (dynamic)"); ?></li>
		</ul>
		<ul class="iconlegend">
			<?php
			if (GALLERY_SECURITY == 'public') {
				?>
				<li><?php echo getStatusIcon('protected') . getStatusIcon('protected_by_parent').  gettext("Password protected/Password protected by parent"); ?></li>
				<?php
			}
			?>
			<li><?php echo getStatusIcon('published') . getStatusIcon('unpublished') . getStatusIcon('unpublished_by_parent'); ?><?php echo gettext("Published/Unpublished/Unpublished by parent"); ?></li>
			<li><?php echo getStatusIcon('publishschedule') . getStatusIcon('expiration') . getStatusIcon('expired'); ?><?php echo gettext("Scheduled publishing/Scheduled expiration/Expired"); ?></li>
			<li><img src="images/comments-on.png" alt="" /><img src="images/comments-off.png" alt="" /><?php echo gettext("Comments on/off"); ?></li>
			<li><img src="images/view.png" alt="" /><?php echo gettext("View the album"); ?></li>
			<li><img src="images/refresh.png" alt="" /><?php echo gettext("Refresh metadata"); ?></li>
			<?php
			if (extensionEnabled('hitcounter')) {
				?>
				<li><img src="images/reset.png" alt="" /><?php echo gettext("Reset hit counters"); ?></li>
				<?php
			}
			?>
			<li><img src="images/fail.png" alt="" /><?php echo gettext("Delete"); ?></li>
		</ul>
		<?php
	}

	/**
	 * puts out a row in the edit album table
	 *
	 * @param object $album is the album being emitted
	 * @param bool $show_thumb set to false to show thumb standin image rather than album thumb
	 * @param object $owner the parent album (or NULL for gallery)
	 *
	 * */
	function printAlbumEditRow($album, $show_thumb, $owner) {
		global $_zp_current_admin_obj;
		$enableEdit = $album->albumSubRights() & MANAGED_OBJECT_RIGHTS_EDIT;
		if (is_object($owner)) {
			$owner = $owner->name;
		}
		?>
		<div class='page-list_row'>

			<div class="page-list_albumthumb">
				<?php
				if ($enableEdit) {
					?>
					<a href="?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
						<?php
					}
					if ($show_thumb) {
						$thumbimage = $album->getAlbumThumbImage();
						printAdminThumb($thumbimage, 'small', '', '', gettext('Album thumb'));
					} else {
						?>
						<img src="images/thumb_standin.png" width="40" height="40" alt="" title="<?php echo gettext('Album thumb'); ?>" loading="lazy" />
						<?php
					}
					if ($enableEdit) {
						?>
					</a>
					<?php
				}
				?>
			</div>
			<div class="page-list_albumtitle">
				<?php
				if ($enableEdit) {
					?>
					<a href="?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo sprintf(gettext('Edit this album: %s'), $album->name); ?>">
						<?php
					}
					echo getBare($album->getTitle());
					if ($enableEdit) {
						?>
					</a>
					<?php
				}
				?>
			</div>
			<?php
			if ($album->isDynamic()) {
				$imgi = '<img src="images/pictures_dn.png" alt="" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture_dn.png" alt="" title="' . gettext('albums') . '" />';
			} else {
				$imgi = '<img src="images/pictures.png" alt="" title="' . gettext('images') . '" />';
				$imga = '<img src="images/folder_picture.png" alt="" title="' . gettext('albums') . '" />';
			}
			$ci = count($album->getImages());
			$si = sprintf('%1$s <span>(%2$u)</span>', $imgi, $ci);
			if ($ci > 0 && !$album->isDynamic()) {
				$si = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=imageinfo" title="' . gettext('Subalbum List') . '">' . $si . '</a>';
			}
			$ca = $album->getNumAlbums();
			$sa = sprintf('%1$s <span>(%2$u)</span>', $imga, $ca);
			if ($ca > 0 && !$album->isDynamic()) {
				$sa = '<a href="?page=edit&amp;album=' . html_encode(pathurlencode($album->name)) . '&amp;tab=subalbuminfo" title="' . gettext('Subalbum List') . '">' . $sa . '</a>';
			}
			?>
			<div class="page-list_extra">
				<?php echo $sa; ?>
			</div>
			<div class="page-list_extra">
				<?php echo $si; ?>
			</div>
			<?php if ($album->hasPublishSchedule()) { ?>
				<div class="page-list_extra">
					<?php printPublished($album); ?>
				</div>
				<?php
			}
			if ($album->hasExpiration() || $album->hasExpired()) {
				?>
				<div class="page-list_extra">
					<?php printExpired($album); ?>
				</div>
			<?php } ?>
			<?php $wide = '40px'; ?>
			<div class="page-list_iconwrapperalbum">
				<div class="page-list_icon">
					<?php printProtectedIcon($album); ?>
				</div>
				<div class="page-list_icon">
					<?php printPublishIconLinkGallery($album, $enableEdit, $owner); ?>
				</div>
				<?php if (extensionEnabled('comment_form')) { ?>
					<div class="page-list_icon">
						<?php
						if ($album->getCommentsAllowed()) {
							if ($enableEdit) {
								?>
								<a href="?action=comments&amp;commentson=0&amp;album=<?php echo html_encode($album->getName()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Disable comments'); ?>">
									<?php
								}
								?>
								<img src="images/comments-on.png" alt="" title="<?php echo gettext("Comments on"); ?>" style="border: 0px;"/>
								<?php
								if ($enableEdit) {
									?>
								</a>
								<?php
							}
						} else {
							if ($enableEdit) {
								?>
								<a href="?action=comments&amp;commentson=1&amp;album=<?php echo html_encode($album->getName()); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('albumedit') ?>" title="<?php echo gettext('Enable comments'); ?>">
									<?php
								}
								?>
								<img src="images/comments-off.png" alt="" title="<?php echo gettext("Comments off"); ?>" style="border: 0px;"/>
								<?php
								if ($enableEdit) {
									?>
								</a>
								<?php
							}
						}
						?>
					</div>
				<?php } ?>
				<div class="page-list_icon">
					<a href="<?php echo WEBPATH; ?>/index.php?album=<?php echo html_encode(pathurlencode($album->name)); ?>" title="<?php echo gettext("View album"); ?>">
						<img src="images/view.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('View album %s'), $album->name); ?>" />
					</a>
				</div>
				<div class="page-list_icon">
					<?php
					if ($album->isDynamic() || !$enableEdit) {
						?>
						<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
						<?php
					} else {
						?>
						<a class="warn js_confirm_metadata_refresh_<?php echo $album->getID(); ?>" href="admin-refresh-metadata.php?page=edit&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('refresh') ?>" title="<?php echo sprintf(gettext('Refresh metadata for the album %s'), $album->name); ?>">
							<img src="images/refresh.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Refresh metadata in the album %s'), $album->name); ?>" />
						</a>
						<script>
						$( document ).ready(function() {
							var element = '.js_confirm_metadata_refresh_<?php echo $album->getID(); ?>';
							var message = '<?php echo js_encode(gettext('Refreshing metadata will overwrite existing data. This cannot be undone!')); ?>';
							confirmClick(element, message);
						});
						</script>
						<?php
					}
					?>
				</div>
				<?php
				if (extensionEnabled('hitcounter')) {
					?>
					<div class="page-list_icon">
						<?php
						if (!$enableEdit) {
							?>
							<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
							<?php
						} else {
							?>
							<a class="reset" href="?action=reset_hitcounters&amp;albumid=<?php echo $album->getID(); ?>&amp;album=<?php echo html_encode(pathurlencode($album->name)); ?>&amp;subalbum=true&amp;return=*<?php echo html_encode(pathurlencode($owner)); ?>&amp;XSRFToken=<?php echo getXSRFToken('hitcounter') ?>" title="<?php echo sprintf(gettext('Reset hit counters for album %s'), $album->name); ?>">
								<img src="images/reset.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Reset hit counters for the album %s'), $album->name); ?>" />
							</a>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
				<div class="page-list_icon">
					<?php
					$myalbum = $_zp_current_admin_obj->getAlbum();
					$supress = !zp_loggedin(MANAGE_ALL_ALBUM_RIGHTS) && $myalbum && $album->getID() == $myalbum->getID();
					if (!$enableEdit || $supress) {
						?>
						<img src="images/icon_inactive.png" style="border: 0px;" alt="" title="<?php echo gettext('unavailable'); ?>" />
						<?php
					} else {
						?>
						<a class="delete" href="javascript:confirmDeleteAlbum('?page=edit&amp;action=deletealbum&amp;album=<?php echo urlencode(pathurlencode($album->name)); ?>&amp;return=<?php echo html_encode(pathurlencode(dirname($album->name))); ?>&amp;XSRFToken=<?php echo getXSRFToken('delete') ?>');" title="<?php echo sprintf(gettext("Delete the album %s"), js_encode($album->name)); ?>">
							<img src="images/fail.png" style="border: 0px;" alt="" title="<?php echo sprintf(gettext('Delete the album %s'), js_encode($album->name)); ?>" />
						</a>
						<?php
					}
					?>
				</div>
				<?php
				if ($enableEdit) {
					?>
					<div class="page-list_icon">
						<input class="checkbox" type="checkbox" name="ids[]" value="<?php echo $album->getName(); ?>" onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" <?php if ($supress) echo ' disabled="disabled"'; ?> />
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * processes the post from the above
	 * @param int $index the index of the entry in mass edit or 0 if single album
	 * @param object $album the album object
	 * @param string $redirectto used to redirect page refresh on move/copy/rename
	 * @return string error flag if passwords don't match
	 * @since 1.1.3
	 */
	function processAlbumEdit($index, $album, &$redirectto) {
		global $_zp_current_admin_obj;
		$redirectto = NULL; // no redirection required
		if ($index == 0) {
			$prefix = $suffix = '';
		} else {
			$prefix = "$index-";
			$suffix = "_$index";
		}
		$tagsprefix = 'tags_' . $prefix;
		$notify = '';
		$album->setTitle(process_language_string_save($prefix . 'albumtitle', 2));
		$album->setDesc(process_language_string_save($prefix . 'albumdesc', EDITOR_SANITIZE_LEVEL));
		$tags = array();
		$l = strlen($tagsprefix);
		foreach ($_POST as $key => $value) {
			$key = postIndexDecode($key);
			if (substr($key, 0, $l) == $tagsprefix) {
				if ($value) {
					$tags[] = sanitize(substr($key, $l));
				}
			}
		}
		$tags = array_unique($tags);
		$album->setTags($tags);
		$album->setDateTime(sanitize($_POST[$prefix . "albumdate"]));
		$album->setLocation(process_language_string_save($prefix . 'albumlocation', 3));
		if (isset($_POST[$prefix . 'thumb'])) {
			$album->setThumb(sanitize($_POST[$prefix . 'thumb']));
		}
		$album->setPublished((int) isset($_POST[$prefix . 'Published']));
		$album->setCommentsAllowed(isset($_POST[$prefix . 'allowcomments']));
		$sorttype = strtolower(sanitize($_POST[$prefix . 'sortby'], 3));
		if ($sorttype == 'custom') {
			$sorttype = unquote(strtolower(sanitize($_POST[$prefix . 'customimagesort'], 3)));
		}
		$album->setSortType($sorttype);
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection(false, 'image');
		} else {
			if (empty($sorttype)) {
				$direction = false;
			} else {
				$direction = isset($_POST[$prefix . 'image_sortdirection']);
			}
			$album->setSortDirection($direction, 'image');
		}
		$sorttype = strtolower(sanitize($_POST[$prefix . 'subalbumsortby'], 3));
		if ($sorttype == 'custom')
			$sorttype = strtolower(sanitize($_POST[$prefix . 'customalbumsort'], 3));
		$album->setSortType($sorttype, 'album');
		if (($sorttype == 'manual') || ($sorttype == 'random')) {
			$album->setSortDirection(false, 'album');
		} else {
			$album->setSortDirection(isset($_POST[$prefix . 'album_sortdirection']), 'album');
		}
		if (isset($_POST['reset_hitcounter' . $prefix])) {
			$album->set('hitcounter', 0);
		}
		if (isset($_POST[$prefix . 'reset_rating'])) {
			$album->set('total_value', 0);
			$album->set('total_votes', 0);
			$album->set('used_ips', 0);
		}
		$album->setPublishDate(sanitize($_POST['publishdate-' . $prefix]));
		$album->setExpireDate(sanitize($_POST['expirationdate-' . $prefix]));
		$fail = '';
		processCredentials($album, $suffix);
		$oldtheme = $album->getAlbumTheme();
		if (isset($_POST[$prefix . 'album_theme'])) {
			$newtheme = sanitize($_POST[$prefix . 'album_theme']);
			if ($oldtheme != $newtheme) {
				$album->setAlbumTheme($newtheme);
			}
		}
		if (isset($_POST[$prefix . 'album_watermark'])) {
			$album->setWatermark(sanitize($_POST[$prefix . 'album_watermark'], 3));
			$album->setWatermarkThumb(sanitize($_POST[$prefix . 'album_watermark_thumb'], 3));
		}
		if (zp_loggedin(CODEBLOCK_RIGHTS)) {
			$album->setCodeblock(processCodeblockSave((int) $prefix));
		}
		if (isset($_POST[$prefix . 'owner'])) {
			$album->setOwner(sanitize($_POST[$prefix . 'owner']));
		}

		$custom = process_language_string_save($prefix . 'album_custom_data', 1);
		$album->setCustomData(zp_apply_filter('save_album_custom_data', $custom, $prefix));
		$album->setLastChangeUser($_zp_current_admin_obj->getUser());
		zp_apply_filter('save_album_utilities_data', $album, $prefix);
		$album->save(true);

		// Move/Copy/Rename the album after saving.
		$mcrerr = array();
		$movecopyrename_action = '';
		if (isset($_POST['a-' . $prefix . 'MoveCopyRename'])) {
			$movecopyrename_action = sanitize($_POST['a-' . $prefix . 'MoveCopyRename'], 3);
		}
		if ($movecopyrename_action == 'delete') {
			$dest = dirname($album->name);
			if ($album->remove()) {
				if ($dest == '/' || $dest == '.') {
					$dest = '';
				}
				$redirectto = $dest;
			} else {
				$mcrerr['mcrerr'][7][$index] = $album->getID();
			}
		}
		if ($movecopyrename_action == 'move') {
			$dest = sanitize_path($_POST['a' . $prefix . '-albumselect']);
			// Append the album name.
			$dest = ($dest ? $dest . '/' : '') . (strpos($album->name, '/') === FALSE ? $album->name : basename($album->name));
			if ($dest && $dest != $album->name) {
				if ($suffix = $album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($dest, -4) != '.' . $suffix) {
						$dest .= '.' . suffix;
					}
				}
				if ($e = $album->move($dest)) {
					$mcrerr['mcrerr'][$e][$index] = $album->getID();
					SearchEngine::clearSearchCache();
				} else {
					$redirectto = $dest;
				}
			} else {
				// Cannot move album to same album.
				$mcrerr['mcrerr'][3][$index] = $album->getID();
			}
		} else if ($movecopyrename_action == 'copy') {
			$dest = sanitize_path($_POST['a' . $prefix . '-albumselect']);
			if ($dest && $dest != $album->name) {
				if ($e = $album->copy($dest)) {
					$mcrerr['mcrerr'][$e][$index] = $album->getID();
				}
			} else {
				// Cannot copy album to existing album.
				// Or, copy with rename?
				$mcrerr['mcrerr'][3][$index] = $album->getID();
			}
		} else if ($movecopyrename_action == 'rename') {
			$renameto = sanitize_path($_POST['a' . $prefix . '-renameto']);
			$renameto = str_replace(array('/', '\\'), '', $renameto);
			if (dirname($album->name) != '.') {
				$renameto = dirname($album->name) . '/' . $renameto;
			}
			if ($renameto != $album->name) {
				if ($suffix = $album->isDynamic()) { // be sure there is a .alb suffix
					if (substr($renameto, -4) != '.' . $suffix) {
						$renameto .= '.' . $suffix;
					}
				}
				if ($e = $album->rename($renameto)) {
					$mcrerr['mcrerr'][$e][$index] = $album->getID();
				} else {
					$redirectto = $renameto;
				}
			} else {
				$mcrerr['mcrerr'][3][$index] = $album->getID();
			}
		}
		if (!empty($mcrerr)) {
			$notify = '&' . http_build_query($mcrerr);
		}
		return $notify;
	}
	
	/**
	 * Returns an array of the names of the parents of the current album.
	 *
	 * @param object $album optional album object to use inseted of the current album
	 * @return array
	 */
	function getParentAlbumsAdmin($album) {
		$parents = array();
		while (!is_null($album = $album->getParent())) {
			array_unshift($parents, $album);
		}
		return $parents;
	}

	function getAlbumBreadcrumbAdmin($album) {
		$link = '';
		$parents = getParentAlbumsAdmin($album);
		foreach ($parents as $parent) {
			$link .= "<a href='" . WEBPATH . '/' . ZENFOLDER . "/admin-edit.php?page=edit&amp;album=" . html_encode(pathurlencode($parent->name)) . "'>" . removeParentAlbumNames($parent) . "</a>/";
		}
		return $link;
	}

	/**
	 * Removes the parent album name so that we can print a album breadcrumb with them
	 *
	 * @param object $album Object of the album
	 * @return string
	 */
	function removeParentAlbumNames($album) {
		$slash = stristr($album->name, "/");
		if ($slash) {
			$array = array_reverse(explode("/", $album->name));
			$albumname = $array[0];
		} else {
			$albumname = $album->name;
		}
		return $albumname;
	}
	
	/**
 * POST handler for album tree sorts
 *
 * @param int $parentid id of owning album
 *
 */
function postAlbumSort($parentid) {
	global $_zp_current_admin_obj, $_zp_db;
	if (isset($_POST['order']) && !empty($_POST['order'])) {
		$order = processOrder(sanitize($_POST['order']));
		$sortToID = array();
		foreach ($order as $id => $orderlist) {
			$id = str_replace('id_', '', $id);
			$sortToID[implode('-', $orderlist)] = $id;
		}
		foreach ($order as $item => $orderlist) {
			$item = intval(str_replace('id_', '', $item));
			$currentalbum = $_zp_db->querySingleRow('SELECT * FROM ' . $_zp_db->prefix('albums') . ' WHERE `id`=' . $item);
			$sortorder = array_pop($orderlist);
			if (count($orderlist) > 0) {
				$newparent = $sortToID[implode('-', $orderlist)];
			} else {
				$newparent = $parentid;
			}
			if ($newparent == $currentalbum['parentid']) {
				$sql = 'UPDATE ' . $_zp_db->prefix('albums') . ' SET `sort_order`=' . $_zp_db->quote($sortorder) . ' WHERE `id`=' . $item;
				$_zp_db->query($sql);
			} else { // have to do a move
				$albumname = $currentalbum['folder'];
				$album = AlbumBase::newAlbum($albumname);
				if (strpos($albumname, '/') !== false) {
					$albumname = basename($albumname);
				}
				if (is_null($newparent)) {
					$dest = $albumname;
				} else {
					$parent = $_zp_db->querySingleRow('SELECT * FROM ' . $_zp_db->prefix('albums') . ' WHERE `id`=' . intval($newparent));
					if ($parent['dynamic']) {
						return "&mcrerr=5";
					} else {
						$dest = $parent['folder'] . '/' . $albumname;
					}
				}
				if ($e = $album->move($dest)) {
					return "&mcrerr=" . $e;
				} else {
					$album->setSortOrder($sortorder);
					$album->setLastChangeUser($_zp_current_admin_obj->getUser());
					$album->save();
				}
			}
		}
		return true;
	}
	return false;
}

/**
 * Prints the sortable nested albums list
 * returns true if nesting levels exceede the database container
 *
 * @param array $pages The array containing all pages
 * @param bool $show_thumb set false to use thumb standin image.
 * @param object $owner the album object of the owner or NULL for the gallery
 *
 * @return bool
 */
function printNestedAlbumsList($albums, $show_thumb, $owner) {
	$indent = 1;
	$open = array(1 => 0);
	$rslt = false;
	foreach ($albums as $album) {
		$order = $album['sort_order'];
		$level = max(1, count($order));
		if ($toodeep = $level > 1 && $order[$level - 1] === '') {
			$rslt = true;
		}
		if ($level > $indent) {
			echo "\n" . str_pad("\t", $indent, "\t") . "<ul class=\"page-list\">\n";
			$indent++;
			$open[$indent] = 0;
		} else if ($level < $indent) {
			while ($indent > $level) {
				$open[$indent]--;
				$indent--;
				echo "</li>\n" . str_pad("\t", $indent, "\t") . "</ul>\n";
			}
		} else { // indent == level
			if ($open[$indent]) {
				echo str_pad("\t", $indent, "\t") . "</li>\n";
				$open[$indent]--;
			} else {
				echo "\n";
			}
		}
		if ($open[$indent]) {
			echo str_pad("\t", $indent, "\t") . "</li>\n";
			$open[$indent]--;
		}
		$albumobj = AlbumBase::newAlbum($album['name']);
		if ($albumobj->isDynamic()) {
			$nonest = ' class="no-nest"';
		} else {
			$nonest = '';
		}
		echo str_pad("\t", $indent - 1, "\t") . "<li id=\"id_" . $albumobj->getID() . "\"$nonest >";
		printAlbumEditRow($albumobj, $show_thumb, $owner);
		$open[$indent]++;
	}
	while ($indent > 1) {
		echo "</li>\n";
		$open[$indent]--;
		$indent--;
		echo str_pad("\t", $indent, "\t") . "</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
	} else {
		echo "\n";
	}
	return $rslt;
}

/**
 * Processes the check box bulk actions for albums
 *
 */
function processAlbumBulkActions() {
	global $_zp_current_admin_obj;
	if (isset($_POST['ids'])) {
		$ids = sanitize($_POST['ids']);
		$action = sanitize($_POST['checkallaction']);
		$total = count($ids);
		if ($action != 'noaction' && $total > 0) {
			if ($action == 'addtags' || $action == 'alltags') {
				$tags = bulkTags();
			}
			if ($action == 'changeowner') {
				$newowner = sanitize($_POST['massownerselect']);
			}
			$n = 0;
			foreach ($ids as $albumname) {
				$n++;
				$albumobj = AlbumBase::newAlbum($albumname);
				switch ($action) {
					case 'deleteallalbum':
						$albumobj->remove();
						SearchEngine::clearSearchCache();
						break;
					case 'showall':
						$albumobj->setPublished(1);
						break;
					case 'hideall':
						$albumobj->setPublished(0);
						break;
					case 'commentson':
						$albumobj->setCommentsAllowed(1);
						break;
					case 'commentsoff':
						$albumobj->setCommentsAllowed(0);
						break;
					case 'resethitcounter':
						$albumobj->set('hitcounter', 0);
						break;
					case 'addtags':
						$mytags = array_unique(array_merge($tags, $albumobj->getTags()));
						$albumobj->setTags($mytags);
						break;
					case 'cleartags':
						$albumobj->setTags(array());
						break;
					case 'alltags':
						$images = $albumobj->getImages();
						foreach ($images as $imagename) {
							$imageobj = Image::newImage($albumobj, $imagename);
							$mytags = array_unique(array_merge($tags, $imageobj->getTags()));
							$imageobj->setTags($mytags);
							$imageobj->setLastchangeUser($_zp_current_admin_obj->getUser());
							$imageobj->save(true);
						}
						break;
					case 'clearalltags':
						$images = $albumobj->getImages();
						foreach ($images as $imagename) {
							$imageobj = Image::newImage($albumobj, $imagename);
							$imageobj->setTags(array());
							$imageobj->setLastchangeUser($_zp_current_admin_obj->getUser());
							$imageobj->save(true);
						}
						break;
					case 'changeowner':
						$albumobj->setOwner($newowner);
						break;
					default:
						callUserFunction($action, $albumobj);
						break;
				}
				$albumobj->setLastchangeUser($_zp_current_admin_obj->getUser());
				$albumobj->save(true);
			}
			return $action;
		}
	}
	return false;
}

/**
 * Returns an option list of administrators who can own albums or images
 * @param string $owner
 * @return string
 */
function admin_album_list($owner) {
	global $_zp_authority;
	$adminlist = '';
	$admins = $_zp_authority->getAdministrators();
	foreach ($admins as $user) {
		if (($user['rights'] & (UPLOAD_RIGHTS | ADMIN_RIGHTS | MANAGE_ALL_ALBUM_RIGHTS))) {
			$adminlist .= '<option value="' . $user['user'] . '"';
			if ($owner == $user['user']) {
				$adminlist .= ' SELECTED="SELECTED"';
			}
			$adminlist .= '>' . $user['user'] . "</option>\n";
		}
	}
	return $adminlist;
}

/**
 *
 * Checks for bad parentIDs from old move/copy bug
 * @param unknown_type $albumname
 * @param unknown_type $id
 */
function checkAlbumParentid($albumname, $id, $recorder) {
	$album = AlbumBase::newAlbum($albumname);
	$oldid = $album->getParentID();
	if ($oldid != $id) {
		$album->set('parentid', $id);
		$album->save();
		if (is_null($oldid))
			$oldid = '<em>NULL</em>';
		if (is_null($id))
			$id = '<em>NULL</em>';
		$msg = sprintf('Fixed album <strong>%1$s</strong>: parentid was %2$s should have been %3$s<br />', $albumname, $oldid, $id);
		$recorder($msg, true);
		echo $msg;
	}
	$id = $album->getID();
	if (!$album->isDynamic()) {
		$albums = $album->getAlbums();
		foreach ($albums as $albumname) {
			checkAlbumParentid($albumname, $id, $recorder);
		}
	}
}

/**
 * Make sure the albumimagesort is only an allowed value. Otherwise returns nothing.

 * @param string $val
 * @param string $type 'albumimagesort' or 'albumimagesort_status'
 * @return string
 */
function checkAlbumimagesort($val, $type = 'albumimagesort') {
	switch ($type) {
		case 'albumimagesort':
			$sortcheck = getSortByOptions('images');
			$direction_check = true;
			break;
		case 'albumimagesort_status':
			$sortcheck = getSortByStatusOptions();
			$direction_check = false;
			break;
	}
	foreach ($sortcheck as $sort) {
		if ($val == $sort || ($direction_check && $val == $sort . '_desc')) {
			return $val;
		}
	}
}

/**
 * Updates the $_zp_admin_imagelist global used on dynamic album editing
 * 
 * @global string $_zp_admin_imagelist
 * @global obj $_zp_gallery
 * @param type $folder
 * @return type
 */
function getSubalbumImages($folder) {
	global $_zp_admin_imagelist, $_zp_gallery;
	$album = AlbumBase::newAlbum($folder);
	if ($album->isDynamic())
		return;
	$images = $album->getImages();
	foreach ($images as $image) {
		$_zp_admin_imagelist[] = '/' . $folder . '/' . $image;
	}
	$albums = $album->getAlbums();
	foreach ($albums as $folder) {
		getSubalbumImages($folder);
	}
}