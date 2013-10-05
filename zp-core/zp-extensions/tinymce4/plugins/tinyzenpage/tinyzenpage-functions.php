<?php
/**
 * tinyZenpage - A TinyMCE plugin for Zenphoto with Zenpage
 * @author Malte Müller (acrylian)
 * @license GPL v2
 */
// sorry about all the inline css but something by TinyMCE's main css seems to override most setting on the css file no matter what I do...Too "lazy" for further investigation...:-)

$host = "http://" . html_encode($_SERVER["HTTP_HOST"]);

/**
 * Prints all albums of the Zenphoto gallery as a partial drop down menu (<option></option> parts).
 *
 * @return string
 */
function printFullAlbumsList() {
	global $_zp_gallery;
	$albumlist = $_zp_gallery->getAlbums();
	foreach ($albumlist as $album) {
		$albumobj = newAlbum($album);
		if ($albumobj->isMyItem(LIST_RIGHTS)) {
			echo "<option value='" . pathurlencode($albumobj->name) . "'>" . html_encode($albumobj->getTitle()) . unpublishedZenphotoItemCheck($albumobj) . " (" . $albumobj->getNumImages() . ")</option>";
			if (!$albumobj->isDynamic()) {
				printSubLevelAlbums($albumobj);
			}
		}
	}
}

/**
 * Recursive helper function for printFullAlbumsList() to get all sub albums of each top level album
 *
 * @return string
 */
function printSubLevelAlbums(&$albumobj) {
	global $_zp_gallery;
	$albumlist = $albumobj->getAlbums();
	foreach ($albumlist as $album) {
		$subalbumobj = newAlbum($album);
		$subalbumname = $subalbumobj->name;
		$level = substr_count($subalbumname, "/");
		$arrow = "";
		for ($count = 1; $count <= $level; $count++) {
			$arrow .= "&raquo; ";
		}
		echo "<option value='" . pathurlencode($subalbumobj->name) . "'>";
		echo $arrow . $subalbumobj->getTitle() . unpublishedZenphotoItemCheck($subalbumobj) . " (" . $subalbumobj->getNumImages() . ")</option>";
		if (!$subalbumobj->isDynamic()) {
			printSubLevelAlbums($subalbumobj);
		}
	}
}

/**
 * checks if a album or image is un-published and returns a '*'
 *
 * @return string
 */
function unpublishedZenphotoItemCheck($obj, $dropdown = true) {
	$span1 = "";
	$span2 = "";
	if ($obj->getShow() != "1") {
		if (!$dropdown) {
			$span1 = "<span class='unpublisheditem'>";
			$span2 = "</span>";
		}
		$show = $span1 . "*" . $span2;
	} else {
		$show = "";
	}
	return $show;
}

/**
 * shortens a string, truncate_string() was not exact enough.
 *
 * @param $title int Title of the image
 * @param $length int The desired length
 * @return string
 */
function shortentitle($title, $length) {
	if (strlen($title) > $length) {
		return substr($title, 0, $length) . "...";
	} else {
		return $title;
	}
}

/**
 * Prints the images and/or albums as thumbnails of the selected album
 *
 * @param $number int The number of images per page
 *
 * @return string
 */
function printImageslist($number) {
	global $_zp_gallery, $host;
	$args = array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

	if (isset($_GET['album']) AND !empty($_GET['album'])) {

		$album = urldecode(sanitize($_GET['album']));
		$albumobj = newAlbum($album);
		echo "<h3>" . gettext("Album:") . " <em>" . html_encode($albumobj->getTitle()) . unpublishedZenphotoItemCheck($albumobj, false) . "</em> / " . gettext("Album folder:") . " <em>" . html_encode($albumobj->name) . "</em><br /><small>" . gettext("(Click on image to include)") . "</small></h3>";

		$images_per_page = $number;
		if (isset($_GET['page'])) {
			$currentpage = sanitize_numeric($_GET['page']);
		} else {
			$currentpage = 1;
		}
		$imagecount = $albumobj->getNumImages();
		$pagestotal = ceil($imagecount / $images_per_page);
		printTinyPageNav($pagestotal, $currentpage, 'images');

		// album thumb display;
		$albumthumb = $albumobj->getAlbumThumbImage();
		$albumthumbalbum = $albumthumb->getAlbum();
		$albumdesc = $albumobj->getDesc();
		$imagedesc = $albumthumb->getDesc();
		$imgurl = getImageProcessorURI($args, $albumthumbalbum->name, $albumthumb->filename);
		$fullimage = pathurlencode(addslashes($albumthumb->getFullImage()));
		$imageType = getImageType($albumthumb);
		if ($imageType) {
			// Not a pure image
			$backgroundcss = 'albumthumb-image';
			$imgurl = $albumthumb->getThumb();
			$itemid = $albumthumb->getID();
		} else {
			$backgroundcss = 'albumthumb-other';
			$imgurl = getImageProcessorURI($args, $albumthumbalbum->name, $albumthumb->filename);
			$itemid = $albumthumb->getID();
		}
		$imgsizeurl = $albumthumb->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, TRUE);
		echo "<div class='thumb'>";
		echo "<a href=\"javascript: ZenpageDialog.insert('" . $itemid . "','" . $imgurl . "','" .
		$albumobj->getAlbumThumb() . "','" .
		"','" .
		urlencode($albumthumb->filename) . "','" .
		js_encode($albumthumb->getTitle()) . "','" .
		js_encode($albumobj->getTitle()) . "','" .
		$fullimage . "',
																											'zenphoto','" .
		js_encode(getWatermarkParam($albumthumb, WATERMARK_THUMB)) . "','" .
		js_encode(getWatermarkParam($albumthumb, WATERMARK_IMAGE)) . "','" .
		$imageType . "',
																											'" . html_encode(addslashes($imagedesc)) . "',
																											'" . html_encode(addslashes($albumdesc)) . "');\"" .
		" title='" . html_encode($albumthumb->getTitle()) . " (" . html_encode($albumthumb->filename) . ")'>
																											<img src='" . $imgsizeurl . "' class='" . $backgroundcss . "' /></a>\n";

		echo "<a href='../../../../../.." . html_encode($albumthumb->getImageLink()) .
		"' title='Zoom' rel='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a> " .
		gettext('<em>Albumthumb</em>') . unpublishedZenphotoItemCheck($albumthumb, false);
		echo "</div>";
		$images = $albumobj->getImages();

		if ($albumobj->getNumImages() != 0) {
			for ($nr = 1; $nr <= $pagestotal; $nr++) {
				$startimage[$nr] = $nr * $images_per_page - $images_per_page; // get start image number
				$endimage[$nr] = $nr * $images_per_page - 1; // get end image number
			}
			$number = $startimage[$currentpage];
			for ($nr = $number; $nr <= $images_per_page * $currentpage; $nr++) {
				if ($nr === $imagecount) {
					break;
				}
				if ($albumobj->isDynamic()) {
					$linkalbumobj = newAlbum($images[$nr]['folder']);
					$imageobj = newImage($linkalbumobj, $images[$nr]['filename']);
				} else {
					$linkalbumobj = $albumobj;
					$imageobj = newImage($albumobj, $images[$nr]);
				}
				$imagedesc = $imageobj->getDesc();
				$albumdesc = $linkalbumobj->getDesc();
				$fullimage = pathurlencode(addslashes($imageobj->getFullImage()));
				$imageType = getImageType($imageobj);
				$thumburl = $imageobj->getThumb();
				$imgurl = $imageobj->getimageLink(false);
				//$sizedimage = $imageobj->getSizedImage(getOption('image_size'));
				switch ($imageType) {
					case '':
						// image photo
						$backgroundcss = 'thumb-image';
						$imgurl = getImageProcessorURI($args, $linkalbumobj->name, $imageobj->filename);
						$sizedimage = $imageobj->getSizedImage(getOption('image_size'));
						$sizedimage = '<img src="' . $sizedimage . '" alt="' . $imageobj->getTitle() . '" class="zenpage_sizedimage" />';
						$itemid = '';
						break;
					case 'textobject':
						$sizedimage = $imageobj->getSizedImage(getOption('image_size'));
						$sizedimage = str_replace('class="textobject"', 'class="textobject zenpage_sizedimage"', $sizedimage);
						$imgurl = getImageProcessorURI($args, $linkalbumobj->name, $imageobj->filename);
						$backgroundcss = 'thumb-textobject';
						$itemid = '';
						break;
					case 'video':
					case 'audio':
						$sizedimage = $imageobj->getThumb();
						$sizedimage = str_replace('class="flowplayer"', 'class="flowplayer zenpage_sizedimage"', $sizedimage);
						$imgurl = getImageProcessorURI($args, $linkalbumobj->name, $imageobj->filename);
						$backgroundcss = 'thumb-multimedia';
						$itemid = $imageobj->getID();
						break;
					default:
						$sizedimage = $imageobj->getSizedImage(getOption('image_size'));
						$backgroundcss = 'thumb-default';
						$itemid = '';
						break;
				}
				$imgsizeurl = $imageobj->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, TRUE);
				echo "<div class='thumb'>\n";
				echo "<a href=\"javascript:ZenpageDialog.insert('" . $itemid . "','" . $imgurl . "','" .
				$thumburl . "','" .
				html_encode($sizedimage) . "','" .
				urlencode($imageobj->filename) . "','" .
				js_encode($imageobj->getTitle()) . "','" .
				js_encode($linkalbumobj->getTitle()) . "','" .
				$fullimage . "',
																												'zenphoto','" .
				js_encode(getWatermarkParam($imageobj, WATERMARK_THUMB)) . "','" .
				js_encode(getWatermarkParam($imageobj, WATERMARK_IMAGE)) . "','" .
				$imageType . "',
																												'" . html_encode(addslashes($imagedesc)) . "',
																												'" . html_encode(addslashes($albumdesc)) . "');\"" .
				" title='" . html_encode($imageobj->getTitle()) . " (" . html_encode($imageobj->filename) . ")'>
																												<img src='" . $imgsizeurl . "' class='" . $backgroundcss . "' /></a>\n";
				echo "<a href='../../../../../.." . html_encode($imageobj->getImageLink()) .
				"' title='Zoom' rel='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a> " .
				html_encode(shortentitle($imageobj->getTitle(), 8)) . unpublishedZenphotoItemCheck($imageobj, false);
				echo "</div>\n";
				if ($nr === $endimage[$currentpage]) {
					break;
				}
			} // for end
		} else {
			echo "<p class='noimages'>" . gettext("<strong>Note:</strong> This album does not contain any images.") . "</p>";
		} // if/else  no image end
	} // if GET album end
}

/**
 * Returns the object "type" of the "image".
 *
 * Note:
 * 	If the root object is a video object then
 * 	If flowplayer3 is enabled a sub-type of video or audio will
 * 	be determined from the suffix. If it is not one of the
 * 	known suffixes or if flowplayer3 is not enabled then 'other' is
 * 	returned as the object type.
 *
 * 	Pure images return empty for an object type.
 *
 * @return string
 */
function getImageType($imageobj) {
	$imageType = strtolower(get_class($imageobj));
	switch ($imageType) {
		case 'video':
			$imagesuffix = getSuffix($imageobj->filename);
			switch ($imagesuffix) {
				case 'flv':
				case 'mp4':
				case 'm4v':
					$imageType = 'video';
					break;
				case 'mp3':
				case 'fla':
				case 'm4a':
					$imageType = 'audio';
					break;
			}
			break;
		case 'image':
			$imageType = '';
			break;
		default:
			$parent = strtolower(get_parent_class($imageobj));
			if ($parent == 'textobject') {
				$imageType = 'textobject';
			}
			break;
	}
	return $imageType;
}

/**
 * Prints all available articles in Zenpage
 *
 * @return string
 */
function printNewsArticlesList($number) {
	global $_zp_zenpage, $_zp_current_zenpage_news, $host;
	if (isset($_GET['zenpage']) && $_GET['zenpage'] == "articles") {
		echo "<h3>Zenpage: <em>" . gettext('Articles') . "</em> <small>" . gettext("(Click on article title to include a link)") . "</small></h3>";
		if (isset($_GET['category'])) {
			$cat = sanitize($_GET['category']);
			$catobj = new ZenpageCategory($cat);
			$items = $catobj->getArticles("", "all");
			$newscount = count($catobj->getArticles(0, 'all'));
		} else {
			$items = $_zp_zenpage->getArticles("", "all");
			$newscount = count($_zp_zenpage->getArticles(0, 'all'));
		}
		$news_per_page = $number;
		if (isset($_GET['page'])) {
			$currentpage = sanitize_numeric($_GET['page']);
		} else {
			$currentpage = 1;
		}
		$pagestotal = ceil($newscount / $news_per_page);
		for ($nr = 1; $nr <= $pagestotal; $nr++) {
			$startnews[$nr] = $nr * $news_per_page - $news_per_page; // get start image number
			$endnews[$nr] = $nr * $news_per_page - 1; // get end image number
		}
		$count = '';
		$number = $startnews[$currentpage];
		//category selector here later
		printTinyZenpageCategorySelector($currentpage);
		if ($newscount != 0) {
			printTinyPageNav($pagestotal, $currentpage, 'news');
			echo "<ul class='zenpagearticles'>";
			for ($nr = $number; $nr <= $news_per_page * $currentpage; $nr++) {
				if ($nr == $newscount) {
					break;
				}
				$newsobj = new ZenpageNews($items[$nr]['titlelink']);
				$count++;
				echo "<li>";
				if ($_GET['zenpage'] == "articles") {
					echo "<a href=\"javascript:ZenpageDialog.insert('','news/" . $newsobj->getTitlelink() . "','','','" . $newsobj->getTitlelink() . "','" . addslashes($newsobj->getTitle()) . "','','','articles','','','','');\" title='" . html_encode(truncate_string(strip_tags($newsobj->getContent()), 300)) . "'>" . addslashes($newsobj->getTitle()) . unpublishedZenpageItemCheck($newsobj) . "</a> <small><em>" . $newsobj->getDatetime() . "</em></small>";
					echo " <a href='zoom.php?news=" . urlencode($newsobj->getTitlelink()) . "' title='Zoom' class='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a><br />";
					echo '<small><em>' . gettext('Categories:');
					$cats = $newsobj->getCategories();
					$count = '';
					foreach ($cats as $cat) {
						$count++;
						$catobj = new ZenpageCategory($cat['titlelink']);
						if ($count == 1) {
							echo ' ';
						} else {
							echo ', ';
						}
						echo $catobj->getTitle();
					}
					echo '</em></small>';
				}
				echo "</li>";
				if ($nr === $endnews[$currentpage]) {
					break;
				}
			} // for end
			echo "</ul>";
		}
	}
}

/**
 * Checks if an album has images for display on the form
 *
 * @return bool
 */
function checkAlbumForImages() {
	global $_zp_gallery;
	if (isset($_GET['album']) AND !empty($_GET['album'])) {
		$album = urldecode(sanitize($_GET['album']));
		if ($album == 'gallery') {
			return FALSE;
		}
		$albumobj = newAlbum($album);
		if ($albumobj->getNumImages() != 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return TRUE;
	}
}

/**
 * Checks if the full Zenphoto include form options should be shown
 *
 * @return bool
 */
function showZenphotoOptions() {
	return isset($_GET['album']) && !empty($_GET['album']);
}

/**
 * Prints the page navigation for albums or Zenpage news articles
 *
 * @param $pagestotal int The number page in total
 * @param $currentpage int Number of the current page
 * @param $mode string 'images' or 'articles'
 * @return string
 */
function printTinyPageNav($pagestotal = "", $currentpage = "", $mode = 'images') {
	$cat = '';
	switch ($mode) {
		case 'images':
			$url = "album=" . pathurlencode(sanitize($_GET['album']));
			break;
		case 'news':
			$url = "zenpage=articles";
			if (isset($_GET['category'])) {
				$cat = '&amp;' . sanitize($_GET['category']);
			}
			break;
	}
	if ($pagestotal > 1) {
		echo "<br /><br /><ul class='tinypagenav'>";
		if ($currentpage != 1) {
			echo "<li class=\"first\"><a href='tinyzenpage.php?" . $url . $cat . "&amp;page=1'>&laquo; first</a></li>";
		} else {
			echo "<li class=\"first\" class='inactive'>&laquo; first</li>";
		}
		if ($currentpage != 1) {
			echo "<li class=\"prev\"><a href='tinyzenpage.php?" . $url . $cat . "&amp;page=" . ($currentpage - 1) . "'>&laquo; prev</a></li>";
		} else {
			echo "<li class=\"prev\" class='inactive'>&laquo; prev</li>";
		}
		$j = max(1, min($currentpage - 3, $pagestotal - 6));
		if ($j != 1) {
			echo "\n <li>";
			echo "<a href=\"tinyzenpage.php?" . $url . $cat . "&amp;page=" . max($j - 4, 1) . "\">...</a>";
			echo '</li>';
		}
		for ($i = $j; $i <= min($pagestotal, $j + 6); $i++) {
			if ($i == $currentpage) {
				echo "<li>" . $i . "</li>\n";
			} else {
				echo "<li><a href='tinyzenpage.php?" . $url . $cat . "&amp;page=" . $i . "' title='" . gettext("Page") . " " . $i . "'>" . $i . "</a></li>\n";
			}
		}
		if ($i <= $pagestotal) {
			echo "\n <li>";
			echo "<a href=\"tinyzenpage.php?" . $url . $cat . "&amp;page=" . min($j + 10, $pagestotal) . "\">...</a>";
			echo '</li>';
		}
		if ($currentpage != $pagestotal) {
			echo "<li class=\"next\"><a href='tinyzenpage.php?" . $url . "&amp;page=" . ($currentpage + 1) . "'>next &raquo;</a></li>";
		} else {
			echo "<li class=\"next\" class='inactive'>next &raquo;</li>";
		}
		if ($currentpage != $pagestotal) {
			echo "<li class=\"last\"><a href='tinyzenpage.php?" . $url . "&amp;page=" . $pagestotal . "'>last &raquo;</a></li>";
		} else {
			echo "<li class=\"last\" class='inactive'>last &raquo;</li>";
		}
		echo "</ul>";
	}
}

/**
 * Prints the Zenpage items as a partial dropdown (pages, news articles, categories)
 *
 * @return string
 */
function printZenpageItems() {
	global $_zp_zenpage;
	$pages = $_zp_zenpage->getPages(false);
	$pagenumber = count($pages);
	$categories = $_zp_zenpage->getAllCategories(false);
	$catcount = count($categories);
	echo "<option value='pages'>" . gettext("pages") . " (" . $pagenumber . ")</option>";
	echo "<option value='articles'>" . gettext("articles") . " (" . count($_zp_zenpage->getArticles(0, 'all')) . ")</option>";
	echo "<option value='categories'>" . gettext("categories") . " (" . $catcount . ")</option>";
}

/**
 * Prints all available pages or categories in Zenpage
 *
 * @return string
 */
function printAllNestedList() {
	global $_zp_zenpage, $host;
	if (isset($_GET['zenpage']) && ($_GET['zenpage'] == "pages" || $_GET['zenpage'] == "categories")) {
		$mode = sanitize($_GET['zenpage']);
		switch ($mode) {
			case 'pages':
				$items = $_zp_zenpage->getPages(false);
				$listtitle = gettext('Pages');
				break;
			case 'categories':
				$items = $_zp_zenpage->getAllCategories(false);
				$listtitle = gettext('Categories');
				break;
		}
		echo "<h3>Zenpage: <em>" . html_encode($listtitle) . "</em> <small> " . gettext("(Click on article title to include a link)") . "</small></h3>";
		echo "<ul class='tinynesteditems'>";
		$indent = 1;
		$open = array(1 => 0);
		$rslt = false;
		foreach ($items as $key => $item) {
			switch ($mode) {
				case 'pages':
					$obj = new ZenpagePage($item['titlelink']);
					$itemcontent = truncate_string(strip_tags($obj->getContent()), 300);
					$zenpagepage = _PAGES_ . '/' . $item['titlelink'];
					$unpublished = unpublishedZenpageItemCheck($obj);
					$counter = '';
					break;
				case 'categories':
					$obj = new ZenpageCategory($item['titlelink']);
					$itemcontent = $obj->getTitle();
					$zenpagepage = "news/category/" . $item['titlelink'];
					$unpublished = unpublishedZenpageItemCheck($obj);
					$counter = ' (' . count($obj->getArticles()) . ') ';
					break;
			}
			$itemsortorder = $obj->getSortOrder();
			$itemtitlelink = $obj->getTitlelink();
			$itemtitle = $obj->getTitle();
			$itemid = $obj->getID();
			$order = explode('-', $itemsortorder);
			$level = max(1, count($order));
			if ($toodeep = $level > 1 && $order[$level - 1] === '') {
				$rslt = true;
			}
			if ($level > $indent) {
				echo "\n" . str_pad("\t", $indent, "\t") . "<ul>\n";
				$indent++;
				$open[$indent] = 0;
			} else if ($level < $indent) {
				while ($indent > $level) {
					$open[$indent]--;
					$indent--;
					echo "</li>\n" . str_pad("\t", $indent, "\t") . "</ul>\n";
				}
			} else { // indent == level
				if ($open[$indent]) {
					echo str_pad("\t", $indent, "\t") . "</li>\n";
					$open[$indent]--;
				} else {
					echo "\n";
				}
			}
			if ($open[$indent]) {
				echo str_pad("\t", $indent, "\t") . "</li>\n";
				$open[$indent]--;
			}
			echo "<li id='" . $itemid . "' class='itemborder'>";
			echo "<a href=\"javascript:ZenpageDialog.insert('','" . $zenpagepage . "','','','" . $itemtitlelink . "','" . js_encode($itemtitle) . "','','','" . $mode . "','','','','');\" title='" . html_encode($itemcontent) . "'>" . html_encode($itemtitle) . $unpublished . $counter . "</a> <small><em>" . $obj->getDatetime() . "</em></small>";
			if ($mode == 'pages') {
				echo " <a href='zoom.php?pages=" . urlencode($itemtitlelink) . "' title='Zoom' class='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a>";
			}
			$open[$indent]++;
		}
		while ($indent > 1) {
			echo "</li>\n";
			$open[$indent]--;
			$indent--;
			echo str_pad("\t", $indent, "\t") . "</ul>";
		}
		if ($open[$indent]) {
			echo "</li>\n";
		} else {
			echo "\n";
		}
		echo "</ul>\n";
	}
}

/**
 * checks if a news article or page is un-published and/or protected and returns a '*'
 *
 * @return string
 */
function unpublishedZenpageItemCheck($page) {
	$class = get_class($page);
	$unpublishednote = '';
	$protected = '';
	switch ($class) {
		case 'ZenpageNews':
		case 'ZenpagePage':
			if ($page->getShow() === "0") {
				$unpublishednote = "<span style='color: red; font-weight: bold'>*</span>";
			}
			switch ($class) {
				case 'ZenpageNews':
					if ($page->inProtectedCategory()) {
						$protected = "<span style='color: red; font-weight: bold'>+</span>";
					}
					break;
				case 'ZenpagePage':
					if ($page->isProtected()) {
						$protected = "<span style='color: red; font-weight: bold'>+</span>";
					}
					break;
			}
			break;
		case 'ZenpageCategory':
			if ($page->isProtected()) {
				$protected = "<span style='color: red; font-weight: bold'>+</span>";
			}
			break;
	}
	return $unpublishednote . $protected;
}

/**
 * Set the locale for gettext translation of this plugin. Somehow ZenPhoto's setPluginDomain() does not work here...
 *
 */
function setTinyZenpageLocale() {
	$encoding = LOCAL_CHARSET;
	$locale = ZENPHOTO_LOCALE;
	@putenv("LANG=$locale");
	$result = setlocale(LC_ALL, $locale);
	$domain = 'tinyzenpage';
	$domainpath = "locale/";
	bindtextdomain($domain, $domainpath);
	// function only since php 4.2.0
	if (function_exists('bind_textdomain_codeset')) {
		bind_textdomain_codeset($domain, $encoding);
	}
	textdomain($domain);
}

/**
 * Prints the dropdown menu for the category selector for the news articles list
 *
 */
function printTinyZenpageCategorySelector($currentpage = '') {
	global $_zp_zenpage;
	$result = $_zp_zenpage->getAllCategories(false);
	if (isset($_GET['category'])) {
		$selected = '';
		$category = sanitize($_GET['category']);
	} else {
		$selected = "selected='selected'";
		$category = "";
	}
	?>
	<form name ="AutoListBox2" id="categorydropdown" style="float:left" action="#" >
		<select name="ListBoxURL" size="1" onchange="gotoLink(this.form)">
	<?php
	echo "<option $selected value='tinyzenpage.php?zenpage=articles&amp;page=" . $currentpage . "'>" . gettext("All categories") . "</option>\n";

	foreach ($result as $cat) {
		$catobj = new ZenpageCategory($cat['titlelink']);
		// check if there are articles in this category. If not don't list the category.
		$count = count($catobj->getArticles(0, 'all'));
		$count = " (" . $count . ")";
		if ($category == $cat['titlelink']) {
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}
		//This is much easier than hacking the nested list function to work with this
		$getparents = $catobj->getParents();
		$levelmark = '';
		foreach ($getparents as $parent) {
			$levelmark .= '&raquo; ';
		}
		$title = $catobj->getTitle();
		if (empty($title)) {
			$title = '*' . $catobj->getTitlelink() . '*';
		}
		if ($count != " (0)") {
			echo "<option $selected value='tinyzenpage.php?zenpage=articles&amp;page=" . $currentpage . "&amp;category=" . $catobj->getTitlelink() . "'>" . $levelmark . $title . $count . "</option>\n";
		}
	}
	?>
		</select>
		<script type="text/javascript" >
			// <!-- <![CDATA[
			function gotoLink(form) {
				var OptionIndex = form.ListBoxURL.selectedIndex;
				this.location = form.ListBoxURL.options[OptionIndex].value;
			}
			// ]]> -->
		</script>
	</form>
	<br />
	<?php
}
?>