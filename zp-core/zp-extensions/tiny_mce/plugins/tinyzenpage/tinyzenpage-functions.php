<?php
/**
 * tinyZenpage - A TinyMCE plugin for Zenphoto with Zenpage
 * @author Malte Müller (acrylian)
 * @license GPL v2
 */
// sorry about all the inline css but something by TinyMCE's main css seems to override most setting on the css file no matter what I do...Too "lazy" for further investigation...:-)

$host = "http://".html_encode($_SERVER["HTTP_HOST"]);
/**
 * Prints all albums of the Zenphoto gallery as a partial drop down menu (<option></option> parts).
 *
 * @return string
 */
function printFullAlbumsList() {
	global $_zp_gallery;
	if(is_null($_zp_gallery)) {
		$_zp_gallery = new Gallery();
	}
	$albumlist = $_zp_gallery->getAlbums();
	foreach($albumlist as $album) {
		$albumobj = new Album($_zp_gallery, $album);
		if ($albumobj->isMyItem(LIST_RIGHTS)) {
			echo "<option value='".pathurlencode($albumobj->name)."'>".html_encode($albumobj->getTitle()).unpublishedZenphotoItemCheck($albumobj)." (".$albumobj->getNumImages().")</option>";
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
	foreach($albumlist as $album) {
		$subalbumobj = new Album($_zp_gallery,$album);
		$subalbumname = $subalbumobj->name;
		$level = substr_count($subalbumname,"/");
		$arrow = "";
		for($count = 1; $count <= $level; $count++) {
			$arrow .= "&raquo; ";
		}
		echo "<option value='".pathurlencode($subalbumobj->name)."'>";
		echo $arrow.$subalbumobj->getTitle().unpublishedZenphotoItemCheck($subalbumobj)." (".$subalbumobj->getNumImages().")</option>";
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
function unpublishedZenphotoItemCheck($obj,$dropdown=true) {
	$span1 = "";
	$span2 = "";
	if($obj->getShow() != "1") {
		if(!$dropdown) {
			$span1 = "<span style='color: red; font-weight: bold'>";
			$span2 = "</span>";
		}
		$show = $span1."*".$span2;
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
function shortentitle($title,$length) {
	if(strlen($title) > $length) {
		return substr($title,0,$length)."...";
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

	if(isset($_GET['album']) AND !empty($_GET['album'])) {

		$album = urldecode(sanitize($_GET['album']));
		$albumobj = new Album($_zp_gallery,$album);
		echo "<h3 style='margin-bottom:10px'>".gettext("Album:")." <em>".html_encode($albumobj->getTitle()).unpublishedZenphotoItemCheck($albumobj,false)."</em> / ".gettext("Album folder:")." <em>".html_encode($albumobj->name)."</em><br /><small>".gettext("(Click on image to include)")."</small></h3>";

			// album thumb display;
		$albumthumb = $albumobj->getAlbumThumbImage();
		$albumthumbalbum = $albumthumb->getAlbum();
		$albumdesc = '';
		$albumdesc = $albumobj->getDesc();
		$imagedesc = $albumthumb->getDesc();
		$imgurl = $host.WEBPATH.'/'.ZENFOLDER."/i.php?a=". urlencode(pathurlencode($albumthumbalbum->name))."&amp;i=".urlencode(urlencode($albumthumb->filename));
		$fullimage = pathurlencode($albumthumb->getFullImage());
		$videocheck = checkIfImageVideo($albumthumb);
		if(get_class($albumthumb) == '_Image') {
			$video = '';
			$backgroundcss = 'border: 1px solid gray; padding: 1px;';
			$imgurl = $host.WEBPATH.'/'.ZENFOLDER."/i.php?a=". urlencode(pathurlencode($albumthumbalbum->name))."&amp;i=".urlencode(urlencode($albumthumb->filename));
		} else {
			$backgroundcss = 'border: 1px solid orange; padding: 1px;background-color: orange';
			$video = $videocheck;
			$imgurl = $albumthumb->getThumb();
		}
		$imgsizeurl = $albumthumb->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, TRUE);
		echo "<div class='albumthumb' style='width: 85px; height: 100px; float: left; margin: 10px 10px 10px 13px'>";
		echo "<a href=\"javascript:ZenpageDialog.insert('".$imgurl."','".urlencode($albumthumb->filename)."','".
																											html_encode($albumthumb->getTitle())."','".
																											html_encode($albumobj->getTitle())."','".
																											$fullimage."','zenphoto','".
																											html_encode(getWatermarkParam($albumthumb, WATERMARK_THUMB))."','".
																											html_encode(getWatermarkParam($albumthumb, WATERMARK_IMAGE))."','".
																											$video."','".html_encode($imagedesc)."','".html_encode($albumdesc)."');\"".
																											" title='".
																											html_encode($albumthumb->getTitle())." (".html_encode($albumthumb->filename).")'><img src='".
																											$imgsizeurl."' style='".$backgroundcss."' /></a>\n";
		echo "<a href='zoom.php?image=".urlencode($albumthumb->filename)."&amp;album=".pathurlencode($albumthumbalbum->name).
																											"' title='Zoom' rel='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a> ".
																											gettext('<em>Albumthumb</em>').unpublishedZenphotoItemCheck($albumthumb,false);
		echo "</div>";

		$images = $albumobj->getImages();

		if($albumobj->getNumImages() != 0) {
			$images_per_page = $number;
			if(isset($_GET['page'])) {
				$currentpage = sanitize_numeric($_GET['page']);
			} else {
				$currentpage = 1;
			}
			$imagecount = $albumobj->getNumImages();
			$pagestotal = ceil($imagecount / $images_per_page);
			for ($nr = 1;$nr <= $pagestotal; $nr++) {
				$startimage[$nr] = $nr * $images_per_page - $images_per_page; // get start image number
				$endimage[$nr] = $nr * $images_per_page - 1; // get end image number
			}
			$number = $startimage[$currentpage];
			printTinyPageNav($pagestotal,$currentpage,'images');
			for ($nr = $number;$nr <= $images_per_page*$currentpage; $nr++)	{
				if ($nr === $imagecount){
					break;
				}
				if($albumobj->isDynamic()) {
					$linkalbumobj = new Album($_zp_gallery,$images[$nr]['folder']);
					$imageobj = newImage($linkalbumobj,$images[$nr]['filename']);
				} else {
					$linkalbumobj = $albumobj;
					$imageobj = newImage($albumobj,$images[$nr]);
				}
				$imagedesc = '';
				$imagedesc = $imageobj->getDesc();
				$albumdesc = '';
				$albumdesc = $linkalbumobj->getDesc();
				$fullimage = pathurlencode($imageobj->getFullImage());
				$videocheck = checkIfImageVideo($imageobj);
				if(get_class($imageobj) == '_Image') {
					$video = '';
					$backgroundcss = 'border: 1px solid gray; padding: 1px;';
					$imgurl = $host.WEBPATH.'/'.ZENFOLDER."/i.php?a=".urlencode(pathurlencode($linkalbumobj->name))."&amp;i=".urlencode(urlencode($imageobj->filename));
				} else if(get_class($imageobj) == 'TextObject' || get_parent_class($imageobj) == 'TextObject') {
					$video = 'textobject';
					$imgurl = $imageobj->getThumb();
					$fullimage = html_encode($imageobj->getBody());
				} else {
					$backgroundcss = 'border: 1px solid orange; padding: 1px;background-color: orange';
					$video = $videocheck;
					$imgurl = $imageobj->getThumb();
				}
				$imgsizeurl = $imageobj->getCustomImage(85, NULL, NULL, 85, 85, NULL, NULL, TRUE);
				echo "<div style='width: 85px; height: 100px; float: left; margin: 10px 10px 10px 13px'>\n";
				echo "<a href=\"javascript:ZenpageDialog.insert('".$imgurl."','".urlencode($imageobj->filename)."','".
																												html_encode($imageobj->getTitle())."','".
																												html_encode($linkalbumobj->getTitle())."','".
																												$fullimage."','zenphoto','".
																												html_encode(getWatermarkParam($imageobj, WATERMARK_THUMB))."','".
																												html_encode(getWatermarkParam($imageobj, WATERMARK_IMAGE))."','".
																												$video."','".html_encode($imagedesc)."','".html_encode($albumdesc)."');\"".
																												" title='".
																												html_encode($imageobj->getTitle())." (".html_encode($imageobj->filename).")'><img src='".
																												$imgsizeurl."' style='".$backgroundcss."' /></a>\n";
				echo "<a href='zoom.php?image=".urlencode($imageobj->filename)."&amp;album=".pathurlencode($linkalbumobj->name).
																												"' title='Zoom' rel='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a> ".
																												html_encode(shortentitle($imageobj->getTitle(),8)).unpublishedZenphotoItemCheck($imageobj,false);
				echo "</div>\n";
				if ($nr === $endimage[$currentpage]){
					break;
				}
			} // for end
		} else {
			echo "<p style='margin-left: 8px'>".gettext("<strong>Note:</strong> This album does not contain any images.")."</p>";
		}	// if/else  no image end
	} // if GET album end
}

/**
 * Checks if the Zenphoto items is a video object (mp3,mp4,flv)
 *
 * @return string
 */

function checkIfImageVideo($imageobj) {
	$video = '';
	if(isImageVideo($imageobj) && getOption('zp_plugin_flowplayer3')) {
		$imagesuffix = getSuffix($imageobj->filename);
		switch($imagesuffix) {
			case 'flv':
			case 'mp4':
				$video = 'video';
				break;
			case 'mp3':
				$video = 'mp3';
				break;
		}
	} else {
		$video = '';
		$backgroundcss = 'border: 1px solid gray; padding: 1px;';
	}
	return $video;
}


/**
 * Prints all available articles in Zenpage
 *
 * @return string
 */
function printNewsArticlesList($number) {
	global $_zp_zenpage, $_zp_current_zenpage_news,$host;
	if(isset($_GET['zenpage']) && $_GET['zenpage'] == "articles") {
		echo "<h3 style='margin-bottom:10px'>Zenpage: <em>".gettext('Articles')."</em> <small>".gettext("(Click on article title to include a link)")."</small></h3>";
		echo "<ul style='list-style-type: none; width: 85%;'>";
		$items = $_zp_zenpage ->getNewsArticles("","all");
		$news_per_page = $number;
		if(isset($_GET['page'])) {
			$currentpage = sanitize_numeric($_GET['page']);
		} else {
			$currentpage = 1;
		}
		$newscount = count($_zp_zenpage ->getNewsArticles(0,'all'));
		$pagestotal = ceil($newscount / $news_per_page);
		for ($nr = 1;$nr <= $pagestotal; $nr++) {
			$startnews[$nr] = $nr * $news_per_page - $news_per_page; // get start image number
			$endnews[$nr] = $nr * $news_per_page - 1; // get end image number
		}
		$count = '';
		$number = $startnews[$currentpage];
		if($newscount != 0) {
			printTinyPageNav($pagestotal,$currentpage,'news');
			echo '<br />';
			for ($nr = $number;$nr <= $news_per_page*$currentpage; $nr++)	{
				if ($nr == $newscount){
					break;
				}
				$newsobj = new ZenpageNews($items[$nr]['titlelink']);
				$count++;
				if($count === 1) {
					$firstitemcss = "border-top: 1px dotted gray; border-bottom: 1px dotted gray; padding: 5px 0px 5px 0px;";
				} else {
					$firstitemcss = "border-bottom: 1px dotted gray; padding: 5px 0px 5px 0px;";
				}
				echo "<li style='".$firstitemcss."'>";
				if($_GET['zenpage'] == "articles") {
					echo "<a href=\"javascript:ZenpageDialog.insert('news/".$newsobj->getTitlelink()."','".$newsobj->getTitlelink()."','".html_encode($newsobj->getTitle())."','','','articles','','','','');\" title='".html_encode(truncate_string(strip_tags($newsobj->getContent()),300))."'>".html_encode($newsobj->getTitle()).unpublishedZenpageItemCheck($newsobj)."</a> <small><em>".$newsobj->getDatetime()."</em></small>";
					echo " <a href='zoom.php?news=".urlencode($newsobj->getTitlelink())."' title='Zoom' class='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a><br />";
					echo '<small><em>'.gettext('Categories:');
					$cats = $newsobj->getCategories();
					$count = '';
					foreach($cats as $cat) {
						$count++;
						$catobj = new ZenpageCategory($cat['titlelink']);
						if($count == 1) {
							echo ' ';
						} else {
							echo ', ';
						}
						echo $catobj->getTitle();
					}
					echo '</em></small>';
				}
				echo "</li>";
				if ($nr === $endnews[$currentpage]){
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
	if(isset($_GET['album']) AND !empty($_GET['album'])) {
		$album = urldecode(sanitize($_GET['album']));
		if($album == 'gallery') {
			return FALSE;
		}
		$albumobj = new Album($_zp_gallery,$album);
		if($albumobj->getNumImages() != 0) {
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
	if((!isset($_GET['zenpage']) OR !isset($_GET['album'])) OR (isset($_GET['album']) AND !empty($_GET['album']))) {
		return TRUE;
	} else {
		return FALSE;
	}
}


/**
 * Prints the page navigation for albums or Zenpage news articles
 *
 * @param $pagestotal int The number page in total
 * @param $currentpage int Number of the current page
	* @param $mode string 'images' or 'articles'
 * @return string
 */
function printTinyPageNav($pagestotal="",$currentpage="",$mode='images') {
	switch($mode) {
		case 'images':
			$url = "album=".pathurlencode(sanitize($_GET['album']));
			break;
		case 'news':
			$url = "zenpage=articles";
			break;
	}
	if($pagestotal > 1) {
		echo "<ul style='display: inline; margin-left: -45px;'>";
		if($currentpage != 1) {
			echo "<li class=\"first\" style='display: inline; margin-left: 5px;'><a href='tinyzenpage.php?".$url."&amp;page=1'>&laquo; first</a></li>";
		} else {
			echo "<li class=\"first\" style='display: inline; margin-left: 5px; color: gray'>&laquo; first</li>";
		}
		if($currentpage != 1) {
			echo "<li class=\"prev\" style='display: inline; margin-left: 5px;'><a href='tinyzenpage.php?".$url."&amp;page=".($currentpage-1)."'>&laquo; prev</a></li>";
		} else {
			echo "<li class=\"prev\" style='display: inline; margin-left: 5px; color: gray'>&laquo; prev</li>";
		}
		$j=max(1, min($currentpage-3, $pagestotal-6));
		if ($j != 1) {
			echo "\n <li style='display: inline; margin-left: 5px;'>";
			echo "<a href=\"tinyzenpage.php?".$url."&amp;page=".max($j-4,1)."\">...</a>";
			echo '</li>';
		}
		for ($i=$j; $i <= min($pagestotal, $j+6); $i++) {
			if($i == $currentpage) {
				echo "<li style='display: inline; margin-left: 5px;'>".$i."</li>\n";
			} else {
				echo "<li style='display: inline; margin-left: 5px;'><a href='tinyzenpage.php?".$url."&amp;page=".$i."' title='".gettext("Page")." ".$i."'>".$i."</a></li>\n";
			}
		}
		if ($i <= $pagestotal) {
			echo "\n <li style='display: inline; margin-left: 5px;'>";
			echo "<a href=\"tinyzenpage.php?".$url."&amp;page=".min($j+10,$pagestotal)."\">...</a>";
			echo '</li>';
		}
		if($currentpage != $pagestotal) {
			echo "<li class=\"next\" style='display: inline; margin-left: 5px;'><a href='tinyzenpage.php?".$url."&amp;page=".($currentpage+1)."'>next &raquo;</a></li>";
		} else {
			echo "<li class=\"next\" style='display: inline; margin-left: 5px; color: gray'>next &raquo;</li>";
		}
		if($currentpage != $pagestotal) {
			echo "<li class=\"last\" style='display: inline; margin-left: 5px;'><a href='tinyzenpage.php?".$url."&amp;page=".$pagestotal."'>last &raquo;</a></li>";
		} else {
			echo "<li class=\"last\" style='display: inline; margin-left: 5px; color: gray'>last &raquo;</li>";
		}
		echo "</ul><br />";
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
	echo "<option value='pages'>".gettext("pages")." (".$pagenumber.")</option>";
	echo "<option value='articles'>".gettext("articles")." (".count($_zp_zenpage ->getNewsArticles(0,'all')).")</option>";
	echo "<option value='categories'>".gettext("categories")." (".$catcount.")</option>";
}

 /**
	* Prints all available pages or categories in Zenpage
	*
	* @return string
	*/
function printAllNestedList() {
	global $_zp_zenpage, $host;
	if(isset($_GET['zenpage']) && ($_GET['zenpage'] == "pages" || $_GET['zenpage'] == "categories")) {
		$mode = sanitize($_GET['zenpage']);
		switch($mode) {
			case 'pages':
				$items = $_zp_zenpage->getPages(false);
				$listtitle = gettext('Pages');
				break;
			case 'categories':
				$items = $_zp_zenpage->getAllCategories(false);
				$listtitle = gettext('Categories');
				break;
		}
		echo "<h3 style='margin-bottom:10px;'>Zenpage: <em>".html_encode($listtitle)."</em> <small> ".gettext("(Click on article title to include a link)")."</small></h3>";
		echo "<ul style='list-style: none; margin: 5px 0px 0px -10px;'>";
		$indent = 1;
		$open = array(1=>0);
		$rslt = false;
		foreach ($items as $key=>$item) {
			$itemcss = "padding: 5px 0px 5px 0px;";
			switch($mode) {
				case 'pages':
					$obj = new ZenpagePage($item['titlelink']);
					$itemcontent = truncate_string(strip_tags($obj->getContent()),300);
					$zenpagepage = 'pages/'.$item['titlelink'];
					$unpublished = unpublishedZenpageItemCheck($obj);
					$counter = '';
					break;
				case 'categories':
					$obj = new ZenpageCategory($item['titlelink']);
					$itemcontent = $obj->getTitle();
					$zenpagepage = "news/category/".$item['titlelink'];
					$unpublished = unpublishedZenpageItemCheck($obj);
					$counter = ' ('.count($obj->getArticles()).') ';
					break;
			}
			$itemsortorder = $obj->getSortOrder();
			$itemtitlelink = $obj->getTitlelink();
			$itemtitle = $obj->getTitle();
			$itemid = $obj->getID();
			$order = explode('-', $itemsortorder);
			$level = max(1,count($order));
			if ($toodeep = $level>1 && $order[$level-1] === '') {
				$rslt = true;
			}
			if ($level > $indent) {
				echo "\n".str_pad("\t",$indent,"\t")."<ul style='margin:6px 0px 0px -10px;'>\n";
				$indent++;
				$open[$indent] = 0;
			} else if ($level < $indent) {
				while ($indent > $level) {
					$open[$indent]--;
					$indent--;
					echo "</li>\n".str_pad("\t",$indent,"\t")."</ul>\n";
				}
			} else { // indent == level
				if ($open[$indent]) {
					echo str_pad("\t",$indent,"\t")."</li>\n";
					$open[$indent]--;
				} else {
					echo "\n";
				}
			}
			if ($open[$indent]) {
				echo str_pad("\t",$indent,"\t")."</li>\n";
				$open[$indent]--;
			}
			echo "<li id='".$itemid."' style='list-style: none; padding: 4px 0px 4px 0px;border-top: 1px dotted gray'>";
			echo "<a href=\"javascript:ZenpageDialog.insert('".$zenpagepage."','".$itemtitlelink."','".html_encode($itemtitle)."','','','".$mode."','','','','');\" title='".html_encode($itemcontent)."'>".html_encode($itemtitle).$unpublished.$counter."</a> <small><em>".$obj->getDatetime()."</em></small>";
			if($mode == 'pages') {
				echo " <a href='zoom.php?pages=".urlencode($itemtitlelink)."' title='Zoom' class='colorbox' style='outline: none;'><img src='img/magnify.png' alt='' style='border: 0' /></a>";
			}
			$open[$indent]++;
		}
		while ($indent > 1) {
			echo "</li>\n";
			$open[$indent]--;
			$indent--;
			echo str_pad("\t",$indent,"\t")."</ul>";
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
	switch($class) {
		case 'ZenpageNews':
		case 'ZenpagePage':
			if($page->getShow() === "0") {
				$unpublishednote = "<span style='color: red; font-weight: bold'>*</span>";
			}
			switch($class) {
				case 'ZenpageNews':
					if($page->inProtectedCategory()) {
						$protected = "<span style='color: red; font-weight: bold'>+</span>";
					}
					break;
				case 'ZenpagePage':
					if($page->isProtected()) {
						$protected = "<span style='color: red; font-weight: bold'>+</span>";
					}
					break;
			}
			break;
		case 'ZenpageCategory':
			if($page->isProtected()) {
				$protected = "<span style='color: red; font-weight: bold'>+</span>";
			}
			break;
	}
	return $unpublishednote.$protected;
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
	if(function_exists('bind_textdomain_codeset')) {
		bind_textdomain_codeset($domain, $encoding);
	}
	textdomain($domain);
}

	?>