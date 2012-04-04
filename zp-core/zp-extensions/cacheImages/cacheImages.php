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
	global $_zp_current_album, $_zp_current_image, $_zp_gallery, $custom, $enabled;
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
				foreach ($custom as $key=>$cacheimage) {
					if (in_array($key, $enabled)) {
						$size = isset($cacheimage['image_size'])?$cacheimage['image_size']:NULL;
						$width = isset($cacheimage['image_width'])?$cacheimage['image_width']:NULL;
						$height = isset($cacheimage['image_height'])?$cacheimage['image_height']:NULL;
						$cw = isset($cacheimage['crop_width'])?$cacheimage['crop_width']:NULL;
						$ch = isset($cacheimage['crop_height'])?$cacheimage['crop_height']:NULL;
						$cx = isset($cacheimage['crop_x'])?$cacheimage['crop_x']:NULL;
						$cy = isset($cacheimage['crop_y'])?$cacheimage['crop_y']:NULL;
						$thumbstandin = isset($cacheimage['thumb'])?$cacheimage['thumb']:NULL;
						$effects = isset($cacheimage['gray'])?$cacheimage['gray']:NULL;
						$passedWM = isset($cacheimage['wmk'])?$cacheimage['wmk']:NULL;
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
							?>
							<a href="<?php echo $uri; ?>&amp;debug">
								<?php
								if ($thumbstandin) {
									echo '<img src="' . $uri . '" height="8" width="8" />'."\n";
								} else {
									echo ' <img src="' . $uri . '" height="20" width="20" />'."\n";
								}
								?>
							</a>
							<?php
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
$custom = array();
$result = query('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages" ORDER BY `aux`');
while ($row = db_fetch_assoc($result)) {
	$row = unserialize($row['data']);
		$custom[] = $row;
}
$custom = sortMultiArray($custom, array('theme','thumb'));
if (isset($_GET['select'])) {
	$enabled = $_POST['enable'];
} else {
	$enabled = false;
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
} else {
	$r = '/admin.php';
	echo "\n<h2>".$clear."</h2>";
}

$cachesizes = 0;
$currenttheme = $_zp_gallery->getCurrentTheme();
?>
<form name="size_selections" action="?select" method="post">
	<?php XSRFToken('cache_images')?>
	<ul>
		<?php
		foreach ($custom as $key=>$cacheimage) {
			if (!is_array($enabled) || in_array($key, $enabled)) {
				if (is_array($enabled)) {
					$checked = ' checked="checked" disabled="disabled"';
				} else {
					if ($currenttheme == @$cacheimage['theme']) {
						$checked = ' checked="checked"';
					} else {
						$checked = '';
					}
				}
				$cachesizes++;
				$size = isset($cacheimage['image_size'])?$cacheimage['image_size']:NULL;
				$width = isset($cacheimage['image_width'])?$cacheimage['image_width']:NULL;
				$height = isset($cacheimage['image_height'])?$cacheimage['image_height']:NULL;
				$cw = isset($cacheimage['crop_width'])?$cacheimage['crop_width']:NULL;
				$ch = isset($cacheimage['crop_height'])?$cacheimage['crop_height']:NULL;
				$cx = isset($cacheimage['crop_x'])?$cacheimage['crop_x']:NULL;
				$cy = isset($cacheimage['crop_y'])?$cacheimage['crop_y']:NULL;
				$thumbstandin = isset($cacheimage['thumb'])?$cacheimage['thumb']:NULL;
				$effects = isset($cacheimage['gray'])?$cacheimage['gray']:NULL;
				$passedWM = isset($cacheimage['wmk'])?$cacheimage['wmk']:NULL;
				$args = array($size, $width, $height, $cw, $ch, $cx, $cy, NULL, $thumbstandin, NULL, $thumbstandin, $passedWM, NULL, $effects);
				$postfix = getImageCachePostfix($args);
				?>
				<li><?php echo gettext('Apply'); ?> <input type="checkbox" name="enable[]" value="<?php echo $key; ?>"<?php echo $checked; ?> /><i><?php echo $cacheimage['theme']; ?></i> <?php echo $postfix; ?></li>
				<?php
			}
		}
		?>
	</ul>
	<?php

	if (!isset($_GET['select']))		{
		?>
		<p class="buttons">
			<button class="tooltip" type="submit" title="<?php echo gettext("Executes the caching of the selected image sizes."); ?>">
				<img src="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/images/redo.png" alt="" /> <?php echo gettext("Cache the images"); ?>
			</button>
		</p>
		<br clear="all">
		<?php
	}

	?>
</form>
<?php
if (is_array($enabled)) {
	if ($cachesizes) {
		echo '<p>';
		printf(ngettext('%u cache size to apply.','%u cache sizes to apply.',$cachesizes),$cachesizes);
		echo '</p>';
		if ($alb) {
			$album = new Album(NULL, $folder);
			$count =loadAlbum($album);
			echo "\n" . "<br />".sprintf(gettext("Finished: Total of %u images cached."), $count);
		} else {
			$albums = $_zp_gallery->getAlbums();
			shuffle($albums);
			foreach ($albums as $folder) {
				$album = new Album($_zp_gallery, $folder);
				if (!$album->isDynamic()) {
					$count = $count + loadAlbum($album);
				}
			}
			echo "\n" . "<br />".sprintf(gettext("Finished: Total of %u images cached."), $count);
		}
	} else {
		echo '<p>'.gettext('No cache sizes enabled.').'</p>';
	}
}

?>
	<p class="buttons">
		<a title="<?php echo gettext('Back to the album list'); ?>"
			href="<?php echo WEBPATH.'/'.ZENFOLDER.$r; ?>"> <img
			src="<?php echo FULLWEBPATH.'/'.ZENFOLDER; ?>/images/cache.png"
			alt="" /> <strong><?php echo gettext("Back"); ?> </strong>
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
