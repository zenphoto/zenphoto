<?php

/**
 *
 * Lets you create custom menus and place them on your theme pages.
 *
 * Use the <var>Menu</var> tab to create your menus. Use <var>printCustomMenu()</var> to place them on your pages.
 *
 * This plugin is recommend for customized themes only that do not use the standard Zenphoto
 * display structure. Standard Zenphoto functions like the breadcrumb functions or the <var>next_album()</var>
 * loop for example will <b>NOT</b> take care of this menu's structure!
 *
 * @author Stephen Billard (sbillard), Malte Müller (acrylian)
 * @package zpcore\plugins\menumanager
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("A menu creation facility. The <em>Menu</em> tab admin interface lets you create arbitrary menu trees.");
$plugin_author = "Stephen Billard (sbillard), Malte Müller (acrylian)";
$plugin_category = gettext('Media');

$option_interface = 'menu_manager';

if (OFFSET_PATH) {
	require_once(dirname(dirname(__FILE__)) . '/template-functions.php');
	zp_register_filter('admin_tabs', 'menu_tabs');
} else {
	zp_register_filter('admin_toolbox_global', 'menu_admin_toolbox_global');
}

/**
 *
 * option handler
 * @author Stephen
 *
 */
class menu_manager {

	/**
	 *
	 * class instantiator
	 */
	function __construct() {
		setOptionDefault('menu_truncate_string', 0);
		setOptionDefault('menu_truncate_indicator', '');
	}

	function getOptionsSupported() {
		global $_zp_common_truncate_handler;

		$options = array(
				gettext('Truncate indicator*') => array(
						'key' => 'menu_truncate_indicator',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'disabled' => $_zp_common_truncate_handler,
						'desc' => gettext('Append this string to truncated titles.')),
				gettext('Truncate titles*') => array(
						'key' => 'menu_truncate_string',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext('Limit titles to this many characters. Zero means no limit.'))
		);
		if ($_zp_common_truncate_handler) {
			$options['note'] = array(
					'key' => 'menu_truncate_note',
					'type' => OPTION_TYPE_NOTE,
					'order' => 8,
					'desc' => '<p class="notebox">' . $_zp_common_truncate_handler . '</p>');
		} else {
			$_zp_common_truncate_handler = gettext('* These options may be set via the <a href="javascript:gotoName(\'menu_manager\');"><em>menu_manager</em></a> plugin options.');
			$options['note'] = array(
					'key' => 'menu_truncate_note',
					'type' => OPTION_TYPE_NOTE,
					'order' => 8,
					'desc' => gettext('<p class="notebox">*<strong>Note:</strong> The setting of these options are shared with other plugins.</p>'));
		}
		return $options;
	}

	function handleOption($option, $currentValue) {
		
	}

}

/**
 *
 * Add menu to the admin toolbox
 */
function menu_admin_toolbox_global($zf) {
	if (zp_loggedin(ADMIN_RIGHTS)) {
		echo "<li>";
		printLinkHTML($zf . '/' . PLUGIN_FOLDER . '/menu_manager/menu_tab.php', gettext("Menu"), NULL, NULL, NULL);
		echo "</li>\n";
	}
}

/**
 * Adds menu manager to admin tabs
 *
 * @param $tabs array Admin tabs
 * @param string $current current tab
 * @return unknown
 */
function menu_tabs($tabs) {
	if (zp_loggedin()) {
		$newtabs = array();
		foreach ($tabs as $key => $tab) {
			if ($key == 'tags') {
				$newtabs['menu'] = array(
						'text' => gettext("menu"),
						'link' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/menu_manager/menu_tab.php?page=menu&amp;tab=menu',
						'default' => 'menu',
						'subtabs' => NULL);
			}
			$newtabs[$key] = $tab;
		}
		return $newtabs;
	}
	return $tabs;
}

/*
 *
 * Common functions
 *
 */

$_zp_menu_manager_items = array();

/**
 * Gets the menu items
 *
 * @param string $menuset the menu tree desired
 * @param string $visible
 * @return array
 */
function getMenuItems($menuset, $visible) {
	global $_zp_menu_manager_items, $_zp_db;
	if (array_key_exists($menuset, $_zp_menu_manager_items) &&
					array_key_exists($visible, $_zp_menu_manager_items[$menuset])) {
		return $_zp_menu_manager_items[$menuset][$visible];
	}
	switch ($visible) {
		case 'visible':
			$where = " WHERE `show` = 1 AND menuset = " . $_zp_db->quote($menuset);
			break;
		case 'hidden':
			$where = " WHERE `show` = 0 AND menuset = " . $_zp_db->quote($menuset);
			break;
		default:
			$where = " WHERE menuset = " . $_zp_db->quote($menuset);
			$visible = 'all';
			break;
	}
	$result = $_zp_db->queryFullArray("SELECT * FROM " . $_zp_db->prefix('menu') . $where . " ORDER BY sort_order", false);
	$_zp_menu_manager_items[$menuset][$visible] = $result;
	return $_zp_menu_manager_items[$menuset][$visible];
}

/**
 * Gets a menu item by its id
 *
 * @param integer $id id of the item
 * @return array
 */
function getItem($id) {
	global $_zp_db;
	$menuset = checkChosenMenuset();
	$result = $_zp_db->querySingleRow("SELECT * FROM " . $_zp_db->prefix('menu') . " WHERE menuset = " . $_zp_db->quote($menuset) . " AND id = " . $id);
	return $result;
}

/**
 * Checks which menu set is chosen via $_GET. If none is explicity chosen the "default" one (create initially) is used.
 *
 * @return string
 */
function checkChosenMenuset($default = 'default') {
	if (isset($_GET['menuset'])) {
		$menuset = sanitize($_GET['menuset']);
	} else if (isset($_POST['menuset'])) {
		$menuset = sanitize($_POST['menuset']);
	} else {
		$menuset = $default;
	}
	return $menuset;
}

/**
 * Checks if the menu item is set visible or not
 *
 * @return string
 */
function checkChosenItemStatus() {
	if (isset($_GET['visible'])) {
		return sanitize($_GET['visible']);
	} else {
		return 'all';
	}
}

/**
 * Gets the title, url and name of a menu item
 *
 * @return array
 */
function getItemTitleAndURL($item) {
	global $_zp_gallery, $_zp_db;
	$themename = $_zp_gallery->getCurrentTheme();
	$array = array(
			"title" => '', 
			"url" => '', 
			"name" => '', 
			'public' => false,
			'protected' => false, 
			'theme' => $themename);
	$valid = true;
	$title = get_language_string($item['title']);
	switch ($item['type']) {
		case 'homepage':
			$array = array(
					"title" => get_language_string($item['title']), 
					"url" => WEBPATH, 
					"name" => WEBPATH, 
					'public' => true,
					'protected' => false, 
					'theme' => $themename);
			break;
		case "galleryindex":
			$array = array(
					"title" => get_language_string($item['title']), 
					"url" => getGalleryIndexURL(), 
					"name" => WEBPATH, 
					'public' => true,
					'protected' => false, 
					'theme' => $themename);
			break;
		case "album":
			$folderFS = internalToFilesystem($item['link']);
			$localpath = ALBUM_FOLDER_SERVERPATH . $folderFS;
			$dynamic = hasDynamicAlbumSuffix($folderFS) && !is_dir($folderFS);
			$valid = file_exists($localpath) && ($dynamic || is_dir($localpath));
			if (!$valid || strpos($localpath, '..') !== false) {
				$valid = false;
				$url = '';
				$public = false;
				$protected = 0;
			} else {
				$obj = AlbumBase::newAlbum($item['link']);
				$url = $obj->getLink(0);
				$public = $obj->isVisible();
				$protected = !$obj->isMyItem(LIST_RIGHTS) && $obj->isProtected();
				$title = $obj->getTitle();
			}
			$array = array(
					"title" => $title, 
					"url" => $url, 
					"name" => $item['link'], 
					'public' => $public,
					'protected' => $protected, 
					'theme' => $themename);
			break;
		case "zenpagepage":
			if(class_exists('zenpage')) {
				$sql = 'SELECT * FROM ' . $_zp_db->prefix('pages') . ' WHERE `titlelink`="' . $item['link'] . '"';
				$result = $_zp_db->querySingleRow($sql);
				if (is_array($result)) {
					$obj = new ZenpagePage($item['link']);
					$url = $obj->getLink();
					$public = $obj->isVisible();
					$protected = !$obj->isMyItem(LIST_RIGHTS) && $obj->isProtected();
					$title = $obj->getTitle();
				} else {
					$valid = false;
					$url = '';
					$public = false;
					$protected = 0;
				}
				$array = array(
						"title" => $title, 
						"url" => $url, 
						"name" => $item['link'], 
						'public' => $public,
						'protected' => $protected, 
						'theme' => $themename);
			}
			break;
		case "zenpagenewsindex":
			if(class_exists('zenpage')) {
				$url = getNewsIndexURL();
				$array = array(
						"title" => get_language_string($item['title']), 
						"url" => $url, 
						"name" => $url, 
						'public' => true,
						'protected' => false, 
						'theme' => $themename);
			}
			break;
		case "zenpagecategory":
			if(class_exists('zenpage')) {
				$sql = "SELECT title FROM " . $_zp_db->prefix('news_categories') . " WHERE titlelink = '" . $item['link'] . "'";
				$obj = $_zp_db->querySingleRow($sql, false);
				if ($obj) {
					$obj = new ZenpageCategory($item['link']);
					$title = $obj->getTitle();
					$public = $obj->isVisible();
					$protected = !$obj->isMyItem(LIST_RIGHTS) && $obj->isProtected();
					$url = $obj->getLink(0);
				} else {
					$valid = false;
					$url = '';
					$public = 0;
					$protected = 0;
				}
				$array = array(
						"title" => $title, 
						"url" => $url, 
						"name" => $item['link'], 
						'public' => $public,
						'protected' => $protected, 
						'theme' => $themename);
			}
			break;
		case "custompage":
			$root = SERVERPATH . '/' . THEMEFOLDER . '/' . $themename . '/';
			if (file_exists($root . $item['link'] . '.php')) {
				$url = getCustomPageURL($item['link']);
			} else {
				$valid = false;
				$url = '';
			}
			$array = array(
					"title" => $title, 
					"url" => $url, 
					"name" => $item['link'], 
					'public' => true,
					'protected' => false, 
					'theme' => $themename);
			break;
		case "customlink":
			$array = array(
					"title" => get_language_string($item['title']), 
					"url" => $item['link'], 
					"name" => $item['link'], 
					'open_newtab' => $item['open_newtab'], 
					'public' => true,
					'protected' => false, 
					'theme' => $themename);
			break;
		case 'menulabel':
			$array = array(
					"title" => get_language_string($item['title']), 
					"url" => NULL,
					'name' => $item['title'],
					'public' => true,
					'protected' => false,
					'theme' => $themename);
			break;
		default:
			$array = array(
					"title" => get_language_string($item['title']), 
					"url" => $item['link'], 
					"name" => $item['link'], 
					'public' => true,
					'protected' => false, 
					'theme' => $themename);
			break;
	}
	$limit = MENU_TRUNCATE_STRING;
	$array['valid'] = $valid;
	if ($limit) {
		$array['title'] = shortenContent($array['title'], $limit, MENU_TRUNCATE_INDICATOR);
	}
	return $array;
}

/* * *****************
 * Theme functions
 * ***************** */

/**
 * Gets the menu visibility
 * @return string
 */
function getMenuVisibility() {
	if (zp_loggedin(VIEW_ALL_RIGHTS)) {
		return "all";
	} else {
		return "visible";
	}
}

/**
 * "invents" a menu item for the current page (for when one does not exist)
 * Adds the item to the current menuset and modifies its "parent" as needed
 *
 * returns a contrived sort_order for the item.
 *
 * @param string $menuset
 * @param string $visibility
 * @param string $field The field of the array item to get, "all" for the full item array, "key" for the array index of the item within the items array (old default value)
 * @return int|array|string
 */
function inventMenuItem($menuset, $visibility, $field = 'sort_order') {
	global $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_search, $_zp_menu_manager_items,
	$_zp_current_zenpage_news, $_zp_current_zenpage_page;
	$currentkey = $insertpoint = NULL;
	$newitems = array();
	switch ($_zp_gallery_page) {
		case 'image.php':
			$name = '';
			if (in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) {
				$dynamic = $_zp_current_search->getDynamicAlbum();
				if (empty($dynamic)) { //	smple search
					foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $item) {
						if ($item['type'] == 'custompage' && $item['link'] == 'search') {
							$insertpoint = $item['sort_order'];
							$currentkey = $insertpoint . '-9999';
							break;
						}
					}
				}
			} else {
				$name = $_zp_current_album->name;
			}
			if (!empty($name)) {
				foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $item) {
					if ($item['type'] == 'album' && $item['title'] == $name) {
						$insertpoint = $item['sort_order'];
						$currentkey = $insertpoint . '-9999';
						break;
					}
				}
			}
			if (!empty($currentkey)) {
				$item = array(
						'id' => 9999,
						'sort_order' => $currentkey,
						'parentid' => $item['id'],
						'type' => 'image',
						'include_li' => true,
						'title' => $_zp_current_image->getTitle(),
						'show' => 1,
						'link' => '',
						'menuset' => $menuset,
						'span_class' => '',
						'span_id' => '');
			}
			break;
		case 'news.php':
			if (in_context(ZP_SEARCH_LINKED)) {
				foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $item) {
					if ($item['type'] == 'custompage' && $item['link'] == 'search') {
						$insertpoint = $item['sort_order'];
						$currentkey = $insertpoint . '-9999';
						break;
					}
				}
			} else {
				foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $item) {
					if ($item['type'] == 'zenpagenewsindex') {
						$insertpoint = $item['sort_order'];
						$currentkey = $insertpoint . '-9999';
						break;
					}
				}
			}
			if (!empty($currentkey)) {
				if (is_NewsArticle()) {
					$item = array(
							'id' => 9999,
							'sort_order' => $currentkey,
							'parentid' => $item['id'],
							'type' => 'zenpagenews',
							'include_li' => true,
							'title' => $_zp_current_zenpage_news->getTitle(),
							'show' => 1,
							'link' => '',
							'menuset' => $menuset,
							'span_class' => '',
							'span_id' => '');
				} else {
					$currentkey = false; // not a news page, must be the index?
				}
			}
			break;
		case 'pages.php':
			if (in_context(ZP_SEARCH_LINKED)) {
				foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $item) {
					if ($item['type'] == 'custompage' && $item['link'] == 'search') {
						$insertpoint = $item['sort_order'];
						$currentkey = $insertpoint . '-9999';
						$item = array(
								'id' => 9999,
								'sort_order' => $currentkey,
								'parentid' => $item['id'],
								'type' => 'zenpagepage',
								'include_li' => true,
								'title' => $_zp_current_zenpage_page->getTitle(),
								'show' => 1,
								'link' => '',
								'menuset' => $menuset,
								'span_class' => '',
								'span_id' => '');
						break;
					}
				}
			}
			break;
	}
	if (!empty($currentkey)) {
		foreach ($_zp_menu_manager_items[$menuset][$visibility] as $key => $olditem) {
			$newitems[$key] = $olditem;
			if ($olditem['sort_order'] == $insertpoint) {
				$newitems[$currentkey] = $item;
			}
		}
		$_zp_menu_manager_items[$menuset][$visibility] = $newitems;
		switch ($field) {
			default://individual field
				if (isset($item[$field])) {
					return $item[$field];
				}
				break;
			case 'all':
				if (isset($item)) {
					return $item;
				}
				break;
			case "key":
				return $currentkey;
		}
	}
}

/**
 * Returns the data of the current menu item
 * 
 * @since 1.5.7 Return values changed to match function name and doc, parameter $field added to help
 * @param string $menuset current menu set
 * @param string $field The field of the array item to get, "all" for the full item array, "key" for the array index of the item within the items array (old default value). Default 'sort_order'
 * @return int|array|string
 */
function getCurrentMenuItem($menuset, $field = 'sort_order') {
	$currentpageURL = rtrim(str_replace('\\', '/', html_encode(getRequestURI())), '/');
	if (isset($_GET['page'])) { // must strip out page numbers, all "pages" are equal
		if (MOD_REWRITE) {
			if (isset($_GET['album'])) {
				$target = '/' . _PAGE_ . '/' . sanitize($_GET['page']);
			} else {
				$target = '/' . sanitize($_GET['page']);
			}
			$i = strrpos($currentpageURL, $target);
			if ($i == (strlen($currentpageURL) - strlen($target))) {
				$currentpageURL = substr($currentpageURL, 0, $i);
			}
		} else {
			$target = '&amp;page=' . sanitize($_GET['page']);
			$i = strpos($currentpageURL, $target);
			if ($i !== false) {
				$currentpageURL = substr($currentpageURL, 0, $i) . substr($currentpageURL, $i + strlen($target));
			}
		}
	}
	$visibility = 'all';
	$items = getMenuItems($menuset, $visibility);
	$currentkey = NULL;
	foreach ($items as $key => $item) {
		switch ($item['type']) {
			case 'menulabel':
			case 'menufunction':
			case 'html':
				break;
			default:
				$checkitem = getItemTitleAndURL($item);
				if ($currentpageURL == html_encode(rtrim($checkitem['url'], '/'))) {
					$currentkey = $key;
					break 2;
				}
				break;
		}
	}
	$inventeditem = false;
	if (is_null($currentkey)) {
		$inventeditem = inventMenuItem($menuset, $visibility, 'all');
	}
	switch ($field) {
		default://individual field
			if($inventeditem) {
				if (isset($inventeditem[$field])) {
					return $inventeditem[$field];
				}
			} else if (isset($items[$currentkey][$field])) {
				return $items[$currentkey][$field];
			}
			break;
		case 'all':
			if ($inventeditem) {
				return $inventeditem;
			} else if (isset($items[$currentkey])) {
				return $items[$currentkey];
			}
			break;
		case "key":
			return $currentkey;
	}
}

/**
 * Returns the link to the predicessor of the current menu item
 * @param string $menuset current menu set
 * @return string
 */
function getMenumanagerPredicessor($menuset = 'default') {
	$sortorder = getCurrentMenuItem($menuset);
	$items = getMenuItems($menuset, getMenuVisibility());
	if (count($items) == 0)
		return NULL;
	if (empty($sortorder))
		return NULL;
	$order = explode('-', $sortorder);
	$next = array_pop($order) - 1;
	$saveorder = $order;
	while ($next >= 0) {
		$order = $saveorder;
		array_push($order, sprintf('%03u', $next));
		$sortorder = implode('-', $order);
		if (array_key_exists($sortorder, $items) && $items[$sortorder]['type'] != 'menulabel') { // omit the menulabels
			$item = getItemTitleAndURL($items[$sortorder]);
			if ($item['public']) {
				return $item;
			}
		}
		$next--;
	}
	return NULL;
}

/**
 * Prints the previous link of the current menu item
 * @param string  $text
 * @param string  $menuset
 * @param string  $title
 * @param string  $class
 * @param string  $id
 */
function printMenumanagerPrevLink($text, $menuset = 'default', $title = NULL, $class = NULL, $id = NULL) {
	$itemarray = getMenumanagerPredicessor($menuset);
	if (is_array($itemarray)) {
		if (is_null($title))
			$title = $itemarray['title'];
		printLinkHTML($itemarray['url'], $text, $title, $class, $id);
	} else {
		echo '<span class="disabledlink">' . html_encode($text) . '</span>';
	}
}

/**
 * Returns the successor link of the current menu item
 * @param string $menuset
 * @return string
 */
function getMenumanagerSuccessor($menuset = 'default') {
	$sortorder = getCurrentMenuItem($menuset);
	$items = getMenuItems($menuset, getMenuVisibility());
	if (count($items) == 0)
		return NULL;
	if (empty($sortorder))
		return NULL;
	$order = explode('-', $sortorder);
	$next = array_pop($order) + 1;
	$short_order = $order;
	array_push($order, sprintf('%03u', $next));
	$sortorder = implode('-', $order);
	while ($next <= 999) {
		$order = $short_order;
		array_push($order, sprintf('%03u', $next));
		$sortorder = implode('-', $order);
		if (array_key_exists($sortorder, $items)) {
			if ($items[$sortorder]['type'] != 'menulabel') { // omit the menulabels
				$item = getItemTitleAndURL($items[$sortorder]);
				if ($item['public']) {
					return $item;
				}
			}
		}
		$next++;
	}
	return NULL;
}

/**
 * Gets the link to the next menu item
 * @param string $text
 * @param string $menuset current menu set
 * @param string $title
 * @param string $class
 * @param string $id
 */
function printMenumanagerNextLink($text, $menuset = 'default', $title = NULL, $class = NULL, $id = NULL) {
	$itemarray = getMenumanagerSuccessor($menuset);
	if (is_array($itemarray)) {
		if (is_null($title))
			$title = $itemarray['title'];
		printLinkHTML($itemarray['url'], $text, $title, $class, $id);
	} else {
		echo '<span class="disabledlink">' . html_encode($text) . '</span>';
	}
}

/**
 * Prints a list of all pages.
 *
 * @param string $prevtext Insert here the linktext like 'previous page'
 * @param string $menuset current menu set
 * @param string $menuset current menu set
 * @param string $class the css class to use, "pagelist" by default
 * @param string $nextprev set to true to get the 'next' and 'prev' links printed
 * @param string $id the css id to use
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printMenuemanagerPageListWithNav($prevtext, $nexttext, $menuset = 'default', $class = 'pagelist', $nextprev = true, $id = NULL, $firstlast = true, $navlen = 9) {
	global $_zp_db;
	$currentitem = getMenuFromLink(html_encode(urldecode(getRequestURI())), $menuset);
	if (is_null($currentitem))
		return; // we are not in menuset
	$orders = explode('-', $currentitem['sort_order']);
	array_pop($orders);
	$lookfor = implode('-', $orders) . '-';
	$sql = 'SELECT `sort_order` FROM ' . $_zp_db->prefix('menu') . ' WHERE `sort_order` LIKE "' . $lookfor . '%" ORDER BY `sort_order` ASC';
	$result = $_zp_db->queryFullArray($sql, false, 'sort_order');
	if (is_array($result)) {
		$l = strlen($lookfor) + 3;
		foreach ($result as $key => $item) { // discard next level items
			if (strlen($key) > $l)
				unset($result[$key]);
		}
		$itemlist = array_keys($result);
		$total = count($itemlist);
		$current = array_search($currentitem['sort_order'], $itemlist) + 1;
		if ($total < 2) {
			$class .= ' disabled_nav';
		}
		if ($navlen == 0)
			$navlen = $total;
		$extralinks = 2;
		if ($firstlast)
			$extralinks = $extralinks + 2;
		$len = floor(($navlen - $extralinks) / 2);
		$j = max(round($extralinks / 2), min($current - $len - (2 - round($extralinks / 2)), $total - $navlen + $extralinks - 1));
		$ilim = min($total, max($navlen - round($extralinks / 2), $current + floor($len)));
		$k1 = round(($j - 2) / 2) + 1;
		$k2 = $total - round(($total - $ilim) / 2);
		$items = getMenuItems($menuset, getMenuVisibility());
		echo "<div" . (($id) ? " id=\"$id\"" : "") . " class=\"$class\">\n";
		echo "<ul class=\"$class\">\n";
		if ($nextprev && getMenumanagerPredicessor($menuset)) {
			echo "<li class=\"prev\">";
			printMenumanagerPrevLink($prevtext, $menuset, $prevtext, gettext("Previous Page"));
			echo "</li>\n";
		}
		if ($firstlast) {
			echo '<li class="' . ($current == 1 ? 'current' : 'first') . '">';
			$itemarray = getItemTitleAndURL($items[$itemlist[0]]);
			printLinkHTML($itemarray['url'], 1, gettext("Page 1"));
			echo "</li>\n";
			if ($j > 2) {
				echo "<li>";
				$itemarray = getItemTitleAndURL($items[$itemlist[$k1 - 1]]);
				printLinkHTML($itemarray['url'], ($j - 1 > 2) ? '...' : $k1, sprintf(ngettext('Page %u', 'Page %u', $k1), $k1));
				echo "</li>\n";
			}
		}
		for ($i = $j; $i <= $ilim; $i++) {
			echo "<li" . (($i == $current) ? " class=\"current\"" : "") . ">";
			$itemarray = getItemTitleAndURL($items[$itemlist[$i - 1]]);
			if ($i == $current) {
				$title = sprintf(ngettext('Page %1$u (Current Page)', 'Page %1$u (Current Page)', $i), $i);
			} else {
				$title = sprintf(ngettext('Page %1$u', 'Page %1$u', $i), $i);
			}
			printLinkHTML($itemarray['url'], $i, $title);
			echo "</li>\n";
		}
		if ($i < $total) {
			echo "<li>";
			$itemarray = getItemTitleAndURL($items[$itemlist[$k2 - 1]]);
			printLinkHTML($itemarray['url'], ($total - $i > 1) ? '...' : $k2, sprintf(ngettext('Page %u', 'Page %u', $k2), $k2));
			echo "</li>\n";
		}
		if ($firstlast && $i <= $total) {
			echo "\n  <li class=\"last\">";
			$itemarray = getItemTitleAndURL($items[$itemlist[$total - 1]]);
			printLinkHTML($itemarray['url'], $total, sprintf(ngettext('Page {%u}', 'Page {%u}', $total), $total));
			echo "</li>";
		}
		if ($nextprev && getMenumanagerSuccessor($menuset)) {
			echo "<li class=\"next\">";
			printMenumanagerNextLink($nexttext, gettext("Next Page"));
			echo "</li>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}

/**
 * Prints a full page navigation including previous and next page links with a list of all pages in between.
 *
 * @param string $nexttext Insert here the linktext like 'next page'
 * @param string $class Insert here the CSS-class name you want to style the link with (default is "pagelist")
 * @param string $id Insert here the CSS-ID name if you want to style the link with this
 * @param bool $firstlast Add links to the first and last pages of you gallery
 * @param int $navlen Number of navigation links to show (0 for all pages). Works best if the number is odd.
 */
function printMenuemanagerPageList($menuset = 'default', $class = 'pagelist', $id = NULL, $firstlast = true, $navlen = 9) {
	printMenuemanagerPageListWithNav(null, null, $menuset, false, $class, $id, false, $navlen);
}

/**
 * Gets the parent menu items of the current menu item. Returns the array of the items or false.
 * @param string $menuset current menu set
 * @param array $currentitem Optional array of the menu item to get parents of. Default null for the current item
 * @return array|false
 */
function getParentMenuItems($menuset = 'default', $currentitem = null) {
	if (is_null($currentitem)) {
		$currentitem = getCurrentMenuItem($menuset, 'all');
	} 
	$items = getMenuItems($menuset, getMenuVisibility());
	if (count($items) > 0) {
		if ($currentitem) {
			$sortorder = $currentitem['sort_order'];
			$parents = array();
			$currentorder = explode('-', $sortorder);
			foreach($items as $item) {
				$itemorder = explode('-', $item['sort_order']);
				if($itemorder[0] == $currentorder[0] && $item['id'] != $currentitem['id'] && $currentitem['parentid'] != 0) {
					$parents[] = $item;
				}
			}
			if (!empty($parents)) {
				sortMultiArray($parents, 'sort_order', false, false, false);
				return $parents;
			}
		}
	}
	return false;
}

/**
 * Prints the breadcrumbs of the current page
 *
 * NOTE: this function is entirely dependedn on the menu tree you have
 * generated. It will work only with static menu trees. That is, if the page
 * upon which you call this function is not present in your menu tree it will
 * not have any parent pages. Thus, menu items generated for instance by function
 * calls cannot have parents in the printMenumanagerBreadcrumb sense.
 *
 * Likewise if you have non exclusive menu links to a page the parentage of that
 * page with respect to breadcrumbs may not reflect on the menu transitions that
 * the user used to arrive on the page.
 *
 * @param string $menuset current menu set
 * @param string $before before text
 * @param string $between between text
 * @param string $after after text
 */
function printMenumanagerBreadcrumb($menuset = 'default', $before = '', $between = ' | ', $after = ' | ') {
	$parents = getParentMenuItems($menuset);
	if ($parents) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		if ($between) {
			$between = '<span class="betweentext">' . html_encode($between) . '</span>';
		}
		$i = 0;
		foreach ($parents as $item) {
			if ($i > 0) {
				echo $between;
			}
			$itemarray = getItemTitleAndURL($item);
			if ($item['type'] == 'menulabel') {
				echo html_encode($itemarray['title']);
			} else {
				printLinkHTML($itemarray['url'], $itemarray['title'], $itemarray['title']);
			}
			$i++;
		}
		if ($after) {
			echo '<span class="aftertext">' . html_encode($after) . '</span>';
		}
	}
}

/**
 * Returns the menu item corresponding to $link
 * @param string $link
 * @param string $menuset
 * @return array
 */
function getMenuFromLink($link, $menuset = 'default') {
	$link = rtrim(str_replace('\\', '/', $link), '/');
	$items = getMenuItems($menuset, getMenuVisibility());
	foreach ($items as $item) {
		$itemarray = getItemTitleAndURL($item);
		if ($itemarray['url'] == $link)
			return $item;
	}
	return NULL;
}

/**
 * Returns true if the current menu item is a sub item of $link
 * @param string $link possible parent
 * @param string $menuset current menuset
 * @return bool
 */
function submenuOf($link, $menuset = 'default') {
	$link_menu = getMenuFromLink($link, $menuset);
	if (is_array($link_menu)) {
		$current = getCurrentMenuItem($menuset);
		$items = getMenuItems($menuset, getMenuVisibility());
		if (!is_null($current)) {
			$sortorder = $link_menu['sort_order'];
			if (strlen($current) > strlen($sortorder)) {
				$p = strpos($current, $sortorder);
				return $p === 0;
			}
		}
	}
	return false;
}

/**
 * Creates a menu set from the items passed. But only if the menu set does not already exist
 * @param array $menuitems items for the menuset
 * 		array elements:
 * 			'type'=>menuset type
 * 			'title'=>title for the menu item
 * 			'link'=>URL or other data for the item link
 * 			'show'=>set to 1:"visible" or 0:"hidden",
 * 			'nesting'=>nesting level of this item in the menu heirarchy
 *
 * @param string $menuset current menuset
 */
function createMenuIfNotExists($menuitems, $menuset = 'default') {
	global $_zp_db;
	$count = $_zp_db->count('menu', 'WHERE menuset=' . $_zp_db->quote($menuset));
	if ($count == 0) { // there was not an existing menu set
		require_once(dirname(__FILE__) . '/menu_manager/menu_manager-admin-functions.php');
		$success = 1;
		$orders = array();
		foreach ($menuitems as $key => $result) {
			if (array_key_exists('nesting', $result)) {
				$nesting = $result['nesting'];
			} else {
				$nesting = 0;
			}
			while ($nesting + 1 < count($orders))
				array_pop($orders);
			while ($nesting + 1 > count($orders))
				array_push($orders, -1);
			$result['id'] = 0;
			if (isset($result['include_li'])) {
				$includeli = $result['include_li'];
			} else {
				$includeli = 1;
			}
			$type = $result['type'];
			switch ($type) {
				case 'all_items':
					$orders[$nesting] ++;
					$_zp_db->query("INSERT INTO " . $_zp_db->prefix('menu') . " (`title`,`link`,`type`,`show`,`menuset`,`sort_order`) " .
									"VALUES ('" . gettext('Home') . "', '" . WEBPATH . '/' . "','galleryindex','1'," . $_zp_db->quote($menuset) . ',' . $_zp_db->quote($orders), true);
					$orders[$nesting] = addAlbumsToDatabase($menuset, $orders);
					if (extensionEnabled('zenpage')) {
						$orders[$nesting] ++;
						$_zp_db->query("INSERT INTO " . $_zp_db->prefix('menu') . " (title`,`link`,`type`,`show`,`menuset`,`sort_order`) " .
										"VALUES ('" . gettext('News index') . "', '" . getNewsIndexURL() . "','zenpagenewsindex','1'," . $_zp_db->quote($menuset) . ',' . $_zp_db->quote(sprintf('%03u', $base + 1)), true);
						$orders[$nesting] = addPagesToDatabase($menuset, $orders) + 1;
						$orders[$nesting] = addCategoriesToDatabase($menuset, $orders);
					}
					$type = false;
					break;
				case 'all_albums':
					$orders[$nesting] ++;
					$orders[$nesting] = addAlbumsToDatabase($menuset, $orders);
					$type = false;
					break;
				case 'all_zenpagepages':
					$orders[$nesting] ++;
					$orders[$nesting] = addPagesToDatabase($menuset, $orders);
					$type = false;
					break;
				case 'all_zenpagecategorys':
				case 'all_zenpagecategories':
					if ($type == 'all_zenpagecategorys') {
						deprecationNotice(gettext('The menu item type all_zenpagecategorys is deprecated. Use all_zenpagecategories instead'));
					}
					$orders[$nesting] ++;
					$orders[$nesting] = addCategoriesToDatabase($menuset, $orders);
					$type = false;
					break;
				case 'album':
					$result['title'] = '';
					if (empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty link.'), $key));
					}
					break;
				case 'homepage':
					$result['link'] = '';
					if (empty($result['title'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title.'), $key));
					}
					break;
				case 'galleryindex':
					$result['link'] = '';
					if (empty($result['title'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title.'), $key));
					}
					break;
				case 'zenpagepage':
					$result['title'] = '';
					if (empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty link.'), $key));
					}
					break;
				case 'zenpagenewsindex':
					$result['link'] = '';
					if (empty($result['title'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title.'), $key));
					}
					break;
				case 'zenpagecategory':
					$result['title'] = '';
					if (empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty link.'), $key));
					}
					break;
				case 'custompage':
					if (empty($result['title']) || empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title or link.'), $key));
					}
					break;
				case 'customlink':
					if (empty($result['title'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title.'), $key));
					} else if (empty($result['link'])) {
						$result['link'] = seoFriendly(get_language_string($result['title']));
					}
					break;
				case 'menulabel':
					if (empty($result['title'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title.'), $key));
					}
					$result['link'] = sha1($result['title']);
					break;
				case 'menufunction':
					if (empty($result['title']) || empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title or link.'), $key));
					}
					break;
				case 'html':
					if (empty($result['title']) || empty($result['link'])) {
						$success = -1;
						debugLog(sprintf(gettext('createMenuIfNotExists item %s has an empty title or link.'), $key));
					}
					break;
				default:
					$success = -1;
					debugLog(sprintf(gettext('createMenuIfNotExists item %s has an invalid type.'), $key));
					break;
			}
			if ($success > 0 && $type) {
				$orders[$nesting] ++;
				$sort_order = '';
				for ($i = 0; $i < count($orders); $i++) {
					$sort_order .= sprintf('%03u', $orders[$i]) . '-';
				}
				$sort_order = substr($sort_order, 0, -1);
				$sql = "INSERT INTO " . $_zp_db->prefix('menu') . " (`title`,`link`,`type`,`show`,`menuset`,`sort_order`,`include_li`) " .
								"VALUES (" . $_zp_db->quote($result['title']) .
								", " . $_zp_db->quote($result['link']) .
								"," . $_zp_db->quote($result['type']) . "," . $result['show'] .
								"," . $_zp_db->quote($menuset) . "," . $_zp_db->quote($sort_order) . ",$includeli)";
				if (!$_zp_db->query($sql, false)) {
					$success = -2;
					debugLog(sprintf(gettext('createMenuIfNotExists item %1$s query (%2$s) failed: %3$s.'), $key, $sql, $_zp_db->getError()));
				}
			}
		}
	} else {
		$success = 0;
	}
	if ($success < 0) {
		trigger_error(gettext('createMenuIfNotExists has posted processing errors to your debug log.'), E_USER_NOTICE);
	}
	return $success;
}

/**
 * Gets the direct child menu items of the current menu item. Returns the array of the items or false.
 * @param string $menuset current menu set
 * @param bool $allchilds Set to false (default) for the next level childs, true for childs of all further levels
 * @param array $item Optional array of the menu item to get parents of. Default null for the current item
 * @return array|false
 */
function getMenuItemChilds($menuset = 'default', $allchilds = false, $currentitem = null) {
	if (is_null($currentitem)) {
		$sortorder = getCurrentMenuItem($menuset);
	} else {
		$sortorder = $currentitem['sort_order'];
	}
	$items = getMenuItems($menuset, getMenuVisibility());
  if (count($items) > 0) {
    if ($sortorder) { 
      $length = strlen($sortorder);
      $level = explode('-', $sortorder);
      $level = count($level);
      $childs = array();
      foreach ($items as $item) {
        $itemlevel = explode('-', $item['sort_order']);
        $itemlevel = count($itemlevel);
        if ($allchilds) {
          $is_validchild = true;
        } else {
          if ($itemlevel == $level + 1) {
            $is_validchild = true;
          } else {
            $is_validchild = false;
          }
        }
        if (substr($item['sort_order'], 0, $length) == $sortorder && $item['sort_order'] != $sortorder && $is_validchild) {
          array_push($childs, $item);
        }
      }
      if (!empty($childs)) {
        return $childs;
      }
    }
  }
  return false;
}

/**
 * Checks if the item $item is a parent item of the current item
 * @param string $menuset Name of the menuset to use
 * @param array $item Array of the item to check
 * @return boolean
 */
function isCurrentitemParent($menuset = 'default', $item = array()) {
	$currentid = getCurrentMenuItem($menuset, 'id');
	$allparents = getParentMenuItems($menuset);
	foreach($allparents as $parent) {
		if($item['id'] != $currentid && $parent['id'] == $item['id']) {
			return true;
		}
		return false;
	} 
	
}

/**
 * Checks if the actual object the menu item represents is actually public
 * 
 * @since 1.6.1
 * @param string  $menuset
 * @param array $item The menu item array of the menu item to check
 * @param array $itemarray The item array of the menu item to check as returned by getItemTitleAndURL()
 * @return bool
 */
function isPublicMenuItem($menuset, $item, $itemarray) {
	if (!$itemarray['public']) {
		return false;
	}
	$parents = getParentMenuItems($menuset, $item);
	if ($parents) {
		foreach ($parents as $parent) {
			$parentitemarray = getItemTitleAndURL($parent);
			if (!$parentitemarray['public']) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Prints a context sensitive menu of all pages as a unordered html list
 *
 * @param string $menuset the menu tree to output
 * @param string $option The mode for the menu:
 * 												"list" context sensitive toplevel plus sublevel pages,
 * 												"list-top" only top level pages,
 * 												"omit-top" only sub level pages
 * 												"list-sub" lists only the current pages direct offspring
 * @param string $css_id CSS id of the top level list
 * @param string $css_class_topactive class of the active item in the top level list
 * @param string $css_class CSS class of the sub level list(s)
 * @param string $css_class_active CSS class of the sub level list(s)
 * @param int $showsubs Set to depth of sublevels that should be shown always. 0 by default. To show all, set to a true! Only valid if option=="list".
 * @param bool $counter TRUE (FALSE default) if you want the count of articles for Zenpage news categories or images/subalbums for albums.

 * @return string
 */
function printCustomMenu($menuset = 'default', $option = 'list', $css_id = '', $css_class_topactive = '', $css_class = '', $css_class_active = '', $showsubs = 0, $counter = false) {
	global $_zp_zenpage, $_zp_gallery_page, $_zp_current_zenpage_page, $_zp_current_category;
	$itemcounter = '';
	if ($css_id != "") {
		$css_id = " id='" . $css_id . "'";
	}
	if ($showsubs === true)
		$showsubs = 9999999999;

	$currentitem = getCurrentMenuItem($menuset, 'all');
	$sortorder = '';
	$currentitem_parentid = '';
	$pageid = '';
	if(is_array($currentitem)) {
		$sortorder = $currentitem['sort_order'];
		$currentitem_parentid = $currentitem['parentid'];
		$pageid = $currentitem['id'];
	}
	$items = getMenuItems($menuset, getMenuVisibility());
	if (count($items) == 0)
		return; // nothing to do
	
	if ($startlist = !($option == 'omit-top' || $option == 'list-sub')) {
		echo "<ul$css_id>";
	}
	$baseindent = max(1, count(explode("-", $sortorder)));
	$indent = 1;
	$open = array($indent => 0);
	$parents = array(NULL);
	$order = explode('-', $sortorder);
	$mylevel = count($order);
	$myparentsort = array_shift($order);

	for ($c = 0; $c <= $mylevel; $c++) {
		$parents[$c] = NULL;
	}
	foreach ($items as $item) {
		$itemarray = getItemTitleAndURL($item);
		if (!isPublicMenuItem($menuset, $item, $itemarray)) {
			continue;
		}
		$itemURL = $itemarray['url'];
		$itemtitle = $itemarray['title'];
		$level = substr_count($item['sort_order'], '-') + 1;
		$process = (($level <= $showsubs && $option == "list") // user wants all the pages whose level is <= to the parameter
						|| ($option == 'list' || $option == 'list-top') && $level == 1 // show the top level
						|| (($option == 'list' || ($option == 'omit-top' && $level > 1)) && (($item['id'] == $pageid) // current page
						|| ($item['parentid'] == $pageid) // offspring of current page
						|| ($level < $mylevel && $level > 1 && strpos($item['sort_order'], $myparentsort) === 0)) // direct ancestor
						|| (($level == $mylevel) && ($currentitem_parentid == $item['parentid'])) // sibling
						) || ($option == 'list-sub' && ($item['parentid'] == $pageid) // offspring of the current page
						)
						);
		if ($process) {
			if ($level > $indent) {
				echo "\n" . str_pad("\t", $indent, "\t") . "<ul class=\"$css_class menu_{$item['type']}\">\n";
				$indent++;
				$parents[$indent] = NULL;
				$open[$indent] = 0;
			} else if ($level < $indent) {
				$parents[$indent] = NULL;
				while ($indent > $level) {
					if ($open[$indent]) {
						$open[$indent] --;
						echo "</li>\n";
					}
					$indent--;
					echo str_pad("\t", $indent, "\t") . "</ul>\n";
				}
			} else { // level == indent, have not changed
				if ($open[$indent]) { // level = indent
					echo str_pad("\t", $indent, "\t") . "</li>\n";
					$open[$indent] --;
				} else {
					echo "\n";
				}
			}

			if ($open[$indent]) { // close an open LI if it exists
				echo "</li>\n";
				$open[$indent] --;
			}

			echo str_pad("\t", $indent - 1, "\t");
			$open[$indent] += $item['include_li'];
			$parents[$indent] = $item['id'];
			if ($counter) {
				switch ($item['type']) {
					case'album':
						$albumobj = AlbumBase::newAlbum($item['link']);
						$numimages = $albumobj->getNumImages();
						$numsubalbums = $albumobj->getNumAlbums();
						$itemcounter = ' <span style="white-space:nowrap;"><small>(';
						if ($numsubalbums != 0) {
							$itemcounter .= sprintf(ngettext('%u album', '%u albums', $numsubalbums), $numsubalbums);
						}
						if ($numimages != 0) {
							if ($numsubalbums != 0) {
								$itemcounter .= ' ';
							}
							$itemcounter .= sprintf(ngettext('%u image', '%u images', $numimages), $numimages);
						}
						$itemcounter .= ')</small></span>';

						break;
					case'zenpagecategory':
						if ((zp_loggedin(ZENPAGE_NEWS_RIGHTS | ALL_NEWS_RIGHTS))) {
							$published = "all";
						} else {
							$published = "published";
						}
						$catobj = new ZenpageCategory($item['link']);
						$catcount = count($catobj->getArticles(0, $published));
						$itemcounter = "<small> (" . $catcount . ")</small>";
						break;
				}
			}
			$class = '';
			if ($item['id'] == $pageid && !is_null($pageid)) {
				if ($level == 1) { // top level
					$class = ' ' . $css_class_topactive;
				} else {
					$class = ' ' . $css_class_active .'-' . $level;
				}
				if ($itemarray['protected']) {
					$class .= ' has_password';
				}
				echo '<li class="menu_' . trim($item['type'] . ' ' . $class) . '">' . $itemtitle . $itemcounter;
			} else {
				if (strpos($sortorder, $item['sort_order']) === 0) { // we are in the heritage chain
					if ($level == 1) { // top level
						$class = ' ' . $css_class_topactive;
					} else {
						$class = ' ' . $css_class_active .'-' . $level;
					}
				} 
				if ($itemarray['protected']) {
					$class .= ' has_password';
				}
				if ($item['include_li']) {
					echo '<li class="menu_' . $item['type'] . $class . '">';
				}
				if ($item['span_id'] || $item['span_class']) {
					echo '<span';
					if ($item['span_id'])
						echo ' id="' . $item['span_id'] . '"';
					if ($item['span_class'])
						echo ' class="' . $item['span_class'] . '"';
					echo '>';
				}
				switch ($item['type']) {
					case 'html':
						echo $item['link'];
						break;
					case 'menufunction':
						$i = strpos($itemURL, '(');
						if ($i) {
							if (function_exists(trim(substr($itemURL, 0, $i)))) {
								eval($itemURL);
							}
						}
						break;
					case 'menulabel':
						echo $itemtitle;
						break;
					default:
						$target_attr = '';
						if($item['type'] == 'customlink' && $item['open_newtab']) {
							$target_attr = ' target="_blank"';
						}
						if (empty($itemURL)) {
							$itemURL = FULLWEBPATH;
						}
						echo '<a href="' . $itemURL . '" title="' . getBare($itemtitle) . '"' . $target_attr . '>' . $itemtitle . '</a>' . $itemcounter;
						break;
				}
				if ($item['span_id'] || $item['span_class']) {
					echo '</span>';
				}
			}
		}
	}
	// cleanup any hanging list elements
	while ($indent > 1) {
		if ($open[$indent]) {
			echo "</li>\n";
			$open[$indent] --;
		}
		$indent--;
		echo str_pad("\t", $indent, "\t") . "</ul>";
	}
	if ($open[$indent]) {
		echo "</li>\n";
		$open[$indent] --;
	} else {
		echo "\n";
	}
	if ($startlist) {
		echo "</ul>\n";
	}
}