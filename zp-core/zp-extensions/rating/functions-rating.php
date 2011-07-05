<?php
/**
 * rating plugin - utility functions
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$_rating_current_IPlist = array();
/**
 * Returns the last vote rating from an IP or false if
 * no vote on record
 *
 * @param string $ip
 * @param array $usedips
 * @param float $ratingAverage
 * @return float
 */
function getRatingByIP($ip, $usedips, $ratingAverage) {
	global $_rating_current_IPlist;
	$rating = 0;
	if (empty($_rating_current_IPlist)) {
		if (!empty($usedips)) {
			$_rating_current_IPlist = unserialize($usedips);
			if (array_key_exists(0, $_rating_current_IPlist) ||
					array_key_exists(count($_rating_current_IPlist)-1, $_rating_current_IPlist)) { // convert old list
				$rating_list = array();
				foreach ($_rating_current_IPlist as $key) {
					$rating_list[$key] = $ratingAverage;
				}
				$_rating_current_IPlist = $rating_list;
			}
			if (array_key_exists($ip, $_rating_current_IPlist)) {
				return $_rating_current_IPlist[$ip];
			}
		}
	}
	return false;
}

/**
 * returns the $object for the current loaded page
 *
 * @param object $object
 * @return object
 */
function getCurrentPageObject() {
	global $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	switch ($_zp_gallery_page) {
		case 'album.php':
			return $_zp_current_album;
		case 'image.php':
			return $_zp_current_image;
		case 'news.php':
			return $_zp_current_zenpage_news;
		case 'pages.php':
			return $_zp_current_zenpage_page;
		default:
			return NULL;
	}
}
?>