<?php
/**
 * Zenpage pages template functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

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
function getNumPages($total = false) {
	global $_zp_zenpage, $_zp_zenpage_pagelist, $_zp_current_search, $_zp_current_zenpage_page, $_zp_db;
	$addquery = '';
	if (!$total) {
		if (in_context(ZP_SEARCH)) {
			$_zp_zenpage_pagelist = $_zp_current_search->getPages();
			return count($_zp_zenpage_pagelist);
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			if (!zp_loggedin(ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS)) {
				$addquery = ' AND `show` = 1';
			}
			return $_zp_db->count('pages', 'WHERE parentid=' . $_zp_current_zenpage_page->getID() . $addquery);
		}
	}
	if (!zp_loggedin(ADMIN_RIGHTS | ZENPAGE_PAGES_RIGHTS)) {
		$addquery = ' WHERE `show` = 1';
	}
	return $_zp_db->count('pages', $addquery);
}

/**
 * Returns pages from the current page object/search/or parent pages based on context
 * Updates $_zp_zenpage_curent_page and returns true if there is another page to be delivered
 * @return boolean
 */
function next_page() {
	global $_zp_zenpage, $_zp_next_pagelist, $_zp_current_search, $_zp_current_zenpage_page, $_zp_current_page_restore;
	if (is_null($_zp_next_pagelist)) {
		if (in_context(ZP_SEARCH)) {
			$_zp_next_pagelist = $_zp_current_search->getPages(NULL, false, NULL, NULL, NULL);
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			if (!is_null($_zp_current_zenpage_page)) {
				$_zp_next_pagelist = $_zp_current_zenpage_page->getPages(NULL, false, NULL, NULL, NULL);
			}
		} else {
			$_zp_next_pagelist = $_zp_zenpage->getPages(NULL, true, NULL, NULL, NULL);
		}
		save_context();
		add_context(ZP_ZENPAGE_PAGE);
		$_zp_current_page_restore = $_zp_current_zenpage_page;
	}
	while (!empty($_zp_next_pagelist)) {
		$page = new ZenpagePage(array_shift($_zp_next_pagelist));
		$_zp_current_zenpage_page = $page;
		return true;
	}
	$_zp_next_pagelist = NULL;
	$_zp_current_zenpage_page = $_zp_current_page_restore;
	restore_context();
	return false;
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
function printPageTitle($before = NULL) {
	if ($title = getPageTitle()) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo html_encode($title);
	}
}

/**
 * Returns the raw title of a page.
 *
 * @return string
 */
function getBarePageTitle() {
	return getBare(getPageTitle());
}

/**
 * prints the raw title of a page.
 *
 * @return string
 */
function printBarePageTitle() {
	echo html_encode(getBarePageTitle());
}

/**
 * Returns titlelink of a page
 *
 * @return string
 */
function getPageTitleLink() {
	global $_zp_current_zenpage_page;
	if (is_Pages()) {
		return $_zp_current_zenpage_page->getName();
	}
}

/**
 * Prints titlelink of a page
 * !!!!!!!!!!NOT THE URL TO THE PAGE!!!!!!!!!!!!!
 *
 * @return string
 */
function printPageTitleLink() {
	global $_zp_current_zenpage_page;
	echo html_encode(getPageURL(getPageTitleLink()));
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
		return zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($d));
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
		$d = $_zp_current_zenpage_page->getLastChange();
		return zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($d));
	}
	return false;
}

/**
 * Prints the last change date of a page
 *
 * @param string $before The text you want to show before the link
 * @return string
 */
function printPageLastChangeDate($before) {
	echo html_encode($before . getPageLastChangeDate());
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
function getPageContent($titlelink = NULL, $published = true) {
	global $_zp_current_zenpage_page;
	if (is_Pages() && empty($titlelink)) {
		if (!$_zp_current_zenpage_page->checkAccess()) {
			return '<p>' . gettext('<em>This page is protected.</em>') . '</p>';
		}
		return $_zp_current_zenpage_page->getContent();
	}
	// print content of a page directly on a normal zenphoto theme page or any other page for example
	if (!empty($titlelink)) {
		$page = new ZenpagePage($titlelink);
		if ($page->isPublished() || ( !$page->isPublished() && ! $published)) {
			return $page->getContent();
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
function printPageContent($titlelink = NULL, $published = true) {
	global $_zp_current_zenpage_page;
	$content = filter::applyFilter('pagecontent_html', getPageContent($titlelink, $published), $_zp_current_zenpage_page);
	echo html_encodeTagged($content);
}

/**
 * Returns page extra content either of the current page or if requested by titlelink directly. If not both return false
 * Set the titlelink of a page to call a specific even un-published page ($published = false) as a gallery description or on another custom page for example
 *
 * @param string $titlelink the titlelink of the page to print the content from
 * @param bool $published If titlelink is set, set this to false if you want to call an un-published page's extra content. True is default
 * @return mixed
 */
function getPageExtraContent($titlelink = '', $published = true) {
	global $_zp_current_zenpage_page;
	if (is_Pages() && empty($titlelink)) {
		return $_zp_current_zenpage_page->getExtracontent();
	}
	// print content of a page directly on a normal zenphoto theme page for example
	if (!empty($titlelink)) {
		$page = new ZenpagePage($titlelink);
		if ($page->isPublished() || ( !$page->isPublished() && ! $published)) {
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
function printPageExtraContent($titlelink = NULL, $published = true) {
	echo getPageExtraContent($titlelink, $published);
}

/**
 * Gets the custom data field of the current page
 *
 * @return string
 */
function getPageCustomData() {
	global $_zp_current_zenpage_page;
	if (!is_null($_zp_current_zenpage_page)) {
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
 * Returns the sortorder of a page
 *
 * @return string
 */
function getPageSortorder() {
	global $_zp_current_zenpage_page;
	if (is_Pages()) {
		return $_zp_current_zenpage_page->getSortOrder();
	}
	return false;
}

/**
 * Returns full path to a specific page
 *
 * @return string
 */
function getPageURL($titlelink = '') {
	global $_zp_zenpage, $_zp_current_zenpage_page;
	if (empty($titlelink)) {
		$obj = $_zp_current_zenpage_page;
	} else {
		$obj = new ZenpagePage($titlelink);
	}
	return $obj->getLink();
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
function printPageURL($linktext = NULL, $titlelink = NULL, $prev = '', $next = '', $class = NULL) {
	if (!is_null($class)) {
		$class = 'class="' . $class . '"';
	}
	if (is_null($linktext)) {
		$linktext = getPageTitle();
	}
	echo $prev . "<a href=\"" . html_encode(getPageURL($titlelink)) . "\" $class title=\"" . html_encode($linktext) . "\">" . html_encode($linktext) . "</a>" . $next;
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
function printSubPagesExcerpts($excerptlength = NULL, $readmore = NULL, $shortenindicator = NULL) {
	global $_zp_current_zenpage_page;
	if (is_null($readmore)) {
		$readmore = get_language_string(ZP_READ_MORE);
	}
	$pages = $_zp_current_zenpage_page->getPages();
	$subcount = 0;
	if (is_null($excerptlength)) {
		$excerptlength = ZP_SHORTEN_LENGTH;
	}
	foreach ($pages as $page) {
		$pageobj = new ZenpagePage($page['titlelink']);
		if ($pageobj->getParentID() == $_zp_current_zenpage_page->getID()) {
			$subcount++;
			$pagetitle = html_encode($pageobj->getTitle());
			$pagecontent = $pageobj->getContent();
			if ($pageobj->checkAccess()) {
				$pagecontent = getContentShorten($pagecontent, $excerptlength, $shortenindicator, $readmore, $pageobj->getLink());
			} else {
				$pagecontent = '<p><em>' . gettext('This page is password protected') . '</em></p>';
			}
			echo '<div class="pageexcerpt">';
			echo '<h4><a href="' . html_encode($pageobj->getLink()) . '" title="' . getBare($pagetitle) . '">' . $pagetitle . '</a></h4>';
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
function printPageMenu($option = 'list', $css_id = NULL, $css_class_topactive = NULL, $css_class = NULL, $css_class_active = NULL, $indexname = NULL, $showsubs = 0, $startlist = true, $limit = NULL) {
	printNestedMenu($option, 'pages', false, $css_id, $css_class_topactive, $css_class, $css_class_active, $indexname, $showsubs, $startlist, $limit);
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
	if (!empty($titlelink)) {
		load_zenpage_pages($titlelink);
		return in_context(ZP_ZENPAGE_PAGE);
	}
	return false;
}