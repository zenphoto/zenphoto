<?php
// force UTF-8// Ø

list($album, $image) = rewrite_get_album_image('album', 'image');
$folders = explode('/', strval($album));
if (array_key_exists(0, $folders) && $folders[0] == CACHEFOLDER) {
	// a failed reference to a cached image?
	require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');
	unset($folders[0]);
	list($image, $args) = getImageProcessorURIFromCacheName(implode('/', $folders).'/'.$image, getWatermarks());
	if (file_exists(getAlbumFolder() . $image)) {
		$uri = getImageURI($args, dirname($image), basename($image), NULL);
		redirectURL($uri, '302');
	}
}

if (isset($_GET['fromlogout'])) {
	redirectURL(WEBPATH . '/index.php', '302');
}

$obj = @$_zp_gallery_page;
$_zp_gallery_page = '404.php';
if (isset($_index_theme)) {
	$_zp_script = SERVERPATH . "/" . THEMEFOLDER . '/' . internalToFilesystem($_index_theme) . '/404.php';
} else {
	$_zp_script = NULL;
}
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
header("HTTP/1.0 404 Not Found");
header("Status: 404 Not Found");
zp_apply_filter('theme_headers');
debug404($album, $image, @$_index_theme);
if ($_zp_script && file_exists($_zp_script)) {
	if (isset($custom) && $custom)
		require_once($custom);
	include($_zp_script);
} else {
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		</head>
		<body>
			<?php
			print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj);
			?>
			<br />
			<a href="<?php echo html_encode(getGalleryIndexURL()); ?>"
				 title="<?php echo gettext('Index'); ?>"><?php echo sprintf(gettext("Return to %s"), getGalleryTitle()); ?></a>
		</body>
	</html>
	<?php
}
?>
