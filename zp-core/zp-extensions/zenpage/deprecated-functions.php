<?php

/*
 * These are the Zenpage functions which have been deprecated
 */

class Zenpage_internal_deprecations {

  /**
   * @deprecated
   * @since 1.4.6
   */
  public static function getNextPrevNews() {
    deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.').gettext('This includes getNextPrevNews(), getNextNewsURL(), printNextNewsLink(), getPrevNewsURL() and printPrevNewsLink().'));
  }

  /**
   * @deprecated
   * @since 1.4.6
   */
  public static function getIndex() {
    deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType(), setSortDirection() and setSortSticky() object methods at the head of your script.'));
  }

  /**
   * @deprecated
   * @since 1.4.6
   */
  public static function getPrevArticle() {
    deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType(), setSortDirection() and setSortSticky() object methods at the head of your script.'));
  }

  /**
   * @deprecated
   * @since 1.4.6
   */
  public static function getNextArticle() {
    deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType(), setSortDirection() and setSortSticky() object methods at the head of your script.'));
  }

  /**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function getLatestNews() {
		deprecated_functions::notify(gettext('CombiNews is deprecated. Remove the  "$option" parameter. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function printLatestNews() {
		deprecated_functions::notify(gettext('CombiNews is deprecated. Remove the  "$option" parameter. See the <a href="http://www.zenphoto.org/news/zenphoto-1.4.6">Zenphoto 1.4.6 release notes</a>.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function next_news() {
		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function next_page() {
		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getNewsLink() {
		deprecated_functions::notify(gettext('Use the getLink method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getCategoryLink() {
		deprecated_functions::notify(gettext('Use the getLink method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getPageLink() {
		deprecated_functions::notify(gettext('Use the getLink method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getPagesLinkPath() {
		deprecated_functions::notify(gettext('Create an object and use its getLink method.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getNewsTitlePath() {
		deprecated_functions::notify(gettext('Create an object and use its getLink method.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getNewsCategoryPath() {
		deprecated_functions::notify(gettext('Create an object and use its getLink method.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getNewsArchivePath() {
		deprecated_functions::notify(gettext('Use getNewsArchivePath().'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getNewsIndexURL() {
		deprecated_functions::notify(gettext('The Zenpage class method is deprecated. Use the global function getNewsIndexURL().'));
	}

	/**
	 * @deprecated
	 * @since 1.4.5
	 */
	static function getSubPages() {
		deprecated_functions::notify(gettext('Use the Zenpage Page class->getPages() method.'));
	}

}

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
		$newsurl = getNewsURL();
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
	$protocol = PROTOCOL . '://';
	if (empty($lang)) {
		$lang = getOption("locale");
	}
	if ($option == 'Category') {
		if (!is_null($categorylink)) {
			$categorylink = '&amp;category=' . html_encode($categorylink);
		} elseif (empty($categorylink) AND ! is_null($_zp_current_category)) {
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
	deprecated_functions::notify(gettext('use printLatestComments($number, $shorten, $type, $itemID, $id, $shortenindicator);'));
	printLatestComments($number, $shorten, $type, $itemID, $id, $shortenindicator);
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
		return rewrite_path(html_encode(getNewsAlbumName()), "index.php?album=" . html_encode(getNewsAlbumName()));
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
 * @deprecated
 * @since 1.4.6
 */
function getPageLinkPath($titlelink) {
	deprecated_functions::notify(gettext('Create an object and use the object getLink'));
	$obj = new ZenpagePage($titlelink);
	return $obj->getLink();
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getNewsTitlePath($titlelink) {
	deprecated_functions::notify(gettext('Create an object and use the object getLink'));
	$obj = new ZenpageNews($titlelink);
	return $obj->getLink();
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getNewsCategoryPath($category, $page) {
	deprecated_functions::notify(gettext('Create an object and use the object getLink'));
	$obj = new ZenpageCategory($category);
	return $obj->getLink($page);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getNewsTitleLink() {
	deprecated_functions::notify(gettext('use getNewsURL()'));
	return getNewsURL();
}

/**
 * @deprecated
 * @since 1.4.6
 */
function printNewsTitleLink($before = '') {
	deprecated_functions::notify(gettext('use printNewsURL()'));
	printNewsURL($before);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getNewsLink($titlelink = '') {
	deprecated_functions::notify(gettext('use getNewsURL()'));
	return getNewsURL($titlelink);
	global $_zp_current_zenpage_news;
}

/**
 * @deprecated
 * @since 1.4.6
 */
function getPageLinkURL($titlelink = '') {
	deprecated_functions::notify(gettext('use getPageURL()'));
	return getPageURL($titlelink);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function printPageLinkURL($linktext, $titlelink, $prev = '', $next = '', $class = NULL) {
	deprecated_functions::notify(gettext('use printPageURL()'));
	printPageURL($linktext, $titlelink, $prev, $next, $class);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function printNewsLink($before = '') {
	deprecated_functions::notify(gettext('use printNewsURL()'));
	printNewsURL($before);
}

/**
 * @deprecated
 * @since 1.4.6
 */
function zenpageOpenedForComments() {
	deprecated_functions::notify(gettext("use the object’s getCommentsAllowed() method"));
	global $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	if (is_NewsArticle()) {
		$obj = $_zp_current_zenpage_news;
	}
	if (is_Pages()) {
		$obj = $_zp_current_zenpage_page;
	}
	return $obj->getCommentsAllowed();
}

?>