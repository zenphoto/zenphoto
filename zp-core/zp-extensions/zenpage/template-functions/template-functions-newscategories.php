<?php

/**
 * Zenpage news categories template functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/**
 * Prints the title of the currently selected news category
 *
 * @param string $before insert what you want to be show before it
 */
function printCurrentNewsCategory($before = '') {
	global $_zp_current_category;
	if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo html_encode($_zp_current_category->getTitle());
	}
}

/**
 * Gets the description of the current news category
 *
 * @return string
 */
function getNewsCategoryDesc() {
	global $_zp_current_category;
	if (!is_null($_zp_current_category)) {
		return $_zp_current_category->getDesc();
	}
}

/**
 * Prints the description of the news category
 *
 */
function printNewsCategoryDesc() {
	global $_zp_current_category;
	$desc = filter::applyFilter('categorydesc_html', getNewsCategoryDesc(), $_zp_current_category);
	echo html_encodeTagged($desc);
}

/**
 * Gets the custom data field of the current news category
 *
 * @return string
 */
function getNewsCategoryCustomData() {
	global $_zp_current_category;
	if (!is_null($_zp_current_category)) {
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
 * Gets the categories of the current news article
 *
 * @return array
 */
function getNewsCategories() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		$categories = $_zp_current_zenpage_news->getCategories();
		return $categories;
	}
	return false;
}

/**
 * Prints the categories of current article as a unordered html list
 *
 * @param string $separator A separator to be shown between the category names if you choose to style the list inline
 * @param string $class The CSS class for styling
 * @return string
 */
function printNewsCategories($separator = '', $before = '', $class = '') {
	$categories = getNewsCategories();
	$catcount = count($categories);
	if ($catcount != 0) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo "<ul class=\"$class\">\n";
		$count = 0;
		if ($separator) {
			$separator = '<span class="betweentext">' . html_encode($separator) . '</span>';
		}
		foreach ($categories as $cat) {
			$count++;
			$catobj = new ZenpageCategory($cat['titlelink']);
			if ($count >= $catcount) {
				$separator = "";
			}
			echo "<li><a href=\"" . $catobj->getLink() . "\" title=\"" . html_encode($catobj->getTitle()) . "\">" . $catobj->getTitle() . '</a>' . $separator . "</li>\n";
		}
		echo "</ul>\n";
	}
}

/**
 * Prints all news categories as a unordered html list
 *
 * @param string $newsindex How you want to call the link the main news page without a category, leave empty if you don't want to print it at all.
 * @param bool $counter TRUE or FALSE (default TRUE). If you want to show the number of articles behind the category name within brackets,
 * @param string $css_id The CSS id for the list
 * @param string $css_class_topactive The css class for the active menu item
 * @param bool $startlist set to true to output the UL tab
 * @param string $css_class CSS class of the sub level list(s)
 * @param string $css_class_active CSS class of the sub level list(s)
 * @param string $option The mode for the menu:
 * 												"list" context sensitive toplevel plus sublevel pages,
 * 												"list-top" only top level pages,
 * 												"omit-top" only sub level pages
 * 												"list-sub" lists only the current pages direct offspring
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param int $limit truncation of display text
 * @return string
 */
function printAllNewsCategories($newsindex = 'All news', $counter = TRUE, $css_id = '', $css_class_topactive = '', $startlist = true, $css_class = '', $css_class_active = '', $option = 'list', $showsubs = false, $limit = NULL) {
	printNestedMenu($option, 'categories', $counter, $css_id, $css_class_topactive, $css_class, $css_class_active, $newsindex, $showsubs, $startlist, $limit);
}

/**
 * Returns the full path to a news category
 *
 * @param string $cat The category titlelink
 *
 * @return string
 */
function getNewsCategoryURL($cat = NULL) {
	global $_zp_zenpage, $_zp_current_category;
	if (empty($cat)) {
		$obj = $_zp_current_category->getName();
	} else {
		$obj = new ZenpageCategory($cat);
	}
	return $obj->getLink(1);
}

/**
 * Prints the full link to a news category
 *
 * @param string $before If you want to print text before the link
 * @param string $catlink The category link of a category
 *
 * @return string
 */
function printNewsCategoryURL($before = '', $catlink = '') {
	$catobj = new ZenpageCategory($catlink);
	echo "<a href=\"" . html_encode($catobj->getLink()) . "\" title=\"" . html_encode($catobj->getTitle()) . "\">";
	if ($before) {
		echo '<span class="beforetext">' . html_encode($before) . '</span>';
	}
	echo html_encode($catobj->getTitle()) . "</a>";
}