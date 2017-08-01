<?php
// force UTF-8 Ø

/**
 * Prints a list of tags, editable by admin. SEO version with nofollow removed.
 *
 * @param string $option links by default, if anything else the
 *               tags will not link to all other images with the same tag
 * @param string $preText text to go before the printed tags
 * @param string $class css class to apply to the div surrounding the UL list
 * @param string $separator what charactor shall separate the tags
 * @since 1.1
 */
function printTags_zb($option = 'links', $preText = NULL, $class = NULL, $separator = ', ') {
	global $_zp_current_search;
	if (is_null($class)) {
		$class = 'taglist';
	}
	$singletag = getTags();
	$tagstring = implode(', ', $singletag);
	if ($tagstring === '' or $tagstring === NULL) {
		$preText = '';
	}
	if (in_context(ZP_IMAGE)) {
		$object = "image";
	} else if (in_context(ZP_ALBUM)) {
		$object = "album";
	} else if (in_context(ZP_ZENPAGE_PAGE)) {
		$object = "pages";
	} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
		$object = "news";
	}
	if (count($singletag) > 0) {
		if (!empty($preText)) {
			echo "<span class=\"tags_title\">" . $preText . "</span>";
		}
		echo '<ul class="' . $class . '" itemprop="keywords">';
		if (is_object($_zp_current_search)) {
			$albumlist = $_zp_current_search->getAlbumList();
		} else {
			$albumlist = NULL;
		}
		$ct = count($singletag);
		$x = 0;
		foreach ($singletag as $atag) {
			if (++$x == $ct) {
				$separator = "";
			}
			if ($option === "links") {
				$links1 = "<a href=\"" . html_encode(getSearchURL(($atag), '', 'tags', 0, array('albums' => $albumlist))) . "\" title=\"" . html_encode($atag) . "\" >";
				$links2 = "</a>";
			} else {
				$links1 = $links2 = '';
			}
			echo "\t<li>" . $links1 . $atag . $links2 . $separator . "</li>\n";
		}
		echo "</ul>";
	} else {
		echo "$tagstring";
	}
}

/**
 * SEO version of the printAllTagsAs function: nofollow removed
 *
 * @param string $option "cloud" for tag cloud, "list" for simple list
 * @param string $class CSS class
 * @param string $sort "results" for relevance list, "abc" for alphabetical, blank for unsorted
 * @param bool $counter TRUE if you want the tag count within brackets behind the tag
 * @param bool $links set to TRUE to have tag search links included with the tag.
 * @param int $maxfontsize largest font size the cloud should display
 * @param int $maxcount the floor count for setting the cloud font size to $maxfontsize
 * @param int $mincount the minimum count for a tag to appear in the output
 * @param int $limit set to limit the number of tags displayed to the top $numtags
 * @param int $minfontsize minimum font size the cloud should display
 * @since 1.1
 */
function printAllTagsAs_zb($option, $class = '', $sort = 'abc', $counter = FALSE, $links = TRUE, $maxfontsize = 2, $maxcount = 50, $mincount = 15, $limit = NULL, $minfontsize = 0.8) {
	global $_zp_current_search;
	$option = strtolower($option);
	if ($class != "") {
		$class = "class=\"" . $class . "\"";
	}
	$tagcount = getAllTagsUnique(NULL, 1, true);
	if (!is_array($tagcount)) {
		return false;
	}
	if ($sort == "results") {
		arsort($tagcount);
	}
	if (!is_null($limit)) {
		$tagcount = array_slice($tagcount, 0, $limit);
	}
	$list = '';
	echo "<ul " . $class . ">\n";
	foreach ($tagcount as $key => $val) {
		if (!$counter) {
			$counter = "";
		} else {
			$counter = " (" . $val . ") ";
		}
		if ($option == "cloud") { // calculate font sizes, formula from wikipedia
			if ($val <= $mincount) {
				$size = $minfontsize;
			} else {
				$size = min(max(round(($maxfontsize * ($val - $mincount)) / ($maxcount - $mincount), 2), $minfontsize), $maxfontsize);
			}
			$size = str_replace(',', '.', $size);
			$size = " style=\"font-size:" . $size . "em;\"";
		} else {
			$size = '';
		}
		if ($val >= $mincount) {
			if ($links) {
				if (is_object($_zp_current_search)) {
					$albumlist = $_zp_current_search->getAlbumList();
				} else {
					$albumlist = NULL;
				}
				$list .= "\t<li><a href=\"" .
								html_encode(getSearchURL($key, '', 'tags', 0, array('albums' => $albumlist))) . "\"$size >" .
								$key . $counter . "</a></li>\n";
			} else {
				$list .= "\t<li$size>" . $key . $counter . "</li>\n";
			}
		}
	} // while end
	if ($list) {
		echo $list;
	} else {
		echo '<li>' . gettext('No popular tags') . "</li>\n";
	}
	echo "</ul>\n";
}

/**
 * Bootstrap breadcrumb. Based on an unordered list. Includes link to root and microdata
 * Prints the breadcrumb navigation for album, gallery and image view.
 *
 * @param string $before Insert here the text to be printed before the links
 * @param string $between Insert here the text to be printed between the links
 * @param string $after Insert here the text to be printed after the links
 * @param mixed $truncate if not empty, the max lenght of the description.
 * @param string $elipsis the text to append to the truncated description
 */
function printParentBreadcrumb_zb() {
	$crumbs = getParentBreadcrumb();
	if (!empty($crumbs)) {
		$output = '';
		$i = 0;
		foreach ($crumbs as $crumb) {
			if ($i > 0) {
				$output;
			}
			$output .= '<li><a href="' . html_encode($crumb['link']) . '">' . html_encode($crumb['text']) . '</a></li>';
			$i++;
		}
		echo $output;
	}
}

/**
 * prints the breadcrumb item for the current images's album. Simpler formatting
 *
 * @param string $before Text to place before the breadcrumb
 * @param string $after Text to place after the breadcrumb
 * @param string $title Text to be used as the URL title tag
 */
function printAlbumBreadcrumb_zb() {
	if ($breadcrumb = getAlbumBreadcrumb()) {
		$output = '';
		$output .= '<li><a href="' . html_encode($breadcrumb['link']) . '">';
		$output .= html_encode($breadcrumb['text']);
		$output .= '</a></li>';
		echo $output;
	}
}

/**
 * Prints the parent items breadcrumb navigation for pages or categories
 *
 * @param string $before Text to place before the breadcrumb item
 * @param string $after Text to place after the breadcrumb item
 */
function printZenpageItemsBreadcrumb_zb() {
	global $_zp_current_page, $_zp_current_category;
	$parentitems = array();
	if (is_Pages()) {
		//$parentid = $_zp_current_page->getParentID();
		$parentitems = $_zp_current_page->getParents();
	}
	if (is_NewsCategory()) {
		//$parentid = $_zp_current_category->getParentID();
		$parentitems = $_zp_current_category->getParents();
	}

	foreach ($parentitems as $item) {
		if (is_Pages()) {
			$pageobj = newPage($item);
			$parentitemurl = html_encode($pageobj->getLink());
			$parentitemtitle = $pageobj->getTitle();
		}
		if (is_NewsCategory()) {
			$catobj = newCategory($item);
			$parentitemurl = $catobj->getLink();
			$parentitemtitle = $catobj->getTitle();
		}
		echo"<li><a href='" . $parentitemurl . "'>" . html_encode($parentitemtitle) . "</a></li>";
	}
}

/**
 * Prints the title of the currently selected news category
 *
 * @param string $before insert what you want to be show before it
 */
function printCurrentNewsCategory_zb() {
	global $_zp_current_category;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		echo '<li>';
		echo html_encode($_zp_current_category->getTitle());
		echo '</li>';
	}
}

/**
 * Puts up random image thumbs from the gallery. The function has been modified to cope with Bootstrap thumbnail layout
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
function printRandomImages_zb($number = 12, $class = null, $option = 'all', $rootAlbum = '', $width = NULL, $height = NULL, $crop = NULL, $fullimagelink = false) {
	if (is_null($crop) && is_null($width) && is_null($height)) {
		$crop = 2;
	} else {
		if (is_null($width))
			$width = 200;
		if (is_null($height))
			$height = 200;
		if (is_null($crop)) {
			$crop = 1;
		} else {
			$crop = (int) $crop && true;
		}
	}
	if (!empty($class))
		$class = ' class="' . $class . '"';
	echo '<div id="images" class="row $class">';
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
			echo '<div class="col-lg-3 col-md-4 col-sm-6" style="height:' . html_encode(getOption("thumb_size") + 55) . 'px;" ><div class="thumbnail" itemtype="http://schema.org/image" itemscope>';
			if ($fullimagelink) {
				$randomImageURL = $randomImage->getFullimageURL();
			} else {
				$randomImageURL = $randomImage->getLink();
			}
			echo '<a href="' . html_encode($randomImageURL) . '" title="' . html_encode($randomImage->getTitle()) . '" ';
			if ($fullimagelink) {
				echo 'rel="lightbox-random"';
			}
			echo '>';
			switch ($crop) {
				case 0:
					$sizes = getSizeCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, $randomImage);
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" />' . "\n";
					break;
				case 1:
					$sizes = getSizeCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, $randomImage);
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" />' . "\n";
					break;
				case 2:
					$sizes = getSizeDefaultThumb($randomImage);
					$html = '<img src="' . html_encode(pathurlencode($randomImage->getThumb())) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($randomImage->getTitle()) . '" rel="lightbox" />' . "\n";
					break;
			}
			echo zp_apply_filter('custom_image_html', $html, false);
			echo '</a>';
			echo '<div class="caption">';
			echo '<a href="' . html_encode($randomImage->getLink()) . '" title="' . html_encode($randomImage->getTitle()) . '">';
			echo html_encode($randomImage->getTitle());
			echo '</a></div>';
			echo '</div></div>';
		} else {
			break;
		}
	}
	echo "</div>";
}

/**
 * Prints the Metadata data of the current image
 * Simpler version of the classic printImageMetadata
 * @param string $title title tag for the class
 * @param bool $toggle set to true to get a javascript toggle on the display of the data
 * @param string $id style class id
 * @param string $class style class
 * @author Ozh, modified by OF
 */
function printImageMetadata_zb() {
	global $_zp_exifvars, $_zp_current_image;
	if (false === ($exif = getImageMetaData($_zp_current_image, true))) {
		return;
	}
	?>
	<h2>
		<?php echo (gettext('Image Info')); ?>
	</h2>
	<table class="table table-striped itemprop="exifData"">
	<?php
	foreach ($exif as $field => $value) {
		$label = $_zp_exifvars[$field][2];
		echo "<tr><th>$label:</th><td>";
		switch ($_zp_exifvars[$field][6]) {
			case 'time':
				echo zpFormattedDate(DATE_FORMAT, strtotime($value));
				break;
			default:
				echo html_encode($value);
				break;
		}
		echo "</td></tr>\n";
	}
	?>
	</table>
	<?php
}

/**
 * Prints jQuery JS to enable the toggling of search results of Zenpage  items
 *
 */
function printZDSearchToggleJS() {
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function toggleExtraElements(category, show) {
			if (show) {
				jQuery('.' + category + '_showless').show();
				jQuery('.' + category + '_showmore').hide();
				jQuery('.' + category + '_extrashow').show();
			} else {
				jQuery('.' + category + '_showless').hide();
				jQuery('.' + category + '_showmore').show();
				jQuery('.' + category + '_extrashow').hide();
			}
		}
		// ]]> -->
	</script>
	<?php
}

/**
 * Prints the "Show more results link" for search results for Zenpage items
 *
 * @param string $option "news" or "pages"
 * @param int $number_to_show how many search results should be shown initially
 */
function printZDSearchShowMoreLink($option, $number_to_show) {
	$option = strtolower($option);
	switch ($option) {
		case "news":
			$num = getNumNews();
			break;
		case "pages":
			$num = getNumPages();
			break;
	}
	if ($num > $number_to_show) {
		?>
		<a class="<?php echo $option; ?>_showmore" href="javascript:toggleExtraElements('<?php echo $option; ?>',true);"><?php echo gettext('Show more results'); ?></a>
		<a class="<?php echo $option; ?>_showless" style="display: none;"	href="javascript:toggleExtraElements('<?php echo $option; ?>',false);"><?php echo gettext('Show fewer results'); ?></a>
		<?php
	}
}

/**
 * Adds the css class necessary for toggling of Zenpage items search results
 *
 * @param string $option "news" or "pages"
 * @param string $c After which result item the toggling should begin. Here to be passed from the results loop.
 */
function printZDToggleClass($option, $c, $number_to_show) {
	$option = strtolower($option);
	$c = sanitize_numeric($c);
	if ($c > $number_to_show) {
		echo ' class="' . $option . '_extrashow" style="display:none;"';
	}
}

function my_checkPageValidity($request, $gallery_page, $page) {
	switch ($gallery_page) {
		case 'gallery.php':
			$gallery_page = 'index.php'; //	same as an album gallery index
			break;
		case 'index.php':
			if (extensionEnabled('zenpage')) {
				if (getOption('zenpage_zp_index_news')) {
					$gallery_page = 'news.php'; //	really a news page
					break;
				}
				if (getOption('zenpage_homepage')) {
					return $page == 1; // only one page if zenpage enabled.
				}
			}
			break;
		case 'news.php':
		case 'album.php':
		case 'search.php':
			break;
		default:
			if ($page != 1) {
				return false;
			}
	}
	return checkPageValidity($request, $gallery_page, $page);
}

/**
 * makex news page 1 link go to the index page
 * @param type $link
 * @param type $obj
 * @param type $page
 */
function newsOnIndex($link, $obj, $page) {
	if (is_string($obj) && $obj == 'news.php') {
		if (MOD_REWRITE) {
			if (preg_match('~' . _NEWS_ . '[/\d/]*$~', $link)) {
				$link = WEBPATH;
				if ($page > 1)
					$link .= '/' . _PAGE_ . '/' . $page;
			}
		} else {
			if (strpos($link, 'category=') === false && strpos($link, 'title=') === false) {
				$link = str_replace('?&', '?', rtrim(str_replace('p=news', '', $link), '?'));
			}
		}
	}
	return $link;
}

if (!OFFSET_PATH) {
	enableExtension('print_album_menu', 1 | THEME_PLUGIN, false);
	setOption('user_logout_login_form', 2, false);
	$_zp_page_check = 'my_checkPageValidity';
	if (extensionEnabled('zenpage') && getOption('zenpage_zp_index_news')) { // only one index page if zenpage plugin is enabled & displaying
		zp_register_filter('getLink', 'newsOnIndex');
	}
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
 * @param integer $threshold the minimum number of ratings an image must have to be included in the list. (Default 0)
 * @return string
 */
function printImageStatistic_zb($number, $option, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
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
	echo '<div id="images" class="row $class">';
	foreach ($images as $image) {
		if ($fullimagelink) {
			$imagelink = $image->getFullImageURL();
		} else {
			$imagelink = $image->getLink();
		}
		echo '<div class="col-lg-3 col-md-4 col-sm-6" style="height:' . html_encode(getOption("thumb_size") + 55) . 'px;" ><div class="thumbnail" itemtype="http://schema.org/image" itemscope><a href="' . html_encode($imagelink) . '" title="' . html_encode($image->getTitle()) . '" ';
		if ($fullimagelink) {
			echo 'rel="lightbox-latest"';
		}
		echo '>';
		switch ($crop) {
			case 0:
				$sizes = getSizeCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, $image);
				echo '<img src="' . html_encode(pathurlencode($image->getCustomImage($width, NULL, NULL, NULL, NULL, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" /></a>\n";
				break;
			case 1:
				$sizes = getSizeCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, $image);
				echo '<img src="' . html_encode(pathurlencode($image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, TRUE))) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" width=\"" . $width . "\" height=\"" . $height . "\" /></a>\n";
				break;
			case 2:
				$sizes = getSizeDefaultThumb($image);
				echo '<img src="' . html_encode(pathurlencode($image->getThumb())) . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" alt="' . html_encode($image->getTitle()) . "\" /></a>\n";
				break;
		}
		if ($showtitle) {
			echo '<div class="caption">';
			echo '<a href="' . html_encode(pathurlencode($image->getLink())) . '" title="' . html_encode($image->getTitle()) . "\">\n";
			echo $image->getTitle() . "</a>\n";
			echo '</div>';
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
			echo shortenContent($image->getDesc(), $desclength, ' (...)');
		}
		echo '</div></div>';
	}
	echo '</div>';
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
function printLatestImages_zb($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
	printImageStatistic_zb($number, "latest", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
}
?>