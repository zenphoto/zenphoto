<?php

/**
 * Zenpage general news template functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/* * ********************************************* */
/* News article functions
	/*********************************************** */

/**
 * Gets the latest news either only news articles or with the latest images or albums
 *
 * NOTE: This function excludes articles that are password protected via a category for not logged in users!
 *
 * @param int $number The number of news items to get
 * @param string $category Optional news articles by category (only "none" option)
 * @param bool $sticky place sticky articles at the front of the list
 * @param string $sortdirection 'asc' ascending otherwise descending
 * @return array
 */
function getLatestNews($number = 2, $category = '', $sticky = true, $sortdirection = 'desc') {
	global $_zp_zenpage, $_zp_current_zenpage_news;
	$sortdir = strtolower($sortdirection) != 'asc';
	if (empty($category)) {
		$latest = $_zp_zenpage->getArticles($number, NULL, true, NULL, $sortdir, $sticky, NULL);
	} else {
		$catobj = new ZenpageCategory($category);
		$latest = $catobj->getArticles($number, NULL, true, NULL, $sortdir, $sticky);
	}
	return $latest;
}

/**
 * Prints the latest news either only news articles or with the latest images or albums as a unordered html list
 *
 * NOTE: Latest images and albums require the image_album_statistic plugin
 *
 * @param int $number The number of news items to get
 * @param string $category Optional news articles by category (only "none" option"
 * @param bool $showdate If the date should be shown
 * @param bool $showcontent If the content should be shown
 * @param int $contentlength The lengths of the content
 * @param bool $showcat If the categories should be shown
 * @param string $readmore Text for the read more link, if empty the option value for "zenpage_readmore" is used
 * @param bool $sticky place sticky articles at the front of the list
 * @return string
 */
function printLatestNews($number = 5, $category = '', $showdate = true, $showcontent = true, $contentlength = 70, $showcat = true, $readmore = NULL, $sticky = true) {
	global $_zp_gallery, $_zp_current_zenpage_news;
	$latest = getLatestNews($number, $category, $sticky);
	echo "\n<ul id=\"latestnews\">\n";
	$count = 0;
	foreach ($latest as $item) {
		$count++;
		$category = "";
		$categories = "";

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
		$date = zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($item['date']));
		echo "<li>";
		echo "<h3><a href=\"" . $link . "\" title=\"" . getBare(html_encode($title)) . "\">" . $title . "</a></h3>\n";
		if ($showdate) {
			echo "<span class=\"latestnews-date\">" . $date . "</span>\n";
		}
		if ($showcontent) {
			echo "<span class=\"latestnews-desc\">" . getContentShorten($content, $contentlength, '', $readmore, $link) . "</span>\n";
		}
		if ($showcat && !empty($categories)) {
			echo "<span class=\"latestnews-cats\">(" . html_encode($categories) . ")</span>\n";
		}
		echo "</li>\n";
		if ($count == $number) {
			break;
		}
	}
	echo "</ul>\n";
}

/**
 * Returns the number of news articles.
 *
 * When in search context this is the count of the articles found. Otherwise
 * it is the count of articles that match the criteria.
 *
 * @param bool $total
 * @return int
 */
function getNumNews($total = false) {
	global $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_news_restore, $_zp_zenpage_articles, $_zp_gallery, $_zp_current_search;
	if ($total) {
		return count($_zp_zenpage->getArticles(0));
	} else if (in_context(ZP_SEARCH)) {
		return count($_zp_current_search->getArticles());
	} else {
		return count($_zp_zenpage->getArticles(0));
	}
}

/**
 * Returns the next news item on a page.
 * sets $_zp_current_zenpage_news to the next news item
 * Returns true if there is an new item to be shown
 *
 * @return bool
 */
function next_news() {
	global $_zp_zenpage, $_zp_current_zenpage_news, $_zp_current_zenpage_news_restore, $_zp_zenpage_articles, $_zp_current_category, $_zp_gallery, $_zp_current_search;
	if (is_null($_zp_zenpage_articles)) {
		if (in_context(ZP_SEARCH)) {
			//note: we do not know how to paginate the search page, so for now we will return all news articles
			$_zp_zenpage_articles = $_zp_current_search->getArticles(ZP_ARTICLES_PER_PAGE, NULL, true, NULL, NULL);
		} else {
			if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
				$_zp_zenpage_articles = $_zp_current_category->getArticles(ZP_ARTICLES_PER_PAGE, NULL, false, NULL, NULL);
			} else {
				$_zp_zenpage_articles = $_zp_zenpage->getArticles(ZP_ARTICLES_PER_PAGE, NULL, false, NULL, NULL);
			}
			if (empty($_zp_zenpage_articles)) {
				return NULL;
			}
		}
		$_zp_current_zenpage_news_restore = $_zp_current_zenpage_news;
	}
	if (!empty($_zp_zenpage_articles)) {
		$news = array_shift($_zp_zenpage_articles);
		if (is_array($news)) {
			add_context(ZP_ZENPAGE_NEWS_ARTICLE);
			$_zp_current_zenpage_news = new ZenpageNews($news['titlelink']);
			return true;
		}
	}

	$_zp_zenpage_articles = NULL;
	$_zp_current_zenpage_news = $_zp_current_zenpage_news_restore;
	rem_context(ZP_ZENPAGE_NEWS_ARTICLE);
	return false;
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
function printNewsArchive($class = 'archive', $yearclass = 'year', $monthclass = 'month', $activeclass = "archive-active", $yearsonly = false, $order = 'desc') {
	global $_zp_zenpage;
	if (!empty($class)) {
		$class = "class=\"$class\"";
	}
	if (!empty($yearclass)) {
		$yearclass = "class=\"$yearclass\"";
	}
	if (!empty($monthclass)) {
		$monthclass = "class=\"$monthclass\"";
	}
	if (!empty($activeclass)) {
		$activeclass = "class=\"$activeclass\"";
	}
	$datecount = $_zp_zenpage->getAllArticleDates($yearsonly, $order);
	$lastyear = "";
	$nr = 0;
	echo "\n<ul $class>\n";
	foreach($datecount as $key => $val) {
		$nr++;
		if ($key == '0000-00-01') {
			$year = "no date";
			$month = "";
		} else {
			if (extension_loaded('intl') && getOption('date_format_localized')) {
				$year = zpFormattedDate('yyyy', $key, true); 
				$month = zpFormattedDate('MMMM', $key, true);
			} else {
				$year = zpFormattedDate('Y', $key, false); 
				$month = zpFormattedDate('F', $key,  false);
			}
		}
		if ($lastyear != $year) {
			$lastyear = $year;
			if (!$yearsonly) {
				if ($nr != 1) {
					echo "</ul>\n</li>\n";
				}
				echo "<li $yearclass>$year\n<ul $monthclass>\n";
			}
		}
		if ($yearsonly) {
			$datetosearch = $key;
		} else {
			$datetosearch = zpFormattedDate('Y-F', $key);
		}
		if (getCurrentNewsArchive('plain') == $datetosearch) {
			$active = $activeclass;
		} else {
			$active = "";
		}
		if ($yearsonly) {
			echo "<li $active><a href=\"" . html_encode(getNewsArchivePath($key, 1)) . "\" title=\"" . $key . " (" . $val . ")\" rel=\"nofollow\">$key ($val)</a></li>\n";
		} else {
			echo "<li $active><a href=\"" . html_encode(getNewsArchivePath(substr($key, 0, 7), 1)) . "\" title=\"" . $month . " (" . $val . ")\" rel=\"nofollow\">$month ($val)</a></li>\n";
		}
	}
	if ($yearsonly) {
		echo "</ul>\n";
	} else {
		echo "</ul>\n</li>\n</ul>\n";
	}
}

/**
 * Gets the current select news date (year-month) or formatted
 *
 * @param string $mode "formatted" for a formatted date or "plain" for the pure year-month (for example "2008-09") archive date
 * @param string $format If $mode="formatted" a datetime format or if localized dates are enabled an ICU dateformat
 * @return string
 */
function getCurrentNewsArchive($mode = 'formatted', $format = 'F Y') {
	global $_zp_post_date;
	if (in_context(ZP_ZENPAGE_NEWS_DATE)) {
		$archivedate = $_zp_post_date;
		if ($mode == "formatted") {
			$archivedate = zpFormattedDate($format, $archivedate);
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
 * @param string $format If $mode="formatted" a datetime format or if localized dates are enabled an ICU dateformat
 * @return string
 */
function printCurrentNewsArchive($before = '', $mode = 'formatted', $format = 'F Y') {
	if ($date = getCurrentNewsArchive($mode, $format)) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo html_encode($date);
	}
}

/**
 * Prints the full link of the news index page (news page 1 by default)
 *
 * @param string $name The linktext
 * @param string $before The text to appear before the link text
 * @param string $archive Name to print for the news date archive link
 * @param int $page Page number to append
 */
function printNewsIndexURL($name = NULL, $before = '', $archive = NULL, $page = '') {
	global $_zp_post_date;
	if ($_zp_post_date) {
		if (is_null($archive)) {
			$name = '<em>' . gettext('Archive') . '</em>';
		} else {
			$name = getBare(html_encode($archive));
		}
		$link = zp_apply_filter('getLink', rewrite_path(_ARCHIVE_ . '/', "/index.php?p=archive"), 'archive.php', NULL);
	} else {
		if (is_null($name)) {
			$name = gettext('News');
		} else {
			$name = getBare(html_encode($name));
		}
		$link = getNewsIndexURL($page);
	}
	if ($before) {
		echo '<span class="beforetext">' . html_encode($before) . '</span>';
	}
	echo "<a href=\"" . html_encode($link) . "\" title=\"" . getBare($name) . "\">" . $name . "</a>";
}

/**
 * Returns path of news date archive
 *
 * @return string
 */
function getNewsArchivePath($date, $page) {
	$rewrite = '/' . _NEWS_ARCHIVE_ . '/' . $date . '/';
	$plain = "/index.php?p=news&date=$date";
	if ($page > 1) {
		$rewrite .=  $page . '/';
		$plain .= "&page=$page";
	}
	return zp_apply_filter('getLink', rewrite_path($rewrite, $plain), 'archive.php', $page);
}

/* * ********************************************************* */
/* News index / category / date archive pagination functions
	/********************************************************** */

function getNewsPathNav($page) {
	global $_zp_current_category, $_zp_post_date;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		return $_zp_current_category->getLink($page);
	}
	if (in_context(ZP_ZENPAGE_NEWS_DATE)) {
		return getNewsArchivePath($_zp_post_date, $page);
	}
	$rewrite = '/' . _NEWS_ . '/';
	$plain = 'index.php?p=news';
	if ($page > 1) {
		$rewrite .= $page . '/';
		$plain .= '&page=' . $page;
	}
	return zp_apply_filter('getLink', rewrite_path($rewrite, $plain), 'news.php', $page);
}

/**
 * Returns true there is a previous news page
 * 
 * @global int $_zp_page
 * @return bool
 */
function hasPrevNewsPage() {
	global $_zp_page;
	return $_zp_page > 1;
}

/**
 * Returns the url to the previous news page
 *
 * @return string
 */
function getPrevNewsPageURL() {
	global $_zp_page;
	if (hasPrevNewsPage()) {
		return getNewsPathNav($_zp_page - 1);
	} 
	return false;
}

/**
 * Prints the link to the previous news page
 *
 * @param string $prev The linktext
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printPrevNewsPageLink($prev = '« prev', $class = 'disabledlink') {
	global $_zp_zenpage, $_zp_page;
	if ($link = getPrevNewsPageURL()) {
		echo "<a href='" . html_encode($link) . "' title='" . gettext("Prev page") . " " . ($_zp_page - 1) . "' >" . html_encode($prev) . "</a>\n";
	} else {
		echo "<span class=\"$class\">" . html_encode($prev) . "</span>\n";
	}
}

/**
 * Returns true if there is a next news page
 * 
 * @global obj $_zp_zenpage
 * @global int $_zp_page
 * @return bool
 */
function hasNextNewsPage() {
	global $_zp_zenpage, $_zp_current_category, $_zp_page;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		$total_pages = $_zp_current_category->getTotalNewsPages();
	} else {
		$total_pages = $_zp_zenpage->getTotalNewsPages();
	}
	return $_zp_page < $total_pages;
}

/**
 * Returns the url to the next news page
 *
 * @return string
 */
function getNextNewsPageURL() {
	global $_zp_page;
	if (hasNextNewsPage()) {
		return getNewsPathNav($_zp_page + 1);
	}
	return false;
}

/**
 * Prints the link to the next news page
 *
 * @param string $next The linktext
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printNextNewsPageLink($next = 'next »', $class = 'disabledlink') {
	global $_zp_page;
	if ($link = getNextNewsPageURL()) {
		echo "<a href='" . html_encode($link) . "' title='" . gettext("Next page") . " " . ($_zp_page + 1) . "'>" . html_encode($next) . "</a>\n";
	} else {
		echo "<span class=\"$class\">" . html_encode($next) . "</span>\n";
	}
}

/**
 * Prints the page number list for news page navigation
 *
 * @param string $class The CSS class for the disabled link
 *
 * @return string
 */
function printNewsPageList($class = 'pagelist') {
	printNewsPageListWithNav("", "", false, $class, true);
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
function printNewsPageListWithNav($next, $prev, $nextprev = true, $class = 'pagelist', $firstlast = true, $navlen = 9) {
	global $_zp_zenpage, $_zp_current_category, $_zp_page;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		$total = $_zp_current_category->getTotalNewsPages();
	} else {
		$total = $_zp_zenpage->getTotalNewsPages();
	}
	if ($total > 1) {
		if ($navlen == 0)
			$navlen = $total;
		$extralinks = 2;
		if ($firstlast)
			$extralinks = $extralinks + 2;
		$len = floor(($navlen - $extralinks) / 2);
		$j = max(round($extralinks / 2), min($_zp_page - $len - (2 - round($extralinks / 2)), $total - $navlen + $extralinks - 1));
		$ilim = min($total, max($navlen - round($extralinks / 2), $_zp_page + floor($len)));
		$k1 = round(($j - 2) / 2) + 1;
		$k2 = $total - round(($total - $ilim) / 2);
		echo "<ul class=\"$class\">\n";
		if ($nextprev) {
			echo "<li class=\"prev\">";
			printPrevNewsPageLink($prev);
			echo "</li>\n";
		}
		if ($firstlast) {
			echo '<li class = "' . ($_zp_page == 1 ? 'current' : 'first') . '">';
			if ($_zp_page == 1) {
				echo "1";
			} else {
				echo '<a href = "' . html_encode(getNewsPathNav(1)) . '" title = "' . gettext("Page") . ' 1">1</a>';
			}
			echo "</li>\n";
			if ($j > 2) {
				echo "<li>";
				$linktext = ($j - 1 > 2) ? '...' : $k1;
				echo '<a href = "' . html_encode(getNewsPathNav($k1)) . '" title = "' . sprintf(ngettext('Page %u', 'Page %u', $k1), $k1) . '">' . $linktext . '</a>';
				echo "</li>\n";
			}
		}
		for ($i = $j; $i <= $ilim; $i++) {
			echo "<li" . (($i == $_zp_page) ? " class=\"current\"" : "") . ">";
			if ($i == $_zp_page) {
				echo $i;
			} else {
				echo '<a href = "' . html_encode(getNewsPathNav($i)) . '" title = "' . sprintf(ngettext('Page %1$u', 'Page %1$u', $i), $i) . '">' . $i . '</a>';
			}
			echo "</li>\n";
		}
		if ($i < $total) {
			echo "<li>";
			$linktext = ($total - $i > 1) ? '...' : $k2;
			echo '<a href = "' . html_encode(getNewsPathNav($k2)) . '" title = "' . sprintf(ngettext('Page %u', 'Page %u', $k2), $k2) . '">' . $linktext . '</a>';
			echo "</li>\n";
		}
		if ($firstlast && $i <= $total) {
			echo "\n  <li class=\"last\">";
			if ($_zp_page == $total) {
				echo $total;
			} else {
				echo '<a href = "' . html_encode(getNewsPathNav($total)) . '" title = "' . sprintf(ngettext('Page {%u}', 'Page {%u}', $total), $total) . '">' . $total . '</a>';
			}
			echo "</li>\n";
		}
		if ($nextprev) {
			echo '<li class = "next">';
			printNextNewsPageLink($next);
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
}

function getTotalNewsPages() {
	global $_zp_zenpage, $_zp_current_category;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		return $_zp_current_category->getTotalNewsPages();
	} else {
		return $_zp_zenpage->getTotalNewsPages();
	}
}