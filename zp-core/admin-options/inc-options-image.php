<div id="tab_image" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="saveimageoptions" value="yes" />
							<p align="center">
								<?php echo gettext('See also the <a href="?tab=theme">Options > Theme</a> admin page for theme specific image options.'); ?>
							</p>

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
								foreach ($_zp_graphics_optionhandlers as $handler) {
									customOptions($handler, '');
								}
								?>
								<tr>
									<td><?php echo gettext("Sort images by"); ?></td>
									<td>
										<?php
										$sort = getSortByOptions('images');
										$cvt = $cv = IMAGE_SORT_TYPE;
										//$sort[gettext('Custom')] = 'custom';

										/*
										 * not recommended--screws with peoples minds during pagination!
										  $sort[gettext('Random')] = 'random';
										 */
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
										<span class="nowrap">
											<select id="imagesortselect" name="image_sorttype" onchange="update_direction(this, 'image_sortdirection', 'customTextBox3')">
												<?php
												if (array_search($cv, $sort) === false) {
													$cv = 'custom';
													$sort[sprintf(gettext("Custom (%s)"), $type)] = 'custom';
												}
												generateListFromArray(array($cv), $sort, false, true);
												?>
											</select>
											<label id="image_sortdirection" style="display:<?php echo $dspd; ?>white-space:nowrap;">
												<input type="checkbox" name="image_sortdirection"	value="1" <?php checked('1', getOption('image_sortdirection')); ?> />
												<?php echo gettext("Descending"); ?>
											</label>
										</span>

									</td>
									<td>
										<p><?php echo gettext("Default sort order for images."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Maximum image size'); ?></td>
									<td width="350">
										<input type="textbox" name="image_max_size" value="<?php echo getOption('image_max_size'); ?>" />
									</td>
									<td><?php echo gettext('The limit on how large an image may be resized. Too large and your server will spend all its time sizing images.'); ?></td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Image quality:"); ?></td>
									<td width="350">
										<p class="nowrap">
											<?php echo gettext('Normal Image'); ?>&nbsp;<input type="text" size="3" id="imagequality" name="image_quality" value="<?php echo getOption('image_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-imagequality").slider({
	<?php $v = getOption('image_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#imagequality").val(ui.value);
																												}
																								});
																												$("#imagequality").val($("#slider-imagequality").slider("value"));
																								});
											</script>
										<div id="slider-imagequality"></div>
										</p>
										<p class="nowrap">
											<?php echo gettext('<em>full</em> Image'); ?>&nbsp;<input type="text" size="3" id="fullimagequality" name="full_image_quality" value="<?php echo getOption('full_image_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-fullimagequality").slider({
	<?php $v = getOption('full_image_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#fullimagequality").val(ui.value);
																												}
																								});
																												$("#fullimagequality").val($("#slider-fullimagequality").slider("value"));
																								});
											</script>
										<div id="slider-fullimagequality"></div>
										</p>
										<p class="nowrap">
											<?php echo gettext('Thumbnail'); ?>&nbsp;<input type="text" size="3" id="thumbquality" name="thumb_quality" value="<?php echo getOption('thumb_quality'); ?>" />
											<script>
																								$(function() {
																								$("#slider-thumbquality").slider({
	<?php $v = getOption('thumb_quality'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#thumbquality").val(ui.value);
																												}
																								});
																												$("#thumbquality").val($("#slider-thumbquality").slider("value"));
																								});
											</script>
										<div id="slider-thumbquality"></div>
										</p>
									</td>
									<td>
										<p><?php echo gettext("Compression quality for images and thumbnails generated by Zenphoto."); ?></p>
										<p><?php echo gettext("Quality ranges from 0 (worst quality, smallest file) to 100 (best quality, biggest file)."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Interlace:"); ?></td>
									<td><input type="checkbox" name="image_interlace" value="1" <?php checked('1', getOption('image_interlace')); ?> /></td>
									<td><?php echo gettext("If checked, resized images will be created <em>interlaced</em> (if the format permits)."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext('Use embedded thumbnail'); ?></td>
									<?php
									if (function_exists('exif_thumbnail')) {
										$disabled = '';
									} else {
										$disabled = ' disabled="disabled"';
										setOption('use_embedded_thumb', 0);
									}
									?>
									<td><input type="checkbox" name="use_embedded_thumb" value="1" <?php checked('1', getOption('use_embedded_thumb')); ?><?php echo $disabled; ?> /></td>
									<td>
										<p><?php echo gettext('If set, Zenphoto will use the thumbnail embedded in the image when creating a cached image that is equal or smaller in size. Note: the quality of this image varies by camera and its orientation may not match the master image.'); ?></p>
										<?php
										if ($disabled) {
											?>
											<p class="notebox"><?php echo gettext('The PHP EXIF extension is required for this option.') ?></p>
											<?php
										}
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Allow upscale:"); ?></td>
									<td><input type="checkbox" name="image_allow_upscale" value="1" <?php checked('1', getOption('image_allow_upscale')); ?> /></td>
									<td><?php echo gettext("Allow images to be scaled up to the requested size. This could result in loss of quality, so it is off by default."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Sharpen:"); ?></td>
									<td>
										<p class="nowrap">
											<label>
												<input type="checkbox" name="image_sharpen" value="1" <?php checked('1', getOption('image_sharpen')); ?> />
												<?php echo gettext('Images'); ?>
											</label>
											<label>
												<input type="checkbox" name="thumb_sharpen" value="1" <?php checked('1', getOption('thumb_sharpen')); ?> />
												<?php echo gettext('Thumbs'); ?>
											</label>
										</p>
										<p class="nowrap">
											<?php echo gettext('Amount'); ?>&nbsp;<input type="text" id="sharpenamount" name="sharpen_amount" size="3" value="<?php echo getOption('sharpen_amount'); ?>" />
											<script>
																								$(function() {
																								$("#slider-sharpenamount").slider({
	<?php $v = getOption('sharpen_amount'); ?>
																								startValue: <?php echo $v; ?>,
																												value: <?php echo $v; ?>,
																												min: 0,
																												max: 100,
																												slide: function(event, ui) {
																												$("#sharpenamount").val(ui.value);
																												}
																								});
																												$("#sharpenamount").val($("#slider-sharpenamount").slider("value"));
																								});
											</script>
										<div id="slider-sharpenamount"></div>
										</p>

										<table>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Radius'); ?>&nbsp;</td>
												<td class="image_option_tablerow"><input type="text" name = "sharpen_radius" size="2" value="<?php echo getOption('sharpen_radius'); ?>" /></td>
											</tr>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Threshold'); ?>&nbsp;</td>
												<td class="image_option_tablerow"><input type="text" name = "sharpen_threshold" size="3" value="<?php echo getOption('sharpen_threshold'); ?>" /></td>
											</tr>
										</table>
									</td>
									<td>
										<p><?php echo gettext("Add an unsharp mask to images and/or thumbnails.") . "</p><p class='warningbox'>" . gettext("<strong>WARNING</strong>: can overload slow servers."); ?></p>
										<p><?php echo gettext("<em>Amount</em>: the strength of the sharpening effect. Values are between 0 (least sharpening) and 100 (most sharpening)."); ?></p>
										<p><?php echo gettext("<em>Radius</em>: the pixel radius of the sharpening mask. A smaller radius sharpens smaller details, and a larger radius sharpens larger details."); ?></p>
										<p><?php echo gettext("<em>Threshold</em>: the color difference threshold required for sharpening. A low threshold sharpens all edges including faint ones, while a higher threshold only sharpens more distinct edges."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Watermarks:"); ?></td>
									<td>
										<table>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Images'); ?> </td>
												<td class="image_option_tablerow">
													<select id="fullimage_watermark" name="fullimage_watermark">
														<?php $current = IMAGE_WATERMARK; ?>
														<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('none'); ?></option>
														<?php
														$watermarks = getWatermarks();
														generateListFromArray(array($current), $watermarks, false, false);
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="image_option_tablerow"><?php echo gettext('Full sized images'); ?> </td>
												<td class="image_option_tablerow">
													<select id="fullsizeimage_watermark" name="fullsizeimage_watermark">
														<?php $current = FULLIMAGE_WATERMARK; ?>
														<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray"><?php echo gettext('none'); ?></option>
														<?php
														$watermarks = getWatermarks();
														generateListFromArray(array($current), $watermarks, false, false);
														?>
													</select>
												</td>
											</tr>
											<?php
											$imageplugins = array_unique($_zp_extra_filetypes);
											$imageplugins[] = 'Image';
											ksort($imageplugins, SORT_LOCALE_STRING);
											foreach ($imageplugins as $plugin) {
												$opt = $plugin . '_watermark';
												$current = getOption($opt);
												?>
												<tr>
													<td class="image_option_tablerow">
														<?php
														printf(gettext('%s thumbnails'), gettext($plugin));
														if ($plugin != 'Image')
															echo ' <strong>*</strong>';
														?> </td>
													<td class="image_option_tablerow">
														<select id="<?php echo $opt; ?>" name="<?php echo $opt; ?>">
															<option value="" <?php if (empty($current)) echo ' selected="selected"' ?> style="background-color:LightGray">
																<?php
																if ($plugin == 'Image')
																	echo gettext('none');
																else
																	echo gettext('image thumb')
																	?>
															</option>
															<?php
															$watermarks = getWatermarks();
															generateListFromArray(array($current), $watermarks, false, false);
															?>
														</select>
													</td>
												</tr>
												<?php
											}
											?>
										</table>
										<p class="nowrap">
											<?php echo gettext('cover') . ' '; ?>
											<input type="text" size="2" name="watermark_scale"
														 value="<?php echo html_encode(getOption('watermark_scale')); ?>" /><?php /* xgettext:no-php-format */ echo gettext('% of image') ?>
										</p>
										<p class="nowrap">
											<?php echo gettext("offset h"); ?>
											<input type="text" size="2" name="watermark_h_offset"
														 value="<?php echo html_encode(getOption('watermark_h_offset')); ?>" /><?php echo /* xgettext:no-php-format */ gettext("% , w "); ?>
											<input type="text" size="2" name="watermark_w_offset"
														 value="<?php echo html_encode(getOption('watermark_w_offset')); ?>" /><?php /* xgettext:no-php-format */ echo gettext("%"); ?>
										</p>
									</td>
									<td>
										<p><?php echo gettext("The watermark image is scaled by to cover <em>cover percentage</em> of the image and placed relative to the upper left corner of the image."); ?></p>
										<p><?php echo gettext("It is offset from there (moved toward the lower right corner) by the <em>offset</em> percentages of the height and width difference between the image and the watermark."); ?></p>
										<p><?php echo gettext("The watermark will not be made larger than the original watermark image."); ?></p>
										<p><?php printf(gettext('Custom watermarks should be placed in the <code>/%s/watermarks/</code> folder. The images must be in png-24 format.'), USER_PLUGIN_FOLDER); ?></p>
										<?php
										if (!empty($imageplugins)) {
											?>
											<p class="notebox"><?php echo '* ' . gettext('If a watermark image is selected for these <em>images classes</em> it will be used in place of the image thumbnail watermark.'); ?></p>
											<?php
										}
										?>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> If you want to watermark <em>Full sized images</em> you have to enable <em>cache the full image</em> from the <em>Full image protection</em> option below.'); ?>
										</p>
									</td>

								</tr>
								<tr>
									<td><?php echo gettext("Caching concurrency:"); ?></td>
									<td>
										<script>
																							$(function() {
																							$("#slider-workers").slider({
	<?php $v = getOption('imageProcessorConcurrency'); ?>
																							startValue: <?php echo $v; ?>,
																											value: <?php echo $v; ?>,
																											min: 1,
																											max:60,
																											slide: function(event, ui) {
																											$("#cache-workers").val(ui.value);
																															$("#cache_processes").html($("#cache-workers").val());
																											}
																							});
																											$("#cache-workers").val($("#slider-workers").slider("value"));
																											$("#cache_processes").html($("#cache-workers").val());
																							});
										</script>
										<div id="slider-workers"></div>
										<input type="hidden" id="cache-workers" name="imageProcessorConcurrency" value="<?php echo getOption('imageProcessorConcurrency'); ?>" />
									</td>
									<td>
										<?php
										printf(gettext('Cache processing worker limit: %s.'), '<span id="cache_processes">' . getOption('imageProcessorConcurrency') . '</span>') .
														'<p class="notebox">' . gettext('More workers will get the job done faster so long as your server does not get swamped or run out of memory.') . '</p>';
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Cache as:"); ?></td>
									<td>
										<?php $type = IMAGE_CACHE_SUFFIX; ?>
										<input type="radio" name="image_cache_suffix" value=""<?php if (empty($type)) echo ' checked="checked"'; ?> />&nbsp;<?php echo gettext("Original"); ?>
										<?php
										$cachesuffix = array_unique($_zp_cachefile_suffix);
										foreach ($cachesuffix as $suffix) {
											?>
											<input type="radio" name="image_cache_suffix" value="<?php echo $suffix; ?>"<?php if ($type == $suffix) echo ' checked="checked"'; ?> />&nbsp;<?php echo $suffix; ?>
											<?php
										}
										?>
									</td>
									<td><?php echo gettext("Select a type for the images stored in the image cache. Select <em>Original</em> to preserve the original image’s type."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Protect image cache"); ?></td>
									<td>
										<input type="checkbox" name="protected_image_cache" value="1"
													 <?php checked('1', getOption('protected_image_cache')); ?> />
									</td>
									<td><?php
										echo gettext('If checked all image URIs will link to the image processor and the image cache will be disabled to browsers via an <em>.htaccess</em> file. Images are still cached but the image processor is used to serve the image rather than allowing the browser to fetch the file.') .
										'<p class="warningbox">' . gettext('<strong>WARNING	:</strong> This option adds significant overhead to <strong>each and every</strong> image reference! Some <em>JavaScript</em> and <em>Flash</em> based image handlers will not work with an image processor URI and are incompatible with this option.') . '</p>';
										?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Secure image processor"); ?></td>
									<td>
										<input type="checkbox" name="secure_image_processor" value="1"
													 <?php checked('1', getOption('secure_image_processor')); ?> />
									</td>
									<td><?php
										echo gettext('When enabled, the image processor will check album access credentials.') .
										'<p class="warningbox">' . gettext('<strong>WARNING	:</strong> This option adds memory overhead to image caching! You may be unable to cache some images depending on your server memory availability.') . '</p>';
										?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Full image protection:"); ?></td>
									<td style="margin:0">
										<p>
											<label>
												<input type="checkbox" name="hotlink_protection" value="1" <?php checked('1', getOption('hotlink_protection')); ?> />
												<?php echo gettext('Disable hotlinking'); ?>
											</label>
											<br />
											<label>
												<input type="checkbox" name="cache_full_image" value="1" <?php checked('1', getOption('cache_full_image')); ?> />
												<?php echo gettext('cache the full image'); ?>
											</label>
										</p>

										<input	type="hidden" name="password_enabled" id="password_enabled" value="0" />
										<?php
										if (GALLERY_SECURITY == 'public') {
											?>
											<br class="clearall" />
											<table class="compact">
												<tr class="passwordextrashow">
													<td style="margin:0; padding:0">
														<a href="javascript:toggle_passwords('',true);">
															<?php echo gettext("password:"); ?>
														</a>
													</td>
													<td style="margin:0; padding:0">
														<?php
														$x = getOption('protected_image_password');
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
												</tr>
												<tr class="passwordextrahide" style="display:none">
													<td style="margin:0; padding:0">
														<a href="javascript:toggle_passwords('',false);">
															<?php echo gettext("user:"); ?>
														</a>
													</td>
													<td style="margin:0; padding:0">
														<input type="text" size="30"
																	 class="dirtyignore"
																	 onkeydown="passwordClear('');"
																	 id="user_name" name="user"
																	 value="<?php echo html_encode(getOption('protected_image_user')); ?>" autocomplete="off" />

													</td>
												</tr>
												<tr class="passwordextrahide" style="display:none" >
													<td style="margin:0; padding:0">
														<span id="strength">
															<?php echo gettext("password:"); ?>
														</span>
														<br />
														<span id="match" class="password_field_">
															<?php echo gettext("(repeat)"); ?>
														</span>
													</td>
													<td style="margin:0; padding:0">
														<?php
														// Autofill honeypot hack (hidden password input),
														// needed to prevent "Are you sure?" from tiggering when autofill is enabled in browsers
														// http://benjaminjshore.info/2014/05/chrome-auto-fill-honey-pot-hack.html
														?>
														<input class="dirtyignore" type="password" name="pass" style="display:none;" />
														<input type="password" size="30"
																	 class="dirtyignore"
																	 id="pass" name="pass"
																	 onkeydown="passwordClear('');"
																	 onkeyup="passwordStrength('');"
																	 value="<?php echo $x; ?>" autocomplete="off" />
														<br />
														<span class="password_field_">
															<input type="password" size="30"
																		 class="dirtyignore"
																		 id="pass_r" name="pass_r" disabled="disabled"
																		 onkeydown="passwordClear('');"
																		 onkeyup="passwordMatch('');"
																		 value="<?php echo $x; ?>" autocomplete="off" />
														</span>
														<br />
														<label><input type="checkbox" name="disclose_password" id="disclose_password" onclick="passwordClear(''); togglePassword('');" /><?php echo gettext('Show password'); ?></label>
													</td>
												</tr>
												<tr class="passwordextrahide" style="display:none" >
													<td style="margin:0; padding:0">
														<?php echo gettext("hint:"); ?>
													</td>
													<td style="margin:0; padding:0">
														<?php print_language_string_list(getOption('protected_image_hint'), 'hint', false, NULL, 'hint'); ?>
													</td>
												</tr>
											</table>
											<?php
										}
										?>
										<p>
											<?php
											echo "<select id=\"protect_full_image\" name=\"protect_full_image\">\n";
											$protection = getOption('protect_full_image');
											$list = array(gettext('Protected view') => 'protected', gettext('Download') => 'download', gettext('No access') => 'no-access');
											if ($_zp_conf_vars['album_folder_class'] != 'external') {
												$list[gettext('Unprotected')] = 'unprotected';
											}
											generateListFromArray(array($protection), $list, false, true);
											echo "</select>\n";
											?>
										</p>
									</td>
									<td>
										<p><?php echo gettext("Disabling hotlinking prevents linking to the full image from other domains. If enabled, external links are redirect to the image page. If you are having problems with full images being displayed, try disabling this setting. Hotlinking is not prevented if <em>Full&nbsp;image&nbsp;protection</em> is <em>Unprotected</em> or if the image is cached."); ?></p>
										<p><?php echo gettext("If <em>Cache the full image</em> is checked the full image will be loaded to the cache and served from there after the first reference. <em>Full&nbsp;image&nbsp;protection</em> must be set to <em>Protected&nbsp;view</em> for the image to be cached. However, once cached, no protections are applied to the image."); ?></p>
										<p><?php echo gettext("The <em>user</em>, <em>password</em>, and <em>hint</em> apply to the <em>Download</em> and <em>Protected view</em> level of protection. If there is a password set, the viewer must supply this password to access the image."); ?></p>
										<p><?php echo gettext("Select the level of protection for full sized images. <em>Download</em> forces a download dialog rather than displaying the image. <em>No&nbsp;access</em> prevents a link to the image from being shown. <em>Protected&nbsp;view</em> forces image processing before the image is displayed, for instance to apply a watermark or to check passwords. <em>Unprotected</em> allows direct display of the image."); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext("Use lock image"); ?></td>
									<td>
										<input type="checkbox" name="use_lock_image" value="1"
													 <?php checked('1', getOption('use_lock_image')); ?> />
									</td>
									<td><?php echo gettext("Substitute a <em>lock</em> image for thumbnails of password protected albums when the viewer has not supplied the password. If your theme supplies an <code>images_errors/err-passwordprotected.png</code> image, it will be shown. Otherwise the zenphoto default lock image is displayed."); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext("Metadata"); ?></td>
									<td>
										<div id="resizable">
											<ul id="metadatalist" class="searchchecklist">
												<?php
												$exifstuff = sortMultiArray($_zp_exifvars, 2, false);
												foreach ($exifstuff as $key => $item) {
													$checked_show = $checked_hide = $checked_disabled = '';
													if (!$item[5]) {
														$checked_disabled = ' checked="checked"';
													} else {
														if ($item[3]) {
															$checked_show = ' checked="checked"';
														} else {
															$checked_hide = ' checked="checked"';
														}
													}
													?>
													<li>
														<label><input id="<?php echo $key; ?>_show" name="<?php echo $key; ?>" type="radio"<?php echo $checked_show ?> value="1" /><img src ="images/pass.png" alt="<?php echo gettext('show'); ?>" /></label>
														<label><input id="<?php echo $key; ?>_hide" name="<?php echo $key; ?>" type="radio"<?php echo $checked_hide ?> value="0" /><img src ="images/reset.png" alt="<?php echo gettext('hide'); ?>" /></label>
														<label><input id="<?php echo $key; ?>_disable" name="<?php echo $key; ?>" type="radio"<?php echo $checked_disabled ?> value="2" /><img src ="images/fail.png" alt="<?php echo gettext('disabled'); ?>" /></label>
														<?php echo $item[2]; ?>&nbsp;&nbsp;&nbsp;
													</li>
													<?php
												}
												?>
											</ul>
										</div>
									</td>
									<td>
										<p>
											<?php echo gettext("Select how image metadata fields are handled."); ?>
										<ul style="list-style: none;">
											<li><img src ="images/pass.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('Show the field and import data'); ?></li>
											<li><img src ="images/reset.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('Hide the field but import data'); ?></li>
											<li><img src ="images/fail.png" alt="<?php echo gettext('show'); ?>" /><?php echo gettext('No display and import'); ?></li>
										</ul>
										</p>
										<p class="warningbox">
											<?php echo gettext('<strong>Important:</strong> The "Refresh metadata" utility which is accessible from the backend "Overview" page, every album-edit page and every image-edit page, will overwrite all manually added metadata with metadata embedded in the image/album for all fields enabled. This cannot be undone!'); ?>
										</p>
									</td>
								</tr>
								<?php
								$sets = array_merge($_zp_utf8->iconv_sets, $_zp_utf8->mb_sets);
								ksort($sets, SORT_LOCALE_STRING);
								if (!empty($sets)) {
									?>
									<tr>
										<td><?php echo gettext("IPTC encoding:"); ?></td>
										<td>
											<select id="IPTC_encoding" name="IPTC_encoding">
												<?php generateListFromArray(array(getOption('IPTC_encoding')), array_flip($sets), false, true) ?>
											</select>
										</td>
										<td>
										<p><?php echo gettext("The default character encoding of image IPTC metadata."); ?></p>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> If you notice unexpected behaviour, especially with non-ASCII characters (Cyrillic for example), try again with the <code>xmpMetadata</code> plugin enabled.'); ?>
										</p>
										</td>
									</tr>
									<?php
								}
        ?>
									<tr>
											<td><?php echo gettext("Metadata refresh behaviour:"); ?></td>
											<td>
												<?php $metarefresh_behaviour = getOption('metadata_refresh_behaviour'); ?>
												<ul class="optionlist">
													<li><label><input type="radio" name="metadata_refresh_behaviour" value="full-refresh"	<?php checked('full-refresh', $metarefresh_behaviour); ?> /> <?php echo gettext('Full metadata refresh'); ?></label></li>
													<li><label><input type="radio" name="metadata_refresh_behaviour" value="skip-title-and-desc" <?php checked('skip-title-and-desc', $metarefresh_behaviour); ?> /> <?php echo gettext('Skip title and description admin fields'); ?></label></li>
													<li><label><input type="radio" name="metadata_refresh_behaviour" value="metadata-fields-only"	<?php checked('metadata-fields-only', $metarefresh_behaviour); ?> /> <?php echo gettext('Skip all admin fields'); ?></label></li>
												</ul>
											</td>
											
											<td><?php echo gettext("Image metadata like EXIF or IPTC data is stored in separate database columns and have no interface to edit. By default a metadata refresh also sets various fields like the title, description or extra fields like location, state etc. based on this data. Date and tags are always updated."); ?></td>
										</tr>		
										
										
										<tr>
										<td><?php echo gettext("IPTC caption linebreaks:"); ?></td>
										<td>
           <label><input type="checkbox" name="IPTC_convert_linebreaks" value="1"	<?php checked('1', getOption('IPTC_convert_linebreaks')); ?> /></label>
										</td>
										<td><?php echo gettext("If checked line breaks embeded in the IPTCcaption field will be converted to <code>&lt;br&gt;</code> on image importing."); ?></td>
									</tr>

								<tr>
									<td><?php echo gettext('Image copyright notice'); ?></td>
									<td>
										<p><?php print_language_string_list(getOption('copyright_image_notice'), 'copyright_image_notice'); ?> <?php echo gettext('Notice'); ?></p>

									</td>
									<td>
										<p><?php echo gettext('The notice will be used by the html_meta_tags plugin. If not set the image meta data is tried instead.'); ?></p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Image Copyright URL'); ?></td>
									<td>
									<?php printZenpagePageSelector('copyright_image_url', 'copyright_image_url_custom', false); ?>
									</td>
									<td>
										<p><?php echo gettext('Choose a Zenpage page or define a custom URL. The URL maybe used to point to some specific copyright info source. Must be an absolute URL address of the form: http://mydomain.com/license.html.'); ?></p>
									</td>
								</tr>

								<tr>
									<td><?php echo gettext("Display Image copyright notice"); ?></td>
									<td>
										<label><input type="checkbox" name="display_copyright_image_notice" id="display_copyright_image_notice" value="1" <?php checked('1', getOption('display_copyright_image_notice')); ?> /> <?php echo gettext('Enable'); ?></label>
									</td>
									<td><?php echo gettext("Enable to display the image copyright notice. This may usually be in the theme below the image but is up to the theme where and if used at all.."); ?></td>
								</tr>

								<tr>
									<td><?php echo gettext('Image copyright rightsholder'); ?></td>
									<td>
										<?php printUserSelector('copyright_image_rightsholder','copyright_image_rightsholder_custom', 'users'); ?>
									</td>
									<td>
										<p><?php echo gettext('The rights holder will be used by the html_meta_tags plugin. If set to <em>none</em> the image metadata fields "copyright" or "owner" are used as fallbacks, if available.'); ?></p>
									</td>
								</tr>



								<tr>
									<?php
											if (GRAPHICS_LIBRARY == 'Imagick') {
												$optionText = gettext('Embed IPTC copyright');
												$desc = gettext('If checked and an image has no IPTC data a copyright notice will be embedded in cached copies.');
											} else {
												$optionText = gettext('Replicate IPTC metadata');
												$desc = gettext('If checked IPTC data from the original image will be embedded in cached copies. If the image has no IPTC data a copyright notice will be embedded. (The text supplied will be used if the orginal image has no copyright.)');
											}
										?>
									<td><?php echo gettext('IPTC copyright embedding'); ?></td>
									<td>
										<p><label><input type="checkbox" name="EmbedIPTC" value="1"	<?php checked('1', getOption('EmbedIPTC')); ?> /> <?php echo $optionText; ?></label></p>
									</td>
									<td>
										<p><?php echo $desc; ?></p>
										<p class="notebox">
											<?php echo gettext('<strong>NOTE:</strong> This option applies only to JPEG format cached images.'); ?>
										</p>
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