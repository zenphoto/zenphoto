<?php
/**
 * JavaScript thumb nav plugin with dynamic loading of thumbs on request via JavaScript.
 * Place <var>printjCarouselThumbNav()</var> on your theme's image.php where you want it to appear.
 *
 * Supports theme based custom css files (place <var>jcarousel.css</var> and needed images in your theme's folder).
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */

$plugin_description = gettext("jQuery jCarousel thumb nav plugin with dynamic loading of thumbs on request via JavaScript.");
$plugin_author = "Malte Müller (acrylian) based on a jCarousel example";

$option_interface = 'jcarousel';

/**
 * Plugin option handling class
 *
 */
class jcarousel {

	function jcarouselOptions() {
		setOptionDefault('jcarousel_scroll', '3');
		setOptionDefault('jcarousel_width', '50');
		setOptionDefault('jcarousel_height', '50');
		setOptionDefault('jcarousel_croph', '50');
		setOptionDefault('jcarousel_cropw', '50');
		setOptionDefault('jcarousel_fullimagelink', '');
		setOptionDefault('jcarousel_vertical', 0);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('jcarousel_thumb_nav');
			cacheManager::addThemeCacheSize('jcarousel_thumb_nav', NULL, getOption('jcarousel_width'), getOption('jcarousel_height'),  getOption('jcarousel_cropw'), getOption('jcarousel_croph'), NULL, NULL, true, NULL, NULL, NULL);
		}
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$options = array(	gettext('Thumbs number') => array('key' => 'jcarousel_scroll', 'type' => OPTION_TYPE_TEXTBOX,
				'desc' => gettext("The number of thumbs to scroll by. Note that the CSS might need to be adjusted.")),
				gettext('width') => array('key' => 'jcarousel_width', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Width of the carousel. Note that the CSS might need to be adjusted.")),
				gettext('height') => array('key' => 'jcarousel_height', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Height of the carousel. Note that the CSS might need to be adjusted.")),
				gettext('Crop width') => array('key' => 'jcarousel_cropw', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => ""),
				gettext('Crop height') => array('key' => 'jcarousel_croph', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => ""),
				gettext('Full image link') => array('key' => 'jcarousel_fullimagelink', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If checked the thumbs link to the full image instead of the image page.")),
				gettext('Vertical') => array('key' => 'jcarousel_vertical', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("If checked the carousel will flow vertically instead of the default horizontal. Changing this may require theme changes!"))
		);
		foreach (getThemeFiles(array('404.php','themeoptions.php','theme_description.php')) as $theme=>$scripts) {
			$list = array();
			foreach ($scripts as $script) {
				$list[$script] = 'jcarousel_'.$theme.'_'.stripSuffix($script);
			}
			$options[$theme] = array('key' => 'jcarousel_'.$theme.'_scripts', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
					'checkboxes' => $list,
					'desc' => gettext('The scripts for which jCarousel is enabled. {If themes require it they might set this, otherwise you need to do it manually!}')
			);
		}
		return $options;
	}

	static function themeJS() {
		$theme = getCurrentTheme();
		$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/jcarousel.css';
		if (file_exists($css)) {
			$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/jcarousel.css';
		} else {
			$css = WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/jcarousel_thumb_nav/jcarousel.css';
		}
		?>
		<script>
			(function($) {
				var userAgent = navigator.userAgent.toLowerCase();

				$.browser = {
						version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1],
						safari: /webkit/.test( userAgent ),
						opera: /opera/.test( userAgent ),
						msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
						mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
				};

			})(jQuery);
		</script>
		<script type="text/javascript" src="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER;?>/jcarousel_thumb_nav/jquery.jcarousel.pack.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER;?>/jcarousel_thumb_nav/jquery.jcarousel.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo html_encode($css); ?>" />
		<?php
	}

}

if (!OFFSET_PATH && getOption('jcarousel_'.$_zp_gallery->getCurrentTheme().'_'.stripSuffix($_zp_gallery_page))) {
	zp_register_filter('theme_head','jcarousel::themeJS');

	/** Prints the jQuery jCarousel HTML setup to be replaced by JS
	 *
	 * @param int $thumbscroll The number of thumbs to scroll by. Note that the CSS might need to be adjusted. Set to NULL if you want to use the backend plugin options.
	 * @param int $width Width Set to NULL if you want to use the backend plugin options.
	 * @param int $height Height Set to NULL if you want to use the backend plugin options.
	 * @param int $cropw Crop width Set to NULL if you want to use the backend plugin options.
	 * @param int $croph Crop heigth Set to NULL if you want to use the backend plugin options.
	 * @param bool $crop TRUE for cropped thumbs, FALSE for un-cropped thumbs. $width and $height then will be used as maxspace. Set to NULL if you want to use the backend plugin options.
	 * @param bool $fullimagelink Set to TRUE if you want the thumb link to link to the full image instead of the image page. Set to NULL if you want to use the backend plugin options.
	 * @param bool $vertical Set to TRUE if you want the thumbs vertical orientated instead of horizontal (false). Set to NULL if you want to use the backend plugin options.
	 */
	function printjCarouselThumbNav($thumbscroll=NULL, $width=NULL, $height=NULL,$cropw=NULL,$croph=NULL,$fullimagelink=NULL,$vertical=NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery_page;
	//	Just incase the theme has not set the option, at least second try will work!
	setOptionDefault('slideshow_'.$_zp_gallery->getCurrentTheme().'_'.stripSuffix($_zp_gallery_page),1);
	$items = "";
	if(is_object($_zp_current_album) && $_zp_current_album->getNumImages() >= 2) {
		if(is_null($thumbscroll)) {
			$thumbscroll = getOption('jcarousel_scroll');
		} else {
			$thumbscroll = sanitize_numeric($thumbscroll);
		}
		if(is_null($width)) {
			$width = getOption('jcarousel_width');
		} else {
			$width = sanitize_numeric($width);
		}
		if(is_null($height)) {
			$height = getOption('jcarousel_height');
		} else {
			$height = sanitize_numeric($height);
		}
		if(is_null($cropw)) {
			$cropw = getOption('jcarousel_cropw');
		} else {
			$cropw = sanitize_numeric($cropw);
		}
		if(is_null($croph)) {
			$croph = getOption('jcarousel_croph');
		} else {
			$croph = sanitize_numeric($croph);
		}
		if(is_null($fullimagelink)) {
			$fullimagelink = getOption('jcarousel_fullimagelink');
		} else {
			$fullimagelink = sanitize($fullimagelink);
		}
		if(is_null($vertical)) {
			$vertical = getOption('jcarousel_vertical');
		} else {
			$vertical = sanitize($vertical);
		}
		if($vertical) {
			$vertical = 'true';
		} else {
			$vertical = 'false';
		}
		if(in_context(ZP_SEARCH_LINKED)) {
				if($_zp_current_search->getNumImages() === 0) {
					$searchimages = false;
				}	else {
					$searchimages = true;
				}
			} else {
				$searchimages = false;
			}
			if(in_context(ZP_SEARCH_LINKED) && $searchimages) {
				$jcarousel_items = $_zp_current_search->getImages();
			} else {
				$jcarousel_items =  $_zp_current_album->getImages();
			}
		if(count($jcarousel_items) >= 2) {
			foreach($jcarousel_items as $item) {
				if(is_array($item)) {
				$imgobj = newImage(newAlbum($item['folder']),$item['filename']);
				} else {
					$imgobj = newImage($_zp_current_album,$item);
				}
				if($fullimagelink) {
					$link = $imgobj->getFullImageURL();
				} else {
					$link = $imgobj->getImageLink();
				}
				if(!is_null($_zp_current_image)) {
					if($_zp_current_album->isDynamic()) {
						if($_zp_current_image->filename == $imgobj->filename && $_zp_current_image->getAlbum()->name == $imgobj->getAlbum()->name) {
							$active = 'active';
						} else {
							$active = '';
						}
					} else {
						if($_zp_current_image->filename == $imgobj->filename) {
							$active = 'active';
						} else {
							$active = '';
						}
					}
				} else {
					$active = '';
				}
				$imageurl = $imgobj->getCustomImage(NULL, $width, $height, $cropw, $croph, NULL, NULL,true);
				$items .= ' {url: "'.html_encode($imageurl).'", title: "'.html_encode($imgobj->getTitle()).'", link: "'.html_encode($link).'", active: "'.$active.'"},';
				$items .= "\n";
			}
		}
		$items = substr($items, 0, -2);
		$numimages = getNumImages();
		if(!is_null($_zp_current_image)) {
			$imgnumber = imageNumber();
		} else {
			$imgnumber = 1;
		}
		?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			var mycarousel_itemList = [
				<?php echo $items; ?>
			];

			function mycarousel_itemLoadCallback(carousel, state) {
					for (var i = carousel.first; i <= carousel.last; i++) {
							if (carousel.has(i)) {
									continue;
							}
							if (i > mycarousel_itemList.length) {
									break;
							}
							carousel.add(i, mycarousel_getItemHTML(mycarousel_itemList[i-1]));
					}
				}

			function mycarousel_getItemHTML(item) {
				if(item.active === "") {
					html = '<a href="' + item.link + '" title="' + item.title + '"><img src="' + item.url + '" width="<?php  echo $width; ?>" height="<?php echo $height; ?>" alt="' + item.url + '" /></a>';
				} else {
					html = '<a href="' + item.link + '" title="' + item.title + '"><img class="activecarouselimage" src="' + item.url + '" width="<?php  echo $width; ?>" height="<?php echo $height; ?>" alt="' + item.url + '" /></a>';
				}
				return html;
				}

			jQuery(document).ready(function() {
					jQuery("#mycarousel").jcarousel({
							vertical: <?php echo $vertical; ?>,
							size: mycarousel_itemList.length,
							start: <?php echo $imgnumber; ?>,
							scroll: <?php echo $thumbscroll; ?>,
							itemLoadCallback: {onBeforeAnimation: mycarousel_itemLoadCallback}
					});
			});
		// ]]> -->
		</script>
		<ul id="mycarousel">
			<!-- The content will be dynamically loaded in here -->
		</ul>
		<?php
		}
	}

}
?>