<?php
/**
 * SEO file/folder name clenser
 *
 * This plugin will scan your images and albums for file/folder names that are not <i>SEO friendly</i>.
 * It will rename those that found needing improvement replacing offending characters with friendly equivalents.
 *
 * Note: Clicking the button causes this process to execute. There is no <i>undo</i>.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage seo
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');


admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());

XSRFdefender('seo_cleanup');

function getE($e) {
	switch ($e) {
		case 2:
			return gettext("Image already exists.");
		case 3:
			return gettext("Album already exists.");
		case 4:
			return gettext("Cannot move, copy, or rename to a subalbum of this album.");
		case 5:
			return gettext("Cannot move, copy, or rename to a dynamic album.");
		case 6:
			return gettext('Cannot rename an image to a different suffix');
		case 7:
			return gettext('Album delete failed');
		default:
			return sprintf(gettext("There was an error #%d with the rename operation."), $e);
	}
}

function cleanAlbum($obj) {
	global $albumcount;
	$subalbum = $obj->name;
	$file = basename($subalbum);
	$seoname = seoFriendly($file);
	if (!$obj->isDynamic()) {
		$count = checkFolder($obj);
	} else {
		$count = 0;
	}
	if ($seoname != $file) {
		$newname = dirname($subalbum);
		if (empty($newname) || $newname == '.') {
			$newname = $seoname;
		} else {
			$newname .= '/' . $seoname;
		}
		if ($e = $obj->rename($newname)) {
			$error = getE($e, $subalbum, $newname);
			printf(gettext('<em>%1$s</em> rename to <em>%2$s</em> failed: %3$s'), $subalbum, $newname, $error);
			echo "<br />\n";
		} else {
			$obj->save();
			clearstatcache();
			printf(gettext('<em>%1$s</em> renamed to <em>%2$s</em>'), $subalbum, $newname);
			echo "<br />\n";
			$albumcount++;
			$obj = newAlbum($newname);
		}
	}
	if ($count || $seoname != $file) {
		Gallery::clearCache($subalbum);
	}
	return $count;
}

function checkFolder($album) {
	global $albumcount;
	$count = 0;
	$subalbums = $album->getAlbums(0);
	foreach ($subalbums as $subalbum) {
		$obj = newAlbum($subalbum);
		$count = $count + cleanAlbum($obj);
	}
	$oldcount = $count;
	$folder = $album->name . '/';
	$files = $album->getImages(0);
	foreach ($files as $filename) {
		$seoname = seoFriendly($filename);
		if (stripSuffix($seoname) != stripSuffix($filename)) {
			$image = newImage($album, $filename);
			if ($e = $image->rename($seoname)) {
				$error = getE($e, $filename, $seoname);
				printf(gettext('<em>%1$s</em> rename to <em>%2$s</em> failed: %3$s'), $folder . $filename, $seoname, $error);
				echo "<br />\n";
			} else {
				$image->save();
				clearstatcache();
				echo '&nbsp;&nbsp;';
				printf(gettext('<em>%1$s</em> renamed to <em>%2$s</em>'), $folder . $filename, $seoname);
				echo "<br />\n";
				$count++;
			}
		}
	}
	return $count;
}

$_zp_gallery->garbageCollect();

printAdminHeader('admin', 'SEO cleaner');

if (isset($_GET['todo'])) {
	$count = sanitize_numeric($_GET['imagecount']);
	$albumcount = sanitize_numeric($_GET['albumcount']);
	$albums = array();
	foreach (explode(',', sanitize($_GET['todo'])) as $album) {
		$albums[] = sanitize($album);
	}
} else {
	$count = 0;
	$albumcount = 0;
	$albums = $_zp_gallery->getAlbums();
}
?>
<?php echo '</head>'; ?>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'seo_cleanup', ''); ?>
			<h1><?php echo gettext('Cleanup album and image names to be SEO friendly'); ?></h1>
			<div class="tabbox">
				<?php
				foreach ($albums as $album) {
					$obj = newAlbum($album);
					$count = $count + cleanAlbum($obj);
				}
				if ($albumcount || $count) {
					zpFunctions::removeDir(SERVERPATH . '/' . STATIC_CACHE_FOLDER, true);
					?>
					<div class="notebox">
						<p>
							<?php
							if ($albumcount) {
								printf(ngettext('%d album cleaned.', '%d albums cleaned', $albumcount), $albumcount);
							} else {
								echo gettext('No albums cleaned.');
							}
							?>
						</p>
						<p>
							<?php
							if ($count) {
								printf(ngettext('%d image cleaned.', '%d images cleaned', $count), $count);
							} else {
								echo gettext('No images cleaned.');
							}
							?>
						</p>
					</div>
					<?php
				} else {
					?>
					<p class="notebox"><?php echo gettext('No albums or images cleaned up.'); ?></p>
					<?php
				}
				?>
			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
<?php
echo "</html>";
?>
