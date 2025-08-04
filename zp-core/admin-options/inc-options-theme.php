<div id="tab_theme" class="tabbox">
						<?php
						zp_apply_filter('admin_note', 'options', $subtab);
						$themelist = array();
						if (zp_loggedin(ADMIN_RIGHTS)) {
							$gallery_title = $_zp_gallery->getTitle();
							if ($gallery_title != gettext("Gallery")) {
								$gallery_title .= ' (' . gettext("Gallery") . ')';
							}
							$themelist[$gallery_title] = '';
						}
						$albums = $_zp_gallery->getAlbums(0);
						foreach ($albums as $alb) {
							$album = AlbumBase::newAlbum($alb);
							if ($album->isMyItem(THEMES_RIGHTS)) {
								$theme = $album->getAlbumTheme();
								if (!empty($theme)) {
									$key = $album->getTitle();
									if ($key != $alb) {
										$key .= " ($alb)";
									}
									$themelist[$key] = pathurlencode($alb);
								}
							}
						}
						$albumtitle = $alb = $album = NULL;
						$themename = $_zp_gallery->getCurrentTheme();
						if (!empty($_REQUEST['themealbum'])) {
							$alb = urldecode(sanitize_path($_REQUEST['themealbum']));
							$album = AlbumBase::newAlbum($alb);
							$albumtitle = $album->getTitle();
							$themename = $album->getAlbumTheme();
						}
						if (!empty($_REQUEST['optiontheme'])) {
							$themename = sanitize($_REQUEST['optiontheme']);
						}
						if (empty($alb)) {
							foreach ($themelist as $albumtitle => $alb)
								break;
							if (empty($alb)) {
								$album = NULL;
							} else {
								$alb = sanitize_path($alb);
								$album = AlbumBase::newAlbum($alb);
								$albumtitle = $album->getTitle();
								$themename = $album->getAlbumTheme();
							}
						}
						if (!(false === ($requirePath = getPlugin('themeoptions.php', $themename)))) {
							require_once($requirePath);
							$optionHandler = new ThemeOptions();
							$supportedOptions = $optionHandler->getOptionsSupported();
							if (method_exists($optionHandler, 'getOptionsDisabled')) {
								$unsupportedOptions = $optionHandler->getOptionsDisabled();
							} else {
								$unsupportedOptions = array();
							}
						} else {
							$unsupportedOptions = array();
							$supportedOptions = array();
						}
						standardThemeOptions($themename, $album);
						?>
						<form class="dirty-check" action="?action=saveoptions" method="post" id="themeoptionsform" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" id="savethemeoptions" name="savethemeoptions" value="yes" />
							<input type="hidden" name="optiontheme" value="<?php echo html_encode($themename); ?>" />
							<input type="hidden" name="old_themealbum" value="<?php echo pathurlencode($alb); ?>" />
							<table class='bordered options'>

								<?php
								if (count($themelist) == 0) {
									?>
									<th>
										<br />
									<div class="errorbox" id="no_themes">
										<h2><?php echo gettext("There are no themes for which you have rights to administer."); ?></h2>
									</div>
									</th>

									<?php
								} else {
									/* handle theme options */
									$themes = $_zp_gallery->getThemes();
									$theme = $themes[$themename];
									?>
									<tr>
										<th colspan='2'>
									<h2 style='float: left'>
										<?php
										if ($albumtitle) {
											printf(gettext('Options for <code><strong>%1$s</strong></code>: <em>%2$s</em>'), $albumtitle, $theme['name']);
										} else {
											printf(gettext('Options for <em>%s</em>'), $theme['name']);
										}
										?>
									</h2>
									</th>
									<th colspan='1' style='text-align: right'>
										<?php
										if (count($themelist) > 1) {
											echo gettext("Show theme for:");
											echo '<select id="themealbum" name="themealbum" onchange="this.form.submit()">';
											generateListFromArray(array(pathurlencode($alb)), $themelist, false, true);
											echo '</select>';
										} else {
											?>
											<input type="hidden" name="themealbum" value="<?php echo pathurlencode($alb); ?>" />
											<?php
											echo '&nbsp;';
										}
										echo "</th></tr>\n";
										?>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset'); $('#themeoptionsform').submit();"><img src="images/refresh.png" alt="" /><strong><?php echo gettext("Revert to default"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
									<tr class="alt1">
										<td align="left">
											<?php echo gettext('<strong>Standard options</strong>') ?>
										</td>
										<td colspan="2" ><?php echo gettext('<em>Image and album presentation options provided by the Zenphoto core for all themes.</em>') . '<p class="notebox">' . gettext('<strong>Note:</strong> These are <em>recommendations</em> as themes may choose to override them for design reasons'); ?></p></td>
									</tr>
									<tr>
										<td style='width: 175px'><?php echo gettext("Albums:"); ?></td>
										<td>
											<?php
											if (in_array('albums_per_page', $unsupportedOptions)) {
												$disable = ' disabled="disabled"';
											} else {
												$disable = '';
											}
											?>
										<p>
				<label><input type="text" size="3" name="albums_per_page" value="<?php echo getThemeOption('albums_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?></label>
			</p>
										</td>
										<td>
											<?php
											echo gettext('Set how many albums should appear on an album page'); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo gettext("Images:"); ?></td>
										<td>
											<?php
											if (in_array('images_per_page', $unsupportedOptions)) {
												$disable = ' disabled="disabled"';
											} else {
												$disable = '';
											}
											?>
											<label><input type="text" size="3" name="images_per_page" value="<?php echo getThemeOption('images_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?></label>
										</td>
										<td>
											<?php
											echo gettext('Set how many images (thumbs) should appear on an image page');
											if (getThemeOption('images_per_row', $album, $themename) > 1) {
												?>
												<p class="notebox">
													<?php
													echo gettext('<strong>Note:</strong> If <em>thumbnails per row</em> is greater than 1, The actual number of thumbnails that are displayed on a page will be rounded up to the next multiple of it.') . ' ';
													printf(gettext('For pages containing images there will be %1$u rows of thumbnails.'), ceil(getThemeOption('images_per_page', $album, $themename) / getThemeOption('images_per_row', $album, $themename)));
													?>
												</p>
												<?php
											}
											?>
										</td>
									</tr>
									<?php
									if (in_array('thumb_transition', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext('Transition:'); ?></td>
										<td>
											<span class="nowrap">
												<?php
											if (getThemeOption('thumb_transition', $album, $themename)) {
											$transition_enabled  = ' checked="checked"';
										} else {
											$transition_enabled  = '';
										}
												?>
												<label><input type="checkbox" name="thumb_transition" value="1"<?php echo $transition_enabled . $disable; ?> /> <?php echo gettext('Enable transition page'); ?></label>
			<?php
			if (in_array('thumb_transition_min', $unsupportedOptions)) {
				$disable = ' disabled="disabled"';
			} else {
				$disable = '';
			}
			?>
			<p>
				<label><input type="text" size="3" name="thumb_transition_min" value="<?php echo getThemeOption('thumb_transition_min', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('Minimum number of image thumbs'); ?></label>
			</p>
			<?php
			if (in_array('thumb_transition_max', $unsupportedOptions)) {
				$disable = ' disabled="disabled"';
			} else {
				$disable = '';
			}
			?>
			<p>
				<label><input type="text" size="3" name="thumb_transition_max" value="<?php echo getThemeOption('thumb_transition_max', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('Maximum number of image thumbs'); ?></label>
			</p>
										</span>
										</td>
										<td><?php echo gettext('If the last page with albums has less albums than the albums per page value, image thumbs share the page with the album thumbs. Their number is calculated from their image per page value and their total number. So if the albums use 30&percnt; of their albums per page value, the image number used is 70&percnt; of their images per page value. The minimum and maximum can be defined. Set both options to the same value to always get a fixed value.'); ?></td>
									</tr>
									<?php
									if (in_array('thumb_size', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									$ts = max(1, getThemeOption('thumb_size', $album, $themename));
									$iw = getThemeOption('thumb_crop_width', $album, $themename);
									$ih = getThemeOption('thumb_crop_height', $album, $themename);
									$thumb_use_side = getThemeOption('thumb_use_side', $album, $themename);
									$cl = round(($ts - $iw) / $ts * 50, 1);
									$ct = round(($ts - $ih) / $ts * 50, 1);
									?>
									<tr>
										<td><?php echo gettext("Thumb size:"); ?></td>
										<td><?php $side = getThemeOption('image_use_side', $album, $themename); ?>
											<table>
												<tr>
													<td rowspan="2" style="margin: 0; padding: 0">
														<input type="text" size="3" name="thumb_size" value="<?php echo $ts; ?>"<?php echo $disable; ?> />
													</td>
													<td style="margin: 0; padding: 0">
														<label> <input type="radio" id="image_use_side1" name="thumb_use_side" value="height"
																																					 <?php if ($thumb_use_side == 'height') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('height') ?> </label>
														<label> <input type="radio" id="image_use_side2"
																					 name="thumb_use_side" value="width"
																					 <?php if ($thumb_use_side == 'width') echo ' checked="checked"'; ?>
																					 <?php echo $disable; ?> /> <?php echo gettext('width') ?> </label>
													</td>
												</tr>
												<tr>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side3" name="thumb_use_side" value="shortest"
																																					 <?php if ($thumb_use_side == 'shortest') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('shortest side') ?>
														</label> <label> <input type="radio" id="image_use_side4"
																										name="thumb_use_side" value="longest"
																										<?php if ($thumb_use_side == 'longest') echo ' checked="checked"'; ?>
																										<?php echo $disable; ?> /> <?php echo gettext('longest side') ?> </label>
													</td>
												</tr>
											</table>
										</td>
										<td><?php printf(gettext("Standard thumbnails will be scaled to %u pixels."), $ts); ?> <br />
											<?php echo gettext("If cropping is disabled the thumbs will be sized so that the <em>height</em>, <em>width</em>, <em>shortest side</em>, or the <em>longest side</em> will be equal to <em>thumb size</em>."); ?>
										</td>
									</tr>
									<?php
									if (in_array('thumb_crop', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext("Crop thumbnails:"); ?></td>
										<td>
											<input type="checkbox" name="thumb_crop" value="1" <?php checked('1', $tc = getThemeOption('thumb_crop', $album, $themename)); ?><?php echo $disable; ?> />
											&nbsp;&nbsp;
											<span class="nowrap">
												<?php printf(gettext('%s%% left &amp; right'), '<input type="text" size="3" name="thumb_crop_width" id="thumb_crop_width" value="' . $cl . '"' . $disable . ' />')
												?>
											</span>&nbsp;
											<span class="nowrap">
												<?php printf(gettext('%s%% top &amp; bottom'), '<input type="text" size="3" name="thumb_crop_height" id="thumb_crop_height"	value="' . $ct . '"' . $disable . ' />');
												?>
											</span>
										</td>
										<td>
											<?php printf(gettext('If checked the thumbnail will be cropped %1$.1f%% in from the top and the bottom margins and %2$.1f%% in from the left and the right margins.'), $ct, $cl); ?>
											<br />
											<p class='notebox'><?php echo gettext('<strong>Note:</strong> changing crop will invalidate existing custom crops.'); ?></p>
										</td>
									</tr>
									<tr>
										<td><?php echo gettext("Gray scale conversion:"); ?></td>
										<td>
											<label class="checkboxlabel">
												<?php echo gettext('image') ?>
												<input type="checkbox" name="image_gray" id="image_gray" value="1" <?php checked('1', getThemeOption('image_gray', $album, $themename)); ?> />
											</label>
											<label class="checkboxlabel">
												<?php echo gettext('thumbnail') ?>
												<input type="checkbox" name="thumb_gray" id="thumb_gray" value="1" <?php checked('1', getThemeOption('thumb_gray', $album, $themename)); ?> />
											</label>
										</td>
										<td><?php echo gettext("If checked, images/thumbnails will be created in gray scale."); ?></td>
									</tr>
									<?php
									if (in_array('image_size', $unsupportedOptions)) {
										$disable = ' disabled="disabled"';
									} else {
										$disable = '';
									}
									?>
									<tr>
										<td><?php echo gettext("Image size:"); ?></td>
										<td><?php $side = getThemeOption('image_use_side', $album, $themename); ?>
											<table>
												<tr>
													<td rowspan="2" style="margin: 0; padding: 0"><input type="text"
																																							 size="3" name="image_size"
																																							 value="<?php echo getThemeOption('image_size', $album, $themename); ?>"
																																							 <?php echo $disable; ?> /></td>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side1" name="image_use_side" value="height"
																																					 <?php if ($side == 'height') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('height') ?> </label>
														<label> <input type="radio" id="image_use_side2"
																					 name="image_use_side" value="width"
																					 <?php if ($side == 'width') echo ' checked="checked"'; ?>
																					 <?php echo $disable; ?> /> <?php echo gettext('width') ?> </label>
													</td>
												</tr>
												<tr>
													<td style="margin: 0; padding: 0"><label> <input type="radio"
																																					 id="image_use_side3" name="image_use_side" value="shortest"
																																					 <?php if ($side == 'shortest') echo ' checked="checked"'; ?>
																																					 <?php echo $disable; ?> /> <?php echo gettext('shortest side') ?>
														</label> <label> <input type="radio" id="image_use_side4"
																										name="image_use_side" value="longest"
																										<?php if ($side == 'longest') echo ' checked="checked"'; ?>
																										<?php echo $disable; ?> /> <?php echo gettext('longest side') ?> </label>
													</td>
												</tr>
											</table>
										</td>
										<td><?php echo gettext("Default image display size."); ?> <br />
											<?php echo gettext("The image will be sized so that the <em>height</em>, <em>width</em>, <em>shortest side</em>, or the <em>longest side</em> will be equal to <em>image size</em>."); ?>
										</td>
									</tr>
									<?php
									if (is_null($album)) {
										if (in_array('custom_index_page', $unsupportedOptions)) {
											$disable = ' disabled="disabled"';
										} else {
											$disable = '';
										}
										?>
										<tr>
											<td><?php echo gettext("Gallery index page link:"); ?></td>
											<td>
												<select id="custom_index_page" name="custom_index_page"<?php echo $disable; ?>>
													<option value="" style="background-color:LightGray"><?php echo gettext('none'); ?></option>
													<?php
													$curdir = getcwd();
													$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $themename . '/';
													chdir($root);
													$filelist = safe_glob('*.php');
													$list = array();
													foreach ($filelist as $file) {
														$file = filesystemToInternal($file);
														$list[$file] = str_replace('.php', '', $file);
													}
													$list = array_diff($list, standardScripts());
													generateListFromArray(array(getThemeOption('custom_index_page', $album, $themename)), $list, false, true);
													chdir($curdir);
													?>
												</select>
											</td>
											<td><?php echo gettext("If this option is not empty, the Gallery Index URL that would normally link to the theme <code>index.php</code> script will instead link to this script. This frees up the <code>index.php</code> script so that you can create a customized <em>Home page</em> script. This option applies only to the main theme for the <em>Gallery</em>."); ?></td>
										</tr>

										<?php
									}
									if (count($supportedOptions) > 0) {
										?>
										<tr class="alt1" >
											<td align="left">
												<?php echo gettext('<strong>Custom theme options</strong>') ?>
											</td>
											<td colspan="2"><em><?php printf(gettext('The following are options specifically implemented by %s.'), $theme['name']); ?></em></td>
										</tr>
										<?php
										customOptions($optionHandler, '', $album, false, $supportedOptions, $themename);
									}
									?>
									<tr>
										<td colspan="3">
											<p class="buttons">
												<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
												<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset'); $('#themeoptionsform').submit();"><img src="images/refresh.png" alt="" /><strong><?php echo gettext("Revert to default"); ?></strong></button>
												<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
											</p>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</form>
					</div>