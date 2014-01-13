<?php

/*
 * These are the Zenpage functions which have been deprecated
 */

/**
 * @deprecated
 * @since 1.2.7
 */
function getZenpageHitcounter($mode = "", $obj = NULL) {
	deprecated_functions::notify(gettext('Use getHitcounter().'));
	return @call_user_func('getHitcounter');
}

/**
 * @deprecated
 * @since 1.2.7
 */
function zenpageHitcounter($option = 'pages', $viewonly = false, $id = NULL) {
	deprecated_functions::notify(gettext('Use getHitcounter().'));
	return @call_user_func('getHitcounter');
}

/**
 * @deprecated
 * @since 1.2.9
 */
function getNewsImageTags() {
	deprecated_functions::notify(gettext('Use object->getTags() method.'));
	global $_zp_current_zenpage_news;
	if (is_object($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getTags();
	} else {
		return false;
	}
}

/**
 * @deprecated
 * @since 1.2.9
 */
function printNewsImageTags($option = 'links', $preText = NULL, $class = 'taglist', $separator = ', ', $editable = TRUE) {
	deprecated_functions::notify(gettext('Use printTags().'));
	printTags($option, $preText, $class, $separator);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getSubCategories($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getSubCategories().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSubCategories();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function inProtectedNewsCategory($articleobj = NULL, $checkProtection = true) {
	deprecated_functions::notify(gettext('Use instead the Zenpage news class method inProtectedCategory().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	if (empty($articleobj) && !is_null($_zp_current_zenpage_news) && get_class($_zp_current_zenpage_news) == 'zenpagenews') {
		$articleobj = $_zp_current_zenpage_news;
	}
	if (!is_object($articleobj))
		return false;
	return $articleobj->inProtectedCategory();
	$categories = $articleobj->getCategories();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function isProtectedNewsCategory($catlink = '') {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_category;
	if (empty($catlink) && !is_null($_zp_current_category)) {
		$cat = $_zp_current_category;
	} else {
		$cat = new ZenpageCategory($catlink);
	}
	return $cat->isProtected();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getParentNewsCategories($parentid, $initparents = true) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getParents().'), E_USER_NOTICE);
	return getParentItems('categories', $parentid, $initparents);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCategoryTitle($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getTitle().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getTitle();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCategoryID($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getID().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getID();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCategoryParentID($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getParentID().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getParentID();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCategorySortOrder($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method getSortOrder().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSortOrder();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getParentPages(&$parentid, $initparents = true) {
	deprecated_functions::notify(gettext('Use instead the Zenpage page class method getParents().'), E_USER_NOTICE);
	return getParentItems('pages', $parentid, $initparents);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function isProtectedPage($pageobj = NULL) {
	deprecated_functions::notify(gettext('Use instead the Zenpage page class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj))
		$pageobj = $_zp_current_zenpage_page;
	return $pageobj->checkforGuest() != 'zp_public_access';
}

/**
 * @deprecated
 * @since 1.4.0
 */
function isMyPage($pageobj = NULL, $action) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj))
		$pageobj = $_zp_current_zenpage_page;
	return $pageobj->isMyItem($action);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function checkPagePassword($pageobj, &$hint, &$show) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	return $pageobj->checkforGuest();
}

//	News category password functions

/**
 * @deprecated
 * @since 1.4.0
 */
function isMyNews($newsobj, $action) {
	deprecated_functions::notify(gettext('Use instead the Zenpage news class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->isMyItem();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function checkNewsAccess($newsobj, &$hint, &$show) {
	deprecated_functions::notify(gettext('Use instead the Zenpage news class method checkNewsAccess().'), E_USER_NOTICE);
	return $newsobj->checkNewsAccess($hint, $show);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function checkNewsCategoryPassword($catlink, $hint, $show) {
	deprecated_functions::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->checkforGuest();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCurrentNewsCategory() {
	deprecated_functions::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getTitlelink().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getTitlelink();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCurrentNewsCategoryID() {
	deprecated_functions::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getID();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getCurrentNewsCategoryParentID() {
	deprecated_functions::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getParentID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getParentID();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function inNewsCategory($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inNewsCategory($catlink).'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->inNewsCategory($catlink);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function inSubNewsCategoryOf($catlink) {
	deprecated_functions::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function isSubNewsCategoryOf($catlink) {
	global $_zp_current_category;
	deprecated_functions::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->isSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
	return $_zp_current_category->isSubNewsCategoryOf($catlink);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printNewsReadMoreLink($readmore = '') {
	deprecated_functions::notify(gettext('Functionality is now included in getNewsContent(), printNewsContent() and getContentShorten() to properly cover custom shortening via TinyMCE <pagebreak>.'), E_USER_NOTICE);
	$readmore = getNewsReadMore($readmore);
	if (!empty($readmore)) {
		if (is_NewsType("news")) {
			$newsurl = getNewsURL(getNewsTitleLink());
		} else {
			$newsurl = html_encode(getNewsTitleLink());
		}
		echo "<a href='" . $newsurl . "' title=\"" . getBareNewsTitle() . "\">" . html_encode($readmore) . "</a>";
	}
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getNewsContentShorten($articlecontent, $shorten, $shortenindicator = '', $readmore = '') {
	deprecated_functions::notify(gettext('Use getContentShorten() instead. Note the read more url must be passed directly.'), E_USER_NOTICE);
	return getContentShorten($articlecontent, $shorten, $shortenindicator, '');
}

/**
 * @deprecated
 * @since 1.4.1
 */
function processExpired($table) {
	deprecated_functions::notify(gettext('This happens automatically.'));
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getParentItems($mode = 'pages', &$parentid, $initparents = true) {
	deprecated_functions::notify(gettext('Use the method from either the ZenpagePage or the ZenpageCategory class instead.'));
	global $_zp_current_zenpage_page, $_zp_current_category;
	switch ($mode) {
		case 'pages':
			if (!is_null($_zp_current_zenpage_page))
				return $_zp_current_zenpage_page->getParents();
			break;
		case 'categories':
			if (!is_null($_zp_current_category))
				return $_zp_current_category->getParents();
			break;
	}
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getPages($published = NULL) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getPages($published);
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getArticles($articles_per_page = '', $category = '', $published = NULL, $ignorepagination = false, $sortorder = "date", $sortdirection = "desc", $sticky = true) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage, $_zp_current_category;
	if (!empty($category)) {
		$catobj = new ZenpageCategory($category);
		return $catobj->getArticles($articles_per_page, $category, $published, $ignorepagination, $sortorder, $sortdirection, $sticky);
	} elseif (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		return $_zp_current_category->getArticles($articles_per_page, $category, $published, $ignorepagination, $sortorder, $sortdirection, $sticky);
	} else {
		return $_zp_zenpage->getArticles($articles_per_page, $category, $published, $ignorepagination, $sortorder, $sortdirection, $sticky);
	}
}

/**
 * @deprecated
 * @since 1.4.1
 */
function countArticles($category = '', $published = 'published', $count_subcat_articles = true) {
	deprecated_functions::notify(gettext('Count the articles instead.'));
	global $_zp_post_date;
	if (zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
		$published = "all";
	} else {
		$published = "published";
	}
	$show = "";
	if (empty($category)) {
		switch ($published) {
			case "published":
				$show = " WHERE `show` = 1 AND date <= '" . date('Y-m-d H:i:s') . "'";
				break;
			case "unpublished":
				$show = " WHERE `show` = 0 AND date <= '" . date('Y-m-d H:i:s') . "'";
				break;
			case "all":
				$show = "";
				break;
		}
		// date archive query addition
		if (in_context(ZP_ZENPAGE_NEWS_DATE)) {
			if (empty($show)) {
				$and = " WHERE ";
			} else {
				$and = " AND ";
			}
			$datesearch = $and . "date LIKE '$_zp_post_date%'";
		} else {
			$datesearch = "";
		}
		$count = db_count('news', $show . $datesearch);
		return $count;
	} else {
		$catobj = new ZenpageCategory($category);
		switch ($published) {
			case "published":
				$show = " AND news.show = 1 AND news.date <= '" . date('Y-m-d H:i:s') . "'";
				break;
			case "unpublished":
				$show = " AND news.show = 0 AND news.date <= '" . date('Y-m-d H:i:s') . "'";
				break;
			case "all":
				$show = "";
				break;
		}
		if ($count_subcat_articles)
			$subcats = $catobj->getSubCategories();
		if ($subcats && $count_subcat_articles) {
			$cat = " (cat.cat_id = '" . $catobj->getID() . "'";
			foreach ($subcats as $subcat) {
				$subcatobj = new ZenpageCategory($subcat);
				$cat .= "OR cat.cat_id = '" . $subcatobj->getID() . "' ";
			}
			$cat .= ") AND cat.news_id = news.id ";
		} else {
			$cat = " cat.cat_id = '" . $catobj->getID() . "' AND cat.news_id = news.id ";
		}
		$result = query_full_array("SELECT DISTINCT news.titlelink FROM " . prefix('news2cat') . " as cat, " . prefix('news') . " as news WHERE " . $cat . $show);
		$count = count($result);
		return $count;
	}
}

/**
 * Returns the articles count
 *
 * @deprecated
 * @since 1.4.1
 */
function getTotalArticles() {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getTotalArticles();
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getAllArticleDates($yearsonly = false) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllArticleDates($yearsonly);
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getCurrentNewsPage() {
	deprecated_functions::notify(gettext('Use the global $_zp_page instead.'));
	global $_zp_page;
	return $_zp_page;
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getCombiNews($articles_per_page = '', $mode = '', $published = NULL, $sortorder = '', $sticky = true) {
	deprecated_functions::notify(gettext('Combinews is removed.'));
	return array();
}

/**
 * @deprecated
 * @since 1.4.1
 */
function countCombiNews($published = NULL) {
	deprecated_functions::notify(gettext('Combinews is removed.'));
	return 0;
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getCategoryLink($catname) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategoryLink($catname);
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getCategory($id) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategory($id);
}

/**
 * @deprecated
 * @since 1.4.1
 */
function getAllCategories() {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllCategories();
}

/**
 * @deprecated
 * @since 1.4.2
 */
function getZenpageRSSHeaderLink($option = '', $categorylink = '', $linktext = '', $lang = '') {
	deprecated_functions::notify(gettext('Use the template function <code>getRSSLink()</code> instead. NOTE: While this function gets a full html link  <code>getRSSLink()</code> just returns the URL.'));
	global $_zp_current_category;
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL . '://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	if (empty($lang)) {
		$lang = getOption("locale");
	}
	if ($option == 'Category') {
		if (!is_null($categorylink)) {
			$categorylink = '&amp;category=' . html_encode($categorylink);
		} elseif (empty($categorylink) AND !is_null($_zp_current_category)) {
			$categorylink = '&amp;category=' . $_zp_current_category->getTitlelink();
		} else {
			$categorylink = '';
		}
	}
	switch ($option) {
		case "News":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode(strip_tags($linktext)) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=news&amp;lang=" . $lang . "\" />\n";
			}
		case "Category":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode(strip_tags($linktext)) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=news&amp;lang=" . $lang . $categorylink . "\" />\n";
			}
		case "NewsWithImages":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode(strip_tags($linktext)) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=news&amp;withimages&amp;lang=" . $lang . "\" />\n";
			}
	}
}

/**
 * @deprecated
 * @since 1.4.5
 */
function printLatestZenpageComments($number, $shorten = '123', $id = 'showlatestcomments', $type = "all", $itemID = "") {
	deprecated_functions::notify(gettext('use printLatestComments($number, $shorten, $type, $itemID, $id);'));
	printLatestComments($number, $shorten, $type, $itemID, $id);
}

/**
 * @deprecated
 * @since 1.4.5
 */
function getZenpageRSSLink($option = 'News', $categorylink = '', $lang = NULL) {
	deprecated_functions::notify(gettext('use getRSSLink($option,$lang,$categorylink).'));
	return getRSSLink($option, $lang, $categorylink);
}

/**
 * @deprecated
 * @since 1.4.5
 */
function printZenpageRSSLink($option = 'News', $categorylink = '', $prev = '', $linktext = '', $next = '', $printIcon = true, $class = null, $lang = NULL) {
	deprecated_functions::notify(gettext('use printRSSLink($option, $prev, $linktext, $next, $printIcon, $class, $lang, $categoryLink).'));
	if (class_exists('RSS'))
		printRSSLink($option, $prev, $linktext, $next, $printIcon, $class, $lang, $categoryLink);
}

/**
 * @deprecated
 * @since 1.4.5
 */
function printZenpageRSSHeaderLink($option = 'News', $categorylink = '', $linktext = '', $lang = null) {
	deprecated_functions::notify(gettext('use printRSSHeaderLink($option, $linktext, $lang, $categorylink).'));
	if (class_exists('RSS'))
		printRSSHeaderLink($option, $linktext, $lang, $categorylink);
}

/**
 * Gets the news type of a news item.
 * "news" for a news article or if using the CombiNews feature
 * "flvmovie" (for flv, fla, mp3, m4a and mp4/m4v), "image", "3gpmovie" or "quicktime"
 *
 * @param obj $newsobj optional news object to check directly outside news context
 * @return string
 * @deprecated since version 1.4.6
 */
function getNewsType($newsobj = NULL) {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return 'news';
}

/**
 * Checks what type the current news item is (See get NewsType())
 *
 * @param string $type The type to check for
 * 										 "news" for a news article or if using the CombiNews feature
 * 										"flvmovie" (for flv, fla, mp3, m4a and mp4/m4v), "image", "3gpmovie" or "quicktime"
 * @param obj $newsobj optional news object to check directly outside news context
 * @return bool
 * @deprecated since version 1.4.6
 */
function is_NewsType($type, $newsobj = NULL) {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return 'news' == $type;
}

/**
 * CombiNews feature: A general wrapper function to check if this is a 'normal' news article (type 'news' or one of the zenphoto news types
 *
 * @return bool
 * @deprecated since version 1.4.6
 */
function is_GalleryNewsType() {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return false;
}

/**
 * Helper function for getNewsContent to get video/audio content if $imageobj is a video/audio object if using Zenpage CombiNews
 *
 * @param object $imageobj The object of an image
 * @deprecated since version 1.4.6
 */
function getNewsVideoContent($imageobj) {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return NULL;
}

/**
 * CombiNews feature only: returns the album title if image or movie/audio or false.
 *
 * @return mixed
 * @deprecated since version 1.4.6
 */
function getNewsAlbumTitle() {
	global $_zp_current_zenpage_news;
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return false;
}

/**
 * CombiNews feature only: returns the raw title of an album if image or movie/audio or false.
 *
 * @return string
 * @deprecated since version 1.4.6
 */
function getBareNewsAlbumTitle() {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return strip_tags(getNewsAlbumTitle());
}

/**
 * CombiNews feature only: returns the album name (folder) if image or movie/audio or returns false.
 *
 * @return mixed
 * @deprecated since version 1.4.6
 */
function getNewsAlbumName() {
	global $_zp_current_zenpage_news;
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return false;
}

/**
 * CombiNews feature only: returns the url to an album if image or movie/audio or returns false.
 *
 * @return mixed
 * @deprecated since version 1.4.6
 */
function getNewsAlbumURL() {
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	if (getNewsAlbumName()) {
		return rewrite_path("/" . html_encode(getNewsAlbumName()), "index.php?album=" . html_encode(getNewsAlbumName()));
	} else {
		return false;
	}
}

/**
 * CombiNews feature only: Returns the fullimage link if image or movie/audio or false.
 *
 * @return mixed
 * @deprecated since version 1.4.6
 */
function getFullNewsImage() {
	global $_zp_current_zenpage_news;
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	return false;
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
 * @param string $sortdirection 'desc' descending (default) or 'asc' ascending
 * @return array
 * @deprecated since version 1.4.6
 */
function getLatestNews($number = 2, $option = 'none', $category = '', $sticky = true, $sortdirection = 'desc') {
	global $_zp_zenpage, $_zp_current_zenpage_news;
	if ($option != 'none')
		deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	$latest = array();
	switch ($option) {
		case 'none':
			if (empty($category)) {
				$latest = $_zp_zenpage->getArticles($number, NULL, true, NULL, $sortdirection, $sticky, NULL);
			} else {
				$catobj = new ZenpageCategory($category);
				$latest = $catobj->getArticles($number, NULL, true, NULL, $sortdirection, $sticky);
			}
			$counter = '';
			$latestnews = array();
			if (is_array($latest)) {
				foreach ($latest as $item) {
					$article = new ZenpageNews($item['titlelink']);
					$counter++;
					$latestnews[$counter] = array(
									"albumname"	 => $article->getTitle(),
									"titlelink"	 => $article->getTitlelink(),
									"date"			 => $article->getDateTime(),
									"type"			 => "news"
					);
					$latest = $latestnews;
				}
			}
			break;
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
 * @deprecated since version 1.4.6
 */
function printLatestNews($number = 5, $option = 'with_latest_images', $category = '', $showdate = true, $showcontent = true, $contentlength = 70, $showcat = true, $readmore = NULL, $sticky = true) {
	global $_zp_gallery, $_zp_current_zenpage_news;
	deprecated_functions::notify(gettext('CombiNews is deprecated. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	$latest = getLatestNews($number, $option, $category, $sticky);
	echo "\n<ul id=\"latestnews\">\n";
	$count = "";
	foreach ($latest as $item) {
		$count++;
		$category = "";
		$categories = "";
		switch ($item['type']) {
			case 'news':
				$obj = new ZenpageNews($item['titlelink']);
				$title = html_encode($obj->getTitle());
				$link = html_encode(getNewsURL($item['titlelink']));
				$count2 = 0;
				$category = $obj->getCategories();
				foreach ($category as $cat) {
					$catobj = new ZenpageCategory($cat['titlelink']);
					$count2++;
					if ($count2 != 1) {
						$categories = $categories . ", ";
					}
					$categories = $categories . $catobj->getTitle();
				}
				$thumb = "";
				$content = $obj->getContent();
				if ($obj->getTruncation()) {
					$shorten = true;
				}
				$date = zpFormattedDate(DATE_FORMAT, strtotime($item['date']));
				$type = 'news';
				break;
			case 'images':
				$obj = newImage(newAlbum($item['albumname']), $item['titlelink']);
				$categories = $item['albumname'];
				$title = $obj->getTitle();
				$link = html_encode($obj->getLink());
				$content = $obj->getDesc();
				if ($option == "with_latest_image_date") {
					$date = zpFormattedDate(DATE_FORMAT, $item['date']);
				} else {
					$date = zpFormattedDate(DATE_FORMAT, strtotime($item['date']));
				}
				$thumb = "<a href=\"" . $link . "\" title=\"" . html_encode(strip_tags($title)) . "\"><img src=\"" . html_encode(pathurlencode($obj->getThumb())) . "\" alt=\"" . html_encode(strip_tags($title)) . "\" /></a>\n";
				$type = "image";
				break;
			case 'albums':
				$obj = newAlbum($item['albumname']);
				$title = $obj->getTitle();
				$categories = "";
				$link = html_encode($obj->getLink());
				$thumb = "<a href=\"" . $link . "\" title=\"" . $title . "\"><img src=\"" . html_encode(pathurlencode($obj->getAlbumThumb())) . "\" alt=\"" . strip_tags($title) . "\" /></a>\n";
				$content = $obj->getDesc();
				$date = zpFormattedDate(DATE_FORMAT, strtotime($item['date']));
				$type = "album";
				break;
		}
		echo "<li>";
		if (!empty($thumb)) {
			echo $thumb;
		}
		echo "<h3><a href=\"" . $link . "\" title=\"" . strip_tags(html_encode($title)) . "\">" . $title . "</a></h3>\n";
		if ($showdate) {
			echo "<span class=\"latestnews-date\">" . $date . "</span>\n";
		}
		if ($showcontent) {
			echo "<span class=\"latestnews-desc\">" . getContentShorten($content, $contentlength, '', $readmore, $link) . "</span>\n";
		}
		if ($showcat AND $type != "album" && !empty($categories)) {
			echo "<span class=\"latestnews-cats\">(" . html_encode($categories) . ")</span>\n";
		}
		echo "</li>\n";
		if ($count == $number) {
			break;
		}
	}
	echo "</ul>\n";
}

?>