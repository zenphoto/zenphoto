<?php
/**
 * These functions have been removed from mainstream Zenphoto as they have been
 * supplanted.
 *
 * They are not maintained and they are not guarentted to function correctly with the
 * current version of Zenphoto.
 *
 * @package plugins
 */
$plugin_description = gettext("Deprecated Zenphoto functions. These functions have been removed from mainstream Zenphoto as they have been supplanted. They are not maintained and they are not guaranteed to function correctly with the current version of Zenphoto.  You should update your theme if you get warnings. This plugin is not required for any theme coded for the current version of Zenphoto.");
$plugin_URL = "http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---deprecated-functions.php.html";
$option_interface = 'deprecated_functions';
$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_version = '1.4.0';

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

function printImageRating($object=NULL) {
	deprecated_function_notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object)) $object = $_zp_current_image;
	printRating(3, $object);
}

function printAlbumRating($object=NULL) {
	deprecated_function_notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object)) $object = $_zp_current_album;
	printRating(3, $object);
}

function printImageEXIFData() {
	deprecated_function_notify(gettext('Use printImageMetadata().'));
	if (isImageVideo()) {
	} else {
		printImageMetadata();
	}
}

function printCustomSizedImageMaxHeight($maxheight) {
	deprecated_function_notify(gettext('Use printCustomSizedImageMaxSpace().'));
	if (getFullWidth() === getFullHeight() OR getDefaultHeight() > $maxheight) {
		printCustomSizedImage(getImageTitle(), null, null, $maxheight, null, null, null, null, null, null);
	} else {
		printDefaultSizedImage(getImageTitle());
	}
}

function getCommentDate($format = NULL) {
	deprecated_function_notify(gettext('Use getCommentDateTime().'));
	if (is_null($format)) {
		$format = getOption('date_format');
		$time_tags = array('%H', '%I', '%R', '%T', '%r');
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

function getCommentTime($format = '%I:%M %p') {
	deprecated_function_notify(gettext('Use getCommentDateTime().'));
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

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

function my_truncate_string($string, $length) {
	deprecated_function_notify(gettext('Use truncate_string().'));
	if (strlen($string) > $length) {
		$short = substr($string, 0, $length);
		return $short. '...';
	} else {
		return $string;
	}
}

function getImageEXIFData() {
	deprecated_function_notify(gettext('Use getImageMetaData().'));
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getMetaData();
}

function getAlbumPlace() {
	deprecated_function_notify(gettext('Use getAlbumLocation().'));
	global $_zp_current_album;
	if (is_object($_zp_current_album)) 	return $_zp_current_album->getLocation();
}

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

function rewrite_path_zenpage($rewrite='',$plain='') {
	deprecated_function_notify(gettext('Use rewrite_path().'));
	if (getOption('mod_rewrite')) {
		return $rewrite;
	} else {
		return $plain;
	}
}

function getNewsImageTags() {
	deprecated_function_notify(gettext('Use object->getTags() method.'));
	global $_zp_current_zenpage_news;
	if(is_GalleryNewsType() && is_object($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getTags();
	} else {
		return false;
	}
}

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

function getNumSubalbums() {
	deprecated_function_notify(gettext('Use getNumAlbums().'));
	return getNumAlbums();
}

function getAllSubalbums($param=NULL) {
	deprecated_function_notify(gettext('Use getAllAlbums().'));
	return getAllAlbums($param);
}

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

function zenJavascript() {
	deprecated_function_notify(gettext('Use zp_apply_filter("theme_head").'));
	zp_apply_filter('theme_head');
}

function normalizeColumns($albumColumns=NULL, $imageColumns=NULL) {
	deprecated_function_notify(gettext('Use instead the theme options for images and albums per row.'), E_USER_NOTICE);
	global $_firstPageImages;
	setOption('albums_per_row',$albumColumns);
	setOption('images_per_row',$imageColumns);
	setThemeColumns();
	return $_firstPageImages;
}

function printParentPagesBreadcrumb($before='', $after='') {
	deprecated_function_notify(gettext('Use printZenpageItemsBreadcrumb().'));
	printZenpageItemsBreadcrumb($before, $after);
}

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
 */
function isProtectedPage($pageobj=NULL) {
	deprecated_function_notify(gettext('Use instead the Zenpage page class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj)) $pageobj = $_zp_current_zenpage_page;
	return $pageobj->checkforGuest() != 'zp_unprotected';
}

/**
 * Checks if user is author of page
 * @param object $pageobj
 * @param bit $action
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
 */
function getNewsContentShorten($articlecontent,$shorten,$shortenindicator='',$readmore='') {
	deprecated_function_notify(gettext('Use getContentShorten() instead. Note the read more url must be passed directly.'), E_USER_NOTICE);
	return getContentShorten($articlecontent,$shorten,$shortenindicator,'');
}

function checkForPassword($hint, $show) {
	deprecated_function_notify(gettext('There is no need for this function as password handling is done by the core.'), E_USER_NOTICE);
	return false;
}

function printAlbumMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $firstPageImages=NULL, $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL){
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

function printImageMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL) {
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

function setupAllowedMaps($defaultmap, $allowedmaps) {
	deprecated_function_notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
}

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

?>