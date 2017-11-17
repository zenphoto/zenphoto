<?php

/**
 * DO NOT MODIFY THIS FILE.
 * If you wish to change the appearance or behavior of
 * the site when closed you may edit the .htm and .xmp files
 */
$_contents = @file_get_contents(dirname(dirname(dirname(__FILE__))) . '/zp-data/zenphoto.cfg.php');
if ($_contents) {
	if (strpos($_contents, '<?php') !== false)
		$_contents = '?>' . $_contents;
	@eval($_contents);
	if (@$_zp_conf_vars['site_upgrade_state'] == 'open') {
		// site is now open, redirect to index
		header("HTTP/1.0 307 Found");
		header("Status: 307 Found");
		header('Location: SITEINDEX');
		exit();
	}
}

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