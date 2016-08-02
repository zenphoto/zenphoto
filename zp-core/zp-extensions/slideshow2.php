<?php
/**
 * An adaption of the jQuery Cycle2 script: http://jquery.malsup.com/cycle2/
 *
 * Shows slideshows of images of an album. Slideshows are responsive by default but beware of theme css.
 *
 * Slideshow
 * The theme file <var>slideshow.php</var> is required witin your theme's folder. A <var>slideshow.css</var> is optional.
 * Required special css should best be incorporated into the theme's css.
 *
 * If you are creating a custom theme, copy these files from an official Zenphoto theme.
 * Note that the Colorbox mode does not require these files as it is called on your theme's image.php and album.php directly
 * via the slideshow button. The Colorbox plugin must be enabled and setup for these pages.
 *
 * <b>NOTE:</b> The jQuery Cycle and the jQuery Colorbox modes do not support movie and audio files.
 * In Colorbox mode there will be no slideshow button on the image page if that current image is a movie/audio file.
 *
 * You can also place the <var>printSlideShow()</var> function anywhere else on your theme to call a slideshow directly.
 *
 * Content macro support:
 * Use [SLIDESHOW <albumname> <true/false for control] for showing a slideshow within image/album descriptions or Zenpage article and page contents.
 * The slideshow size options must fit the space
 * Notes:
 * <ul>
 * 	<li>The slideshow scripts must be enabled for the pages you wish to use it on.</li>
 * 	<li>Use only one slideshow per page to avoid CSS conflicts.</li>
 * 	<li>Also your theme might require extra CSS for this usage, especially the controls.</li>
 * 	<li>This only creates a slideshow in jQuery mode, no matter how the mode is set.</li>
 * </ul>
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 9 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext("Slideshow plugin based on the Cycle2 jQuery plugin.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (extensionEnabled('slideshow')) ? sprintf(gettext('Only one slideshow plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'), 'slideshow') : '';

$option_interface = 'cycle';

global $_zp_gallery, $_zp_gallery_page;
if (($_zp_gallery_page == 'slideshow.php' && getOption('cycle-slideshow_mode') == 'cycle') || getOption('cycle_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page))) {
	zp_register_filter('theme_head', 'cycle::cycleJS');
}
zp_register_filter('content_macro', 'cycle::macro');

/**
 * Plugin option handling class
 *
 */
class cycle {

	function __construct() {
		global $_zp_gallery;
		if (OFFSET_PATH == 2) {
			//normal slideshow
			setOptionDefault('cycle-slideshow_width', '595');
			setOptionDefault('cycle-slideshow_height', '595');
			setOptionDefault('cycle-slideshow_mode', 'cycle');
			setOptionDefault('cycle-slideshow_effect', 'fade');
			setOptionDefault('cycle-slideshow_speed', '1000');
			setOptionDefault('cycle-slideshow_timeout', '3000');
			setOptionDefault('cycle-slideshow_showdesc', 0);
			// colorbox mode
			setOptionDefault('cycle-slideshow_colorbox_transition', 'fade');
			setOptionDefault('cycle-slideshow_colorbox_imagetype', 'sizedimage');
			setOptionDefault('cycle-slideshow_colorbox_imagetitle', 1);
			if (class_exists('cacheManager')) {
				cacheManager::deleteThemeCacheSizes('cycle');
				cacheManager::addThemeCacheSize('cycle', NULL, getOption('cycle-slideshow_width'), getOption('cycle-slideshow_height'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, true);
			}
		}
	}

	function getOptionsSupported() {

		/*		 * *********************
		 * 	slideshow options
		 * ********************* */
		$options = array(
						gettext('Slideshow: Mode')	 => array('key'				 => 'cycle-slideshow_mode', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 0,
										'selections' => array(gettext("jQuery Cycle") => "cycle", gettext("jQuery Colorbox") => "colorbox"),
										'desc'			 => gettext('<em>jQuery Cycle</em> for slideshow using the jQuery Cycle2 plugin<br /><em>jQuery Colorbox</em> for slideshow using Colorbox (Colorbox plugin required).<br />NOTE: The jQuery Colorbox mode is attached to the link the printSlideShowLink() function prints and can neither be called directly nor used on the slideshow.php theme page.')),
						gettext('Slideshow: Speed')	 => array('key'		 => 'cycle-slideshow_speed', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("Speed of the transition in milliseconds."))
		);

		switch (getOption('cycle-slideshow_mode')) {
			case 'cycle':
				$options = array_merge($options, array(gettext('Slideshow: Slide width')					 => array('key'		 => 'cycle-slideshow_width', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 5,
												'desc'	 => gettext("Width of the images in the slideshow.")),
								gettext('Slideshow: Slide height')				 => array('key'		 => 'cycle-slideshow_height', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 6,
												'desc'	 => gettext("Height of the images in the slideshow.")),
								gettext('Slideshow: Effect')							 => array('key'				 => 'cycle-slideshow_effect', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 2,
												'selections' => array(
																gettext('none')							 => "none",
																gettext('fade')							 => "fade",
																gettext('fadeOut')					 => "fadeOut",
																gettext('shuffle')					 => "shuffle",
																gettext('Scroll horizontal') => "scrollHorz",
																gettext('Scroll vertical')	 => "scrollVert",
																gettext('Flip horizontal')	 => "flipHorz",
																gettext('Flip vertical')		 => "flipVert",
																gettext('Tile slide')				 => "tileSlide",
																gettext('Tile blind')				 => "tileBlind"),
												'desc'			 => gettext("The cycle slide effect to be used. Flip transitions are only supported on browsers that support CSS3 3D transforms. (IE10+, current Chrome, Firefox, Opera and Safari.)")),
								gettext('Slideshow: Tile Effect - Extra')	 => array('key'				 => 'cycle-slideshow_tileeffect', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 3,
												'selections' => array(
																gettext('Horziontal')	 => "tileVert",
																gettext('Vertical')		 => "tileHorz"),
												'desc'			 => gettext("If one of the tile effects is selected, this is its orientation.")),
								gettext('Slideshow: Timeout')							 => array('key'		 => 'cycle-slideshow_timeout', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 4,
												'desc'	 => gettext("Milliseconds between slide transitions (0 to disable auto advance.)")),
								gettext('Slideshow: Description')					 => array('key'		 => 'cycle-slideshow_showdesc', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 7,
												'desc'	 => gettext("Check if you want to show the image’s description below the slideshow.")),
								gettext('Slideshow: Swipe gestures')			 => array('key'		 => 'cycle-slideshow_swipe', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 8,
												'desc'	 => gettext("Check if you want to enable touch screen swipe gestures.")),
								gettext('Slideshow: Pause on hover')			 => array('key'		 => 'cycle-slideshow_pausehover', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 9,
												'desc'	 => gettext("Check if you want the slidesshow to pause on hover."))
				));
				break;

			case 'colorbox':
				$options = array_merge($options, array(gettext('Colorbox: Transition')	 => array('key'				 => 'cycle-slideshow_colorbox_transition', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 2,
												'selections' => array(
																gettext('elastic') => "elastic",
																gettext('fade')		 => "fade",
																gettext('none')		 => "none"),
												'desc'			 => gettext("The Colorbox transition slide effect to be used.")),
								gettext('Colorbox: Image type')	 => array('key'				 => 'cycle-slideshow_colorbox_imagetype', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 3,
												'selections' => array(gettext('full image') => "fullimage", gettext("sized image") => "sizedimage"),
												'desc'			 => gettext("The image type you wish to use for the Colorbox. If you choose “sized image” the slideshow width value will be used for the longest side of the image.")),
								gettext('Colorbox: Image title') => array('key'		 => 'cycle-slideshow_colorbox_imagetitle', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 4,
												'desc'	 => gettext("If the image title should be shown at the bottom of the Colorbox."))
				));
				if (getOption('cycle-slideshow_colorbox_imagetype') == 'sizedimage') {
					$options = array_merge($options, array(gettext('Colorbox: Slide width') => array('key'		 => 'cycle-slideshow_width', 'type'	 => OPTION_TYPE_TEXTBOX,
													'order'	 => 3.5,
													'desc'	 => gettext("Width of the images in the slideshow."))
					));
				}
				break;
		}

		foreach (getThemeFiles(array('404.php', 'themeoptions.php', 'theme_description.php', 'slideshow.php', 'functions.php', 'password.php', 'sidebar.php', 'register.php', 'contact.php')) as $theme => $scripts) {
			$list = array();
			foreach ($scripts as $script) {
				$list[$script] = 'cycle_' . $theme . '_' . stripSuffix($script);
			}
			$options2[$theme] = array('key'				 => 'cycle_' . $theme . '_scripts', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
							'checkboxes' => $list,
							'desc'			 => gettext('The scripts for which the cycle2 plugin is enabled. {If themes require it they might set this, otherwise you need to do it manually!}')
			);
		}

		$options = array_merge($options, $options2);

		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	static function getSlideshowPlayer($album, $controls = false, $width = NULL, $height = NULL) {
		$albumobj = NULL;
		if (!empty($album)) {
			$albumobj = newAlbum($album, NULL, true);
		}
		if (is_object($albumobj) && $albumobj->loaded) {
			$returnpath = rewrite_path(pathurlencode($albumobj->name) . '/', '/index.php?album=' . urlencode($albumobj->name));
			return cycle::getShow(false, false, $albumobj, NULL, $width, $height, false, false, false, $controls, $returnpath, 0);
		} else {
			return '<div class="errorbox" id="message"><h2>' . gettext('Invalid slideshow album name!') . '</h2></div>';
		}
	}

	static function getShow($heading, $speedctl, $albumobj, $imageobj, $width, $height, $crop, $shuffle, $linkslides, $controls, $returnpath, $imagenumber) {
    global $_zp_gallery, $_zp_gallery_page;
    setOption('cycle-slideshow_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page), 1);
    if (!$albumobj->isMyItem(LIST_RIGHTS) && !checkAlbumPassword($albumobj)) {
      return '<div class="errorbox" id="message"><h2>' . gettext('This album is password protected!') . '</h2></div>';
    }
    // setting the image size
    if (empty($width) || empty($height)) {
      $width = getOption('cycle-slideshow_width');
      $height = getOption('cycle-slideshow_height');
    }
    if ($crop) {
      $cropw = $width;
      $croph = $height;
    } else {
      $cropw = NULL;
      $croph = NULL;
    }
    //echo $imagenumber;
    $slides = $albumobj->getImages(0);
    $numslides = $albumobj->getNumImages();
    if ($shuffle) { // means random order, not the effect!
      shuffle($slides);
    }
    //echo "<pre>";
    // print_r($slides);
    //echo "</pre>";
    //cycle2 in progressive loading mode cannot start with specific slides as it does not "know" them.
    //The start slide needs to be set manually so I remove and append those before the desired start slide at the end
    if ($imagenumber != 0) { // if start slide not the first
      $count = -1; //cycle2 starts with 0
      $extractslides = array();
      foreach ($slides as $slide) {
        $count++;
        if ($count < $imagenumber) {
          $extractslides[] = $slide;
          unset($slides[$count]);
        }
      }
      $slides = array_merge($slides, $extractslides);
    }
    //echo "<pre>";
    // print_r($slides);
    //echo "</pre>";
    //$albumid = $albumobj->getID();
    if (getOption('cycle-slideshow_swipe')) {
      $option_swipe = 'true';
    } else {
      $option_swipe = 'false';
    }
    if (getOption('cycle-slideshow_pausehover')) {
      $option_pausehover = 'true';
    } else {
      $option_pausehover = 'false';
    }
    $option_fx = getOption('cycle-slideshow_effect');
    $option_tilevertical = '';
    if ($option_fx == 'tileSlide' || $option_fx == 'tileBlind') {
      $option_tileextra = getOption('cycle-slideshow_tileeffect');
      switch ($option_tileextra) {
        case 'tileVert':
          $option_tilevertical = 'data-cycle-tile-vertical=true';
          break;
        case 'tileHorz':
          $option_tilevertical = 'data-cycle-tile-vertical=false';
          break;
        default:
          $option_tilevertical = '';
          break;
      }
    }
    if ($numslides == 0) {
      return '<div class="errorbox" id="message"><h2>' . gettext('No images for the slideshow!') . '</h2></div>';
    }
    $slideshow = '<section class="slideshow"><!-- extra class with album id so we can address slides! -->' . "\n";
    if ($controls) {
      $slideshow .= '<ul class="slideshow_controls">' . "\n";
      $slideshow .= '<li><a href="#" data-cycle-cmd="prev" class="cycle-slideshow-prev icon-backward" title="' . gettext('prev') . '"></a></li>' . "\n";
      $slideshow .= '<li><a href="' . $returnpath . '" class="cycle-slideshow-stop icon-stop" title="' . gettext('stop') . '"></a></li>' . "\n";
      $slideshow .= '<li><a href="#" data-cycle-cmd="pause" class="cycle-slideshow-pause icon-pause" title="' . gettext('pause') . '"></a></li>' . "\n";
      $slideshow .= '<li><a href="#" data-cycle-cmd="resume" class="cycle-slideshow-resume icon-play" title="' . gettext('play') . '"></a></li>' . "\n";
      $slideshow .= '<li><a href="#" data-cycle-cmd="next" class="cycle-slideshow-next icon-forward" title="' . gettext('next') . '"></a></li>' . "\n";
      $slideshow .= '</ul>' . "\n";
    }
    //class cylce-slideshow is mandatory!
    $slideshow .= '<div class="cycle-slideshow"' . "\n";
    $slideshow .= 'data-cycle-pause-on-hover=' . $option_pausehover . "\n";
    $slideshow .= 'data-cycle-fx="' . $option_fx . '"' . "\n";
    $slideshow .= $option_tilevertical . "\n";
    $slideshow .= 'data-cycle-speed=' . getOption('cycle-slideshow_speed') . "\n";
    $slideshow .= 'data-cycle-timeout=' . getOption('cycle-slideshow_timeout') . "\n";
    $slideshow .= 'data-cycle-slides=".slide"' . "\n";
    $slideshow .= 'data-cycle-auto-height=true' . "\n";
    $slideshow .= 'data-cycle-center-horz=true' . "\n";
    $slideshow .= 'data-cycle-center-vert=true' . "\n";
    $slideshow .= 'data-cycle-swipe=' . $option_swipe . "\n";
    $slideshow .= 'data-cycle-loader=true' . "\n";
    $slideshow .= 'data-cycle-progressive=".slides"' . "\n";
    $slideshow .= '>';
    // first slide manually for progressive slide loading
    $firstslide = array_shift($slides);
    /*
     * This obj stuff could be done within printslides but we
     * might need to exclude types although cycle2 should display all
     * In that case we need the filename before printSlide as
     * otherwise the slides count is disturbed as it is done on all
     */
    $slideobj = cycle::getSlideObj($firstslide, $albumobj);
    //$ext = slideshow::is_valid($slideobj->filename, $validtypes);
    //if ($ext) {
    $slideshow .= cycle::getSlide($albumobj, $slideobj, $width, $height, $cropw, $croph, $linkslides, false);
    //}
    $slideshow .= '<script class="slides" type="text/cycle" data-cycle-split="---">' . "\n";
    $count = '';
    foreach ($slides as $slide) {
      $count++;
      $slideobj = cycle::getSlideObj($slide, $albumobj);
      $slideshow .= cycle::getSlide($albumobj, $slideobj, $width, $height, $cropw, $croph, $linkslides, false);
      if ($count != $numslides) {
        $slideshow .= "---\n"; // delimiter for the progressive slide loading
      }
    }
    $slideshow .='</script>' . "\n";
    $slideshow .='</div>' . "\n";
    $slideshow .='</section>' . "\n";
    return $slideshow;
  }

  /**
	 * Helper function to print the individual slides
	 *
	 * @param obj $albumobj Album object
	 * @param obj $imgobj Current slide obj
	 * @param int $width Slide image width
	 * @param int $height Slide image height
	 * @param int $cropw Slide image crop width
	 * @param int $croph Slide image crop height
	 * @param bool $linkslides True or false if the slides should be linked to their image page.
	 *                          Note: In carousel mode this means full image links as here slides are always linked to the image page.
	 * @param bool $crop True or false to crop the image
	 * @param bool $carousel if the slideshow is a carousel so we can enable full image linking (only images allowed!)
	 */
 static function getSlide($albumobj, $imgobj, $width, $height, $cropw, $croph, $linkslides, $crop = false, $carousel = false) {
    global $_zp_current_image;
    if ($crop) {
      $imageurl = $imgobj->getCustomImage(NULL, $width, $height, $cropw, $croph, NULL, NULL, true, NULL);
    } else {
      $maxwidth = $width;
      $maxheight = $height;
      getMaxSpaceContainer($maxwidth, $maxheight, $imgobj);
      $imageurl = $imgobj->getCustomImage(NULL, $maxwidth, $maxheight, NULL, NULL, NULL, NULL, NULL, NULL);
    }
    $slidecontent = '<div class="slide">' . "\n";
    // no space in carousels for titles!
    if (!$carousel) {
      $slidecontent .= '<h4>' . html_encode($albumobj->getTitle()) . ': ' . html_Encode($imgobj->getTitle()) . '</h4>' . "\n";
    }
    if ($carousel) {
      // on the carousel this means fullimage as they are always linked anyway
      if ($linkslides) {
        $url = pathurlencode($imgobj->getFullImageURL());
      } else {
        $url = $imgobj->getLink();
      }
      $slidecontent .= '<a href="' . $url . '">' . "\n";
    } else if (!$carousel && $linkslides) {
      $slidecontent .= '<a href="' . $imgobj->getLink() . '">' . "\n";
    }
    $active = '';
    if ($carousel && !is_null($_zp_current_image)) {
      if ($_zp_current_image->filename == $imgobj->filename) {
        $active = ' class="activeslide"';
      } else {
        $active = '';
      }
    }
    $slidecontent .='<img src="' . pathurlencode($imageurl) . '" alt=""' . $active . '>' . "\n";
    if ($linkslides || $carousel) {
      $slidecontent .= '</a>' . "\n";
    }
    // no space in carousels for this!
    if (getOption("cycle-slideshow_showdesc") && !$carousel) {
      $slidecontent .= '<div class="slide_desc">' . html_encodeTagged($imgobj->getDesc()) . '</div>' . "\n";
    }
    $slidecontent .= '</div>' . "\n";
    return $slidecontent;
  }

  /**
   * Helper function to print the individual slides
   *
   * @param obj $albumobj Album object
   * @param obj $imgobj Current slide obj
   * @param int $width Slide image width
   * @param int $height Slide image height
   * @param int $cropw Slide image crop width
   * @param int $croph Slide image crop height
   * @param bool $linkslides True or false if the slides should be linked to their image page.
   *                          Note: In carousel mode this means full image links as here slides are always linked to the image page.
   * @param bool $crop True or false to crop the image
   * @param bool $carousel if the slideshow is a carousel so we can enable full image linking (only images allowed!)
   */
  static function printSlide($albumobj, $imgobj, $width, $height, $cropw, $croph, $linkslides, $crop = false, $carousel = false) {
    echo getSlide($albumobj, $imgobj, $width, $height, $cropw, $croph, $linkslides, $crop, $carousel);
  }

  /**
	 * We might need this to exclude file types or not…
	 *
	 * @param type $slide
	 * @param type $albumobj
	 * @return type
	 */
	static function getSlideObj($slide, $albumobj) {
		return newImage($albumobj, $slide);
	}

	static function macro($macros) {
		$macros['SLIDESHOW'] = array(
						'class'	 => 'function',
						'params' => array('string', 'bool*', 'int*', 'int*'),
						'value'	 => 'cycle::getSlideshowPlayer',
						'owner'	 => 'cycle',
						'desc'	 => gettext('provide the album name as %1 and (optionally) <code>true</code> (or <code>false</code>) as %2 to show (hide) controls. Hiding the controls is the default. Width(%3) and height(%4) may also be specified to override the defaults.')
		);
		return $macros;
	}

	static function cycleJS() {
		?>
		<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.min.js" type="text/javascript"></script>
		<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.center.min.js" type="text/javascript"></script>
		<!-- effect plugins -->

		<?php if (getOption('cycle-slideshow_effect') == 'flipHorz' || getOption('cycle-slideshow_effect') == 'flipVert') { ?>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.flip.min.js" type="text/javascript"></script>
		<?php } ?>

		<!--[if lt IE 9]>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.ie-fade.min.js" type="text/javascript"></script>
		<![endif]-->

		<?php if (getOption('cycle-slideshow_effect') == 'shuffle') { ?>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.shuffle.min.js" type="text/javascript"></script>
		<?php } ?>

		<?php if (getOption('cycle-slideshow_effect') == 'tileSlide' || getOption('cycle-slideshow_effect') == 'tileBlind') { ?>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.tile.min.js" type="text/javascript"></script>
		<?php } ?>

		<?php if (getOption('cycle-slideshow_effect') == 'scrollVert') { ?>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.scrollVert.min.js" type="text/javascript"></script>
		<?php } ?>

		<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.carousel.min.js" type="text/javascript"></script>

		<!--  swipe with iOS fix -->
		<?php if (getOption('cycle-slideshow_swipe')) { ?>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/jquery.cycle2.swipe.min.js" type="text/javascript"></script>
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/ios6fix.js" type="text/javascript"></script>
		<?php }
		$theme = getCurrentTheme();
		$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/slideshow2.css';
		if (file_exists($css)) {
			$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/slideshow2.css';
		} else {
			$css = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/slideshow2/slideshow2.css';
		}
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo $css ?>" />
		<!--[if lte IE 7]>
			<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/fonts/ie7.css" />
			<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow2/fonts/ie7.js" type="text/javascript"></script>
		<![endif]-->
		<?php
	}

	/** TODO WE MIGHT NOT NEED THIS AS CYCLE2 MIGHT BE ABLE TO DISPLAY ANYTHING!
	 * Returns the file extension if the item passed is displayable by the player
	 *
	 * @param mixed $image either an image object or the filename of an image.
	 * @param array $valid_types list of the types we will accept
	 * @return string;
	 */
	static function is_valid($image, $valid_types) {
		if (is_object($image)) {
			$image = $image->filename;
		}
		$ext = getSuffix($image);
		if (in_array($ext, $valid_types)) {
			return $ext;
		}
		return false;
	}

}

// cycle class end

if ($plugin_disable) {
	enableExtension('slideshow2', 0);
}
if (extensionEnabled('slideshow2')) {

	/**
	 * Prints a link to call the slideshow (not shown if there are no images in the album)
	 * To be used on album.php and image.php
	 * A CSS id names 'slideshowlink' is attached to the link so it can be directly styled.
	 *
	 * If the mode is set to "jQuery Colorbox" and the Colorbox plugin is enabled this link starts a Colorbox slideshow
	 * from a hidden HTML list of all images in the album. On album.php it starts with the first always, on image.php with the current image.
	 *
	 * @param string $linktext Text for the link
	 * @param string $linkstyle Style of Text for the link
	 */
	function printSlideShowLink($linktext = NULL, $linkstyle = Null) {
		global $_zp_gallery, $_zp_current_image, $_zp_current_album, $_zp_current_search, $slideshow_instance, $_zp_gallery_page, $_myFavorites;
		if (is_null($linktext)) {
			$linktext = gettext('View Slideshow');
		}
		if (empty($_GET['page'])) {
			$pagenr = 1;
		} else {
			$pagenr = sanitize_numeric($_GET['page']);
		}
		$slideshowhidden = '';
		$numberofimages = 0;
		if (in_context(ZP_SEARCH)) {
			$imagenumber = '';
			$imagefile = '';
			$albumnr = 0;
			$slideshowlink = rewrite_path(_PAGE_ . '/slideshow/', "index.php?p=slideshow");
			$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="' . html_encode($_zp_current_search->getSearchParams()) . '" />';
		} else {
			if (in_context(ZP_IMAGE)) {
				$imagenumber = imageNumber();
				$imagefile = $_zp_current_image->filename;
			} else {
				$imagenumber = '';
				$imagefile = '';
			}
			if (in_context(ZP_SEARCH_LINKED)) {
				$albumnr = -$_zp_current_album->getID();
				$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="' . html_encode($_zp_current_search->getSearchParams()) . '" />';
			} else {
				$albumnr = $_zp_current_album->getID();
			}
			if ($albumnr) {
				$slideshowlink = rewrite_path(pathurlencode($_zp_current_album->getFolder()) . '/' . _PAGE_ . '/slideshow/', "index.php?p=slideshow&amp;album=" . urlencode($_zp_current_album->getFolder()));
			} else {
				$slideshowlink = rewrite_path(_PAGE_ . '/slideshow', "index.php?p=slideshow");
				$slideshowhidden = '<input type="hidden" name="favorites_page" value="1" />' . "\n" . '<input type="hidden" name="title" value="' . $_myFavorites->instance . '" />';
			}
		}
		$numberofimages = getNumImages();
		$option = getOption('cycle-slideshow_mode');
		switch ($option) {
			case 'cycle':
				if ($numberofimages > 1) {
					?>
					<form name="slideshow_<?php echo $slideshow_instance; ?>" method="post"	action="<?php echo $slideshowlink; ?>">
						<?php echo $slideshowhidden; ?>
						<input type="hidden" name="pagenr" value="<?php echo html_encode($pagenr); ?>" />
						<input type="hidden" name="albumid" value="<?php echo $albumnr; ?>" />
						<input type="hidden" name="numberofimages" value="<?php echo $numberofimages; ?>" />
						<input type="hidden" name="imagenumber" value="<?php echo $imagenumber; ?>" />
						<input type="hidden" name="imagefile" value="<?php echo html_encode($imagefile); ?>" />
						<?php if (!empty($linkstyle)) echo '<p style="' . $linkstyle . '">'; ?>
						<a class="slideshowlink" id="slideshowlink_<?php echo $slideshow_instance; ?>" 	href="javascript:document.slideshow_<?php echo $slideshow_instance; ?>.submit()"><?php echo $linktext; ?></a>
						<?php if (!empty($linkstyle)) echo '</p>'; ?>
					</form>
					<?php
				}
				$slideshow_instance++;
				break;
			case 'colorbox':
				$theme = $_zp_gallery->getCurrentTheme();
				$script = stripSuffix($_zp_gallery_page);
				if (!getOption('colorbox_' . $theme . '_' . $script)) {
					setOptionDefault('colorbox_' . $theme . '_' . $script, 1);
					$themes = $_zp_gallery->getThemes();
					?>
					<div class="errorbox"><?php printf(gettext('Slideshow not available because colorbox is not enabled on %1$s <em>%2$s</em> pages.'), $themes[$theme]['name'], $script); ?></div>
					<?php
					break;
				}
				if ($numberofimages > 1) {
					if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
						$images = $_zp_current_search->getImages(0);
					} else {
						$images = $_zp_current_album->getImages(0);
					}
					$count = '';
					?>
					<script type="text/javascript">
					$(document).ready(function() {
						$("a[rel='slideshow']").colorbox({
							slideshow: true,
							loop: true,
							transition: '<?php echo getOption('cycle-slideshow_colorbox_transition'); ?>',
							slideshowSpeed: <?php echo getOption('cycle-slideshow_speed'); ?>,
							slideshowStart: '<?php echo gettext("start slideshow"); ?>',
							slideshowStop: '<?php echo gettext("stop slideshow"); ?>',
							previous: '<?php echo gettext("prev"); ?>',
							next: '<?php echo gettext("next"); ?>',
							close: '<?php echo gettext("close"); ?>',
							current: '<?php printf(gettext('image %1$s of %2$s'), '{current}', '{total}'); ?>',
							maxWidth: '98%',
							maxHeight: '98%',
							photo: true
						});
					});
					</script>
					<?php
					foreach ($images as $image) {
						if (is_array($image)) {
							$suffix = getSuffix($image['filename']);
						} else {
							$suffix = getSuffix($image);
						}
						$suffixes = array('jpg', 'jpeg', 'gif', 'png');
						if (in_array($suffix, $suffixes)) {
							$count++;
							if (is_array($image)) {
								$albobj = newAlbum($image['folder']);
								$imgobj = newImage($albobj, $image['filename']);
							} else {
								$imgobj = newImage($_zp_current_album, $image);
							}
							if (in_context(ZP_SEARCH_LINKED) || $_zp_gallery_page != 'image.php') {
								if ($count == 1) {
									$style = '';
								} else {
									$style = ' style="display:none"';
								}
							} else {
								if ($_zp_current_image->filename == $image) {
									$style = '';
								} else {
									$style = ' style="display:none"';
								}
							}
							switch (getOption('cycle-slideshow_colorbox_imagetype')) {
								case 'fullimage':
									$imagelink = getFullImageURL($imgobj);
									break;
								case 'sizedimage':
									$imagelink = $imgobj->getCustomImage(getOption("cycle-slideshow_width"), NULL, NULL, NULL, NULL, NULL, NULL, false, NULL);
									break;
							}
							$imagetitle = '';
							if (getOption('cycle-slideshow_colorbox_imagetitle')) {
								$imagetitle = html_encode(getBare($imgobj->getTitle()));
							}
							?>
							<a class="slideshowlink" href="<?php echo html_encode(pathurlencode($imagelink)); ?>" rel="slideshow"<?php echo $style; ?> title="<?php echo $imagetitle; ?>"><?php echo $linktext; ?></a>
							<?php
						}
					}
				}
				break;
		}
	}

	/**
	 * Prints the slideshow using the {@link http://http://www.malsup.com/jquery/cycle2/  jQuery plugin Cycle2 }
	 *
	 * Two ways to use:
	 * a) Use on your theme's slideshow.php page and called via printSlideShowLink():
	 * If called from image.php it starts with that image, called from album.php it starts with the first image (jQuery only)
	 * To be used on slideshow.php only and called from album.php or image.php.
	 *
	 * b) Calling directly via printSlideShow() function (jQuery mode)
	 * Place the printSlideShow() function where you want the slideshow to appear and set create an album object for $albumobj and if needed an image object for $imageobj.
	 * The controls are disabled automatically.
	 *
	 * NOTE: The jQuery mode does not support movie and audio files anymore. If you need to show them please use the Flash mode.
	 * Also note that this function is not used for the Colorbox mode!
	 *
	 * @param bool $heading set to true (default) to emit the slideshow breadcrumbs in flash mode
	 * @param bool $speedctl controls whether an option box for controlling transition speed is displayed
	 * @param obj $albumobj The object of the album to show the slideshow of. If set this overrides the POST data of the printSlideShowLink()
	 * @param obj $imageobj The object of the image to start the slideshow with. If set this overrides the POST data of the printSlideShowLink(). If not set the slideshow starts with the first image of the album.
	 * @param int $width The width of the images (jQuery mode). If set this overrides the size the slideshow_width plugin option that otherwise is used.
	 * @param int $height The heigth of the images (jQuery mode). If set this overrides the size the slideshow_height plugin option that otherwise is used.
	 * @param bool $crop Set to true if you want images cropped width x height (jQuery mode only)
	 * @param bool $shuffle Set to true if you want random (shuffled) order
	 * @param bool $linkslides Set to true if you want the slides to be linked to their image pages (jQuery mode only)
	 * @param bool $controls Set to true (default) if you want the slideshow controls to be shown (might require theme CSS changes if calling outside the slideshow.php page) (jQuery mode only)
	 *
	 */
	function printSlideShow($heading = true, $speedctl = false, $albumobj = NULL, $imageobj = NULL, $width = NULL, $height = NULL, $crop = false, $shuffle = false, $linkslides = false, $controls = true) {
		global $_myFavorites, $_zp_conf_vars;
		if (!isset($_POST['albumid']) AND ! is_object($albumobj)) {
			return '<div class="errorbox" id="message"><h2>' . gettext('Invalid linking to the slideshow page.') . '</h2></div>';
		}
		//getting the image to start with
		if (!empty($_POST['imagenumber']) AND ! is_object($imageobj)) {
			$imagenumber = sanitize_numeric($_POST['imagenumber']) - 1; // slideshows starts with 0, but zp with 1.
		} elseif (is_object($imageobj)) {
			$imagenumber = $imageobj->getIndex();
		} else {
			$imagenumber = 0;
		}
		// set pagenumber to 0 if not called via POST link
		if (isset($_POST['pagenr'])) {
			$pagenumber = sanitize_numeric($_POST['pagenr']);
		} else {
			$pagenumber = 1;
		}
		// getting the number of images
		if (!empty($_POST['numberofimages'])) {
			$numberofimages = sanitize_numeric($_POST['numberofimages']);
		} elseif (is_object($albumobj)) {
			$numberofimages = $albumobj->getNumImages();
		} else {
			$numberofimages = 0;
		}
		if ($numberofimages < 2 || $imagenumber > $numberofimages) {
			$imagenumber = 0;
		}

		//getting the album to show
		if (!empty($_POST['albumid']) && !is_object($albumobj)) {
			$albumid = sanitize_numeric($_POST['albumid']);
		} elseif (is_object($albumobj)) {
			$albumid = $albumobj->getID();
		} else {
			$albumid = 0;
		}

		if (isset($_POST['preserve_search_params'])) { // search page
			$search = new SearchEngine();
			$params = sanitize($_POST['preserve_search_params']);
			$search->setSearchParams($params);
			$searchwords = $search->getSearchWords();
			$searchdate = $search->getSearchDate();
			$searchfields = $search->getSearchFields(true);
			$page = $search->page;
			$returnpath = getSearchURL($searchwords, $searchdate, $searchfields, $page);
			$albumobj = new AlbumBase(NULL, false);
			$albumobj->setTitle(gettext('Search'));
			$albumobj->images = $search->getImages(0);
		} else {
			if (isset($_POST['favorites_page'])) {
				$albumobj = $_myFavorites;
				$returnpath = $_myFavorites->getLink($pagenumber);
			} else {
				$albumq = query_single_row("SELECT title, folder FROM " . prefix('albums') . " WHERE id = " . $albumid);
				$albumobj = newAlbum($albumq['folder']);
				if (empty($_POST['imagenumber'])) {
					$returnpath = $albumobj->getLink($pagenumber);
				} else {
					$image = newImage($albumobj, sanitize($_POST['imagefile']));
					$returnpath = $image->getLink();
				}
			}
		}
		echo cycle::getShow($heading, $speedctl, $albumobj, $imageobj, $width, $height, $crop, $shuffle, $linkslides, $controls, $returnpath, $imagenumber);
	}

}