<?php
/**
 * This template is used to generate cache images. Running it will process the entire gallery,
 * supplying an album name (ex: loadAlbums.php?album=newalbum) will only process the album named.
 * Passing clear=on will purge the designated cache before generating cache images
 * @package core
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once("../../admin-globals.php");
require_once(SERVERPATH.'/'.ZENFOLDER.'/template-functions.php');


if (isset($_REQUEST['album'])) {
	$localrights = ALBUM_RIGHTS;
} else {
	$localrights = NULL;
}
admin_securityChecks($localrights, $return = currentRelativeURL());

XSRFdefender('cache_images');

function loadAlbum($album) {
	global $_zp_current_album, $_zp_current_image, $_zp_gallery, $custom;
	$subalbums = $album->getAlbums();
	$started = false;
	$tcount = $count = 0;
	foreach ($subalbums as $folder) {
		$subalbum = new Album($_zp_gallery, $folder);
		if (!$subalbum->isDynamic()) {
			$tcount = $tcount + loadAlbum($subalbum);
		}
	}
	$theme = $_zp_gallery->getCurrentTheme();
	$id = 0;
	$parent = getUrAlbum($album);
	$albumtheme = $parent->getAlbumTheme();
	if (!empty($albumtheme)) {
		$theme = $albumtheme;
		$id = $parent->getID();
	}
	loadLocalOptions($id,$theme);
	$_zp_current_album = $album;

	if ($album->getNumImages() > 0) {
		echo "<br />" . $album->name . ' ';
		while (next_image(true)) {
			if (isImagePhoto($_zp_current_image)) {
				$countit = 0;
				foreach ($custom as $crop) {
					$size = isset($crop['image_size'])?$crop['image_size']:NULL;
					$width = isset($crop['image_width'])?$crop['image_width']:NULL;
					$height = isset($crop['image_height'])?$crop['image_height']:NULL;
					$cw = isset($crop['crop_width'])?$crop['crop_width']:NULL;
					$ch = isset($crop['crop_height'])?$crop['crop_height']:NULL;
					$cx = isset($crop['crop_x'])?$crop['crop_x']:NULL;
					$cy = isset($crop['crop_y'])?$crop['crop_y']:NULL;
					$thumbstandin = isset($crop['thumb'])?$crop['thumb']:NULL;
					$effects = isset($crop['gray'])?$crop['gray']:NULL;
					$passedWM = isset($crop['wmk'])?$crop['wmk']:NULL;
					$args = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
					$args = getImageParameters($args, $album->name);
					$uri = getImageURI($args, $album->name, $_zp_current_image->filename, $_zp_current_image->filemtime);
					if (strpos($uri, 'i.php?') !== false) {
						if (!($count+$countit)) {
							echo "{ ";
						} else {
							echo ' | ';
						}
						$countit = 1;
						if ($thumbstandin) {
							echo '<img src="' . $uri . '" height="8" width="8" />'."\n";
						} else {
							echo ' <img src="' . $uri . '" height="20" width="20" />'."\n";
						}
					}
				}
				$count = $count+$countit;
			}
		}
		if ($count) echo ' } ';
		printf(ngettext('[%u image]','[%u images]',$count),$count);
		echo "<br />\n";
	}
	return $count + $tcount;
}
if (isset($_GET['album'])) {
	$alb = sanitize($_GET['album']);
} else if (isset($_POST['album'])) {
	$alb = sanitize(urldecode($_POST['album']));
} else {
	$alb = '';
}
if ($alb) {
	$folder = sanitize_path($alb);
	$object = $folder;
	$tab = 'edit';
	$album = new Album(NULL, $folder);
	if (!$album->isMyItem(ALBUM_RIGHTS)) {
		if (!zp_apply_filter('admin_managed_albums_access',false, $return)) {
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php');
			exitZP();
		}
	}
} else {
	$object = '<em>'.gettext('Gallery').'</em>';
	$tab = gettext('overview');
}
$options = $custom = array();
$result = query('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages"');
while ($row = db_fetch_assoc($result)) {
	$custom[] = unserialize($row['data']);
}
if (empty($custom)) {
	$custom[] = array('image_size'=>getOption('image_size'),'image_use_side'=>getOption('image_use_side'));
	$custom[] = array('image_size'=>getOption('thumb_size'),'thumb'=>1);
}

$zenphoto_tabs['overview']['subtabs']=array(gettext('Cache')=>'');
printAdminHeader($tab,gettext('Cache'));
echo "\n</head>";
echo "\n<body>";

printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';
?>
<?php printSubtabs('Cache'); ?>
<div class="tabbox">
<?php
zp_apply_filter('admin_note','cache', '');
$clear = sprintf(gettext('Refreshing cache for %s'), $object);
$count = 0;


if ($alb) {
	$r = '/admin-edit.php?page=edit&album='.$alb;
	echo "\n<h2>".$clear."</h2>";
	$album = new Album(NULL, $folder);
	$count =loadAlbum($album);
} else {
	$r = '/admin.php';
	echo "\n<h2>".$clear."</h2>";
	$albums = $_zp_gallery->getAlbums();
	shuffle($albums);
	foreach ($albums as $folder) {
		$album = new Album($_zp_gallery, $folder);
		if (!$album->isDynamic()) {
			$count = $count + loadAlbum($album);
		}
	}
}
echo "\n" . "<br />".sprintf(gettext("Finished: Total of %u images cached."), $count);

?>
<p class="buttons">
	<a title="<?php echo gettext('Back to the album list'); ?>" href="<?php echo WEBPATH.'/'.ZENFOLDER.$r; ?>">
	<img	src="<?php echo FULLWEBPATH.'/'.ZENFOLDER; ?>/images/cache.png" alt="" />
	<strong><?php echo gettext("Back"); ?></strong>
	</a>
</p>
<br clear="all">
<?php
echo "\n" . '</div>';
echo "\n" . '</div>';
echo "\n" . '</div>';

printAdminFooter();

echo "\n</body>";
echo "\n</head>";
?>
