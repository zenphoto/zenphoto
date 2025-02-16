<?php
/**
 * RSS Extractor and Displayer
 * (c) 2007-2009  Scriptol.com - License Mozilla 1.1. rsslib.php
 * - Modified for zenphoto by s. billard removed unused functions made more resilient
 * - Further modifications and changed to static class by Malte Müller (acrylian) for Zenphoto 1.6.6
 * 
 * @package core
 * @subpackage libraries
 */
class rssLib {

	/**
	 * Gets the main elements of an RSS feed item
	 * 
	 * @param object $item DOM object of an item
	 * @param string $type item type
	 * @return array
	 */
	static function getItemElements($item, $type) {
		$y = array();
		$y["type"] = $type;
		$tnl = $item->getElementsByTagName("title");
		$tnl = $tnl->item(0);
		if (is_object($tnl->firstChild)) {
			$y["title"] = $tnl->firstChild->textContent;
		} else {
			$y["title"] = '';
		}

		$tnl = $item->getElementsByTagName("link");
		$tnl = $tnl->item(0);
		if (is_object($tnl->firstChild)) {
			$link = $tnl->firstChild->textContent;
			$y["link"] = $link;
		} else {
			$y["link"] = '';
		}

		$tnl = $item->getElementsByTagName("description");
		$tnl = $tnl->item(0);
		if (is_object($tnl->firstChild)) {
			$y["description"] = $tnl->firstChild->textContent;
		} else {
			$y["description"] = '';
		}

		$tnl = $item->getElementsByTagName("pubDate");
		$tnl = $tnl->item(0);
		if (is_object($tnl->firstChild)) {
			$y["pubDate"] = $tnl->firstChild->textContent;
		} else {
			$y["pubDate"] = '';
		}
		return $y;
	}

	/**
	 * Gets the channel information of an RSS feed
	 * @param object $channel DOM object
	 * @return array
	 */
	static function getChannel($channel) {
		$rss_content = array();
		$items = $channel->getElementsByTagName("item");
		// Processing channel
		$y = rsslib::getItemElements($channel, 0);	// get description of channel, type 0
		array_push($rss_content, $y);
		// Processing articles
		foreach ($items as $item) {
			$y = rsslib::getItemElements($item, 1); // get description of article, type 1
			array_push($rss_content, $y);
		}
		return $rss_content;
	}

	/**
	 * Retrieves an RSS feed
	 * @param string $url URL of the RSS feed to retrieve
	 * @return array
	 */
	static function retrieve($url) {
		$rss_content = array();
		$doc = new DOMDocument();
		if (@$doc->load($url)) {
			$channels = $doc->getElementsByTagName("channel");
			foreach ($channels as $channel) {
				$rss_content = array_merge($rss_content, rssLib::getChannel($channel));
			}
			return $rss_content;
		} else {
			return NULL;
		}
	}

}