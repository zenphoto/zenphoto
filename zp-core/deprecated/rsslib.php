<?php
/**
 * RSS Extractor and Displayer
 * (c) 2007-2009  Scriptol.com - License Mozilla 1.1.
 * rsslib.php
 * Modified for zenphoto by s. billard
 * 		removed unused functions
 * 		made more resilient
 * 
 * Requirements:
 * - PHP 5.
 * - A RSS feed
 */
function RSS_Tags($item, $type) {
	deprecationNotice(gettext('Use rsslib::getItemElements() instead'));
	return rsslib::getItemElements($item, $type);
}

function RSS_Channel($channel) {
	deprecationNotice(gettext('Use rsslib::getChannel() instead'));
	return rsslib::getChannel($channel);
}

function RSS_Retrieve($url) {
	deprecationNotice(gettext('Use rsslib::retrieve() instead'));
	return rsslib::retrieve($url);
}

?>
