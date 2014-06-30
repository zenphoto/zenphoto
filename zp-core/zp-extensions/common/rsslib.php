<?php
/**
 * @package plugins
 * @subpackage zenphoto_news
 */


/*
 RSS Extractor and Displayer
 (c) 2007-2009  Scriptol.com - License Mozilla 1.1.
 rsslib.php

 Modified for zenphoto by s. billard
 		removed unused functions
 		made more resilient

 Requirements:
 - PHP 5.
 - A RSS feed.

 Using the library:
 Insert this code into the page that displays the RSS feed:

 <?php
 require_once("rsslib.php");
 echo RSS_Display("http://www.xul.fr/rss.xml", 15);
 ?>

 */

function RSS_Tags($item, $type) {
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
		$y["pubDate"]  = $tnl->firstChild->textContent;
	} else {
		$y["pubDate"] = '';
	}
	return $y;
}

function RSS_Channel($channel) {
	$RSS_Content = array();
	$items = $channel->getElementsByTagName("item");
	// Processing channel
	$y = RSS_Tags($channel, 0);		// get description of channel, type 0
	array_push($RSS_Content, $y);
	// Processing articles
	foreach($items as $item) {
		$y = RSS_Tags($item, 1);	// get description of article, type 1
		array_push($RSS_Content, $y);
	}
	return $RSS_Content;
}

function RSS_Retrieve($url) {
	$RSS_Content = array();
	$doc  = new DOMDocument();
	if (@$doc->load($url)) {
		$channels = $doc->getElementsByTagName("channel");
		foreach($channels as $channel) {
			$RSS_Content = array_merge($RSS_Content, RSS_Channel($channel));
		}
		return $RSS_Content;
	} else {
		return NULL;
	}
}

?>
