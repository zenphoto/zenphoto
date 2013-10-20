<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package plugins
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');


if (isset($_REQUEST['album'])) {
	$localrights = ALBUM_RIGHTS;
} else {
	$localrights = NULL;
}
admin_securityChecks($localrights, $return = currentRelativeURL());

function loadAlbum($album) {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery, $custom, $enabled;
	$subalbums = $album->getAlbums();
	$started = false;
	$tcount = $count = 0;
	foreach ($subalbums as $folder) {
		$subalbum = newAlbum($folder);
		if (!$subalbum->isDynamic()) {
			$tcount = $tcount + loadAlbum($subalbum);
		}
	}
	$theme = $_zp_gallery->getCurrentTheme();
	$id = 0;
	$parent = getUrAlbum($album);
	$albumtheme = $parent->getAlbumTheme();
	if (!empty($albumtheme)) {
		$theme = $albumtheme;
		$id = $parent->getID();
	}
	loadLocalOptions($id, $theme);
	$_zp_current_album = $album;

	if ($album->getNumImages() > 0) {
		echo "<br />" . $album->name . ' ';
		while (next_image(true)) {
			if (isImagePhoto($_zp_current_image)) {
				$countit = 0;
				if (in_array('*', $enabled)) {
					$uri = getFullImageURL(NULL, 'Protected view');
					if (strpos($uri, 'full-image.php?') !== false) {
						if (!($count + $countit)) {
							echo "{ ";
						} else {
							echo ' | ';
						}
						$countit = 1;
						?>
						<a href="<?php echo html_encode($uri); ?>&amp;debug">
							<?php
							echo '<img src="' . html_encode(pathurlencode($uri)) . '" height="30" width="30" alt="X" />' . "\n";
							?>
						</a>
						<?php
					}
				}

				foreach ($custom as $key => $cacheimage) {
					if (in_array($key, $enabled)) {
						$size = isset($cacheimage['image_size']) ? $cacheimage['image_size'] : NULL;
						$width = isset($cacheimage['image_width']) ? $cacheimage['image_width'] : NULL;
						$height = isset($cacheimage['image_height']) ? $cacheimage['image_height'] : NULL;
						$thumbstandin = isset($cacheimage['thumb']) ? $cacheimage['thumb'] : NULL;
						if ($special = ($thumbstandin === true)) {
							list($special, $cw, $ch, $cx, $cy) = $_zp_current_image->getThumbCropping($size, $width, $height);
						}
						if (!$special) {
							$cw = isset($cacheimage['crop_width']) ? $cacheimage['crop_width'] : NULL;
							$ch = isset($cacheimage['crop_height']) ? $cacheimage['crop_height'] : NULL;
							$cx = isset($cacheimage['crop_x']) ? $cacheimage['crop_x'] : NULL;
							$cy = isset($cacheimage['crop_y']) ? $cacheimage['crop_y'] : NULL;
						}
						$effects = isset($cacheimage['gray']) ? $cacheimage['gray'] : NULL;
						$passedWM = isset($cacheimage['wmk']) ? $cacheimage['wmk'] : NULL;
						if (isset($cacheimage['maxspace'])) {
							getMaxSpaceContainer($width, $height, $_zp_current_image, $thumbstandin);
						}
						$args = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
						$args = getImageParameters($args, $album->name);
						$uri = getImageURI($args, $album->name, $_zp_current_image->filename, $_zp_current_image->filemtime);
						if (strpos($uri, 'i.php?') !== false) {
							if (!($count + $countit)) {
								echo "{ ";
							} else {
								echo ' | ';
							}
							$countit = 1;
							?>
							<a href="<?php echo html_encode($uri); ?>&amp;debug">
								<?php
								if ($thumbstandin) {
									echo '<img src="' . html_encode(pathurlencode($uri)) . '" height="8" width="8" alt="x" />' . "\n";
								} else {
									echo '<img src="' . html_encode(pathurlencode($uri)) . '" height="20" width="20" alt="X" />' . "\n";
								}
								?>
							</a>
							<?php
						}
					}
				}
				$count = $count + $countit;
			}
		}
		if ($count)
			echo ' } ';
		printf(ngettext('[%u image]', '[%u images]', $count), $count);
		echo "<br />\n";
	}
	return $count + $tcount;
}

if (isset($_GET['album'])) {
	$alb = sanitize($_GET['album']);
} else if (isset($_POST['album'])) {
	$alb = sanitize(urldecode($_POST['album']));
} else {
	$alb = '';
}
if ($alb) {
	$folder = sanitize_path($alb);
	$object = $folder;
	$tab = 'edit';
	$album = newAlbum($folder);
	if (!$album->isMyItem(ALBUM_RIGHTS)) {
		if (!zp_apply_filter('admin_managed_albums_access', false, $return)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exitZP();
		}
	}
} else {
	$object = '<em>' . gettext('Gallery') . '</em>';
	$zenphoto_tabs['overview']['subtabs'] = array(gettext('Cache images')				 => PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&amp;tab=images',
					gettext('Cache stored images') => PLUGIN_FOLDER . '/cacheManager/cacheDBImages.php?page=overview&amp;tab=DB&amp;XSRFToken=' . getXSRFToken('cacheDBImages'));
}
$custom = array();

$result = query('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" ORDER BY `aux`');
while ($row = db_fetch_assoc($result)) {
	$row = unserialize($row['data']);
	$custom[] = $row;
}
$custom = sortMultiArray($custom, array('theme', 'thumb', 'image_size', 'image_width', 'image_height'));

if (isset($_GET['select'])) {
	XSRFdefender('cacheImages');
	$enabled = $_POST['enable'];
} else {
	$enabled = false;
}

printAdminHeader('overview', 'images');
echo "\n</head>";
echo "\n<body>";

printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';
?>
<?php printSubtabs(); ?>
<div class="tabbox">


	<?php
	zp_apply_filter('admin_note', 'cache', '');
	$clear = sprintf(gettext('Refreshing cache for %s'), $object);
	$count = 0;

	if ($alb) {
		$r = '/admin-edit.php?page=edit&album=' . $alb;
		echo "\n<h2>" . $clear . "</h2>";
	} else {
		$r = '/admin.php';
		echo "\n<h2>" . $clear . "</h2>";
	}

	$cachesizes = 0;
	$currenttheme = $_zp_gallery->getCurrentTheme();
	$themes = array();
	foreach ($_zp_gallery->getThemes() as $theme => $data) {
		$themes[$theme] = $data['name'];
	}
	$last = '';
	?>
	<script type="text/javascript">
		//<!-- <![CDATA[
		function checkTheme(theme) {
			$('.' + theme).prop('checked', $('#' + theme).prop('checked'));
		}
		function showTheme(theme) {
			html = $('#' + theme + '_arrow').html();
			if (html.match(/down/)) {
				html = html.replace(/_down/, '_up');
				html = html.replace(/title="<?php echo gettext('Show'); ?>/, 'title="<?php echo gettext('Hide'); ?>"');
				$('#' + theme + '_list').show();
			} else {
				html = html.replace(/_up/, '_down');
				html = html.replace(/title="<?php echo gettext('Hide'); ?>/, 'title="<?php echo gettext('Show'); ?>"');
				$('#' + theme + '_list').hide();
			}
			$('#' + theme + '_arrow').html(html);
		}
		//]]> -->
	</script>
	<form name="size_selections" action="?select&album=<?php echo $alb; ?>" method="post">
		<?php XSRFToken('cacheImages') ?>
		<ol class="no_bullets">
			<?php
			if (getOption('cache_full_image') && (!is_array($enabled) || in_array('*', $enabled))) {
				if (is_array($enabled)) {
					$checked = ' checked="checked" disabled="disabled"';
				} else {
					$checked = '';
				}
				$cachesizes++;
				?>
				<li>
					<?php
					if (!is_array($enabled)) {
						?>
						<span class="icons" id="<?php echo $theme; ?>_arrow">
							<img class="icon-position-top4" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/place_holder_icon.png'; ?>" alt="" />
						</span>
						<?php
					}
					?>
					<label>
						<input type="checkbox" name="enable[]" value="*" <?php echo $checked; ?> />
						<?php echo gettext('Apply'); ?> <code><?php echo gettext('Full Image'); ?></code>
					</label>
				</li>
				<?php
			}

			$seen = array();
			foreach ($custom as $key => $cacheimage) {
				if (!is_array($enabled) || in_array($key, $enabled)) {
					if (is_array($enabled)) {
						$checked = ' checked="checked" disabled="disabled"';
					} else {
						if ($currenttheme == $cacheimage['theme'] || $cacheimage['theme'] == 'admin') {
							$checked = ' checked="checked"';
						} else {
							$checked = '';
						}
					}
					$cachesizes++;
					$size = isset($cacheimage['image_size']) ? $cacheimage['image_size'] : NULL;
					$width = isset($cacheimage['image_width']) ? $cacheimage['image_width'] : NULL;
					$height = isset($cacheimage['image_height']) ? $cacheimage['image_height'] : NULL;
					$cw = isset($cacheimage['crop_width']) ? $cacheimage['crop_width'] : NULL;
					$ch = isset($cacheimage['crop_height']) ? $cacheimage['crop_height'] : NULL;
					$cx = isset($cacheimage['crop_x']) ? $cacheimage['crop_x'] : NULL;
					$cy = isset($cacheimage['crop_y']) ? $cacheimage['crop_y'] : NULL;
					$thumbstandin = isset($cacheimage['thumb']) ? $cacheimage['thumb'] : NULL;
					$effects = isset($cacheimage['gray']) ? $cacheimage['gray'] : NULL;
					$passedWM = isset($cacheimage['wmk']) ? $cacheimage['wmk'] : NULL;
					$args = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
					$postfix = getImageCachePostfix($args);
					if (isset($cacheimage['maxspace']) && $cacheimage['maxspace']) {
						if ($width && $height) {
							$postfix = str_replace('_w', '_wMax', $postfix);
							$postfix = str_replace('_h', '_hMax', $postfix);
						} else {
							$postfix = '_' . gettext('invalid_MaxSpace');
							$checked = ' disabled="disabled"';
						}
					}
					$themeid = $theme = $cacheimage['theme'];
					if (isset($themes[$theme])) {
						$themeid = $themes[$theme];
					}
					if ($theme != $last && !is_array($enabled)) {
						if ($last) {
							?>
						</ol>
						</li>
						<?php
					}
					$last = $theme;
					?>
					<li>
						<span class="icons" id="<?php echo $theme; ?>_arrow">
							<a href="javascript:showTheme('<?php echo $theme; ?>');" title="<?php echo gettext('Show'); ?>">
								<img class="icon-position-top4" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/arrow_down.png'; ?>" alt="" />
							</a>
						</span>
						<label>
							<input type="checkbox" name="<?php echo $theme; ?>" id="<?php echo $theme; ?>" value="" onclick="checkTheme('<?php echo $theme; ?>');"<?php echo $checked; ?> /><?php printf(gettext('all sizes for <i>%1$s</i>'), $themeid); ?>
						</label>
						<span id="<?php echo $theme; ?>_list" style="display:none">
							<ol class="no_bullets">
								<?php
							}
							$show = true;
							if (is_array($enabled)) {
								if (array_key_exists($postfix, $seen)) {
									$show = false;
									unset($custom[$key]);
								}
								$seen[$postfix] = true;
							}
							if ($show) {
								?>
								<li>
									<?php
									if (is_array($enabled)) {
										?>
										<input type="hidden" name="enable[]" value="<?php echo $key; ?>" />
										<?php
									}
									?>
									<label>
										<input type="checkbox" name="enable[]" class="<?php echo $theme; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?> />
										<?php echo gettext('Apply'); ?> <code><?php echo ltrim($postfix, '_'); ?></code>
									</label>
								</li>
								<?php
							}
						}
					}
					if (!is_array($enabled)) {
						?>
					</ol>
				</span>
			</li>
			<?php
		}
		?>
		</ol>
		<?php
		if (is_array($enabled)) {
			if ($cachesizes) {
				echo '<p>';
				printf(ngettext('%u cache size to apply.', '%u cache sizes to apply.', $cachesizes), $cachesizes);
				echo '</p>';
				if ($alb) {
					$album = newAlbum($folder);
					$count = loadAlbum($album);
				} else {
					$albums = $_zp_gallery->getAlbums();
					shuffle($albums);
					foreach ($albums as $folder) {
						$album = newAlbum($folder);
						if (!$album->isDynamic()) {
							$count = $count + loadAlbum($album);
						}
					}
				}
				$partb = sprintf(ngettext('%u cache size requested', '%u cache sizes requested', $count * $cachesizes), $count * $cachesizes);
				echo "\n" . "<br />" . sprintf(ngettext('Finished processing %1$u image (%2$s).', 'Finished processing %1$u images (%2$s).', $count), $count, $partb);
				if ($count) {
					$button = array('text' => gettext("Refresh"), 'title' => gettext('Refresh the caching of the selected image sizes if some images did not render.'));
				} else {
					$button = false;
				}
			} else {
				$button = false;
				?>
				<p><?php echo gettext('No cache sizes enabled.'); ?></p>';
				<?php
			}
		} else {
			$button = array('text' => gettext("Cache the images"), 'title' => gettext('Executes the caching of the selected image sizes.'));
		}
		?>
		<p class="buttons">
			<a href="<?php echo WEBPATH . '/' . ZENFOLDER . $r; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" />
				<strong><?php echo gettext("Back"); ?> </strong>
			</a>
		</p>
		<?php
		if ($button) {
			?>
			<p class="buttons">
				<button class="tooltip" type="submit" title="<?php echo $button['title']; ?>" >
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/redo.png" alt="" />
					<?php echo $button['text']; ?>
				</button>
			</p>
			<?php
		}
		?>
		<br class="clearall" />
	</form>

	<?php
	echo "\n" . '</div>';
	echo "\n" . '</div>';
	echo "\n" . '</div>';

	printAdminFooter();

	echo "\n</body>";
	echo "\n</head>";
	?>
