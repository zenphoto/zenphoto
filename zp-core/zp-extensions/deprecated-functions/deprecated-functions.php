<?php
/**
 * The version number within @deprecated indicates the version these will be removed completely
 * 
 * @package plugins
 * @subpackage deprecated-functions
 */


if (function_exists('printImageStatistic')) {

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printPopularAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = 'hitcounter', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecated_functions::notify(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "popular", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printLatestAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
		deprecated_functions::notify(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "latest", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printMostRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecated_functions::notify(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "mostrated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printTopRatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $threshold = 0, $collection = false) {
		deprecated_functions::notify(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "toprated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $threshold, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printAlbumStatistisc() instead
	 */
	function printLatestUpdatedAlbums($number = 5, $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $albumfolder = '', $firstimglink = false, $collection = false) {
		deprecated_functions::notify(gettext("Use printAlbumStatistisc() instead"));
		printAlbumStatistic($number, "latestupdated", $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $albumfolder, $firstimglink, $collection);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printPopularImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "popular", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printTopRatedImages($number = 5, $albumfolder = "", $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "toprated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}
	
	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printMostRatedImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false, $threshold = 0) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "mostrated", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink, $threshold);
	}

		/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImages($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}
		/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImagesByDate($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest-date", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}

	/**
	 * @deprecated 2.0 – Use printImageStatistisc() instead
	 */
	function printLatestImagesByMtime($number = 5, $albumfolder = '', $showtitle = false, $showdate = false, $showdesc = false, $desclength = 40, $showstatistic = '', $width = NULL, $height = NULL, $crop = NULL, $collection = false, $fullimagelink = false) {
		deprecated_functions::notify(gettext("Use printImageStatistisc() instead"));
		printImageStatistic($number, "latest-mtime", $albumfolder, $showtitle, $showdate, $showdesc, $desclength, $showstatistic, $width, $height, $crop, $collection, $fullimagelink);
	}

}

/**
 *
 * fixes unbalanced HTML tags. Used by shortenContent when PHP tidy is not present
 *
 * @deprecated 2.0 Use tidyHTML() instead
 * @since 1.5
 *
 * @param string $html
 * @return string
 */
function cleanHTML($html) {
	deprecated_functions::notify(gettext("Use tidyHTML() instead"));
	return tidyHTML($html);
}

/**
 * Returns the count of all the images in the album and any subalbums
 * @deprecated 2.0
 * @since 1.5.2
 * 
 * @param object $album The album whose image count you want
 * @return int
 */
function getTotalImagesIn($album) {
	deprecated_functions::notify(gettext("Use AlbumBase class method getNumAllImages() instead"));
	return $album->getNumAllImages();
}

/**
 * checks if the item has expired
 * @deprecated ZenphotoCMS 2.0 - Use themeObject::checkScheduledPublishing() instead
 * @since 1.5.7
 * @param array $row database row of the object
 */
function checkPublishDates($row) {
	deprecated_functions::notify(gettext("Use themeObject::checkScheduledPublishing() instead"));
	return themeObject::checkScheduledPublishing($row);
}

/**
 * Zenphoto general deprecated functions
 * 
 * 
 *
 * @package plugins
 * @subpackage deprecated-functions
 */
class internal_deprecations {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains() instead
	 * @since 1.5
	 */
	static function LanguageSubdomains() {
		deprecated_functions::notify(gettext('Use getLanguageSubdomains() instead'));
	}
	/**
	 * @deprecated 2.0 Use getLanguageText() instead
	 * @since 1.5
	 */
	static function getLanguageText() {
		deprecated_functions::notify(gettext('Use getLanguageText() instead'));
	}
	/**
	 * @deprecated 2.0 Use setexifvars() instead
	 * @since 1.5
	 */
	static function setexifvars() {
		deprecated_functions::notify(gettext('Use setexifvars() instead'));
	}
	/**
	 * @deprecated 2.0 Use hasPrimaryScripts() instead
	 * @since 1.5
	 */
	static function hasPrimaryScripts() {
		deprecated_functions::notify(gettext('Use hasPrimaryScripts() instead'));
	}
	/**
	 * @deprecated 2.0 Use removeDir() instead
	 * @since 1.5
	 */
	static function removeDir() {
		deprecated_functions::notify(gettext('Use removeDir() instead'));
	}
	/**
	 * @deprecated 2.0 Use tagURLs() instead
	 * @since 1.5
	 */
	static function tagURLs() {
		deprecated_functions::notify(gettext('Use tagURLs() instead'));
	}
	/**
	 * @deprecated 2.0 Use unTagURLs() instead
	 * @since 1.5
	 */
	static function unTagURLs() {
		deprecated_functions::notify(gettext('Use unTagURLs() instead'));
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink() instead
	 * @since 1.5
	 */
	static function updateImageProcessorLink() {
		deprecated_functions::notify(gettext('Use updateImageProcessorLink() instead'));
	}
	/**
	 * @deprecated 2.0 Use pluginDebug() instead
	 * @since 1.5
	 */
	static function pluginDebug() {
		deprecated_functions::notify(gettext('Use pluginDebug() instead'));
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash() instead
	 * @since 1.5
	 */
	static function removeTrailingSlash($string) {
		deprecated_functions::notify(gettext('Use removeTrailingSlash() instead'));
	}

	/**
	 * @deprecated 2.0 Use htmlTidy() instead
	 * @since 1.5
	 */
	static function tidyHTML() {
		deprecated_functions::notify(gettext('Use tidyHTML() instead'));
	}

	/**
	 * @deprecated 2.0 Use instantiate() method
	 * @since 1.4.6
	 */
	static function PersistentObject() {
		deprecated_functions::notify(gettext('Use the instantiate method instead'));
	}

}

/**
 * @deprecated 2.0
 * @since 1.5
 */
class zpFunctions {

	/**
	 * @deprecated 2.0 Use getLanguageSubdomains()
	 * @since 1.5
	 * @see getLanguageSubdomains()
	 */
	static function LanguageSubdomains() {
		internal_deprecations::LanguageSubdomains();
		return getLanguageSubdomains();
	}

	/**
	 * @deprecated 2.0 Use getLanguageText()
	 * @since 1.5
	 * @see getLanguageText()
	 */
	static function getLanguageText($loc = NULL, $separator = NULL) {
		internal_deprecations::getLanguageText();
		return getLanguageText($loc, $separator);
	}

	/**
	 * @deprecated 2.0 Use setexifvars()
	 * @since 1.5
	 * @see setexifvars()
	 */
	static function setexifvars() {
		internal_deprecations::setexifvars();
		setexifvars();
	}

	/**
	 * @deprecated 2.0 Use hasPrimaryScripts()
	 * @since 1.5
	 * @see hasPrimaryScripts()
	 */
	static function hasPrimaryScripts() {
		internal_deprecations::hasPrimaryScripts();
		return hasPrimaryScripts();
	}

	/**
	 * @deprecated 2.0 Use removeDir()
	 * @since 1.5
	 * @see removeDir()
	 */
	static function removeDir($path, $within = false) {
		internal_deprecations::removeDir();
		return removeDir($path, $within);
	}

	/**
	 * @deprecated 2.0 Use tagURLs()
	 * @since 1.5
	 * @see tagURLs()
	 */
	static function tagURLs($text) {
		internal_deprecations::tagURLs();
		return tagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use untagURLs()
	 * @since 1.5
	 * @see untagURLs()
	 */
	static function unTagURLs($text) {
		internal_deprecations::unTagURLs();
		return unTagURLs($text);
	}

	/**
	 * @deprecated 2.0 Use updateImageProcessorLink()
	 * @since 1.5
	 * @see updateImageProcessorLink()
	 */
	static function updateImageProcessorLink($text) {
		internal_deprecations::updateImageProcessorLink();
		return updateImageProcessorLink($text);
	}

	/**
	 * @deprecated 2.0 Use pluginDebug()
	 * @since 1.5
	 * @see pluginDebug()
	 */
	static function pluginDebug($extension, $priority, $start) {
		internal_deprecations::pluginDebug();
		pluginDebug($extension, $priority, $start);
	}

	/**
	 * @deprecated 2.0 Use removeTrailingSlash()
	 * @since 1.5
	 * @see removeTrailingSlash()
	 */
	static function removeTrailingSlash($string) {
		internal_deprecations::removeTrailingSlash();
		return removeTrailingSlash($string);
	}

	/**
	 * @deprecated 2.0 Use tidyHTML()
	 * @since 1.5
	 * @see tidyHTML()
	 */
	static function tidyHTML($html) {
		internal_deprecations::tidyHTML();
		return tidyHTML($html);
	}
	
	

}