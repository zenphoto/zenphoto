<?php

// force UTF-8 ÿ

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

class ThemeOptions {

	function __construct() {
		// force core theme options for this theme
		setThemeOption('thumb_size', 168, null);
		setThemeOption('thumb_crop', 1, null);
		setThemeOption('image_use_side', 'longest', null);

		// set core theme option defaults
		setThemeOptionDefault('image_size', 1200);
		setThemeOptionDefault('albums_per_row', 6);
		setThemeOptionDefault('images_per_row', 9);
		setThemeOptionDefault('albums_per_page', 16);
		setThemeOptionDefault('images_per_page', 32);
		setThemeOptionDefault('thumb_transition', 2);

		// set theme option defaults
		setThemeOptionDefault('zpmas_usenews', true);
		setThemeOptionDefault('zpmas_css', 'dark');
		setThemeOptionDefault('zpmas_logo', '');
		setThemeOptionDefault('zpmas_logoheight', '');
		setThemeOptionDefault('zpmas_zpsearchcount', 2);
		setThemeOptionDefault('zpmas_finallink', 'nolink');
		setThemeOptionDefault('zpmas_disablemeta', false);
		setThemeOptionDefault('zpmas_imagetitle', false);
		setThemeOptionDefault('zpmas_thumbsize', 'small');
		setThemeOptionDefault('zpmas_thumbcrop', true);
		setThemeOptionDefault('zpmas_infscroll', true);
		setThemeOptionDefault('zpmas_fixsidebar', true);
		setThemeOptionDefault('zpmas_cbtarget', true);
		setThemeOptionDefault('zpmas_cbstyle', 'example3');
		setThemeOptionDefault('zpmas_cbtransition', 'fade');
		setThemeOptionDefault('zpmas_cbssspeed', '2500');
		setThemeOptionDefault('zpmas_ss', true);
		setThemeOptionDefault('zpmas_sstype', 'random');
		setThemeOptionDefault('zpmas_sscount', 5);
		setThemeOptionDefault('zpmas_sseffect', 'fade');
		setThemeOptionDefault('zpmas_ssspeed', '4000');
		setThemeOptionDefault('jcarousel_zpmasonry_image', 1);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('zpmasonry');
			cacheManager::addThemeCacheSize('zpmasonry', null, 108, 108, 108, 108, null, null, null, true, getOption('Image_watermark'), false, false); // image thumbs - small
			cacheManager::addThemeCacheSize('zpmasonry', null, 248, 248, 248, 248, null, null, true, getOption('Image_watermark'), false, false); // album thumbs - small - square
			cacheManager::addThemeCacheSize('zpmasonry', null, 248, 125, 248, 125, null, null, true, getOption('Image_watermark'), false, false); // album thumbs - small - landscape
			cacheManager::addThemeCacheSize('zpmasonry', null, 528, 528, 528, 528, null, null, true, getOption('Image_watermark'), false, false); // fp slideshow - small - square
			cacheManager::addThemeCacheSize('zpmasonry', null, 528, 270, 528, 270, null, null, true, getOption('Image_watermark'), false, false); // fp slideshow - small - landscape
			cacheManager::addThemeCacheSize('zpmasonry', null, 168, 168, 168, 168, null, null, null, true, getOption('Image_watermark'), false, false); // image thumbs - large
			cacheManager::addThemeCacheSize('zpmasonry', null, 368, 368, 368, 368, null, null, true, getOption('Image_watermark'), false, false); // album thumbs - large - square
			cacheManager::addThemeCacheSize('zpmasonry', null, 368, 200, 368, 200, null, null, true, getOption('Image_watermark'), false, false); // album thumbs - large - landscape
			cacheManager::addThemeCacheSize('zpmasonry', null, 768, 768, 768, 768, null, null, true, getOption('Image_watermark'), false, false); // fp slideshow - large - square
			cacheManager::addThemeCacheSize('zpmasonry', null, 768, 360, 768, 360, null, null, true, getOption('Image_watermark'), false, false); // fp slideshow - large - landscape
			cacheManager::addThemeCacheSize('zpmasonry', 1200, null, null, null, null, null, null, false, getOption('fullimage_watermark'), null, null); // full image
		}
	}

	function getOptionsDisabled() {
		return array('thumb_crop', 'thumb_size', 'image_use_side1', 'image_use_side2', 'image_use_side3', 'image_use_side4');
	}

	function getOptionsSupported() {
		return array(
				gettext('Style') => array('key' => 'zpmas_css', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 1,
						'desc' => gettext('Select a dark or light overall color style.')),
				gettext('General Thumb Sizes') => array('key' => 'zpmas_thumbsize', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 2,
						'desc' => gettext('Toggle large or small thumbnails.  This theme does not allow you to set the thumb sizes above for layout reasons.
				For advanced users, these are set in the functions.php file.')),
				gettext('Crop Album Thumbs to Landscape Orientation?') => array('key' => 'zpmas_thumbcrop', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 3,
						'desc' => gettext('Check to crop the album thumbs to a more landscape orientation.')),
				gettext('Show Image Title on Album Pages?') => array('key' => 'zpmas_imagetitle', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 4,
						'desc' => gettext('Check to show the image title above images on the album and search pages.')),
				gettext('Use Infinite Scroll?') => array('key' => 'zpmas_infscroll', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 5,
						'desc' => gettext('Loads subsequent pages into the current page, disables normal pagination. Experimental, use with caution.')),
				gettext('Fix the sidebar\'s position?') => array('key' => 'zpmas_fixsidebar', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 6,
						'desc' => gettext('Check to fix the sidebar and make it always visible as user scrolls.  If sidebar is too tall for the users
				viewport, it will revert to static position.')),
				gettext('Logo Image') => array('key' => 'zpmas_logo', 'type' => OPTION_TYPE_TEXTBOX,
						'order' => 7,
						'desc' => gettext('Enter an image file located in the themes image directory, including extension, to use as an image logo. Or leave
				blank to use a text representation of your Gallery name.  As an example there is a logo.gif image in the images directory with a
				height of 83 (must enter height of image as well!).')),
				gettext('Logo Height') => array('key' => 'zpmas_logoheight', 'type' => OPTION_TYPE_NUMBER,
						'order' => 8,
						'desc' => gettext('If using a logo image above, you must enter the height of the image in pixels here.')),
				gettext('Final Image Link Option') => array('key' => 'zpmas_finallink', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 9,
						'desc' => gettext('Choose the option for the final image link on image.php.  Can either link to full image using standard zenphoto
				process (with core options), colorbox (if plugin enabled), or no link (default).')),
				gettext('Disable MetaData Display?') => array('key' => 'zpmas_disablemeta', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 10,
						'desc' => gettext('Check to disable the metadata (EXIF,IPTC) display on the image page.')),
				gettext('ZenPage Search Results') => array('key' => 'zpmas_zpsearchcount', 'type' => OPTION_TYPE_SLIDER,
						'min' => 0,
						'max' => 4,
						'order' => 11,
						'desc' => gettext('If using Zenpage, enter the number of search results to display for each news and pages.  Default is 2 (4 total
				possible).')),
				gettext('Colorbox Style') => array('key' => 'zpmas_cbstyle', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 12,
						'desc' => gettext('Select the Colorbox style you wish to use (examples on the colorbox site).')),
				gettext('Colorbox Transition Type') => array('key' => 'zpmas_cbtransition', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 13,
						'desc' => gettext('The colorbox transition type. Can be set to elastic, fade, or none.')),
				gettext('Colorbox Slideshow Speed') => array('key' => 'zpmas_cbssspeed', 'type' => OPTION_TYPE_NUMBER,
						'order' => 14,
						'desc' => gettext('Enter a number here in milliseconds that determines the colorbox slideshow speed. Default is \'2500\'.')),
				gettext('Colorbox Target Sized Image in Galleriffic') => array('key' => 'zpmas_cbtarget', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 15,
						'desc' => gettext('Click to enable colorbox targeting the sized image setting in the top options, instead of the full original image.
				This is usefull if you upload large images as you can set Colorbox to target a smaller, resized version based on your setting
				above.')),
				gettext('Frontpage Slideshow?') => array('key' => 'zpmas_ss', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 16,
						'desc' => gettext('Check if you want to show a slideshow on the frontpage. Contents of slideshow and settings can be selected below.')),
				gettext('Frontpage Slideshow Option') => array('key' => 'zpmas_sstype', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 17,
						'desc' => gettext('Select the contents option of the frontpage slideshow if selected above.')),
				gettext('Frontpage Slideshow Count') => array('key' => 'zpmas_sscount', 'type' => OPTION_TYPE_NUMBER,
						'order' => 18,
						'desc' => gettext('How many images/albums to display in the slideshow.')),
				gettext('Frontpage Slideshow Transition Effect?') => array('key' => 'zpmas_sseffect', 'type' => OPTION_TYPE_CUSTOM,
						'order' => 19,
						'desc' => gettext('Choose the transition effect, if slideshow is selected above.')),
				gettext('Frontpage Slideshow Speed?') => array('key' => 'zpmas_ssspeed', 'type' => OPTION_TYPE_NUMBER,
						'order' => 20,
						'desc' => gettext('Choose the delay of each rotation for slideshow in milliseconds.')),
				gettext('Use News Feature') => array('key' => 'zpmas_usenews', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 21,
						'desc' => gettext("IF you have the Zenpage plugin enabled, you can uncheck this to NOT use the news feature of the Zenpage plugin (use only pages)")),
				gettext('Custom CSS') => array('order' => 9, 'key' => 'zpmas_customcss', 'type' => OPTION_TYPE_TEXTAREA,
						'order' => 22,
						'multilingual' => false,
						'desc' => gettext('Enter any custom CSS, safely carries over upon theme upgrade.'))
		);
	}

	function handleOption($option, $currentValue) {

		if ($option == 'zpmas_cbstyle') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="example1"';
			if ($currentValue == "example1") {
				echo ' selected="selected">style1</option>\n';
			} else {
				echo '>style1</option>\n';
			}

			echo '<option value="example2"';
			if ($currentValue == "example2") {
				echo ' selected="selected">style2</option>\n';
			} else {
				echo '>style2</option>\n';
			}

			echo '<option value="example3"';
			if ($currentValue == "example3") {
				echo ' selected="selected">style3</option>\n';
			} else {
				echo '>style3</option>\n';
			}

			echo '<option value="example4"';
			if ($currentValue == "example4") {
				echo ' selected="selected">style4</option>\n';
			} else {
				echo '>style4</option>\n';
			}

			echo '<option value="example5"';
			if ($currentValue == "example5") {
				echo ' selected="selected">style5</option>\n';
			} else {
				echo '>style5</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmas_cbtransition') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="fade"';
			if ($currentValue == "fade") {
				echo ' selected="selected">Fade</option>\n';
			} else {
				echo '>Fade</option>\n';
			}

			echo '<option value="elastic"';
			if ($currentValue == "elastic") {
				echo ' selected="selected">Elastic</option>\n';
			} else {
				echo '>Elastic</option>\n';
			}

			echo '<option value="none"';
			if ($currentValue == "none") {
				echo ' selected="selected">None</option>\n';
			} else {
				echo '>None</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmas_sstype') {
			echo '<select id="' . $option . '" name="' . $option . '"' . ">\n";
			echo '<option value="random"';
			if ($currentValue == "random") {
				echo ' selected="selected">Random</option>\n';
			} else {
				echo '>Random</option>\n';
			}
			echo '<option value="image-popular"';
			if ($currentValue == "image-popular") {
				echo ' selected="selected">Image-Popular</option>\n';
			} else {
				echo '>Image-Popular</option>\n';
			}
			echo '<option value="image-latest"';
			if ($currentValue == "image-latest") {
				echo ' selected="selected">Image-Latest</option>\n';
			} else {
				echo '>Image-Latest</option>\n';
			}
			echo '<option value="image-latest-date"';
			if ($currentValue == "image-latest-date") {
				echo ' selected="selected">Image-Latest-Date</option>\n';
			} else {
				echo '>Image-Latest-Date</option>\n';
			}
			echo '<option value="image-latest-mtime"';
			if ($currentValue == "image-latest-mtime") {
				echo ' selected="selected">Image-Latest-mtime</option>\n';
			} else {
				echo '>Image-Latest-mtime</option>\n';
			}
			echo '<option value="image-mostrated"';
			if ($currentValue == "image-mostrated") {
				echo ' selected="selected">Image-MostRated</option>\n';
			} else {
				echo '>Image-MostRated</option>\n';
			}
			echo '<option value="image-toprated"';
			if ($currentValue == "image-toprated") {
				echo ' selected="selected">Image-TopRated</option>\n';
			} else {
				echo '>Image-TopRated</option>\n';
			}
			echo '<option value="album-latest"';
			if ($currentValue == "album-latest") {
				echo ' selected="selected">Album-Latest</option>\n';
			} else {
				echo '>Album-Latest</option>\n';
			}
			echo '<option value="album-latestupdated"';
			if ($currentValue == "album-latestupdated") {
				echo ' selected="selected">Album-LatestUpdated</option>\n';
			} else {
				echo '>Album-LatestUpdated</option>\n';
			}
			echo '<option value="album-mostrated"';
			if ($currentValue == "album-mostrated") {
				echo ' selected="selected">Album-MostRated</option>\n';
			} else {
				echo '>Album-MostRated</option>\n';
			}
			echo '<option value="album-toprated"';
			if ($currentValue == "album-toprated") {
				echo ' selected="selected">Album-TopRated</option>\n';
			} else {
				echo '>Album-TopRated</option>\n';
			}
			echo "</select>\n";
		}

		if ($option == 'zpmas_sseffect') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="fade"';
			if ($currentValue == "fade") {
				echo ' selected="selected">Fade</option>\n';
			} else {
				echo '>Fade</option>\n';
			}
			echo '<option value="shuffle"';
			if ($currentValue == "shuffle") {
				echo ' selected="selected">Shuffle</option>\n';
			} else {
				echo '>Shuffle</option>\n';
			}
			echo '<option value="scrollUp"';
			if ($currentValue == "scrollUp") {
				echo ' selected="selected">Scroll Up</option>\n';
			} else {
				echo '>Scroll Up</option>\n';
			}
			echo '<option value="scrollDown"';
			if ($currentValue == "scrollDown") {
				echo ' selected="selected">Scroll Down</option>\n';
			} else {
				echo '>Scroll Down</option>\n';
			}
			echo '<option value="scrollRight"';
			if ($currentValue == "scrollRight") {
				echo ' selected="selected">Scroll Right</option>\n';
			} else {
				echo '>Scroll Right</option>\n';
			}
			echo '<option value="scrollLeft"';
			if ($currentValue == "scrollLeft") {
				echo ' selected="selected">Scroll Left</option>\n';
			} else {
				echo '>Scroll Left</option>\n';
			}
			echo '<option value="scrollHorz"';
			if ($currentValue == "scrollHorz") {
				echo ' selected="selected">Scroll Horizontal</option>\n';
			} else {
				echo '>Scroll Horizontal</option>\n';
			}
			echo '<option value="scrollVert"';
			if ($currentValue == "scrollVert") {
				echo ' selected="selected">Scroll Vertical</option>\n';
			} else {
				echo '>Scroll Vertical</option>\n';
			}
			echo '<option value="blindX"';
			if ($currentValue == "blindX") {
				echo ' selected="selected">Blind X</option>\n';
			} else {
				echo '>Blind X</option>\n';
			}
			echo '<option value="blindY"';
			if ($currentValue == "blindY") {
				echo ' selected="selected">Blind Y</option>\n';
			} else {
				echo '>Blind Y</option>\n';
			}
			echo '<option value="cover"';
			if ($currentValue == "cover") {
				echo ' selected="selected">Cover</option>\n';
			} else {
				echo '>Cover</option>\n';
			}
			echo '<option value="curtainX"';
			if ($currentValue == "curtainX") {
				echo ' selected="selected">Curtain X</option>\n';
			} else {
				echo '>Curtain X</option>\n';
			}
			echo '<option value="curtainY"';
			if ($currentValue == "curtainY") {
				echo ' selected="selected">Curtain Y</option>\n';
			} else {
				echo '>Curtain Y</option>\n';
			}
			echo '<option value="fadeZoom"';
			if ($currentValue == "fadeZoom") {
				echo ' selected="selected">Fade Zoom</option>\n';
			} else {
				echo '>Fade Zoom</option>\n';
			}
			echo '<option value="growX"';
			if ($currentValue == "growX") {
				echo ' selected="selected">Grow X</option>\n';
			} else {
				echo '>Grow X</option>\n';
			}
			echo '<option value="growY"';
			if ($currentValue == "growY") {
				echo ' selected="selected">Grow Y</option>\n';
			} else {
				echo '>Grow Y</option>\n';
			}
			echo '<option value="slideX"';
			if ($currentValue == "slideX") {
				echo ' selected="selected">Slide X</option>\n';
			} else {
				echo '>Slide X</option>\n';
			}
			echo '<option value="slideY"';
			if ($currentValue == "slideY") {
				echo ' selected="selected">Slide Y</option>\n';
			} else {
				echo '>Slide Y</option>\n';
			}
			echo '<option value="toss"';
			if ($currentValue == "toss") {
				echo ' selected="selected">Toss</option>\n';
			} else {
				echo '>Toss</option>\n';
			}
			echo '<option value="turnUp"';
			if ($currentValue == "turnUp") {
				echo ' selected="selected">Turn Up</option>\n';
			} else {
				echo '>Turn Up</option>\n';
			}
			echo '<option value="turnDown"';
			if ($currentValue == "turnDown") {
				echo ' selected="selected">Turn Down</option>\n';
			} else {
				echo '>Turn Down</option>\n';
			}
			echo '<option value="turnRight"';
			if ($currentValue == "turnRight") {
				echo ' selected="selected">Turn Right</option>\n';
			} else {
				echo '>Turn Right</option>\n';
			}
			echo '<option value="turnLeft"';
			if ($currentValue == "turnLeft") {
				echo ' selected="selected">Turn Left</option>\n';
			} else {
				echo '>Turn Left</option>\n';
			}
			echo '<option value="uncover"';
			if ($currentValue == "uncover") {
				echo ' selected="selected">Uncover</option>\n';
			} else {
				echo '>Uncover</option>\n';
			}
			echo '<option value="wipe"';
			if ($currentValue == "wipe") {
				echo ' selected="selected">Wipe</option>\n';
			} else {
				echo '>Wipe</option>\n';
			}
			echo '<option value="zoom"';
			if ($currentValue == "zoom") {
				echo ' selected="selected">Zoom</option>\n';
			} else {
				echo '>Zoom</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmas_finallink') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="colorbox"';
			if ($currentValue == "colorbox") {
				echo ' selected="selected">Colorbox</option>\n';
			} else {
				echo '>Colorbox</option>\n';
			}

			echo '<option value="nolink"';
			if ($currentValue == "nolink") {
				echo ' selected="selected">No Link</option>\n';
			} else {
				echo '>No Link</option>\n';
			}

			echo '<option value="standard"';
			if ($currentValue == "standard") {
				echo ' selected="selected">Standard</option>\n';
			} else {
				echo '>Standard</option>\n';
			}

			echo '<option value="standard-new"';
			if ($currentValue == "standard-new") {
				echo ' selected="selected">New Window</option>\n';
			} else {
				echo '>New Window</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmas_thumbsize') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="small"';
			if ($currentValue == "small") {
				echo ' selected="selected">small</option>\n';
			} else {
				echo '>small</option>\n';
			}

			echo '<option value="large"';
			if ($currentValue == "large") {
				echo ' selected="selected">large</option>\n';
			} else {
				echo '>large</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmas_css') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="dark"';
			if ($currentValue == "dark") {
				echo ' selected="selected">dark</option>\n';
			} else {
				echo '>dark</option>\n';
			}

			echo '<option value="light"';
			if ($currentValue == "light") {
				echo ' selected="selected">light</option>\n';
			} else {
				echo '>light</option>\n';
			}

			echo "</select>\n";
		}
	}

}

?>