<?php

/**
 * Zenphoto general deprecated functions
 */
class internal_deprecations {

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function PersistentObject() {
		deprecated_functions::notify(gettext('Use the instantiate method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function next_album() {
		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function next_image() {
		deprecated_functions::notify(gettext('Sort parameter options should be set instead with the setSortType() and setSortDirection() object methods at the head of your script.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function getSearchURL() {
		deprecated_functions::notify(gettext('Pass array("albums" => array(album, album, ...)) for the object list.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function getGalleryIndexURL() {
		deprecated_functions::notify(gettext('The parameter to getGalleryIndexURL() is deprecated. Use getCustomPageURL() instead for custom pages.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	public static function setSortDirection() {
		deprecated_functions::notify(gettext('The parameter order album::setSortDirection($what, $val) is deprecated. Use album::setSortDirection($val, $what) instead.'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getFolder() {
		deprecated_functions::notify(gettext('Use the getFilename() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getImageLink() {
		deprecated_functions::notify(gettext('Use the getLink() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getAlbumLink() {
		deprecated_functions::notify(gettext('Use the getLink() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getAlbumSortType() {
		deprecated_functions::notify(gettext('Use the getSortType() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function getAlbumThumb() {
		deprecated_functions::notify(gettext('Use the getThumb() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function setAlbumThumb() {
		deprecated_functions::notify(gettext('Use the setThumb() method instead'));
	}

	/**
	 * @deprecated
	 * @since 1.4.6
	 */
	static function setSubalbumSortType($sorttype) {
		deprecated_functions::notify(gettext('Use the setSortType() method instead'));
	}

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
	deprecated_functions::notify(gettext('Register a “theme_head” filter.'));
	global $_zp_plugin_scripts;
	$_zp_plugin_scripts[] = $script;
	if (!zp_has_filter('theme_head', 'deprecated_functions::addPluginScript()')) {
		zp_register_filter('theme_head', 'deprecated_functions::addPluginScript()');
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
	deprecated_functions::notify(gettext('printPreloadScript is deprecated. It is a helper for a specific theme and should be placed within that theme’s "functions.php" script.'));
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
	$protocol = PROTOCOL . '://';
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
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss&amp;albumtitle=" . urlencode(getAlbumTitle()) . "&amp;albumname=" . urlencode($_zp_current_album->getFileName()) . "&amp;lang=" . $lang . "\" />\n";
			}
		case "Collection":
			if (getOption('RSS_album_image')) {
				return "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"" . html_encode($linktext) . "\" href=\"" . $protocol . $host . WEBPATH . "/index.php?rss&amp;albumtitle=" . urlencode(getAlbumTitle()) . "&amp;folder=" . urlencode($_zp_current_album->getFileName()) . "&amp;lang=" . $lang . "\" />\n";
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
function generateCaptcha(&$img) {
	deprecated_functions::notify(gettext('Use $_zp_captcha->getCaptcha($prompt). Note that you will require updating your code to the new function.'));
	return $img = NULL;
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printAlbumZip() {
	deprecated_functions::notify(gettext('Use downloadList plugin <code>printDownloadAlbumZipURL()</code>.'));
	global $_zp_current_album;
	enableExtension('downloadList', 20 | ADMIN_PLUGIN | THEME_PLUGIN, false);
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList.php');
	printDownloadAlbumZipURL(gettext('Download a zip file of this album'), $_zp_current_album);
}

/**
 * @deprecated
 * @since 1.4.3
 */
function printImageDiv() {
	deprecated_functions::notify(gettext('Use printImageThumb().'));
	if (!isset($_GET['sortable'])) {
		echo '<a href="' . html_encode(getImageURL()) . '" title="' . html_encode(getImageTitle()) . '">';
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
	deprecated_functions::notify(gettext('Use echo “image_”.$_zp_current_image->getID().'));
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
	deprecated_functions::notify(gettext('Use echo “image_”.$_zp_current_album->getID().'));
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
	deprecated_functions::notify(gettext("If you need this function copy it to your theme’s functions.php script."));
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
function printAdminToolbox($id = 'admin', $customDIV = false) {
	deprecated_functions::notify(gettext('This feature is now done by a "theme_body_close" filter. You can remove the function call.'));
}

/**
 * @deprecated
 * @since version 1.4.6
 */
function getURL($image) {
	deprecated_functions::notify(gettext('Use the appropriate object method. <strong>Note:</strong> this function gives different results depending on the setting of <code>mod_rewrite</code> so which object method to use depends on what your settings were!'));
	return rewrite_path(pathurlencode($image->getAlbumName()) . "/" . urlencode($image->filename), "/index.php?album=" . pathurlencode($image->getAlbumName()) . "&image=" . urlencode($image->filename));
}

/**
 * @deprecated
 * @since version 1.4.6
 */
function getLink($url, $text, $title = NULL, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use getLinkHTML()'));
	return getLinkHTML($url, $text, $title, $class, $id);
}

/**
 * @deprecated
 * @since version 1.4.6
 */
function printLink($url, $text, $title = NULL, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printLinkHTML()'));
	echo getLinkHTML($url, $text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function setPluginDomain($plugindomain) {
	deprecated_functions::notify(gettext('use setupDomain()'));
	return setupDomain($plugindomain, "plugin");
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function setThemeDomain($themedomain) {
	deprecated_functions::notify(gettext('use setupDomain()'));
	return setupDomain($themedomain, "theme");
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function getAlbumLinkURL($album = NULL) {
	deprecated_functions::notify(gettext('use getAlbumURL()'));
	return getAlbumURL();
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function getImageLinkURL() {
	deprecated_functions::notify(gettext('use getImageURL()'));
	return getImageURL();
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printAlbumLink($text, $title, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printAlbumURL()'));
	printAlbumURL($text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printImageLink($text, $title, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printImageURL()'));
	printImageURL($text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printNextPageLink($text, $title = NULL, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printNextPageURL()'));
	printNextPageURL($text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printPrevPageLink($text, $title = NULL, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printPrevPageURL()'));
	printPrevPageURL($text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function printSizedImageLink($size, $text, $title, $class = NULL, $id = NULL) {
	deprecated_functions::notify(gettext('use printSizedImageURL()'));
	printSizedImageURL($size, $text, $title, $class, $id);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function openedForComments() {
	deprecated_functions::notify(gettext("use the object’s getCommentsAllowed() method"));
	global $_zp_gallery_page, $_zp_current_image, $_zp_current_album, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	switch ($_zp_gallery_page) {
		case 'album.php':
			return $_zp_current_album->getCommentsAllowed();
		case 'image.php':
			return $_zp_current_image->getCommentsAllowed();
		case 'pages.php':
			return $_zp_current_zenpage_page->getCommentsAllowed();
		case 'news.php':
			if (is_NewsArticle()) {
				$_zp_current_zenpage_news->getCommentsAllowed();
			}
	}
	return false;
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function addPluginType($suffix, $objectName) {
	deprecated_functions::notify(gettext("use Gallery::addImageHandler()"));
	return Gallery::addImageHandler($suffix, $objectName);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function is_valid_image($filename) {
	deprecated_functions::notify(gettext("use Gallery::validImage()"));
	return Gallery::validImage($filename);
}

/**
 * @deprecated
 * @since 1.4.6
 *
 */
function is_valid_other_type($filename) {
	deprecated_functions::notify(gettext("use Gallery::validImageAlt()"));
	return Gallery::validImageAlt($filename);
}

?>
