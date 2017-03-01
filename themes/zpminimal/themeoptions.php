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
		setThemeOptionDefault('zpmin_homeoption', '');
		setThemeOptionDefault('zpmin_album_thumb_size', 158);
		setThemeOptionDefault('zpmin_switch', false);
		setThemeOptionDefault('zpmin_menu', '');
		setThemeOptionDefault('zpmin_logo', '');
		setThemeOptionDefault('zpmin_colorbox', true);
		setThemeOptionDefault('zpmin_cbstyle', 'style3');
		setThemeOptionDefault('zpmin_zpsearchcount', 2);
		setThemeOptionDefault('zpmin_finallink', 'nolink');
		setThemeOptionDefault('jcarousel_zpminimal_image', 1);
	}

	function getOptionsDisabled() {
		return array('image_size', 'image_use_side');
	}

	function getOptionsSupported() {
		return array(
				gettext('Home Page Image Option') => array('key' => 'zpmin_homeoption', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose the option for the single image on the homepage.  See Image-Album-Statistics plugin or Random Image functions for more information.')),
				gettext('Final Image Link Option') => array('key' => 'zpmin_finallink', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Choose the option for the final image link on image.php.  Can either link to full image using standard zenphoto process (with core options), colorbox, or no link (default).')),
				gettext('Sidebar Position on the Right?') => array('key' => 'zpmin_switch', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to reverse the sidebar and content area positions.  Default (unchecked) is the sidebar on the left.')),
				gettext('Use colorbox on Album and Search Pages?') => array('key' => 'zpmin_colorbox', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('If checked, 2 links appear when hovering images in album and search pages, one to popup the image in colorbox, and the other to go to the details page.')),
				gettext('Colorbox Style') => array('key' => 'zpmin_cbstyle', 'type' => OPTION_TYPE_CUSTOM, 'desc' => gettext('Select the Colorbox style you wish to use (examples on the colorbox site).')),
				gettext('Disable MetaData Display?') => array('key' => 'zpmin_disablemeta', 'type' => OPTION_TYPE_CHECKBOX, 'desc' => gettext('Check to disable the metadata (EXIF,IPTC) display on the image page.')),
				gettext('Album Thumb Size') => array('key' => 'zpmin_album_thumb_size', 'type' => OPTION_TYPE_NUMBER, 'desc' => gettext('Select the size of album thumbs. The thumb size above is for image thumbs. Album thumbs will also take on your selection of cropping or not.  A good size for albums is 158 (3 per row), and a good size above for image thumbs is 113 (4 per row). Make sure you also set the number of thumbs per row for images and albums above! ')),
				gettext('Menu Name of Menu Manager') => array('key' => 'zpmin_menu', 'type' => OPTION_TYPE_TEXTBOX, 'multilingual' => false, 'desc' => gettext('If you use the menu manager plugin, enter the name of the menu you would like to use.  Make sure you create the menu in the menu manager backend! Or leave blank to use the theme menu.')),
				gettext('ZenPage Search Results') => array('key' => 'zpmin_zpsearchcount', 'type' => OPTION_TYPE_NUMBER, 'desc' => gettext('If using Zenpage, enter the number of search results to display for each news and pages.  Default is 2 (4 total possible).')),
				gettext('Logo Image') => array('key' => 'zpmin_logo', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Enter an image file located in the themes image directory, including extension, to use as an image logo. Or leave blank to use a text representation of your Gallery name.  As an example there is a logo.gif image in the images directory.'))
		);
	}

	function handleOption($option, $currentValue) {

		if ($option == 'zpmin_cbstyle') {
			echo '<select style="width:200px;" id="' . $option . '" name="' . $option . '"' . ">\n";

			echo '<option value="style1"';
			if ($currentValue == "style1") {
				echo ' selected="selected">style1</option>\n';
			} else {
				echo '>style1</option>\n';
			}

			echo '<option value="style2"';
			if ($currentValue == "style2") {
				echo ' selected="selected">style2</option>\n';
			} else {
				echo '>style2</option>\n';
			}

			echo '<option value="style3"';
			if ($currentValue == "style3") {
				echo ' selected="selected">style3</option>\n';
			} else {
				echo '>style3</option>\n';
			}

			echo '<option value="style4"';
			if ($currentValue == "style4") {
				echo ' selected="selected">style4</option>\n';
			} else {
				echo '>style4</option>\n';
			}

			echo '<option value="style5"';
			if ($currentValue == "style5") {
				echo ' selected="selected">style5</option>\n';
			} else {
				echo '>style5</option>\n';
			}

			echo "</select>\n";
		}

		if ($option == 'zpmin_homeoption') {
			echo '<select id="' . $option . '" name="' . $option . '"' . ">\n";
			echo '<option value="random"';
			if ($currentValue == "random") {
				echo ' selected="selected">Random</option>\n';
			} else {
				echo '>Random</option>\n';
			}
			echo '<option value="random-daily"';
			if ($currentValue == "random-daily") {
				echo ' selected="selected">Random Daily</option>\n';
			} else {
				echo '>Random Daily</option>\n';
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

		if ($option == 'zpmin_finallink') {
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
	}

}

?>