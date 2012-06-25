<?php
/**
 * zenpage template functions
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 * @subpackage zenpage
 */


/************************************************/
/* ZENPAGE TEMPLATE FUNCTIONS
/************************************************/

/************************************************
 * Global definitions
 */


/************************************************/
/* General functions
/************************************************/

/**
 * Checks if the current page is in news context.
 *
 * @return bool
 */
function is_News() {
	global $_zp_current_zenpage_news;
	return(!is_null($_zp_current_zenpage_news));
}

/**
 * Checks if the current page is the news page in general.
 *
 * @return bool
 */
function is_NewsPage() {
	global $_zp_gallery_page;
	return $_zp_gallery_page == 'news.php';
}

/**
 * Checks if the current page is a single news article page
 *
 * @return bool
 */
function is_NewsArticle() {
	return is_News() && in_context(ZP_ZENPAGE_SINGLE);
}


/**
 * Checks if the current page is a news category page
 *
 * @return bool
 */
function is_NewsCategory() {
	return in_context(ZP_ZENPAGE_NEWS_CATEGORY);
}


/**
 * Checks if the current page is a news archive page
 *
 * @return bool
 */
function is_NewsArchive() {
	return in_context(ZP_ZENPAGE_NEWS_DATE);
}


/**
 * Checks if the current page is a zenpage page
 *
 * @return bool
 */
function is_Pages() {
	return in_context(ZP_ZENPAGE_PAGE);
}

/**
 * Gets the news type of a news item.
 * "news" for a news article or if using the CombiNews feature
 * "flvmovie" (for flv, fla, mp3, m4a and mp4/m4v), "image", "3gpmovie" or "quicktime"
 *
 * @param obj $newsobj optional news object to check directly outside news context
 * @return string
 */
function getNewsType($newsobj=NULL) {
	global $_zp_current_zenpage_news;
	if(is_null($newsobj)) {
		$ownerclass = strtolower(get_class($_zp_current_zenpage_news));
	} else {
		$ownerclass = strtolower(get_class($newsobj));
	}
	switch($ownerclass) {
		case "video":
			$newstype = "video";
			break;
		case "album":
			$newstype = "album";
			break;
		case "zenpagenews":
			$newstype = "news";
			break;
		default:
			$newstype = 'image';
			break;
	}
 return $newstype;
}

/**
 * returns the "sticky" value of the news article
 * @param obj $newsobj optional news object to check directly outside news context
 * @return bool
 */
function stickyNews($newsobj=NULL) {
	global $_zp_current_zenpage_news;
	if (is_null($newsobj)) {
		$newsobj = $_zp_current_zenpage_news;
	}
	if(is_NewsType('news',$newsobj)) {
		return $newsobj->getSticky();
	}
	return false;
}


/**
 * Checks what type the current news item is (See get NewsType())
 *
 * @param string $type The type to check for
 * 										 "news" for a news article or if using the CombiNews feature
 * 										"flvmovie" (for flv, fla, mp3, m4a and mp4/m4v), "image", "3gpmovie" or "quicktime"
 * @param obj $newsobj optional news object to check directly outside news context
 * @return bool
 */
function is_NewsType($type,$newsobj=NULL) {
	return getNewsType($newsobj) == $type;
}


/**
 * CombiNews feature: A general wrapper function to check if this is a 'normal' news article (type 'news' or one of the zenphoto news types
 *
 * @return bool
 */
function is_GalleryNewsType() {
	return is_NewsType("image") || is_NewsType("video") || is_NewsType("album"); // later to be extended with albums, too
}


/**
 * Wrapper function to get the author of a news article or page: Used by getNewsAuthor() and getPageAuthor().
 *
 * @param bool $fullname False for the user name, true for the full name
 *
 * @return string
 */
function getAuthor($fullname=false) {
	global $_zp_current_zenpage_page, $_zp_current_zenpage_news;
		if(is_Pages()) {
			$obj = $_zp_current_zenpage_page;
		}
		if(is_News()) {
			$obj = 	$_zp_current_zenpage_news;
		}
	if(is_Pages() || is_News()) {
		if($fullname) {
			$admin = Zenphoto_Authority::getAnAdmin(array('`user`=' => $obj->getAuthor(), '`valid`=' => 1));
			if (is_object($admin)) {
				return $admin->getName();
			}
		}
		return $obj->getAuthor();
	}
}

/************************************************/
/* News article functions
/************************************************/

/**
 * Returns the number of news articles.
 *
 * When in search context this is the count of the articles found. Otherwise
 * it is the count of articles that match the criteria.
 *
 * @param bool $total
 * @return int
 */
function getNumNews($total=false) {
	global $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_news_restore, $_zp_zenpage_articles, $_zp_gallery, $_zp_current_search;
	Zenpage::processExpired('news');
	if ($total) {
		return count($_zp_zenpage->getArticles(0));
	} else if (in_context(ZP_SEARCH)) {
		return count($_zp_current_search->getArticles());
	} else if(ZP_COMBINEWS AND !is_NewsCategory() AND !is_NewsArchive()) {
		return count($_zp_zenpage->getCombiNews(0));
	} else {
		return count($_zp_zenpage->getArticles(0));
	}
}

/**
 * Returns the next news item on a page.
 * sets $_zp_current_zenpage_news to the next news item
 * Returns true if there is an new item to be shown
 *
 * NOTE: If you set the sortorder and sortdirection parameters you also have to set the same ones
 * on the next/prevNewsLink/URL functions for the single news article pagination!
 *
 * @param string $sortorder "date" for sorting by date (default)
 * 													"title" for sorting by title
 * 													This parameter is not used for date archives and CombiNews mode.
 * @param string $sortdirection "desc" (default) for descending sort order
 * 													    "asc" for ascending sort order
 * 											        This parameter is not used for date archives and CombiNews mode
 *
 * @return bool
 */
function next_news($sortorder="date", $sortdirection="desc") {
	global $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_news_restore, $_zp_zenpage_articles, $_zp_current_category, $_zp_gallery, $_zp_current_search;
	$_zp_current_zenpage_news_restore = $_zp_current_zenpage_news;
	if (is_null($_zp_zenpage_articles)) {
		if (in_context(ZP_SEARCH)) {
			Zenpage::processExpired('news');
			//note: we do not know how to paginate the search page, so for now we will return all news articles
			$_zp_zenpage_articles = $_zp_current_search->getArticles(ZP_ARTICLES_PER_PAGE,NULL,true,$sortorder, $sortdirection);
		} else if(ZP_COMBINEWS AND !is_NewsCategory() AND !is_NewsArchive()) {
			$_zp_zenpage_articles = $_zp_zenpage->getCombiNews(ZP_ARTICLES_PER_PAGE);
		} else {
			if(in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$_zp_zenpage_articles = $_zp_current_category->getArticles(ZP_ARTICLES_PER_PAGE,NULL,false,$sortorder,$sortdirection);
			} else {
				$_zp_zenpage_articles = $_zp_zenpage->getArticles(ZP_ARTICLES_PER_PAGE,NULL,false,$sortorder,$sortdirection);
			}
		}
		while (!empty($_zp_zenpage_articles)) {
			$news = array_shift($_zp_zenpage_articles);
			if (is_array($news)) {
				add_context(ZP_ZENPAGE_NEWS_ARTICLE);
				if(ZP_COMBINEWS AND array_key_exists("type",$news) AND array_key_exists("albumname",$news)) {
					if($news['type'] == "images") {
						$albumobj = new Album(NULL,$news['albumname']);
						$_zp_current_zenpage_news = newImage($albumobj,$news['titlelink']);
					} else if($news['type'] == "albums") {
						switch(getOption("zenpage_combinews_mode")) {
							case "latestimagesbyalbum-thumbnail":
							case "latestimagesbyalbum-thumbnail-customcrop":
							case "latestimagesbyalbum-sizedimage":
							case "latestimagesbyalbum-sizedimage-maxspace":
							case "latestimagesbyalbum-fullimage":
								$_zp_current_zenpage_news = new Album(NULL,$news['titlelink']);
								$_zp_current_zenpage_news->set('date', $news['date']); // in this mode this stores the date of the images to group not the album (inconvenient workaround...)
								break;
							default:
								$_zp_current_zenpage_news = new Album(NULL,$news['albumname']);
								break;
						}
					} else {
						$_zp_current_zenpage_news = new ZenpageNews($news['titlelink']);
					}
				} else {
					$_zp_current_zenpage_news = new ZenpageNews($news['titlelink']);
				}
				return true;
			} else {
				break;
			}
		}
	} else {
		$_zp_current_zenpage_news = NULL;
		while (!empty($_zp_zenpage_articles)) {
			$news = array_shift($_zp_zenpage_articles);
			if (is_array($news)) {
				add_context(ZP_ZENPAGE_NEWS_ARTICLE);
				if(ZP_COMBINEWS AND array_key_exists("type",$news) AND array_key_exists("albumname",$news)) {
					if($news['type'] == "images") {
						$albumobj = new Album(NULL,$news['albumname']);
						$_zp_current_zenpage_news = newImage($albumobj,$news['titlelink']);
					} else if($news['type'] == "albums") {
						switch(getOption("zenpage_combinews_mode")) {
							case "latestimagesbyalbum-thumbnail":
							case "latestimagesbyalbum-thumbnail-customcrop":
							case "latestimagesbyalbum-sizedimage":
							case "latestimagesbyalbum-sizedimage-maxspace":
							case "latestimagesbyalbum-fullimage":
								$_zp_current_zenpage_news = new Album(NULL,$news['titlelink']);
								$_zp_current_zenpage_news->set('date', $news['date']); // in this mode this stores the date of the images to group not the album (inconvenient workaround...)
								break;
							default:
								$_zp_current_zenpage_news = new Album(NULL,$news['albumname']);
								break;
						}
					} else {
						$_zp_current_zenpage_news = new ZenpageNews($news['titlelink']);
					}
				} else {
					$_zp_current_zenpage_news = new ZenpageNews($news['titlelink']);
				}
				return true;
			} else {
				break;
			}
		}
	}
	$_zp_zenpage_articles = NULL;
	$_zp_current_zenpage_news = $_zp_current_zenpage_news_restore;
	rem_context(ZP_ZENPAGE_NEWS_ARTICLE);
	return false;
}

/**
 * Gets the id of a news article/item
 *
 * @return int
 */
function getNewsID() {
	global $_zp_current_zenpage_news;
	if(!is_null($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getID();
	}
}


/**
 * Gets the news article title
 *
 * @return string
 */
function getNewsTitle() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		if(is_NewsType("album") && (
		getOption("zenpage_combinews_mode") == "latestimagesbyalbum-thumbnail" ||
		getOption("zenpage_combinews_mode") == "latestimagesbyalbum-thumbnail-customcrop" ||
		getOption("zenpage_combinews_mode") == "latestimagesbyalbum-sizedimage" ||
		getOption("zenpage_combinews_mode") == "latestimagesbyalbum-sizedimage-maxspace" ||
		getOption("zenpage_combinews_mode") == "latestimagesbyalbum-fullimage")) {
			if(getOption("zenpage_combinews_sortorder") == 'publishdate') {
				$date = 'IFNULL(publishdate,date)';
				$order = 'publishdate';
			} else {
				$date = 'date';
				$order = $date;
			}
			$result = query_full_array("SELECT filename FROM ".prefix('images')." AS images WHERE ".$date." LIKE '".$_zp_current_zenpage_news->getDateTime()."%' AND albumid = ".$_zp_current_zenpage_news->getID()." ORDER BY ".$order." DESC");
			$imagetitles = "";
			$countresult = count($result);
			$count = "";
			if(ZP_COMBINEWS_IMAGETITLES != 0) {
				foreach($result as $image) {
					$imageobj = newImage($_zp_current_zenpage_news,$image['filename']);
					$imagetitles .= $imageobj->getTitle();
					$count++;
					$append = ', ';
					if($count < $countresult) {
						$imagetitles .= $append;
					}
					if($count >= ZP_COMBINEWS_IMAGETITLES && $count != $countresult) {
						$imagetitles .= ZP_SHORTENINDICATOR;
						break;
					}
				}
			}
			return sprintf(ngettext(ZP_SINGULAR,ZP_PLURAL,$countresult),$countresult,$_zp_current_zenpage_news->getTitle(),$imagetitles);
		} else {
			return $_zp_current_zenpage_news->getTitle();
		}
	}
}


/**
 * prints the news article title
 *
 * @param string $before insert if you want to use for the breadcrumb navigation or in the html title tag
 */
function printNewsTitle($before='') {
	if ($title = getNewsTitle()) {
		echo html_encodeTagged($before).html_encode($title);
	}
}

/**
 * Returns the raw title of a news article.
 *
 *
 * @return string
 */
function getBareNewsTitle() {
	return strip_tags(getNewsTitle());
}


/**
 * Returns the titlelink (url name) of the current news article.
 *
 * If using the CombiNews feature this also returns the full path to a image.php page if the item is an image.
 *
 * @return string
 */
function getNewsTitleLink() {
	global $_zp_current_zenpage_news;
	if(!is_null($_zp_current_zenpage_news)) {
		$type = getNewsType();
		switch($type) {
			case "album":
				$link = getNewsAlbumURL();
				break;
			case "news":
				$link = $_zp_current_zenpage_news->getTitlelink();
				break;
			case "image":
			case "video":
				$link = $_zp_current_zenpage_news->getImageLink();
				break;
		}
		return $link;
	}
}


/**
 * Prints the titlelin of a news article as a full html link
 *
 * @param string $before insert what you want to be show before the titlelink.
 */
function printNewsTitleLink($before='') {
	if (getNewsTitle()) {
		if(is_NewsType("news")) {
			echo "<a href=\"".html_encode(getNewsURL(getNewsTitleLink()))."\" title=\"".getBareNewsTitle()."\">".html_encodeTagged($before).html_encode(getNewsTitle())."</a>";
		} else if (is_GalleryNewsType()) {
			echo "<a href=\"".html_encode(getNewsTitleLink())."\" title=\"".getBareNewsTitle()."\">".html_encodeTagged($before).html_encode(getNewsTitle())."</a>";
		}
	}
}


/**
 * Gets the content of a news article
 *
 * If using the CombiNews feature this returns the description for gallery items (see printNewsContent for more)
 *
 * @param int $shorten The optional length of the content for the news list for example, will override the plugin option setting if set, "" (empty) for full content (not used for image descriptions!)
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 *
 * @return string
 */
function getNewsContent($shorten=false, $shortenindicator=NULL,$readmore=NULL) {
	global $_zp_flash_player, $_zp_current_image, $_zp_gallery, $_zp_current_zenpage_news, $_zp_page;
	if (!$_zp_current_zenpage_news->checkAccess()) {
		return '<p>'.gettext('<em>This entry belongs to a protected album.</em>').'</p>';
	}
	$newstype = getNewsType();
	$excerptbreak = false;
	if(!$shorten && !is_NewsArticle()) {
		$shorten = ZP_SHORTEN_LENGTH;
	}
	$articlecontent = "";
	$size = ZP_CN_IMAGESIZE;
	$width = ZP_CN_THUMBWIDTH;
	$height = ZP_CN_THUMBHEIGHT;
	$cropwidth = ZP_CN_CROPWIDTH;
	$cropheight= ZP_CN_CROPHEIGHT;
	$cropx = ZP_CN_CROPX;
	$cropy = ZP_CN_CROPY;
	$mode = ZP_CN_MODE;
	$headline = '';
	switch($newstype) {
		case 'news':
			$articlecontent = $_zp_current_zenpage_news->getContent();
			if(!is_NewsArticle()) {
				$articlecontent = getContentShorten($articlecontent,$shorten,$shortenindicator,$readmore,getNewsURL($_zp_current_zenpage_news->getTitlelink()));
			}
			break;
		case 'image':
			switch($mode) {
				case 'latestimages-sizedimage':
				case 'latestimages-sizedimage-maxspace':
				case 'latestimages-fullimage':
					if(isImagePhoto($_zp_current_zenpage_news)) {
						$articlecontent = '<a href="'.pathurlencode($_zp_current_zenpage_news->getImageLink()).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'">';
						switch($mode) {
							case 'latestimages-sizedimage':
								$imagesource = $_zp_current_zenpage_news->getSizedImage($size);
								break;
							case 'latestimages-sizedimage-maxspace':
								getMaxSpaceContainer($width, $height, $_zp_current_zenpage_news,true);
								$imagesource = $_zp_current_zenpage_news->getCustomImage(NULL, $width,$height, $width,$height, NULL, NULL,true);
								break;
							case 'latestimages-fullimage':
								$imagesource = $_zp_current_zenpage_news->getFullImage();
								break;
						}
						$articlecontent .= '<img src="'.pathurlencode($imagesource).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" />';
						$articlecontent .= '</a>';
					} else if(isImageVideo($_zp_current_zenpage_news)) {
						$articlecontent .= $_zp_current_zenpage_news->getSizedImage($size);
					} else {
						$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getImageLink()).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'">';
						$articlecontent .= '<img src="'.pathurlencode($_zp_current_zenpage_news->getCustomImage($size, NULL,NULL, NULL, NULL, NULL, NULL,true)).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" />';
						$articlecontent .= '</a>';
					}
					break;
				case 'latestimages-thumbnail':
					$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getImageLink()).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($_zp_current_zenpage_news->getThumb()).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>';
					break;
				case 'latestimages-thumbnail-customcrop':
					if(isImagePhoto($_zp_current_zenpage_news)) {
						$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getImageLink()).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($_zp_current_zenpage_news->getCustomImage(NULL, $width, $height, $cropwidth, $cropheight, $cropx, $cropy)).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>';
					} else {
						$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getImageLink()).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($_zp_current_zenpage_news->getCustomImage(NULL, $width, $height, $cropwidth, $cropheight, $cropx, $cropy,true)).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>';
					}
					break;
			}
			$articlecontent .= getContentShorten($_zp_current_zenpage_news->getDesc(),$shorten,$shortenindicator,$readmore,$_zp_current_zenpage_news->getImageLink());
			break;
		case 'video':
			$articlecontent = getNewsVideoContent($_zp_current_zenpage_news,$shorten);
			break;
		case 'album':
			$albumdesc = getContentShorten($_zp_current_zenpage_news->getDesc(),$shorten,$shortenindicator,$readmore,$_zp_current_zenpage_news->getAlbumLink(1));
			$albumthumbobj = $_zp_current_zenpage_news->getAlbumThumbImage();
			switch($mode) {
				case 'latestalbums-sizedimage':
				case 'latestalbums-sizedimage-maxspacce':
				case 'latestalbums-fullimage':
					if(isImagePhoto($albumthumbobj)) {
						switch($mode) {
							case 'latestalbums-sizedimage':
								$imgurl = $albumthumbobj->getSizedImage($size);
								break;
							case 'latestalbums-sizedimage-maxspace':
								getMaxSpaceContainer($width, $height, $albumthumbobj,true);
								$imgurl = $albumthumbobj->getCustomImage(NULL, $width,$height, $width,$height, NULL, NULL,true);
								break;
							case 'latestalbums-fullimage':
								$imgurl = $albumthumbobj->getFullImage();
								break;
						}
					} else {
						$imgurl = $albumthumbobj->getCustomImage($size, NULL,NULL, NULL, NULL, NULL, NULL,true);
					}
					$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getAlbumLink(1)).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($imgurl).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>'.$albumdesc;
					break;
				case 'latestalbums-thumbnail':
					$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getAlbumLink(1)).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($_zp_current_zenpage_news->getAlbumThumb()).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>'.$albumdesc;
					break;
				case 'latestalbums-thumbnail-customcrop':
					$articlecontent = '<a href="'.html_encode($_zp_current_zenpage_news->getAlbumLink(1)).'" title="'.html_encode($_zp_current_zenpage_news->getTitle()).'"><img src="'.pathurlencode($albumthumbobj->getCustomImage(NULL, $width, $height, $cropwidth, $cropheight, $cropx, $cropy,true)).'" alt="'.html_encode($_zp_current_zenpage_news->getTitle()).'" /></a>'.$albumdesc;
					break;
				case 'latestimagesbyalbum-thumbnail':
				case 'latestimagesbyalbum-thumbnail-customcrop':
				case 'latestimagesbyalbum-sizedimage':
				case 'latestimagesbyalbum-sizedimage-maxspace':
				case 'latestimagesbyalbum-fullimage':
					if(getOption("zenpage_combinews_sortorder") == 'publishdate') {
						$date = 'IFNULL(publishdate,date)';
					} else {
						$date = 'date';
					}
					$numberimages = getOption('combinews-numberimages');
					$limit = '';
					if(!empty($numberimages)) {
						$limit = ' LIMIT '.$numberimages;
					}
					$images = query_full_array("SELECT title, filename FROM ".prefix('images')." AS images WHERE ".$date." LIKE '".$_zp_current_zenpage_news->getDateTime()."%' AND albumid = ".$_zp_current_zenpage_news->getID()." ORDER BY ".$date." DESC".$limit);
					foreach($images as $image) {
						$imageobj = newImage($_zp_current_zenpage_news,$image['filename']);
						if(getOption('combinews-latestimagesbyalbum-imgdesc')) {
							$imagedesc = $imageobj->getDesc();
							$imagedesc = getContentShorten($imagedesc,$shorten,$shortenindicator,$readmore,$imageobj->getImageLink());
						} else {
							$imagedesc = '';
						}
						$articlecontent .= '<div class="latestimagesbyalbum">'; // entry wrapper
						switch($mode) {
							case 'latestimagesbyalbum-thumbnail':
								if(getOption('combinews-latestimagesbyalbum-imgtitle')) $headline = '<h4>'.html_encode($imageobj->getTitle()).'</h4>';
								$articlecontent .= '<a href="'.html_encode($imageobj->getImageLink()).'" title="'.html_encode($imageobj->getTitle()).'"><img src="'.pathurlencode($imageobj->getThumb()).'" alt="'.html_encode($imageobj->getTitle()).'" /></a>'.$headline.$imagedesc;
								break;
							case 'latestimagesbyalbum-thumbnail-customcrop':
								if(getOption('combinews-latestimagesbyalbum-imgtitle')) $headline = '<h4>'.html_encode($imageobj->getTitle()).'</h4>';
								if(isImagePhoto($imageobj)){
									$articlecontent .= '<a href="'.html_encode($imageobj->getImageLink()).'" title="'.html_encode($imageobj->getTitle()).'"><img src="'.pathurlencode($imageobj->getCustomImage(NULL, $width, $height, $cropwidth, $cropheight, $cropx, $cropy)).'" alt="'.html_encode($imageobj->getTitle()).'" /></a>'.$headline.$imagedesc;
								} else if(isImageVideo($imageobj)) {
									$articlecontent .= getNewsVideoContent($imageobj).$imagedesc;
								} else  {
									$articlecontent .= '<a href="'.html_encode($imageobj->getImageLink()).'" title="'.html_encode($imageobj->getTitle()).'"><img src="'.pathurlencode($imageobj->getCustomImage(NULL, $width, $height, $cropwidth, $cropheight, $cropx, $cropy,true)).'" alt="'.html_encode($imageobj->getTitle()).'" /></a>'.$headline.$imagedesc;
								}
								break;
							case 'latestimagesbyalbum-sizedimage':
							case 'latestimagesbyalbum-sizedimage-maxspace':
							case 'latestimagesbyalbum-fullimage':
								if(getOption('combinews-latestimagesbyalbum-imgtitle')) $headline = '<h4>'.html_encode($imageobj->getTitle()).'</h4>';
								if(isImagePhoto($imageobj)) {
									switch($mode) {
										case 'latestimagesbyalbum-sizedimage':
											$imagesource = pathurlencode($imageobj->getSizedImage($size));
											break;
										case 'latestimagesbyalbum-sizedimage-maxspace':
											getMaxSpaceContainer($width, $height,$imageobj,true);
											$imagesource = pathurlencode($imageobj->getCustomImage(NULL, $width,$height, $width,$height, NULL, NULL,true));
											break;
										case 'latestimagesbyalbum-fullimage':
											$imagesource = pathurlencode($imageobj->getFullImage());
											break;
									}
									$articlecontent .= '<a href="'.html_encode($imageobj->getImageLink()).'" title="'.html_encode($imageobj->getTitle()).'"><img src="'.$imagesource.'" alt="'.html_encode($imageobj->getTitle()).'" /></a>'.$headline.$imagedesc;
								} else if(isImageVideo($imageobj)) {
									$articlecontent .= getNewsVideoContent($imageobj).$headline.$imagedesc;
								} else {
									$articlecontent .= '<a href="'.html_encode($imageobj->getImageLink()).'" title="'.html_encode($imageobj->getTitle()).'"><img src="'.pathurlencode($imageobj->getCustomImage($size, NULL,NULL, NULL, NULL, NULL, NULL,true)).'" alt="'.html_encode($imageobj->getTitle()).'" /></a>'.$headline.$imagedesc;
								}
								break;
						} // switch "latest images by album end"
						$articlecontent .= '</div>'; // entry wrapper end
					} // foreach end
					break;
			} // switch "albums mode end"
	} // main switch end
	return $articlecontent;
}



/**
 * Prints the news article content. Note: TinyMCE used by Zenpage for news articles may already add a surrounding <p></p> to the content.
 *
 * If using the CombiNews feature this prints the thumbnail or sized image for a gallery item.
 * If using the 'CombiNews sized image' mode it shows movies directly and the description below.
 *
 * @param int $shorten $shorten The lengths of the content for the news main page for example (only for video/audio descriptions, not for normal image descriptions)
	* @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 */
function printNewsContent($shorten=false,$shortenindicator=NULL,$readmore=NULL) {
	global $_zp_current_zenpage_news, $_zp_page;
	$newstype = getNewsType();
	$newscontent = getNewsContent($shorten,$shortenindicator,$readmore);
	echo html_encodeTagged($newscontent);
}

/**
 * Shorten the content of any type of item and add the shorten indicator and readmore link
 * set on the Zenpage plugin options. Helper function for getNewsContent() but usage of course not limited to that.
 * If there is nothing to shorten the content passed.
 *
 * The read more link is wrapped within <p class="readmorelink"></p>.
 *
 * @param string $text The text content to be shortenend.
 * @param integer $shorten The lenght the content should be shortened
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 * @param string $readmoreurl The url the read more link should point to
 */
function getContentShorten($text,$shorten,$shortenindicator=NULL,$readmore=NULL,$readmoreurl=NULL) {
	$readmorelink = '';
	if(is_null($shortenindicator)) {
		$shortenindicator = ZP_SHORTENINDICATOR;
	}
	if(is_null($readmore)) {
		$readmore = get_language_string(ZP_READ_MORE);
	}
	if(!is_null($readmoreurl)) {
		$readmorelink ='<p class="readmorelink"><a href="'.html_encode($readmoreurl).'" title="'.html_encode($readmore).'">'.html_encode($readmore).'</a></p>';
	}
	if(!$shorten && !is_NewsArticle()) {
		$shorten  = ZP_SHORTEN_LENGTH;
	}
	$contentlenght = strlen($text);
	if (!empty($shorten) && ($contentlenght > $shorten)) {
		if(stristr($text,'<!-- pagebreak -->')) {
			$array = explode('<!-- pagebreak -->',$text);
			$newtext = array_shift($array);
			while (!empty($array) && (strlen($newtext)+strlen($array[0])) < $shorten) {	//	find the last break within shorten
				$newtext .= array_shift($array);
			}
			if ($shortenindicator && empty($array) || ($array[0] == '</p>' || trim($array[0]) =='')) {	//	page break was at end of article
				$text = shortenContent($newtext, $shorten, '').$readmorelink;
			} else {
				$text = shortenContent($newtext, $shorten, $shortenindicator, true).$readmorelink;
			}
		} else {
			$text = shortenContent($text,$shorten,$shortenindicator).$readmorelink;
		}
	}
	return $text;
}

/**
 * Helper function for getNewsContent to get video/audio content if $imageobj is a video/audio object if using Zenpage CombiNews
 *
 * @param object $imageobj The object of an image
 */
function getNewsVideoContent($imageobj) {
	global $_zp_flash_player, $_zp_current_image, $_zp_gallery, $_zp_page;
	$videocontent = "";
	$ext = strtolower(strrchr($imageobj->getFullImage(), "."));
	switch($ext) {
		case '.flv':
		case '.mp3':
		case '.mp4':
		case '.fla':
		case '.m4a':
		case '.m4v':
			if(is_null($_zp_flash_player)) {
				$videocontent = '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/err-noflashplayer.png" alt="'.gettext('No flash player installed.').'" />';
			} else {
				$_zp_current_image = $imageobj;
				$videocontent = $_zp_flash_player->getPlayerConfig(getFullNewsImageURL(),getNewsTitle(),$_zp_current_image->get("id"));
			}
			break;
		case '.3gp':
		case '.mov':
			$videocontent = $imageobj->getBody();
			break;
	}
	return $videocontent;
}

/**
 * Gets the extracontent of a news article if in single news articles view or returns FALSE
 *
 * @return string
 */
function getNewsExtraContent() {
	global $_zp_current_zenpage_news;
	if(is_News()) {
		$extracontent = $_zp_current_zenpage_news->getExtraContent();
		return $extracontent;
	} else {
		return FALSE;
	}
}


/**
 * Prints the extracontent of a news article if in single news articles view
 *
 * @return string
 */
function printNewsExtraContent() {
	echo getNewsExtraContent();
}

/**
 * Returns the text for the read more link for news articles or gallery items if in CombiNews mode
 *
 * @return string
 */
function getNewsReadMore() {
	global $_zp_current_zenpage_news;
	$type = getNewsType();
	switch($type) {
		case "news":
			$readmore = get_language_string(ZP_READ_MORE);
			break;
		case "image":
		case "video":
		case "album":
			$readmore = get_language_string(getOption("zenpage_combinews_readmore"));
			break;
	}
	return $readmore;
}

/**
 * Gets the custom data field of the curent news article
 *
 * @return string
 */
function getNewsCustomData() {
	global $_zp_current_zenpage_news;
	if(!is_null($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getCustomData();
	}
}
/**
 * Prints the custom data field of the curent news article
 *
 */
function printNewsCustomData() {
	echo getNewsCustomData();
}

/**
 * Gets the author of a news article (if in Combinews mode for gallery items the owner)
 *
 * @return string
 */
function getNewsAuthor($fullname=false) {
	global $_zp_current_zenpage_news,$_zp_authority;
	if(is_News()) {
		if(is_NewsType("news")) {
			return getAuthor($fullname);
		} else {
			$authorname = '';
			$authorid = $_zp_current_zenpage_news->getOwner();
			if($fullname) {
				$admins = $_zp_authority->getAdministrators();
				foreach($admins as $admin) {
					if($admin['user'] == $authorid) {
						$authorname = $admin['name'];
						break;
					}
				}
				if(empty($authorname)) {
					return $authorid;
				} else {
					return $authorname;
				}
			} else {
				return $authorid;
			}
		}
	}
}


/**
 * Prints the author of a news article
 *
 * @return string
 */
function printNewsAuthor($fullname=false) {
	if (getNewsTitle()) {
		echo html_encode(getNewsAuthor($fullname));
	}
}


/**
 * CombiNews feature only: returns the album title if image or movie/audio or false.
 *
 * @return mixed
 */
function getNewsAlbumTitle() {
	global $_zp_current_zenpage_news;
	if(is_GalleryNewsType()) {
		if(!is_NewsType("album")) {
			$albumobj = $_zp_current_zenpage_news->getAlbum();
			return $albumobj->getTitle();
		} else {
			return $_zp_current_zenpage_news->getTitle();
		}
	} else {
		return false;
	}
}

/**
 * CombiNews feature only: returns the raw title of an album if image or movie/audio or false.
 *
 * @return string
 */
function getBareNewsAlbumTitle() {
	return strip_tags(getNewsAlbumTitle());
}

/**
 * CombiNews feature only: returns the album name (folder) if image or movie/audio or returns false.
 *
 * @return mixed
 */
function getNewsAlbumName() {
	global $_zp_current_zenpage_news;
	if(is_GalleryNewsType()) {
		if(!is_NewsType("album")) {
			$albumobj = $_zp_current_zenpage_news->getAlbum();
			return $albumobj->getFolder();
		} else {
			return $_zp_current_zenpage_news->getFolder();
		}
	} else {
		return false;
	}
}


/**
 * CombiNews feature only: returns the url to an album if image or movie/audio or returns false.
 *
 * @return mixed
 */
function getNewsAlbumURL() {
	if(getNewsAlbumName()) {
		return rewrite_path("/".html_encode(getNewsAlbumName()),"index.php?album=".html_encode(getNewsAlbumName()));
	} else {
		return false;
	}
}

/**
 * CombiNews feature only: Returns the fullimage link if image or movie/audio or false.
 *
 * @return mixed
 */
function getFullNewsImageURL() {
	global $_zp_current_zenpage_news;
	if(is_NewsType('image') || is_NewsType('video')) {
		return $_zp_current_zenpage_news->getFullImage();
	} else {
		return false;
	}
}

/**
 * Gets the categories of the current news article
 *
 * @return array
 */
function getNewsCategories() {
	global $_zp_current_zenpage_news;
	if(!is_null($_zp_current_zenpage_news) AND is_NewsType("news")) {
		$categories = $_zp_current_zenpage_news->getCategories();
		return $categories;
	}
	return false;
}

/**
 * Prints the title of the currently selected news category
 *
 * @param string $before insert what you want to be show before it
 */
function printCurrentNewsCategory($before='') {
	global $_zp_current_category;
	if(in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		echo html_encode($before.$_zp_current_category->getTitle());
	}
}

/**
 * Gets the description of the current news category
 *
 * @return string
 */
function getNewsCategoryDesc() {
	global $_zp_current_category;
	if(!is_null($_zp_current_category)) {
		return $_zp_current_category->getDesc();
	}
}

/**
 * Prints the description of the news category
 *
 */
function printNewsCategoryDesc() {
	echo html_encodeTagged(getNewsCategoryDesc());
}

/**
 * Gets the custom data field of the current news category
 *
 * @return string
 */
function getNewsCategoryCustomData() {
	global $_zp_current_category;
	if(!is_null($_zp_current_category)) {
		return $_zp_current_category->getCustomData();
	}
}
/**
 * Prints the custom data field of the news category
 *
 */
function printNewsCategoryCustomData() {
	echo getNewsCategoryCustomData();
}

/**
 * Prints the categories of current article as a unordered html list
 *
 * @param string $separator A separator to be shown between the category names if you choose to style the list inline
 * @param string $class The CSS class for styling
 * @return string
 */
function printNewsCategories($separator='',$before='',$class='') {
	$categories = getNewsCategories();
	$catcount = count($categories);
	if($catcount != 0) {
		if(is_NewsType("news")) {
			echo  html_encodeTagged($before)."<ul class=\"$class\">\n";
			$count = 0;
			foreach($categories as $cat) {
				$count++;
				$catobj = new ZenpageCategory($cat['titlelink']);
				if($count >= $catcount) {
					$separator = "";
				}
				echo "<li><a href=\"".getNewsCategoryURL($catobj->getTitlelink())."\" title=\"".html_encode($catobj->getTitle())."\">".$catobj->getTitle().'</a>'.html_encodeTagged($separator)."</li>\n";
			}
			echo "</ul>\n";
		}
	}
}

/**
 * Gets the date of the current news article
 *
 * @return string
 */
function getNewsDate() {
	global $_zp_current_zenpage_news;
	if(!is_null($_zp_current_zenpage_news)) {
		if(is_GalleryNewsType() && ZP_COMBINEWS_SORTORDER == 'mtime') {
			$d = $_zp_current_zenpage_news->get('mtime');
			$d = date('Y-m-d H:i:s',$d);
		} else {
			$d = $_zp_current_zenpage_news->getDateTime();
		}
		return zpFormattedDate(DATE_FORMAT, strtotime($d));
	}
	return false;
}


/**
 * Prints the date of the current news article
 *
 * @return string
 */
function printNewsDate() {
	echo html_encode(getNewsDate());
}


/**
 * Prints the monthy news archives sorted by year
 * NOTE: This does only include news articles.
 *
 * @param string $class optional class
 * @param string $yearclass optional class for "year"
 * @param string $monthclass optional class for "month"
 * @param string $activeclass optional class for the currently active archive
 * @param bool $yearsonly If set to true the archive only shows the years with total count (Default false)
 * @param string $order 'desc' (default) or 'asc' for descending or ascending
 */
function printNewsArchive($class='archive', $yearclass='year', $monthclass='month', $activeclass="archive-active",$yearsonly=false,$order='desc') {
	global $_zp_zenpage;
	if (!empty($class)){ $class = "class=\"$class\""; }
	if (!empty($yearclass)){ $yearclass = "class=\"$yearclass\""; }
	if (!empty($monthclass)){ $monthclass = "class=\"$monthclass\""; }
	if (!empty($activeclass)){ $activeclass = "class=\"$activeclass\""; }
	$datecount = $_zp_zenpage->getAllArticleDates($yearsonly,$order);
	$lastyear = "";
	$nr = "";
	echo "\n<ul $class>\n";
	while (list($key, $val) = each($datecount)) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			$dt = strftime('%Y-%B', strtotime($key));
			$year = substr($dt, 0, 4);
			$month = substr($dt, 5);
		}
		if ($lastyear != $year) {
			$lastyear = $year;
			if(!$yearsonly) {
				if($nr != 1) { echo "</ul>\n</li>\n";}
				echo "<li $yearclass>$year\n<ul $monthclass>\n";
			}
		}
		if($yearsonly) {
			$datetosearch = $key;
		} else {
			$datetosearch = strftime('%Y-%B', strtotime($key));
		}
		if(getCurrentNewsArchive('plain') == $datetosearch) {
			$active = $activeclass;
		} else {
			$active = "";
		}
		if($yearsonly) {
			echo "<li $active><a href=\"".html_encode(getNewsBaseURL().getNewsArchivePath()).$key."\" title=\"".$key." (".$val.")\" rel=\"nofollow\">$key ($val)</a></li>\n";
		} else {
			echo "<li $active><a href=\"".html_encode(getNewsBaseURL().getNewsArchivePath()).substr($key,0,7)."\" title=\"".$month." (".$val.")\" rel=\"nofollow\">$month ($val)</a></li>\n";
		}
	}
	if($yearsonly) {
		echo "</ul>\n";
	} else {
		echo "</ul>\n</li>\n</ul>\n";
	}
}

/**
 * Gets the current select news date (year-month) or formatted
 *
 * @param string $mode "formatted" for a formatted date or "plain" for the pure year-month (for example "2008-09") archive date
 * @param string $format If $mode="formatted" how the date should be printed (see PHP's strftime() function for the requirements)
 * @return string
 */
function getCurrentNewsArchive($mode='formatted',$format='%B %Y') {
	global $_zp_post_date;
	if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
		$archivedate = $_zp_post_date;
		if($mode == "formatted") {
		 $archivedate = strtotime($archivedate);
		 $archivedate = strftime($format,$archivedate);
		}
		return $archivedate;
	}
	return false;
}


/**
 * Prints the current select news date (year-month) or formatted
 *
 * @param string $before What you want to print before the archive if using in a breadcrumb navigation for example
 * @param string $mode "formatted" for a formatted date or "plain" for the pure year-month (for example "2008-09") archive date
 * @param string $format If $mode="formatted" how the date should be printed (see PHP's strftime() function for the requirements)
 * @return string
 */
function printCurrentNewsArchive($before='',$mode='formatted',$format='%B %Y') {
	if($date = getCurrentNewsArchive($mode,$format)) {
		echo html_encodeTagged($before).html_encode($date);
	}
}


/**
 * Prints all news categories as a unordered html list
 *
 * @param string $newsindex How you want to call the link the main news page without a category, leave empty if you don't want to print it at all.
 * @param bool $counter TRUE or FALSE (default TRUE). If you want to show the number of articles behind the category name within brackets,
 * @param string $css_id The CSS id for the list
 * @param string $css_class_active The css class for the active menu item
 * @param bool $startlist set to true to output the UL tab
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param string $css_class CSS class of the sub level list(s)
 * @param string $$css_class_active CSS class of the sub level list(s)
 * @param string $option The mode for the menu:
 * 												"list" context sensitive toplevel plus sublevel pages,
 * 												"list-top" only top level pages,
 * 												"omit-top" only sub level pages
 * 												"list-sub" lists only the current pages direct offspring
 * @param int $limit truncation of display text
 * @return string
 */
function printAllNewsCategories($newsindex='All news', $counter=TRUE, $css_id='',$css_class_topactive='',$startlist=true,$css_class='',$css_class_active='',$option='list',$showsubs=false,$limit=NULL) {
	printNestedMenu($option,'allcategories',$counter, $css_id,$css_class_topactive,$css_class,$css_class_active,$newsindex,$showsubs,$startlist,$limit);
}

/**
 * Gets the latest news either only news articles or with the latest images or albums
 *
 * NOTE: This function excludes articles that are password protected via a category for not logged in users!
 *
 * @param int $number The number of news items to get
 * @param string $option "none" for only news articles
 * 											 "with_latest_images" for news articles with the latest images by id
 * 											 "with_latest_images_date" for news articles with the latest images by date
 * 											 "with_latest_images_mtime" for news articles with the latest images by mtime (upload date)
 * 											 "with_latest_images_publishdate" for news articles with the latest images by publishdate (if not set date is used)
 * 											 "with_latest_albums" for news articles with the latest albums by id
 * 											 "with_latest_albums_date" for news articles with the latest albums by date
 * 											 "with_latest_albums_mtime" for news articles with the latest albums by mtime (upload date)
 * 										 	 "with_latest_albums_publishdate" for news articles with the latest albums by publishdate (if not set date is used)
 * 											 "with_latestupdated_albums" for news articles with the latest updated albums
 * @param string $category Optional news articles by category (only "none" option)
 * @param bool $sticky place sticky articles at the front of the list
 * @return array
 */
function getLatestNews($number=2,$option='none', $category='', $sticky=true) {
	global $_zp_zenpage,$_zp_current_zenpage_news;
	$latest = '';
	switch($option) {
		case 'none':
			if(!empty($category)) {
				$catobj = new ZenpageCategory($category);
				$latest = $catobj->getArticles($number,NULL,true,'date','desc',$sticky);
			} else {
				$latest = $_zp_zenpage->getArticles($number,NULL,true,'date','desc',$sticky);
			}
			$counter = '';
			$latestnews = array();
			foreach($latest as $item) {
				$article = new ZenpageNews($item['titlelink']);
				if ($article->checkAccess($hint, $show)) {
						$counter++;
						$latestnews[$counter] = array(
						"albumname" => $article->getTitle(),
						"titlelink" => $article->getTitlelink(),
						"date" => $article->getDateTime(),
						"type" => "news"
					);
				}
				$latest = $latestnews;
			}
			break;
		case 'with_latest_images':
			$latest = $_zp_zenpage->getCombiNews($number,'latestimages-thumbnail',NULL,'id',$sticky);
			break;
		case 'with_latest_images_date':
			$latest = $_zp_zenpage->getCombiNews($number,'latestimages-thumbnail',NULL,'date',$sticky);
			break;
		case 'with_latest_images_mtime':
			$latest = $_zp_zenpage->getCombiNews($number,'latestimages-thumbnail',NULL,'mtime',$sticky);
			break;
		case 'with_latest_images_publishdate':
			$latest = $_zp_zenpage->getCombiNews($number,'latestimages-thumbnail',NULL,'publishdate',$sticky);
			break;
		case 'with_latest_albums':
			$latest = $_zp_zenpage->getCombiNews($number,'latestalbums-thumbnail',NULL,'id',$sticky);
			break;
		case 'with_latest_albums_date':
			$latest = $_zp_zenpage->getCombiNews($number,'latestalbums-thumbnail',NULL,'date',$sticky);
			break;
		case 'with_latest_albums_mtime':
			$latest = $_zp_zenpage->getCombiNews($number,'latestalbums-thumbnail',NULL,'mtime',$sticky);
			break;
		case 'with_latest_albums_publishdate':
			$latest = $_zp_zenpage->getCombiNews($number,'latestalbums-thumbnail',NULL,'publishdate',$sticky);
			break;
		case 'with_latestupdated_albums':
			$latest = $_zp_zenpage->getCombiNews($number,'latestupdatedalbums-thumbnail',NULL,'',$sticky);
			break;
			/*case "latestimagesbyalbum-thumbnail":
			 $latest = $_zp_zenpage->getCombiNews($number,'latestalbums-thumbnail',NULL,'id','',false);
			break; */
	}
	return $latest;
}


/**
 * Prints the latest news either only news articles or with the latest images or albums as a unordered html list
 *
 * NOTE: Latest images and albums require the image_album_statistic plugin
 *
 * @param int $number The number of news items to get
 * @param string $option "none" for only news articles
 * 											 "with_latest_images" for news articles with the latest images by id
 * 											 "with_latest_images_date" for news articles with the latest images by date
 * 											 "with_latest_images_mtime" for news articles with the latest images by mtime (upload date)
 * 											 "with_latest_images_publishdate" for news articles with the latest images by publishdate (if not set date is used)
 * 											 "with_latest_albums" for news articles with the latest albums by id
 * 											 "with_latest_albums_date" for news articles with the latest albums by date
 * 											 "with_latest_albums_mtime" for news articles with the latest albums by mtime (upload date)
 * 										 	 "with_latest_albums_publishdate" for news articles with the latest albums by publishdate (if not set date is used)
 * 											 "with_latestupdated_albums" for news articles with the latest updated albums
 * @param string $category Optional news articles by category (only "none" option"
 * @param bool $showdate If the date should be shown
 * @param bool $showcontent If the content should be shown
 * @param int $contentlength The lengths of the content
 * @param bool $showcat If the categories should be shown
 * @param string $readmore Text for the read more link, if empty the option value for "zenpage_readmore" is used
 * @param bool $sticky place sticky articles at the front of the list
 * @return string
 */
function printLatestNews($number=5,$option='with_latest_images', $category='', $showdate=true, $showcontent=true, $contentlength=70, $showcat=true,$readmore=NULL,$sticky=true){
	global $_zp_gallery, $_zp_current_zenpage_news;
	$latest = getLatestNews($number,$option,$category,$sticky);
	echo "\n<ul id=\"latestnews\">\n";
	$count = "";
	foreach($latest as $item) {
		$count++;
		$category = "";
		$categories = "";
		switch($item['type']) {
			case 'news':
				$obj = new ZenpageNews($item['titlelink']);
				$title = html_encode($obj->getTitle());
				$link = html_encode(getNewsURL($item['titlelink']));
				$count2 = 0;
				$category = $obj->getCategories();
				foreach($category as $cat){
					$catobj = new ZenpageCategory($cat['titlelink']);
					$count2++;
					if($count2 != 1) {
						$categories = $categories.", ";
					}
					$categories = $categories.$catobj->getTitle();
				}
				$thumb = "";
				$content = $obj->getContent();
				$date = zpFormattedDate(DATE_FORMAT,strtotime($item['date']));
				$type = 'news';
				break;
			case 'images':
				$obj = newImage(new Album($_zp_gallery,$item['albumname']),$item['titlelink']);
				$categories = $item['albumname'];
				$title = $obj->getTitle();
				$link = html_encode($obj->getImageLink());
				$content = $obj->getDesc();
				if($option == "with_latest_image_date") {
					$date = zpFormattedDate(DATE_FORMAT,$item['date']);
				} else {
					$date = zpFormattedDate(DATE_FORMAT,strtotime($item['date']));
				}
				$thumb = "<a href=\"".$link."\" title=\"".html_encode(strip_tags($title))."\"><img src=\"".pathurlencode($obj->getThumb())."\" alt=\"".html_encode(strip_tags($title))."\" /></a>\n";
				$type = "image";
				break;
			case 'albums':
				$obj = new Album(NULL,$item['albumname']);
				$title = $obj->getTitle();
				$categories = "";
				$link = html_encode($obj->getAlbumLink());
				$thumb = "<a href=\"".$link."\" title=\"".$title."\"><img src=\"".pathurlencode($obj->getAlbumThumb())."\" alt=\"".strip_tags($title)."\" /></a>\n";
				$content = $obj->getDesc();
				$date = zpFormattedDate(DATE_FORMAT,strtotime($item['date']));
				$type = "album";
				break;
		}
		echo "<li>";
		if(!empty($thumb)) {
			echo $thumb;
		}
		echo "<h3><a href=\"".$link."\" title=\"".strip_tags(html_encode($title))."\">".$title."</a></h3>\n";;
		if($showdate) {
			echo "<span class=\"latestnews-date\">". $date."</span>\n";
		}
		if($showcontent) {
			echo "<span class=\"latestnews-desc\">".getContentShorten($content,$contentlength,'',$readmore,$link)."</span>\n";
		}
		if($showcat AND $type != "album" && !empty($categories)) {
			echo "<span class=\"latestnews-cats\">(".html_encode($categories).")</span>\n";
		}
		echo "</li>\n";
		if($count == $number) {
			break;
		}
	}
	echo "</ul>\n";
}

/************************************************/
/* News article URL functions
/************************************************/

/**
 * Returns the full path to a news category
 *
 * @param string $catlink The category link of a category
 *
 * @return string
 */
function getNewsCategoryURL($catlink='') {
	global $_zp_zenpage, $_zp_current_category;
	if(empty($catlink)) {
		$titlelink = $_zp_current_category->getTitlelink();
	} else {
		$titlelink = $catlink;
	}
	return $_zp_zenpage->getNewsBaseURL().$_zp_zenpage->getNewsCategoryPath().urlencode($titlelink);
}


/**
 * Prints the full link to a news category
 *
 * @param string $before If you want to print text before the link
 * @param string $catlink The category link of a category
	*
 * @return string
 */
function printNewsCategoryURL($before='',$catlink='') {
	$catobj = new ZenpageCategory($catlink);
	echo "<a href=\"".html_encode(getNewsCategoryURL($catlink))."\" title=\"".html_encode($catobj->getTitle())."\">".html_encodeTagged($before).html_encode($catobj->getTitle())."</a>";
}


/**
 * Returns the full path of the news index page (news page 1) or if the "news on zp index" option is set a link to the gallery index.
 *
 * @return string
 */
function getNewsIndexURL() {
	global $_zp_zenpage;
	if($_zp_zenpage->news_on_index) {
		return getGalleryIndexURL(false);
	} else {
		return $_zp_zenpage->getNewsIndexURL();
	}
}


/**
 * Prints the full link of the news index page (news page 1)
 *
 * @param string $name The linktext
 * @param string $before The text to appear before the link text
 * @return string
 */
function printNewsIndexURL($name='', $before='') {
	echo html_encodeTagged($before)."<a href=\"".html_encode(getNewsIndexURL())."\" title=\"".strip_tags(html_encode($name))."\">".html_encode($name)."</a>";
}


/**
 * Returns the base /news or index.php?p=news url
 *
 * @return string
 */
function getNewsBaseURL() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsBaseURL();
}


/**
 * Returns partial path of news category
 *
 * @return string
 */
function getNewsCategoryPath() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsCategoryPath();
}

/**
 * Returns partial path of news date archive
 *
 * @return string
 */
function getNewsArchivePath() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsArchivePath();
}


/**
 * Returns partial path of news article title
 *
 * @return string
 */
function getNewsTitlePath() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsTitlePath();
}


/**
 * Returns partial path of a news page number path
 *
 * @return string
 */
function getNewsPagePath() {
	global $_zp_zenpage;
	return $_zp_zenpage->getNewsPagePath();
}


/**
 * Returns the url to a news article
 *
 * @param string $titlelink The titlelink of a news article
 *
 * @return string
 */
function getNewsURL($titlelink='') {
	global $_zp_current_zenpage_news;
	if(empty($titlelink)) {
		return $_zp_current_zenpage_news->getNewsLink();
	} else {
		return getNewsBaseURL().getNewsTitlePath().urlencode($titlelink);
	}
}


/**
 * Prints the url to a news article
 *
 * @param string $titlelink The titlelink of a news article
 *
 * @return string
 */
function printNewsURL($titlelink='') {
	echo html_encode(getNewsURL($titlelink));
}


/************************************************************/
/* News index / category / date archive pagination functions
 /***********************************************************/


/**
 * News cat path only for use in the news article pagination
 *
 * @return string
 */
function getNewsCategoryPathNav() {
	global $_zp_current_category;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		return getNewsCategoryPath().urlencode($_zp_current_category->getTitlelink());
	}
	return false;
}


/**
 * news archive path only for use in the news article pagination
 *
 * @return string
 */
function getNewsArchivePathNav() {
	global $_zp_post_date;
	if (in_context(ZP_ZENPAGE_NEWS_DATE)) {
		return getNewsArchivePath().$_zp_post_date;
	}
	return false;
}


/**
 * Returns the url to the previous news page
 *
 * @return string
 */
function getPrevNewsPageURL() {
	global $_zp_page;
	if($_zp_page != 1) {
		if(($_zp_page - 1) == 1) {
			if(is_NewsCategory()) {
				return getNewsBaseURL().getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath().($_zp_page - 1);
			} else {
				return getNewsIndexURL();
			}
		} else {
			return getNewsBaseURL().getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath().($_zp_page - 1);
		}
	} else {
		return false;
	}
}


/**
 * Prints the link to the previous news page
 *
 * @param string $prev The linktext
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printPrevNewsPageLink($prev='« prev',$class='disabledlink') {
	global $_zp_zenpage, $_zp_page;
	if(getPrevNewsPageURL()) {
		echo "<a href='".getPrevNewsPageURL()."' title='".gettext("Prev page")." ".($_zp_page - 1)."' >".html_encode($prev)."</a>\n";
	} else {
		echo "<span class=\"$class\">".html_encode($prev)."</span>\n";
	}
}


/**
 * Returns the url to the next news page
 *
 * @return string
 */
function getNextNewsPageURL() {
	global $_zp_zenpage, $_zp_page;
	$total_pages = ceil($_zp_zenpage->getTotalArticles() / ZP_ARTICLES_PER_PAGE);
	if ($_zp_page != $total_pages) {
		return getNewsBaseURL().getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath().($_zp_page + 1);
	} else {
		return false;
	}
}


/**
 * Prints the link to the next news page
 *
 * @param string $next The linktext
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printNextNewsPageLink($next='next »', $class='disabledlink') {
	global $_zp_page;
	if (getNextNewsPageURL())	{
		echo "<a href='".getNextNewsPageURL()."' title='".gettext("Next page")." ".($_zp_page + 1)."'>".html_encode($next)."</a>\n";
	} else {
		echo "<span class=\"$class\">".html_encode($next)."</span>\n";
	}
}

/**
 * Prints the page number list for news page navigation
 *
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printNewsPageList($class='pagelist') {
	printNewsPageListWithNav("", "", false, $class,true);
}


/**
 * Prints the full news page navigation with prev/next links and the page number list
 *
 * @param string $next The next page link text
 * @param string $prev The prev page link text
 * @param bool $nextprev If the prev/next links should be printed
 * @param string $class The CSS class for the disabled link
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 *
 * @return string
 */
function printNewsPageListWithNav($next,$prev,$nextprev=true, $class='pagelist',$firstlast=true, $navlen=9) {
	global $_zp_zenpage, $_zp_page;
	$total = ceil($_zp_zenpage->getTotalArticles() / ZP_ARTICLES_PER_PAGE);
	if ($total > 1) {
		if ($navlen == 0)
			$navlen = $total;
		$extralinks = 2;
		if ($firstlast) $extralinks = $extralinks + 2;
		$len = floor(($navlen-$extralinks) / 2);
		$j = max(round($extralinks/2), min($_zp_page-$len-(2-round($extralinks/2)), $total-$navlen+$extralinks-1));
		$ilim = min($total, max($navlen-round($extralinks/2), $_zp_page+floor($len)));
		$k1 = round(($j-2)/2)+1;
		$k2 = $total-round(($total-$ilim)/2);
		echo "<ul class=\"$class\">\n";
		if ($nextprev) {
			echo "<li class=\"prev\">";
			printPrevNewsPageLink($prev);
			echo "</li>\n";
		}
		if ($firstlast) {
			echo '<li class="'.($_zp_page==1?'current':'first').'">';
			if($_zp_page == 1) {
				echo "1";
			} else {
				if($_zp_zenpage->news_on_index) {
					echo "<a href='".html_encode(getNewsIndexURL())."' title='".gettext("Page")." 1'>1</a>";
				} else {
					echo "<a href='".getNewsBaseURL().html_encode(getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath())."1' title='".gettext("Page")." 1'>1</a>";
				}
			}
			echo "</li>\n";
			if ($j>2) {
				echo "<li>";
				$linktext = ($j-1>2)?'...':$k1;
				echo "<a href=\"".getNewsBaseURL().html_encode(getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath()).$k1."\" title=\"".sprintf(ngettext('Page %u','Page %u',$k1),$k1)."\">".$linktext."</a>";
				echo "</li>\n";
			}
		}
		for ($i=$j; $i <= $ilim; $i++) {
			echo "<li" . (($i == $_zp_page) ? " class=\"current\"" : "") . ">";
			if ($i == $_zp_page) {
				echo $i;
			} else {
				echo "<a href='".getNewsBaseURL().html_encode(getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath()).$i."' title='".sprintf(ngettext('Page %1$u','Page %1$u', $i),$i)."'>".$i."</a>";
			}
			echo "</li>\n";
		}
		if ($i < $total) {
			echo "<li>";
			$linktext = ($total-$i>1)?'...':$k2;
			echo "<a href='".getNewsBaseURL().html_encode(getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath()).$k2."' title='".sprintf(ngettext('Page %u','Page %u',$k2),$k2)."'>".$linktext."</a>";
			echo "</li>\n";
		}
		if ($firstlast && $i <= $total) {
			echo "\n  <li class=\"last\">";
			if($_zp_page == $total) {
				echo $total;
			} else {
				echo "<a href=\"".getNewsBaseURL().html_encode(getNewsCategoryPathNav().getNewsArchivePathNav().getNewsPagePath()).$total."\" title=\"".sprintf(ngettext('Page {%u}','Page {%u}',$total),$total)."\">".$total."</a>";
			}
			echo "</li>\n";
		}
		if ($nextprev) {
			echo "<li class=\"next\">"; printNextNewsPageLink($next); echo "</li>\n";
		}
		echo "</ul>\n";
	}
}


function getTotalNewsPages() {
	global $_zp_zenpage;
	if(ZP_COMBINEWS AND !is_NewsCategory() AND !is_NewsArchive()) {
		$articlecount = $_zp_zenpage->countCombiNews();
	} else {
		$articlecount = count($_zp_zenpage ->getArticles(0,'all'));
	}
}

/************************************************************************/
/* Single news article pagination functions (previous and next article)
/************************************************************************/


/**
 * Returns the title and the titlelink of the next or previous article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $option "prev" or "next"
 * @param string $sortorder "desc" (default)or "asc" for descending or ascending news. Required if these for next_news() loop are changed.
 * @param string $sortdirection "date" (default) or "title" for sorting by date or title. Required if these for next_news() loop are changed.
 *
 * @return mixed
 */
function getNextPrevNews($option='',$sortorder='date',$sortdirection='desc') {
	global $_zp_zenpage,$_zp_current_zenpage_news;
	$article_url = array();
	if(!ZP_COMBINEWS) {
		$current = 0;
		if(!empty($option)) {
			$all_articles = $_zp_zenpage->getArticles(0,NULL,false,$sortorder,$sortdirection);
			$count = 0;
			foreach($all_articles as $article) {
				$newsobj = new ZenpageNews($article['titlelink']);
				$count++;
				$title[$count] = $newsobj->getTitle();
				$titlelink[$count] = $newsobj->getTitlelink();
				if($titlelink[$count] == $_zp_current_zenpage_news->getTitlelink()){
					$current = $count;
				}
			}
			switch($option) {
				case "prev":
					$prev = $current - 1;
					if($prev > 0) {
						$articlelink = getNewsURL($title[$prev]);
						$articletitle = $title[$prev];
						$article_url = array("link" => getNewsURL($titlelink[$prev]), "title" => $title[$prev]);
					}
					break;
				case "next":
					$next = $current + 1;
					if($next <= $count){
						$articlelink = getNewsURL($title[$next]);
						$articletitle = $title[$next];
						$article_url = array("link" => getNewsURL($titlelink[$next]), "title" => $title[$next]);
					}
					break;
			}
			return $article_url;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Returns the title and the titlelink of the next article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 * @param string $sortorder "desc" (default)or "asc" for descending or ascending news. Required if these for next_news() loop are changed.
 * @param string $sortdirection "date" (default) or "title" for sorting by date or titlelink. Required if these for next_news() loop are changed.
 *
 * @return mixed
 */
function getNextNewsURL($sortorder='date',$sortdirection='desc') {
	return getNextPrevNews("next",$sortorder,$sortdirection);
}


/**
 * Returns the title and the titlelink of the previous article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 * @param string $sortorder "desc" (default)or "asc" for descending or ascending news. Required if these for next_news() loop are changed.
 * @param string $sortdirection "date" (default) or "title" for sorting by date or titlelink. Required if these for next_news() loop are changed.
 *
 * @return mixed
 */
function getPrevNewsURL($sortorder='date',$sortdirection='desc') {
	return getNextPrevNews("prev",$sortorder,$sortdirection);
}


/**
 * Prints the link of the next article in single news article pagination if available
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $next If you want to show something with the title of the article like a symbol
 * @param string $sortorder "desc" (default)or "asc" for descending or ascending news. Required if these for next_news() loop are changed.
 * @param string $sortdirection "date" (default) or "title" for sorting by date or titlelink. Required if these for next_news() loop are changed.
 * @return string
 */
function printNextNewsLink($next=" »",$sortorder='date',$sortdirection='desc') {
	$article_url = getNextPrevNews("next",$sortorder,$sortdirection);
	if(array_key_exists('link', $article_url) && $article_url['link'] != "") {
		echo "<a href=\"".html_encode($article_url['link'])."\" title=\"".html_encode(strip_tags($article_url['title']))."\">".$article_url['title']."</a> ".html_encode($next);
	}
}


/**
 * Prints the link of the previous article in single news article pagination if available
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $next If you want to show something with the title of the article like a symbol
 * @param string $sortorder "desc" (default)or "asc" for descending or ascending news. Required if these for next_news() loop are changed.
 * @param string $sortdirection "date" (default) or "title" for sorting by date or titlelink. Required if these for next_news() loop are changed.
 * @return string
 */
function printPrevNewsLink($prev="« ",$sortorder='date',$sortdirection='desc') {
	$article_url = getNextPrevNews("prev",$sortorder,$sortdirection);
	if(array_key_exists('link', $article_url) && $article_url['link'] != "") {
		echo html_encode($prev)." <a href=\"".html_encode($article_url['link'])."\" title=\"".html_encode(strip_tags($article_url['title']))."\">".$article_url['title']."</a>";
	}
}


/**********************************************************/
/* Functions - shared by Pages and News articles
 /**********************************************************/

/**
 * Gets the statistic for pages, news articles or categories as an unordered list
 *
 * @param int $number The number of news items to get
 * @param string $option "all" pages, articles  and categories
 * 											 "news" for news articles
 * 											 "categories" for news categories
 * 											 "pages" for pages
 * @param string $mode "popular" most viewed for pages, news articles and categories
 * 										 "mostrated" for news articles and pages
 * 										 "toprated" for news articles and pages
 * 										 "random" for pages, news articles and categories
 * @return array
 */
function getZenpageStatistic($number=10, $option="all",$mode="popular") {
	global $_zp_current_zenpage_news, $_zp_current_zenpage_pages;
	$statsarticles = array();
	$statscats = array();
	$statspages = array();
	switch($mode) {
		case "popular":
			$sortorder = "hitcounter";
			break;
		case "mostrated":
			$sortorder = "total_votes";
			break;
		case "toprated":
			$sortorder = "rating";
			break;
		case "random":
			$sortorder = "RAND()";
			break;
		}
	if($option == "all" OR $option == "news") {
		$articles = query_full_array("SELECT titlelink FROM " . prefix('news')." ORDER BY $sortorder DESC LIMIT $number");
		$counter = "";
		$statsarticles = array();
		foreach ($articles as $article) {
		$counter++;
			$obj = new ZenpageNews($article['titlelink']);
			$statsarticles[$counter] = array(
					"id" => $obj->getID(),
					"title" => $obj->getTitle(),
					"titlelink" => $article['titlelink'],
					"hitcounter" => $obj->getHitcounter(),
					"total_votes" => $obj->get('total_votes'),
					"rating" => $obj->get('rating'),
					"content" => $obj->getContent(),
					"date" => $obj->getDateTime(),
					"type" => "News"
			);
		}
		$stats = $statsarticles;
	}
	if(($option == "all" OR $option == "categories") && $mode != "mostrated" && $mode != "toprated") {
		$categories = query_full_array("SELECT id, titlelink as title, title as titlelink, hitcounter FROM " . prefix('news_categories')." ORDER BY $sortorder DESC LIMIT $number");
		$counter = "";
		$statscats = array();
		foreach ($categories as $cat) {
		$counter++;
			$statscats[$counter] = array(
					"id" => $cat['id'],
					"title" => html_encode(get_language_string($cat['title'])),
					"titlelink" => getNewsCategoryURL($cat['titlelink']),
					"hitcounter" => $cat['hitcounter'],
					"total_votes" => "",
					"rating" => "",
					"content" => '',
					"date" => '',
					"type" => "Category"
			);
		}
		$stats = $statscats;
	}
	if($option == "all" OR $option == "pages") {
		$pages = query_full_array("SELECT titlelink FROM " . prefix('pages')." ORDER BY $sortorder DESC LIMIT $number");
		$counter = "";
		$statspages = array();
		foreach ($pages as $page) {
			$counter++;
			$pageobj = new ZenpagePage($page['titlelink']);
			$statspages[$counter] = array(
					"id" => $pageobj->getID(),
					"title" => $pageobj->getTitle(),
					"titlelink" => $page['titlelink'],
					"hitcounter" => $pageobj->getHitcounter(),
					"total_votes" => $pageobj->get('total_votes'),
					"rating" => $pageobj->get('rating'),
					"content" => $pageobj->getContent(),
					"date" => $pageobj->getDateTime(),
					"type" => "Page"
			);
		}
		$stats = $statspages;
	}
	if($option == "all") {
		$stats = array_merge($statsarticles,$statscats,$statspages);
	}
	$stats = sortMultiArray($stats,$sortorder,true);
	return $stats;
}

/**
 * Prints the statistics Zenpage items as an unordered list
 *
 * @param int $number The number of news items to get
 * @param string $option "all" pages and articles
 * 											 "news" for news articles
 * 											 "pages" for pages
 * @param string $mode "popular" most viewed for pages, news articles and categories
 * 										 "mostrated" for news articles and pages
 * 										 "toprated" for news articles and pages
 * 										 "random" for pages, news articles and categories
 * @param bool $showstats if the value should be shown
 * @param bool $showtype if the type should be shown
 * @param bool $showdate if the date should be shown (news articles and pages only)
 * @param bool $showcontent if the content should be shown (news articles and pages only)
 * @param bool $contentlength The shortened lenght of the content
 */
function printZenpageStatistic($number=10, $option="all",$mode="popular",$showstats=true,$showtype=true, $showdate=true, $showcontent=true, $contentlength=40) {
	$stats = getZenpageStatistic($number, $option,$mode);
	$contentlength = sanitize_numeric($contentlength);
	switch($mode) {
		case 'popular':
			$cssid = "'zenpagemostpopular'";
			break;
		case 'mostrated':
			$cssid ="'zenpagemostrated'";
			break;
		case 'toprated':
			$cssid ="'zenpagetoprated'";
			break;
		case 'random':
			$cssid ="'zenpagerandom'";
			break;
	}
	echo "<ul id=$cssid>";
	foreach($stats as $item) {
		switch($mode) {
			case 'popular':
				$statsvalue = $item['hitcounter'];
				break;
			case 'mostrated':
				$statsvalue = $item['total_votes'];
				break;
			case 'toprated':
				$statsvalue = $item['rating'];
				break;
		}
		switch($item['type']) {
			case 'Page':
				$titlelink = html_encode(getPageLinkURL($item['titlelink']));
			case 'News':
				$titlelink = html_encode(getNewsURL($item['titlelink']));
				break;
			case 'Category':
				$titlelink = html_encode(getNewsCategoryURL($item['titlelink']));
				break;
		}
		echo '<li><a href="'.$titlelink.'" title="'.html_encode(strip_tags($item['title'])).'"><h3>'.$item['title'];
		echo '<small>';
		if($showtype) {
			echo ' ['.$item['type'].']';
		}
		if($showstats && ($item['type'] != 'Category' && $mode != 'mostrated' && $mode != 'toprated')) {
			echo ' ('.$statsvalue.')';
		}
		echo '</small>';
		echo '</h3></a>';
		if($showdate && $item['type'] != 'Category') {
			echo "<p>". zpFormattedDate(DATE_FORMAT,strtotime($item['date']))."</p>";
		}
		if($showcontent && $item['type'] != 'Category') {
			echo '<p>'.truncate_string($item['content'], $contentlength).'</p>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Prints the most popular pages, news articles and categories as an unordered list
 *
 * @param int $number The number of news items to get
 * @param string $option "all" pages and articles
 * 											 "news" for news articles
 * 											 "pages" for pages
 * @param bool $showstats if the value should be shown
 * @param bool $showtype if the type should be shown
 * @param bool $showdate if the date should be shown (news articles and pages only)
 * @param bool $showcontent if the content should be shown (news articles and pages only)
 * @param bool $contentlength The shortened lenght of the content
 */
function printMostPopularItems($number=10, $option="all",$showstats=true,$showtype=true, $showdate=true, $showcontent=true, $contentlength=40) {
	printZenpageStatistic($number, $option,"popular",$showstats,$showtype, $showdate, $showcontent, $contentlength);
}

/**
 * Prints the most rated pages and news articles as an unordered list
 *
 * @param int $number The number of news items to get
 * @param string $option "all" pages and articles
 * 											 "news" for news articles
 * 											 "pages" for pages
 * @param bool $showstats if the value should be shown
 * @param bool $showtype if the type should be shown
 * @param bool $showdate if the date should be shown (news articles and pages only)
 * @param bool $showcontent if the content should be shown (news articles and pages only)
 * @param bool $contentlength The shortened lenght of the content
 */
function printMostRatedItems($number=10, $option="all",$showstats=true,$showtype=true, $showdate=true, $showcontent=true, $contentlength=40) {
	printZenpageStatistic($number, $option,"mostrated",$showstats,$showtype, $showdate, $showcontent, $contentlength);
}

/**
 * Prints the top rated pages and news articles as an unordered list
 *
 * @param int $number The number of news items to get
 * @param string $option "all" pages and articles
 * 											 "news" for news articles
 * 											 "pages" for pages
 * @param bool $showstats if the value should be shown
 * @param bool $showtype if the type should be shown
 * @param bool $showdate if the date should be shown (news articles and pages only)
 * @param bool $showcontent if the content should be shown (news articles and pages only)
 * @param bool $contentlength The shortened lenght of the content
 */
function printTopRatedItems($number=10, $option="all",$showstats=true,$showtype=true, $showdate=true, $showcontent=true, $contentlength=40) {
	printZenpageStatistic($number, $option,"toprated",$showstats,$showtype, $showdate, $showcontent, $contentlength);
}


/**
 * Prints a context sensitive menu of all pages as a unordered html list
 *
 * @param string $option The mode for the menu:
 * 												"list" context sensitive toplevel plus sublevel pages,
 * 												"list-top" only top level pages,
 * 												"omit-top" only sub level pages
 * 												"list-sub" lists only the current pages direct offspring
 * @param string $mode 'pages' or 'categories'
 * @param bool $counter Only $mode = 'categories': Count the articles in each category
 * @param string $css_id CSS id of the top level list
 * @param string $css_class_topactive class of the active item in the top level list
 * @param string $css_class CSS class of the sub level list(s)
 * @param string $$css_class_active CSS class of the sub level list(s)
 * @param string $indexname insert the name (default "Gallery Index") how you want to call the link to the gallery index, insert "" (default) if you don't use it, it is not printed then.
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param bool $startlist set to true to output the UL tab (false automatically if you use 'omit-top' or 'list-sub')
 * @param int $limit truncation limit display strings
 * @return string
 */
function printNestedMenu($option='list',$mode=NULL,$counter=TRUE, $css_id=NULL,$css_class_topactive=NULL,$css_class=NULL,$css_class_active=NULL,$indexname=NULL,$showsubs=0,$startlist=true, $limit=NULL) {
	global $_zp_zenpage, $_zp_gallery_page, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
	if (is_null($limit)) {
		$limit = MENU_TRUNCATE_STRING;
	}
	if ($css_id != "") { $css_id = " id='".$css_id."'"; }
	if ($css_class_topactive != "") { $css_class_topactive = " class='".$css_class_topactive."'"; }
	if ($css_class != "") { $css_class = " class='".$css_class."'"; }
	if ($css_class_active != "") { $css_class_active = " class='".$css_class_active."'"; }
	if ($showsubs === true) $showsubs = 9999999999;
	switch($mode) {
		case 'pages':
			$items = $_zp_zenpage->getPages();
			$currentitem_id = getPageID();
			if (is_object($_zp_current_zenpage_page)) {
				$currentitem_parentid = $_zp_current_zenpage_page->getParentID();
			} else {
				$currentitem_parentid = NULL;
			}
			$currentitem_sortorder = getPageSortorder();
			break;
		case 'categories':
		case 'allcategories':
			$items = $_zp_zenpage->getAllCategories();
			if (is_object($_zp_current_category)) {
				$currentitem_sortorder = $_zp_current_category->getSortOrder();
				$currentitem_id  = $_zp_current_category->getID();
				$currentitem_parentid = $_zp_current_category->getParentID();
			} else {
				$currentitem_sortorder = NULL;
				$currentitem_id  = NULL;
				$currentitem_parentid = NULL;
			}
			break;
	}
	// don't highlight current pages or foldout if in search mode as next_page() sets page context
	if(in_context(ZP_SEARCH) && $mode == 'pages') { // categories are not searched
		$css_class_topactive != "";
		$css_class_active != "";
		rem_context(ZP_ZENPAGE_PAGE);
	}
	if (count($items)==0) return; // nothing to do
	$startlist = $startlist && !($option == 'omit-top'	|| $option == 'list-sub');
	if ($startlist) echo "<ul$css_id>";
	// if index link and if if with count
	if(!empty($indexname)) {
		if ($limit) {
			$display = shortenContent($indexname, $limit, MENU_TRUNCATE_INDICATOR);
		} else {
			$display = $indexname;
		}
		switch($mode) {
			case 'pages':
				if($_zp_gallery_page == "index.php") {
					echo "<li $css_class_topactive>".html_encode($display)."</li>";
				} else {
					echo "<li><a href='".html_encode(getGalleryIndexURL(true))."' title='".html_encode($indexname)."'>".html_encode($display)."</a></li>";
				}
				break;
			case 'categories':
			case 'allcategories':
				if(($_zp_gallery_page == "news.php" || ($_zp_zenpage->news_on_index && $_zp_gallery_page == "index.php")) && !is_NewsCategory() && !is_NewsArchive() && !is_NewsArticle()) {
					echo "<li $css_class_topactive>".html_encode($display);
				} else {
					echo "<li><a href=\"".html_encode(getNewsIndexURL())."\" title=\"".html_encode($indexname)."\">".html_encode($display)."</a>";
				}
				if($counter) {
					if(ZP_COMBINEWS) {
						$totalcount = $_zp_zenpage->countCombiNews();
					} else {
						if(in_context(ZP_ZENPAGE_NEWS_CATEGORY) && $mode == 'categories') {
							$totalcount = count($_zp_current_category->getArticles(0));
						} else {
							$totalcount = count($_zp_zenpage->getArticles(0));
						}
					}
					echo ' <span style="white-space:nowrap;"><small>('.sprintf(ngettext('%u article','%u articles',$totalcount),$totalcount).')</small></span>';
				}
				echo "</li>\n";
				break;
		}
	}
	$baseindent = max(1,count(explode("-", $currentitem_sortorder)));
	$indent = 1;
	$open = array($indent=>0);
	$parents = array(NULL);
	$order = explode('-', $currentitem_sortorder);
	$mylevel = count($order);
	$myparentsort = array_shift($order);
	for ($c=0; $c<=$mylevel; $c++) {
		$parents[$c] = NULL;
	}
	foreach ($items as $item) {
		switch($mode) {
			case 'pages':
				$catcount = 1;	//	so page items all show.
				$pageobj = new ZenpagePage($item['titlelink']);
				$itemtitle = $pageobj->getTitle();
				$itemsortorder = $pageobj->getSortOrder();
				$itemid = $pageobj->getID();
				$itemparentid = $pageobj->getParentID();
				$itemtitlelink = $pageobj->getTitlelink();
				$itemurl = getPageLinkURL($itemtitlelink);
				$count = '';
			break;
			case 'categories':
			case 'allcategories':
				$catobj = new ZenpageCategory($item['titlelink']);
				$itemtitle = $catobj->getTitle();
				$itemsortorder = $catobj->getSortOrder();
				$itemid = $catobj->getID();
				$itemparentid = $catobj->getParentID();
				$itemtitlelink = $catobj->getTitlelink();
				$itemurl = getNewsCategoryURL($catobj->getTitlelink());
				$catcount = count($catobj->getArticles());
				if($counter) {
					$count = ' <span style="white-space:nowrap;"><small>('.sprintf(ngettext('%u article', '%u articles',$catcount),$catcount).')</small></span>';
				} else {
					$count = '';
				}
				break;
		}
		if ($catcount) {
			$level = max(1,count(explode('-', $itemsortorder)));
			$process = (($level <= $showsubs && $option == "list") // user wants all the pages whose level is <= to the parameter
									|| ($option == 'list' || $option == 'list-top') && $level==1 // show the top level
									|| (($option == 'list' || ($option == 'omit-top' && $level>1))
											&& (($itemid == $currentitem_id) // current page
												|| ($itemparentid == $currentitem_id) // offspring of current page
												|| ($level < $mylevel && $level > 1 && (strpos($itemsortorder, $myparentsort) === 0) )// direct ancestor
												|| (($level == $mylevel) && ($currentitem_parentid == $itemparentid))	// sibling
												)
										)
									|| ($option == 'list-sub'
											&& ($itemparentid==$currentitem_id) // offspring of the current page
										 )
									);

			if ($process) {
				if ($level > $indent) {
					echo "\n".str_pad("\t",$indent,"\t")."<ul$css_class>\n";
					$indent++;
					$parents[$indent] = NULL;
					$open[$indent] = 0;
				} else if ($level < $indent) {
					$parents[$indent] = NULL;
					while ($indent > $level) {
						if ($open[$indent]) {
							$open[$indent]--;
							echo "</li>\n";
						}
						$indent--;
						echo str_pad("\t",$indent,"\t")."</ul>\n";
					}
				} else { // level == indent, have not changed
					if ($open[$indent]) { // level = indent
						echo str_pad("\t",$indent,"\t")."</li>\n";
						$open[$indent]--;
					} else {
						echo "\n";
					}
				}
				if ($open[$indent]) { // close an open LI if it exists
					echo "</li>\n";
					$open[$indent]--;
				}
				echo str_pad("\t",$indent-1,"\t");
				$open[$indent]++;
				$parents[$indent] = $itemid;
				if($level == 1) { // top level
					$class = $css_class_topactive;
				} else {
					$class = $css_class_active;
				}
				if(!is_null($_zp_current_zenpage_page)) {
					$gettitle = $_zp_current_zenpage_page->getTitle();
					$getname = $_zp_current_zenpage_page->getTitlelink();
				} else if (!is_null($_zp_current_category)) {
					$gettitle = $_zp_current_category->getTitle();
					$getname = $_zp_current_category->getTitlelink();
				} else {
					$gettitle = '';
					$getname = '';
				}
				$current = "";
				if($itemtitlelink == $getname && !in_context(ZP_SEARCH)) {
					switch($mode) {
						case 'pages':
							if ($_zp_gallery_page == 'pages.php') {
								$current = $class;
							}
							break;
						case 'categories':
						case 'allcategories':
							if ($_zp_gallery_page == 'news.php') {
								$current = $class;
							}
							break;
					}
				}
				if ($limit) {
					$itemtitle = shortenContent($itemtitle, $limit, MENU_TRUNCATE_INDICATOR);
				}
				echo "<li><a $current href=\"".html_encode($itemurl)."\" title=\"".html_encode(strip_tags($itemtitle))."\">".html_encode($itemtitle)."</a>".$count;
			}
		}
	}
	// cleanup any hanging list elements
	while ($indent > 1) {
		if ($open[$indent]) {
			echo "</li>\n";
			$open[$indent]--;
		}
		$indent--;
		echo str_pad("\t",$indent,"\t")."</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
		$open[$indent]--;
	} else {
		echo "\n";
	}
	if ($startlist) echo "</ul>\n";
}


/**
 * Prints the parent items breadcrumb navigation for pages or categories
 *
 * @param string $before Text to place before the breadcrumb item
 * @param string $after Text to place after the breadcrumb item
 */
function printZenpageItemsBreadcrumb($before=NULL, $after=NULL) {
	global $_zp_current_zenpage_page, $_zp_current_category;
	$parentitems = array();
	if(is_Pages()) {
		//$parentid = $_zp_current_zenpage_page->getParentID();
		$parentitems = $_zp_current_zenpage_page->getParents();
	}
	if(is_NewsCategory()) {
		//$parentid = $_zp_current_category->getParentID();
		$parentitems = $_zp_current_category->getParents();
	}
	foreach($parentitems as $item) {
		if(is_Pages()) {
			$pageobj = new ZenpagePage($item);
			$parentitemurl = html_encode(getPageLinkURL($item));
			$parentitemtitle = $pageobj->getTitle();
		}
		if(is_NewsCategory()) {
			$catobj = new ZenpageCategory($item);
			$parentitemurl = getNewsCategoryURL($item);
			$parentitemtitle = $catobj->getTitle();
		}
		echo html_encode($before)."<a href='".$parentitemurl."'>".html_encode($parentitemtitle) ."</a>".html_encode($after);
	}
}


/************************************************/
/* Pages functions
/************************************************/
$_zp_zenpage_pagelist = NULL;

/**
 * Returns a count of the pages
 *
 * If in search context, the count is the number of items found.
 * If in a page context, the count is the number of sub-pages of the current page.
 * Otherwise it is the total number of pages.
 *
 * @param bool $total return the count of all pages
 *
 * @return int
 */
function getNumPages($total=false) {
	global $_zp_zenpage, $_zp_zenpage_pagelist, $_zp_current_search, $_zp_current_zenpage_page;
	Zenpage::processExpired('pages');
	$addquery = '';
	if (!$total) {
		if (in_context(ZP_SEARCH)) {
			$_zp_zenpage_pagelist = $_zp_current_search->getPages();
			return count($_zp_zenpage_pagelist);
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			if(!zp_loggedin(ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS)) {
				$addquery = ' AND `show` = 1';
			}
			return db_count('pages','WHERE parentid='.$_zp_current_zenpage_page->getID().$addquery);
		}
	}
	if(!zp_loggedin(ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS)) {
		$addquery = ' WHERE `show` = 1';
	}
	return db_count('pages',$addquery);
}

/**
 * Returns a page from the search list
 *
 * @return object
 */
function next_page() {
	global $_zp_zenpage, $_zp_zenpage_pagelist,$_zp_current_search,$_zp_current_zenpage_page;
	if (!in_context(ZP_SEARCH)) {
		return false;
	}
	add_context(ZP_ZENPAGE_PAGE);
	if (is_null($_zp_zenpage_pagelist)) {
		Zenpage::processExpired('pages');
		$_zp_zenpage_pagelist = $_zp_current_search->getPages();
	}
	if (empty($_zp_zenpage_pagelist)) {
		$_zp_zenpage_pagelist = NULL;
		rem_context(ZP_ZENPAGE_PAGE);
		return false;
	}
	$page = array_shift($_zp_zenpage_pagelist);
	$_zp_current_zenpage_page = new ZenpagePage($page);
	return true;
}

/**
 * Returns title of a page
 *
 * @return string
 */
function getPageTitle() {
	global $_zp_current_zenpage_page;
	if (!is_null($_zp_current_zenpage_page)) {
		return $_zp_current_zenpage_page->getTitle();
	}
}


/**
 * Prints the title of a page
 *
 * @return string
 */
function printPageTitle($before=NULL) {
	echo html_encodeTagged($before).html_encode(getPageTitle());
}

/**
 * Returns the raw title of a page.
 *
 * @return string
 */
function getBarePageTitle() {
	return strip_tags(getPageTitle());
}

/**
 * Returns titlelink of a page
 *
 * @return string
 */
function getPageTitleLink() {
	global $_zp_current_zenpage_page;
	if(is_Pages()) {
		return $_zp_current_zenpage_page->getTitlelink();
	}
}


/**
 * Prints titlelink of a page
 *
 * @return string
 */
function printPageTitleLink() {
	global $_zp_current_zenpage_page;
	echo '<a href="'.html_encode(getPageLinkURL(getPageTitleLink())).'" title="'.getBarePageTitle().'">'.getPageTitle().'</a>';
}


/**
 * Returns the id of a page
 *
 * @return int
 */
function getPageID() {
	global $_zp_current_zenpage_page;
	if (is_Pages()) {
		return $_zp_current_zenpage_page->getID();
	}
}


/**
 * Prints the id of a page
 *
 * @return string
 */
function printPageID() {
	echo getPageID();
}


/**
 * Returns the id of the parent page of a page
 *
 * @return int
 */
function getPageParentID() {
	global $_zp_current_zenpage_page;
	if (is_Pages()) {
		return $_zp_current_zenpage_page->getParentid();
	}
}


/**
 * Returns the creation date of a page
 *
 * @return string
 */
function getPageDate() {
	global $_zp_current_zenpage_page;
	if (!is_null($_zp_current_zenpage_page)) {
		$d = $_zp_current_zenpage_page->getDatetime();
		return zpFormattedDate(DATE_FORMAT,strtotime($d));
	}
	return false;
}


/**
 * Prints the creation date of a page
 *
 * @return string
 */
function printPageDate() {
	echo html_encode(getPageDate());
}


/**
 * Returns the last change date of a page if available
 *
 * @return string
 */
function getPageLastChangeDate() {
	global $_zp_current_zenpage_page;
	if (!is_null($_zp_current_zenpage_page)) {
		$d = $_zp_current_zenpage_page->getLastchange();
		return zpFormattedDate(DATE_FORMAT,strtotime($d));
	}
	return false;
}


/**
 * Prints the last change date of a page
 *
 * @param string $before The text you want to show before the link
 * @return string
 */
function printPageLastChangeDate() {
	echo html_encode($before.getPageLastChangeDate());
}


/**
 * Returns page content either of the current page or if requested by titlelink directly. If not both return false
 * Set the titlelink of a page to call a specific even un-published page ($published = false) as a gallery description or on another custom page for example
 *
 * @param string $titlelink the titlelink of the page to print the content from
 * @param bool $published If titlelink is set, set this to false if you want to call an un-published page's content. True is default
 *
 * @return mixed
 */
function getPageContent($titlelink=NULL,$published=true) {
	global $_zp_current_zenpage_page;
	if (is_Pages() AND empty($titlelink)) {
		return $_zp_current_zenpage_page->getContent();
	}
	// print content of a page directly on a normal zenphoto theme page or any other page for example
	if(!empty($titlelink)) {
		$page = new ZenpagePage($titlelink);
		if($page->getShow() OR (!$page->getShow() AND !$published)) {
			return 	$page->getContent();
		}
	}
	return false;
}

/**
 * Print page content either of the current page or if requested by titlelink directly. If not both return false
 * Set the titlelink of a page to call a specific even un-published page ($published = false) as a gallery description or on another custom page for example
 *
 * @param string $titlelink the titlelink of the page to print the content from
 * @param bool $published If titlelink is set, set this to false if you want to call an un-published page's content. True is default
 * @return mixed
 */
function printPageContent($titlelink=NULL,$published=true) {
	echo html_encodeTagged(getPageContent($titlelink,$published));
}


/**
 * Returns page extra content either of the current page or if requested by titlelink directly. If not both return false
 * Set the titlelink of a page to call a specific even un-published page ($published = false) as a gallery description or on another custom page for example
 *
 * @param string $titlelink the titlelink of the page to print the content from
 * @param bool $published If titlelink is set, set this to false if you want to call an un-published page's extra content. True is default
 * @return mixed
 */
function getPageExtraContent($titlelink='',$published=true) {
	global $_zp_current_zenpage_page;
	if (is_Pages() AND empty($titlelink)) {
		return $_zp_current_zenpage_page->getExtracontent();
	}
	// print content of a page directly on a normal zenphoto theme page for example
	if(!empty($titlelink)) {
		$page = new ZenpagePage($titlelink);
		if($page->getShow() OR (!$page->getShow() AND !$published)) {
			return $page->getExtracontent();
		}
	}
	return false;
}


/**
 * Prints page extra content if on a page either of the current page or if requested by titlelink directly. If not both return false
 * Set the titlelink of a page to call a specific even un-published page ($published = false) as a gallery description or on another custom page for example
 *
 * @param string $titlelink the titlelink of the page to print the content from
 * @param bool $published If titlelink is set, set this to false if you want to call an un-published page's extra content. True is default
 * @return mixed
 */
function printPageExtraContent($titlelink=NULL,$published=true) {
	echo getPageExtraContent($titlelink,$published);
}

/**
 * Gets the custom data field of the current page
 *
 * @return string
 */
function getPageCustomData() {
	global $_zp_current_zenpage_page;
	if(!is_null($_zp_current_zenpage_page)) {
		return $_zp_current_zenpage_page->getCustomData();
	}
}
/**
 * Prints the custom data field of the current page
 *
 */
function printPageCustomData() {
	echo getPageCustomData();
}


/**
 * Returns the author of a page
 *
 * @param bool $fullname True if you want to get the full name if set, false if you want the login/screenname
 *
 * @return string
 */
function getPageAuthor($fullname=false) {
	if(is_Pages()) {
		return getAuthor($fullname);
	}
	return false;
}


/**
 * Prints the author of a page
 *
 * @param bool $fullname True if you want to get the full name if set, false if you want the login/screenname
 * @return string
 */
function printPageAuthor($fullname=false) {
	if (getNewsTitle()) {
		echo html_encode(getPageAuthor($fullname));
	}
}


/**
 * Returns the sortorder of a page
 *
 * @return string
 */
function getPageSortorder() {
	global  $_zp_current_zenpage_page;
	if (is_Pages()) {
		return $_zp_current_zenpage_page->getSortOrder();
	}
	return false;
}



/**
 * Returns path to the pages.php page
 *
 * @return string
 */
function getPageLinkPath() {
	global $_zp_zenpage;
	return $_zp_zenpage->getPagesLinkPath();
}


/**
 * Returns full path to a specific page
 *
 * @return string
 */
function getPageLinkURL($titlelink='') {
	global $_zp_zenpage, $_zp_current_zenpage_page;
	if(empty($titlelink)) {
		return $_zp_current_zenpage_page->getPageLink();
	} else {
		return getPageLinkPath().$titlelink;
	}
}

/**
 * Prints the url to a specific zenpage page
 *
 * @param string $linktext Text for the URL
 * @param string $titlelink page to include in URL
 * @param string $prev text to insert before the URL
 * @param string $next text to follow the URL
 * @param string $class optional class
 */
function printPageLinkURL($linktext, $titlelink, $prev='', $next='', $class=NULL) {
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	echo $prev."<a href=\"".html_encode(getPageLinkURL($titlelink))."\" $class title=\"".html_encode($linktext)."\">".html_encode($linktext)."</a>".$next;
}

/**
 * Prints excerpts of the direct subpages (1 level) of a page for a kind of overview. The setup is:
 * <div class='pageexcerpt'>
 * <h4>page title</h3>
 * <p>page content excerpt</p>
 * <p>read more</p>
 * </div>
 *
 * @param int $excerptlength The length of the page content, if nothing specifically set, the plugin option value for 'news article text length' is used
 * @param string $readmore The text for the link to the full page. If empty the read more setting from the options is used.
 * @param string $shortenindicator The optional placeholder that indicates that the content is shortened, if this is not set the plugin option "news article text shorten indicator" is used.
 * @return string
 */
function printSubPagesExcerpts($excerptlength=NULL, $readmore=NULL, $shortenindicator=NULL) {
	global $_zp_current_zenpage_page;
	if(is_null($readmore)) {
		$readmore = get_language_string(ZP_READ_MORE);
	}
	$pages = $_zp_current_zenpage_page->getPages();
	$subcount = 0;
	if(is_null($excerptlength)) {
		$excerptlength = ZP_SHORTEN_LENGTH;
	}
	foreach($pages as $page) {
		$pageobj = new ZenpagePage($page);
		if($pageobj->getParentID() == $_zp_current_zenpage_page->getID()) {
			$subcount++;
			$pagetitle = html_encode($pageobj->getTitle());
			$pagecontent = $pageobj->getContent();
			if($pageobj->checkAccess()) {
				$pagecontent = getContentShorten($pagecontent, $excerptlength, $shortenindicator,$readmore, getPageLinkURL($pageobj->getTitlelink()));
			} else {
				$pagecontent = '<p><em>'.gettext('This page is password protected').'</em></p>';
			}
			echo '<div class="pageexcerpt">';
			echo '<h4><a href="'.html_encode(getPageLinkURL($pageobj->getTitlelink())).'" title="'.strip_tags($pagetitle).'">'.$pagetitle.'</a></h4>';
			echo $pagecontent;
			echo '</div>';
		}
	}
}



/**
 * Prints a context sensitive menu of all pages as a unordered html list
 *
 * @param string $option The mode for the menu:
 * 												"list" context sensitive toplevel plus sublevel pages,
 * 												"list-top" only top level pages,
 * 												"omit-top" only sub level pages
 * 												"list-sub" lists only the current pages direct offspring
 * @param string $css_id CSS id of the top level list
 * @param string $css_class_topactive class of the active item in the top level list
 * @param string $css_class CSS class of the sub level list(s)
 * @param string $$css_class_active CSS class of the sub level list(s)
 * @param string $indexname insert the name (default "Gallery Index") how you want to call the link to the gallery index, insert "" (default) if you don't use it, it is not printed then.
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param bool $startlist set to true to output the UL tab
 * @@param int $limit truncation of display text
 * @return string
 */
function printPageMenu($option='list',$css_id=NULL,$css_class_topactive=NULL,$css_class=NULL,$css_class_active=NULL,$indexname=NULL,$showsubs=0,$startlist=true,$limit=NULL) {
	printNestedMenu($option,'pages',false, $css_id,$css_class_topactive,$css_class,$css_class_active,$indexname,$showsubs,$startlist,$limit);
}

/**
 * If the titlelink is valid this will setup for the page
 * Returns true if page is setup and valid, otherwise returns false
 *
 * @param string $titlelink The page to setup
 *
 * @return bool
 */
function checkForPage($titlelink) {
	global $_zp_current_zenpage_page;
	if(!empty($titlelink)) {
		$page = new ZenpagePage($titlelink);
		if ($page->loaded) {
			add_context(ZP_ZENPAGE_PAGE);
			$_zp_current_zenpage_page = $page;
			return true;
		}
	}
	return false;
}

/************************************************/
/* Comments
/************************************************/

/**
 * Returns if comments are open for this news article or page (TRUE or FALSE)
 *
 * @return bool
 */
function zenpageOpenedForComments() {
	global $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	if(is_NewsArticle()) {
		$obj = $_zp_current_zenpage_news;
	}
	if(is_Pages()) {
		$obj = $_zp_current_zenpage_page;
	}
	return $obj->get('commentson');
}


/**
 * Gets latest comments for news articles and pages
 *
 * @param int $number how many comments you want.
 * @param string $type 	"all" for all latest comments for all news articles and all pages
 * 											"news" for the lastest comments of one specific news article
 * 											"page" for the lastest comments of one specific page
 * @param int $itemID the ID of the element to get the comments for if $type != "all"
 */
function getLatestZenpageComments($number,$type="all",$itemID="") {
	$itemID = sanitize_numeric($itemID);
	$number = sanitize_numeric($number);
	if($type == 'all' || $type == 'news') {
		$newspasswordcheck = "";
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			$newscheck = query_full_array("SELECT * FROM " . prefix('news'). " ORDER BY date");
			foreach ($newscheck as $articlecheck) {
				$obj = new ZenpageNews($articlecheck['titlelink']);
				if($obj->inProtectedCategory()) {
					$excludenews = " AND id != ".$articlecheck['id'];
					$newspasswordcheck = $newspasswordcheck.$excludenews;
				}
			}
		}
	}
	if($type == 'all' || $type == 'page') {
		$pagepasswordcheck = "";
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			$pagescheck = query_full_array("SELECT * FROM " . prefix('pages'). " ORDER BY date");
			foreach ($pagescheck as $pagecheck) {
				$obj = new ZenpagePage($pagecheck['titlelink']);
				if($obj->isProtected()) {
					$excludepages = " AND pages.id != ".$pagecheck['id'];
					$pagepasswordcheck = $pagepasswordcheck.$excludepages;
				}
			}
		}
	}
	switch ($type) {
		case "news":
			$whereNews = " WHERE news.show = 1 AND news.id = ".$itemID." AND c.ownerid = news.id AND c.type = 'news' AND c.private = 0 AND c.inmoderation = 0".$newspasswordcheck;
			break;
		case "page":
			$wherePages = " WHERE pages.show = 1 AND pages.id = ".$itemID." AND c.ownerid = pages.id AND c.type = 'pages' AND c.private = 0 AND c.inmoderation = 0".$pagepasswordcheck;
			break;
		case "all":
			$whereNews = " WHERE news.show = 1 AND c.ownerid = news.id AND c.type = 'news' AND c.private = 0 AND c.inmoderation = 0".$newspasswordcheck;
			$wherePages = " WHERE pages.show = 1 AND c.ownerid = pages.id AND c.type = 'pages' AND c.private = 0 AND c.inmoderation = 0".$pagepasswordcheck;
			break;
	}
	$comments_news = array();
	$comments_pages = array();
	if ($type == "all" OR $type == "news") {
		$comments_news = query_full_array("SELECT c.id, c.name, c.type, c.website,"
		. " c.date, c.anon, c.comment, news.title, news.titlelink FROM ".prefix('comments')." AS c, ".prefix('news')." AS news "
		. $whereNews
		. " ORDER BY c.id DESC LIMIT $number");
	}
	if ($type == "all" OR $type == "page") {
		$comments_pages = query_full_array("SELECT c.id, c.name, c.type, c.website,"
		. " c.date, c.anon, c.comment, pages.title, pages.titlelink FROM ".prefix('comments')." AS c, ".prefix('pages')." AS pages "
		. $wherePages
		. " ORDER BY c.id DESC LIMIT $number");
	}
	$comments = array();
	foreach ($comments_news as $comment) {
		$comments[$comment['id']] = $comment;
	}
	foreach ($comments_pages as $comment) {
		$comments[$comment['id']] = $comment;
	}
	krsort($comments);
	return array_slice($comments, 0, $number);
}


/**
 * Prints out latest comments for news articles and pages as a unordered list
 *
 * @param int $number how many comments you want.
 * @param string $shorten the number of characters to shorten the comment display
 * @param string $id The css id to style the list
 * @param string $type 	"all" for all latest comments for all news articles and all pages
 * 											"news" for the lastest comments of one specific news article
 * 											"page" for the lastest comments of one specific page
 * @param int $itemID the ID of the element to get the comments for if $type != "all"
 */
function printLatestZenpageComments($number, $shorten='123', $id='showlatestcomments',$type="all",$itemID="") {
	if(empty($class)) {
		$id = "";
	} else {
		$id = "id='".$id." ";
	}
	$comments = getLatestZenpageComments($number,$type,$itemID);
	echo "<ul $id>\n";
	foreach ($comments as $comment) {
		if($comment['anon']) {
			$author = "";
		} else {
			$author = " ".gettext("by")." ".$comment['name'];
		}
		$date = $comment['date'];
		$title = get_language_string($comment['title']);
		$titlelink = $comment['titlelink'];
		$website = $comment['website'];
		$shortcomment = truncate_string($comment['comment'], $shorten);
		$url = "";
		switch($comment['type']){
			case "news":
				$url = getNewsURL($titlelink);
				break;
			case "pages":
				$url = getPageLinkURL($titlelink);
				break;
		}
		echo "<li><a href=\"".html_encode($url)."\" class=\"commentmeta\">".$title.$author."</a><br />\n";
		echo "<span class=\"commentbody\">".$shortcomment."</span></li>";
	}
	echo "</ul>\n";
}


/************************************************/
/* RSS functions
/************************************************/

/**
 * Gets a RSS link
 *
 * @param string $option type of RSS: "News" feed for all news articles
 * 																		"Category" for only the news articles of the category that is currently selected
 * 																		"NewsWithImages" for all news articles and latest images
 * 																		"Comments" for all news articles and pages
 * 																		"Comments-news" for comments of only the news article it is called from
 * 																		"Comments-page" for comments of only the page it is called from
 * 																		"Comments-all" for comments from all albums, images, news articels and pages
 * @param string $categorylink The specific category you want a RSS feed from (only 'Category' mode)
 * @param string $prev text to before before the link
 * @param string $linktext title of the link
 * @param string $next text to appear after the link
 * @param bool $printIcon print an RSS icon beside it? if true, the icon is zp-core/images/rss.png
 * @param string $class css class
 * @param string $lang optional to display a feed link for a specific language (currently works for latest images only). Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 */
function getZenpageRSSLink($option='News', $categorylink='', $lang=NULL) {
	global $_zp_current_category;
	if(empty($lang)) {
		$lang = getOption('locale');
	}
	if($option == 'Category') {
		if(!is_null($categorylink)) {
			$categorylink = '&category='.sanitize($categorylink);
		} elseif(empty($categorylink) AND !is_null($_zp_current_category)) {
			$categorylink = '&category='.$_zp_current_category->getTitlelink();
		} else {
			$categorylink = '';
		}
	}
	switch($option) {
		case 'News':
			if (getOption('RSS_articles')) {
				return WEBPATH.'/index.php?rss=news&lang='.$lang;
			}
			break;
		case 'Category':
			if (getOption('RSS_articles')) {
				return WEBPATH.'/index.php?rss=news&lang='.$lang.$categorylink;
			}
			break;
		case 'NewsWithImages':
			if (getOption('RSS_articles')) {
				return WEBPATH.'/index.php?rss=news&withimages&lang='.$lang;
			}
			break;
		case 'Comments':
			if (getOption('RSS_article_comments')) {
				return WEBPATH.'/index.php?rss=comments&type=zenpage&lang='.$lang;
			}
			break;
		case 'Comments-news':
			if (getOption('RSS_article_comments')) {
				return WEBPATH.'/index.php?rss=comments&id='.getNewsID().'&type=news&lang='.$lang;
			}
			break;
		case 'Comments-page':
			if (getOption('RSS_article_comments')) {
				return WEBPATH.'/index.php?rss=comments&id='.getPageID().'&type=page&lang='.$lang;
			}
			break;
		case 'Comments-all':
			if (getOption('RSS_article_comments')) {
				return WEBPATH.'/index.php?rss=comments&type=allcomments&lang='.$lang;
			}
			break;
	}
}

/**
 * Prints a RSS link
 *
 * @param string $option type of RSS: "News" feed for all news articles
 * 																		"Category" for only the news articles of the category that is currently selected
 * 																		"NewsWithImages" for all news articles and latest images
 * 																		"Comments" for all news articles and pages
 * 																		"Comments-news" for comments of only the news article it is called from
 * 																		"Comments-page" for comments of only the page it is called from
 * 																		"Comments-all" for comments from all albums, images, news articels and pages
 * @param string $categorylink The specific category you want a RSS feed from (only 'Category' mode)
 * @param string $prev text to before before the link
 * @param string $linktext title of the link
 * @param string $next text to appear after the link
 * @param bool $printIcon print an RSS icon beside it? if true, the icon is zp-core/images/rss.png
 * @param string $class css class
 * @param string $lang optional to display a feed link for a specific language (currently works for latest images only). Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 */
function printZenpageRSSLink($option='News', $categorylink='', $prev='', $linktext='', $next='', $printIcon=true, $class=null, $lang=NULL) {
	if ($printIcon) {
		$icon = ' <img src="' . FULLWEBPATH . '/' . ZENFOLDER . '/images/rss.png" alt="RSS Feed" />';
	} else {
		$icon = '';
	}
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	$linktext = html_encode($linktext);
	echo $prev."<a $class href=\"".html_encode(getZenpageRSSLink($option,$categorylink,$lang))."\" title=\"".gettext("News RSS")."\" rel=\"nofollow\">".$linktext."$icon</a>".$next;
}


/**
 * Prints the RSS link for use in the HTML HEAD
 * @param string $option type of RSS: "News" feed for all news articles
 * 																		"Category" for only the news articles of the category that is currently selected
 * 																		"NewsWithImages" for all news articles and latest images
 * 																		"Comments" for all news articles and pages
 * 																		"Comments-news" for comments of only the news article it is called from
 * 																		"Comments-page" for comments of only the page it is called from
 * 																		"Comments-all" for comments from all albums, images, news articels and pages
 * @param string $categorylink The specific category you want a RSS feed from (only 'Category' mode)
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language (currently works for latest images only). Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 *
 */
function printZenpageRSSHeaderLink($option='News',$categorylink='',$linktext='',$lang=null) {
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL.'://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode(strip_tags($linktext))."\" href=\"".$protocol.$host.html_encode(getZenpageRSSLink($option,$categorylink,$lang))."\" />\n";
}

/**
 * support to show an image from an album
 * The imagename is optional. If absent the album thumb image will be
 * used and the link will be to the album. If present the link will be
 * to the image.
 *
 * @param string $albumname
 * @param string $imagename
 * @param int $size the size to make the image. If omitted image will be 50% of 'image_size' option.
 * @param bool $linkalbum set true to link specific image to album instead of image
 */
function zenpageAlbumImage($albumname, $imagename=NULL, $size=NULL, $linkalbum=false) {
	global $_zp_gallery;
	echo '<br />';
	$album = new Album(NULL, $albumname);
	if ($album->loaded) {
		if (is_null($size)) {
			$size = floor(getOption('image_size') * 0.5);
		}
		$image = NULL;
		if (is_null($imagename)) {
			$linkalbum = true;
			$image = $album->getAlbumThumbImage();
		} else {
			$image = newImage($album, $imagename);
		}
		if ($image && $image->loaded) {
			makeImageCurrent($image);
			if ($linkalbum) {
				rem_context(ZP_IMAGE);
				echo '<a href="'.html_encode(getAlbumLinkURL($album)).'"   title="'.sprintf(gettext('View the %s album'), $albumname).'">';
				add_context(ZP_IMAGE);
				printCustomSizedImage(sprintf(gettext('View the album %s'), $albumname), $size);
				rem_context(ZP_IMAGE | ZP_ALBUM);
				echo '</a>';
			} else {
				echo '<a href="'.html_encode(getImageLinkURL($image)).'" title="'.sprintf(gettext('View %s'), $imagename).'">';
				printCustomSizedImage(sprintf(gettext('View %s'), $imagename), $size);
				rem_context(ZP_IMAGE | ZP_ALBUM);
				echo '</a>';
			}
		} else {
			?>
			<span style="background:red;color:black;">
			<?php
			printf(gettext('<code>zenpageAlbumImage()</code> did not find the image %1$s:%2$s'), $albumname, $imagename);
			?>
			</span>
			<?php
		}
	} else {
		?>
		<span style="background:red;color:black;">
		<?php
		printf(gettext('<code>zenpageAlbumImage()</code> did not find the album %1$s'), $albumname);
		?>
		</span>
		<?php
	}
}

// page password functions


?>