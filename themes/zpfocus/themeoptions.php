<?php

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

require_once(SERVERPATH . "/" . ZENFOLDER . "/admin-functions.php");

class ThemeOptions {

	function __construct() {
		// force core theme options for this theme
		setThemeOption('albums_per_row', 2, null);
		setThemeOption('images_per_row', 3, null);
		setThemeOption('thumb_size', 160, null);
		setThemeOption('thumb_crop', 0, null);
		setThemeOption('image_size', 800, null); //for sized cb target option only
		setThemeOption('image_use_side', 'longest', null);

		// set core theme option defaults
		setThemeOptionDefault('albums_per_page', 6);
		setThemeOptionDefault('images_per_page', 9);
		setThemeOptionDefault('thumb_transition', 2);

		// set theme option defaults
		setThemeOptionDefault('zpfocus_tagline', 'A ZenPhoto / ZenPage Powered Theme');
		setThemeOptionDefault('zpfocus_allow_search', true);
		setThemeOptionDefault('zpfocus_show_archive', true);
		setThemeOptionDefault('zpfocus_use_colorbox', true);
		setThemeOptionDefault('zpfocus_use_colorbox_slideshow', true);
		setThemeOptionDefault('zpfocus_homepage', 'none');
		setThemeOptionDefault('zpfocus_spotlight', 'manual');
		setThemeOptionDefault('zpfocus_spotlight_text', '<p>This is the <span class="spotlight-span">spotlight</span> area that can be set in the theme options.  You can either enter the text manually in the options or set it to display the latest news if ZenPage is being used. If you want nothing to appear here, set the spotlight to none.</p>');
		setThemeOptionDefault('zpfocus_show_credit', true);
		setThemeOptionDefault('zpfocus_menutype', 'dropdown');
		setThemeOptionDefault('zpfocus_logotype', true);
		setThemeOptionDefault('zpfocus_logofile', 'logo.jpg');
		setThemeOptionDefault('zpfocus_showrandom', 'rotator');
		setThemeOptionDefault('zpfocus_rotatoreffect', 'fade');
		setThemeOptionDefault('zpfocus_rotatorspeed', '3000');
		setThemeOptionDefault('zpfocus_cbtarget', true);
		setThemeOptionDefault('zpfocus_cbstyle', 'example3');
		setThemeOptionDefault('zpfocus_cbtransition', 'fade');
		setThemeOptionDefault('zpfocus_cbssspeed', '2500');
		setThemeOptionDefault('zpfocus_final_link', 'nolink');
		setThemeOptionDefault('zpfocus_news', true);

		// plugin options
		setOptionDefault('jcarousel_zpfocus_image', 1);

		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('zpfocus');
			cacheManager::addThemeCacheSize('zpfocus', null, 600, 900, null, null, null, null, false, getOption('fullimage_watermark'), false, true);
			cacheManager::addThemeCacheSize('zpfocus', null, 300, 300, 300, 300, null, null, true, getOption('fullimage_watermark'), false, false);
			if ((getOption('zpfocus_use_colorbox')) && (getOption('zpfocus_cbtarget'))) {
				cacheManager::addThemeCacheSize('zpfocus', 800, null, null, null, null, null, null, false, getOption('fullimage_watermark'), false, false);
			}
		}
	}

	function getOptionsSupported() {
		return array(
				gettext('Tagline') => array('order' => 1, 'key' => 'zpfocus_tagline', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'desc' => gettext('The text above the sitename on the home page.')),
				gettext('Album Menu Type') => array('order' => 2, 'key' => 'zpfocus_menutype', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose whether to show a dropdown menu item in the main menu for all your albums or a jump menu next to the search input. For sites with a lot of albums, the jump menu is recommended.')),
				gettext('Show Archive Link') => array('order' => 3, 'key' => 'zpfocus_show_archive', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Display a menu link drop down for the dated archive of images.')),
				gettext('Allow search') => array('order' => 4, 'key' => 'zpfocus_allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable search form.')),
				gettext('Use Image as Logo?') => array('order' => 5, 'key' => 'zpfocus_logotype', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to use an image file instead of text as your logo.')),
				gettext('Use Image as Logo Filename:') => array('order' => 6, 'key' => 'zpfocus_logofile', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => 1, 'multilingual' => false, 'desc' => gettext('If checked above, enter full file name of logo file including file extension(image must be located within the images folder of the zpFocus theme folder). ')),
				gettext('Homepage') => array('order' => 7, 'key' => 'zpfocus_homepage', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext("Choose here any <em>unpublished Zenpage page</em> (listed by <em>titlelink</em>) to act as your site's homepage instead the normal gallery index.")),
				gettext('Spotlight') => array('order' => 8, 'key' => 'zpfocus_spotlight', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select what to use in the spotlight area. Latest News obviously requires ZenPage.')),
				gettext('Spotlight Text') => array('order' => 9, 'key' => 'zpfocus_spotlight_text', 'type' => OPTION_TYPE_TEXTAREA, 'desc' => gettext('Enter "Spotlight Text" if option above for the spotlight area is set to manual.  If Latest News is selected above this text will NOT be displayed.')),
				gettext('Use Colorbox') => array('order' => 10, 'key' => 'zpfocus_use_colorbox', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to utilize the jQuery script colorbox to zoom images on the album page.')),
				gettext('Colorbox Style') => array('order' => 11, 'key' => 'zpfocus_cbstyle', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select the Colorbox style you wish to use (examples on the colorbox site).')),
				gettext('Colorbox Target Sized Image') => array('order' => 12, 'key' => 'zpfocus_cbtarget', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Click to enable colorbox targeting a sized image of 800px (longest side), instead of the full original image. This is usefull if you upload large images as you can set Colorbox to target a smaller, resized version.')),
				gettext('Colorbox Transition Type') => array('order' => 13, 'key' => 'zpfocus_cbtransition', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('The colorbox transition type. Can be set to elastic, fade, or none.')),
				gettext('Use Colorbox Slideshow') => array('order' => 14, 'key' => 'zpfocus_use_colorbox_slideshow', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to utilize the jQuery script colorbox to display a slideshow on the album page.')),
				gettext('Colorbox Slideshow Speed') => array('order' => 15, 'key' => 'zpfocus_cbssspeed', 'type' => OPTION_TYPE_NUMBER, 'desc' => gettext('Enter a number here in milliseconds that determines the colorbox slideshow speed. Default is \'2500\'.')),
				gettext('Random Image Option?') => array('order' => 16, 'key' => 'zpfocus_showrandom', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose how to display random image(s), top left. Or select none to not display.')),
				gettext('Rotator Transition Effect?') => array('order' => 17, 'key' => 'zpfocus_rotatoreffect', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose the transition effect, if rotator is selected above.')),
				gettext('Rotator Speed?') => array('order' => 18, 'key' => 'zpfocus_rotatorspeed', 'type' => OPTION_TYPE_NUMBER, 'desc' => gettext('Choose the delay of each rotation in milliseconds.')),
				gettext('Image Final Link Option') => array('order' => 19, 'key' => 'zpfocus_final_link', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select the final image link option as viewed on image.php.  Default is no link, but choose standard (or new to open in new window) if you want to take advantage of some of the core image link options (such as automatic download).')),
				gettext('Show zenphoto Credit') => array('order' => 22, 'key' => 'zpfocus_show_credit', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to display the Powered by ZenPhoto20 link in the footer.')),
				gettext('Custom CSS') => array('order' => 21, 'key' => 'zpfocus_customcss', 'type' => OPTION_TYPE_TEXTAREA, 'desc' => gettext('Enter any custom CSS, safely carries over upon theme upgrade.')),
				gettext('Enable Zenpage News') => array('order' => 20, 'key' => 'zpfocus_news', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to enable the news function of Zenpage (default). Uncheck to use pages only (hides menu items and search results of news).'))
		);
	}

	function getOptionsDisabled() {
		return array('thumb_size', 'thumb_crop', 'image_size');
	}

	function handleOption($option, $currentValue) {

		if ($option == 'zpfocus_showrandom') {
			echo '<select style="width:200px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="single"';
			if ($currentValue == "single") {
				echo ' selected="selected">Single Random</option>\n';
			} else {
				echo '>single</option>\n';
			}

			echo '<option value="rotator"';
			if ($currentValue == "rotator") {
				echo ' selected="selected">rotator</option>\n';
			} else {
				echo '>rotator</option>\n';
			}

			echo '<option value="none"';
			if ($currentValue == "none") {
				echo ' selected="selected">None</option>\n';
			} else {
				echo '>none</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpfocus_rotatoreffect') {
			echo '<select style="width:200px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

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

		if ($option == 'zpfocus_cbstyle') {
			echo '<select style="width:200px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

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

		if ($option == 'zpfocus_cbtransition') {
			echo '<select style="width:200px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

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

		if ($option == 'zpfocus_final_link') {
			echo '<select style="width:200px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="colorbox"';
			if ($currentValue == "colorbox") {
				echo ' selected="selected">colorbox</option>\n';
			} else {
				echo '>colorbox</option>\n';
			}

			echo '<option value="nolink"';
			if ($currentValue == "nolink") {
				echo ' selected="selected">nolink</option>\n';
			} else {
				echo '>nolink</option>\n';
			}

			echo '<option value="standard"';
			if ($currentValue == "standard") {
				echo ' selected="selected">standard</option>\n';
			} else {
				echo '>standard</option>\n';
			}

			echo '<option value="standard-new"';
			if ($currentValue == "standard-new") {
				echo ' selected="selected">standard-new</option>\n';
			} else {
				echo '>standard-new</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpfocus_menutype') {
			echo '<select style="width:100px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="dropdown"';
			if ($currentValue == "dropdown") {
				echo ' selected="selected">DropDown</option>\n';
			} else {
				echo '>DropDown</option>\n';
			}

			echo '<option value="jump"';
			if ($currentValue == 'jump') {
				echo ' selected="selected">Jump</option>\n';
			} else {
				echo '>Jump</option>\n';
			}

			echo "</select>\n";
		}


		if ($option == 'zpfocus_spotlight') {
			echo '<select style="width:100px;
						" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="none"';
			if ($currentValue == "none") {
				echo ' selected="selected">None</option>\n';
			} else {
				echo '>None</option>\n';
			}

			echo '<option value="manual"';
			if ($currentValue == 'manual') {
				echo ' selected="selected">Manual</option>\n';
			} else {
				echo '>Manual</option>\n';
			}

			echo '<option value="latest"';
			if ($currentValue == 'latest') {
				echo ' selected="selected">Latest News</option>\n';
			} else {
				echo '>Latest News</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == "zpfocus_homepage") {
			$unpublishedpages = query_full_array("SELECT titlelink FROM " . prefix('pages') . " WHERE `show` != 1 ORDER by `sort_order`");
			if (empty($unpublishedpages)) {
				echo gettext("No unpublished pages available");
				// clear option if no unpublished pages are available or have been published meanwhile
				// so that the normal gallery index appears and no page is accidentally set if set to unpublished again.
				setOption("zpfocus_homepage", "none");
			} else {
				echo '<input type="hidden" name="' . CUSTOM_OPTION_PREFIX . 'selector-zpfocus_homepage" value="0" />' . "\n";
				echo '<select id="' . $option . '" name="zpfocus_homepage">' . "\n";
				if ($currentValue === "none") {
					$selected = " selected = 'selected'";
				} else {
					$selected = "";
				}
				echo "<option$selected>" . gettext("none") . "</option>";
				foreach ($unpublishedpages as $page) {
					if ($currentValue === $page["titlelink"]) {
						$selected = "  selected =    'selected'";
					} else {
						$selected = "";
					}
					echo "<option$selected>" . $page["titlelink"] . "</option>";
				}
				echo "</select>\n";
			}
		}
	}

}

?>