<?php
/**
 * Hitcounter handler for slidshow
 *
 * @package zpcore\plugins\slideshow2
 */

require_once("../../functions/functions.php");

$album_name = sanitize($_GET["album"]);
$img_name = sanitize($_GET["img"]);

if ($album_name && $img_name ) {
	$album = AlbumBase::newAlbum($album_name);
	$image = Image::newImage($album, $img_name);
	//update hit counter
	if (!$album->isMyItem(LIST_RIGHTS)) {
		$hc = $image->getHitcounter()+1;
		$image->set('hitcounter', $hc);
		$image->save();
	}
}
?>
