<?php
/**
 * Zenpage general template functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

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
 * returns the "sticky" value of the news article
 * @param obj $newsobj optional news object to check directly outside news context
 * @return bool
 */
function stickyNews($newsobj = NULL) {
	global $_zp_current_zenpage_news;
	if (is_null($newsobj)) {
		$newsobj = $_zp_current_zenpage_news;
	}
	return $newsobj->getSticky();
}


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
 * 										 "random" for pages and news articles
 * @param boolean $sortdirection true for descending (default); false for ascending
 * @return array
 */
function getZenpageStatistic($number = 10, $option = "all", $mode = "popular", $sortdirection = true) {
	global $_zp_zenpage;
	switch (strtolower($sortdirection)) {
		case 'asc':
			$sortdirection = false;
			trigger_error(gettext('getZenpageStatistic - "asc" for the $sortdirection is deprecated since ZenphotoCMS 1.5.8. Use false instead.'), E_USER_NOTICE);
			break;
		case 'desc':
			trigger_error(gettext('getZenpageStatistic - "desc" for the $sortdirection is deprecated since ZenphotoCMS 1.5.8. Use true instead.'), E_USER_NOTICE);
			$sortdirection = true;
			break;
	}
	$statsarticles = array();
	$statscats = array();
	$statspages = array();
	if ($option == "all" || $option == "news") {
		$articles = $_zp_zenpage->getArticles($number, NULL, true, $mode, $sortdirection, false);
		$counter = 0;
		$statsarticles = array();
		foreach ($articles as $article) {
			$counter++;
			$obj = new ZenpageNews($article['titlelink']);
			$statsarticles[$counter] = array(
							"id"					 => $obj->getID(),
							"title"				 => $obj->getTitle(),
							"titlelink"		 => $article['titlelink'],
							"hitcounter"	 => $obj->getHitcounter(),
							"total_votes"	 => $obj->getTotal_votes(),
							"rating"			 => $obj->getRating(),
							"content"			 => $obj->getContent(),
							"date"				 => $obj->getDateTime(),
							"type"				 => "News"
			);
		}
		$stats = $statsarticles;
	}
	if (($option == "all" || $option == "categories") && $mode != "mostrated" && $mode != "toprated") {
		$categories = $_zp_zenpage->getAllCategories(true, $mode, $sortdirection);
		$counter = 0;
		$statscats = array();
		foreach ($categories as $cat) {
			$counter++;
			$statscats[$counter] = array(
							"id"					 => $cat['id'],
							"title"				 => html_encode(i18n::getLanguageString($cat['title'])),
							"titlelink"		 => getNewsCategoryURL($cat['titlelink']),
							"hitcounter"	 => $cat['hitcounter'],
							"total_votes"	 => "",
							"rating"			 => "",
							"content"			 => '',
							"date"				 => '',
							"type"				 => "Category"
			);
		}
		$stats = $statscats;
	}
	if ($option == "all" || $option == "pages") {
		$pages = $_zp_zenpage->getPages(NULL, false, $number, $mode, $sortdirection);
		$counter = 0;
		$statspages = array();
		foreach ($pages as $page) {
			$counter++;
			$pageobj = new ZenpagePage($page['titlelink']);
			$statspages[$counter] = array(
							"id"					 => $pageobj->getID(),
							"title"				 => $pageobj->getTitle(),
							"titlelink"		 => $page['titlelink'],
							"hitcounter"	 => $pageobj->getHitcounter(),
							"total_votes"	 => $pageobj->get('total_votes'),
							"rating"			 => $pageobj->get('rating'),
							"content"			 => $pageobj->getContent(),
							"date"				 => $pageobj->getDateTime(),
							"type"				 => "Page"
			);
		}
		$stats = $statspages;
	}
	if ($option == "all") {
		$stats = array_merge($statsarticles, $statscats, $statspages);
		if ($mode == 'random') {
			shuffle($stats);
		} else {
			$stats = sortMultiArray($stats, $mode, $sortdirection);
		}
	}
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
 * @param bool $sortdir True fro descending (default), false for ascending
 */
function printZenpageStatistic($number = 10, $option = "all", $mode = "popular", $showstats = true, $showtype = true, $showdate = true, $showcontent = true, $contentlength = 40, $sortdir = true) {
	$stats = getZenpageStatistic($number, $option, $mode, $sortdir);
	$contentlength = sanitize_numeric($contentlength);
	switch ($mode) {
		case 'popular':
			$cssid = "'zenpagemostpopular'";
			break;
		case 'mostrated':
			$cssid = "'zenpagemostrated'";
			break;
		case 'toprated':
			$cssid = "'zenpagetoprated'";
			break;
		case 'random':
			$cssid = "'zenpagerandom'";
			break;
	}
	echo "<ul id=$cssid>";
	foreach ($stats as $item) {
		switch ($mode) {
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
		switch ($item['type']) {
			case 'Page':
				$titlelink = html_encode(getPageURL($item['titlelink']));
			case 'News':
				$titlelink = html_encode(getNewsURL($item['titlelink']));
				break;
			case 'Category':
				$titlelink = html_encode(getNewsCategoryURL($item['titlelink']));
				break;
		}
		echo '<li><a href = "' . $titlelink . '" title = "' . html_encode(getBare($item['title'])) . '"><h3>' . $item['title'];
		echo '<small>';
		if ($showtype) {
			echo ' [' . $item['type'] . ']';
		}
		if ($showstats && ($item['type'] != 'Category' && $mode != 'mostrated' && $mode != 'toprated')) {
			echo ' (' . $statsvalue . ')';
		}
		echo '</small>';
		echo '</h3></a>';
		if ($showdate && $item['type'] != 'Category') {
			echo "<p>" . zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($item['date'])) . "</p>";
		}
		if ($showcontent && $item['type'] != 'Category') {
			echo '<p>' . truncate_string($item['content'], $contentlength) . '</p>';
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
function printMostPopularItems($number = 10, $option = "all", $showstats = true, $showtype = true, $showdate = true, $showcontent = true, $contentlength = 40) {
	printZenpageStatistic($number, $option, "popular", $showstats, $showtype, $showdate, $showcontent, $contentlength);
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
function printMostRatedItems($number = 10, $option = "all", $showstats = true, $showtype = true, $showdate = true, $showcontent = true, $contentlength = 40) {
	printZenpageStatistic($number, $option, "mostrated", $showstats, $showtype, $showdate, $showcontent, $contentlength);
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
function printTopRatedItems($number = 10, $option = "all", $showstats = true, $showtype = true, $showdate = true, $showcontent = true, $contentlength = 40) {
	printZenpageStatistic($number, $option, "toprated", $showstats, $showtype, $showdate, $showcontent, $contentlength);
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
function printNestedMenu($option = 'list', $mode = NULL, $counter = TRUE, $css_id = NULL, $css_class_topactive = NULL, $css_class = NULL, $css_class_active = NULL, $indexname = NULL, $showsubs = 0, $startlist = true, $limit = NULL) {
	global $_zp_zenpage, $_zp_gallery_page, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
	if (is_null($limit)) {
		$limit = MENU_TRUNCATE_STRING;
	}
	if($mode == 'allcategories') {
		$mode = 'categories';
	}
	if (is_null($css_id)) {
		switch ($mode) {
			case 'pages':
				$css_id = 'menu_pages';
				break;
			case 'categories':
				$css_id = 'menu_categories';
				break;
		}
	}
	if (is_null($css_class_topactive)) {
		$css_class_topactive = 'menu_topactive';
	}
	if (is_null($css_class)) {
		$css_class = 'submenu';
	}
	if (is_null($css_class_active)) {
		$css_class_active = 'menu-active';
	}
	if ($showsubs === true)
		$showsubs = 9999999999;
	switch ($mode) {
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
			$items = $_zp_zenpage->getAllCategories();
			if (is_object($_zp_current_category) && $mode == 'categories') {
				$currentitem_sortorder = $_zp_current_category->getSortOrder();
				$currentitem_id = $_zp_current_category->getID();
				$currentitem_parentid = $_zp_current_category->getParentID();
			} else {
				$currentitem_sortorder = '';
				$currentitem_id = NULL;
				$currentitem_parentid = NULL;
			}
			break;
	}

	// don't highlight current pages or foldout if in search mode as next_page() sets page context
	if (in_context(ZP_SEARCH) && $mode == 'pages') { // categories are not searched
		$css_class_topactive = "";
		$css_class_active = "";
		rem_context(ZP_ZENPAGE_PAGE);
	}
	if (0 == count($items) + (int) ($mode == 'categories')) {
		return; // nothing to do
	}
	$startlist = $startlist && !($option == 'omit-top' || $option == 'list-sub');
	if ($startlist) {
		echo '<ul id="' . $css_id . '">';
	}
	// if index link and if if with count
	if (!empty($indexname)) {
		if ($limit) {
			$display = shortenContent($indexname, $limit, MENU_TRUNCATE_INDICATOR);
		} else {
			$display = $indexname;
		}
		switch ($mode) {
			case 'pages':
				if ($_zp_gallery_page == "index.php") {
					echo '<li class="' . $css_class_topactive . '">' . html_encode($display) . '</li>';
				} else {
					echo "<li><a href='" . html_encode(getGalleryIndexURL()) . "' title='" . html_encode($indexname) . "'>" . html_encode($display) . "</a></li>";
				}
				break;
			case 'categories':
				if (($_zp_gallery_page == "news.php") && !is_NewsCategory() && !is_NewsArchive() && !is_NewsArticle()) {
					echo '<li class="' . $css_class_topactive . '">' . html_encode($display);
				} else {
					echo "<li><a href=\"" . html_encode(getNewsIndexURL()) . "\" title=\"" . html_encode($indexname) . "\">" . html_encode($display) . "</a>";
				}
				if ($counter) {
					save_context();
					rem_context(ZP_ZENPAGE_NEWS_DATE);
					$totalcount = $_zp_zenpage->getTotalArticles();
					restore_context();
					echo ' <span style="white-space:nowrap;"><small>(' . sprintf(ngettext('%u article', '%u articles', $totalcount), $totalcount) . ')</small></span>';
				}
				echo "</li>\n";
				break;
		}
	}
	$baseindent = max(1, count(explode("-", strval($currentitem_sortorder))));
	$indent = 1;
	$open = array($indent => 0);
	$parents = array(NULL);
	$order = explode('-', strval($currentitem_sortorder));
	$mylevel = count($order);
	$myparentsort = array_shift($order);
	for ($c = 0; $c <= $mylevel; $c++) {
		$parents[$c] = NULL;
	}
	foreach ($items as $item) {

		$password_class = '';
		switch ($mode) {
			case 'pages':
				$catcount = 1; //	so page items all show.
				$pageobj = new ZenpagePage($item['titlelink']);
				$itemtitle = $pageobj->getTitle();
				$itemsortorder = $pageobj->getSortOrder();
				$itemid = $pageobj->getID();
				$itemparentid = $pageobj->getParentID();
				$itemtitlelink = $pageobj->getName();
				$itemurl = $pageobj->getLink();
				$count = '';
				if (!$pageobj->isMyItem(LIST_RIGHTS) && $pageobj->isProtected()) {
					$password_class = ' has_password';
				}
				break;
			case 'categories':
				$catobj = new ZenpageCategory($item['titlelink']);
				$itemtitle = $catobj->getTitle();
				$itemsortorder = $catobj->getSortOrder();
				$itemid = $catobj->getID();
				$itemparentid = $catobj->getParentID();
				$itemtitlelink = $catobj->getName();
				$itemurl = $catobj->getLink();
				$catcount = count($catobj->getArticles());
				if (!$catobj->isMyItem(LIST_RIGHTS) && $catobj->isProtected()) {
					$catcount = 1; // count for protected is 0 but we want to show them
					$counter = false; // disable counter for protected items 
					$password_class = ' has_password';
				}
				if ($counter) {
					$count = ' <span style="white-space:nowrap;"><small>(' . sprintf(ngettext('%u article', '%u articles', $catcount), $catcount) . ')</small></span>';
				} else {
					$count = '';
				}

				
				break;
		}
		if ($catcount) {

			$level = max(1, count(explode('-', strval($itemsortorder))));
			$process = (($level <= $showsubs && $option == "list") // user wants all the pages whose level is <= to the parameter
							|| ($option == 'list' || $option == 'list-top') && $level == 1 // show the top level
							|| (($option == 'list' || ($option == 'omit-top' && $level > 1)) && (($itemid == $currentitem_id) // current page
							|| ($itemparentid == $currentitem_id) // offspring of current page
							|| ($level < $mylevel && $level > 1 && (strpos($itemsortorder, $myparentsort) === 0) )// direct ancestor
							|| (($level == $mylevel) && ($currentitem_parentid == $itemparentid)) // sibling
							)
							) || ($option == 'list-sub' && ($itemparentid == $currentitem_id) // offspring of the current page
							)
							);

			if ($process) {
				if ($level > $indent) {
					echo "\n" . str_pad("\t", $indent, "\t") . '<ul class="' . $css_class . '">' . "\n";
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
						echo str_pad("\t", $indent, "\t") . "</ul>\n";
					}
				} else { // level == indent, have not changed
					if ($open[$indent]) { // level = indent
						echo str_pad("\t", $indent, "\t") . "</li>\n";
						$open[$indent]--;
					} else {
						echo "\n";
					}
				}
				if ($open[$indent]) { // close an open LI if it exists
					echo "</li>\n";
					$open[$indent]--;
				}
				echo str_pad("\t", $indent - 1, "\t");
				$open[$indent]++;
				$parents[$indent] = $itemid;
				if ($level == 1) { // top level
					$class = $css_class_topactive . $password_class;
				} else {
					$class = $css_class_active . $password_class;
				}
				if (!is_null($_zp_current_zenpage_page)) {
					$gettitle = $_zp_current_zenpage_page->getTitle();
					$getname = $_zp_current_zenpage_page->getName();
				} else if (!is_null($_zp_current_category)) {
					$gettitle = $_zp_current_category->getTitle();
					$getname = $_zp_current_category->getName();
				} else {
					$gettitle = '';
					$getname = '';
				}
				$current = "";
				if ($itemtitlelink == $getname && !in_context(ZP_SEARCH)) {
					switch ($mode) {
						case 'pages':
							if ($_zp_gallery_page == 'pages.php') {
								$current = $class;
							}
							break;
						case 'categories':
							if ($_zp_gallery_page == 'news.php') {
								$current = $class;
							}
							break;
					}
				}
				if (empty($current)) {
					$current = trim($password_class);
				}
				if ($limit) {
					$itemtitle = shortenContent($itemtitle, $limit, MENU_TRUNCATE_INDICATOR);
				}
				echo '<li class="' . $current . '"><a href="' . html_encode($itemurl) . '" title="' . html_encode(getBare($itemtitle)) . '">' . html_encode($itemtitle) . '</a>' . $count;
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
		echo str_pad("\t", $indent, "\t") . "</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
		$open[$indent]--;
	} else {
		echo "\n";
	}
	if ($startlist) {
		echo "</ul>\n";
	}
}

/**
 * Prints the parent items breadcrumb navigation for pages or categories
 *
 * @param string $before Text to place before the breadcrumb item
 * @param string $after Text to place after the breadcrumb item
 */
function printZenpageItemsBreadcrumb($before = NULL, $after = NULL) {
	global $_zp_current_zenpage_page, $_zp_current_zenpage_news, $_zp_current_category;
	if (is_NewsPage()) {
		$page = '';
		if (is_NewsArticle()) {
			$page = $_zp_current_zenpage_news->getNewsLoopPage();
		}
		$archive = '';
		if(is_NewsArchive()) {
			$archive = null;
		}
		printNewsIndexURL(NULL, '', $archive, $page);
	}
	if (is_Pages() || is_NewsCategory()) {
		$parentitems = array();
		if (is_Pages()) {
			$parentitems = $_zp_current_zenpage_page->getParents();
		}
		if (is_NewsCategory()) {
			$parentitems = $_zp_current_category->getParents();
		}
		foreach ($parentitems as $item) {
			if (is_Pages()) {
				$pageobj = new ZenpagePage($item);
				$parentitemurl = html_encode($pageobj->getLink());
				$parentitemtitle = $pageobj->getTitle();
			}
			if (is_NewsCategory()) {
				$catobj = new ZenpageCategory($item);
				$parentitemurl = $catobj->getLink();
				$parentitemtitle = $catobj->getTitle();
			}
			if ($before) {
				echo '<span class="beforetext">' . html_encode($before) . '</span>';
			}
			echo"<a href='" . $parentitemurl . "'>" . html_encode($parentitemtitle) . "</a>";
			if ($after) {
				echo '<span class="aftertext">' . html_encode($after) . '</span>';
			}
		}
	}
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
function getLatestZenpageComments($number, $type = "all", $itemID = "") {
	global $_zp_db;
	$itemID = sanitize_numeric($itemID);
	$number = sanitize_numeric($number);

	if ($type == 'all' || $type == 'news') {
		$newspasswordcheck = "";
		if (zp_loggedin(MANAGE_ALL_NEWS_RIGHTS)) {
			$newsshow = '';
		} else {
			$newsshow = 'news.show=1 AND';
			$newscheck = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('news') . " ORDER BY date");
			foreach ($newscheck as $articlecheck) {
				$obj = new ZenpageNews($articlecheck['titlelink']);
				if ($obj->isVisible()) {
					$newsshow = '';
				} else {
					$excludenews = " AND news.id != " . $articlecheck['id'];
					$newspasswordcheck = $newspasswordcheck . $excludenews;
				}
			}
		}
	}
	if ($type == 'all' || $type == 'page') {
		$pagepasswordcheck = "";
		if (zp_loggedin(MANAGE_ALL_PAGES_RIGHTS)) {
			$pagesshow = '';
		} else {
			$pagesshow = 'pages.show=1 AND';
			$pagescheck = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('pages') . " ORDER BY date");
			foreach ($pagescheck as $pagecheck) {
				$obj = new ZenpagePage($pagecheck['titlelink']);
				if ($obj->isVisible()) {
					$pagesshow = '';
				} else {
					$excludepages = " AND pages.id != " . $pagecheck['id'];
					$pagepasswordcheck = $pagepasswordcheck . $excludepages;
				}
			}
		}
	}
	switch ($type) {
		case "news":
			$whereNews = " WHERE $newsshow news.id = " . $itemID . " AND c.ownerid = news.id AND c.type = 'news' AND c.private = 0 AND c.inmoderation = 0" . $newspasswordcheck;
			break;
		case "page":
			$wherePages = " WHERE $pagesshow pages.id = " . $itemID . " AND c.ownerid = pages.id AND c.type = 'pages' AND c.private = 0 AND c.inmoderation = 0" . $pagepasswordcheck;
			break;
		case "all":
			$whereNews = " WHERE $newsshow c.ownerid = news.id AND c.type = 'news' AND c.private = 0 AND c.inmoderation = 0" . $newspasswordcheck;
			$wherePages = " WHERE $pagesshow c.ownerid = pages.id AND c.type = 'pages' AND c.private = 0 AND c.inmoderation = 0" . $pagepasswordcheck;
			break;
	}
	$comments_news = array();
	$comments_pages = array();
	if ($type == "all" OR $type == "news") {
		$comments_news = $_zp_db->queryFullArray("SELECT c.id, c.name, c.type, c.website,"
						. " c.date, c.anon, c.comment, news.title, news.titlelink FROM " . $_zp_db->prefix('comments') . " AS c, " . $_zp_db->prefix('news') . " AS news "
						. $whereNews
						. " ORDER BY c.id DESC LIMIT $number");
	}
	if ($type == "all" OR $type == "page") {
		$comments_pages = $_zp_db->queryFullArray("SELECT c.id, c.name, c.type, c.website,"
						. " c.date, c.anon, c.comment, pages.title, pages.titlelink FROM " . $_zp_db->prefix('comments') . " AS c, " . $_zp_db->prefix('pages') . " AS pages "
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
function zenpageAlbumImage($albumname, $imagename = NULL, $size = NULL, $linkalbum = false) {
	global $_zp_gallery;
	echo '<br />';
	$album = AlbumBase::newAlbum($albumname);
	if ($album->loaded) {
		if (is_null($size)) {
			$size = floor(getOption('image_size') * 0.5);
		}
		$image = NULL;
		if (is_null($imagename)) {
			$linkalbum = true;
			$image = $album->getAlbumThumbImage();
		} else {
			$image = Image::newImage($album, $imagename);
		}
		if ($image && $image->loaded) {
			makeImageCurrent($image);
			if ($linkalbum) {
				rem_context(ZP_IMAGE);
				echo '<a href="' . html_encode($album->getLink()) . '"   title="' . sprintf(gettext('View the %s album'), $albumname) . '">';
				add_context(ZP_IMAGE);
				printCustomSizedImage(sprintf(gettext('View the album %s'), $albumname), $size);
				rem_context(ZP_IMAGE | ZP_ALBUM);
				echo '</a>';
			} else {
				echo '<a href="' . html_encode(getImageURL()) . '" title="' . sprintf(gettext('View %s'), $imagename) . '">';
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

