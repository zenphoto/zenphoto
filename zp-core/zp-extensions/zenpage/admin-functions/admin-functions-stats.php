<?php

/**
 * General Zenpage admin statistics functions
 * 
 * @since 1.7 separated from zenpage-admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/**
 * returns an array of how many pages, articles, categories and news or pages comments we got.
 *
 * @param string $option What the statistic should be shown of: "news", "pages", "categories"
 */
function getNewsPagesStatistic($option) {
	global $_zp_zenpage;
	switch ($option) {
		case "news":
			$items = $_zp_zenpage->getArticles();
			$type = gettext("Articles");
			break;
		case "pages":
			$items = $_zp_zenpage->getPages(false);
			$type = gettext("Pages");
			break;
		case "categories":
			$type = gettext("Categories");
			$items = $_zp_zenpage->getAllCategories(false);
			break;
	}
	$total = count($items);
	$pub = 0;
	foreach ($items as $item) {
		switch ($option) {
			case "news":
				$itemobj = new ZenpageNews($item['titlelink']);
				break;
			case "pages":
				$itemobj = new ZenpagePage($item['titlelink']);
				break;
			case "categories":
				$itemobj = new ZenpageCategory($item['titlelink']);
				break;
		}
		if ($itemobj->isPublished()) {
			$pub++;
		}
	}
	$unpub = $total - $pub;
	return array($total, $type, $unpub);
}

function printPagesStatistic() {
	list($total, $type, $unpub) = getNewsPagesStatistic("pages");
	if (empty($unpub)) {
		printf(ngettext('<strong>%1$u</strong> page', '<strong>%1$u</strong> pages', $total), $total);
	} else {
		printf(ngettext('<strong>%1$u</strong> page (<strong>%2$u</strong> un-published)', '<strong>%1$u</strong> pages (<strong>%2$u</strong> un-published)', $total), $total, $unpub);
	}
}

function printNewsStatistic() {
	list($total, $type, $unpub) = getNewsPagesStatistic("news");
	if (empty($unpub)) {
		printf(ngettext('<strong>%1$u</strong> article', '<strong>%1$u</strong> articles', $total), $total);
	} else {
		printf(ngettext('<strong>%1$u</strong> article (<strong>%2$u</strong> un-published)', '<strong>%1$u</strong> articles (<strong>%2$u</strong> un-published)', $total), $total, $unpub);
	}
}

function printCategoriesStatistic() {
	list($total, $type, $unpub) = getNewsPagesStatistic("categories");
	if (empty($unpub)) {
		printf(ngettext('<strong>%1$u</strong> category', '<strong>%1$u</strong> categories', $total), $total);
	} else {
		printf(ngettext('<strong>%1$u</strong> category (<strong>%2$u</strong> un-published)', '<strong>%1$u</strong> categories (<strong>%2$u</strong> un-published)', $total), $total, $unpub);
	}
}
