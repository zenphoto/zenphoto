<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package plugins
 * @subpackage cachemanager
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH . '/' . ZENFOLDER . '/functions-image.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');


if (isset($_REQUEST['album'])) {
	$localrights = ALBUM_RIGHTS;
} else {
	$localrights = NULL;
}
admin_securityChecks($localrights, $return = currentRelativeURL());

if (isset($_GET['action'])) {
	$action = sanitize($_GET['action']);
	if ($action == 'cleanup_cache_sizes') {
		XSRFdefender('CleanupCacheSizes');
		cacheManager::cleanupCacheSizes();
		$report = gettext('Image cache sizes cleaned up.');
		header('location:' . FULLWEBPATH .'/'. ZENFOLDER. '/admin.php?action=external&msg=' . $report);
		exitZP();
	}
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
	$zenphoto_tabs['overview']['subtabs'] = array(gettext('Cache images') => PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&tab=images',
			gettext('Cache stored images') => PLUGIN_FOLDER . '/cacheManager/cacheDBImages.php?page=overview&tab=DB&XSRFToken=' . getXSRFToken('cacheDBImages'));
}
$_zp_cachemanager_sizes = cacheManager::getSizes('active');

if (isset($_GET['select']) && isset($_POST['enable'])) {
	XSRFdefender('cacheImages');
	$enabled_sizes = sanitize($_POST['enable']);
	if(!is_array($enabled_sizes) || empty($enabled_sizes)) {
		$enabled_sizes = array();
	}
	$_zp_cachemanager_enabledsizes = $enabled_sizes;
} else {
	$_zp_cachemanager_enabledsizes = array();
}
printAdminHeader('overview', 'images'); ?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id = "main">
<?php printTabs(); ?>
<div id = "content">
<?php printSubtabs(); ?>
<div class="tabbox">
	<?php
	zp_apply_filter('admin_note', 'cache', '');
	$clear = sprintf(gettext('Refreshing cache for %s'), $object);

	if ($alb) {
		$returnpage = '/admin-edit.php?page = edit&album = ' . $alb;
		echo "\n<h2>" . $clear . "</h2>";
	} else {
		$returnpage = '/admin.php';
		echo "\n<h2>" . $clear . "</h2>";
	}

	$cachesizes = 0;
	$currenttheme = $_zp_gallery->getCurrentTheme();
	$themes = array();
	foreach ($_zp_gallery->getThemes() as $theme => $data) {
		$themes[$theme] = $data['name'];
	}
	$last = '';
	cacheManager::printJS();
	cacheManager::printCurlNote();
	echo gettext('This tool searches uncached image sizes from your albums or within theme or plugin. If uncached images sizes you can have this tool generate these. Note that this is a quite time consuming measure depending on the size of your albums and the power of your server.')
	?>
	<form class="dirty-check clearfix" name="size_selections" action="?select&album=<?php echo $alb; ?>" method="post" autocomplete="off">
			<?php XSRFToken('cacheImages') ?>
		<ol class="no_bullets">
			<?php
			$defaultsizes = array(
					array(
							'option' => 'cache_full_image', 
							'key' => '*', 
							'text' => gettext('Full Image')),
					array(
							'option' => 'cachemanager_defaultthumb', 
							'key' => 'defaultthumb', 
							'text' => gettext('Default thumb size (or manual crop)')),
					array(
							'option' => 'cachemanager_defaultsizedimage', 
							'key' => 'defaultsizedimage', 
							'text' => gettext('Default sized image size'))
			);
			foreach($defaultsizes as $defaultsize) {
				if (getOption($defaultsize['option']) && (empty($_zp_cachemanager_enabledsizes) || array_key_exists($defaultsize['key'], $_zp_cachemanager_enabledsizes))) {
					if (!empty($_zp_cachemanager_enabledsizes)) {
						$checked = ' checked="checked" disabled="disabled"';
					} else {
						if(in_array($defaultsize['key'], array('defaultthumb', 'defaultsizedimage'))) {
							$checked = ' checked="checked"';
						} else {
							$checked = '';
						}
					}
					$cachesizes++;
					cacheManager::printSizesListEntry($defaultsize['key'], $checked, $defaultsize['text']);
				}
			}
			$seen = array();
			foreach ($_zp_cachemanager_sizes as $key => $cacheimage) {
				if ((empty($_zp_cachemanager_enabledsizes) || array_key_exists($key, $_zp_cachemanager_enabledsizes))) {
					$checked = '';
					if (array_key_exists($key, $_zp_cachemanager_enabledsizes)) {
						$checked = ' checked="checked" disabled="disabled"';
					} else {
						if ($currenttheme == $cacheimage['theme'] || $cacheimage['theme'] == 'admin') {
							$checked = ' checked="checked"';
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
							$checked .= ' disabled="disabled"';
						}
					}
					$themeid = $theme = $cacheimage['theme'];
					if (isset($themes[$theme])) {
						$themeid = $themes[$theme];
					}
					if ($theme != $last && empty($_zp_cachemanager_enabledsizes)) {
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
							<input type="checkbox" name="<?php echo $theme; ?>" id="<?php echo $theme; ?>" value="" onclick="checkTheme('<?php echo $theme; ?>');"<?php echo $checked; ?> /> <?php printf(gettext('all sizes for <i>%1$s</i>'), $themeid); ?>
						</label>
						<span id="<?php echo $theme; ?>_list" style="display:none">
							<ol class="no_bullets">
								<?php
							}
							$show = true;
							if (!empty($_zp_cachemanager_enabledsizes)) {
								if (array_key_exists($postfix, $seen)) {
									$show = false;
									unset($_zp_cachemanager_sizes[$key]);
								}
								$seen[$postfix] = true;
							}
							if ($show) {
								cacheManager::printSizesListEntry($key, $checked, ltrim($postfix, '_'), $theme);
							}
						}
					}
					if (empty($_zp_cachemanager_enabledsizes)) {
						?>
					</ol>
				</span>
			</li>
			<?php
		}
		?>
		</ol>
		<?php
		$button = false;
		if (!empty($_zp_cachemanager_enabledsizes)) {
			if ($cachesizes) {
				?>
				<p><?php printf(ngettext('%u cache size to apply.', '%u cache sizes to apply.', $cachesizes), $cachesizes); ?></p>
				<script>
						var starttime = Date.now();
						var endtime = 0;
						var totaltime = 0;
				</script>
				<hr>
				<?php
				$allalbums = array();
				if($alb) {
					$currentalbum = newAlbum($alb);
					genAlbumList($allalbums, $currentalbum); //get subalbums if available
					$allalbums[$alb] = $currentalbum->getTitle(); // album itself
				} else {
					genAlbumList($allalbums);
				}
				$images_count_total = 0;
				$images_sizes_total = 0;
				$albums_count_total = 0;
				foreach ($allalbums as $key => $value) {
					$album = newAlbum($key);
					if (!$album->isDynamic()) {
						$data = cacheManager::loadAlbum($album, true); 
						if($data['images_count'] !== 0) {
							$albums_count_total++;
						}
						$images_count_total = $images_count_total + $data['images_count'];
						$images_sizes_total = $images_sizes_total + $data['sizes_count'];
					}
				}
				?>
				<div class="imagecaching_progress">
					<h2 class="imagecaching_headline"><?php echo gettext('Image caching in progress.'); ?></h2>
					<div class="notebox">
						<p><?php echo gettext('Please be patient as this might take quite a while! It depends on the number of images to pre-cache, their dimensions and the power of your server.'); ?></p>
						<p><?php echo gettext('If you move away from this page before this loader disapeared, the caching will be incomplete but you can re-start any time later.'); ?></p>
					</div>
					<img class="imagecaching_loader" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/ajax-loader.gif" alt="">
					<ul>	
						<li><?php echo gettext('Image cache sizes generated: '); ?><span class="imagecaching_imagesizes">0</span>/<span><?php echo $images_sizes_total; ?></span></li>
						<li><?php echo gettext('Images processed: '); ?><span class="imagecaching_imagecount">0</span>/<span><?php echo $images_count_total; ?></span></li>
						<li><?php echo gettext('Albums processed: '); ?><span class="imagecaching_albumcount">0</span>/<span><?php echo $albums_count_total; ?></span></li>
						<li><?php echo gettext('Processing time: '); ?><span class="imagecaching_time">0</span> <?php echo gettext('minutes'); ?></li>
					</ul>
				</div>
				<?php cacheManager::printButtons($returnpage, $alb, true); ?>
				<hr>
				<h2><?php echo gettext('Caching log'); ?></h2>
				<?php
				$starttime = time();
				$images_count = 0;
				$images_sizes = 0;
				$albums_count = 0;
				?>
				<ol>
				<?php
				foreach ($allalbums as $key => $value) {
					$album = newAlbum($key);
					if (!$album->isDynamic()) {
						?>
						<li><strong><?php echo html_encode($value); ?></strong> (<?php echo html_encode($key); ?>)
							<ul>
								<?php 
								$data = cacheManager::loadAlbum($album); 
								if($data['images_count'] !== 0) {
									$albums_count++;
								}
								$images_count = $images_count + $data['images_count'];
								$images_sizes = $images_sizes + $data['sizes_count'];
								$endtime_temp = time();
								$time_total_temp = ($endtime_temp - $starttime) / 60;
								?>
							</ul>
						</li>
						<script>
							$('.imagecaching_imagecount').text(<?php echo $images_count; ?>);
							$('.imagecaching_imagesizes').text(<?php echo $images_sizes; ?>);
							$('.imagecaching_albumcount').text(<?php echo $albums_count; ?>);
							$('.imagecaching_time').text(<?php echo round($time_total_temp, 2); ?>);
						</script>
						<?php
					}
				}
				?>
				</ol>
				<?php
				$endtime = time();
				$time_total = ($endtime - $starttime) / 60;
				?>
				<p><strong><?php echo gettext('Caching done!'); ?></strong></p>
				<script>
					$( document ).ready(function() {
						$('.imagecaching_progress').addClass('messagebox');
						$('.imagecaching_headline').text('<?php echo gettext('Caching done!'); ?>');
						$('.imagecaching_progress .notebox, .imagecaching_loader').remove();
						$('.imagecaching_imagecount').text(<?php echo $images_count; ?>);
						$('.imagecaching_imagesizes').text(<?php echo $images_sizes; ?>);
						$('.imagecaching_albumcount').text(<?php echo $albums_count; ?>);
						$('.imagecaching_time').text(<?php echo round($time_total, 2); ?>);
						$('.buttons_cachefinished').removeClass('hidden');
					});
				</script>
				<?php
			} else {
				$button = false;
				?>
				<p><?php echo gettext('No cache sizes enabled.'); ?></p>
				<?php
			}
		} else {
			$button = array('text' => gettext("Cache the images"), 'title' => gettext('Executes the caching of the selected image sizes.'));
		}
		cacheManager::printButtons($returnpage, $alb, true);
		if ($button) {
			?>
			<p class="buttons clearfix">
				<button class="tooltip" type="submit" title="<?php echo $button['title']; ?>" >
					<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
			<?php echo $button['text']; ?>
				</button>
			</p>
			<?php
		}
		?>
	</form>

</div>
</div>
</div>
<?php printAdminFooter(); ?>

</body>
</html>


