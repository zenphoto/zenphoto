<?php
/**
 * The version number within @deprecated indicates the version these will be removed completely
 * 
 * @package zpcore\plugins\dprecatedfunctions
 * @deprecated 2.0
 */

/**
 * controls the thumbnail layout of themes.
 *
 * Uses the theme options:
 * 	albums_per_row
 * 	albums_per_page
 * 	images_per_row
 * 	images_per_page
 * 
 * @deprecated 2.0 - This function is obsolete and has no functionality anymore
 * @since 1.6
 *
 * Computes a normalized images/albums per page and computes the number of
 * images that will fit on the "transitional" page between album thumbs and
 * image thumbs. This function is "internal" and is called from the root
 * index.php script before the theme script is loaded.
 */
function setThemeColumns() {
	deprecationNotice(gettext("This function is obsolete and has no functionality anymore."));
}
 
/**
 * Returns the mimetype for a suffix
 * 
 * @deprecated 2.0 - Use mimeTypes::getType(m() instead
 * @since 1.6
 * 
 * @param string $suffix Suffix without . to get the mimetype for
 * @return string
 */
function getMimeString($suffix) {
	deprecationNotice(gettext("Use mimeTypes::getType() instead"));
	require_once SERVERPATH . '/' . ZENFOLDER . '/classes/class-mimetypes.php';
	return mimeTypes::getType($suffix);
}

/**
 * Wrapper instantiation function for albums. Do not instantiate directly
 * 
 * @deprecated 2.0 - Use AlbumBase::newAlbum() instead
 * @since 1.6
 * 
 * @param string $folder8 the name of the folder (inernal character set)
 * @param bool $cache true if the album should be fetched from the cache
 * @param bool $quiet true to supress error messages
 * @return Album
 */
function newAlbum($folder8, $cache = true, $quiet = false) {
	deprecationNotice(gettext("Use AlbumBase::newAlbum() instead"));
	return AlbumBase::newAlbum($folder8, $cache, $quiet);
}

/**
 * Returns true if the object is a zenphoto 'album'
  * 
 * @deprecated 2.0 - Use AlbumBase::siAlbumClass() instead
 * @since 1.6
 *
 * @param object $album
 * @return bool
 */
function isAlbumClass($album = NULL) {
	deprecationNotice(gettext("Use AlbumBase::isAlbumClass() instead"));
	return AlbumBase::isAlbumClass($album);
}



/**
 * Returns the oldest ancestor of an alubm;
 *
 * @deprecated 2.0 – Use $album->getUrParent() instead
 * @since 1.6
 *
 * @param string $album an album object
 * @return object
 */
function getUrAlbum($album) {
	deprecationNotice(gettext('Use $album->getUrParent() instead'));
	if (!is_object($album))
		return NULL;
	return $album->getUrParent();
}

/**
 * Returns a new "image" object based on the file extension
  * 
 * @deprecated 2.0 - Use Image::newImage() instead
 * @since 1.6
 * 
 * @param object $album the owner album
 * @param string $filename the filename
 * @param bool $quiet set true to supress error messages (used by loadimage)
 * @return object
 */
function newImage($album, $filename, $quiet = false) {
	deprecationNotice(gettext("Use Image::newImage() instead"));
	return Image::newImage($album, $filename, $quiet);
}

/**
 * Returns true if the object is a zenphoto 'image'
 * 
 * @deprecated 2.0 - Use Image::isImageClass() instead
 * @since 1.6
 * 
 * @param object $image
 * @return bool
 */
function isImageClass($image = NULL) {
	deprecationNotice(gettext("Use Image::isImageClass() instead"));
	return Image::isImageClass($image);
}

/**
 * encloses search word in quotes if needed
 * 
 * @deprecated 2.0 - Use SearchEngine::getSearchQuote() instead
 * @since 1.6
 *  
 * @param string $word
 * @return string
 */
function search_quote($word) {
	deprecationNotice(gettext("Use SearchEngine::getSearchQuote() instead"));
	return SearchEngine::getSearchQuote($word);
}

/**

 * Returns video argument of the current Image.
 *
 * @deprecated 2.0 – Use the Image class method isVideo() instead
 * @since 1.6
 *
 * @param object $image optional image object
 * @return bool
 */
function isImageVideo($image = NULL) {
	deprecationNotice(gettext("Use Image object method isVideo() instead"));
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	return $image->isVideo();
}

/**
 * Returns true if the image is a standard photo type
 *
 * @deprecated 2.0 – Use the Image class method isPhoto() instead
 * @since 1.6
 *
 * @param object $image optional image object
 * @return bool
 */
function isImagePhoto($image = NULL) {
	deprecationNotice(gettext("Use Image object method isPhoto() instead"));
	if (is_null($image)) {
		if (!in_context(ZP_IMAGE))
			return false;
		global $_zp_current_image;
		$image = $_zp_current_image;
	}
	return $image->isPhoto();
}

/**
 * Replaces/renames an option. If the old option exits, it creates the new option with the old option's value as the default 
 * unless the new option has already been set otherwise. Independently it always deletes the old option.
 * 
* @deprecated 2.0 – Use renameOptionl() instead
 * 
 * @param string $oldkey Old option name
 * @param string $newkey New option name
 * 
 * @since 1.5.1
 */
function replaceOption($oldkey, $newkey) {
	deprecationNotice(gettext("Use renameOption() instead"));
	renameOption($oldkey, $newkey);
}

/**
 * Determines if the input is an e-mail address. Adapted from WordPress.
 * Name changed to avoid conflicts in WP integrations.
 * 
  * @deprecated 2.0 – Use isValidEmail() instead
 *
 * @param string $input_email email address?
 * @return bool
 */
function is_valid_email_zp($input_email) {
	deprecationNotice(gettext("Use the isValidEmail() instead"));
	return isValidEmail($input_email);
}

/**
 * Populatest $list with an one dimensional list with album name and title of all albums or the subalbums of a specific album
 * 
 * @deprecated 2.0 – Use the gallery class method getAllAlbums() or getAllAlbumsFromDB() instead
 * 
 * @global obj $_zp_gallery
 * @param array $list The array to fill with the album list
 * @param obj $curAlbum Optional object of the album to start with
 * @param int $rights Rights constant to filter album access by.
 */
function genAlbumList(&$list, $curAlbum = NULL, $rights = UPLOAD_RIGHTS) {
	global $_zp_gallery;
	deprecationNotice(gettext("Use the gallery class method getAllAlbums() or getAllAlbumsFromDB() instead"));
	$list = $_zp_gallery->getAllAlbums($curAlbum, $rights, true);
}

/**
 * Returns a list of all albums decendent from an album
 * 
 * @deprecated 2.0 – Use the gallery class method getAllAlbums() or getAllAlbumsFromDB() instead
 *
 * @param object $album optional album. If absent the current album is used
 * @return array
 */
function getAllAlbums($album = NULL) {
	deprecationNotice(gettext("Use the gallery class method getAllAlbums() or getAllAlbumsFromDB() instead"));
	global $_zp_current_album, $_zp_gallery;
	if (is_null($album))
		$album = $_zp_current_album;
	if (!is_object($album))
		return;
	$list = getAllAlbums($albumobj = NULL, $rights = LIST_RIGHTS, false);
	return $list;
}

if (function_exists('printImageStatistic')) {

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printPopularAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = 'hitcounter', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecationNotice(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "popular", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printLatestAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
		deprecationNotice(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "latest", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printMostRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecationNotice(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "mostrated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printTopRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecationNotice(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "toprated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printLatestUpdatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
		deprecationNotice(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "latestupdated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printPopularImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "popular", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printTopRatedImages($number = 5, $albumfolder = "", $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "toprated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}
	
	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printMostRatedImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "mostrated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}

		/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}
		/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImagesByDate($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest-date", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImagesByMtime($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecationNotice(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest-mtime", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}

}

/**
 *
 * performs a query and then filters out "illegal" images returning the first "good" image
 * used by the random image functions.
 * 
 * @deprecated 2.0 - There is no direct replacement, use the general object model instead
 *
 * @param object $result query result
 * @param string $source album object if this is search within the album
 */
function filterImageQuery($result, $source) {
	deprecationNotice(gettext("There is no replacement"));
	global $_zp_db;
	if ($result) {
		while ($row = $_zp_db->fetchAssoc($result)) {
			$image = Image::newImage(null, $row);
			$album = $image->album;
			if ($album->name == $source || $album->checkAccess()) {
				if ($image->isPhoto()) {
					if ($image->checkAccess()) {
						return $image;
					}
				}
			}
		}
		$_zp_db->freeResult($result);
	}
	return NULL;
}

/**
 * Returns a randomly selected image from the gallery. (May be NULL if none exists)
 * @param bool $daily set to true and the picture changes only once a day.
 * 
 * @deprecated 2.0 Use the image_album_statistics plugin function getImageStatistic()with appropiate parameters – For daily use the same plugin's function getPictureOfTheDay()
 *
 * @return object
 */
function getRandomImages($daily = false) {
	global $_zp_gallery, $_zp_db;;
	$deprecatednote = gettext("Use the image_album_statistics plugin function getImageStatistic() with appropiate parameters");
	if($daily) {
		$deprecatednote .= ' ' . gettext("For picture of the day functionality use the image_album_statistics plugin function getPictureOfTheDay()");
	}
	deprecationNotice($deprecatednote);
	if ($daily) {
		$potd = getSerializedArray(getOption('picture_of_the_day'));
		if (date('Y-m-d', $potd['day']) == date('Y-m-d')) {
			$album = AlbumBase::newAlbum($potd['folder'], true, true);
			if ($album->exists) {
				$image = Image::newImage($album, $potd['filename'], true);
				if ($image->exists) {
					return $image;
				}
			}
		}
	}
	if (zp_loggedin()) {
		$imageWhere = '';
	} else {
		$imageWhere = " AND " . $_zp_db->prefix('images') . ".show=1";
	}
	$result = $_zp_db->query('SELECT `folder`, `filename` ' .
					' FROM ' . $_zp_db->prefix('images') .
					' INNER JOIN ' . $_zp_db->prefix('albums') . ' ON ' . $_zp_db->prefix('images') . '.albumid = ' . $_zp_db->prefix('albums') . '.id ' .
					' INNER JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM ' . $_zp_db->prefix('images') . ')) AS id) AS r2 ON ' . $_zp_db->prefix('images') . '.id >= r2.id ' . 
					' WHERE ' . $_zp_db->prefix('albums') . '.folder!="" ' . $imageWhere . ' ORDER BY ' . $_zp_db->prefix('images') . '.id LIMIT 1');

	$images = filterImageQuery($result, NULL);
	if ($images) {
		if ($daily) {
			$potd = array('day' => time(), 'folder' => $images->getAlbumName(), 'filename' => $images->getName());
			setThemeOption('picture_of_the_day', serialize($potd), NULL, $_zp_gallery->getCurrentTheme());
		}
		return $images;
	}
	return NULL;
}

/**
 * Returns a randomly selected image from the album or its subalbums. (May be NULL if none exists)
 *
 * @deprecated 2.0 Use the image_album_statistic plugin function getImageStatistic() with appropriate parameters – For daily use the same plugin's function getPictureOfTheDay()
 * 
 * @param mixed $rootAlbum optional album object/folder from which to get the image.
 * @param bool $daily set to true to change picture only once a day.
 *
 * @return object
 */
function getRandomImagesAlbum($rootAlbum = NULL, $daily = false) {
	global $_zp_current_album, $_zp_gallery, $_zp_db;
	$deprecatednote = gettext("Use the image_album_statistics plugin function getImageStatisic() with appropiate parameters");
	if($daily) {
		$deprecatednote .= ' ' . gettext("For picture of the day functionality use the image_album_statiscic plugin function getPictureOfTheDay()-");
	}
	deprecationNotice($deprecatednote);
	if (empty($rootAlbum) && !in_context(ZP_ALBUM)) {
		return null;
	}
	if (empty($rootAlbum)) {
		$album = $_zp_current_album;
	} else {
		if (is_object($rootAlbum)) {
			$album = $rootAlbum;
		} else {
			$album = AlbumBase::newAlbum($rootAlbum);
		}
	}
	if ($daily && ($potd = getOption('picture_of_the_day:' . $album->name))) {
		$potd = getSerializedArray($potd);
		if (date('Y-m-d', $potd['day']) == date('Y-m-d')) {
			$rndalbum = AlbumBase::newAlbum($potd['folder']);
			$image = Image::newImage($rndalbum, $potd['filename']);
			if ($image->exists)
				return $image;
		}
	}
	$image = NULL;
	if ($album->isDynamic()) {
		$images = $album->getImages(0);
		shuffle($images);
		while (count($images) > 0) {
			$result = array_pop($images);
			if (Gallery::validImage($result['filename'])) {
				$image = Image::newImage(AlbumBase::newAlbum($result['folder']), $result['filename']);
			}
		}
	} else {
		$albumfolder = $album->getName();
		if ($album->isMyItem(LIST_RIGHTS)) {
			$imageWhere = '';
			$albumInWhere = '';
		} else {
			$imageWhere = " AND " . $_zp_db->prefix('images') . ".show=1";
			$albumInWhere = $_zp_db->prefix('albums') . ".show=1";
		}
		$query = "SELECT id FROM " . $_zp_db->prefix('albums') . " WHERE ";
		if ($albumInWhere) {
			$query .= $albumInWhere . ' AND ';
		}
		$query .= "folder LIKE " . $_zp_db->quote($_zp_db->likeEscape($albumfolder) . '%');
		$result = $_zp_db->query($query);
		if ($result) {
			$albumids = array();
			while ($row = $_zp_db->fetchAssoc($result)) {
				$albumids[] = $row['id'];
			}
			if (empty($albumids)) {
				$albumInWhere = ' AND ' . $albumInWhere;
			} else {
				$albumInWhere = ' AND ' . $_zp_db->prefix('albums') . ".id IN (" . implode(',', $albumids) . ')';
			}
			$_zp_db->freeResult($result);
			$sql = 'SELECT `folder`, `filename` ' .
							' FROM ' . $_zp_db->prefix('images') . ', ' . $_zp_db->prefix('albums') .
							' WHERE ' . $_zp_db->prefix('albums') . '.folder!="" AND ' . $_zp_db->prefix('images') . '.albumid = ' .
							$_zp_db->prefix('albums') . '.id ' . $albumInWhere . $imageWhere . ' ORDER BY RAND()';
			$result = $_zp_db->query($sql);
			$image = filterImageQuery($result, $album->name);
		}
	}
	if ($image) {
		if ($daily) {
			$potd = array('day' => time(), 'folder' => $image->getAlbumName(), 'filename' => $image->getName());
			setThemeOption('picture_of_the_day:' . $album->name, serialize($potd), NULL, $_zp_gallery->getCurrentTheme());
		}
	}
	return $image;
}

/**
 * Puts up random image thumbs from the gallery
 * 
 * @deprecated 2.0 Use the image_album_statiscic plugin function printImageStatisic()with appropiate parameters. You might need to adjust your theme's CSS.
 *
 * @param int $number how many images
 * @param string $class optional class
 * @param string $option what you want selected: all for all images, album for selected ones from an album
 * @param mixed $rootAlbum optional album object/folder from which to get the image.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size.
 * @param integer $height the height/cropheight of the thumb if crop=true else not used
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 */
function printRandomImages($number = 5, $class = null, $option = 'all', $rootAlbum = '', $width = NULL, $height = NULL, $crop = NULL, $fullimagelink = false) {
	deprecationNotice(gettext("Use the image_album_statistics plugin function getImageStatistic() with appropiate parameters. You might need to adjust your theme's CSS."));
	if (is_null($crop) && is_null($width) && is_null($height)) {
		$crop = 2;
	} else {
		if (is_null($width))
			$width = 85;
		if (is_null($height))
			$height = 85;
		if (is_null($crop)) {
			$crop = 1;
		} else {
			$crop = (int) $crop && true;
		}
	}
	if (!empty($class))
		$class = ' class="' . $class . '"';
	echo "<ul" . $class . ">";
	for ($i = 1; $i <= $number; $i++) {
		switch ($option) {
			case "all":
				$randomImage = getRandomImages();
				break;
			case "album":
				$randomImage = getRandomImagesAlbum($rootAlbum);
				break;
		}
		if (is_object($randomImage) && $randomImage->exists) {
			echo "<li>\n";
			if ($fullimagelink) {
				$randomImageURL = $randomImage->getFullimageURL();
			} else {
				$randomImageURL = $randomImage->getLink();
			}
			echo '<a href="' . html_encode($randomImageURL) . '" title="' . sprintf(gettext('View image: %s'), html_encode($randomImage->getTitle())) . '">';
			switch ($crop) {
				case 0:
					$sizes = getSizeCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, $randomImage, 'thumb');
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" />' . "\n";
					break;
				case 1:
					$sizes = getSizeCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, $randomImage);
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" />' . "\n";
					break;
				case 2:
					$sizes = getSizeDefaultThumb($randomImage);
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getThumb())) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" />' . "\n";
					break;
			}
			echo zp_apply_filter('custom_image_html', $html, false, $randomImage);
			echo "</a>";
			echo "</li>\n";
		} else {
			break;
		}
	}
	echo "</ul>";
}

/**
 *
 * fixes unbalanced HTML tags. Used by shortenContent when PHP tidy is not present
 *
 * @deprecated 2.0 Use tidyHTML() instead
 * @since 1.5
 *
 * @param string $html
 * @return string
 */
function cleanHTML($html) {
	deprecationNotice(gettext("Use tidyHTML() instead"));
	return tidyHTML($html);
}

/**
 * Returns the count of all the images in the album and any subalbums
 * @deprecated 2.0
 * @since 1.5.2
 * 
 * @param object $album The album whose image count you want
 * @return int
 */
function getTotalImagesIn($album) {
	deprecationNotice(gettext("Use AlbumBase class method getNumAllImages() instead"));
	return $album->getNumAllImages();
}

/**
 * checks if the item has expired
 * @deprecated  2.0 - Use themeObject::checkScheduledPublishing() instead
 * @since 1.5.7
 * @param array $row database row of the object
 */
function checkPublishDates($row) {
	deprecationNotice(gettext("Use themeObject::checkScheduledPublishing() instead"));
	return themeObject::checkScheduledPublishing($row);
}

/**
 * Zenphoto general deprecated functions
 * 
	* @deprecated 2.0  
 * @package zpcore\plugins\deprecatedfunctions
 */
class internal_deprecations {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains() instead
	 * @since 1.5
	 */
	static function LanguageSubdomains() {
		deprecationNotice(gettext('Use getLanguageSubdomains() instead'));
	}
	/**
	 * @deprecated 2.0 Use getLanguageText() instead
	 * @since 1.5
	 */
	static function getLanguageText() {
		deprecationNotice(gettext('Use getLanguageText() instead'));
	}
	/**
	 * @deprecated 2.0 Use setexifvars() instead
	 * @since 1.5
	 */
	static function setexifvars() {
		deprecationNotice(gettext('Use setexifvars() instead'));
	}
	/**
	 * @deprecated 2.0 Use hasPrimaryScripts() instead
	 * @since 1.5
	 */
	static function hasPrimaryScripts() {
		deprecationNotice(gettext('Use hasPrimaryScripts() instead'));
	}
	/**
	 * @deprecated 2.0 Use removeDir() instead
	 * @since 1.5
	 */
	static function removeDir() {
		deprecationNotice(gettext('Use removeDir() instead'));
	}
	/**
	 * @deprecated 2.0 Use tagURLs() instead
	 * @since 1.5
	 */
	static function tagURLs() {
		deprecationNotice(gettext('Use tagURLs() instead'));
	}
	/**
	 * @deprecated 2.0 Use unTagURLs() instead
	 * @since 1.5
	 */
	static function unTagURLs() {
		deprecationNotice(gettext('Use unTagURLs() instead'));
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink() instead
	 * @since 1.5
	 */
	static function updateImageProcessorLink() {
		deprecationNotice(gettext('Use updateImageProcessorLink() instead'));
	}
	/**
	 * @deprecated 2.0 Use pluginDebug() instead
	 * @since 1.5
	 */
	static function pluginDebug() {
		deprecationNotice(gettext('Use pluginDebug() instead'));
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash() instead
	 * @since 1.5
	 */
	static function removeTrailingSlash($string) {
		deprecationNotice(gettext('Use removeTrailingSlash() instead'));
	}

	/**
	 * @deprecated 2.0 Use htmlTidy() instead
	 * @since 1.5
	 */
	static function tidyHTML() {
		deprecationNotice(gettext('Use tidyHTML() instead'));
	}

	/**
	 * @deprecated 2.0 Use instantiate() method
	 * @since 1.4.6
	 */
	static function PersistentObject() {
		deprecationNotice(gettext('Use the instantiate method instead'));
	}

}

/**
 * @deprecated 2.0
 * @since 1.5
 */
class zpFunctions {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains()
	 * @since 1.5
	 * @see getLanguageSubdomains()
	 */
	static function LanguageSubdomains() {
		internal_deprecations::LanguageSubdomains();
		return getLanguageSubdomains();
	}

	/**
	 * @deprecated 2.0 Use getLanguageText()
	 * @since 1.5
	 * @see getLanguageText()
	 */
	static function getLanguageText($loc = NULL, $separator = NULL) {
		internal_deprecations::getLanguageText();
		return getLanguageText($loc, $separator);
	}

	/**
	 * @deprecated 2.0 Use setexifvars()
	 * @since 1.5
	 * @see setexifvars()
	 */
	static function setexifvars() {
		internal_deprecations::setexifvars();
		setexifvars();
	}

	/**
	 * @deprecated 2.0 Use hasPrimaryScripts()
	 * @since 1.5
	 * @see hasPrimaryScripts()
	 */
	static function hasPrimaryScripts() {
		internal_deprecations::hasPrimaryScripts();
		return hasPrimaryScripts();
	}

	/**
	 * @deprecated 2.0 Use removeDir()
	 * @since 1.5
	 * @see removeDir()
	 */
	static function removeDir($path, $within = false) {
		internal_deprecations::removeDir();
		return removeDir($path, $within);
	}

	/**
	 * @deprecated 2.0 Use tagURLs()
	 * @since 1.5
	 * @see tagURLs()
	 */
	static function tagURLs($text) {
		internal_deprecations::tagURLs();
		return tagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use untagURLs()
	 * @since 1.5
	 * @see untagURLs()
	 */
	static function unTagURLs($text) {
		internal_deprecations::unTagURLs();
		return unTagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink()
	 * @since 1.5
	 * @see updateImageProcessorLink()
	 */
	static function updateImageProcessorLink($text) {
		internal_deprecations::updateImageProcessorLink();
		return updateImageProcessorLink($text);
	}

	/**
	 * @deprecated 2.0 Use pluginDebug()
	 * @since 1.5
	 * @see pluginDebug()
	 */
	static function pluginDebug($extension, $priority, $start) {
		internal_deprecations::pluginDebug();
		pluginDebug($extension, $priority, $start);
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash()
	 * @since 1.5
	 * @see removeTrailingSlash()
	 */
	static function removeTrailingSlash($string) {
		internal_deprecations::removeTrailingSlash();
		return removeTrailingSlash($string);
	}

	/**
	 * @deprecated 2.0 Use tidyHTML()
	 * @since 1.5
	 * @see tidyHTML()
	 */
	static function tidyHTML($html) {
		internal_deprecations::tidyHTML();
		return tidyHTML($html);
	}
	
	

}