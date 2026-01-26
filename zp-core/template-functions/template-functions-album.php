<?php
/**
 * Album related template functions
 * 
 * @since 1.7 moved to separate file from template-functions.php
 * 
 * @package zpcore\functions\template
 */

/**
 * Sets the album passed as the current album
 *
 * @param object $album the album to be made current
 */
function makeAlbumCurrent($album) {
	global $_zp_current_album;
	$_zp_current_album = $album;
	set_context(ZP_INDEX | ZP_ALBUM);
}

/**
 * Returns the raw title of the current album.
 *
 * @return string
 */
function getAlbumTitle() {
	if (!in_context(ZP_ALBUM))
		return false;
	global $_zp_current_album;
	return $_zp_current_album->getTitle();
}

/**
 * Returns a text-only title of the current album.
 *
 * @return string
 */
function getBareAlbumTitle() {
	return getBare(getAlbumTitle());
}

/**
 * Returns an album title taged with of Not visible or password protected status
 *
 * @return string;
 */
function getAnnotatedAlbumTitle() {
	global $_zp_current_album;
	$title = getBareAlbumTitle();
	$pwd = $_zp_current_album->getPassword();
	if (zp_loggedin() && !empty($pwd)) {
		$title .= "\n" . gettext('The album is password protected.');
	}
	if (!$_zp_current_album->isPublished()) {
		$title .= "\n" . gettext('The album is un-published.');
	}
	return $title;
}

function printAnnotatedAlbumTitle() {
	echo html_encode(getAnnotatedAlbumTitle());
}

/**
 * Prints an encapsulated title of the current album.
 * If you are logged in you can click on this to modify the title on the fly.
 *
 * @author Ozh
 */
function printAlbumTitle() {
	echo html_encodeTagged(getAlbumTitle());
}

function printBareAlbumTitle() {
	echo html_encodeTagged(getBareAlbumTitle());
}

/**
 * Gets the 'n' for n of m albums
 *
 * @return int
 */
function albumNumber() {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_gallery;
	$name = $_zp_current_album->getName();
	if (in_context(ZP_SEARCH)) {
		$albums = $_zp_current_search->getAlbums();
	} else if (in_context(ZP_ALBUM)) {
		$parent = $_zp_current_album->getParent();
		if (is_null($parent)) {
			$albums = $_zp_gallery->getAlbums();
		} else {
			$albums = $parent->getAlbums();
		}
	}
	$c = 0;
	foreach ($albums as $albumfolder) {
		$c++;
		if ($name == $albumfolder) {
			return $c;
		}
	}
	return false;
}

/**
 * Returns an array of the names of the parents of the current album.
 *
 * @param object $album optional album object to use inseted of the current album
 * @return array
 */
function getParentAlbums($album = null) {
	$parents = array();
	if (in_context(ZP_ALBUM)) {
		global $_zp_current_album, $_zp_current_search, $_zp_gallery;
		if (is_null($album)) {
			if (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) {
				$album = $_zp_current_search->getDynamicAlbum();
				if (empty($album))
					return $parents;
			} else {
				$album = $_zp_current_album;
			}
		}
		while (!is_null($album = $album->getParent())) {
			array_unshift($parents, $album);
		}
	}
	return $parents;
}

/**
 * returns the breadcrumb item for the current images's album
 *
 * @param string $title Text to be used as the URL title tag
 * @return array
 */
function getAlbumBreadcrumb($title = NULL) {
	global $_zp_current_search, $_zp_gallery, $_zp_current_album, $_zp_last_album;
	$output = array();
	if (in_context(ZP_SEARCH_LINKED)) {
		$album = NULL;
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			if (!is_null($_zp_current_album)) {
				if (in_context(ZP_ALBUM_LINKED) && $_zp_last_album == $_zp_current_album->name) {
					$album = $_zp_current_album;
				}
			}
		} else {
			if (in_context(ZP_IMAGE) && in_context(ZP_ALBUM_LINKED)) {
				$album = $_zp_current_album;
			} else {
				$album = $dynamic_album;
			}
		}
	} else {
		$album = $_zp_current_album;
	}
	if ($album) {
		if (is_null($title)) {
			$title = $album->getTitle();
			if (empty($title)) {
				$title = gettext('Album Thumbnails');
			}
		}
		return array('link' => $album->getLink(getAlbumPage()), 'text' => $title, 'title' => getBare($title));
	}
	return false;
}

/**
 * prints the breadcrumb item for the current images's album
 *
 * @param string $before Text to place before the breadcrumb
 * @param string $after Text to place after the breadcrumb
 * @param string $title Text to be used as the URL title attribute and text link
 */
function printAlbumBreadcrumb($before = '', $after = '', $title = NULL) {
	if ($breadcrumb = getAlbumBreadcrumb($title)) {
		if ($before) {
			$output = '<span class="beforetext">' . html_encode($before) . '</span>';
		} else {
			$output = '';
		}
		$output .= '<a href="' . html_encode($breadcrumb['link']) . '" title="' . html_encode($breadcrumb['title']) . '">';
		$output .= html_encode($breadcrumb['text']);
		$output .= '</a>';
		if ($after) {
			$output .= '<span class="aftertext">' . html_encode($after) . '</span>';
		}
		echo $output;
	}
}

/**
 * Prints the "breadcrumb" for a search page
 * 		if the search was for a data range, the breadcrumb is "Archive"
 * 		otherwise it is "Search"
 * @param string $between Insert here the text to be printed between the links
 * @param string $class is the class for the link (if present)
 * @param string $search text for a search page title
 * @param string $archive text for an archive page title
 * @param string $format data format for archive page breadcrumb - A datetime format, if using localized dates an ICU dateformat
 */
function printSearchBreadcrumb($between = NULL, $class = NULL, $search = NULL, $archive = NULL, $format = 'F Y') {
	global $_zp_current_search;
	if (is_null($between)) {
		$between = ' | ';
	}
	if ($class) {
		$class = ' class="' . $class . '"';
	}
	$searchmode = $_zp_current_search->getMode();
	switch($searchmode) {
		default:
		case 'search':
		case 'tag':
			if (is_null($search)) {
				$text = gettext('Search');
				$textdecoration = true;
			} else {
				$text = getBare(html_encode($search));
				$textdecoration = false;
			}
			$date = '';
			$link = sprintf('%s' . $text . '%s', $textdecoration ? '<em>' : '', $textdecoration ? '</em>' : '');
			break;
		case 'archive':
			if (is_null($archive)) {
				$text = gettext('Archive');
			} else {
				$text = getBare(html_encode($archive));
			}
			if ($format) {
				$date = zpFormattedDate($format, $_zp_current_search->getSearchDate());
			} else {
				$date = zpFormattedDate('F Y', $_zp_current_search->getSearchDate());
			}
			$textdecoration = true;
			$link = '<a href="' . html_encode(getCustomPageURL('archive', NULL)) . '"'.$class.' title="'. html_encode(strip_tags($text)) . '">';
			$link .= sprintf('%s' . $text . '%s', $textdecoration ? '<em>' : '', $textdecoration ? '</em>' : '');
			$link .= '</a>';
			$link .= '<span class="betweentext">' . html_encode($between) . '</span>';
			$link .= $date;
			break;
	}
	echo $link;
}

/**
 * returns the breadcrumb navigation for album, gallery and image view.
 *
 * @return array
 */
function getParentBreadcrumb() {
	global $_zp_gallery, $_zp_current_search, $_zp_current_album, $_zp_last_album;
	$parents = $output = array();
	if (in_context(ZP_SEARCH_LINKED)) {
		if (in_context(ZP_IMAGE) && !in_context(ZP_ALBUM_LINKED)) {
			$alb_pages = ceil($_zp_current_search->getNumAlbums() / max(1, getOption('albums_per_page')));
			$img_pages = ceil((imageNumber() - getFirstPageImages()) / max(1, getOption('images_per_page')));
			$page = $alb_pages + $img_pages;
		} else {
			$page = $_zp_current_search->page;
		}
		$searchwords = $_zp_current_search->getSearchWords();
		$searchdate = $_zp_current_search->getSearchDate();
		$searchfields = $_zp_current_search->getSearchFields(true);
		$searchpagepath = SearchEngine::getSearchURL($searchwords, $searchdate, $searchfields, $page);
		$dynamic_album = $_zp_current_search->getDynamicAlbum();
		if (empty($dynamic_album)) {
			if (empty($searchdate)) {
				$output[] = array('link' => $searchpagepath, 'title' => gettext("Return to search"), 'text' => gettext("Search"));
				if (is_null($_zp_current_album)) {
					return $output;
				} else {
					$parents = getParentAlbums();
				}
			} else {
				return array(array('link' => $searchpagepath, 'title' => gettext("Return to archive"), 'text' => gettext("Archive")));
			}
		} else {
			$album = $dynamic_album;
			$parents = getParentAlbums($album);
			if (in_context(ZP_ALBUM_LINKED)) {
				array_push($parents, $album);
			}
		}
	} else {
		$parents = getParentAlbums();
	}
	if ($parents) {
		array_push($parents, $_zp_current_album);
		$index = -1;
		foreach ($parents as $parent) {
			$index++;
			if($index != 0) {
				$parentparent = $parents[$index-1];
				$page = $parent->getGalleryPage();
				$url = $parentparent->getLink($page);
				$output[] = array('link' => html_encode($url), 'title' => $parentparent->getTitle(), 'text' => $parentparent->getTitle());
			}
		}
	}
	return $output;
}

/**
 * Prints the breadcrumb navigation for album, gallery and image view.
 *
 * @param string $before Insert here the text to be printed before the links
 * @param string $between Insert here the text to be printed between the links
 * @param string $after Insert here the text to be printed after the links
 * @param mixed $truncate if not empty, the max lenght of the description.
 * @param string $elipsis the text to append to the truncated description
 */
function printParentBreadcrumb($before = NULL, $between = NULL, $after = NULL, $truncate = NULL, $elipsis = NULL) {
	$crumbs = getParentBreadcrumb();
	if (!empty($crumbs)) {
		if (is_null($between)) {
			$between = ' | ';
		}
		if (is_null($after)) {
			$after = ' | ';
		}
		if (is_null($elipsis)) {
			$elipsis = '...';
		}
		if ($before) {
			$output = '<span class="beforetext">' . html_encode($before) . '</span>';
		} else {
			$output = '';
		}
		if ($between) {
			$between = '<span class="betweentext">' . html_encode($between) . '</span>';
		}
		$i = 0;
		foreach ($crumbs as $crumb) {
			if ($i > 0) {
				$output .= $between;
			}
//cleanup things in description for use as attribute tag
			$desc = $crumb['title'];
			if (!empty($desc) && $truncate) {
				$desc = truncate_string($desc, $truncate, $elipsis);
			}
			$output .= '<a href="' . html_encode($crumb['link']) . '"' . ' title="' . html_encode(getBare($desc)) . '">' . html_encode($crumb['text']) . '</a>';
			$i++;
		}
		if ($after) {
			$output .= '<span class="aftertext">' . html_encode($after) . '</span>';
		}
		echo $output;
	}
}

/**
 * Prints a link to the 'main website', not the Zenphoto site home page!
 * Only prints the link if the url is not empty and does not point back the gallery page
 *
 * @param string $before text to precede the link
 * @param string $after text to follow the link
 * @param string $title Title text
 * @param string $class optional css class
 * @param string $id optional css id
 *  */
function printHomeLink($before = '', $after = '', $title = NULL, $class = NULL, $id = NULL) {
	global $_zp_gallery;
	$site = rtrim(strval($_zp_gallery->getParentSiteURL()), '/');
	if (!empty($site)) {
		$name = $_zp_gallery->getParentSiteTitle();
		if (empty($name)) {
			$name = gettext('Home');
		}
		if ($site != SEO_FULLWEBPATH) {
			if ($before) {
				echo '<span class="beforetext">' . html_encode($before) . '</span>';
			}
			printLinkHTML($site, $name, $title, $class, $id);
			if ($after) {
				echo '<span class="aftertext">' . html_encode($after) . '</span>';
			}
		}
	}
}

/**
 * Returns the formatted date field of the album
 *
 * @param string $format optional format string for the date - A datetime format, if using localized dates an ICU dateformat
 * @return string
 */
function getAlbumDate($format = null) {
	global $_zp_current_album;
	$d = $_zp_current_album->getDateTime();
	if (empty($d) || ($d == '0000-00-00 00:00:00')) {
		return false;
	}
	if (is_null($format)) {
		return $d;
	}
	return zpFormattedDate($format, strtotime($d));
}

/**
 * Prints the date of the current album
 *
 * @param string $before Insert here the text to be printed before the date.
 * @param string $format A datetime format, if using localized dates an ICU dateformat
 */
function printAlbumDate($before = '', $format = NULL) {
	global $_zp_current_album;
	if (is_null($format)) {
		$format = DATETIME_DISPLAYFORMAT;
	}
	$date = getAlbumDate($format);
	if ($date) {
		if ($before) {
			$date = '<span class="beforetext">' . $before . '</span>' . $date;
		}
	}
	echo html_encodeTagged($date);
}

/**
 * Returns the Location of the album.
 *
 * @return string
 */
function getAlbumLocation() {
	global $_zp_current_album;
	return $_zp_current_album->getLocation();
}

/**
 * Prints the location of the album
 *
 * @author Ozh
 */
function printAlbumLocation() {
	echo html_encodeTagged(getAlbumLocation());
}

/**
 * Returns the raw description of the current album.
 *
 * @return string
 */
function getAlbumDesc() {
	if (!in_context(ZP_ALBUM)) {
		return false;
	}
	global $_zp_current_album;
	if (!$_zp_current_album->checkAccess()) {
		return '<p>' . gettext('<em>This album is protected.</em>') . '</p>';
	}
	return $_zp_current_album->getDesc();
}

/**
 * Returns a text-only description of the current album.
 *
 * @return string
 */
function getBareAlbumDesc() {
	return getBare(getAlbumDesc());
}

/**
 * Prints description of the current album
 *
 * @author Ozh
 */
function printAlbumDesc() {
	global $_zp_current_album;
	echo html_encodeTagged(getAlbumDesc());
}

function printBareAlbumDesc() {
	echo html_encode(getBareAlbumDesc());
}

/**
 * Returns the custom_data field of the current album
 *
 * @return string
 */
function getAlbumCustomData() {
	global $_zp_current_album;
	return $_zp_current_album->getCustomData();
}

/**
 * Prints the custom_data field of the current album.
 * Converts and displays line break in the admin field as <br />.
 *
 * @author Ozh
 */
function printAlbumCustomData() {
	echo html_encodeTagged(getAlbumCustomData());
}

/**
 * A composit for getting album data
 *
 * @param string $field which field you want
 * @return string
 */
function getAlbumData($field) {
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_album_image;
	return i18n::getLanguageString($_zp_album_image->get($field));
}

/**
 * Prints arbitrary data from the album object
 *
 * @param string $field the field name of the data desired
 * @param string $label text to label the field
 * @author Ozh
 */
function printAlbumData($field, $label = '') {
	global $_zp_current_album;
	echo html_encodeTagged($_zp_current_album->get($field));
}

/**
 * Returns the album page number of the current image
 *
 * @param object $album optional album object
 * @return integer
 */
function getAlbumPage($album = NULL) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_first_page_images;
	if (is_null($album)) {
		$album = $_zp_current_album;
	}
	if (!$_zp_first_page_images) {
		$_zp_first_page_images = getFirstPageImages();
	}
	$use_realalbum = false;
	if (!$album->isDynamic()) {
		$use_realalbum = true;
	} 
	$page = 0;
	if (in_context(ZP_IMAGE) && !in_context(ZP_SEARCH)) {
		$imageindex = $_zp_current_image->getIndex($use_realalbum);
		$numalbums = $album->getNumAlbums();
		$imagepage = floor(($imageindex - $_zp_first_page_images) / max(1, getOption('images_per_page'))) + 1;
		$albumpages = ceil($numalbums / max(1, getOption('albums_per_page')));
		if ($albumpages == 0 && $_zp_first_page_images > 0) {
			$imagepage++;
		}
		$page = $albumpages + $imagepage;
	}
	return $page;
}

/**
 * Returns the album link url of the current album.
 *
 * @param object $album optional album object
 * @return string
 */
function getAlbumURL($album = NULL) {
	global $_zp_current_album;
	if (is_null($album))
		$album = $_zp_current_album;
	if (in_context(ZP_IMAGE)) {
		$page = getAlbumPage($album);
		if ($page <= 1)
			$page = 0;
	} else {
		$page = 0;
	}
	return $album->getLink($page);
}

/**
 * Prints the album link url of the current album.
 *
 * @param string $text Insert the link text here.
 * @param string $title Insert the title text here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 */
function printAlbumURL($text, $title, $class = NULL, $id = NULL) {
	printLinkHTML(getAlbumURL(), $text, $title, $class, $id);
}

/**
 * Returns the name of the defined album thumbnail image.
 *
 * @return string
 */
function getAlbumThumb() {
	global $_zp_current_album;
	return $_zp_current_album->getThumb();
}

/**
 * Returns an <img> element of the password protect thumb substitute
 *
 * @deprecated 2.0 Use printPasswordProtectedImage() instead
 * @param string $extra extra stuff to put in the HTML
 * @return string
 */
function getPasswordProtectImage($extra = '') {
	deprecationNotice(gettext('Use printPasswordProtectedImage() instead'));
	printPasswordProtectedImage($extra);
}

/**
 * Gets the URL to the password protected images
 * 
 * @since 1.6.1
 * @global string $_zp_themeroot
 * @return string
 */
function getPasswordProtectedImage() {
	global $_zp_themeroot;
	$image = '';
	$themedir = SERVERPATH . '/themes/' . basename($_zp_themeroot);
	if (file_exists(internalToFilesystem($themedir . '/images/err-passwordprotected.png'))) {
		$image = $_zp_themeroot . '/images/err-passwordprotected.png';
	} else if (file_exists(internalToFilesystem($themedir . '/images/err-passwordprotected.gif'))) {
		$image = $_zp_themeroot . '/images/err-passwordprotected.gif';
	} else {
		$image = WEBPATH . '/' . ZENFOLDER . '/images_errors/err-passwordprotected.png';
	}
	return $image;
}

/**
 * Prints an image element with the password protected image.
 * 
 * @since 1.6.1 Replaces getPasswordProtectImage()
 * 
 * @param string $extra extra attributes, trailing space required. Do not pass the width/height as it is taken from the image itself
 */
function printPasswordProtectedImage($extra = '') {
	$image = getPasswordProtectedImage();
	echo '<img src="' . html_encode($image) . '" alt="protected" loading="lazy"' . $extra . ' />';
}

/**
 * Prints the album thumbnail image.
 *
 * @param string $alt Insert the text for the alternate image name here.
 * @param string $class Insert here the CSS-class name with with you want to style the link.
 * @param string $id Insert here the CSS-id name with with you want to style the link.
 * @param string $title option title attribute
 *  */
function printAlbumThumbImage($alt = '', $class = '', $id = '' , $title = '') {
	global $_zp_current_album;
	$thumbobj = $_zp_current_album->getAlbumThumbImage();
	$sizes = getSizeDefaultThumb($thumbobj);
	if (empty($title)) {
		$title = $alt;
	}
	$attr = array(
			'src' => html_pathurlencode($thumbobj->getThumb('album')),
			'alt' => html_encode($alt),
			'title' => html_encode($title),
			'class' => $class,
			'id' => $id,
			'width' => $sizes[0],
			'height' => $sizes[1],
			'loading' => 'lazy'
	);
	if (!$_zp_current_album->isPublished()) {
		$attr['class'] .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$attr['class'] .= " password_protected";
	}
	$attr['class'] = trim($attr['class']);
	$attr_filtered = filter::applyFilter('standard_album_thumb_attr', $attr, $thumbobj);
	if (!getOption('use_lock_image') || $_zp_current_album->isMyItem(LIST_RIGHTS) || !$_zp_current_album->isProtected()) {
		$attributes = generateAttributesFromArray($attr_filtered);
		$html = '<img' . $attributes . ' />';
		$html = filter::applyFilter('standard_album_thumb_html', $html, $thumbobj);
		echo $html;
	} else {
		$size = ' width="' . $attr['width'] . '"';
		printPasswordProtectedImage($size);
	}
}

/**
 * Returns a link to a custom sized thumbnail of the current album
 *
 * @param int $size the size of the image to have
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param bool $effects image effects (e.g. set 'gray' to force grayscale)
 *
 * @return string
 */
function getCustomAlbumThumb($size = null, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = null, $effects = NULL) {
	global $_zp_current_album;
	$thumb = $_zp_current_album->getAlbumThumbImage();
	return $thumb->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, true, $effects);
}

/**
 * Prints a link to a custom sized thumbnail of the current album
 *
 * See getCustomImageURL() for details.
 *
 * @param string $alt Alt atribute text
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw cropwidth
 * @param int $croph crop height
 * @param int $cropx crop part x axis
 * @param int $cropy crop part y axis
 * @param string $class css class
 * @param string $id css id
 * @param string $title title attribute
 * @param bool $maxspace true for maxspace image, false is default
 *
 * @return string
 */
function printCustomAlbumThumbImage($alt = '', $size = null, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = null, $class = NULL, $id = NULL, $title = null, $maxspace = false) {
	global $_zp_current_album;
	$thumbobj = $_zp_current_album->getAlbumThumbImage();
	$sizes = getSizeCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumbobj, 'thumb');
	if (empty($title)) {
		$title = $alt;
	}
	$attr = array(
			'alt' => html_encode($alt),
			'class' => $class,
			'title' => html_encode($title),
			'id' => $id,
			'loading' => 'lazy'
	);
	if ($maxspace) {
		getMaxSpaceContainer($width, $height, $thumbobj, true);
		$attr['width'] = $width;
		$attr['height'] = $height;
	} else {
		$attr['width'] = $sizes[0];
		$attr['height'] = $sizes[1];
	}
	if (!$_zp_current_album->isPublished()) {
		$attr['class'] .= " not_visible";
	}
	$pwd = $_zp_current_album->getPassword();
	if (!empty($pwd)) {
		$attr['class'] .= " password_protected";
	}
	if (is_string($attr['class'])) {
		$attr['class'] = trim($attr['class']);
	}
	if ($maxspace) {
		$attr['src']= html_pathurlencode(getCustomAlbumThumb(null, $width, $height, null, null, null, null));
	} else {
		$attr['src']= html_pathurlencode(getCustomAlbumThumb($size, $width, $height, $cropw, $croph, $cropx, $cropy));
	}
	$attr_filtered = filter::applyFilter('custom_album_thumb_attr', $attr, $thumbobj);
	if (!getOption('use_lock_image') || $_zp_current_album->isMyItem(LIST_RIGHTS) || !$_zp_current_album->isProtected()) {
		$attributes = generateAttributesFromArray($attr_filtered);
		$html = '<img' . $attributes . ' />';
		$html = filter::applyFilter('custom_album_thumb_html', $html, $thumbobj);
		echo $html;
	} else {
		$size = ' width="' . $attr['width'] . '" height="' . $attr['height'] . '"';
		printPasswordProtectedImage($size);
	}
}


/**
 * Returns a link to a un-cropped custom sized version of the current album thumb within the given height and width dimensions.
 *
 * @param int $width width
 * @param int $height height
 * @return string
 */
function getCustomAlbumThumbMaxSpace($width, $height) {
	global $_zp_current_album;
	$albumthumb = $_zp_current_album->getAlbumThumbImage();
	getMaxSpaceContainer($width, $height, $albumthumb, true);
	return getCustomAlbumThumb(NULL, $width, $height, NULL, NULL, NULL, NULL);
}

/**
 * Prints a un-cropped custom sized album thumb within the given height and width dimensions.
 * Note: a class of 'not_visible' or 'password_protected' will be added as appropriate
 *
 * @param string $alt Alt text for the url
 * @param int $width width
 * @param int $height height
 * @param string $class Optional style class
 * @param string $id Optional style id
 * @param string $title Optional title attribute
 */
function printCustomAlbumThumbMaxSpace($alt = '', $width = null, $height = null, $class = NULL, $id = NULL, $title = null) {
	printCustomAlbumThumbImage($alt, NULL, $width, $height, NULL, NULL, NULL, NULL, $class, $id, $title, true);
}

/**
 * Returns the next album
 *
 * @return object
 */
function getNextAlbum() {
	global $_zp_current_album, $_zp_current_search, $_zp_gallery;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$nextalbum = $_zp_current_search->getNextAlbum($_zp_current_album->name);
	} else if (in_context(ZP_ALBUM)) {
		$nextalbum = $_zp_current_album->getNextAlbum();
	} else {
		return null;
	}
	return $nextalbum;
}

/**
 * Get the URL of the next album in the gallery.
 *
 * @return string
 */
function getNextAlbumURL() {
	$nextalbum = getNextAlbum();
	if ($nextalbum) {
		return $nextalbum->getLink();
	}
	return false;
}

/**
 * Returns the previous album
 *
 * @return object
 */
function getPrevAlbum() {
	global $_zp_current_album, $_zp_current_search;
	if (in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
		$prevalbum = $_zp_current_search->getPrevAlbum($_zp_current_album->name);
	} else if (in_context(ZP_ALBUM)) {
		$prevalbum = $_zp_current_album->getPrevAlbum();
	} else {
		return null;
	}
	return $prevalbum;
}

/**
 * Get the URL of the previous album in the gallery.
 *
 * @return string
 */
function getPrevAlbumURL() {
	$prevalbum = getPrevAlbum();
	if ($prevalbum) {
		return $prevalbum->getLink();
	}
	return false;
}

/**
 * Returns true if this page has image thumbs on it
 *
 * @return bool
 */
function isImagePage() {
	if (getNumImages()) {
		global $_zp_page, $_zp_first_page_images;
		$imagestart = getTotalPages(2); // # of album pages
		if (!$_zp_first_page_images)
			$imagestart++; // then images start on the last album page.
		return $_zp_page >= $imagestart;
	}
	return false;
}

/**
 * Returns true if this page has album thumbs on it
 *
 * @return bool
 */
function isAlbumPage() {
	global $_zp_page;
	$pageCount = Ceil(getNumAlbums() / max(1, getOption('albums_per_page')));
	return ($_zp_page <= $pageCount);
}

/**
 * Returns the number of images in the album.
 *
 * @return int
 */
function getNumImages() {
	global $_zp_current_album, $_zp_current_search;
	if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getNumImages();
	} else {
		return $_zp_current_album->getNumImages();
	}
}

/**
 * 
 * @since 1.6
 * 
 * @global obj $_zp_current_album
 * @global type $_zp_current_search
 * @param type $one_image_page
 * @return type
 */
function getFirstPageImages($one_image_page = false) {
	global $_zp_current_album, $_zp_current_search;
	if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
		return $_zp_current_search->getFirstPageImages($one_image_page);
	} else {
		return $_zp_current_album->getFirstPageImages($one_image_page);
	}
}

/**
 * Returns the next image on a page.
 * sets $_zp_current_image to the next image in the album.

 * Returns true if there is an image to be shown
 *
 * @param bool $all set to true disable pagination
 * @param int $firstPageCount the number of images which can go on the page that transitions between albums and images
 * 							Normally this parameter should be NULL so as to use the default computations.
 * @param bool $mine overridePassword the password check
 * @return bool
 *
 * @return bool
 */
function next_image($all = false, $firstPageCount = NULL, $mine = NULL) {
	global $_zp_images, $_zp_current_image, $_zp_current_album, $_zp_page, $_zp_current_image_restore, $_zp_current_search, $_zp_first_page_images;
	if (is_null($firstPageCount)) {
		$firstPageCount = getFirstPageImages();
	}
	$imagePageOffset = getTotalPages(2); /* gives us the count of pages for album thumbs */
	if ($all) {
		$imagePage = 1;
		$firstPageCount = 0;
	} else {
		$_zp_first_page_images = $firstPageCount; /* save this so pagination can see it */
		$imagePage = $_zp_page - $imagePageOffset;
	}
	if ($firstPageCount > 0 && $imagePageOffset > 0) {
		$imagePage = $imagePage + 1; /* can share with last album page */
	}
	if ($imagePage <= 0) {
		return false; /* we are on an album page */
	}
	if (is_null($_zp_images)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_images = $_zp_current_search->getImages($all ? 0 : ($imagePage), $firstPageCount, NULL, NULL, true, $mine);
		} else {
			$_zp_images = $_zp_current_album->getImages($all ? 0 : ($imagePage), $firstPageCount, NULL, NULL, true, $mine);
		}
		if (empty($_zp_images)) {
			return NULL;
		}
		$_zp_current_image_restore = $_zp_current_image;
		$img = array_shift($_zp_images);
		$_zp_current_image = Image::newImage($_zp_current_album, $img, true, true);
		save_context();
		add_context(ZP_IMAGE);
		return true;
	} else if (empty($_zp_images)) {
		$_zp_images = NULL;
		$_zp_current_image = $_zp_current_image_restore;
		restore_context();
		return false;
	} else {
		$img = array_shift($_zp_images);
		$_zp_current_image = Image::newImage($_zp_current_album, $img, true, true);
		return true;
	}
}