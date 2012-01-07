<?php
/**
 * Hitcounter handler for slidshow
 *
 * @package plugins
 */

require_once("../../functions.php");

$album_name = sanitize($_GET["album"]);
$img_name = sanitize($_GET["img"]);

if ($album_name && $img_name ) {
	$_zp_gallery = new Gallery();
	$album = new Album($_zp_gallery, $album_name);
	$image = newImage($album, $img_name);
	//update hit counter
	if (!$album->isMyItem(LIST_RIGHTS)) {
		$hc = $image->get('hitcounter')+1;
		$image->set('hitcounter', $hc);
		$image->save();
	}
}
?>
