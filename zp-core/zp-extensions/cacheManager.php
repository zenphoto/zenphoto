<?php
/**
 *
 * This plugin is the centralized Image Cache manager for Zenphoto.
 *
 * It provides:
 * <ul>
 * 		<li><i>pre-creating</i> the Image cache images</li>
 * 		<li>utilities for purging Image caches</li>
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
 * @package zpcore\plugins\cachemanager
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides image cache management utilities.");
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_category = gettext('Admin');

$option_interface = 'cacheManager';

zp_register_filter('admin_utilities_buttons', 'cacheManager::overviewbutton');
zp_register_filter('edit_album_utilities', 'cacheManager::albumbutton', -9999);

/**
 *
 * Standard options interface
 * @author Stephen
 *
 */
class cacheManager {
	public static $sizes = array();
	public static $enabledsizes = array();
	public static $albums_cached = 0;
	public static $images_cached = 0;
	public static $imagesizes_total = 0;
	public static $starttime = 0;
	public static $imagesizes_cached = 0;
	public static $imagesizes_failed = 0;
	
	public static $missingimages = null;

	function __construct() {
		self::deleteCacheSizes('admin');
		self::addCacheSize('admin', 40, NULL, NULL, 40, 40, NULL, NULL, true);
		self::addCacheSize('admin', 80, NULL, NULL, 80, 80, NULL, NULL, true);
		self::addCacheSize('admin', 110, NULL, NULL, NULL, NULL, NULL, NULL, true);
		self::addCacheSize('admin', 135, NULL, NULL, NULL, NULL, NULL, NULL, true);
		setOptionDefault('cachemanager_defaultthumb', 1);
		setOptionDefault('cachemanager_defaultsizedimage', 1);
		setOptionDefault('cachemanager_generationmode', 'classic');
		purgeOption('cacheManager_images');
		purgeOption('cacheManager_albums');
		purgeOption('cacheManager_news');
		purgeOption('cacheManager_pages');
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		$options = array(
				gettext('Cache default sizes') => array(
						'key' => 'cachemanager_defaultsizes',
						'type' => OPTION_TYPE_CHECKBOX_ARRAY,
						'order' => 0,
						'checkboxes' => array(
								gettext('Default thumb size') => 'cachemanager_defaultthumb',
								gettext('Default sized image') => 'cachemanager_defaultsizedimage'
						),
						'desc' => gettext('If enabled the default thumb size (or if set a manual crop) and/or the default sized image as set on the theme options are enabled for caching. Themes or plugins can request to override this option being disabled by defining <code>addDefaultThumbSize()</code> and/or <code>addDefaultSizedImageSize()</code> on their option definitions.')
				),
				gettext('Pre-caching generation mode') => array(
						'key' => 'cachemanager_generationmode',
						'type' => OPTION_TYPE_RADIO,
						'order' => 0,
						'buttons' => array(
								gettext('Classic') => 'classic',
								gettext('cURL') => 'curl'
						),
						'desc' => gettext('Choose the way how the cachemanager generates the cache sizes via its utility.')
						. '<ul>'
						. '<li>' . gettext('<em>Classic</em> (default) outputs the image sizes to generate directly. This is faster and works basically on all servers but is not always creating all sizes reliably so the process may have to be repeated.') . '</li>'
						. '<li>' . gettext('<em>cURL</em> uses PHP cURL requests to generate the images without output. Although processing time and server load is similar to the classic mode, it is actually more reliable in creating the sizes, especially if you have lots of images and albums. However this does not work properly on all servers.') . '</li>'
						. '</ul>'
				)
		);
		return $options;
	}

	static function printJS() {
		?>
		<script>
			function checkTheme(theme) {
				$('.' + theme).prop('checked', $('#' + theme).prop('checked'));
			}
			function showTheme(theme) {
				html = $('#' + theme + '_arrow').html();
				if (html.match(/down/)) {
					html = html.replace(/-down/, '-up');
					html = html.replace(/title = "<?php echo gettext('Show'); ?>/, 'title="<?php echo gettext('Hide');
		?>"');
					$('#' + theme + '_list').show();
				} else {
					html = html.replace(/-up/, '-down');
					html = html.replace(/title="<?php echo gettext('Hide'); ?>/, 'title="<?php echo gettext('Show'); ?>"');
					$('#' + theme + '_list').hide();
				}
				$('#' + theme + '_arrow').html(html);
			}

		</script>
		<?php
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
		global $_zp_db;
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
		$sql = 'INSERT INTO ' . $_zp_db->prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("cacheManager",' . $_zp_db->quote($owner) . ',' . $_zp_db->quote($cacheSize) . ')';
		$_zp_db->query($sql);
	}

	/**
	 * Adds the default theme thumb cache size
	 */
	static function addDefaultThumbSize() {
		setOption('cachemanager_defaultthumb', 1);
	}

	/**
	 * Adds default sized image size for the cachemanger
	 */
	static function addDefaultSizedImageSize() {
		setOption('cachemanager_defaultsizedimage', 1);
	}

	/**
	 *
	 * filter for the setShow() methods
	 * 
	 * @deprecated 2.0
	 * 
	 * @param object $obj
	 */
	static function published($obj) {
		deprecationNotice(gettext('Functionality of this method has been moved to the RSS and static_html_cache plugins'));
		return $obj;
	}

	/**
	 * 
	 * @param string $table
	 * @param string $row
	 * @return string
	 */
	static function getTitle($table, $row) {
		global $_zp_db;
		$title = '';
		switch ($table) {
			case 'images':
				$album = $_zp_db->querySingleRow('SELECT `folder` FROM ' . $_zp_db->prefix('albums') . ' WHERE `id`=' . $row['albumid']);
				$title = gettext('Missing album');
				if ($album) {
					$title = sprintf(gettext('%1$s: image %2$s'), $album['folder'], $row['filename']);
				}
				break;
			case 'albums':
				$title = sprintf(gettext('album %s'), $row['folder']);
				break;
		}
		return $title;
	}

	/**
	 * Updates an global array variable with missing images
	 * 
	 * @param string $table
	 * @param string $row
	 * @param string $image
	 */
	static function recordMissing($table, $row, $image) {
		$obj = getItemByID($table, $row['id']);
		if ($obj) { // just to be sure
			cacheManager::$missingimages[] = '<a href="' . $obj->getLink() . '">' . $obj->getTitle() . '</a> (' . html_encode($image) . ')<br />';
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
		global $_zp_db;
		$result = $_zp_db->query('SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type` = "cacheManager" ORDER BY `aux`');
		while ($row = $_zp_db->fetchAssoc($result)) {
			$sizes[] = getSerializedArray($row['data']);
		}
		$sizes = sortMultiArray($sizes, array('theme', 'thumb', 'image_size', 'image_width', 'image_height'));
		$sizes_filtered = array();
		if ($mode == 'active' || $mode == 'inactive') {
			foreach ($sizes as $size) {
				if ($mode == 'active') {
					if (cacheManager::isValid($size['theme'])) {
						$sizes_filtered[] = $size;
					}
				}
				if ($mode == 'inactive') {
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
	 * Processes an album and recursively its subalbums
	 * @param type $albumobj
	 * @return type
	 */
	static function loadAlbums($albumobj) {
		cachemanager::loadAlbum($albumobj);
		$subalbums = $albumobj->getAlbums();
		foreach ($subalbums as $folder) {
			$subalbum = AlbumBase::newAlbum($folder);
			if (!$subalbum->isDynamic()) {
				cachemanager::loadAlbums($subalbum);
			}
		}
	}

	/**
	 * Processes a single album to cache its images
	 * 
	 * @global obj $_zp_gallery
	 * @param obj $album Object of the album to cache the images
	 */
	static function loadAlbum($albumobj) {
		global $_zp_gallery;
		$theme = $_zp_gallery->getCurrentTheme();
		$id = 0;
		$parent = $albumobj->getUrParent();
		$albumtheme = $parent->getAlbumTheme();
		if (!empty($albumtheme)) {
			$theme = $albumtheme;
			$id = $parent->getID();
		}
		loadLocalOptions($id, $theme);
		echo '<li><strong>' . html_encode($albumobj->getTitle()) . '</strong> (' . html_encode($albumobj->name) . ')<ul>';
		cacheManager::loadImages($albumobj);
		echo '</ul></li>';
		cacheManager::$albums_cached++;
		?>
		<script>
			$('.imagecaching_albumcount').text(<?php echo cacheManager::$albums_cached; ?>);
		</script>
		<?php
	}

	/**
	 * Processes the images of an album
	 * 
	 * @global obj $_zp_gallery
	 * @param type $albumobj
	 * @return int
	 */
	static function loadImages($albumobj) {
		$images = $albumobj->getImages(0);
		if (is_array($images) && count($images) > 0) {
			foreach ($images as $image) {
				$imageobj = Image::newImage($albumobj, $image);
				cacheManager::loadImage($imageobj, true);
			}
		} else {
			?>
			<li><p class="notebox"><em><?php echo gettext('This album does not have any images.'); ?></p></li>
			<?php
		}
	}
	
	/**
	 * Caches sizes for a single image
	 * 
	 * @since 1.6.1
	 * 
	 * @param obj $imageobj Image object
	 * @param boolean $output If a list entry with the size generation status should be output. Default true. Set to false to return true|false for success. 
	 *												Note disabling output only works with the cURL mode!
	 * @return mixed
	 */
	static function loadImage($imageobj, $output = true) {
		if (!is_object($imageobj)) {
			return false;
		}
		$sizes_count = 0;
		$sizeuris = array();
		$albumobj = $imageobj->album;
		if ($imageobj->isPhoto()) { // not valid for non "photo" types 
			if (array_key_exists('*', cachemanager::$enabledsizes)) {
				$uri = getFullImageURL($imageobj);
				if (strpos($uri, 'full-image.php?') !== false) {
					$sizes_count++;
					$sizeuris[] = $uri;
				}
			}
		} 
		if (array_key_exists('defaultthumb', cachemanager::$enabledsizes)) {
			$thumb = $imageobj->getThumb();
			if (strpos($thumb, 'i.php?') !== false) {
				$sizes_count++;
				$sizeuris[] = $thumb;
			}
		}
		if (array_key_exists('defaultsizedimage', cachemanager::$enabledsizes)) {
			if ($imageobj->isPhoto()) {
				$defaultimage = $imageobj->getSizedImage(getOption('image_size'));
			} else {
				$defaultimage = $imageobj->getCustomImage(getOption('image_size'), null, null, null, null, null, null, true);
			}
			if (strpos($defaultimage, 'i.php?') !== false) {
				$sizes_count++;
				$sizeuris[] = $defaultimage;
			}
		}
		foreach (cacheManager::$sizes as $key => $cacheimage) {
			if (array_key_exists($key, cacheManager::$enabledsizes)) {
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
				if (!empty($cacheimage['maxspace'])) {
					getMaxSpaceContainer($width, $height, $imageobj, $thumbstandin);
				}
				$args_array = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
				$args = getImageParameters($args_array, $albumobj->name);
				if ($albumobj->isDynamic()) {
					$folder = $imageobj->album->name;
				} else {
					$folder = $albumobj->name;
				}
				$uri = '';
				if ($imageobj->isPhoto()) {
					$uri = getImageURI($args, $folder, $imageobj->filename, $imageobj->filemtime);
				} else {
					if ($imageobj->objectsThumb) {
						$uri = getImageURI($args, $folder, $imageobj->objectsThumb, $imageobj->filemtime);
					}
				}
				if (!empty($uri) && strpos($uri, 'i.php?') !== false) {
					$sizes_count++;
					$sizeuris[] = $uri;
				}
			}
		}
		$imagetitle = html_encode($imageobj->getTitle()) . ' (' . html_encode($imageobj->filename) . '): ';
		if ($output) {
			echo '<li>';
		}
		if ($sizes_count == 0) {
			if ($output) { 
				echo $imagetitle; 
				?>
				<em style="color:green"><?php echo gettext('All already cached.'); ?></em>
				<?php
			}
		} else {
			cacheManager::$images_cached++;
			if ($output) {
				echo $imagetitle; 
			}	
			foreach ($sizeuris as $sizeuri) {
				cacheManager::generateImage($sizeuri, $output);
				$endtime_temp = time();
				$time_total_temp = ($endtime_temp - cacheManager::$starttime) / 60;
				if ($output) {
					?>
					<script>
						$('.imagecaching_imagecount').text(<?php echo cacheManager::$images_cached; ?>);
						$('.imagecaching_imagesizes').text(<?php echo cacheManager::$imagesizes_cached; ?>);
						$('.imagecaching_imagesizes_failed').text(<?php echo cacheManager::$imagesizes_failed; ?>);
						$('.imagecaching_time').text(<?php echo round($time_total_temp, 2); ?>);
					</script>
					<?php
				}
			}
		}
		if ($output) { 
			echo '</li>';
		}
	}

	/**
	 * Sends a single cURL request to i.php to generate the image size requested if curl is available, otherwise requests the image size directly by printing it.
	 * 
	 * @param string $imageuri The image processor uri to this image
	 * @param boolean $output If a list entry with the size generation status should be output. Default true. Set to false to return true|false for success. 
	 *												Note disabling output only works with the cURL mode!
	 * @return mixed
	 */
	static function generateImage($imageuri, $output = true) {
		if (function_exists('curl_init') && getOption('cachemanager_generationmode') == 'curl') {
			$success = generateImageCacheFile($imageuri);
			if ($success) {
				if ($output) {
					echo '<a href="' . html_encode(pathurlencode($imageuri)) . '&amp;debug"><img class="icon-position-top4" src="' . WEBPATH . '/' . ZENFOLDER . '/images/pass.png" alt="" title="' . html_encode($imageuri) . '"></a>';
				} else {
					return true;
				}
				cacheManager::$imagesizes_cached++;
			} else {
				if ($output) {
					echo '<a href="' . html_encode(pathurlencode($imageuri)) . '&amp;debug"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/fail.png" alt=""></a>';
				} else {
					return false;
				}
				cacheManager::$imagesizes_failed++;
			}
		} else {
			if ($output) {
				?>
								<a href="<?php echo html_encode(pathurlencode($imageuri)); ?>&amp;debug"><img src="<?php echo html_encode(pathurlencode($imageuri)); ?>" height="25" alt="x" /></a>';
				<?php
			} else {
				return false;
			}
			cacheManager::$imagesizes_cached++;
		}
	}

	/**
	 * Used to check and skip cachemanager sizes if 
	 * - their owner (theme or extension) isn't existing or not enabled.
	 * Also used to sort our default thumb/default sized image
	 * 
	 * - Checks if this is from an existing theme ("admin" sizes are always valid)
	 * - Checks if this is from an enabeled extension
	 * - Checks if this is from an installed extension at least
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
		foreach($themes as $theme) {
			if (strtolower(strval($theme['name'])) == strtolower(strval($owner))) {
				return true;
			}
		}
		//allow only sizes from active plugins
		if (extensionEnabled($owner)) {
			return true;
		}
		//Sizes that don't apply above and are not from an inactive plugin 
		//are most certainly custom sizes set via cachemanager itself so better allow them
		$allplugins = array_keys(getPluginFiles('*.php'));
		if (in_array($owner, $allplugins)) {
			return true;
		}
		return false;
	}

	/**
	 * Prints the form elements to select a cache size to process
	 * 
	 * @param string $value
	 * @param string $checked
	 * @param string $text
	 * @param string $class
	 */
	static function printSizesListEntry($value, $checked, $text, $class = null) {
		$arrayindex = $value;
		if (in_array($value, array('*', 'defaultthumb', 'defaultsizedimage'))) {
			$arrayindex = $value;
		}
		if (!is_null($class)) {
			$class = '  class="' . html_encode($class) . '"';
		}
		if (!empty(cacheManager::$enabledsizes)) {
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
		global $_zp_db;
		if ($_zp_db->querySingleRow('SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
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
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?page=overview&tab=images',
				'icon' => 'images/cache.png',
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS,
				'title' => $title
		);

		$buttons[] = array(
				'XSRFTag' => 'clear_cache',
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Purge Image cache'),
				'formname' => 'purge_image_cache',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=action=clear_cache',
				'icon' => 'images/edit-delete.png',
				'alt' => '',
				'title' => gettext('Delete all files from the Image cache'),
				'hidden' => '<input type="hidden" name="action" value="clear_cache" />',
				'rights' => ADMIN_RIGHTS
		);

		$buttons[] = array(
				'category' => gettext('Cache'),
				'enable' => true,
				'button_text' => gettext('Cleanup image cache sizes'),
				'formname' => 'clearcache_button',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?action=cleanup_cache_sizes',
				'icon' => 'images/edit-delete.png',
				'title' => gettext('Removes old stored image cache size definitions of themes and plugins not existing on this site. It also removes sizes from inactive plugins.'),
				'alt' => '',
				'hidden' => '<input type="hidden" name="action" value="cleanup_cache_sizes">',
				'rights' => ADMIN_RIGHTS,
				'XSRFTag' => 'CleanupCacheSizes'
		);
		return $buttons;
	}

	static function albumbutton($html, $object, $prefix) {
		global $_zp_db;
		$html .= '<hr />';
		if ($_zp_db->querySingleRow('SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
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
		global $_zp_db;
		$_zp_db->query('DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="cacheManager" AND `aux`=' . $_zp_db->quote($owner));
	}

	/**
	 * Removes up all sizes by non existing themes and non active plugins on this install
	 */
	static function cleanupCacheSizes() {
		global $_zp_db;
		$sizes = cacheManager::getSizes('inactive');
		$sizes_delete = array();
		foreach ($sizes as $size) {
			if (!cacheManager::isValid($size['theme'])) {
				$sizes_delete[] = "'" . $size['theme'] . "'";
			}
		}
		if (!empty($sizes_delete)) {
			$delete = implode(',', array_unique($sizes_delete));
			$query = 'DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="cacheManager" AND `aux` IN (' . $delete . ')';
			$_zp_db->query($query);
		}
	}

	/**
	 * Just prints a note if the PHP extension cURL is not available
	 */
	static function printCurlNote() {
		if (!function_exists('curl_init') && getOption('cachemanager_generationmode') == 'curl') {
			?>
			<p class='warningbox'><?php echo gettext('Your server does not support the native PHP extension <code>cURL</code>. Pre-caching images is much more effective using it.'); ?></p>
			<?php
		}
	}

	/**
	 * Prints the buttons after you cached image sizes
	 * 
	 * @param string $returnpage Pageurl to return to
	 * @param string $alb Name of the album if caching from an album page
	 * @param bool $hidden True if the buttons should be hidden via the css class "hidden" initially.
	 */
	static function printButtons($returnpage, $alb = '', $hidden = false) {
		$class = '';
		if ($hidden) {
			$class = ' hidden';
		}
		?>
		<p class="buttons buttons_cachefinished clearfix<?php echo $class; ?>">
			<a title="<?php echo gettext('Back to the overview'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . $returnpage; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/cache.png" alt="" />
				<strong><?php echo gettext("Back"); ?> </strong>
			</a>
			<?php if (is_array(cacheManager::$enabledsizes)) { ?>
				<a title="<?php echo gettext('New cache size selection'); ?>" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/cacheManager/cacheImages.php?page=overview&tab=images&album=<?php echo $alb; ?>"> <img src="<?php echo FULLWEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
					<strong><?php echo gettext("New cache size selection"); ?> </strong>
				</a>
			<?php } ?>
		</p>
		<?php
	}

}
