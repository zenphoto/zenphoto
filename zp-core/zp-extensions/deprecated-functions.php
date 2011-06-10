<?php
/**
 * These functions have been removed from mainstream Zenphoto as they have been
 * supplanted.
 *
 * They are not maintained and they are not guaranteed to function correctly with the
 * current version of Zenphoto.
 *
 * @package plugins
 */
$plugin_description = gettext("Deprecated Zenphoto functions. These functions have been removed from mainstream Zenphoto as they have been supplanted. They are not maintained and they are not guaranteed to function correctly with the current version of Zenphoto.  You should update your theme if you get warnings. This plugin is not required for any theme coded for the current version of Zenphoto.");
$option_interface = 'deprecated_functions';
$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_version = '1.4.1';

class deprecated_functions {

	var $listed_functions = array();

	function deprecated_functions() {
		global $_internalFunctions;
		$deprecated = file_get_contents(__FILE__);
		$i = strpos($deprecated, '//'.' IMPORTANT:: place all deprecated functions below this line!!!');
		$deprecated = substr($deprecated, $i);
		preg_match_all('/function\040+(.*)\040?\(.*\)\040?\{/',$deprecated,$functions);
		$this->listed_functions = array_merge($functions[1],$_internalFunctions);
		// remove the items from this class and notify function, leaving only the deprecated functions
		foreach ($this->listed_functions as $key=>$funct) {
			if ($funct == '_emitPluginScripts') {	// special case!!!!
				unset($this->listed_functions[$key]);
			} else {
				setOptionDefault('deprecated_'.$funct,1);
			}
		}
	}

	function getOptionsSupported() {
		$list = array();
		foreach ($this->listed_functions as $funct) {
			$list[$funct] = 'deprecated_'.$funct;
		}
		return array(gettext('Functions')=>array('key' => 'deprecated_Function_list', 'type' => OPTION_TYPE_CHECKBOX_UL,
												'checkboxes' => $list,
												'desc' => gettext('Send the <em>deprecated</em> notification message if the function name is checked. Un-checking these boxes will allow you to continue using your theme without warnings while you upgrade its implementation.')));
	}
}

/*
 * used to provided deprecated function notification.
 */
function deprecated_function_notify($use) {
	$traces = @debug_backtrace();
	$fcn = $traces[1]['function'];
	if (empty($fcn) || getOption('deprecated_'.$fcn)) {
		if (empty($fcn)) $fcn = gettext('function');
		if (!empty($use)) $use = ' '.$use;
		if (isset($traces[1]['file']) && isset($traces[1]['line'])) {
			$script = basename($traces[1]['file']);
			$line = $traces[1]['line'];
		} else {
			$script = $line = gettext('unknown');
		}
		// insure that the error shows!
		$old_reporting = error_reporting();
		if (version_compare(PHP_VERSION,'5.0.0') == 1) {
			error_reporting(E_ALL | E_STRICT);
		} else {
			error_reporting(E_ALL);
		}
		trigger_error(sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated'),$fcn,$script,$line).$use, E_USER_NOTICE);
		error_reporting($old_reporting);
	}
}

// IMPORTANT:: place all deprecated functions below this line!!!

/**
 * @deprecated
 * Enter description here ...
 * @param $mode
 * @param $obj
 */
function getZenpageHitcounter($mode="",$obj=NULL) {
	deprecated_function_notify(gettext('Use getHitcounter().'));
	global $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_gallery_page, $_zp_current_category;
	switch($mode) {
		case "news":
			if((is_NewsArticle() OR is_News()) AND !is_object($obj)) {
				$obj = $_zp_current_zenpage_news;
				$hc = $obj->get('hitcounter');
			} else if(is_object($obj)) {
				$hc = $obj->get('hitcounter');
			} else {
				$hc = 0;
			}
			return $hc;
			break;
		case "page":
			if(is_Pages() AND !is_object($obj)) {
				$obj = $_zp_current_zenpage_page;
				$hc = $obj->get('hitcounter');
			} else if(is_object($obj)) {
				$hc = $obj->get('hitcounter');
			} else {
				$hc = 0;
			}
			return $hc;
			break;
		case "category":
			if(!is_object($obj) || is_NewsCategory() AND !empty($obj)) {
				$obj = $_zp_current_category;
				$hc = $obj->get('hitcounter');
			} else if(is_object($obj)) {
				$hc = $obj->get('hitcounter');
			} else {
				$hc = 0;
			}
			return $hc;
			break;
	}
}

/**
 * @deprecated
 * Enter description here ...
 * @param $object
 */
function printImageRating($object=NULL) {
	deprecated_function_notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object)) $object = $_zp_current_image;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printAlbumRating($object=NULL) {
	deprecated_function_notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object)) $object = $_zp_current_album;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printImageEXIFData() {
	deprecated_function_notify(gettext('Use printImageMetadata().'));
	if (isImageVideo()) {
	} else {
		printImageMetadata();
	}
}

/**
 * @deprecated
 */
function printCustomSizedImageMaxHeight($maxheight) {
	deprecated_function_notify(gettext('Use printCustomSizedImageMaxSpace().'));
	if (getFullWidth() === getFullHeight() OR getDefaultHeight() > $maxheight) {
		printCustomSizedImage(getImageTitle(), null, null, $maxheight, null, null, null, null, null, null);
	} else {
		printDefaultSizedImage(getImageTitle());
	}
}

/**
 * @deprecated
 */
function getCommentDate($format = NULL) {
	deprecated_function_notify(gettext('Use getCommentDateTime().'));
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
 */
function getCommentTime($format = '%I:%M %p') {
	deprecated_function_notify(gettext('Use getCommentDateTime().'));
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * @deprecated
 */
function hitcounter($option='image', $viewonly=false, $id=NULL) {
	deprecated_function_notify(gettext('Use getHitcounter().'));
	switch($option) {
		case "image":
			if (is_null($id)) {
				$id = getImageID();
			}
			$dbtable = prefix('images');
			break;
		case "album":
			if (is_null($id)) {
				$id = getAlbumID();
			}
			$dbtable = prefix('albums');
			break;
	}
	$sql = "SELECT `hitcounter` FROM $dbtable WHERE `id` = $id";
	$result = query_single_row($sql);
	$resultupdate = $result['hitcounter'];
	return $resultupdate;
}

/**
 * @deprecated
 */
function my_truncate_string($string, $length) {
	deprecated_function_notify(gettext('Use truncate_string().'));
	if (strlen($string) > $length) {
		$short = substr($string, 0, $length);
		return $short. '...';
	} else {
		return $string;
	}
}

/**
 * @deprecated
 */
function getImageEXIFData() {
	deprecated_function_notify(gettext('Use getImageMetaData().'));
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getMetaData();
}

/**
 * @deprecated
 */
function getAlbumPlace() {
	deprecated_function_notify(gettext('Use getAlbumLocation().'));
	global $_zp_current_album;
	if (is_object($_zp_current_album)) 	return $_zp_current_album->getLocation();
}

/**
 * @deprecated
 */
function printAlbumPlace($editable=false, $editclass='', $messageIfEmpty = true) {
	deprecated_function_notify(gettext('Use printAlbumLocation().'));
	if ( $messageIfEmpty === true ) {
		$messageIfEmpty = gettext('(No place...)');
	}
	printEditable('album', 'location', $editable, $editclass, $messageIfEmpty, !getOption('tinyMCEPresent'));
}


/***************************
 * ZENPAGE PLUGIN FUNCTIONS
 ***************************/

/**
 * @deprecated
 */
function zenpageHitcounter($option='pages', $viewonly=false, $id=NULL) {
	deprecated_function_notify(gettext('Use getHitcounter().'));
	global $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	switch($option) {
		case "pages":
			if (is_null($id)) {
				$id = getPageID();
			}
			$dbtable = prefix('pages');
			$doUpdate = true;
			break;
		case "category":
			if (is_null($id)) {
				$id = getCurrentNewsCategoryID();
			}
			$dbtable = prefix('news_categories');
			$doUpdate = getCurrentNewsPage() == 1; // only count initial page for a hit on an album
			break;
		case "news":
			if (is_null($id)) {
				$id = getNewsID();
			}
			$dbtable = prefix('news');
			$doUpdate = true;
			break;
	}
	if(($option == "pages" AND is_Pages()) OR ($option == "news" AND is_NewsArticle()) OR ($option == "category" AND is_NewsCategory())) {
		if ((zp_loggedin(ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS)) || $viewonly) { $doUpdate = false; }
		$hitcounter = "hitcounter";
		$whereID = " WHERE `id` = $id";
		$sql = "SELECT `".$hitcounter."` FROM $dbtable $whereID";
		if ($doUpdate) { $sql .= " FOR UPDATE"; }
		$result = query_single_row($sql);
		$resultupdate = $result['hitcounter'];
		if ($doUpdate) {
			$resultupdate++;
			query("UPDATE $dbtable SET `".$hitcounter."`= $resultupdate $whereID");
		}
		return $resultupdate;
	}
}

/**
 * @deprecated
 */
function rewrite_path_zenpage($rewrite='',$plain='') {
	deprecated_function_notify(gettext('Use rewrite_path().'));
	if (MOD_REWRITE) {
		return $rewrite;
	} else {
		return $plain;
	}
}

/**
 * @deprecated
 */
function getNewsImageTags() {
	deprecated_function_notify(gettext('Use object->getTags() method.'));
	global $_zp_current_zenpage_news;
	if(is_GalleryNewsType() && is_object($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getTags();
	} else {
		return false;
	}
}

/**
 * @deprecated
 */
function printNewsImageTags($option='links',$preText=NULL,$class='taglist',$separator=', ',$editable=TRUE) {
	deprecated_function_notify(gettext('Use printTags().'));
	global $_zp_current_zenpage_news;
	if(is_GalleryNewsType()) {
		$singletag = getNewsImageTags();
		$tagstring = implode(', ', $singletag);
		if (empty($tagstring)) { $preText = ""; }
		if (count($singletag) > 0) {
			echo "<ul class=\"".$class."\">\n";
			if (!empty($preText)) {
				echo "<li class=\"tags_title\">".$preText."</li>";
			}
			$ct = count($singletag);
			foreach ($singletag as $atag) {
				if ($x++ == $ct) { $separator = ""; }
				if ($option == "links") {
					$links1 = "<a href=\"".html_encode(getSearchURL($atag, '', 'tags', 0))."\" title=\"".$atag."\" rel=\"nofollow\">";
					$links2 = "</a>";
				}
				echo "\t<li>".$links1.html_encode($atag).$links2.$separator."</li>\n";
			}

			echo "</ul>";

			echo "<br clear=\"all\" />\n";
		}
	}
}

/**
 * @deprecated
 */
function getNumSubalbums() {
	deprecated_function_notify(gettext('Use getNumAlbums().'));
	return getNumAlbums();
}

/**
 * @deprecated
 */
function getAllSubalbums($param=NULL) {
	deprecated_function_notify(gettext('Use getAllAlbums().'));
	return getAllAlbums($param);
}

/**
 * @deprecated
 */
function addPluginScript($script) {
	deprecated_function_notify(gettext('Register a "theme_head" filter.'));
	global $_zp_plugin_scripts;
	$_zp_plugin_scripts[] = $script;

	if (!function_exists('_emitPluginScripts')) {
		function _emitPluginScripts() {
			global $_zp_plugin_scripts;
			if (is_array($_zp_plugin_scripts)) {
				foreach ($_zp_plugin_scripts as $script) {
					echo $script."\n";
				}
			}
		}
		zp_register_filter('theme_head','_emitPluginScripts');
	}
}

/**
 * @deprecated
 */
function zenJavascript() {
	deprecated_function_notify(gettext('Use zp_apply_filter("theme_head").'));
	zp_apply_filter('theme_head');
}

/**
 * @deprecated
 */
function normalizeColumns($albumColumns=NULL, $imageColumns=NULL) {
	deprecated_function_notify(gettext('Use instead the theme options for images and albums per row.'), E_USER_NOTICE);
	global $_firstPageImages;
	setOption('albums_per_row',$albumColumns);
	setOption('images_per_row',$imageColumns);
	setThemeColumns();
	return $_firstPageImages;
}

/**
 * @deprecated
 */
function printParentPagesBreadcrumb($before='', $after='') {
	deprecated_function_notify(gettext('Use printZenpageItemsBreadcrumb().'));
	printZenpageItemsBreadcrumb($before, $after);
}

/**
 * @deprecated
 */
function isMyAlbum($albumname, $action) {
	deprecated_function_notify(gettext('Use instead the Album class method isMyItem().'), E_USER_NOTICE);
	$album = new Album(new Gallery(), $albumname);
	return $album->isMyItem($action);
}

/***************************
 * Category class changes
 ***************************/

/**
 * Return an array of the catlinks of the subcategories of the requested category
 *
 * @param string $catlink the catlink of the sub categories to get of
 * @return array
 * @deprecated
 */
function getSubCategories($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getSubCategories().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSubCategories();
}

/**
 * Checks if an article (not CombiNews gallery items!) is in a password protected category and returns TRUE or FALSE
 * NOTE: This function does not check if the password has been entered! Use checkAccess() for that.
 *
 * @param bool $checkProtection If set to TRUE (default) this check if the article is actually protected (remember only articles that are in the protected category only are!).
 * 															If set to FALSE it simply checks if it is in an otherwise protected category at all
 * @param obj $articleobj Optional news article object to check directly, if empty the current news article is checked if available
 * @return bool
 * @deprecated
 */
function inProtectedNewsCategory($articleobj=NULL,$checkProtection=true) {
	deprecated_function_notify(gettext('Use instead the Zenpage news class method inProtectedCategory().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	if(empty($articleobj) && !is_null($_zp_current_zenpage_news) && get_class($_zp_current_zenpage_news) == 'zenpagenews') {
		$articleobj = $_zp_current_zenpage_news;
	}
	if (!is_object($articleobj)) return false;
	return $articleobj->inProtectedCategory();
	$categories = $articleobj->getCategories();
}

/**
 * Checks if a category is protected and returns TRUE or FALSE
 * NOTE: This function does only check if a password is set not if it has been entered! Use checkNewsCategoryPassword() for that.
 *
 * @param string $catlink The optional categorylink of a category, if empty the current category is checked if available
 * @return bool
 * @deprecated
 */
function isProtectedNewsCategory($catlink='') {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_category;
	if(empty($catlink) && !is_null($_zp_current_category)) {
		$cat = $_zp_current_category;
	} else {
		$cat = new ZenpageCategory($catlink);
	}
	return $cat->isProtected();
}

/**
 * Gets the parent categories recursivly to the category whose parentid is passed
 *
 * @param int $parentid The parentid of the page to get the parents of
 * @param bool $initparents
 * @return array
 * @deprecated
 */
function getParentNewsCategories($parentid,$initparents=true) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getParents().'), E_USER_NOTICE);
	return getParentItems('categories',$parentid,$initparents);
}

/**
 * Gets the category title of a category
 *
 * @param string $catlink the categorylink of the category
 * @return string
 * @deprecated
 */
function getCategoryTitle($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getTitle().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getTitle();
}

/**
 * Gets the id of a category
 *
 * @param string $catlink the categorylink of the category id to get
 * @return int
 * @deprecated
 */
function getCategoryID($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getID().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getID();
}

/**
 * Gets the parentid of a category
 *
 * @param string $catlink the categorylink of the category id to get
 * @return int
 * @deprecated
 */
function getCategoryParentID($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getParentID().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getParentID();
}

/**
 * Gets the sortorder of a category. Returns false if not.
 *
 * @param string $catlink the categorylink of the category sortorder to get
 * @return string
 * @deprecated
 */
function getCategorySortOrder($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method getSortOrder().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSortOrder();
}

/**
 * Gets the parent pages recursivly to the page whose parentid is passed
 *
 * @param int $parentid The parentid of the page to get the parents of
 * @param bool $initparents
 * @return array
 * @deprecated
 */
function getParentPages(&$parentid,$initparents=true) {
	deprecated_function_notify(gettext('Use instead the Zenpage page class method getParents().'), E_USER_NOTICE);
	return getParentItems('pages',$parentid,$initparents);
}

/**
 * Checks if the page is itself password protected or is inheriting protection from a parent pages.
 * NOTE: This function does only check if a password is set not if it has been entered! Use checkPagePassword() for that.
 *
 * @param obj $pageobj Optional page object to test directly, otherwise the current page is checked if available.
 * @deprecated
 */
function isProtectedPage($pageobj=NULL) {
	deprecated_function_notify(gettext('Use instead the Zenpage page class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj)) $pageobj = $_zp_current_zenpage_page;
	return $pageobj->checkforGuest() != 'zp_public_access';
}

/**
 * Checks if user is author of page
 * @param object $pageobj
 * @param bit $action
 * @deprecated
 */
function isMyPage($pageobj=NULL, $action) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj)) $pageobj = $_zp_current_zenpage_page;
	return $pageobj->isMyItem($action);
}

/**
 * Checks for allowed access to a page
 * @param object $pageobj
 * @param string $hint
 * @param bool $show
 * @deprecated
 */
function checkPagePassword($pageobj, &$hint, &$show) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	return $pageobj->checkforGuest();
}

//	News category password functions

/**
 * Checks if user is news author
 * @param object $newsobj News object being checked
 * @param $action
 * @deprecated
 */
function isMyNews($newsobj, $action) {
	deprecated_function_notify(gettext('Use instead the Zenpage news class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->isMyItem();
}

/**
 * Checks if user is allowed access t the news article
 * @param $newsobj
 * @param $hint
 * @param $show
 * @deprecated
 */
function checkNewsAccess($newsobj, &$hint, &$show) {
	deprecated_function_notify(gettext('Use instead the Zenpage news class method checkNewsAccess().'), E_USER_NOTICE);
	return $newsobj->checkNewsAccess($hint, $show);
}

/**
 * Checks if user is allowed to access News category
 * @param $catlink
 * @param $hint
 * @param $show
 * @deprecated
 */
function checkNewsCategoryPassword($catlink, $hint, $show) {
	deprecated_function_notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->checkforGuest();
}

/**
 * Gets the title of the currently selected news category
 *
 * @return string
 * @deprecated
 */
function getCurrentNewsCategory() {
	deprecated_function_notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getTitlelink().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getTitlelink();
}

/**
 * Gets the id of the current selected news category
 *
 * @return int
 * @deprecated
 */
function getCurrentNewsCategoryID() {
	deprecated_function_notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getID();
}

/**
 * Gets the parentid of the current selected news category
 *
 * @return int
 * @deprecated
 */
function getCurrentNewsCategoryParentID() {
	deprecated_function_notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getParentID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getParentID();
}

/**
 * Checks if an article is in a category and returns TRUE or FALSE
 *
 * @param string $catlink The titlelink of a category
 * @return bool
 * @deprecated
 */
function inNewsCategory($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inNewsCategory($catlink).'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->inNewsCategory($catlink);
}

/**
 * Checks if an article is in a sub category of $catlink
 *
 * @param string $catlink The titlelink of a category
 * @return bool
 * @deprecated
 */
function inSubNewsCategoryOf($catlink) {
	deprecated_function_notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink);
}

/**
 * Checks if the current news category is a sub category of $catlink
 *
 * @param string $catlink The categorylink of a category
 * @return bool
 * @deprecated
 */
function isSubNewsCategoryOf($catlink) {
	global $_zp_current_category;
	deprecated_function_notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->isSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
	return $_zp_current_category->isSubNewsCategoryOf($catlink);
}

/**
 * Prints the read more link or if using CombiNews feature also the link to the image.php gallery page as a full html link
 *
 * @param string $readmore The readmore text to be shown for the full news article link. If empty the option setting is used.
 * @return string
 * @deprecated
 */
function printNewsReadMoreLink($readmore='') {
	deprecated_function_notify(gettext('Functionality is now included in getNewsContent(), printNewsContent() and getContentShorten() to properly cover custom shortening via TinyMCE <pagebreak>.'), E_USER_NOTICE);
	$readmore = getNewsReadMore($readmore);
	if(!empty($readmore)) {
		if(is_NewsType("news")) {
			$newsurl = getNewsURL(getNewsTitleLink());
		} else {
			$newsurl = html_encode(getNewsTitleLink());
		}
		echo "<a href='".$newsurl."' title=\"".getBareNewsTitle()."\">".html_encode($readmore)."</a>";
	}
}

/**
 * Shorten the content of any type of item and add the shorten indicator and readmore link
 * set on the Zenpage plugin options. Helper function for getNewsContent() but usage of course not limited to that.
 * If there is nothing to shorten the content passed.
 *
 * The read more link is wrapped within <p class="readmorelink"></p>.
 *
 * @param string $articlecontent The article or page content or image/album description for CombiNews to shorten
 * @param integer $shorten The lenght the content should be shortened
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 * @deprecated
 */
function getNewsContentShorten($articlecontent,$shorten,$shortenindicator='',$readmore='') {
	deprecated_function_notify(gettext('Use getContentShorten() instead. Note the read more url must be passed directly.'), E_USER_NOTICE);
	return getContentShorten($articlecontent,$shorten,$shortenindicator,'');
}

/**
 * @deprecated
 */
function checkForPassword($hint, $show) {
	deprecated_function_notify(gettext('There is no need for this function as password handling is done by the core.'), E_USER_NOTICE);
	return false;
}

/**
 * @deprecated
 */
function printAlbumMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $firstPageImages=NULL, $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL){
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function printImageMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL) {
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function setupAllowedMaps($defaultmap, $allowedmaps) {
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
}

/**
 * @deprecated
 */
function printPreloadScript() {
	deprecated_function_notify(gettext('printPreloadScript is deprecated. It is a helper for a specific theme and should be placed within that theme\'s "functions.php" script.'));
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

/*****************************/
$_internalFunctions = array (
	'getSearchURL',
	'printPasswordForm'
);


/********************************************
 * former zenpage-functions.php functions
 ********************** *********************/

/**
 * Un-publishes pages/news whose expiration date has been reached
 *
 */
function processExpired($table) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	$expire = date('Y-m-d H:i:s');
	query('update'.prefix($table).'SET `show`=0 WHERE `date`<="'.$expire.'"'.
		' AND `expiredate`<="'.$expire.'"'.
		' AND `expiredate`!="0000-00-00 00:00:00"'.
		' AND `expiredate`!=NULL');
}

/**
 * Gets the parent items recursivly to the item whose parentid is passed
 * @param string $mode "pages" or "categories"
 * @param int $parentid The parentid of the page to get the parents of
 * @param bool $initparents If the
 * @return array
 */
function getParentItems($mode='pages',&$parentid,$initparents=true) {
	deprecated_function_notify(gettext('Use the method from either the ZenpagePage or the ZenpageCategory class instead.'));
	global $_zp_current_zenpage_page, $_zp_current_category;
	switch($mode) {
		case 'pages':
			if(!is_null($_zp_current_zenpage_page))	return $_zp_current_zenpage_page->getParents();
			break;
		case 'categories':
			if(!is_null($_zp_current_category)) return $_zp_current_category->getParents();
			break;
	}
}



/* general page functions   */


/**
 * Gets the titlelink and sort order for all pages or published ones.
 *
 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude pages that are password protected
 *
 * @param bool $published TRUE for published or FALSE for all pages including un-published
 * @return array
 */
function getPages($published=NULL) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage_all_pages;
	processExpired('pages');
	if (is_null($published)) {
		if(zp_loggedin(ZENPAGE_PAGES_RIGHTS)) {
			$published = FALSE;
		} else {
			$published = TRUE;
		}
	}
	if($published) {
		$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
	} else {
		$show = '';
	}
	$_zp_zenpage_all_pages = NULL; // Disabled cache var for now because it does not return un-publishded and published if logged on index.php somehow if logged in.
	if(is_null($_zp_zenpage_all_pages)) {
		$_zp_zenpage_all_pages  = query_full_array("SELECT * FROM ".prefix('pages').$show." ORDER by `sort_order`");
		return $_zp_zenpage_all_pages;
	} else {
		return $_zp_zenpage_all_pages;
	}
}





/* general news article functions   */


/**
 * Gets news articles titlelink either all or by category or by archive date.
 *
 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude articles that are password protected via a category
 *
 *
 * @param int $articles_per_page The number of articles to get
 * @param string $category The categorylink of the category
 * @param string $published "published" for an published articles,
 * 													"unpublished" for an unpublised articles,
 * 													"sticky" for sticky articles,
 * 													"all" for all articles
 * @param boolean $ignorepagination Since also used for the news loop this function automatically paginates the results if the "page" GET variable is set. To avoid this behaviour if using it directly to get articles set this TRUE (default FALSE)
 * @param string $sortorder "date" for sorting by date (default)
 * 													"title" for sorting by title
 * 													This parameter is not used for date archives
 * @param string $sortdirection "desc" (default) for descending sort order
 * 													    "asc" for ascending sort order
 * 											        This parameter is not used for date archives
 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
 * @return array
 */
function getNewsArticles($articles_per_page='', $category='', $published=NULL,$ignorepagination=false,$sortorder="date", $sortdirection="desc",$sticky=true) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_current_category, $_zp_post_date;
	processExpired('news');
	if (is_null($published)) {
		if(zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
			$published = "all";
		} else {
			$published = "published";
		}
	}
	$show = "";
	// new code to get nested cats
	if (!empty($category)) {
		$catobj = new ZenpageCategory($category);
		$catid = $catobj->getID();
		$subcats = $catobj->getSubCategories();
		if($subcats) {
			$cat = " (cat.cat_id = '".$catid."'";
			foreach($subcats as $subcat) {
				$subcatobj = new ZenpageCategory($subcat);
				$cat .= "OR cat.cat_id = '".$subcatobj->getID()."' ";
			}
			$cat .= ") AND cat.news_id = news.id ";
		} else {
			$cat = " cat.cat_id = '".$catid."' AND cat.news_id = news.id ";
		}
	} elseif(in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		$catid = $_zp_current_category->getID();
		$subcats = $_zp_current_category->getSubCategories();
		if($subcats) {
			$cat = " (cat.cat_id = '".$catid."' AND cat.news_id = news.id) ";
			foreach($subcats as $subcat) {
				$subcatobj = new ZenpageCategory($subcat);
				$cat .= "OR (cat.cat_id = '".$subcatobj->getID()."' AND cat.news_id = news.id) ";
			}
		} else {
			$cat = " cat.cat_id = '".$catid."' AND cat.news_id = news.id ";
		}
	} else {
		$catid = '';
		$cat ='';
	}
	if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
		$postdate = $_zp_post_date;
	} else {
		$postdate = NULL;
	}
	$limit = getLimitAndOffset($articles_per_page,$ignorepagination);
	if ($sticky) {
		$sticky = 'sticky DESC,';
	}
	// sortorder and sortdirection (only used for all news articles and categories naturally)
	switch($sortorder) {
		case "date":
		default:
			$sort1 = "date";
			break;
		case "title":
			$sort1 = "title";
			break;
	}
	switch($sortdirection) {
		case "desc":
		default:
			$dir = "DESC";
			break;
		case "asc":
			$dir = "ASC";
			$sticky = false;	//makes no sense
			break;
	}
	if (!empty($category) OR in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		/*** get articles by category ***/
		switch($published) {
			case "published":
				$show = " AND `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "unpublished":
				$show = " AND `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case 'sticky':
				$show = ' AND `sticky` <> 0';
				break;
			case "all":
				$show = "";
				break;
		}
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			$datesearch = " AND news.date LIKE '".$postdate."%' ";
			$order = " ORDER BY ".$sticky."news.date DESC";
		} else {
			$datesearch = "";
			$order = " ORDER BY ".$sticky."news.$sort1 $dir";
		}
		$sql = "SELECT DISTINCT news.titlelink FROM ".prefix('news')." as news, ".prefix('news2cat')." as cat WHERE".$cat.$show.$datesearch.$order.$limit;
		$result = query_full_array($sql);

	} else {
		/***get all articles ***/
		switch($published) {
			case "published":
				$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "unpublished":
				$show = " WHERE `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case 'sticky':
				$show = ' WHERE `sticky` <> 0';
				break;
			case "all":
				$show = "";
				break;
		}
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			switch($published) {
				case "published":
					$datesearch = " AND date LIKE '$postdate%' ";
					break;
				case "unpublished":
					$datesearch = " WHERE date LIKE '$postdate%' ";
					break;
				case 'sticky':
					$show = ' WHERE `sticky` <> 0';
					break;
				case "all":
					$datesearch = " WHERE date LIKE '$postdate%' ";
					break;
			}
			$order = " ORDER BY $sticky date DESC";
		} else {
			$datesearch = "";
			$order = " ORDER BY ".$sticky.$sort1." ".$dir;
		}
		$sql = "SELECT titlelink FROM ".prefix('news').$show.$datesearch." ".$order.$limit;
		$result = query_full_array($sql);
	}
	return $result;
}


/**
 * Counts news articles, either all or by category or archive date, published or un-published
 *
 * @param string $category The categorylink of the category to count
 * @param string $published "published" for an published articles,
 * 													"unpublished" for an unpublised articles,
 * 													"all" for all articles
 * @return array
 */
function countArticles($category='', $published='published',$count_subcat_articles=true) {
	deprecated_function_notify(gettext('Count the articles instead.'));		global $_zp_post_date;
	if(zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
		$published = "all";
	} else {
		$published = "published";
	}
	$show="";
	if (empty($category)) {
		switch($published) {
			case "published":
				$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "unpublished":
				$show = " WHERE `show` = 0 AND date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "all":
				$show = "";
				break;
		}
		// date archive query addition
		if(in_context(ZP_ZENPAGE_NEWS_DATE)) {
			$postdate = $_zp_post_date;
			if(empty($show)) {
				$and = " WHERE ";
			} else {
				$and = " AND ";
			}
			$datesearch = $and."date LIKE '$postdate%'";
		} else {
			$datesearch = "";
		}
		$result = query("SELECT COUNT(*) FROM ".prefix('news').$show.$datesearch);
		$row = db_fetch_row($result);
		$count = $row[0];
		return $count;
	} else {
		$catobj = new ZenpageCategory($category);
		switch($published) {
			case "published":
				$show = " AND news.show = 1 AND news.date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "unpublished":
				$show = " AND news.show = 0 AND news.date <= '".date('Y-m-d H:i:s')."'";
				break;
			case "all":
				$show = "";
				break;
		}
		if($count_subcat_articles) $subcats = $catobj->getSubCategories();
		if($subcats && $count_subcat_articles) {
			$cat = " (cat.cat_id = '".$catobj->getID()."'";
			foreach($subcats as $subcat) {
				$subcatobj = new ZenpageCategory($subcat);
				$cat .= "OR cat.cat_id = '".$subcatobj->getID()."' ";
			}
			$cat .= ") AND cat.news_id = news.id ";
		} else {
			$cat = " cat.cat_id = '".$catobj->getID()."' AND cat.news_id = news.id ";
		}
		$result = query_full_array("SELECT DISTINCT news.titlelink FROM ".prefix('news2cat')." as cat, ".prefix('news')." as news WHERE ".$cat.$show);
		$count = count($result);
		return $count;
	}
}

/**
 * Gets the LIMIT and OFFSET for the query that gets the news articles
 *
 * @param int $articles_per_page The number of articles to get
 * @param bool $ignorepagination If pagination should be ingored so always with the first is started (false is default)
 * @return string
 */
function getLimitAndOffset($articles_per_page,$ignorepagination=false) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage_total_pages;
	if(strstr(dirname($_SERVER['REQUEST_URI']), '/'.PLUGIN_FOLDER.'/zenpage')) {
		$page = getCurrentAdminNewsPage();
	} else {
		$page = getCurrentNewsPage();
	}
	if(!empty($articles_per_page)) {
		$_zp_zenpage_total_pages = ceil(getTotalArticles() / $articles_per_page);
	}
	if($ignorepagination) {
		$offset = 0;
	} else {
		$offset = ($page - 1) * $articles_per_page;
	}
	// Prevent sql limit/offset error when saving plugin options and on the plugins page
	if(empty($articles_per_page)) {
		$limit = "";
	} else {
		$limit = " LIMIT ".$offset.",".$articles_per_page;
	}
	return $limit;
}

/**
 * Returns the articles count
 *
 */
function getTotalArticles() {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_current_category;
	if(ZP_COMBINEWS AND !isset($_GET['title']) AND !isset($_GET['category']) AND !isset($_GET['date']) AND OFFSET_PATH != 4) {
		return countCombiNews();
	} else {
		if(empty($_zp_current_category)) {
			if (isset($_GET['category'])) {
				$cat = sanitize($_GET['category']);
			} else {
				return countArticles();
			}
		} else {
			$cat = $_zp_current_category->getTitlelink();
		}
		return countArticles($cat);
	}
}

/**
 * Retrieves a list of all unique years & months
 * @param bool $yearsonly If set to true only the years' count is returned (Default false)
 * @return array
 */
function getAllArticleDates($yearsonly=false) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	$alldates = array();
	$cleandates = array();
	$sql = "SELECT date FROM ". prefix('news');
	if (!zp_loggedin(ZENPAGE_NEWS_RIGHTS)) { $sql .= " WHERE `show` = 1"; }
	$result = query_full_array($sql);
	foreach($result as $row){
		$alldates[] = $row['date'];
	}
	foreach ($alldates as $adate) {
		if (!empty($adate)) {
			if($yearsonly) {
				$cleandates[] = substr($adate, 0, 4);
			} else {
				$cleandates[] = substr($adate, 0, 7) . "-01";
			}
		}
	}
	$datecount = array_count_values($cleandates);
	krsort($datecount);
	return $datecount;
}


/**
 * Gets the current news page number
 *
 * @return int
 */
function getCurrentNewsPage() {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	if(isset($_GET['page'])) {
		$page = sanitize_numeric($_GET['page']);
	} else {
		$page = 1;
	}
	return $page;
}


/**
 * Get current news page for admin news pagination
 * Addition needed because $_GET['page'] conflict with zenphoto
 * could probably removed now...
 *
 * @return int
 */
function getCurrentAdminNewsPage() {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	if(isset($_GET['pagenr'])) {
		$page = sanitize_numeric($_GET['pagenr']);
	} else {
		$page = 1;
	}
	return $page;
}

/**
 * Gets news articles and images of a gallery to show them together on the news section
 *
 * NOTE: This function does not exclude articles that are password protected via a category
 *
 * @param int $articles_per_page The number of articles to get
 * @param string $mode 	"latestimages-thumbnail"
 *											"latestimages-thumbnail-customcrop"
 *											"latestimages-sizedimage"
 *											"latestalbums-thumbnail"
 *		 									"latestalbums-thumbnail-customcrop"
 *		 									"latestalbums-sizedimage"
 *		 									"latestimagesbyalbum-thumbnail"
 *		 									"latestimagesbyalbum-thumbnail-customcrop"
 *		 									"latestimagesbyalbum-sizedimage"
 *		 									"latestupdatedalbums-thumbnail" (for RSS and getLatestNews() used only)
 *		 									"latestupdatedalbums-thumbnail-customcrop" (for RSS and getLatestNews() used only)
 *		 									"latestupdatedalbums-sizedimage" (for RSS and getLatestNews() used only)
 *	NOTE: The "latestupdatedalbums" variants do NOT support pagination as required on the news loop!
 *
 * @param string $published "published" for published articles,
 * 													"unpublished" for un-published articles,
 * 													"all" for all articles
 * @param string $sortorder 	id, date or mtime, only for latestimages-... modes
 * @param bool $sticky set to true to place "sticky" articles at the front of the list.
 * @return array
 */
function getCombiNews($articles_per_page='', $mode='',$published=NULL,$sortorder='',$sticky=true) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_gallery, $_zp_flash_player;
	processExpired('news');
	if (is_null($published)) {
		if(zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
			$published = "all";
		} else {
			$published = "published";
		}
	}
	if(empty($mode)) {
		$mode = getOption("zenpage_combinews_mode");
	}
	if($published == "published") {
		$show = " WHERE `show` = 1 AND date <= '".date('Y-m-d H:i:s')."'";
		$imagesshow = " AND images.show = 1 ";
	} else {
		$show = "";
		$imagesshow = "";
	}
	$passwordcheck = "";
	if (zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
		$albumWhere = "";
		$passwordcheck = "";
	} else {
		$albumscheck = query_full_array("SELECT * FROM " . prefix('albums'). " ORDER BY title");
		foreach($albumscheck as $albumcheck) {
			if(!checkAlbumPassword($albumcheck['folder'])) {
				$albumpasswordcheck= " AND albums.id != ".$albumcheck['id'];
				$passwordcheck = $passwordcheck.$albumpasswordcheck;
			}
		}
		$albumWhere = "AND albums.show=1".$passwordcheck;
	}
	$limit = getLimitAndOffset($articles_per_page);
	if(empty($sortorder)) {
		$combinews_sortorder = getOption("zenpage_combinews_sortorder");
	} else {
		$combinews_sortorder = $sortorder;
	}
	$stickyorder = '';
	if($sticky) {
		$stickyorder = 'sticky DESC,';
	}
	$type3 = query("SET @type3:='0'");
	switch($mode) {
		case "latestimages-thumbnail":
		case "latestimages-thumbnail-customcrop":
		case "latestimages-sizedimage":
			$sortorder = "images.".$combinews_sortorder;
			$type1 = query("SET @type1:='news'");
			$type2 = query("SET @type2:='images'");
			switch($combinews_sortorder) {
				case 'id':
				case 'date':
					$imagequery = "(SELECT albums.folder, images.filename, images.date, @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
							WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					break;
				case 'mtime':
					$imagequery = "(SELECT albums.folder, images.filename, FROM_UNIXTIME(images.mtime), @type2, @type3 as sticky FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
							WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					break;
			}
			$result = query_full_array("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM ".prefix('news')." ".$show.")
																		UNION
																		".$imagequery."
																		ORDER BY $stickyorder date DESC $limit
																		");
			break;
		case "latestalbums-thumbnail":
		case "latestalbums-thumbnail-customcrop":
		case "latestalbums-sizedimage":
			$sortorder = $combinews_sortorder;
			$type1 = query("SET @type1:='news'");
			$type2 = query("SET @type2:='albums'");
			switch($combinews_sortorder) {
				case 'id':
				case 'date':
					$albumquery = "(SELECT albums.folder, albums.title, albums.date, @type2, @type3 as sticky FROM ".prefix('albums')." AS albums
							".$show.$albumWhere.")";
					break;
				case 'mtime':
					$albumquery = "(SELECT albums.folder, albums.title, FROM_UNIXTIME(albums.mtime), @type2, @type3 as sticky FROM ".prefix('albums')." AS albums
							".$show.$albumWhere.")";
					break;
			}
			$result = query_full_array("(SELECT title as albumname, titlelink, date, @type1 as type, sticky FROM ".prefix('news')." ".$show.")
																		UNION
																		".$albumquery."
																		ORDER BY $stickyorder date DESC $limit
																		");
			break;
		case "latestimagesbyalbum-thumbnail":
		case "latestimagesbyalbum-thumbnail-customcrop":
		case "latestimagesbyalbum-sizedimage":
			$type1 = query("SET @type1:='news'");
			$type2 = query("SET @type2:='albums'");
			if(empty($combinews_sortorder) || $combinews_sortorder != "date" || $combinews_sortorder != "mtime" ) {
				$combinews_sortorder = "date";
			}
			$combinews_sortorder = "date";
			$sortorder = "images.".$combinews_sortorder;
			switch(		$combinews_sortorder) {
				case "date":
					$imagequery = "(SELECT DISTINCT DATE_FORMAT(".$sortorder.",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.`date`,'%Y-%m-%d'), @type2 FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
														WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					break;
				case "mtime":
					$imagequery = "(SELECT DISTINCT FROM_UNIXTIME(".$sortorder.",'%Y-%m-%d'), albums.folder, DATE_FORMAT(images.`mtime`,'%Y-%m-%d'), @type2 FROM ".prefix('images')." AS images, ".prefix('albums')." AS albums
														WHERE albums.id = images.albumid ".$imagesshow.$albumWhere.")";
					break;
			}
			$result = query_full_array("(SELECT title as albumname, titlelink, date, @type1 as type FROM ".prefix('news')." ".$show.")
																		UNION
																		".$imagequery."
																		ORDER By date DESC $limit
																		");
			//echo "<pre>"; print_r($result); echo "</pre>";
			//$result = "";
			break;
		case "latestupdatedalbums-thumbnail":
		case "latestupdatedalbums-thumbnail-customcrop":
		case "latestupdatedalbums-sizedimage":
			$latest = getNewsArticles($articles_per_page,'',NULL,true);
			$counter = '';
			foreach($latest as $news) {
				$article = new ZenpageNews($news['titlelink']);
				if ($article->checkAccess($hint, $show)) {
					$counter++;
					$latestnews[$counter] = array(
						"albumname" => $article->getTitle(),
						"titlelink" => $article->getTitlelink(),
						"date" => $article->getDateTime(),
						"type" => "news",
					);
				}
			}
			$albums = getAlbumStatistic($articles_per_page, "latestupdated");
			$latestalbums = array();
			$counter = "";
			foreach($albums as $album) {
				$counter++;
				$tempalbum = new Album($_zp_gallery, $album['folder']);
				$tempalbumthumb = $tempalbum->getAlbumThumbImage();
				$timestamp = $tempalbum->get('mtime');
				if($timestamp == 0) {
					$albumdate = $tempalbum->getDateTime();
				} else {
					$albumdate = strftime('%Y-%m-%d %H:%M:%S',$timestamp);
				}
				$latestalbums[$counter] = array(
					"albumname" => $tempalbum->getFolder(),
					"titlelink" => $tempalbum->getTitle(),
					"date" => $albumdate,
					"type" => 'albums',
				);
			}
			//$latestalbums = array_merge($latestalbums, $item);
			$latest = array_merge($latestnews, $latestalbums);
			$result = sortMultiArray($latest,"date",true);
			if(count($result) > $articles_per_page) {
				$result = array_slice($result,0,10);
			}
			break;
	}
	//$result = "";
	return $result;
}


/**
 * CombiNews Feature: Counts all news articles and all images
 *
 * @return int
 */
function countCombiNews($published=NULL) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_gallery;
	$countGalleryitems = 0;
	$countArticles = 0;
	if(ZP_COMBINEWS) {
		$countArticles = countArticles();
		if(is_null($published)) {
			if(zp_loggedin(ZENPAGE_NEWS_RIGHTS)) {
				$published = FALSE;
			} else {
				$published = TRUE;
			}
		}
		$mode = getOption("zenpage_combinews_mode");
		if(is_object($_zp_gallery)) { // workaround if called on the admin pages....
			switch($mode) {
				case "latestimages-sizedimage":
				case "latestimages-thumbnail":
				case "latestimages-thumbnail-customcrop":
					$countGalleryitems = $_zp_gallery->getNumImages($published);
					break;
				case "latestalbums-sizedimage":
				case "latestalbums-thumbnail":
				case "latestalbums-thumbnail-customcrop":
					$countGalleryitems = $_zp_gallery->getNumAlbums(true,$published);
					break;
				case "latestimagesbyalbum-thumbnail":
				case "latestimagesbyalbum-thumbnail-customcrop":
				case "latestimagesbyalbum-sizedimage":
					($published) ? $show = "WHERE `show`= 1" : $show = "";
					$result = query("SELECT COUNT(DISTINCT Date(date),albumid) FROM " . prefix('images'). " ".$show);
					$countGalleryitems = db_result($result, 0);
					break;
			}
		} else {
			$countGalleryitems = 0;
		}
		$totalcount = $countArticles+$countGalleryitems;
		return $totalcount;
	}
}

/************************************/
/* general news category functions  */
/************************************/

/**
 * Gets the category link of a category
 *
 * @param string $catname the title of the category
 * @return string
 */
function getCategoryLink($catname) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	foreach(getAllCategories() as $cat) {
		if($cat['titlelink'] == $catname) {
			return $cat['title'];
		}
	}
}


/**
 * Gets a category titlelink by id
 *
 * @param int $id id of the category
 * @return array
 */
function getCategory($id) {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	foreach(getAllCategories() as $cat) {
		if($cat['id'] == $id) {
			return $cat;
		}
	}
	return '';
}


/**
 * Gets all categories
 *
 * @return array
 */
function getAllCategories() {
	deprecated_function_notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage_all_categories;
	if(is_null($_zp_zenpage_all_categories) OR isset($_GET['delete']) OR isset($_GET['update']) OR isset($_GET['save'])) {
		$_zp_zenpage_all_categories = query_full_array("SELECT * FROM ".prefix('news_categories')." ORDER by sort_order", false, 'title');
	}
	return $_zp_zenpage_all_categories;
}


/**
 * Checks if the album is password protected
 * @param object $album
 */
function isProtectedAlbum($album=NULL) {
	deprecated_function_notify(gettext('Use the album class method <code>isProtected()</code> instead.'));
	global $_zp_current_album;
	if (is_null($album)) $album = $_zp_current_album;
	return $album->isProtected();
}


?>