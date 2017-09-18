<?php

/**
 * Support functions for "statistics" about images and albums.
 *
 * Supports such statistics as "most popular", "latest", "top rated", etc.
 *
 * <b>CAUTION:</b> The way to get a specific album has changed. You now have to pass the foldername of an album instead the album title.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage theme
 */
$plugin_description = gettext("Functions that provide various statistics about images and albums in the gallery.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";

require_once(dirname(dirname(__FILE__)) . '/template-functions.php');

/**
 * Returns an array of album objects accordingly to $option
 *
 * @param int $number the number of albums to get
 * @param string $option
 * 		"popular" for the most popular albums,
 * 		"latest" for the latest uploaded by id (Discovery)
 * 		"latest-date" for the latest by date
 * 		"latest-mtime" for the latest by mtime
 *   	"latest-publishdate" for the latest by publishdate
 *    "mostrated" for the most voted,
 * 		"toprated" for the best voted
 * 		"latestupdated" for the latest updated
 * 		"random" for random order (yes, strictly no statistical order...)
 * @param string $albumfolder The name of an album to get only the statistc for its subalbums
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an album must have to be included in the list. (Default 0)
 * @param mixed $sortdirection "asc" for ascending order otherwise order is descending
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 * @return array
 */
function getAlbumStatistic($number = 5, $option, $albumfolder = '', $threshold = 0, $sortdirection = 'desc', $collection = false) {
	global $_zp_gallery;
	$where = '';
	if ($albumfolder) {
		$obj = newAlbum($albumfolder);
		$where = ' WHERE parentid = ' . $obj->getID();
		if ($collection) {
			$ids = getAllSubAlbumIDs($albumfolder);
			if (!empty($ids)) {
				foreach ($ids as $id) {
					$getids[] = $id['id'];
				}
				$getids = implode(', ', $getids);
				$where = ' WHERE id IN (' . $getids . ')';
			}
		}
	} else {
		$obj = $_zp_gallery;
	}
	switch (strtolower($sortdirection)) {
		case false:
		case 'asc':
			$sortdir = ' ASC';
			break;
		default:
			$sortdir = ' DESC';
	}

	$opt = '';
	switch ($option) {
		case "popular":
			$sortorder = "hitcounter";
			$opt = 'hitcounter >= ' . $threshold;
			break;
		default:
		case "latest":
			$sortorder = "id";
			break;
		case "latest-mtime":
			$sortorder = "mtime";
			break;
		case "latest-date":
			$sortorder = "date";
			break;
		case "latest-publishdate":
			$sortorder = "IFNULL(publishdate,date)";
			break;
		case "mostrated":
			$sortorder = "total_votes";
			break;
		case "toprated":
			$sortorder = "(total_value/total_votes) DESC, total_value";
			$opt = 'total_votes >= ' . $threshold;
			break;
		case "latestupdated":
			$sortorder = 'updateddate';
			break;
		case "random":
			$sortorder = "RAND()";
			break;
	}
	if ($opt) {
		if ($where) {
			$where .= ' AND ' . $opt;
		} else {
			$where = ' WHERE ' . $opt;
		}
	}
	$albums = array();
	if ($albumfolder && $obj->isDynamic()) {
		$albums = $obj->getAlbums(0, $sortorder, $sortdir);
		foreach ($albums as $album) {
			$album = newAlbum($album);
			if ($album->checkAccess()) {
				$albums[] = $album;
				if (count($albums) >= $number) { // got enough
					break;
				}
			}
		}
	} else {
		$result = query('SELECT id, title, folder, thumb FROM ' . prefix('albums') . $where . ' ORDER BY ' . $sortorder . $sortdir);
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				if (empty($albumfolder) || strpos($row['folder'], $albumfolder) === 0) {
					$obj = newAlbum($row['folder'], true);
					if ($obj->exists && $obj->checkAccess()) {
						$albums[] = newAlbum($row['folder']);
						if (count($albums) >= $number) { // got enough
							break;
						}
					}
				}
			}
			db_free_result($result);
		}
	}
	return $albums;
}

/**
 * Prints album statistic according to $option as an unordered HTML list
 * A css id is attached by default named '$option_album'
 *
 * @param string $number the number of albums to get
 * @param string $option
 * 		"popular" for the most popular albums,
 * 		"latest" for the latest uploaded by id (Discovery)
 * 		"latest-date" for the latest by date
 * 		"latest-mtime" for the latest by mtime
 *   	"latest-publishdate" for the latest by publishdate
 *    "mostrated" for the most voted,
 * 		"toprated" for the best voted
 * 		"latestupdated" for the latest updated
 * 		"random" for random order (yes, strictly no statistical order...)
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an album must have to be included in the list. (Default 0)
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printAlbumStatistic($number, $option, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
	$albums = getAlbumStatistic($number, $option, $albumfolder, $threshold, $collection);
	echo "\n<div id=\"" . $option . "_album\">\n";
	echo "<ul>";
	foreach ($albums as $album) {
		printAlbumStatisticItem($album, $option, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $firstimglink);
	}
	echo "</ul></div>\n";
}

/**
 * A helper function that only prints a item of the loop within printAlbumStatistic()
 * Not for standalone use.
 *
 * @param array $tempalbum object
 * @param string $option
 * 		"popular" for the most popular albums,
 * 		"latest" for the latest uploaded by id (Discovery)
 * 		"latest-date" for the latest by date
 * 		"latest-mtime" for the latest by mtime
 *   	"latest-publishdate" for the latest by publishdate
 *    "mostrated" for the most voted,
 * 		"toprated" for the best voted
 * 		"latestupdated" for the latest updated
 * 		"random" for random order (yes, strictly no statistical order...)
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printAlbumStatisticItem($album, $option, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $firstimglink = false) {
	global $_zp_gallery;
	$twidth = $width;
	$theight = $height;
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

	if ($firstimglink && $tempimage = $tempalbum->getImage(0)) {
		$albumpath = $tempimage->getLink();
	} else {
		$albumpath = $tempalbum->getLink();
	}
	echo "<li><a href=\"" . $albumpath . "\" title=\"" . html_encode($tempalbum->getTitle()) . "\">\n";
	$albumthumb = $tempalbum->getAlbumThumbImage();
	switch ($crop) {
		case 0:
			$sizes = getSizeCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, $albumthumb);
			echo '<img src="' . html_encode(pathurlencode($albumthumb->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($albumthumb->getTitle()) . '" /></a>' . "\n";
			break;
		case 1;
			if (isImagePhoto($albumthumb)) {
				$sizes = getSizeCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, $albumthumb);
			} else {
				$sizes = array($width, $height);
			}
			echo '<img src="' . html_encode(pathurlencode($albumthumb->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($albumthumb->getTitle()) . '" /></a>' . "\n";
			break;
		case 2:
			$sizes = getSizeDefaultThumb($albumthumb);
			echo '<img src="' . html_encode(pathurlencode($albumthumb->getThumb())) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($albumthumb->getTitle()) . '" /></a>' . "\n";
			break;
	}
	if ($showtitle) {
		echo "<h3><a href=\"" . $albumpath . "\" title=\"" . html_encode($tempalbum->getTitle()) . "\">\n";
		echo $tempalbum->getTitle() . "</a></h3>\n";
	}
	if ($showdate) {
		if ($option === "latestupdated") {
			$filechangedate = strtotime($tempalbum->getUpdatedDate());
			echo "<p>" . sprintf(gettext("Last update: %s"), zpFormattedDate(DATE_FORMAT, $filechangedate)) . "</p>";
			$latestimage = query_single_row("SELECT mtime FROM " . prefix('images') . " WHERE albumid = " . $tempalbum->getID() . " AND `show` = 1 ORDER BY id DESC");
			if ($latestimage) {
				$count = db_count('images', "WHERE albumid = " . $tempalbum->getID() . " AND mtime = " . $latestimage['mtime']);
				if ($count <= 1) {
					$image = gettext("image");
				} else {
					$image = gettext("images");
				}
				echo "<span>" . sprintf(gettext('%1$u new %2$s'), $count, $image) . "</span>";
			}
		} else {
			echo "<p>" . zpFormattedDate(DATE_FORMAT, strtotime($tempalbum->getDateTime())) . "</p>";
		}
	}
	if ($showstatistic === "rating" OR $showstatistic === "rating+hitcounter") {
		$votes = $tempalbum->get("total_votes");
		$value = $tempalbum->get("total_value");
		if ($votes != 0) {
			$rating = round($value / $votes, 1);
		}
		echo "<p>" . sprintf(gettext('Rating: %1$u (Votes: %2$u)'), $rating, $tempalbum->get("total_votes")) . "</p>";
	}
	if ($showstatistic === "hitcounter" OR $showstatistic === "rating+hitcounter") {
		$hitcounter = $tempalbum->getHitcounter();
		if (empty($hitcounter)) {
			$hitcounter = "0";
		}
		echo "<p>" . sprintf(gettext("Views: %u"), $hitcounter) . "</p>";
	}
	if ($showdesc) {
		echo html_encodeTagged(shortenContent($tempalbum->getDesc(), $desclength, ' (...)'));
	}
	echo "</li>";
}

/**
 * Prints the most popular albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an album must have to be included in the list. (Default 0)
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printPopularAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = 'hitcounter', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
	printAlbumStatistic($number, "popular", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
}

/**
 * Prints the latest albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printLatestAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
	printAlbumStatistic($number, "latest", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
}

/**
 * Prints the most rated albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an album must have to be included in the list. (Default 0)
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printMostRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
	printAlbumStatistic($number, "mostrated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
}

/**
 * Prints the top voted albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an album must have to be included in the list. (Default 0)
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printTopRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
	printAlbumStatistic($number, "toprated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
}

/**
 * Prints the latest updated albums
 *
 * @param string $number the number of albums to get
 * @param bool $showtitle if the album title should be shown
 * @param bool $showdate if the album date should be shown
 * @param bool $showdesc if the album description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $firstimglink 'false' (default) if the album thumb link should lead to the album page, 'true' if to the first image of theh album if the album itself has images
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics to include all subalbum levels
 */
function printLatestUpdatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
	printAlbumStatistic($number, "latestupdated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
}

/**
 * Returns an array of image objects according to $option
 *
 * NOTE: for performance reasons this function will NOT discover new images.
 *
 * @param string $number the number of images to get
 * @param string $option "popular" for the most popular images,
 * 		"popular" for the most popular albums,
 * 		"latest" for the latest uploaded by id (Discovery)
 * 		"latest-date" for the latest by date
 * 		"latest-mtime" for the latest by mtime
 *   	"latest-publishdate" for the latest by publishdate
 *    "mostrated" for the most voted,
 * 		"toprated" for the best voted
 * 		"latestupdated" for the latest updated
 * 		"random" for random order (yes, strictly no statistical order...)
 * @param string $albumfolder foldername of an specific album
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an image must have to be included in the list. (Default 0)
 * @param mixed $sortdirection "asc" for ascending order otherwise order is descending
 * @return array
 */
function getImageStatistic($number, $option, $albumfolder = NULL, $collection = false, $threshold = 0, $sortdirection = 'desc') {
	global $_zp_gallery;
	$where = '';
	if ($albumfolder) {
		$obj = newAlbum($albumfolder);
		$where = ' AND albums.id = ' . $obj->getID();
		if ($collection) {
			$ids = getAllSubAlbumIDs($albumfolder);
			if (!empty($ids)) {
				foreach ($ids as $id) {
					$getids[] = $id['id'];
				}
				$getids = implode(', ', $getids);
				$where = ' AND albums.id IN (' . $getids . ')';
			}
		}
	}

	switch (strtolower($sortdirection)) {
		case false:
		case 'asc':
			$sortdir = 'ASC';
			break;
		default:
			$sortdir = 'DESC';
			break;
	}
	switch ($option) {
		case "popular":
			$sortorder = "images.hitcounter";
			$where .= 'AND images.hitcounter >= ' . $threshold;
			break;
		case "latest-date":
			$sortorder = "images.date";
			break;
		case "latest-mtime":
			$sortorder = "images.mtime";
			break;
		default:
		case "latest":
			$sortorder = "images.id";
			break;
		case "latest-publishdate":
			$sortorder = "IFNULL(images.publishdate,images.date)";
			break;
		case "mostrated":
			$sortorder = "images.total_votes";
			break;
		case "toprated":
			$sortorder = "(images.total_value/images.total_votes) DESC, images.total_value";
			$where .= 'AND images.total_votes >= ' . $threshold;
			break;
		case "random":
			$sortorder = "RAND()";
			break;
	}

	$imageArray = array();
	if (!empty($albumfolder) && $obj->isDynamic()) {
		$sorttype = str_replace('images.', '', $sortorder);
		$images = $obj->getImages(0, 0, $sorttype, $sortdir);
		foreach ($images as $image) {
			$image = newImage($obj, $image);
			if ($image->checkAccess()) {
				$imageArray[] = $image;
				if (count($imageArray) >= $number) { // got enough
					break;
				}
			}
		}
	} else {
		$result = query("SELECT images.filename AS filename, albums.folder AS folder FROM " . prefix('images') . " AS images, " . prefix('albums') . " AS albums " . "WHERE (images.albumid = albums.id) " . $where . " ORDER BY " . $sortorder . " " . $sortdir);
		if ($result) {
			while ($row = db_fetch_assoc($result)) {
				$image = newImage($row, true, true);
				if ($image->exists && $image->checkAccess()) {
					$imageArray[] = $image;
					if (count($imageArray) >= $number) { // got enough
						break;
					}
				}
			}
			db_free_result($result);
		}
	}
	return $imageArray;
}

/**
 * Prints image statistic according to $option as an unordered HTML list
 * A css id is attached by default named accordingly'$option'
 *
 * @param string $number the number of albums to get
 * @param string $option "popular" for the most popular images,
 * 		"popular" for the most popular albums,
 * 		"latest" for the latest uploaded by id (Discovery)
 * 		"latest-date" for the latest by date
 * 		"latest-mtime" for the latest by mtime
 *   	"latest-publishdate" for the latest by publishdate
 *    "mostrated" for the most voted,
 * 		"toprated" for the best voted
 * 		"latestupdated" for the latest updated
 * 		"random" for random order (yes, strictly no statistical order...)
 * @param string $albumfolder foldername of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic "hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an image must have to be included in the list. (Default 0)
 */
function printImageStatistic($number, $option, $albumfolder = NULL, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
	$images = getImageStatistic($number, $option, $albumfolder, $collection, $threshold);
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
	echo "\n<div id=\"$option\">\n";
	echo "<ul>";
	foreach ($images as $image) {
		if ($fullimagelink) {
			$imagelink = $image->getFullImageURL();
		} else {
			$imagelink = $image->getLink();
		}
		echo '<li><a href="' . html_encode($imagelink) . '" title="' . html_encode($image->getTitle()) . "\">\n";
		switch ($crop) {
			case 0:
				$sizes = getSizeCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, $image);
				echo '<img src="' . html_encode(pathurlencode($image->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" /></a>\n";
				break;
			case 1:
				$sizes = getSizeCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, $image);
				echo '<img src="' . html_encode(pathurlencode($image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" /></a>\n";
				break;
			case 2:
				$sizes = getSizeDefaultThumb($image);
				echo '<img src="' . html_encode(pathurlencode($image->getThumb())) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" /></a>\n<br />";
				break;
		}
		if ($showtitle) {
			echo '<h3><a href="' . html_encode($image->getLink()) . '" title="' . html_encode($image->getTitle()) . "\">\n";
			echo $image->getTitle() . "</a></h3>\n";
		}
		if ($showdate) {
			echo "<p>" . zpFormattedDate(DATE_FORMAT, strtotime($image->getDateTime())) . "</p>";
		}
		if ($showstatistic === "rating" OR $showstatistic === "rating+hitcounter") {
			$votes = $image->get("total_votes");
			$value = $image->get("total_value");
			if ($votes != 0) {
				$rating = round($value / $votes, 1);
			}
			echo "<p>" . sprintf(gettext('Rating: %1$u (Votes: %2$u)'), $rating, $votes) . "</p>";
		}
		if ($showstatistic === "hitcounter" OR $showstatistic === "rating+hitcounter") {
			$hitcounter = $image->getHitcounter();
			if (empty($hitcounter)) {
				$hitcounter = "0";
			}
			echo "<p>" . sprintf(gettext("Views: %u"), $hitcounter) . "</p>";
		}
		if ($showdesc) {
			echo html_encodeTagged(shortenContent($image->getDesc(), $desclength, ' (...)'));
		}
		echo "</li>";
	}
	echo "</ul></div>\n";
}

/**
 * Prints the most popular images
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an image must have to be included in the list. (Default 0)
 */
function printPopularImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
	printImageStatistic($number, "popular", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
}

/**
 * Prints the n top rated images
 *
 * @param int $number The number if images desired
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an image must have to be included in the list. (Default 0)
 */
function printTopRatedImages($number = 5, $albumfolder = "", $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
	printImageStatistic($number, "toprated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
}

/**
 * Prints the n most rated images
 *
 * @param int $number The number if images desired
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 * @param integer $threshold the minimum number of ratings (for rating options) or hits (for popular option) an image must have to be included in the list. (Default 0)
 */
function printMostRatedImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
	printImageStatistic($number, "mostrated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
}

/**
 * Prints the latest images by ID (the order zenphoto recognized the images on the filesystem)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 */
function printLatestImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
	printImageStatistic($number, "latest", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
}

/**
 * Prints the latest images by date order (date taken order)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 		"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 */
function printLatestImagesByDate($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
	printImageStatistic($number, "latest-date", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
}

/**
 * Prints the latest images by mtime order (date uploaded order)
 *
 * @param string $number the number of images to get
 * @param string $albumfolder folder of an specific album
 * @param bool $showtitle if the image title should be shown
 * @param bool $showdate if the image date should be shown
 * @param bool $showdesc if the image description should be shown
 * @param integer $desclength the length of the description to be shown
 * @param string $showstatistic
 * 		"hitcounter" for showing the hitcounter (views),
 * 		"rating" for rating,
 * 	"rating+hitcounter" for both.
 * @param integer $width the width/cropwidth of the thumb if crop=true else $width is longest size. (Default 85px)
 * @param integer $height the height/cropheight of the thumb if crop=true else not used.  (Default 85px)
 * @param bool $crop 'true' (default) if the thumb should be cropped, 'false' if not
 * @param bool $collection only if $albumfolder is set: true if you want to get statistics from this album and all of its subalbums
 * @param bool $fullimagelink 'false' (default) for the image page link , 'true' for the unprotected full image link (to use Colorbox for example)
 */
function printLatestImagesByMtime($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
	printImageStatistic($number, "latest-mtime", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
}

/**
 * A little helper function that checks if an image or album is to be considered 'new' within the time range set in relation to getImageDate()/getAlbumDate()
 * Returns true or false.
 *
 * @param string $mode What to check "image" or "album".
 * @param integer $timerange The time range the item should be considered new. Default is 604800 (unix time seconds = ca. 7 days)
 * @return bool
 */
function checkIfNew($mode = "image", $timerange = 604800) {
	$currentdate = date("U");
	switch ($mode) {
		case "image":
			$itemdate = getImageDate("%s");
			break;
		case "album":
			$itemdate = getAlbumDate("%s");
			break;
	}
	$newcheck = $currentdate - $itemdate;
	if ($newcheck < $timerange) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Gets the number of all subalbums of all subalbum levels of either the current album or $albumobj
 *
 * @param object $albumobj Optional album object to check
 * @param string $pre Optional text you want to print before the number
 * @return bool
 */
function getNumAllSubalbums($albumobj, $pre = '') {
	global $_zp_gallery, $_zp_current_album;
	if (is_null($albumobj)) {
		$albumobj = $_zp_current_album;
	}
	$count = '';
	$albums = getAllAlbums($_zp_current_album);
	if (count($albums) != 0) {
		$count = '';
		foreach ($albums as $album) {
			$count++;
		}
		return $pre . $count;
	} else {
		return false;
	}
}

?>