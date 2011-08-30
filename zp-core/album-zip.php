<?php
/*
 * This is the handler script for album zip downloads
 */
if (!defined('OFFSET_PATH')) define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/functions.php');
if(isset($_GET['album']) && is_dir(realpath(ALBUM_FOLDER_SERVERPATH . internalToFilesystem($_GET['album'])))){
	createAlbumZip(sanitize_path($_GET['album']));
}

/*
 * adds an album into the zip file
 * recurses into the albums subalbums
 *
 * @param object $album album object to add
 * @param int $base the length of the base album name
 * @param object $zip container zipfile object
 */
function zipAddAlbum($album, $base, $zip) {
	global $_zp_zip_list, $zip_gallery;
	$albumbase = '.'.substr($album->name,$base).'/';
	foreach ($album->sidecars as $suffix) {
		$f = $albumbase.$album->name.'.'.$suffix;
		if (file_exists($f)) {
			$_zp_zip_list[] = $f;
		}
	}
	$images = $album->getImages();
	foreach ($images as $imagename) {
		$image = newImage($album, $imagename);
		$_zp_zip_list[] = $albumbase.$image->filename;
		$imagebase = stripSuffix($image->filename);
		foreach ($image->sidecars as $suffix) {
			$f = $albumbase.$imagebase.'.'.$suffix;
			if (file_exists($f)) {
				$_zp_zip_list[] = $f;
			}
		}
	}
	$albums = $album->getAlbums();
	foreach ($albums as $albumname) {
		$subalbum = new Album($zip_gallery,$albumname);
		if ($subalbum->exists && !$album->isDynamic()) {
			zipAddAlbum($subalbum, $base, $zip);
		}
	}
}

/**
 * Creates a zip file of the album
 *
 * @param string $albumname album folder
 */
function createAlbumZip($albumname){
	global $_zp_zip_list, $zip_gallery;
	$zip_gallery = new Gallery();
	$album = new Album($zip_gallery, $albumname);
	if (!$album->isMyItem(LIST_RIGHTS) && !checkAlbumPassword($albumname)) {
		pageError(403, gettext("Forbidden"));
		exit();
	}
	if (!$album->exists) {
		pageError(404, gettext('Album not found'));
		exit();
	}
	$persist = $zip_gallery->getPersistentArchive();
	$dest = $album->localpath.'.zip';
	if (!$persist  || !file_exists($dest)) {
		include_once('archive.php');
		$curdir = getcwd();
		chdir($album->localpath);
		$_zp_zip_list = array();
		$z = new zip_file($dest);
		$z->set_options(array('basedir' => realpath($album->localpath.'/'), 'inmemory' => 0, 'recurse' => 0, 'storepaths' => 1));
		zipAddAlbum($album, strlen($albumname), $z);
		$z->add_files($_zp_zip_list);
		$z->create_archive();
		unset($_zp_zip_list);
		chdir($curdir);
	}
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . pathurlencode($albumname) . '.zip"');
	header("Content-Length: " . filesize($dest));
	printLargeFileContents($dest);
	if (!$persist) {
		unlink($dest);
	}
	unset($zip_gallery);
	unset($album);
	unset($persist);
	unset($dest);
}
?>