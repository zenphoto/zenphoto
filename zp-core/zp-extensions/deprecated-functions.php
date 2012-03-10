<?php
/**
 * These functions have been removed from mainstream Zenphoto as they have been
 * supplanted.
 * They are not maintained and they are not guaranteed to function correctly with the
 * current version of Zenphoto.
 *
 * The default settings cause an <code>E_USER_NOTICE</code> error to be generated when the function is used.
 * The text of the error message will tell you how to replace calls on the deprecated function. The error
 * message can be disabled to allow your scripts to continue to run. Visit the <i>deprecated-functions</i>
 * plugin options. Find the function and uncheck the box by the function.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_description = gettext("Provides deprecated Zenphoto functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> requried for the Zenphoto distributed functions.");
$option_interface = 'deprecated_functions';
$plugin_is_filter = 9|CLASS_PLUGIN;

zp_register_filter('admin_utilities_buttons', 'deprecated_functions::button');

class deprecated_functions {

	var $internalFunctions = array (
																	'getSearchURL',
																	'printPasswordForm'
																	);
	var $listed_functions = array();

	function deprecated_functions() {
		global $_internalFunctions;
		$deprecated = file_get_contents(__FILE__);
		$i = strpos($deprecated, '//'.' IMPORTANT:: place all deprecated functions below this line!!!');
		$deprecated = substr($deprecated, $i);
		preg_match_all('/function\040+(.*)\040?\(.*\)\040?\{/',$deprecated,$functions);
		$this->listed_functions = array_merge($functions[1],$this->internalFunctions);
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

	/*
	 * used to provided deprecated function notification.
	 */
	static function notify($use) {
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
			trigger_error(sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated'),$fcn,$script,$line).$use.'<br />'.sprintf(gettext('You can disable this error message by going to the <em>deprecated-functions</em> plugin options and un-checking <strong>%s</strong> in the list of functions.'.'<br />'),$fcn), E_USER_NOTICE);
			error_reporting($old_reporting);
		}
	}

	static function button($buttons) {
		$buttons[] = array(
												'category'=>gettext('development'),
												'enable'=>true,
												'button_text'=>gettext('Check deprecated use'),
												'formname'=>'deprecated_functions.php',
												'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/deprecated_functions/check_for_deprecated.php',
												'icon'=>'images/magnify.png',
												'title'=>gettext("Searches PHP scripts for use of deprecated functions."),
												'alt'=>gettext('Check for update'),
												'hidden'=>'',
												'rights'=> ADMIN_RIGHTS
		);
		return $buttons;
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
	deprecated_function::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 * Enter description here ...
 * @param $object
 */
function printImageRating($object=NULL) {
	deprecated_function::notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object)) $object = $_zp_current_image;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printAlbumRating($object=NULL) {
	deprecated_function::notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object)) $object = $_zp_current_album;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printImageEXIFData() {
	deprecated_function::notify(gettext('Use printImageMetadata().'));
	if (isImageVideo()) {
	} else {
		printImageMetadata();
	}
}

/**
 * @deprecated
 */
function printCustomSizedImageMaxHeight($maxheight) {
	deprecated_function::notify(gettext('Use printCustomSizedImageMaxSpace().'));
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
	deprecated_function::notify(gettext('Use getCommentDateTime().'));
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
	deprecated_function::notify(gettext('Use getCommentDateTime().'));
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * @deprecated
 */
function hitcounter($option='image', $viewonly=false, $id=NULL) {
	deprecated_function::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 */
function my_truncate_string($string, $length) {
	deprecated_function::notify(gettext('Use truncate_string().'));
	return truncate_string($string, $length);
}

/**
 * @deprecated
 */
function getImageEXIFData() {
	deprecated_function::notify(gettext('Use getImageMetaData().'));
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getMetaData();
}

/**
 * @deprecated
 */
function getAlbumPlace() {
	deprecated_function::notify(gettext('Use getAlbumLocation().'));
	global $_zp_current_album;
	if (is_object($_zp_current_album)) {
		return $_zp_current_album->getLocation();
	} else {
		return false;
	}
}

/**
 * @deprecated
 */
function printAlbumPlace() {
	deprecated_function::notify(gettext('Use printAlbumLocation().'));
	printField('album', 'location');
}

/**
 * @deprecated
 */
function printEditable($context, $field, $editable = NULL, $editclass = 'unspecified', $messageIfEmpty = true, $convertBR = false, $override = false, $label='') {
	deprecated_function::notify(gettext('Use printField().'));
	printField($context,$field,$convertBR,$override,$label);
}

/***************************
 * ZENPAGE PLUGIN FUNCTIONS
 ***************************/

/**
 * @deprecated
 */
function zenpageHitcounter($option='pages', $viewonly=false, $id=NULL) {
	deprecated_function::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 */
function rewrite_path_zenpage($rewrite='',$plain='') {
	deprecated_function::notify(gettext('Use rewrite_path().'));
	return rewrite_path($rewrite, $plain);
}

/**
 * @deprecated
 */
function getNewsImageTags() {
	deprecated_function::notify(gettext('Use object->getTags() method.'));
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
	deprecated_function::notify(gettext('Use printTags().'));
	printTags($option, $preText, $class, $separator);
}

/**
 * @deprecated
 */
function getNumSubalbums() {
	deprecated_function::notify(gettext('Use getNumAlbums().'));
	return getNumAlbums();
}

/**
 * @deprecated
 */
function getAllSubalbums($param=NULL) {
	deprecated_function::notify(gettext('Use getAllAlbums().'));
	return getAllAlbums($param);
}

/**
 * @deprecated
 */
function addPluginScript($script) {
	deprecated_function::notify(gettext('Register a "theme_head" filter.'));
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
	deprecated_function::notify(gettext('Use zp_apply_filter("theme_head").'));
	zp_apply_filter('theme_head');
}

/**
 * @deprecated
 */
function normalizeColumns($albumColumns=NULL, $imageColumns=NULL) {
	deprecated_function::notify(gettext('Use instead the theme options for images and albums per row.'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use printZenpageItemsBreadcrumb().'));
	printZenpageItemsBreadcrumb($before, $after);
}

/**
 * @deprecated
 */
function isMyAlbum($albumname, $action) {
	deprecated_function::notify(gettext('Use instead the Album class method isMyItem().'), E_USER_NOTICE);
	$album = new Album(NULL, $albumname);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getSubCategories().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage news class method inProtectedCategory().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method isProtected().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getParents().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getTitle().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getID().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getParentID().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method getSortOrder().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage page class method getParents().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage page class method isProtected().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method isMyItem().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage news class method isMyItem().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage news class method checkNewsAccess().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getTitlelink().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getID().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getParentID().'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inNewsCategory($catlink).'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->isSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Functionality is now included in getNewsContent(), printNewsContent() and getContentShorten() to properly cover custom shortening via TinyMCE <pagebreak>.'), E_USER_NOTICE);
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
	deprecated_function::notify(gettext('Use getContentShorten() instead. Note the read more url must be passed directly.'), E_USER_NOTICE);
	return getContentShorten($articlecontent,$shorten,$shortenindicator,'');
}

/**
 * @deprecated
 */
function checkForPassword($hint, $show) {
	deprecated_function::notify(gettext('There is no need for this function as password handling is done by the core.'), E_USER_NOTICE);
	return false;
}

/**
 * @deprecated
 */
function printAlbumMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $firstPageImages=NULL, $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL){
	deprecated_function::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function printImageMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL) {
	deprecated_function::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function setupAllowedMaps($defaultmap, $allowedmaps) {
	deprecated_function::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
}

/**
 * @deprecated
 */
function printPreloadScript() {
	deprecated_function::notify(gettext('printPreloadScript is deprecated. It is a helper for a specific theme and should be placed within that theme\'s "functions.php" script.'));
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

/********************************************
 * former zenpage-functions.php functions
 ********************** *********************/

/**
 * Un-publishes pages/news whose expiration date has been reached
 *
 * @deprecated
 */
function processExpired($table) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	Zenpage::processExpired($table);
}

/**
 * Gets the parent items recursivly to the item whose parentid is passed
 * @param string $mode "pages" or "categories"
 * @param int $parentid The parentid of the page to get the parents of
 * @param bool $initparents If the
 * @return array
 * @deprecated
 */
function getParentItems($mode='pages',&$parentid,$initparents=true) {
	deprecated_function::notify(gettext('Use the method from either the ZenpagePage or the ZenpageCategory class instead.'));
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

/**
 * Gets the titlelink and sort order for all pages or published ones.
 *
 * NOTE: Since this function only returns titlelinks for use with the object model it does not exclude pages that are password protected
 *
 * @param bool $published TRUE for published or FALSE for all pages including un-published
 * @return array
 * @deprecated
 */
function getPages($published=NULL) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getPages($published);
}

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
 * @deprecated
 */
function getArticles($articles_per_page='', $category='', $published=NULL,$ignorepagination=false,$sortorder="date", $sortdirection="desc",$sticky=true) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage, $_zp_current_category;
	if (!empty($category)) {
		$catobj = new ZenpageCategory($category);
		return $catobj->getArticles($articles_per_page, $category, $published,$ignorepagination,$sortorder, $sortdirection,$sticky);
	} elseif(in_context(ZP_ZENPAGE_NEWS_CATEGORY)) {
		return $_zp_current_category->getArticles($articles_per_page, $category, $published,$ignorepagination,$sortorder, $sortdirection,$sticky);
	} else {
		return $_zp_zenpage->getArticles($articles_per_page, $category, $published,$ignorepagination,$sortorder, $sortdirection,$sticky);
	}
}


/**
 * Counts news articles, either all or by category or archive date, published or un-published
 *
 * @param string $category The categorylink of the category to count
 * @param string $published "published" for an published articles,
 * 													"unpublished" for an unpublised articles,
 * 													"all" for all articles
 * @return array
 * @deprecated
 */
function countArticles($category='', $published='published',$count_subcat_articles=true) {
	deprecated_function::notify(gettext('Count the articles instead.'));
	global $_zp_post_date;
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
		$count = db_count('news',$show.$datesearch);
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
 * Returns the articles count
 *
 * @deprecated
 */
function getTotalArticles() {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getTotalArticles();
}

/**
 * Retrieves a list of all unique years & months
 * @param bool $yearsonly If set to true only the years' count is returned (Default false)
 * @return array
 * @deprecated
 */
function getAllArticleDates($yearsonly=false) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllArticleDates($yearsonly);
}


/**
 * Gets the current news page number
 *
 * @return int
 * @deprecated
 */
function getCurrentNewsPage() {
	deprecated_function::notify(gettext('Use the global $_zp_page instead.'));
	global $_zp_page;
	return $_zp_page;
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
 * @deprecated
 */
function getCombiNews($articles_per_page='', $mode='',$published=NULL,$sortorder='',$sticky=true) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCombiNews($articles_per_page, $mode,$published,$sortorder,$sticky);
}


/**
 * CombiNews Feature: Counts all news articles and all images
 *
 * @return int
 * @deprecated
 */
function countCombiNews($published=NULL) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->countCombiNews($published);
}

/************************************/
/* general news category functions  */
/************************************/

/**
 * Gets the category link of a category
 *
 * @param string $catname the title of the category
 * @return string
 * @deprecated
 */
function getCategoryLink($catname) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategoryLink($catname);
}


/**
 * Gets a category titlelink by id
 *
 * @param int $id id of the category
 * @return array
 * @deprecated
 */
function getCategory($id) {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategory($id);
}


/**
 * Gets all categories
 *
 * @return array
 * @deprecated
 */
function getAllCategories() {
	deprecated_function::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllCategories();
}


/**
 * Checks if the album is password protected
 * @param object $album
 * @deprecated
 */
function isProtectedAlbum($album=NULL) {
	deprecated_function::notify(gettext('Use the album class method <code>isProtected()</code> instead.'));
	global $_zp_current_album;
	if (is_null($album)) $album = $_zp_current_album;
	return $album->isProtected();
}


/**
 * Returns the RSS link for use in the HTML HEAD
 *
 * @param string $option type of RSS: "Gallery" feed for the whole gallery
 * 																		"Album" for only the album it is called from
 * 																		"Collection" for the album it is called from and all of its subalbums
 * 																		 "Comments" for all comments
 * 																		"Comments-image" for comments of only the image it is called from
 * 																		"Comments-album" for comments of only the album it is called from
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language. Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 *
 *
 * @return string
 * @deprecated
 */
function getRSSHeaderLink($option, $linktext='', $lang='') {
	deprecated_function::notify(gettext('Use the template function <code>getRSSLink()</code> instead. NOTE: While this function gets a full html link <code>getRSSLink()</code> just returns the URL.'));
	global $_zp_current_album;
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL.'://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	if(empty($lang)) {
		$lang = getOption("locale");
	}
	switch($option) {
		case "Gallery":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss&amp;lang=".$lang."\" />\n";
			}
		case "Album":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss&amp;albumtitle=".urlencode(getAlbumTitle())."&amp;albumname=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" />\n";
			}
		case "Collection":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss&amp;albumtitle=".urlencode(getAlbumTitle())."&amp;folder=".urlencode($_zp_current_album->getFolder())."&amp;lang=".$lang."\" />\n";
			}
		case "Comments":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=comments&amp;lang=".$lang."\" />\n";
			}
		case "Comments-image":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=comments&amp;id=".getImageID()."&amp;title=".urlencode(getImageTitle())."&amp;type=image&amp;lang=".$lang."\" />\n";
			}
		case "Comments-album":
			if (getOption('RSS_comments')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=comments&amp;id=".getAlbumID()."&amp;title=".urlencode(getAlbumTitle())."&amp;type=album&amp;lang=".$lang."\" />\n";
			}
		case "AlbumsRSS":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode($linktext)."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=comments&amp;lang=".$lang."&amp;albumsmode\" />\n";
			}

	}
}

/**
 * Returns the RSS link for use in the HTML HEAD
 *
 * @param string $option type of RSS: "News" feed for all news articles
 * 																		"Category" for only the news articles of a specific category
 * 																		"NewsWithImages" for all news articles and latest images
 * @param string $categorylink The specific category you want a RSS feed from (only 'Category' mode)
 * @param string $linktext title of the link
 * @param string $lang optional to display a feed link for a specific language (currently works for latest images only). Enter the locale like "de_DE" (the locale must be installed on your Zenphoto to work of course). If empty the locale set in the admin option or the language selector (getOption('locale') is used.
 *
 * @return string
 * @deprecated
 */
function getZenpageRSSHeaderLink($option='', $categorylink='', $linktext='', $lang='') {
	deprecated_function::notify(gettext('Use the template function <code>getZenpageRSSLink()</code> instead. NOTE: While this function gets a full html link  <code>getZenpageRSSLink()</code> just returns the URL.'));
	global $_zp_current_category;
	$host = html_encode($_SERVER["HTTP_HOST"]);
	$protocol = SERVER_PROTOCOL.'://';
	if ($protocol == 'https_admin') {
		$protocol = 'https://';
	}
	if(empty($lang)) {
		$lang = getOption("locale");
	}
	if($option == 'Category') {
		if(!is_null($categorylink)) {
			$categorylink = '&amp;category='.html_encode($categorylink);
		} elseif(empty($categorylink) AND !is_null($_zp_current_category)) {
			$categorylink = '&amp;category='.$_zp_current_category->getTitlelink();
		} else {
			$categorylink = '';
		}
	}
	switch($option) {
		case "News":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode(strip_tags($linktext))."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=news&amp;lang=".$lang."\" />\n";
			}
		case "Category":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode(strip_tags($linktext))."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=news&amp;lang=".$lang.$categorylink."\" />\n";
			}
		case "NewsWithImages":
			if (getOption('RSS_articles')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"".html_encode(strip_tags($linktext))."\" href=\"".$protocol.$host.WEBPATH."/index.php?rss=news&amp;withimages&amp;lang=".$lang."\" />\n";
			}
	}
}
/**
 * @deprecated
 */
function generateCaptcha(&$img) {
	deprecated_function::notify(gettext('Use $_zp_captcha->getCaptcha(). Note that you will require updating your code to the new function.'));
	return $img = NULL;
}

?>