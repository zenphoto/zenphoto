<?php codeblocktabsJS(); ?>
					<div id="tab_gallery" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input	type="hidden" name="savegalleryoptions" value="yes" />
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
								<tr>
									<td width="175"><?php echo gettext("Gallery title:"); ?></td>
									<td width="350">
										<?php print_language_string_list($_zp_gallery->getTitle('all'), 'gallery_title'); ?>
									</td>
									<td><?php echo gettext("What you want to call your Zenphoto site."); ?></td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Gallery description:"); ?></td>
									<td width="350">
										<?php print_language_string_list($_zp_gallery->getDesc('all'), 'Gallery_description', true, NULL, 'texteditor'); ?>
									</td>
									<td><?php echo gettext("A brief description of your gallery. Some themes may display this text."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright notice'); ?></td>
									<td>
										<p><?php print_language_string_list($_zp_gallery->getCopyrightNotice('all'), 'copyright_site_notice'); ?> <?php echo gettext('Notice'); ?></p>

									</td>
									<td>
										<p><?php echo gettext('The notice will be used by the html_meta_tags plugin. If not set the image meta data is tried instead.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright URL'); ?></td>
									<td>
									<?php printZenpagePageSelector('copyright_site_url', 'copyright_site_url_custom', false, true); ?>
									</td>
									<td>
										<p><?php echo gettext('Choose a Zenpage page or define a custom URL. The URL maybe used to point to some specific copyright info source. Must be an absolute URL address of the form: http://mydomain.com/license.html.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext("Display copyright notice"); ?></td>
									<td>
										<label><input type="checkbox" name="display_copyright_notice" id="display_copyright_notice" value="1" <?php checked('1', getOption('display_copyright_notice')); ?> /> <?php echo gettext('Enable'); ?></label>
									</td>
									<td><?php echo gettext("Enable to display the copyright notice. This may usually be in the theme footer but is up to the theme."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Site copyright rightsholder'); ?></td>
									<td>
										<?php printUserSelector('copyright_site_rightsholder','copyright_site_rightsholder_custom', 'users', true); ?>
									</td>
									<td>
										<p><?php echo gettext('The rights holder will be used by the html_meta_tags plugin. If set to <em>none</em> the image metadata fields "copyright" or "owner" are used as fallbacks, if available.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext('Gallery type'); ?></td>
									<td>
										<label><input type="radio" name="gallery_security" value="public" alt="<?php echo gettext('public'); ?>"<?php if (GALLERY_SECURITY == 'public') echo ' checked="checked"' ?> onclick="javascript:$('.public_gallery').show();" /><?php echo gettext('public'); ?></label>
										<label><input type="radio" name="gallery_security" value="private" alt="<?php echo gettext('private'); ?>"<?php if (GALLERY_SECURITY != 'public') echo 'checked="checked"' ?> onclick="javascript:$('.public_gallery').hide();" /><?php echo gettext('private'); ?></label>
									</td>
									<td>
										<?php echo gettext('Private galleries are viewable only by registered users.'); ?>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									?>
									<tr class="passwordextrashow public_gallery">
										<td style="background-color: #ECF1F2;">
											<p>
												<a href="javascript:toggle_passwords('',true);">
													<?php echo gettext("Gallery password:"); ?>
												</a>
											</p>
										</td>
										<td style="background-color: #ECF1F2;">
											<p>
											<?php
											$x = $_zp_gallery->getPassword();
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
											</p>
										</td>
										<td style="background-color: #ECF1F2;">
											<p>
												<?php echo gettext("Master password for the gallery. Click on <em>Gallery password</em> to change."); ?>
											</p>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none">
										<td>
											<p>
											<a href="javascript:toggle_passwords('',false);">
												<?php echo gettext("Gallery guest user:"); ?>
											</a>
											</p>
										</td>
										<td>
											<p>
											<input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>"
														 class="dirtyignore"
														 onkeydown="passwordClear('');"
														 id="user_name" name="user"
														 value="<?php echo html_encode($_zp_gallery->getUser()); ?>" />
											</p>

										</td>
										<td>
											<?php echo gettext("User ID for the gallery guest user") ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<span id="strength">
												<?php echo gettext("Gallery password:"); ?>
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
											<?php echo gettext("Master password for the gallery. If this is set, visitors must know this password to view the gallery."); ?>
										</td>
									</tr>
									<tr class="passwordextrahide" style="display:none" >
										<td>
											<?php echo gettext("Gallery password hint:"); ?>
										</td>
										<td>
											<?php print_language_string_list($_zp_gallery->getPasswordHint('all'), 'hint', false, NULL, 'hint'); ?>
										</td>
										<td>
											<?php echo gettext("A reminder hint for the password."); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td><?php echo gettext('Unprotected pages:'); ?></td>
									<td>
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
											<input type="hidden" name="gallery-page_<?php echo $page; ?>" value="0" />
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
									<td><?php echo gettext('Place a checkmark on any page scripts which should not be protected by the gallery password.'); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Parent website title:"); ?></td>
									<td>
										<?php print_language_string_list($_zp_gallery->getParentSiteTitle('all'), 'website_title'); ?>
									</td>
									<td><?php echo gettext("Your parent website title for use in e.g. breadcrumbs if you use Zenphoto as part of a bigger site run by another CMS. Not needed on plain Zenphoto sites."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Parent website URL:"); ?></td>
									<td><input type="text" size="<?php echo TEXT_INPUT_SIZE; ?>" name="website_url"
														 value="<?php echo html_encode($_zp_gallery->getParentSiteURL()); ?>" /></td>
									<td><?php echo gettext("This URL is used to link back to your parent website in e.g. breadcrumbs, but your theme must support it. Not needed on plain Zenphoto sites."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Album thumbnails:"); ?></td>
									<td>
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
									<td><?php echo gettext("Default thumbnail selection for albums."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Sort gallery by:"); ?></td>
									<td>
										<?php
										$sort = getSortByOptions('albums');

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
														if (array_search($cv, $sort) === false) {
															$cv = 'custom';
															$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
														}
														generateListFromArray(array($cv), $sort, false, true);
														?>
													</select>
												</td>
												<td>
													<span id="gallery_sortdirection" style="display:<?php echo $dspd; ?>">
														<label>
															<input type="checkbox" name="gallery_sortdirection"	value="1" <?php checked('1', $_zp_gallery->getSortDirection()); ?> />
															<?php echo gettext("Descending"); ?>
														</label>
													</span>
												</td>
											</tr>

										</table>
									</td>
									<td>
										<?php
										echo gettext('Sort order for all albums and subalbums.');
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Gallery behavior:"); ?></td>
									<td>
										<p>
											<label>
												<input type="checkbox" name="album_default"	value="1"<?php if ($_zp_gallery->getAlbumPublish()) echo ' checked="checked"'; ?> />
												<?php echo gettext("Publish albums by default"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="image_default"	value="1"<?php if ($_zp_gallery->getImagePublish()) echo ' checked="checked"'; ?> />
												<?php echo gettext("Publish images by default"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="album_use_new_image_date" id="album_use_new_image_date"
															 value="1" <?php checked('1', $_zp_gallery->getAlbumUseImagedate()); ?> />
															 <?php echo gettext("use latest image date as album date"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="thumb_select_images" id="thumb_select_images"
															 value="1" <?php checked('1', $_zp_gallery->getThumbSelectImages()); ?> />
															 <?php echo gettext("visual thumb selection"); ?>
											</label>
										</p>
										<p>
											<label>
												<input type="checkbox" name="multilevel_thumb_select_images" id="thumb_select_images"
															 value="1" <?php checked('1', $_zp_gallery->getSecondLevelThumbs()); ?> />
															 <?php echo gettext("show subalbum thumbs"); ?>
											</label>
										</p>
									</td>
									<td>
										<p><?php echo gettext("<a href=\"javascript:toggle('albumpub');\" >Details</a> for <em>publish albums by default</em>"); ?></p>
										<div id="albumpub" style="display: none">
											<p><?php echo gettext("This sets the default behavior for when Zenphoto discovers an album. If checked, the album will be published, if unchecked it will be unpublished.") ?></p>
										</div>
										<p><?php echo gettext("<a href=\"javascript:toggle('imagepub');\" >Details</a> for <em>publish images by default</em>"); ?></p>
										<div id="imagepub" style="display: none">
											<p><?php echo gettext("This sets the default behavior for when Zenphoto discovers an image. If checked, the image will be published, if unchecked it will be unpublished.") ?></p>
										</div>
										<p><?php echo gettext("<a href=\"javascript:toggle('albumdate');\" >Details</a> for <em>use latest image date as album date</em>"); ?></p>
										<div id="albumdate" style="display: none">
											<p>
												<?php echo gettext("If you wish your album date to reflect the date of the latest image uploaded set this option. Otherwise the date will be set initially to the date the album was created.") ?>
											</p>
											<p class="notebox">
												<?php echo gettext('<strong>NOTE</strong>: Zenphoto will update the album date only if an image is discovered which is newer than the current date of the album.<br>Be sure to refresh image metadata for this option to have effect.'); ?>
											</p>
										</div>

										<p><?php echo gettext("<a href=\"javascript:toggle('visualthumb');\" >Details</a> for <em>visual thumb selection</em>"); ?></p>
										<div id="visualthumb" style="display: none">
											<p><?php echo gettext("Setting this option places thumbnails in the album thumbnail selection list (the dropdown list on each album’s edit page). In Firefox the dropdown shows the thumbs, but in IE and Safari only the names are displayed (even if the thumbs are loaded!). In albums with many images loading these thumbs takes much time and is unnecessary when the browser will not display them. Uncheck this option and the images will not be loaded."); ?></p>
										</div>

										<p><?php echo gettext("<a href=\"javascript:toggle('multithumb');\" >Details</a> for <em>subalbum thumb selection</em>"); ?></p>
										<div id="multithumb" style="display: none">
											<p><?php echo gettext("Setting this option allows selecting images from subalbums as well as from the album. Naturally populating these images adds overhead. If your album edit tabs load too slowly, do not select this option."); ?></p>
										</div>

									</td>
								</tr>

								<tr valign="top">
									<td class="topalign-nopadding"><br /><?php echo gettext("Codeblocks:"); ?></td>
									<td>
										<?php printCodeblockEdit($_zp_gallery, 0); ?>
									</td>
									<td>
									</td>
								</tr>

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
					<!-- end of tab-gallery div -->