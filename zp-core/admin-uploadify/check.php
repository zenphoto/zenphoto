<?php
require_once(dirname(dirname(__FILE__)).'/functions.php');
if(!function_exists("json_encode")) {
	// load the drop-in replacement library
	require_once('../lib-json.php');
}
$fileArray = array();
if (isset($_POST['folder'])) {
	$albumparmas = explode(':', sanitize($_POST['folder'],3),3);
	$folder = $albumparmas[1];
} else {
	$folder = '';
}
foreach ($_POST as $key => $value) {
	if ($key != 'folder') {	
		$name = seoFriendly($value);
		if (strpos($name,'.')===0) $name = md5($value).$name; // soe stripped out all the name.
		$targetPath = getAlbumFolder().internalToFilesystem($folder.'/'.$name);
		if (file_exists($targetPath)) {
			$fileArray[$key] = $name;
		}
	}
}

if (count($fileArray) > 0) {
	$r =  json_encode($fileArray);
} else {
	$r = '{}';
}
echo $r;

?>