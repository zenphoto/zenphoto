<?php
// force UTF-8// Ø

list($album, $image) = rewrite_get_album_image('album', 'image');
$folders = explode('/', $album);
if (array_key_exists(0, $folders) && $folders[0] == CACHEFOLDER) {
	// a failed reference to a cached image?
	require_once(SERVERPATH . '/' . ZENFOLDER . '/admin-functions.php');
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/functions.php');
	unset($folders[0]);
	list($image, $args) = getImageProcessorURIFromCacheName(implode('/', $folders), getWatermarks());
	if (file_exists(getAlbumFolder() . $image)) {
		$uri = getImageURI($args, dirname($image), basename($image), NULL);
		header("HTTP/1.0 302 Found");
		header("Status: 302 Found");
		header('Location: ' . $uri);
		exitZP();
	}
}

debug404($album, $image, @$_index_theme);
$_zp_gallery_page = '404.php';
$_zp_script = SERVERPATH . "/" . THEMEFOLDER . '/' . internalToFilesystem($_index_theme) . '/404.php';
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
header("HTTP/1.0 404 Not Found");
header("Status: 404 Not Found");
zp_apply_filter('theme_headers');
if (file_exists($_zp_script)) {
	if (isset($custom) && $custom)
		require_once($custom);
	include($_zp_script);
} else {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		</head>
		<body>
			<?php
			echo "\n<strong>" . gettext("Zenphoto Error:</strong> the requested object was not found.");
			if (isset($album)) {
				echo '<br />' . sprintf(gettext('Album: %s'), html_encode($album));
			}
			if (isset($image)) {
				echo '<br />' . sprintf(gettext('Image: %s'), html_encode($image));
			}
			if (isset($obj)) {
				echo '<br />' . sprintf(gettext('Page: %s'), html_encode(substr(basename($obj), 0, -4)));
			}
			?>
			<br />
			<a href="<?php echo html_encode(getGalleryIndexURL()); ?>"
				 title="<?php echo gettext('Albums Index'); ?>"><?php echo sprintf(gettext("Return to %s"), getGalleryTitle()); ?></a>
		</body>
	</html>
	<?php
}
?>