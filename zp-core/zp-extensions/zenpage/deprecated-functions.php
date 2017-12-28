<?php

/**
 * These are the Zenpage functions which have been deprecated
 *
 * See the deprecated functions plugin for examples
 * @package plugins/zenpage
 */
class Zenpage_internal_deprecations {

}

/**
 * @deprecated
 * @since 1.4.0
 */
function getNextPrevNews($option = 'Next', $sortorder = 'date', $sortdirection = 'desc') {
	deprecated_functions::notify(gettext('Use the individual getPrevNewsURL() and getNextNewsURL() functions.'));
	$request = 'get' . ucfirst($option) . 'NewsURL';
	return $request($sortorder, $sortdirection);
}

/**
 * @deprecated
 * @since 1.4.0
 */
function zenpagePublish($obj, $show) {
	deprecated_functions::notify(gettext('Use the setShow method directly.'));
	$obj->setShow($show);
	$obj->save();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getNewsCustomData() {
	global $_zp_current_article;
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	if (!is_null($_zp_current_article)) {
		return $_zp_current_article->getCustomData();
	}
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printNewsCustomData() {
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	echo getNewsCustomData();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getNewsCategoryCustomData() {
	global $_zp_current_category;
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	if (!is_null($_zp_current_category)) {
		return $_zp_current_category->getCustomData();
	}
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printNewsCategoryCustomData() {
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	echo getNewsCategoryCustomData();
}

/**
 * @deprecated
 * @since 1.4.0
 */
function getPageCustomData() {
	global $_zp_current_page;
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	if (!is_null($_zp_current_page)) {
		return $_zp_current_page->getCustomData();
	}
}

/**
 * @deprecated
 * @since 1.4.0
 */
function printPageCustomData() {
	deprecated_functions::notify(gettext('Use customFieldExtender to define unique fields'));
	echo getPageCustomData();
}

/**
 * @deprecated
 * @since 1.5.3
 */
function getContentShorten($text, $shorten, $shortenindicator = NULL, $readmore = NULL, $readmoreurl = NULL) {
	deprecated_functions::notify(gettext('Use shortenContent()'));
	$readmorelink = '';
	if (is_null($shortenindicator)) {
		$shortenindicator = ZP_SHORTENINDICATOR;
	}
	if (is_null($readmore)) {
		$readmore = get_language_string(ZP_READ_MORE);
	}
	if (!is_null($readmoreurl)) {
		$readmorelink = '<p class="readmorelink"><a href="' . html_encode($readmoreurl) . '" title="' . html_encode($readmore) . '">' . html_encode($readmore) . '</a></p>';
	}

	if (!empty($shorten)) {
		$text = shortenContent($text, $shorten, $shortenindicator . $readmorelink);
	}
	return $text;
}

?>