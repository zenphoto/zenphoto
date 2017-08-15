<?php
/**
 * Responsive JavaScript carousel thumb nav plugin adapted from
 * http://bxslider.com
 *
 * Place <var>printThumbNav()</var> on your theme's image.php where you want it to appear.
 *
 * Supports theme based custom css files (place <var>jquery.bxslider.css</var> and needed images in your theme's folder).
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard), Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage theme
 */
$plugin_description = gettext("Responsive jQuery bxSlider thumb nav plugin based on <a href='http://bxslider.com'>http://bxslider.com</a>");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard), Fred Sondaar (fretzl)";
$plugin_disable = (extensionEnabled('jcarousel_thumb_nav')) ? sprintf(gettext('Only one Carousel plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), 'jcarousel_thumb_nav') : '';
$option_interface = 'bxslider';

/**
 * Plugin option handling class
 *
 */
class bxslider {

	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('bxslider_minitems', '3');
			setOptionDefault('bxslider_maxitems', '8');
			setOptionDefault('bxslider_width', '50');
			setOptionDefault('bxslider_height', '50');
			setOptionDefault('bxslider_croph', '50');
			setOptionDefault('bxslider_cropw', '50');
			setOptionDefault('bxslider_speed', '500');
			setOptionDefault('bxslider_fullimagelink', '');
			setOptionDefault('bxslider_mode', 'horizontal');
			$found = array();
			$result = getOptionsLike('bxslider_');
			foreach ($result as $option) {

				preg_match('/bxslider_(.*)_(.*)/', $option, $matches);
				if (count($matches) == 3 && $matches[2] != 'scripts') {
					if ($value) {
						$found[$matches[1]][] = $matches[2];
					}
					purgeOption('bxslider_' . $matches[1] . '_' . $matches[2]);
				}
			}

			foreach ($found as $theme => $scripts) {
				setOptionDefault('bxslider_' . $theme . '_scripts', serialize($scripts));
			}
			if (class_exists('cacheManager')) {
				cacheManager::deleteThemeCacheSizes('bxslider_thumb_nav');
				cacheManager::addThemeCacheSize('bxslider_thumb_nav', NULL, getOption('bxslider_width'), getOption('bxslider_height'), getOption('bxslider_cropw'), getOption('bxslider_croph'), NULL, NULL, true, NULL, NULL, NULL);
			}
		}
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$options = array(
				gettext('Minimum items') => array('key' => 'bxslider_minitems', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext("The minimum number of slides to be shown. Slides will be sized down if carousel becomes smaller than the original size."),
						'order' => 1),
				gettext('Maximum items') => array('key' => 'bxslider_maxitems', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext("The maximum number of slides to be shown. Slides will be sized up if carousel becomes larger than the original size."),
						'order' => 2),
				gettext('Width') => array('key' => 'bxslider_width', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext("Width of the thumb. Note that the CSS might need to be adjusted."),
						'order' => 3),
				gettext('Height') => array('key' => 'bxslider_height', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext("Height of the thumb. Note that the CSS might need to be adjusted."),
						'order' => 4),
				gettext('Crop width') => array('key' => 'bxslider_cropw', 'type' => OPTION_TYPE_NUMBER,
						'desc' => "",
						'order' => 5),
				gettext('Crop height') => array('key' => 'bxslider_croph', 'type' => OPTION_TYPE_NUMBER,
						'desc' => "",
						'order' => 6),
				gettext('Speed') => array('key' => 'bxslider_speed', 'type' => OPTION_TYPE_NUMBER,
						'desc' => gettext("The speed in milliseconds the slides advance when clicked."),
						'order' => 7),
				gettext('Full image link') => array('key' => 'bxslider_fullimagelink', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If checked the thumbs link to the full image instead of the image page."),
						'order' => 8),
				gettext('Mode') => array('key' => 'bxslider_mode', 'type' => OPTION_TYPE_SELECTOR,
						'selections' => array(
								gettext('Horizontal') => "horizontal",
								gettext('Vertical') => "vertical",
								gettext('Fade') => "fade"),
						'desc' => gettext("The mode of the thumb nav. Note this might require theme changes."),
						'order' => 9)
		);
		$c = 30;
		$options['note'] = array('key' => 'bxslider_note', 'type' => OPTION_TYPE_NOTE,
				'order' => $c,
				'desc' => gettext('<strong>NOTE:</strong> the plugin will automatically set the following options based on actual script page use. They may also be set by the themes themselves. It is unnecessary to set them here, but the first time used the JavaScript and CSS files may not be loaded and the thumb slider not shown. Refreshing the page will then show the thumb slider.')
		);
		foreach (getThemeFiles(array('404.php', 'themeoptions.php', 'theme_description.php', 'functions.php', 'password.php', 'sidebar.php', 'register.php', 'contact.php')) as $theme => $scripts) {
			$list = array();
			foreach ($scripts as $script) {
				$list[$script] = stripSuffix($script);
			}
			$options[$theme] = array('key' => 'bxslider_' . $theme . '_scripts', 'type' => OPTION_TYPE_CHECKBOX_ARRAYLIST,
					'order' => $c++,
					'checkboxes' => $list,
					'desc' => gettext('The scripts for which BxSlider is enabled.')
			);
		}
		return $options;
	}

	/**
	 * Use by themes to declare which scripts should have the colorbox CSS loaded
	 *
	 * @param string $theme
	 * @param array $scripts list of the scripts
	 */
	static function registerScripts($scripts, $theme = NULL) {
		if (is_null($theme)) {
			list($theme, $creaator) = getOptionOwner();
		}
		setOptionDefault('bxslider_' . $theme . '_scripts', serialize($scripts));
	}

	/**
	 * Checks if the theme script is registered for colorbox. If not it will register the script
	 * so next time things will workl
	 *
	 * @global type $_zp_gallery
	 * @global type $_zp_gallery_page
	 * @param string $theme
	 * @param string $script
	 * @return boolean true registered
	 */
	static function scriptEnabled($theme, $script) {
		global $_zp_gallery, $_zp_gallery_page;
		$scripts = getSerializedArray(getOption('bxslider_' . $_zp_gallery->getCurrentTheme() . '_scripts'));
		if (!in_array(stripSuffix($_zp_gallery_page), $scripts)) {
			array_push($scripts, $script);
			setOption('bxslider_' . $theme . '_scripts', serialize($scripts));
			return false;
		}
		return true;
	}

	static function themeJS() {
		$theme = getCurrentTheme();
		$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/jquery.bxslider.css';
		if (file_exists($css)) {
			$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/jquery.bxslider.css';
		} else {
			$css = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/bxslider_thumb_nav/jquery.bxslider.css';
		}
		?>

		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/bxslider_thumb_nav/jquery.bxslider.min.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo html_encode($css); ?>" />
		<?php
	}

}

if (!$plugin_disable && !OFFSET_PATH && in_array(stripSuffix($_zp_gallery_page), getSerializedArray(getOption('bxslider_' . $_zp_gallery->getCurrentTheme() . '_scripts')))) {
	zp_register_filter('theme_head', 'bxslider::themeJS');

	/** Prints the jQuery bxslider HTML setup to be replaced by JS
	 *
	 * @param int $minitems The minimum number of thumbs to be visible always if resized regarding responsiveness.
	 * @param int $maxitems The maximum number of thumbs to be visible always if resized regarding responsiveness.
	 * @param int $width Width Set to NULL if you want to use the backend plugin options.
	 * @param int $height Height Set to NULL if you want to use the backend plugin options.
	 * @param int $cropw Crop width Set to NULL if you want to use the backend plugin options.
	 * @param int $croph Crop heigth Set to NULL if you want to use the backend plugin options.
	 * @param bool $crop TRUE for cropped thumbs, FALSE for un-cropped thumbs. $width and $height then will be used as maxspace. Set to NULL if you want to use the backend plugin options.
	 * @param bool $fullimagelink Set to TRUE if you want the thumb link to link to the full image instead of the image page. Set to NULL if you want to use the backend plugin options.
	 * @param string $mode 'horizontal','vertical', 'fade'
	 * @param int $speed The speed in milliseconds the slides advance when clicked
	 */
	function printThumbNav($minitems = NULL, $maxitems = NULL, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $fullimagelink = NULL, $mode = NULL, $speed = NULL) {
		global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery_page;
		//	Just incase the theme has not set the option, at least second try will work!
		bxslider::scriptEnabled($_zp_gallery->getCurrentTheme(), stripSuffix($_zp_gallery_page));
		$items = "";
		if (is_object($_zp_current_album) && $_zp_current_album->getNumImages() >= 2) {
			if (is_null($minitems)) {
				$minitems = getOption('bxslider_minitems');
			} else {
				$minitems = sanitize_numeric($minitems);
			}
			$minitems = max(1, (int) $minitems);
			if (is_null($maxitems)) {
				$maxitems = getOption('bxslider_maxitems');
			} else {
				$maxitems = sanitize_numeric($maxitems);
			}
			$maxitems = max(1, (int) $maxitems);
			if (is_null($width)) {
				$width = getOption('bxslider_width');
			} else {
				$width = sanitize_numeric($width);
			}
			if (is_null($height)) {
				$height = getOption('bxslider_height');
			} else {
				$height = sanitize_numeric($height);
			}
			if (is_null($cropw)) {
				$cropw = getOption('bxslider_cropw');
			} else {
				$cropw = sanitize_numeric($cropw);
			}
			if (is_null($croph)) {
				$croph = getOption('bxslider_croph');
			} else {
				$croph = sanitize_numeric($croph);
			}
			if (is_null($fullimagelink)) {
				$fullimagelink = getOption('bxslider_fullimagelink');
			}
			if (is_null($mode)) {
				$mode = getOption('bxslider_mode');
			}
			if (is_null($speed)) {
				$speed = getOption('bxslider_speed');
			} else {
				$speed = sanitize_numeric($speed);
			}
			if (in_context(ZP_SEARCH_LINKED)) {
				if ($_zp_current_search->getNumImages() === 0) {
					$searchimages = false;
				} else {
					$searchimages = true;
				}
			} else {
				$searchimages = false;
			}
			if (in_context(ZP_SEARCH_LINKED) && $searchimages) {
				$bxslider_items = $_zp_current_search->getImages();
			} else {
				$bxslider_items = $_zp_current_album->getImages();
			}
			if (count($bxslider_items) >= 2) {
				foreach ($bxslider_items as $item) {
					$imgobj = newImage($_zp_current_album, $item);
					if ($fullimagelink) {
						$link = $imgobj->getFullImageURL();
					} else {
						$link = $imgobj->getLink();
					}
					if (!is_null($_zp_current_image)) {
						if ($_zp_current_album->isDynamic()) {
							if ($_zp_current_image->filename == $imgobj->filename && $_zp_current_image->getAlbum()->name == $imgobj->getAlbum()->name) {
								$active = ' class="activeimg" ';
							} else {
								$active = '';
							}
						} else {
							if ($_zp_current_image->filename == $imgobj->filename) {
								$active = ' class="activeimg" ';
							} else {
								$active = '';
							}
						}
					} else {
						$active = '';
					}
					$imageurl = $imgobj->getCustomImage(NULL, $width, $height, $cropw, $croph, NULL, NULL, true);
					$items[] = '<li' . $active . '><a href="' . $link . '"><img src="' . html_encode($imageurl) . '" alt="' . html_encode($imgobj->getTitle()) . '"></a></li>';
				}
			}
			$albumid = $_zp_current_album->get('id');
			//$items = substr($items, 0, -2);
			$numimages = getNumImages();
			if (!is_null($_zp_current_image)) {
				$imgnumber = (imageNumber() - 1);
			} else {
				$imgnumber = 0;
			}
			?>
			<ul class="bxslider<?php echo $albumid; ?>">
				<?php
				$count = '';
				foreach ($items as $item) {
					echo $item;
				}
				?>
			</ul>
			<script type="text/javascript">
				window.addEventListener('load', function () {
					var index = $('.bxslider<?php echo $albumid; ?> li.activeimg').index();
					index = ++index;
					currentPager = parseInt(index / <?php echo $maxitems; ?>)
					$('.bxslider<?php echo $albumid; ?>').bxSlider({
						mode: '<?php echo $mode; ?>',
						minSlides: <?php echo $minitems; ?>,
						maxSlides: <?php echo $maxitems; ?>,
						speed: <?php echo $speed; ?>,
						slideWidth: <?php echo $width; ?>,
						slideMargin: 5,
						moveSlides: <?php echo $maxitems; ?> - 1,
						pager: false,
						adaptiveHeight: true,
						useCSS: false,
						startSlide: currentPager
					});
				}, false);
			</script>
			<?php
		}
	}

}
?>