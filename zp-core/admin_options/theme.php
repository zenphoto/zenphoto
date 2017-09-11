<?php
/*
 * Guts of the theme options tab
 */
$optionRights = THEMES_RIGHTS;

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
	$album = newAlbum($alb);
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
$alb = $album = NULL;
$themename = $_zp_gallery->getCurrentTheme();
if (!empty($_REQUEST['themealbum'])) {
	$alb = urldecode(sanitize_path($_REQUEST['themealbum']));
	$album = newAlbum($alb);
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
		$album = newAlbum($alb);
		$themename = $album->getAlbumTheme();
	}
}
if (!(false === ($requirePath = getPlugin('themeoptions.php', $themename)))) {
	require_once($requirePath);
	$_zp_gallery->setCurrentTheme($themename);
	$optionHandler = new ThemeOptions();
} else {
	$optionHandler = NULL;
}

function saveOptions() {
	global $_zp_gallery;

	$themeswitch = $themealbum = $notify = $table = NULL;
	$themename = urldecode(sanitize($_POST['optiontheme'], 3));
	$returntab = "&tab=theme";
	if ($themename)
		$returntab .= '&optiontheme=' . urlencode($themename);
	// all theme specific options are custom options, handled below
	if (!isset($_POST['themealbum']) || empty($_POST['themealbum'])) {
		$themeswitch = urldecode(sanitize_path($_POST['old_themealbum'])) != '';
	} else {
		$alb = urldecode(sanitize_path($_POST['themealbum']));
		$themealbum = $table = newAlbum($alb);
		if ($themealbum->exists) {
			$table = $themealbum;
			$returntab .= '&themealbum=' . html_encode(pathurlencode($alb)) . '&tab=theme';
			$themeswitch = $alb != urldecode(sanitize_path($_POST['old_themealbum']));
		} else {
			$themealbum = NULL;
		}
	}

	if ($themeswitch) {
		$notify = '?switched';
	} else {
		if (isset($_POST['savethemeoptions']) && $_POST['savethemeoptions'] == 'reset') {
			$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `theme`=' . db_quote($themename);
			if ($themealbum) {
				$sql .= ' AND `ownerid`=' . $themealbum->getID();
			} else {
				$sql .= ' AND `ownerid`=0';
			}
			query($sql);
			$themeswitch = true;
		} else {
			$ncw = $cw = getThemeOption('thumb_crop_width', $table, $themename);
			$nch = $ch = getThemeOption('thumb_crop_height', $table, $themename);
			if (isset($_POST['image_size']))
				setThemeOption('image_size', sanitize_numeric($_POST['image_size']), $table, $themename);
			if (isset($_POST['image_use_side']))
				setThemeOption('image_use_side', sanitize($_POST['image_use_side']), $table, $themename);
			setThemeOption('thumb_crop', (int) isset($_POST['thumb_crop']), $table, $themename);
			if (isset($_POST['thumb_size'])) {
				$ts = sanitize_numeric($_POST['thumb_size']);
				setThemeOption('thumb_size', $ts, $table, $themename);
			} else {
				$ts = getThemeOption('thumb_size', $table, $themename);
			}
			if (isset($_POST['thumb_crop_width'])) {
				if (is_numeric($_POST['thumb_crop_width'])) {
					$ncw = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_width']) / 100);
				}
				setThemeOption('thumb_crop_width', $ncw, $table, $themename);
			}
			if (isset($_POST['thumb_crop_height'])) {
				if (is_numeric($_POST['thumb_crop_height'])) {
					$nch = round($ts - $ts * 2 * sanitize_numeric($_POST['thumb_crop_height']) / 100);
				}
				setThemeOption('thumb_crop_height', $nch, $table, $themename);
			}
			if (isset($_POST['albums_per_page']) && isset($_POST['albums_per_row'])) {
				$albums_per_page = sanitize_numeric($_POST['albums_per_page']);
				$albums_per_row = max(1, sanitize_numeric($_POST['albums_per_row']));
				$albums_per_page = ceil($albums_per_page / $albums_per_row) * $albums_per_row;
				setThemeOption('albums_per_page', $albums_per_page, $table, $themename);
				setThemeOption('albums_per_row', $albums_per_row, $table, $themename);
			}
			if (isset($_POST['images_per_page']) && isset($_POST['images_per_row'])) {
				$images_per_page = sanitize_numeric($_POST['images_per_page']);
				$images_per_row = max(1, sanitize_numeric($_POST['images_per_row']));
				$images_per_page = ceil($images_per_page / $images_per_row) * $images_per_row;
				setThemeOption('images_per_page', $images_per_page, $table, $themename);
				setThemeOption('images_per_row', $images_per_row, $table, $themename);
			}
			if (isset($_POST['theme_head_separator'])) {
				setThemeOption('theme_head_separator', sanitize($_POST['theme_head_separator']), $table, $themename);
			}
			setThemeOption('theme_head_listparents', (int) isset($_POST['theme_head_listparents']), $table, $themename);

			if (isset($_POST['thumb_transition']))
				setThemeOption('thumb_transition', (int) ((sanitize_numeric($_POST['thumb_transition']) - 1) && true), $table, $themename);
			$otg = getThemeOption('thumb_gray', $table, $themename);
			setThemeOption('thumb_gray', (int) isset($_POST['thumb_gray']), $table, $themename);
			if ($otg = getThemeOption('thumb_gray', $table, $themename))
				$wmo = 99; // force cache clear
			$oig = getThemeOption('image_gray', $table, $themename);
			setThemeOption('image_gray', (int) isset($_POST['image_gray']), $table, $themename);
			if ($oig = getThemeOption('image_gray', $table, $themename))
				$wmo = 99; // force cache clear
			if ($nch != $ch || $ncw != $cw) { // the crop height/width has been changed
				$sql = 'UPDATE ' . prefix('images') . ' SET `thumbX`=NULL,`thumbY`=NULL,`thumbW`=NULL,`thumbH`=NULL WHERE `thumbY` IS NOT NULL';
				query($sql);
				$wmo = 99; // force cache clear as well.
			}
			if (isset($wmo)) {
				Gallery::clearCache();
			}
		}
	}

	return array($returntab, $notify, $themealbum, $themename, $themeswitch);
}

function getOptionContent() {
	global $_zp_gallery, $optionHandler, $themelist, $themename, $themealbum, $alb, $album, $albumtitle;
	?>
	<div id="tab_theme" class="tabbox">
		<?php
		if ($optionHandler) {
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
		<form class="dirtylistening" onReset="setClean('themeoptionsform');" action="?action=saveoptions" method="post" id="themeoptionsform" autocomplete="off" >
			<?php XSRFToken('saveoptions'); ?>
			<input type="hidden" id="saveoptions" name="saveoptions" value="theme" />
			<input type="hidden" name="optiontheme" value="<?php echo urlencode($themename); ?>" />
			<input type="hidden" name="old_themealbum" value="<?php echo pathurlencode($alb); ?>" />
			<table>

				<?php
				if (count($themelist) == 0) {
					?>
					<tr>
						<th>
							<br />
							<div class="errorbox" id="no_themes">
								<h2><?php echo gettext("There are no themes for which you have rights to administer."); ?></h2>
							</div>
						</th>
					</tr>
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
						<th style='text-align: right'>
							<?php
							if (count($themelist) > 1) {
								echo gettext("Show theme for");
								echo '<select id="themealbum" class="ignoredirty" name="themealbum" onchange="this.form.submit()">';
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
						<td colspan="100%">
							<p class="buttons">
								<button type="submit" value="<?php echo gettext('Apply') ?>">
									<?php echo CHECKMARK_GREEN; ?>
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset');
										$('#themeoptionsform').submit();">
													<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
									<strong><?php echo gettext("Revert to default"); ?></strong>
								</button>
								<button type="reset" value="<?php echo gettext('reset') ?>">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
						</td>
					</tr>
					<tr class="alt1">
						<th align="left">

						</th>
						<th colspan="100%" >
							<?php echo gettext('<em>These image and album presentation options provided by the Core for all themes.</em>') . '<p class="notebox">' . gettext('<strong>Note:</strong> These are <em>recommendations</em> as themes may choose to override them for design reasons.'); ?></p>
						</th>
					</tr>
					<tr>
						<td class="option_name"><?php echo gettext("Albums"); ?></td>
						<td class="option_value">
							<?php
							if (in_array('albums_per_row', $unsupportedOptions)) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = '';
							}
							?>
							<input type="text" size="3" name="albums_per_row" value="<?php echo getThemeOption('albums_per_row', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per row'); ?>
							<br />
							<?php
							if (in_array('albums_per_page', $unsupportedOptions)) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = '';
							}
							?>
							<input type="text" size="3" name="albums_per_page" value="<?php echo getThemeOption('albums_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php
									echo gettext('These specify the Theme <a title="Look at your album page and count the number of album thumbnails that show up in one row. This is the value you should set for the option.">CSS determined number</a> of album thumbnails that will fit in a "row" and the number of albums thumbnails you wish per page.');
									if (getThemeOption('albums_per_row', $album, $themename) > 1) {
										?>
										<p class="notebox">
											<?php
											echo gettext('<strong>Note:</strong> If <em>thumbnails per row</em> is greater than 1, The actual number of thumbnails that are displayed on a page will be rounded up to  the next multiple of it.') . ' ';
											printf(gettext('For album pages there will be %1$u rows of thumbnails.'), ceil(getThemeOption('albums_per_page', $album, $themename) / getThemeOption('albums_per_row', $album, $themename)));
											?>
										</p>
										<?php
									}
									?>
								</div>
							</span>
						</td>
					</tr>
					<tr>
						<td class="option_name"><?php echo gettext("Images"); ?></td>
						<td class="option_value">
							<?php
							if (in_array('images_per_row', $unsupportedOptions)) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = '';
							}
							?>
							<input type="text" size="3" name="images_per_row" value="<?php echo getThemeOption('images_per_row', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per row'); ?>
							<br />
							<?php
							if (in_array('images_per_page', $unsupportedOptions)) {
								$disable = ' disabled="disabled"';
							} else {
								$disable = '';
							}
							?>
							<input type="text" size="3" name="images_per_page" value="<?php echo getThemeOption('images_per_page', $album, $themename); ?>"<?php echo $disable; ?> /> <?php echo gettext('thumbnails per page'); ?>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php
									echo gettext('These specify the Theme <a title="Look at your album page and count the number of image thumbnails that show up in one row. This is the value you should set for the option.">CSS determined number</a> of image thumbnails that will fit in a "row" and the number of image thumbnails you wish per page.');
									if (getThemeOption('images_per_row', $album, $themename) > 1) {
										?>
										<p class="notebox">
											<?php
											echo gettext('<strong>Note:</strong> If <em>thumbnails per row</em> is greater than 1, The actual number of thumbnails that are displayed on a page will be rounded up to  the next multiple of it.') . ' ';
											printf(gettext('For pages containing images there will be %1$u rows of thumbnails.'), ceil(getThemeOption('images_per_page', $album, $themename) / getThemeOption('images_per_row', $album, $themename)));
											?>
										</p>
										<?php
									}
									?>
								</div>
							</span>
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
						<td class="option_name"><?php echo gettext('Transition'); ?></td>
						<td class="option_value">
							<span class="nowrap">
								<?php
								if (!$disable && (getThemeOption('albums_per_row', $album, $themename) > 1) && (getThemeOption('images_per_row', $album, $themename) > 1)) {
									if (getThemeOption('thumb_transition', $album, $themename)) {
										$separate = '';
										$combined = ' checked="checked"';
									} else {
										$separate = ' checked="checked"';
										$combined = '';
									}
								} else {
									$combined = $separate = ' disabled="disabled"';
								}
								?>
								<label><input type="radio" name="thumb_transition" value="1"<?php echo $separate; ?> /><?php echo gettext('separate'); ?></label>
								<label><input type="radio" name="thumb_transition" value="2"<?php echo $combined; ?> /><?php echo gettext('combined'); ?></label>
							</span>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext('if both album and image <em>thumbnails per row</em> are greater than 1 you can choose if album thumbnails and image thumbnails are placed together on the page that transitions from only album thumbnails to only image thumbnails.'); ?></div>
							</span>
						</td>

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
					$cl = round(($ts - $iw) / $ts * 50, 1);
					$ct = round(($ts - $ih) / $ts * 50, 1);
					?>
					<tr>
						<td class="option_name"><?php echo gettext("Thumb size"); ?></td>
						<td class="option_value">
							<input type="text" size="3" name="thumb_size" value="<?php echo $ts; ?>"<?php echo $disable; ?> />
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php printf(gettext("Standard thumbnails will be scaled to %u pixels."), $ts); ?>
								</div>
							</span>
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
						<td class="option_name"><?php echo gettext("Crop thumbnails"); ?></td>
						<td class="option_value">
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
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php printf(gettext('If checked the thumbnail will be cropped %1$.1f%% in from the top and the bottom margins and %2$.1f%% in from the left and the right margins.'), $ct, $cl); ?>
									<br />
									<p class='notebox'><?php echo gettext('<strong>Note:</strong> changing crop will invalidate existing custom crops.'); ?></p>
								</div>
							</span>
						</td>
					</tr>
					<tr>
						<td class="option_name"><?php echo gettext("Gray scale conversion"); ?></td>
						<td class="option_value">
							<label class="checkboxlabel">
								<?php echo gettext('image') ?>
								<input type="checkbox" name="image_gray" id="image_gray" value="1" <?php checked('1', getThemeOption('image_gray', $album, $themename)); ?> />
							</label>
							<label class="checkboxlabel">
								<?php echo gettext('thumbnail') ?>
								<input type="checkbox" name="thumb_gray" id="thumb_gray" value="1" <?php checked('1', getThemeOption('thumb_gray', $album, $themename)); ?> />
							</label>
						</td>
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext("If checked, images/thumbnails will be created in gray scale."); ?>
								</div>
							</span>
						</td>
					</tr>
					<?php
					if (in_array('image_size', $unsupportedOptions)) {
						$disable = ' disabled="disabled"';
					} else {
						$disable = '';
					}
					?>
					<tr>
						<td class="option_name"><?php echo gettext("Image size"); ?></td>
						<td class="option_value"><?php $side = getThemeOption('image_use_side', $album, $themename); ?>
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
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php echo gettext("Default image display size."); ?> <br />
									<?php echo gettext("The image will be sized so that the <em>height</em>, <em>width</em>, <em>shortest side</em>, or the <em>longest side</em> will be equal to <em>image size</em>."); ?>
								</div>
							</span>
						</td>
					</tr>
					<?php
					if (is_null($album)) {
						?>
						<tr>
							<td class="option_name"><?php echo gettext("Theme head &lt;title&gt; tag"); ?></td>
							<td class="option_value">
								<label><input type="checkbox" name="theme_head_listparents" value="1"<?php if (getThemeOption('theme_head_listparents', $album, $themename)) echo ' checked="checked"'; ?> /><?php echo gettext('enabled'); ?></label>
								<br />
								<input type="text" name="theme_head_separator" size="2em" value="<?php echo getThemeOption('theme_head_separator', $album, $themename); ?>" /><?php echo "separator"; ?>
							</td>

							<td class="option_desc">
								<span class="option_info">
									<?php echo INFORMATION_BLUE; ?>
									<div class="option_desc_hidden">
										<?php echo gettext('Select if you want parent breadcrumbs and if so the separator for them.'); ?>
									</div>
								</span></td>
						</tr>
						<?php
					}
					if (count($supportedOptions) > 0) {
						?>
						<tr class="alt1" >
							<th align="left">

							</th>
							<th colspan="100%">
								<em><?php printf(gettext('The following are options specifically implemented by %s.'), $theme['name']); ?></em>
							</th>
						</tr>
						<?php
						customOptions($optionHandler, '', $album, false, $supportedOptions, $themename);
					}
					?>
					<tr>
						<td colspan="100%">
							<p class="buttons">
								<button type="submit" value="<?php echo gettext('Apply') ?>">
									<?php echo CHECKMARK_GREEN; ?>
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<button type="button" value="<?php echo gettext('Revert to default') ?>" onclick="$('#savethemeoptions').val('reset');
										$('#themeoptionsform').submit();">
													<?php echo CLOCKWISE_OPEN_CIRCLE_ARROW_GREEN; ?>
									<strong><?php echo gettext("Revert to default"); ?></strong>
								</button>
								<button type="reset" value="<?php echo gettext('reset') ?>">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
			$prev = $next = $found = NULL;
			foreach ($themes as $atheme => $data) {
				array_shift($themes);
				if ($atheme == $themename) {
					$found = true;
				} else {
					if ($found) {
						$next = $atheme;
						break;
					}
					$prev = $atheme;
				}
			}
			?>
			<p class="padded">
				<a href="?page=options&tab=theme&optiontheme=<?php echo urlencode($prev); ?>"><?php echo $prev; ?></a>
				<span class="floatright" >
					<a href="?page=options&tab=theme&optiontheme=<?php echo urlencode($next); ?>"><?php echo $next; ?></a>
				</span>
			</p>
			<br class="clearall">
		</form>
	</div>
	<!-- end of tab_theme div -->
	<?php
}
