<?php
/**
 *
 * This plugin is the centralized Cache manager for Zenphoto.
 *
 * It provides:
 * <ul>
 * 		<li>Options to purge the HTML and RSS caches on publish state changes of:
 * 			<ul>
 * 				<li>albums</li>
 * 				<li>images</li>
 * 				<li>news articles</li>
 * 				<li>pages</li>
 * 			</ul>
 * 		</li>
 * 		<li><i>pre-creating</i> the Image cache images</li>
 * 		<li>utilities for purging Image, HTML, and RSS caches</li>
 * </ul>
 *
 * Image cache <i>pre-creating</i> will examine the gallery and make image references to any images which have not
 * already been cached. Your browser will then request these images causing the caching process to be
 * executed.
 *
 * The Zenphoto distributed themes have created <i>Caching</i> size options
 * for the images sizes they use.
 *
 *
 * <b>Notes:</b>
 * <ul>
 * 		<li>
 * 			Setting theme options or installing a new version of Zenphoto will re-create these caching sizes.
 * 			Use a different <i>theme name</i> for custom versions that you create. If you set image options that
 * 			impact the default caching you will need to re-create these caching sizes by one of the above methods.
 * 		</li>
 * 		<li>
 * 			The <i>pre-creating</i> process will cause your browser to display each and every image that has not
 * 			been previously cached. If your server does not do a good job of thread management this may swamp
 * 			it! You should probably also clear your browser cache before using this utility. Otherwise
 * 			your browser may fetch the images locally rendering the above process useless.
 * 		</li>
 * 		<li>
 * 			You may have to refresh the page multiple times until the report of the number of images cached is zero.
 * 			If some images seem to never be rendered you may be experiencing memory limit or other graphics processor
 * 			errors. You can click on the image that does not render to get the <var>i.php</var> debug screen for the
 * 			image. This may help in figuring out what has gone wrong.
 * 		</li>
 * 		<li>
 * 			Caching sizes shown on the <var>cache images</var> tab will be identified
 * 			with the same post-fixes as the image names in your cache folders. Some examples
 * 			are shown below:
 * 			<ul>
 * 					<li>
 * 					<var>_595</var>: sized to 595 pixels
 * 				</li>
 * 				<li>
 * 					<var>_w180_cw180_ch80_thumb</var>: a size of 180px wide and 80px high
 * 							and it is a thumbnail (<var>thumb</var>)
 * 				</li>
 * 				<li>
 * 					<var>_85_cw72_ch72_thumb_copyright_gray</var>: sized 85px cropped at about
 * 							7.6% (one half of 72/85) from the horizontal and vertical sides with a
 * 							watermark (<var>copyright</var>) and rendered in grayscale (<var>gray</var>)
 * 				</li>
 * 				<li>
 * 					<var>_w85_h85_cw350_ch350_cx43_cy169_thumb_copyright</var>: a custom cropped 85px
 * 						thumbnail with watermark.
 * 				</li>
 * 			</ul>
 *
 * 			If a field is not represented in the cache size, it is not applied.
 *
 * 			Custom crops (those with cx and cy)
 * 			really cannot be cached easily since each image has unique values. See the
 * 			<i>template-functions</i>::<var>getCustomImageURL()</var> comment block
 * 			for details on these fields.
 * 		</li>
 * </ul>
 *
 *
 * @package plugins
 * @subpackage cachemanager
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides cache management utilities for Image, HTML, and RSS caches.");
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_category = gettext('Admin');

$option_interface = 'cacheManager';

require_once(SERVERPATH . '/' . ZENFOLDER . '/class-feed.php');

zp_register_filter('admin_utilities_buttons', 'cacheManager::overviewbutton');
zp_register_filter('edit_album_utilities', 'cacheManager::albumbutton', -9999);
zp_register_filter('show_change', 'cacheManager::published');

$_zp_cached_feeds = array('RSS'); //	Add to this array any feed classes that need cache clearing

/**
 * Fake feed descendent class so we can use the feed::clearCache()
 */
class cacheManagerFeed extends feed {

	protected $feed = NULL;

	function __construct($feed) {
		$this->feed = $feed;
	}

}

/**
 *
 * Standard options interface
 * @author Stephen
 *
 */
class cacheManager {

	function __construct() {
		self::deleteCacheSizes('admin');
		self::addCacheSize('admin', 40, NULL, NULL, 40, 40, NULL, NULL, -1);
		self::addCacheSize('admin', 80, NULL, NULL, 80, 80, NULL, NULL, -1);
		setOptionDefault('cachemanager_defaultthumb', 1);
		setOptionDefault('cachemanager_defaultsizedimage', 1);
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		$options = array(
				gettext('Image caching sizes') => array(
						'key' => 'cropImage_list',
						'type' => OPTION_TYPE_CUSTOM,
						'order' => 1,
						'desc' => '<p>' .
						gettext('Cropped images will be made in these parameters if the <em>Create image</em> box is checked. Un-check to box to remove the settings.' .
										'You can determine the values for these fields by examining your cached images. The file names will look something like these:') .
						'<ul>' .
						'<li>' . gettext('<code>photo_595.jpg</code>: sized to 595 pixels') . '</li>' .
						'<li>' . gettext('<code>photo_w180_cw180_ch80_thumb.jpg</code>: a size of 180px wide and 80px high and it is a thumbnail (<code>thumb</code>)') . '</li>' .
						'<li>' . gettext('<code>photo_85_cw72_ch72_thumb_copyright_gray.jpg</code>: sized 85px cropped at about 7.6% (one half of 72/85) from the horizontal and vertical sides with a watermark (<code>copyright</code>) and rendered in grayscale (<code>gray</code>).') . '</li>' .
						'<li>' . gettext('<code>photo_w85_h85_cw350_ch350_cx43_cy169_thumb_copyright.jpg</code>: a custom cropped 85px thumbnail with watermark.') . '</li>' .
						'</ul>' .
						'</p>' .
						'<p>' .
						gettext('If a field is not represented in the cached name, leave the field blank. Custom crops (those with cx and cy) really cannot be cached easily since each image has unique values. ' .
										'See the <em>template-functions</em>::<code>getCustomImageURL()</code> comment block for details on these fields.') .
						'</p>' .
						'<p>' .
						gettext('Some themes use <em>MaxSpace</em> image functions. To cache images referenced by these functions set the <em>width</em> and <em>height</em> parameters to the <em>MaxSpace</em> container size and check the <code>MaxSpace</code> checkbox.') .
						'</p>'
				)
		);
		$list = array(
				'<em>' . gettext('Albums') . '</em>' => 'cacheManager_albums', 
				'<em>' . gettext('Images') . '</em>' => 'cacheManager_images');
		if (extensionEnabled('zenpage')) {
			$list['<em>' . gettext('News') . '</em>'] = 'cacheManager_news';
			$list['<em>' . gettext('Pages') . '</em>'] = 'cacheManager_pages';
		} else {
			setOption('cacheManager_news', 0);
			setOption('cacheManager_pages', 0);
		}
		$options[gettext('Purge cache files')] = array(
				'key' => 'cacheManager_items',
				'type' => OPTION_TYPE_CHECKBOX_ARRAY,
				'order' => 0,
				'checkboxes' => $list,
				'desc' => gettext('If a <em>type</em> is checked, the HTML and RSS caches for the item will be purged when the published state of an item of <em>type</em> changes.') .
				'<div class="notebox">' . gettext('<strong>NOTE:</strong> The entire cache is cleared since there is no way to ascertain if a gallery page contains dependencies on the item.') . '</div>'
		);
		$options[gettext('Cache default sizes')] = array(
				'key' => 'cachemanager_defaultsizes',
				'type' => OPTION_TYPE_CHECKBOX_ARRAY,
				'order' => 0,
				'checkboxes' => array(
						gettext('Default thumb size') => 'cachemanager_defaultthumb',
						gettext('Default sized image') => 'cachemanager_defaultsizedimage'
				),
				'desc' => gettext('If enabled the default thumb size (or if set a manual crop) and/or the default sized image as set on the theme options are enabled for caching. Themes or plugins can request to override this option being disabled by defining <code>addThemeDefaultThumbSize()</code> and/or <code>addThemeDefaultSizedImageSize()</code> on their option definitions.')
		);
		return $options;
	}

	/**
	 *
	 * custom option handler
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {
		global $_zp_gallery;
		$currenttheme = $_zp_gallery->getCurrentTheme();
		$custom = array();
		$result = query('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" ORDER BY `aux`');
		$key = 0;
		while ($row = db_fetch_assoc($result)) {
			$theme = $row['aux'];
			$data = getSerializedArray($row['data']);
			$custom[$theme][] = $data;
		}
		ksort($custom, SORT_LOCALE_STRING);
		$custom[''] = array(array());
		$c = 0;
		self::printJS();
		foreach ($custom as $theme => $themedata) {
			$themedata = sortMultiArray($themedata, array('thumb', 'image_size', 'image_width', 'image_height'));
			?>
			<span class="icons" id="<?php echo $theme; ?>_arrow">
				<?php
				if ($theme) {
					$inputclass = 'hidden';
					echo '<em>' . $theme . '</em> (' . count($themedata), ')';
				} else {
					$inputclass = 'textbox';
					echo '<br />' . gettext('add');
				}
				?>
				<a href="javascript:showTheme('<?php echo $theme; ?>');" title="<?php echo gettext('Show'); ?>">
					<img class="icon-position-top4" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/arrow_down.png'; ?>" alt="" />
				</a>
			</span>
			<br />
			<div id="<?php echo $theme; ?>_list" style="display:none">
				<br />
				<?php
				foreach ($themedata as $cache) {
					$key++;
					if ($c % 2) {
						$class = 'boxb';
					} else {
						$class = 'box';
					}
					?>
					<div>
						<?php
						$c++;
						if (isset($cache['enable']) && $cache['enable']) {
							$allow = ' checked="checked"';
						} else {
							$allow = '';
						}
						?>
						<div class="<?php echo $class; ?>">
							<input type="<?php echo $inputclass; ?>" size="25" name="cacheManager_theme_<?php echo $key; ?>" value="<?php echo $theme; ?>" />
							<?php
							if ($theme) {
								?>
								<span class="displayinlineright"><?php echo gettext('Delete'); ?> <input type="checkbox" name="cacheManager_delete_<?php echo $key; ?>" value="1" /></span>
								<input type="hidden" name="cacheManager_valid_<?php echo $key; ?>" value="1" />
								<?php
							}
							?>
							<br />
							<?php
							foreach (array('image_size' => gettext('Size'), 'image_width' => gettext('Width'), 'image_height' => gettext('Height'),
					'crop_width' => gettext('Crop width'), 'crop_height' => gettext('Crop height'), 'crop_x' => gettext('Crop X axis'),
					'crop_y' => gettext('Crop Y axis')) as $what => $display) {
								if (isset($cache[$what])) {
									$v = $cache[$what];
								} else {
									$v = '';
								}
								?>
								<span class="nowrap"><?php echo $display; ?> <input type="textbox" size="2" name="cacheManager_<?php echo $what; ?>_<?php echo $key; ?>" value="<?php echo $v; ?>" /></span>
								<?php
							}
							if (isset($cache['wmk'])) {
								$wmk = $cache['wmk'];
							} else {
								$wmk = '';
							}
							?>
							<span class="nowrap"><?php echo gettext('Watermark'); ?> <input type="textbox" size="20" name="cacheManager_wmk_<?php echo $key; ?>" value="<?php echo $wmk; ?>" /></span>
							<br />
							<span class="nowrap"><?php echo gettext('MaxSpace'); ?><input type="checkbox"  name="cacheManager_maxspace_<?php echo $key; ?>" value="1"<?php if (isset($cache['maxspace']) && $cache['maxspace']) echo ' checked="checked"'; ?> /></span>
							<span class="nowrap"><?php echo gettext('Thumbnail'); ?><input type="checkbox"  name="cacheManager_thumb_<?php echo $key; ?>" value="1"<?php if (isset($cache['thumb']) && $cache['thumb']) echo ' checked="checked"'; ?> /></span>
							<span class="nowrap"><?php echo gettext('Grayscale'); ?><input type="checkbox"  name="cacheManager_gray_<?php echo $key; ?>" value="gray"<?php if (isset($cache['gray']) && $cache['gray']) echo ' checked="checked"'; ?> /></span>
						</div>
						<br />
					</div>
					<?php
				}
				?>
			</div><!-- <?php echo $theme; ?>_list -->
			<?php
		}
	}

	static function printJS() {
		?>
		<script type="text/javascript">
			function checkTheme(theme) {
				$('.' + theme).prop('checked', $('#' + theme).prop('checked'));
			}
			function showTheme(theme) {
				html = $('#' + theme + '_arrow').html();
				if (html.match(/down/)) {
					html = html.replace(/_down/, '_up');
					html = html.replace(/title = "<?php echo gettext('Show'); ?>/, 'title="<?php echo gettext('Hide');
		?>"');
					$('#' + theme + '_list').show();
				} else {
					html = html.replace(/_up/, '_down');
					html = html.replace(/title="<?php echo gettext('Hide'); ?>/, 'title="<?php echo gettext('Show'); ?>"');
					$('#' + theme + '_list').hide();
				}
				$('#' + theme + '_arrow').html(html);
			}

		</script>
		<?php
	}

	/**
	 *
	 * process custom option saves
	 * @param string $themename
	 * @param string $themealbum
	 * @return string
	 */
	static function handleOptionSave($themename, $themealbum) {
		$cache = array();
		foreach ($_POST as $key => $value) {
			preg_match('/^cacheManager_(.*)_(.*)/', $key, $matches);
			if ($value && !empty($matches)) {
				$cache[$matches[2]][$matches[1]] = sanitize(trim($value));
			}
		}
		query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager"');
		foreach ($cache as $cacheimage) {
			if (!isset($cacheimage['delete']) && count($cacheimage) > 1) {
				$cacheimage['theme'] = preg_replace("/[\s\"\']+/", "-", $cacheimage['theme']);
				$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("cacheManager",' . db_quote($cacheimage['theme']) . ',' . db_quote(serialize($cacheimage)) . ')';
				query($sql);
			}
		}
		return false;
	}
	
	/**
	 * Adds a custom image cache size for themes and – despite the method name – also for plugins
	 * 
	 * @param string $owner Name of the theme (or plugin) this belongs to
	 * @param int $size
	 * @param int $width
	 * @param int $height
	 * @param int $cw crop width
	 * @param int $ch crop height
	 * @param int $cx crop x
	 * @param int $cy crop y
	 * @param bool $thumb
	 * @param bool $watermark
	 * @param string $effects
	 * @param bool $maxspace
	 */
	static function addCacheSize($owner, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark = NULL, $effects = NULL, $maxspace = false) {
		$cacheSize = serialize(array(
				'theme' => $owner,
				'apply' => false,
				'image_size' => $size,
				'image_width' => $width,
				'image_height' => $height,
				'crop_width' => $cw,
				'crop_height' => $ch,
				'crop_x' => $cx,
				'crop_y' => $cy,
				'thumb' => $thumb,
				'wmk' => $watermark,
				'gray' => $effects,
				'maxspace' => $maxspace,
				'valid' => 1));
		$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("cacheManager",' . db_quote($owner) . ',' . db_quote($cacheSize) . ')';
		query($sql);
	}

	/**
	 * Adds a custom image cache size for themes and – despite the method name – also for plugins
	 * 
	 * @deprecated Zenphoto 1.6 - Use cacheManager::addCacheSize() instead
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeCacheSize($theme, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark = NULL, $effects = NULL, $maxspace = false) {
		cachemanager_internal_deprecations::addThemeCacheSize();
		self::addCacheSize($theme, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark, $effects, $maxspace);
	}
	
		/**
	 * Adds the default theme thumb cache size
	 */
	static function addDefaultThumbSize() {
		setOption('cachemanager_defaultthumb', 1);
	}

	/**
	 * Adds the default theme thumb cache size
	 * @deprecated Zenphoto 1.6 - Better use cacheManager::addDefaultThumbSize();
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeDefaultThumbSize() {
		cachemanager_internal_deprecations::addThemeDefaultThumbSize();
		cacheManager::addDefaultThumbSize();
	}
	
	/**
	 * Adds default sized image size for the cachemanger
	 */
	static function addDefaultSizedImageSize() {
		setOption('cachemanager_defaultsizedimage', 1);
	}
	
	/**
	 * @deprecated Zenphoto 1.6 - Better use cacheManager::addDefaultSizedImageSize();
	 * @since Zenphoto 1.5.1
	 */
	static function addThemeDefaultSizedImageSize() {
		cachemanager_internal_deprecations::addThemeDefaultSizedImageSize();
		cacheManager::addDefaultSizedImageSize();;
	}

	/**
	 *
	 * filter for the setShow() methods
	 * @param object $obj
	 */
	static function published($obj) {
		global $_zp_HTML_cache, $_zp_cached_feeds;
		if (getOption('cacheManager_' . $obj->table)) {
			$_zp_HTML_cache->clearHTMLCache();
			foreach ($_zp_cached_feeds as $feed) {
				$feeder = new cacheManagerFeed($feed);
				$feeder->clearCache();
			}
		}
		return $obj;
	}

	/**
	 * 
	 * @param string $table
	 * @param string $row
	 * @return string
	 */
	static function getTitle($table, $row) {
		switch ($table) {
			case 'images':
				$album = query_single_row('SELECT `folder` FROM ' . prefix('albums') . ' WHERE `id`=' . $row[albumid]);
				$title = gettext('Missing album'); 
				if($album) {
					$title = sprintf(gettext('%1$s: image %2$s'), $album['folder'], $row[$filename]);
				}
				break;
			case 'albums':
				$title = sprintf(gettext('album %s'), $row[$folder]);
				break;
			case 'news':
			case 'pages':
				$title = sprintf(gettext('%1$s: %2$s'), $table, $row['titlelink']);
				break;
		}
		return $title;
	}

	/**
	 * Updates an global array variable with missing images
	 * 
	 * @global array $_zp_cachemanager_missingimages
	 * @param string $table
	 * @param string $row
	 * @param string $image
	 */
	static function recordMissing($table, $row, $image) {
		global $_zp_cachemanager_missingimages;
		$obj = getItemByID($table, $row['id']);
		if ($obj) { // just to be sure
			$_zp_cachemanager_missingimages[] = '<a href="' . $obj->getLink() . '">' . $obj->getTitle() . '</a> (' . html_encode($image) . ')<br />';
		}
	}

	/**
	 * Updates the path to the cache folder
	 * 
	 * @param mixed $text
	 * @param string $target
	 * @param string $update
	 * @return mixed
	 */
	static function updateCacheName($text, $target, $update) {
		if (is_string($text) && preg_match('/^a:[0-9]+:{/', $text)) { //	serialized array
			$text = getSerializedArray($text);
			$serial = true;
		} else {
			$serial = false;
		}
		if (is_array($text)) {
			foreach ($text as $key => $textelement) {
				$text[$key] = self::updateCacheName($textelement, $target, $update);
			}
			if ($serial) {
				$text = serialize($text);
			}
		} else {
			$text = str_replace($target, $update, $text);
		}
		return $text;
	}

	/**
	 * Returns an array with the stored sizes to cache
	 * 
	 * Note: The default thumb and sized image sizes are not included even if set
	 * @param string $mode "all" for all sizes stored, "inactive' or "active"
	 * @return array
	 */
	static function getSizes($mode = 'all') {
		$result = query('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type` = "cacheManager" ORDER BY `aux`');
		while ($row = db_fetch_assoc($result)) {
			$sizes[] = getSerializedArray($row['data']);
		}
		$sizes = sortMultiArray($sizes, array('theme', 'thumb', 'image_size', 'image_width', 'image_height'));
		$sizes_filtered = array();
		if($mode ==  'active' || $mode == 'inactive') {
			foreach ($sizes as $size) {
				if($mode ==  'active') {
					if (cacheManager::isValid($size['theme'])) {
						$sizes_filtered[] = $size;
					}
				}
				if($mode == 'inactive') {
					if (!cacheManager::isValid($size['theme'])) {
						$sizes_filtered[] = $size;
					}
				}
			}
			$sizes = $sizes_filtered;
		}
		return $sizes;
	}

	/**
	 * Caches the images of an album. 
	 * 
	 * @global obj $_zp_gallery
	 * @global array  $_zp_cachemanager_sizes
	 * @global array $_zp_cachemanager_enabledsizes
	 * @param obj $album Object of the album to cache the images
	 */
	static function loadAlbum($albumobj, $countmode = false) {
		global $_zp_gallery, $_zp_cachemanager_sizes, $_zp_cachemanager_enabledsizes;
		$theme = $_zp_gallery->getCurrentTheme();
		$id = 0;
		$parent = getUrAlbum($albumobj);
		$albumtheme = $parent->getAlbumTheme();
		if (!empty($albumtheme)) {
			$theme = $albumtheme;
			$id = $parent->getID();
		}
		$data = array(
				'images_count' => 0,
				'sizes_count' => 0
		);
		loadLocalOptions($id, $theme);
		if ($albumobj->getNumImages() > 0) {
			foreach ($albumobj->getImages(0) as $image) {
				$results = array();
				$imageobj = newImage($albumobj, $image);
				if (isImagePhoto($imageobj)) {
					if (array_key_exists('*', $_zp_cachemanager_enabledsizes)) {
						$uri = getFullImageURL($imageobj);
						if (strpos($uri, 'full-image.php?') !== false) {
							$data['sizes_count']++;
							if(!$countmode) {
								$results[] = cacheManager::generateImage($uri);
							}
						}
					}
					if (array_key_exists('defaultthumb', $_zp_cachemanager_enabledsizes)) {
						$thumb = $imageobj->getThumb();
						if (strpos($thumb, 'i.php?') !== false) {
							$data['sizes_count']++;
							if(!$countmode) {
								$results[] = cacheManager::generateImage($thumb);
							}
						}
					}
					if (array_key_exists('defaultsizedimage', $_zp_cachemanager_enabledsizes)) {
						$defaultimage = $imageobj->getSizedImage(getOption('image_size'));
						if (strpos($defaultimage, 'i.php?') !== false) {
							$data['sizes_count']++;
							if(!$countmode) {
								$results[] = cacheManager::generateImage($defaultimage);
							}
						}
					}
					foreach ($_zp_cachemanager_sizes as $key => $cacheimage) {
						if (array_key_exists($key, $_zp_cachemanager_enabledsizes)) {
							$size = isset($cacheimage['image_size']) ? $cacheimage['image_size'] : NULL;
							$width = isset($cacheimage['image_width']) ? $cacheimage['image_width'] : NULL;
							$height = isset($cacheimage['image_height']) ? $cacheimage['image_height'] : NULL;
							$thumbstandin = isset($cacheimage['thumb']) ? $cacheimage['thumb'] : NULL;
							if ($special = ($thumbstandin === true)) {
								list($special, $cw, $ch, $cx, $cy) = $imageobj->getThumbCropping($size, $width, $height);
							}
							if (!$special) {
								$cw = isset($cacheimage['crop_width']) ? $cacheimage['crop_width'] : NULL;
								$ch = isset($cacheimage['crop_height']) ? $cacheimage['crop_height'] : NULL;
								$cx = isset($cacheimage['crop_x']) ? $cacheimage['crop_x'] : NULL;
								$cy = isset($cacheimage['crop_y']) ? $cacheimage['crop_y'] : NULL;
							}
							$effects = isset($cacheimage['gray']) ? $cacheimage['gray'] : NULL;
							if (isset($cacheimage['wmk'])) {
								$passedWM = $cacheimage['wmk'];
							} else {
								if ($thumbstandin) {
									$passedWM = getWatermarkParam($imageobj, WATERMARK_THUMB);
								} else {
									$passedWM = getWatermarkParam($imageobj, WATERMARK_IMAGE);
								}
							}
							if (isset($cacheimage['maxspace'])) {
								getMaxSpaceContainer($width, $height, $imageobj, $thumbstandin);
							}
							$args = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
							$args = getImageParameters($args, $albumobj->name);
							$uri = getImageURI($args, $albumobj->name, $imageobj->filename, $imageobj->filemtime);
							if (strpos($uri, 'i.php?') !== false) {
								$data['sizes_count']++;
								if(!$countmode) {
									$results[] = cacheManager::generateImage($uri);
								}
							}
						}
					}
					if ($data['sizes_count'] !== 0) {
						$data['images_count'] ++;
						if (!$countmode) {
							echo '<li>';
							echo html_encode($imageobj->getTitle()) . ' (' . html_encode($imageobj->filename) . '): ';
							$count = '';
							foreach ($results as $result) {
								$count++;
								echo $result;
							}
							echo '</li>';
							
						}
					}
				}
			}
			if ($data['sizes_count'] === 0 && !$countmode) {
				?>
				<li><?php echo gettext('All already cached.'); ?></li>
				<?php
			}
		} else {
			if (!$countmode) {
				?>
				<li><em><?php echo gettext('This album does not have any images.'); ?></em></li>
				<?php
			}
		}
		return $data;
	}

	/**
	 * Sends a cURL request to i.php to generate the image requested
	 * Returns a success icon or a fail icon for print out.
	 * 
	 * @param string $imageuri The image processor uri to this image
	 * @return mixed
	 */
	static function generateImage($imageuri) {
		if (function_exists('curl_init')) {
			$success = generateImageCacheFile($imageuri);
			if ($success) {
				return '<a href="'. html_encode(pathurlencode($imageuri)) .'&amp;debug"><img class="icon-position-top4" src="' . WEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="" title="' . html_encode($imageuri) . '"></a>';
			} else {
				return '<a href="'. html_encode(pathurlencode($imageuri)) .'&amp;debug"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/fail.png" alt=""></a>';
			}
		} else {
			?>
			<a href="<?php echo html_encode(pathurlencode($imageuri)); ?>&amp;debug">
				<img src="<?php echo html_encode(pathurlencode($imageuri)); ?>" height="50" alt="x" />
			</a>
			<?php
		}
	}

	/**
	 * Used to check and skip cachemanager sizes if 
	 * - their owner (theme or extension) isn't existing or not enabled.
	 * Also used to sort our default thumb/default sized image
	 * 
	 * - Checks if this is from an existing theme ("admin" sizes are always valid)
	 * - Checks if this is from an enabeled extension
	 * - Checks if this is a custom size (e.g. not one of the non active plugins)
	 * 
	 * @param string $owner
	 * @return boolean
	 */
	static function isValid($owner) {
		global $_zp_gallery;
		if ($owner == 'admin') {
			return true;
		}
		//allow sizes of all themes in case we use an album theme somewhere
		//The list to select will also filter
		$themes = $_zp_gallery->getThemes();
		if (in_array($owner, $themes)) {
			return true;
		} 
		//allow only sizes from active plugins
		if (extensionEnabled($owner)) {
			return true;
		}
		//Sizes that don't apply above and are not from an inactive plugin 
		//are most certainly custom sizes set via cachemanager itself so better allow them
		$allplugins = array_keys(getPluginFiles('*.php'));
		if(!in_array($owner, $allplugins)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Prints the form elements to select a cache size to process
	 * 
	 * @global array $_zp_cachemanager_enabledsizes
	 * @param string $value
	 * @param string $checked
	 * @param string $text
	 * @param string $class
	 */
	static function printSizesListEntry($value, $checked, $text, $class = null) {
		global $_zp_cachemanager_enabledsizes;
		$arrayindex = $value;
		if(in_array($value, array('*', 'defaultthumb', 'defaultsizedimage'))) {
			$arrayindex = $value;
		}
		if (!is_null($class)) {
			$class = '  class="' . html_encode($class) . '"';
		}
		if (!empty($_zp_cachemanager_enabledsizes)) {
			?>
			<input type="hidden" name="enable[<?php echo $arrayindex; ?>]" value="<?php echo $value; ?>" />
			<?php
		}
		?>
		<li>
			<label>
				<input type="checkbox" name="enable[<?php echo $arrayindex; ?>]"<?php echo $class; ?> value="<?php echo $value; ?>"<?php echo $checked; ?> />
				<?php echo gettext('Apply'); ?> <code><?php echo $text; ?></code>
			</label>
		</li>
		<?php
	}
	
	static function overviewbutton($buttons) {
		if (query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
			$enable = true;
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$enable = false;
			$title = gettext('You must first set the plugin options for cached image parameters.');
		}

		$buttons[] = array(
				'category' => gettext('Cache'),
				'enable' => $enable,
				'button_text' => gettext('Cache manager'),
				'formname' => 'cacheManager_button',
				'action' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&tab=images',
				'icon' => 'images/cache.png',
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS,
				'title' => $title
		);
		if (class_exists('RSS')) {
			$buttons[] = array(
					'XSRFTag' => 'clear_cache',
					'category' => gettext('Cache'),
					'enable' => true,
					'button_text' => gettext('Purge RSS cache'),
					'formname' => 'purge_rss_cache.php',
					'action' => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_rss_cache',
					'icon' => 'images/edit-delete.png',
					'alt' => '',
					'title' => gettext('Delete all files from the RSS cache'),
					'hidden' => '<input type="hidden" name="action" value="clear_rss_cache" />',
					'rights' => ADMIN_RIGHTS
			);
		}
		$buttons[] = array(
				'XSRFTag' => 'clear_cache',
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Purge Image cache'),
				'formname' => 'purge_image_cache.php',
				'action' => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=action=clear_cache',
				'icon' => 'images/edit-delete.png',
				'alt' => '',
				'title' => gettext('Delete all files from the Image cache'),
				'hidden' => '<input type="hidden" name="action" value="clear_cache" />',
				'rights' => ADMIN_RIGHTS
		);
		$buttons[] = array(
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Purge HTML cache'),
				'formname' => 'clearcache_button',
				'action' => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_html_cache',
				'icon' => 'images/edit-delete.png',
				'title' => gettext('Clear the static HTML cache. HTML pages will be re-cached as they are viewed.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="clear_html_cache">',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'ClearHTMLCache'
		);

		$buttons[] = array(
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Purge search cache'),
				'formname' => 'clearcache_button',
				'action' => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_search_cache',
				'icon' => 'images/edit-delete.png',
				'title' => gettext('Clear the static search cache.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="clear_search_cache">',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'ClearSearchCache'
		);
		$buttons[] = array(
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Cleanup image cache sizes'),
				'formname' => 'clearcache_button',
				'action' => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?action=cleanup_cache_sizes',
				'icon' => 'images/edit-delete.png',
				'title' => gettext('Removes old stored image cache sizes of themes and plugins not existing on this site. It also removes sizes from inactive plugins.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="cleanup_cache_sizes">',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'CleanupCacheSizes'
		);
		return $buttons;
	}

	static function albumbutton($html, $object, $prefix) {
		$html .= '<hr />';
		if (query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
			$disable = '';
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$disable = ' disabled="disabled"';
			$title = gettext("You must first set the plugin options for cached image parameters.");
		}
		$html .= '<div class="button buttons tooltip" title="' . $title . '"><a href="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?album=' . html_encode($object->name) . '&amp;XSRFToken=' . getXSRFToken('cacheImages') . '"' . $disable . '><img src="images/cache.png" />' . gettext('Cache album images') . '</a><br class="clearall" /></div>';
		return $html;
	}
	
	
	/** 
	 * Deletes theme cache sizes of the owner 
	 * @param string $owner) Owner of the cache size (theme or extension)
	 */
	static function deleteCacheSizes($owner) {
		query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" AND `aux`=' . db_quote($owner));
	}

	/**
	 * @deprecated Zenphoto 1.6 - Use cachemanager::deleteCacheSizes() instead
	 * @since Zenphoto 1.5.1
	 * @param string $theme Owner of the cache size (theme or extension)
	 */
	static function deleteThemeCacheSizes($theme) {
		cachemanager_internal_deprecations::deleteThemeCacheSizes();
		self::deleteCacheSizes($owner);
	}

	/**
	 * Removes up all sizes by non existing themes and non active plugins on this install
	 */
	static function cleanupCacheSizes() {
		$sizes = cacheManager::getSizes('inactive');
		$sizes_delete = array();
		foreach ($sizes as $size) {
			if (!cacheManager::isValid($size['theme'])) {
				$sizes_delete[] = "'" . $size['theme'] . "'";
			}
		}
		if (!empty($sizes_delete)) {
			$delete = implode(',', $sizes_delete);
			$query = query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" AND `aux` IN (' . $delete . ')');
		}
	}
	/**
	 * Just prints a note if the PHP extension cURL is not available
	 */
	static function printCurlNote() {
		if (!function_exists('curl_init')) {
			?>
			<p class='warningbox'><?php echo gettext('Your server does not support the native PHP extension <code>cURL</code>. Pre-caching images is much more effective using it. '); ?></p>
			<?php
		}
	}
	/**
	 * Prints the buttons after you cached image sizes
	 * 
	 * @global array $_zp_cachemanager_enabledsizes
	 * @param string $returnpage Pageurl to return to
	 * @param string $alb Name of the album if caching from an album page
	 * @param bool $hidden True if the buttons should be hidden via the css class "hidden" initially.
	 */
	static function printButtons($returnpage, $alb = '', $hidden = false) {
		global $_zp_cachemanager_enabledsizes;
		$class= '';
		if($hidden) {
			$class= ' hidden';
		}
		?>
		<p class="buttons buttons_cachefinished clearfix<?php echo $class; ?>">
			<a title="<?php echo gettext('Back to the overview'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . $returnpage; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" />
				<strong><?php echo gettext("Back"); ?> </strong>
			</a>
			<?php if (is_array($_zp_cachemanager_enabledsizes)) { ?>
			<a title="<?php echo gettext('New cache size selection'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cacheManager/cacheImages.php?page=overview&tab=images&album=<?php echo $alb; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
				<strong><?php echo gettext("New cache size selection"); ?> </strong>
			</a>
			<?php } ?>
		</p>
		<?php
	}

}
