<?php

$glob = array();
if (($dir = opendir(dirname(__FILE__))) !== false) {
	while (($file = readdir($dir)) !== false) {
		preg_match('~(.*)\-closed\.*~', $file, $matches);
		if (@$matches[1]) {
			$glob[@$matches[1]] = $file;
		}
	}
}
$xml = '';
foreach ($glob as $key => $file) {
	if (isset($_GET['$key'])) {
		$path = dirname(__FILE__) . '/' . $file;
		$xml = file_get_contents($path);
		$xml = preg_replace('~<pubDate>(.*)</pubDate>~', '<pubDate>' . date("r", time()) . '</pubDate>', $xml);
		echo $xml;
	}
}
if (empty($xml)) {
	echo file_get_contents(dirname(__FILE__) . '/closed.htm');
}
?>