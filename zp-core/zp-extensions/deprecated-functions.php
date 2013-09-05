<?php

/**
 * These functions have been removed from mainstream Zenphoto as they have been
 * supplanted.
 * They are not maintained and they are not guaranteed to function correctly with the
 * current version of Zenphoto.
 *
 * The default settings cause an <var>E_USER_NOTICE</var> error to be generated when the function is used.
 * The text of the error message will tell you how to replace calls on the deprecated function. The error
 * message can be disabled to allow your scripts to continue to run. Visit the <i>deprecated-functions</i>
 * plugin options. Find the function and uncheck the box by the function.
 *
 * A utility button is provided that allows you to search themes and plugins for uses of functions which have been deprecated.
 * Use it to be proactive in replacing these discontinued items.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage development
 */
$plugin_description = gettext("Provides deprecated Zenphoto functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the Zenphoto distributed functions.");
$option_interface = 'deprecated_functions';
$plugin_is_filter = 9 | CLASS_PLUGIN;

enableExtension('deprecated-functions', $plugin_is_filter); //	Yes, I know some people will be annoyed that this keeps coming back,
//	but each release may deprecated new functions which would then just give
//	(perhaps unseen) errors. Better the user should disable this once he knows
//	his site is working.

zp_register_filter('admin_utilities_buttons', 'deprecated_functions::button');

class deprecated_functions {

	var $internalFunctions = array(
					'getSearchURL',
					'printPasswordForm'
	);
	var $listed_functions = array();

	function deprecated_functions() {
		global $_internalFunctions;
		$deprecated = file_get_contents(__FILE__);
		$i = strpos($deprecated, '//' . ' IMPORTANT:: place all deprecated functions below this line!!!');
		$deprecated = substr($deprecated, $i);
		preg_match_all('/function\040+(.*)\040?\(.*\)\040?\{/', $deprecated, $functions);
		$this->listed_functions = array_merge($functions[1], $this->internalFunctions);
		// remove the items from this class and notify function, leaving only the deprecated functions
		foreach ($this->listed_functions as $key => $funct) {
			if ($funct == '_emitPluginScripts') { // special case!!!!
				unset($this->listed_functions[$key]);
			} else {
				setOptionDefault('deprecated_' . $funct, 1);
			}
		}
	}

	function getOptionsSupported() {
		$list = array();
		foreach ($this->listed_functions as $funct) {
			$list[$funct] = 'deprecated_' . $funct;
		}
		return array(gettext('Functions') => array('key'				 => 'deprecated_Function_list', 'type'			 => OPTION_TYPE_CHECKBOX_UL,
										'checkboxes' => $list,
										'desc'			 => gettext('Send the <em>deprecated</em> notification message if the function name is checked. Un-checking these boxes will allow you to continue using your theme without warnings while you upgrade its implementation.')));
	}

	/*
	 * used to provided deprecated function notification.
	 */

	static function notify($use) {
		$traces = @debug_backtrace();
		$fcn = $traces[1]['function'];
		if (empty($fcn) || getOption('deprecated_' . $fcn)) {
			if (empty($fcn))
				$fcn = gettext('function');
			if (!empty($use))
				$use = ' ' . $use;
			if (isset($traces[1]['file']) && isset($traces[1]['line'])) {
				$script = basename($traces[1]['file']);
				$line = $traces[1]['line'];
			} else {
				$script = $line = gettext('unknown');
			}
			trigger_error(sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated'), $fcn, $script, $line) . $use . '<br />' . sprintf(gettext('You can disable this error message by going to the <em>deprecated-functions</em> plugin options and un-checking <strong>%s</strong> in the list of functions.' . '<br />'), $fcn), E_USER_WARNING);
		}
	}

	static function button($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Development'),
						'enable'			 => true,
						'button_text'	 => gettext('Check deprecated use'),
						'formname'		 => 'deprecated_functions.php',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/deprecated_functions/check_for_deprecated.php',
						'icon'				 => 'images/magnify.png',
						'title'				 => gettext("Searches PHP scripts for use of deprecated functions."),
						'alt'					 => gettext('Check for update'),
						'hidden'			 => '',
						'rights'			 => ADMIN_RIGHTS
		);
		return $buttons;
	}

}

// IMPORTANT:: place all deprecated functions below this line!!!

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
function printImageRating($object = NULL) {
	deprecated_functions::notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object))
		$object = $_zp_current_image;
	printRating(3, $object);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function printAlbumRating($object = NULL) {
	deprecated_functions::notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object))
		$object = $_zp_current_album;
	printRating(3, $object);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function printImageEXIFData() {
	deprecated_functions::notify(gettext('Use printImageMetadata().'));
	if (isImageVideo()) {

	} else {
		printImageMetadata();
	}
}

/**
 * @deprecated
 * @since 1.2.7
 */
function printCustomSizedImageMaxHeight($maxheight) {
	deprecated_functions::notify(gettext('Use printCustomSizedImageMaxSpace().'));
	if (getFullWidth() === getFullHeight() OR getDefaultHeight() > $maxheight) {
		printCustomSizedImage(getImageTitle(), null, null, $maxheight, null, null, null, null, null, null);
	} else {
		printDefaultSizedImage(getImageTitle());
	}
}

/**
 * @deprecated
 * @since 1.2.7
 */
function getCommentDate($format = NULL) {
	deprecated_functions::notify(gettext('Use getCommentDateTime().'));
	if (is_null($format)) {
		$format = DATE_FORMAT;
		$time_tags = array('%H', '%I', '%R', '%T', '%r', '%H', '%M', '%S');
		foreach ($time_tags as $tag) { // strip off any time formatting
			$t = strpos($format, $tag);
			if ($t !== false) {
				$format = trim(substr($format, 0, $t));
			}
		}
	}
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function getCommentTime($format = '%I:%M %p') {
	deprecated_functions::notify(gettext('Use getCommentDateTime().'));
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function hitcounter($option = 'image', $viewonly = false, $id = NULL) {
	deprecated_functions::notify(gettext('Use getHitcounter().'));
	return @call_user_func('getHitcounter');
}

/**
 * @deprecated
 * @since 1.2.7
 */
function my_truncate_string($string, $length) {
	deprecated_functions::notify(gettext('Use truncate_string().'));
	return truncate_string($string, $length);
}

/**
 * @deprecated
 * @since 1.2.7
 */
function getImageEXIFData() {
	deprecated_functions::notify(gettext('Use getImageMetaData().'));
	global $_zp_current_image;
	if (is_null($_zp_current_image))
		return false;
	return $_zp_current_image->getMetaData();
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
 * @since 1.2.8
 */
function getAlbumPlace() {
	deprecated_functions::notify(gettext('Use getAlbumLocation().'));
	global $_zp_current_album;
	if (is_object($_zp_current_album)) {
		return $_zp_current_album->getLocation();
	} else {
		return false;
	}
}

/**
 * @deprecated
 * @since 1.2.8
 */
function printAlbumPlace() {
	deprecated_functions::notify(gettext('Use printAlbumLocation().'));
	printAlbumLocation();
}

/**
 * @deprecated
 * @since 1.2.8
 */
function printEditable($context, $field, $editable = NULL, $editclass = 'unspecified', $messageIfEmpty = true, $convertBR = false, $override = false, $label = '') {
	deprecated_functions::notify(gettext('No longer supported.'));
	printField($context, $field, $convertBR, $override, $label);
}

/**
 * @deprecated
 * @since 1.2.9
 */
function rewrite_path_zenpage($rewrite = '', $plain = '') {
	deprecated_functions::notify(gettext('Use rewrite_path().'));
	return rewrite_path($rewrite, $plain);
}

/**
 * @deprecated
 * @since 1.2.9
 */
function getNewsImageTags() {
	deprecated_functions::notify(gettext('Use object->getTags() method.'));
	global $_zp_current_zenpage_news;
	if (is_GalleryNewsType() && is_object($_zp_current_zenpage_news)) {
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
 * @since 1.2.9
 */
function getNumSubalbums() {
	deprecated_functions::notify(gettext('Use getNumAlbums().'));
	return getNumAlbums();
}

/**
 * @deprecated
 * @since 1.2.9
 */
function getAllSubalbums($param = NULL) {
	deprecated_functions::notify(gettext('Use getAllAlbums().'));
	return getAllAlbums($param);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function addPluginScript($script) {
	deprecated_functions::notify(gettext('Register a "theme_head" filter.'));
	global $_zp_plugin_scripts;
	$_zp_plugin_scripts[] = $script;

	if (!function_exists('_emitPluginScripts')) {

		function _emitPluginScripts() {
			global $_zp_plugin_scripts;
			if (is_array($_zp_plugin_scripts)) {
				foreach ($_zp_plugin_scripts as $script) {
					echo $script . "\n";
				}
			}
		}

		zp_register_filter('theme_head', '_emitPluginScripts');
	}
}

/**
 * @deprecated
 * @since 1.4.0
 */
function zenJavascript() {
	deprecated_functions::notify(gettext('Use zp_apply_filter("theme_head").'));
	zp_apply_filter('theme_head');
}

/**
 * @deprecated
 * @since 1.4.0
 */
function normalizeColumns($albumColumns = NULL, $imageColumns = NULL) {
	deprecated_functions::notify(gettext('Use instead the theme options for images and albums per row.'), E_USER_NOTICE);
	global $_firstPageImages;
	setOption('albums_per_row', $albumColumns);
	setOption('images_per_row', $imageColumns);
	setThemeColumns();
	return $_firstPageImages;
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printParentPagesBreadcrumb($before = '', $after = '') {
	deprecated_functions::notify(gettext('Use printZenpageItemsBreadcrumb().'));
	printZenpageItemsBreadcrumb($before, $after);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function isMyAlbum($albumname, $action) {
	deprecated_functions::notify(gettext('Use instead the Album class method isMyItem().'), E_USER_NOTICE);
	$album = newAlbum($albumname);
	return $album->isMyItem($action);
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
 * @since 1.4.0
 */
function checkForPassword($hint, $show) {
	deprecated_functions::notify(gettext('There is no need for this function as password handling is done by the core.'), E_USER_NOTICE);
	return false;
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printAlbumMap($zoomlevel = NULL, $defaultmaptype = NULL, $width = NULL, $height = NULL, $text = NULL, $toggle = true, $id = 'googlemap', $firstPageImages = NULL, $mapselections = NULL, $addwiki = NULL, $background = NULL, $mapcontrol = NULL, $maptypecontrol = NULL, $customJS = NULL) {
	deprecated_functions::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap'))
		printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printImageMap($zoomlevel = NULL, $defaultmaptype = NULL, $width = NULL, $height = NULL, $text = NULL, $toggle = true, $id = 'googlemap', $mapselections = NULL, $addwiki = NULL, $background = NULL, $mapcontrol = NULL, $maptypecontrol = NULL, $customJS = NULL) {
	deprecated_functions::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap'))
		printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function setupAllowedMaps($defaultmap, $allowedmaps) {
	deprecated_functions::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printPreloadScript() {
	deprecated_functions::notify(gettext('printPreloadScript is deprecated. It is a helper for a specific theme and should be placed within that theme\'s "functions.php" script.'));
	global $_zp_current_image;
	$size = getOption('image_size');
	if (hasNextImage() || hasPrevImage()) {
		echo "<script type=\"text/javascript\">\n// <!-- <![CDATA[\n";
		if (hasNextImage()) {
			$nextimg = $_zp_current_image->getNextImage();
			echo "  nextimg = new Image();\n  nextimg.src = \"" . $nextimg->getSizedImage($size) . "\";\n";
		}
		if (hasPrevImage()) {
			$previmg = $_zp_current_image->getPrevImage();
			echo "  previmg = new Image();\n  previmg.src = \"" . $previmg->getSizedImage($size) . "\";\n";
		}
		echo "</script>\n\\ ]]> -->\n";
	}
}

/**
 * @deprecated
 * @since 1.4.1
 */
function processExpired($table) {
	deprecated_functions::notify(gettext('This happens automatically.'));
	global $_zp_zenpage;
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
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCombiNews($articles_per_page, $mode, $published, $sortorder, $sticky);
}

/**
 * @deprecated
 * @since 1.4.1
 */
function countCombiNews($published = NULL) {
	deprecated_functions::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->countCombiNews($published);
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
 * @since 1.4.1
 */
function isProtectedAlbum($album = NULL) {
	deprecated_functions::notify(gettext('Use the album class method <code>isProtected()</code> instead.'));
	global $_zp_current_album;
	if (is_null($album))
		$album = $_zp_current_album;
	return $album->isProtected();
}

/**
 * @deprecated
 * @since 1.4.2
 */
function getRSSHeaderLink($option, $linktext = '', $lang = '') {
	deprecated_functions::notify(gettext('Use the template function <code>getRSSLink()</code> instead. NOTE: While this function gets a full html link <code>getRSSLink()</code> just returns the URL.'));
	global $_zp_current_album;
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL . '://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	if (empty($lang)) {
		$lang = getOption("locale");
	}
	switch ($option) {
		case "Gallery":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss&amp;lang=" . $lang . "\" />\n";
			}
		case "Album":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss&amp;albumtitle=" . urlencode(getAlbumTitle()) . "&amp;albumname=" . urlencode($_zp_current_album->getFolder()) . "&amp;lang=" . $lang . "\" />\n";
			}
		case "Collection":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss&amp;albumtitle=" . urlencode(getAlbumTitle()) . "&amp;folder=" . urlencode($_zp_current_album->getFolder()) . "&amp;lang=" . $lang . "\" />\n";
			}
		case "Comments":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=comments&amp;lang=" . $lang . "\" />\n";
			}
		case "Comments-image":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=comments&amp;id=" . getImageID() . "&amp;title=" . urlencode(getImageTitle()) . "&amp;type=image&amp;lang=" . $lang . "\" />\n";
			}
		case "Comments-album":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=comments&amp;id=" . getAlbumID() . "&amp;title=" . urlencode(getAlbumTitle()) . "&amp;type=album&amp;lang=" . $lang . "\" />\n";
			}
		case "AlbumsRSS":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss=comments&amp;lang=" . $lang . "&amp;albumsmode\" />\n";
			}
	}
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
 * @since 1.4.2
 */
function generateCaptcha(&$img) {
	deprecated_functions::notify(gettext('Use $_zp_captcha->getCaptcha($prompt). Note that you will require updating your code to the new function.'));
	return $img = NULL;
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printAlbumZip() {
	deprecated_functions::notify(gettext('Use downloadList plugin <code>printDownloadLinkAlbumZip()</code>.'));
	global $_zp_current_album;
	enableExtension('downloadList', 20 | ADMIN_PLUGIN | THEME_PLUGIN, false);
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList.php');
	printDownloadLinkAlbumZip(gettext('Download a zip file of this album'), $_zp_current_album);
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printImageDiv() {
	deprecated_functions::notify(gettext('Use printImageThumb().'));
	if (!isset($_GET['sortable'])) {
		echo '<a href="' . html_encode(getImageLinkURL()) . '" title="' . html_encode(getImageTitle()) . '">';
	}
	printImageThumb(getImageTitle());
	if (!isset($_GET['sortable'])) {
		echo '</a>';
	}
}

/**
 * @deprecated
 * @since 1.4.3
 */
function getImageID() {
	deprecated_functions::notify(gettext('Use $_zp_current_image->getID().'));
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getID();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printImageID() {
	deprecated_functions::notify(gettext('Use echo "image_".$_zp_current_image->getID().'));
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	echo "image_" . getImageID();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function getAlbumId() {
	deprecated_functions::notify(gettext('Use echo "image_".$_zp_current_album->getID().'));
	global $_zp_current_album;
	if (is_null($_zp_current_album)) {
		return null;
	}
	return $_zp_current_album->getID();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function resetCurrentAlbum() {
	deprecated_functions::notify(gettext('Just what do you expect this does?'));
	global $_zp_images, $_zp_current_album;
	$_zp_images = NULL;
	$_zp_current_album->images = NULL;
	setThemeColumns();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function setAlbumCustomData($val) {
	deprecated_functions::notify(gettext('Use object methods.'));
	global $_zp_current_album;
	$_zp_current_album->setCustomData($val);
	$_zp_current_album->save();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function setImageCustomData($val) {
	deprecated_functions::notify(gettext('Use object methods.'));
	Global $_zp_current_image;
	$_zp_current_image->setCustomData($val);
	$_zp_current_image->save();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function getImageSortOrder() {
	deprecated_functions::notify(gettext('Use object methods.'));
	if (!in_context(ZP_IMAGE))
		return false;
	global $_zp_current_image;
	return $_zp_current_image->getSortOrder();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printImageSortOrder() {
	deprecated_functions::notify(gettext('Use echo $_zp_current_image->getSortOrder().'));
	if (!in_context(ZP_IMAGE))
		return false;
	echo getImageSortOrder();
}

/**
 * @deprecated
 * @since 1.4.3
 */
function getFirstImageURL() {
	deprecated_functions::notify('');
	global $_zp_current_album;
	if (is_null($_zp_current_album))
		return false;
	$firstimg = $_zp_current_album->getImage(0);
	return rewrite_path("/" . pathurlencode($_zp_current_album->name) . "/" . urlencode($firstimg->filename) . IM_SUFFIX, "/index.php?album=" . pathurlencode($_zp_current_album->name) . "&image=" . urlencode($firstimg->filename));
}

/**
 * @deprecated
 * @since 1.4.3
 */
function getLastImageURL() {
	deprecated_functions::notify('');
	global $_zp_current_album;
	if (is_null($_zp_current_album))
		return false;
	$lastimg = $_zp_current_album->getImage($_zp_current_album->getNumImages() - 1);
	return rewrite_path("/" . pathurlencode($_zp_current_album->name) . "/" . urlencode($lastimg->filename) . IM_SUFFIX, "/index.php?album=" . pathurlencode($_zp_current_album->name) . "&image=" . urlencode($lastimg->filename));
}

/**
 * @deprecated
 * @since 1.4.4
 */
function getTheme(&$zenCSS, &$themeColor, $defaultColor) {
	deprecated_functions::notify(gettext("If you need this function copy it to your theme's functions.php script."));
	global $_zp_themeroot;
	$themeColor = getThemeOption('Theme_colors');
	$zenCSS = $_zp_themeroot . '/styles/' . $themeColor . '.css';
	$unzenCSS = str_replace(WEBPATH, '', $zenCSS);
	if (!file_exists(SERVERPATH . internalToFilesystem($unzenCSS))) {
		$zenCSS = $_zp_themeroot . "/styles/" . $defaultColor . ".css";
		return ($themeColor == '');
	} else {
		return true;
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
function printCaptcha($preText = '', $midText = '', $postText = '') {
	global $_zp_captcha;
	deprecated_functions::notify(gettext('use $_zp_captcha->getCaptcha() and format the results as desired.'));
	if ($_zp_captcha && getOption('Use_Captcha')) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA <strong>*</strong>"));
		if (isset($captcha['hidden']))
			echo $captcha['hidden'];
		echo $preText;
		if (isset($captcha['input'])) {
			echo $captcha['input'];
			echo $midText;
		}
		if (isset($captcha['html']))
			echo $captcha['html'];
		echo $postText;
	}
}

/**
 * @deprecated
 * @since 1.4.5
 */
function printField($context, $field, $convertBR = NULL, $override = false, $label = '') {
	deprecated_functions::notify(gettext('Front end editing is not supported. Use the property specific methods.'));
	if (is_null($convertBR))
		$convertBR = !extensionEnabled('tiny_mce');
	switch ($context) {
		case 'image':
			global $_zp_current_image;
			$object = $_zp_current_image;
			break;
		case 'album':
			global $_zp_current_album;
			$object = $_zp_current_album;
			break;
		case 'pages':
			global $_zp_current_zenpage_page;
			$object = $_zp_current_zenpage_page;
			break;
		case 'news':
			global $_zp_current_zenpage_news;
			$object = $_zp_current_zenpage_news;
			break;
		default:
			trigger_error(sprintf(gettext('printField() invalid function call, context %X.'), $context), E_USER_NOTICE);
			return false;
	}
	if (!$field) {
		trigger_error(sprintf(gettext('printField() invalid function call, field:%s.'), $field), E_USER_NOTICE);
		return false;
	}
	if (!is_object($object)) {
		trigger_error(gettext('printField() invalid function call, not an object.'), E_USER_NOTICE);
		return false;
	}
	if ($override) {
		$text = trim($override);
	} else {
		$text = trim(get_language_string($object->get($field)));
	}
	$text = zpFunctions::unTagURLs($text);

	$text = html_encodeTagged($text);
	if ($convertBR) {
		$text = str_replace("\r\n", "\n", $text);
		$text = str_replace("\n", "<br />", $text);
	}

	if (!empty($text))
		echo $label;
	echo $text;
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
 * @deprecated
 * @since 1.4.5
 */
function printAdminToolbox($id = 'admin', $customDIV = false) {
	deprecated_functions::notify(gettext('This feature is now done by a "theme_body_close" filter. You can remove the function call.'));
}

/**
 * @deprecated
 * @since 1.4.5
 */
function printSlideShowJS() {
	deprecated_functions::notify(gettext('This feature is now done by a "theme_head" filter. You can remove the function call.'));
}

?>
