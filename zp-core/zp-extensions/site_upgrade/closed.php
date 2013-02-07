<?php
if (isset($_GET['rss'])) {
	$path = dirname(__FILE__).'/rss-closed.xml';
	if (file_exists($path)) {
		$xml = file_get_contents($path);
		$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>'.date("r",time()).'</pubDate>', $xml);
		echo $xml;
	}
} else {
	header('Location: closed.htm', true, 301);
}
?>