<?php
/**
 * Prints a list of all albums context sensitive.
 *
 * menu types:
 * 	<ul>
 * 			<li><var>list</var> for html list</li>
 * 			<li><var>list-top</var> for only the top level albums</li>
 * 			<li><var>omit-top</var> same as list, but the first level of albums is omitted</li>
 * 			<li><var>list-sub</var> lists the offspring level of subalbums for the current album</li>
 * 			<li><var>jump</var> dropdown menu of all albums(not context sensitive)</li>
 * 	</ul>
 *
 * Call the function <var>printAlbumMenu()</var> at the point where you want the menu to appear.
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_description = gettext("Adds a theme function to print an album menu either as a nested list or as a dropdown menu.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

$option_interface = 'print_album_menu';

if (!defined('MENU_TRUNCATE_STRING'))
	define('MENU_TRUNCATE_STRING', getOption('menu_truncate_string'));
if (!defined('MENU_TRUNCATE_INDICATOR'))
	define('MENU_TRUNCATE_INDICATOR', getOption('menu_truncate_indicator'));
define('ALBUM_MENU_COUNT', getOption('print_album_menu_count'));
define('ALBUM_MENU_SHOWSUBS', getOption('print_album_menu_showsubs'));

$_recursion_limiter = array();

/**
 * Plugin option handling class
 *
 */
class print_album_menu {

	function register_user_options() {
		setOptionDefault('print_album_menu_showsubs', 0);
		setOptionDefault('print_album_menu_count', 1);
		setOptionDefault('menu_truncate_string', 0);
		setOptionDefault('menu_truncate_indicator', '');
	}

	function getOptionsSupported() {
		global $_common_truncate_handler;
		$options = array(gettext('"List" subalbum level') => array('key'		 => 'print_album_menu_showsubs', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 0,
										'desc'	 => gettext('The depth of subalbum levels shown with the <code>printAlbumMenu</code> and <code>printAlbumMenuList</code> "List" option. Note: themes may override this default.')),
						gettext('Show counts')					 => array('key'		 => 'print_album_menu_count', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 1,
										'desc'	 => gettext('If checked, image and album counts will be included in the list. Note: Themes may override this option.')),
						gettext('Truncate titles*')			 => array('key'			 => 'menu_truncate_string', 'type'		 => OPTION_TYPE_TEXTBOX,
										'disabled' => $_common_truncate_handler,
										'order'		 => 6,
										'desc'		 => gettext('Limit titles to this many characters. Zero means no limit.')),
						gettext('Truncate indicator*')	 => array('key'			 => 'menu_truncate_indicator', 'type'		 => OPTION_TYPE_TEXTBOX,
										'disabled' => $_common_truncate_handler,
										'order'		 => 7,
										'desc'		 => gettext('Append this string to truncated titles.'))
		);
		if ($_common_truncate_handler) {
			$options['note'] = array('key'		 => 'menu_truncate_note', 'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 8,
							'desc'	 => '<p class="notebox">' . $_common_truncate_handler . '</p>');
		} else {
			$_common_truncate_handler = gettext('* These options may be set via the <a href="javascript:gotoName(\'print_album_menu\');"><em>print_album_menu</em></a> plugin options.');
			$options['note'] = array('key'		 => 'menu_truncate_note',
							'type'	 => OPTION_TYPE_NOTE,
							'order'	 => 8,
							'desc'	 => gettext('<p class="notebox">*<strong>Note:</strong> The setting of these options may be shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

}

/**
 * Prints a list of all albums context sensitive.
 * Since 1.4.3 this is a wrapper function for the separate functions printAlbumMenuList() and printAlbumMenuJump().
 * that was included to remain compatiblility with older installs of this menu.
 *
 * Usage: add the following to the php page where you wish to use these menus:
 * enable this extension on the zenphoto admin plugins tab.
 * Call the function printAlbumMenu() at the point where you want the menu to appear.
 *
 * @param string $option
 * 									"list" for html list,
 * 									"list-top" for only the top level albums,
 * 									"omit-top" same as list, but the first level of albums is omitted
 * 									"list-sub" lists the offspring level of subalbums for the current album
 * 									"jump" dropdown menu of all albums(not context sensitive)
 *
 * @param bool $showcount true for a image counter or subalbum count in brackets behind the album name, false for no image numbers or leave blank
 * @param string $css_id insert css id for the main album list, leave empty if you don't use (only list mode)
 * @param string $css_class_topactive insert css class for the active link in the main album list (only list mode)
 * @param string $css_class insert css class for the sub album lists (only list mode)
 * @param string $css_class_active insert css class for the active link in the sub album lists (only list mode)
 * @param string $indexname insert the name how you want to call the link to the gallery index (insert "" if you don't use it, it is not printed then)
 * @param int C Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param bool $firstimagelink If set to TRUE and if the album has images the link will point to page of the first image instead the album thumbnail page
 * @param bool $keeptopactive If set to TRUE the toplevel album entry will stay marked as active if within its subalbums ("list" only)
 * @param int $limit truncation of display text
 * @since 1.2
 */
function printAlbumMenu($option, $showcount = NULL, $css_id = '', $css_class_topactive = '', $css_class = '', $css_class_active = '', $indexname = "Gallery Index", $showsubs = NULL, $firstimagelink = false, $keeptopactive = false) {
	if ($option == "jump") {
		printAlbumMenuJump($showcount, $indexname, $firstimagelink);
	} else {
		printAlbumMenuList($option, $showcount, $css_id, $css_class_topactive, $css_class, $css_class_active, $indexname, $showsubs, $firstimagelink, $keeptopactive);
	}
}

/**
 * Prints a nested html list of all albums context sensitive.
 *
 * Usage: add the following to the php page where you wish to use these menus:
 * enable this extension on the zenphoto admin plugins tab;
 * Call the function printAlbumMenuList() at the point where you want the menu to appear.
 *
 * @param string $option
 * 									"list" for html list,
 * 									"list-top" for only the top level albums,
 * 									"omit-top" same as list, but the first level of albums is omitted
 * 									"list-sub" lists the offspring level of subalbums for the current album
 * @param bool $showcount true for a image counter in brackets behind the album name, false for no image numbers or leave empty
 * @param string $css_id insert css id for the main album list, leave empty if you don't use (only list mode)
 * @param string $css_id_active insert css class for the active link in the main album list (only list mode)
 * @param string $css_class insert css class for the sub album lists (only list mode)
 * @param string $css_class_active insert css class for the active link in the sub album lists (only list mode)
 * @param string $indexname insert the name (default "Gallery Index") how you want to call the link to the gallery index, insert "" if you don't use it, it is not printed then.
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param bool $firstimagelink If set to TRUE and if the album has images the link will point to page of the first image instead the album thumbnail page
 * @param bool $keeptopactive If set to TRUE the toplevel album entry will stay marked as active if within its subalbums ("list" only)
 * @param bool $startlist set to true to output the UL tab (false automatically if you use 'omit-top' or 'list-sub')
 * @param int $limit truncation of display text
 * @return html list of the albums
 */
function printAlbumMenuList($option, $showcount = NULL, $css_id = '', $css_class_topactive = '', $css_class = '', $css_class_active = '', $indexname = "Gallery Index", $showsubs = NULL, $firstimagelink = false, $keeptopactive = false, $startlist = true, $limit = NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_gallery_page;
	// if in search mode don't use the foldout contextsensitiveness and show only toplevel albums
	if (in_context(ZP_SEARCH_LINKED)) {
		$option = "list-top";
	}

	$albumpath = rewrite_path("/", "/index.php?album=");
	if (empty($_zp_current_album) || ($_zp_gallery_page != 'album.php' && $_zp_gallery_page != 'image.php')) {
		$currentfolder = "";
	} else {
		$currentfolder = $_zp_current_album->name;
	}

	// check if css parameters are used
	if ($css_id != "") {
		$css_id = " id='" . $css_id . "'";
	}
	if ($css_class_topactive != "") {
		$css_class_topactive = " class='" . $css_class_topactive . "'";
	}
	if ($css_class != "") {
		$css_class = " class='" . $css_class . "'";
	}
	if ($css_class_active != "") {
		$css_class_active = " class='" . $css_class_active . "'";
	}
	$startlist = $startlist && !($option == 'omit-top' || $option == 'list-sub');
	if ($startlist)
		echo "<ul" . $css_id . ">\n"; // top level list
	/*	 * ** Top level start with Index link  *** */
	if ($option === "list" OR $option === "list-top") {
		if (!empty($indexname)) {
			echo "<li><a href='" . html_encode(getGalleryIndexURL()) . "' title='" . html_encode($indexname) . "'>" . $indexname . "</a></li>";
		}
	}

	if ($option == 'list-sub' && in_context(ZP_ALBUM)) {
		$albums = $_zp_current_album->getAlbums();
	} else {
		$albums = $_zp_gallery->getAlbums();
	}

	printAlbumMenuListAlbum($albums, $albumpath, $currentfolder, $option, $showcount, $showsubs, $css_class, $css_class_topactive, $css_class_active, $firstimagelink, $keeptopactive, $limit);

	if ($startlist)
		echo "</ul>\n";
}

/**
 * Handles an album for printAlbumMenuList
 *
 * @param array $albums albums array
 * @param string $path for createAlbumMenuLink
 * @param string $folder
 * @param string $option see printAlbumMenuList
 * @param string $showcount see printAlbumMenuList
 * @param int $showsubs see printAlbumMenuList
 * @param string $css_class see printAlbumMenuList
 * @param string $css_class_topactive see printAlbumMenuList
 * @param string $css_class_active see printAlbumMenuList
 * @param bool $firstimagelink If set to TRUE and if the album has images the link will point to page of the first image instead the album thumbnail page
 * @param bool $keeptopactive If set to TRUE the toplevel album entry will stay marked as active if within its subalbums ("list" only)
 * @param int $limit truncation of display text
 */
function printAlbumMenuListAlbum($albums, $path, $folder, $option, $showcount, $showsubs, $css_class, $css_class_topactive, $css_class_active, $firstimagelink, $keeptopactive, $limit = NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_search, $_recursion_limiter;
	if (is_null($limit)) {
		$limit = MENU_TRUNCATE_STRING;
	}
	if (is_null($showcount))
		$showcount = ALBUM_MENU_COUNT;
	if (is_null($showsubs))
		$showsubs = ALBUM_MENU_SHOWSUBS;
	if ($showsubs && !is_numeric($showsubs))
		$showsubs = 9999999999;
	$pagelevel = count(explode('/', $folder));
	$currenturalbumname = "";

	foreach ($albums as $album) {

		$level = count(explode('/', $album));
		$process = (($level < $showsubs && $option == "list") // user wants all the pages whose level is <= to the parameter
						|| ($option != 'list-top' // not top only
						&& strpos($folder, $album) === 0 // within the family
						&& $level <= $pagelevel) // but not too deep\
						);

		if ($process && hasDynamicAlbumSuffix($album)) {
			if (in_array($album, $_recursion_limiter))
				$process = false; // skip already seen dynamic albums
		}
		$topalbum = newAlbum($album, true);
		if ($level > 1 || ($option != 'omit-top')) { // listing current level album
			if ($level == 1) {
				$css_class_t = $css_class_topactive;
			} else {
				$css_class_t = $css_class_active;
			}
			if ($keeptopactive) {
				if (isset($_zp_current_album) && is_object($_zp_current_album)) {
					$currenturalbum = getUrAlbum($_zp_current_album);
					$currenturalbumname = $currenturalbum->name;
				}
			}
			$count = "";
			if ($showcount) {
				$toplevelsubalbums = $topalbum->getAlbums();
				$toplevelsubalbums = count($toplevelsubalbums);
				$topalbumnumimages = $topalbum->getNumImages();
				if ($topalbumnumimages + $toplevelsubalbums > 0) {
					$count = ' <span style="white-space:nowrap;"><small>(';
					if ($toplevelsubalbums > 0) {
						$count .= sprintf(ngettext('%u album', '%u albums', $toplevelsubalbums), $toplevelsubalbums);
					}
					if ($topalbumnumimages > 0) {
						if ($toplevelsubalbums) {
							$count .= ' ';
						}
						$count .= sprintf(ngettext('%u image', '%u images', $topalbumnumimages), $topalbumnumimages);
					}
					$count .= ')</small></span>';
				}
			}

			if ((in_context(ZP_ALBUM) && !in_context(ZP_SEARCH_LINKED) && (@$_zp_current_album->getID() == $topalbum->getID() ||
							$topalbum->name == $currenturalbumname)) ||
							(in_context(ZP_SEARCH_LINKED)) && ($a = $_zp_current_search->getDynamicAlbum()) && $a->name == $topalbum->name) {
				$current = $css_class_t . ' ';
			} else {
				$current = "";
			}
			$title = $topalbum->getTitle();
			if ($limit) {
				$display = shortenContent($title, $limit, MENU_TRUNCATE_INDICATOR);
			} else {
				$display = $title;
			}
			if ($firstimagelink && $topalbum->getNumImages() != 0) {
				$link = "<li><a " . $current . "href='" . html_encode($topalbum->getImage(0)->getImageLink()) . "' title='" . html_encode($title) . "'>" . html_encode($display) . "</a>" . $count;
			} else {
				$link = "<li><a " . $current . "href='" . html_encode($topalbum->getAlbumLink(0)) . "' title='" . html_encode($title) . "'>" . html_encode($display) . "</a>" . $count;
			}
			echo $link;
		}
		if ($process) { // listing subalbums
			$subalbums = $topalbum->getAlbums();
			if (!empty($subalbums)) {
				echo "\n<ul" . $css_class . ">\n";
				array_push($_recursion_limiter, $album);
				printAlbumMenuListAlbum($subalbums, $path, $folder, $option, $showcount, $showsubs, $css_class, $css_class_topactive, $css_class_active, $firstimagelink, false, $limit);
				array_pop($_recursion_limiter);
				echo "\n</ul>\n";
			}
		}
		if ($option == 'list' || $option == 'list-top' || $level > 1) { // close the LI
			echo "\n</li>\n";
		}
	}
}

/**
 * Prints a dropdown menu of all albums(not context sensitive)
 * Is used by the wrapper function printAlbumMenu() if the options "jump" is choosen. For standalone use, too.
 *
 * Usage: add the following to the php page where you wish to use these menus:
 * enable this extension on the zenphoto admin plugins tab;
 * Call the function printAlbumMenuJump() at the point where you want the menu to appear.
 *
 * @param string $option "count" for a image counter in brackets behind the album name, "" = for no image numbers
 * @param string $indexname insert the name (default "Gallery Index") how you want to call the link to the gallery index, insert "" if you don't use it, it is not printed then.
 * @param bool $firstimagelink If set to TRUE and if the album has images the link will point to page of the first image instead the album thumbnail page
 */
function printAlbumMenuJump($option = "count", $indexname = "Gallery Index", $firstimagelink = false) {
	global $_zp_gallery, $_zp_current_album, $_zp_gallery_page;
	$albumpath = rewrite_path("/", "/index.php?album=");
	if (!is_null($_zp_current_album) || $_zp_gallery_page == 'album.php') {
		$currentfolder = $_zp_current_album->name;
	}
	?>
	<script type="text/javaScript">
		// <!-- <![CDATA[
		function gotoLink(form) {
		var OptionIndex=form.ListBoxURL.selectedIndex;
		parent.location = form.ListBoxURL.options[OptionIndex].value;
		}
		// ]]> -->
	</script>
	<form name="AutoListBox" action="#">
		<p>
			<select name="ListBoxURL" size="1" onchange="gotoLink(this.form);">
				<?php
				if (!empty($indexname)) {
					$selected = checkSelectedAlbum("", "index");
					?>
					<option <?php echo $selected; ?> value="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo $indexname; ?></option>
					<?php
				}
				$albums = $_zp_gallery->getAlbums();
				printAlbumMenuJumpAlbum($albums, $option, $albumpath, $firstimagelink);
				?>
			</select>
		</p>
	</form>
	<?php
}

/**
 * Handles a single album level for printAlbumMenuJump
 *
 * @param array $albums list of album names
 * @param string $showcount see printAlbumMenuJump
 * @param string $albumpath path of the page album
 * @param bool $firstimagelink If set to TRUE and if the album has images the link will point to page of the first image instead the album thumbnail page
 * @param int $level current level
 */
function printAlbumMenuJumpAlbum($albums, $option, $albumpath, $firstimagelink, $level = 1) {
	global $_zp_gallery;
	foreach ($albums as $album) {
		$subalbum = newAlbum($album, true);


		if ($option === "count" AND $subalbum->getNumImages() > 0) {
			$count = " (" . $subalbum->getNumImages() . ")";
		} else {
			$count = "";
		}
		$arrow = str_replace(':', '» ', str_pad("", $level - 1, ":"));

		$selected = checkSelectedAlbum($subalbum->name, "album");
		if ($firstimagelink && $subalbum->getNumImages() != 0) {
			$link = "<option $selected value='" . html_encode($subalbum->getImage(0)->getImageLink()) . "'>" . $arrow . strip_tags($subalbum->getTitle()) . $count . "</option>";
		} else {
			$link = "<option $selected value='" . html_encode($albumpath . pathurlencode($subalbum->name)) . "'>" . $arrow . strip_tags($subalbum->getTitle()) . $count . "</option>";
		}
		echo $link;
		$subalbums = $subalbum->getAlbums();
		if (!empty($subalbums)) {
			printAlbumMenuJumpAlbum($subalbums, $option, $albumpath, $firstimagelink, $level + 1);
		}
	}
}

/**
 * A printAlbumMenu() helper function for the jump menu mode of printAlbumMenu() that only
 * checks which the current album so that the entry in the in the dropdown jump menu can be selected
 * Not for standalone use.
 *
 * @param string $checkalbum The album folder name to check
 * @param string $option "index" for index level, "album" for album level
 * @return string returns nothing or "selected"
 */
function checkSelectedAlbum($checkalbum, $option) {
	global $_zp_current_album, $_zp_gallery_page;
	if (is_object($_zp_current_album)) {
		$currentalbumname = $_zp_current_album->name;
	} else {
		$currentalbumname = "";
	}
	$selected = "";
	switch ($option) {
		case "index":
			if ($_zp_gallery_page === "index.php") {
				$selected = "selected";
			}
			break;
		case "album":
			if ($currentalbumname === $checkalbum) {
				$selected = "selected";
			}
			break;
	}
	return $selected;
}
?>