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
 */
$plugin_description = gettext("Provides deprecated Zenphoto functions.");
$plugin_notice = gettext("This plugin is <strong>NOT</strong> required for the Zenphoto distributed functions.");
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
			trigger_error(sprintf(gettext('%1$s (called from %2$s line %3$s) is deprecated'),$fcn,$script,$line).$use.'<br />'.sprintf(gettext('You can disable this error message by going to the <em>deprecated-functions</em> plugin options and un-checking <strong>%s</strong> in the list of functions.'.'<br />'),$fcn), E_USER_WARNING);
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
 */
function getZenpageHitcounter($mode="",$obj=NULL) {
	self::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 */
function printImageRating($object=NULL) {
	self::notify(gettext('Use printRating().'));
	global $_zp_current_image;
	if (is_null($object)) $object = $_zp_current_image;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printAlbumRating($object=NULL) {
	self::notify(gettext('Use printRating().'));
	global $_zp_current_album;
	if (is_null($object)) $object = $_zp_current_album;
	printRating(3, $object);
}

/**
 * @deprecated
 */
function printImageEXIFData() {
	self::notify(gettext('Use printImageMetadata().'));
	if (isImageVideo()) {
	} else {
		printImageMetadata();
	}
}

/**
 * @deprecated
 */
function printCustomSizedImageMaxHeight($maxheight) {
	self::notify(gettext('Use printCustomSizedImageMaxSpace().'));
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
	self::notify(gettext('Use getCommentDateTime().'));
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
	self::notify(gettext('Use getCommentDateTime().'));
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * @deprecated
 */
function hitcounter($option='image', $viewonly=false, $id=NULL) {
	self::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 */
function my_truncate_string($string, $length) {
	self::notify(gettext('Use truncate_string().'));
	return truncate_string($string, $length);
}

/**
 * @deprecated
 */
function getImageEXIFData() {
	self::notify(gettext('Use getImageMetaData().'));
	global $_zp_current_image;
	if (is_null($_zp_current_image)) return false;
	return $_zp_current_image->getMetaData();
}

/**
 * @deprecated
 */
function getAlbumPlace() {
	self::notify(gettext('Use getAlbumLocation().'));
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
	self::notify(gettext('Use printAlbumLocation().'));
	printField('album', 'location');
}

/**
 * @deprecated
 */
function printEditable($context, $field, $editable = NULL, $editclass = 'unspecified', $messageIfEmpty = true, $convertBR = false, $override = false, $label='') {
	self::notify(gettext('Use printField().'));
	printField($context,$field,$convertBR,$override,$label);
}

/***************************
 * ZENPAGE PLUGIN FUNCTIONS
 ***************************/

/**
 * @deprecated
 */
function zenpageHitcounter($option='pages', $viewonly=false, $id=NULL) {
	self::notify(gettext('Use getHitcounter().'));
	return getHitcounter();
}

/**
 * @deprecated
 */
function rewrite_path_zenpage($rewrite='',$plain='') {
	self::notify(gettext('Use rewrite_path().'));
	return rewrite_path($rewrite, $plain);
}

/**
 * @deprecated
 */
function getNewsImageTags() {
	self::notify(gettext('Use object->getTags() method.'));
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
	self::notify(gettext('Use printTags().'));
	printTags($option, $preText, $class, $separator);
}

/**
 * @deprecated
 */
function getNumSubalbums() {
	self::notify(gettext('Use getNumAlbums().'));
	return getNumAlbums();
}

/**
 * @deprecated
 */
function getAllSubalbums($param=NULL) {
	self::notify(gettext('Use getAllAlbums().'));
	return getAllAlbums($param);
}

/**
 * @deprecated
 */
function addPluginScript($script) {
	self::notify(gettext('Register a "theme_head" filter.'));
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
	self::notify(gettext('Use zp_apply_filter("theme_head").'));
	zp_apply_filter('theme_head');
}

/**
 * @deprecated
 */
function normalizeColumns($albumColumns=NULL, $imageColumns=NULL) {
	self::notify(gettext('Use instead the theme options for images and albums per row.'), E_USER_NOTICE);
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
	self::notify(gettext('Use printZenpageItemsBreadcrumb().'));
	printZenpageItemsBreadcrumb($before, $after);
}

/**
 * @deprecated
 */
function isMyAlbum($albumname, $action) {
	self::notify(gettext('Use instead the Album class method isMyItem().'), E_USER_NOTICE);
	$album = new Album(NULL, $albumname);
	return $album->isMyItem($action);
}

/**
 * @deprecated
 */
function getSubCategories($catlink) {
	self::notify(gettext('Use instead the Zenpage category class method getSubCategories().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSubCategories();
}

/**
 * @deprecated
 */
function inProtectedNewsCategory($articleobj=NULL,$checkProtection=true) {
	self::notify(gettext('Use instead the Zenpage news class method inProtectedCategory().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	if(empty($articleobj) && !is_null($_zp_current_zenpage_news) && get_class($_zp_current_zenpage_news) == 'zenpagenews') {
		$articleobj = $_zp_current_zenpage_news;
	}
	if (!is_object($articleobj)) return false;
	return $articleobj->inProtectedCategory();
	$categories = $articleobj->getCategories();
}

/**
 * @deprecated
 */
function isProtectedNewsCategory($catlink='') {
	self::notify(gettext('Use instead the Zenpage category class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_category;
	if(empty($catlink) && !is_null($_zp_current_category)) {
		$cat = $_zp_current_category;
	} else {
		$cat = new ZenpageCategory($catlink);
	}
	return $cat->isProtected();
}

/**
 * @deprecated
 */
function getParentNewsCategories($parentid,$initparents=true) {
	self::notify(gettext('Use instead the Zenpage category class method getParents().'), E_USER_NOTICE);
	return getParentItems('categories',$parentid,$initparents);
}

/**
 * @deprecated
 */
function getCategoryTitle($catlink) {
	self::notify(gettext('Use instead the Zenpage category class method getTitle().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getTitle();
}

/**
 * @deprecated
 */
function getCategoryID($catlink) {
	self::notify(gettext('Use instead the Zenpage category class method getID().'), E_USER_NOTICE);
	$catlink = sanitize($catlink);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getID();
}

/**
 * @deprecated
 */
function getCategoryParentID($catlink) {
	self::notify(gettext('Use instead the Zenpage category class method getParentID().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getParentID();
}

/**
 * @deprecated
 */
function getCategorySortOrder($catlink) {
	self::notify(gettext('Use instead the Zenpage category class method getSortOrder().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->getSortOrder();
}

/**
 * @deprecated
 */
function getParentPages(&$parentid,$initparents=true) {
	self::notify(gettext('Use instead the Zenpage page class method getParents().'), E_USER_NOTICE);
	return getParentItems('pages',$parentid,$initparents);
}

/**
 * @deprecated
 */
function isProtectedPage($pageobj=NULL) {
	self::notify(gettext('Use instead the Zenpage page class method isProtected().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj)) $pageobj = $_zp_current_zenpage_page;
	return $pageobj->checkforGuest() != 'zp_public_access';
}

/**
 * @deprecated
 */
function isMyPage($pageobj=NULL, $action) {
	self::notify(gettext('Use instead the Zenpage category class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_page;
	if (is_null($pageobj)) $pageobj = $_zp_current_zenpage_page;
	return $pageobj->isMyItem($action);
}

/**
 * @deprecated
 */
function checkPagePassword($pageobj, &$hint, &$show) {
	self::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	return $pageobj->checkforGuest();
}

//	News category password functions

/**
 * @deprecated
 */
function isMyNews($newsobj, $action) {
	self::notify(gettext('Use instead the Zenpage news class method isMyItem().'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->isMyItem();
}

/**
 * @deprecated
 */
function checkNewsAccess($newsobj, &$hint, &$show) {
	self::notify(gettext('Use instead the Zenpage news class method checkNewsAccess().'), E_USER_NOTICE);
	return $newsobj->checkNewsAccess($hint, $show);
}

/**
 * @deprecated
 */
function checkNewsCategoryPassword($catlink, $hint, $show) {
	self::notify(gettext('Use instead the Zenpage category class method checkforGuest().'), E_USER_NOTICE);
	$catobj = new ZenpageCategory($catlink);
	return $catobj->checkforGuest();
}

/**
 * @deprecated
 */
function getCurrentNewsCategory() {
	self::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getTitlelink().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getTitlelink();
}

/**
 * @deprecated
 */
function getCurrentNewsCategoryID() {
	self::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getID();
}

/**
 * @deprecated
 */
function getCurrentNewsCategoryParentID() {
	self::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->getParentID().'), E_USER_NOTICE);
	global $_zp_current_category;
	return $_zp_current_category->getParentID();
}

/**
 * @deprecated
 */
function inNewsCategory($catlink) {
	self::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inNewsCategory($catlink).'), E_USER_NOTICE);
	global $_zp_current_zenpage_news;
	return $_zp_current_zenpage_news->inNewsCategory($catlink);
}

/**
 * @deprecated
 */
function inSubNewsCategoryOf($catlink) {
	self::notify(gettext('Use instead the Zenpage news global object variable: $_zp_current_zenpage_news->inSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
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
	self::notify(gettext('Use instead the Zenpage category global object variable: $_zp_current_category->isSubNewsCategoryOf($catlink).'), E_USER_NOTICE);
	return $_zp_current_category->isSubNewsCategoryOf($catlink);
}

/**
* @deprecated
 */
function printNewsReadMoreLink($readmore='') {
	self::notify(gettext('Functionality is now included in getNewsContent(), printNewsContent() and getContentShorten() to properly cover custom shortening via TinyMCE <pagebreak>.'), E_USER_NOTICE);
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
 * @deprecated
 */
function getNewsContentShorten($articlecontent,$shorten,$shortenindicator='',$readmore='') {
	self::notify(gettext('Use getContentShorten() instead. Note the read more url must be passed directly.'), E_USER_NOTICE);
	return getContentShorten($articlecontent,$shorten,$shortenindicator,'');
}

/**
 * @deprecated
 */
function checkForPassword($hint, $show) {
	self::notify(gettext('There is no need for this function as password handling is done by the core.'), E_USER_NOTICE);
	return false;
}

/**
 * @deprecated
 */
function printAlbumMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $firstPageImages=NULL, $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL){
	self::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function printImageMap($zoomlevel=NULL, $defaultmaptype=NULL, $width=NULL, $height=NULL, $text=NULL, $toggle=true, $id='googlemap', $mapselections=NULL, $addwiki=NULL, $background=NULL, $mapcontrol=NULL, $maptypecontrol=NULL, $customJS=NULL) {
	self::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
	if (function_exists('printGoogleMap')) printGoogleMap($text, $id, $toggle, NULL, NULL);
}

/**
 * @deprecated
 */
function setupAllowedMaps($defaultmap, $allowedmaps) {
	self::notify(gettext('The google-maps plugin is deprecated. Convert to GoogleMap.'));
}

/**
 * @deprecated
 */
function printPreloadScript() {
	self::notify(gettext('printPreloadScript is deprecated. It is a helper for a specific theme and should be placed within that theme\'s "functions.php" script.'));
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
 */
function processExpired($table) {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	Zenpage::processExpired($table);
}

/**
 * @deprecated
 */
function getParentItems($mode='pages',&$parentid,$initparents=true) {
	self::notify(gettext('Use the method from either the ZenpagePage or the ZenpageCategory class instead.'));
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
 * @deprecated
 */
function getPages($published=NULL) {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getPages($published);
}

/**
 * @deprecated
 */
function getArticles($articles_per_page='', $category='', $published=NULL,$ignorepagination=false,$sortorder="date", $sortdirection="desc",$sticky=true) {
	self::notify(gettext('Use the Zenpage class method instead.'));
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
 * @deprecated
 */
function countArticles($category='', $published='published',$count_subcat_articles=true) {
	self::notify(gettext('Count the articles instead.'));
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
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getTotalArticles();
}

/**
 * @deprecated
 */
function getAllArticleDates($yearsonly=false) {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllArticleDates($yearsonly);
}


/**
* @deprecated
 */
function getCurrentNewsPage() {
	self::notify(gettext('Use the global $_zp_page instead.'));
	global $_zp_page;
	return $_zp_page;
}

/**
 * @deprecated
 */
function getCombiNews($articles_per_page='', $mode='',$published=NULL,$sortorder='',$sticky=true) {
	self::notify(gettext('Use the Zenpage class method instead.'));
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
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->countCombiNews($published);
}

/**
 * @deprecated
 */
function getCategoryLink($catname) {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategoryLink($catname);
}


/**
 * @deprecated
 */
function getCategory($id) {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getCategory($id);
}


/**
 * @deprecated
 */
function getAllCategories() {
	self::notify(gettext('Use the Zenpage class method instead.'));
	global $_zp_zenpage;
	return $_zp_zenpage->getAllCategories();
}


/**
 * @deprecated
 */
function isProtectedAlbum($album=NULL) {
	self::notify(gettext('Use the album class method <code>isProtected()</code> instead.'));
	global $_zp_current_album;
	if (is_null($album)) $album = $_zp_current_album;
	return $album->isProtected();
}

/**
 * @deprecated
 */
function getRSSHeaderLink($option, $linktext='', $lang='') {
	self::notify(gettext('Use the template function <code>getRSSLink()</code> instead. NOTE: While this function gets a full html link <code>getRSSLink()</code> just returns the URL.'));
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
 * @deprecated
 */
function getZenpageRSSHeaderLink($option='', $categorylink='', $linktext='', $lang='') {
	self::notify(gettext('Use the template function <code>getZenpageRSSLink()</code> instead. NOTE: While this function gets a full html link  <code>getZenpageRSSLink()</code> just returns the URL.'));
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
	self::notify(gettext('Use $_zp_captcha->getCaptcha(). Note that you will require updating your code to the new function.'));
	return $img = NULL;
}

/**
 * Creates an URL for to download of a zipped copy of the current album
 *
 * @deprecated
 */
function printAlbumZip(){
	self::notify(gettext('Use downloaList plugin <code>printDownloadLinkAlbumZip</code>.'));
	global $_zp_current_album;
	setOption('zp_plugin_downloadList',20|ADMIN_PLUGIN|THEME_PLUGIN);
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/downloadList.php');
	printDownloadLinkAlbumZip(gettext('Download a zip file of this album'),$_zp_current_album);
}


?>