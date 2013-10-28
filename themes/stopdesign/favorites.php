<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
require_once('normalizer.php');
$thisalbum = $_zp_current_album;
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
		<?php
		if (class_exists('RSS'))
			printRSSHeaderLink('Album', getAlbumTitle());
		if (function_exists('getCommentErrors') && getCommentErrors()) {
			$errors = 1;
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot ?>/css/comments-show.css" />
			<?php
		} else {
			$errors = 0;
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot ?>/css/comments-hide.css" />
			<?php
		}
		?>
	</head>

	<body class="gallery">
		<?php zp_apply_filter('theme_body_open'); ?>
		<?php
		printGalleryTitle();
		if (getOption('Allow_search')) {
			printSearchForm();
		}
		?>

		<div id="content">

			<div class="galleryinfo">
				<h1><?php printAlbumTitle(); ?></h1>
				<div class="desc"><?php printAlbumDesc(); ?></div>
			</div>

			<?php
			$first = true;
			while (next_album()) {
				if ($first) {
					echo '<div class="galleries">';
					echo "\n<h2></h2>\n<ul>\n";
					$first = false;
				}
				?>
				<li class="gal">
					<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:') . ' ';
					 printAnnotatedAlbumTitle();
					 ?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, ALBUM_THUMB_WIDTH, ALBUM_THUMB_HEIGHT, ALBUM_THUMB_WIDTH, ALBUM_THUMB_HEIGHT); ?></a>
						<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
					<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:') . ' ';
					printAnnotatedAlbumTitle();
						?>"><?php printAlbumTitle(); ?></a></h3>
					<p>
						<?php
						$anumber = getNumAlbums();
						$inumber = getNumImages();
						if ($anumber > 0 || $inumber > 0) {
							echo '<p><em>(';
							if ($anumber == 0 && $inumber == 1) {
								printf(gettext('1 image'));
							} else if ($anumber == 0 && $inumber > 1) {
								printf(gettext('%u images'), $inumber);
							} else if ($anumber == 1 && $inumber == 1) {
								printf(gettext('1 album,&nbsp;1 image'));
							} else if ($anumber > 1 && $inumber == 1) {
								printf(gettext('%u album,&nbsp;1 image'), $anumber);
							} else if ($anumber > 1 && $inumber > 1) {
								printf(gettext('%1$u album,&nbsp;%2$u images'), $anumber, $inumber);
							} else if ($anumber == 1 && $inumber == 0) {
								printf(gettext('1 album'));
							} else if ($anumber > 1 && $inumber == 0) {
								printf(gettext('%u album'), $anumber);
							} else if ($anumber == 1 && $inumber > 1) {
								printf(gettext('1 album,&nbsp;%u images'), $inumber);
							}
							echo ')</em><br />';
						}
						$text = getAlbumDesc();
						if (strlen($text) > 50) {
							$text = preg_replace("/[^ ]*$/", '', sanitize(substr($text, 0, 50)), 1) . "...";
						}
						echo $text;
						?>
					</p>
				</li>
				<?php
			}
			if (!$first) {
				echo "\n</ul>\n</div>\n";
			}
			?>
			<!--getOption('images_per_row')*133 should have the *133 changed if you are looking to fit more on a
				line or less, as this will decide much of your spacing. This is due to StopDesign not
				respecting the options->theme choices in the admin section.-->
			<ul class="slideset" style="width:<?php echo getOption('images_per_row') * 133; ?>px;">
				<?php
				$firstImage = null;
				$lastImage = null;
				while (next_image()) {
					if (is_null($firstImage)) {
						$lastImage = imageNumber();
						$firstImage = $lastImage;
					} else {
						$lastImage++;
					}
					if (isLandscape()) { //Beware if adjusting these without expected results that you must also adjust the CSS container.
						$iw = 89; //image width
						$ih = NULL; //image height
						$cw = 89; //cropped width
						$ch = 67; //cropped height
					} else {
						$iw = NULL;
						$ih = 89;
						$ch = 89;
						$cw = 67;
					}
					echo
					'<li class="thumb">
			<span><em style="background-image:url(' . html_encode($_zp_current_image->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)) . '); ">
			<a href="' . html_encode(getImageLinkURL()) . '" title="' . html_encode(getAnnotatedImageTitle()) . '" style="background:#fff;">"' . getImageTitle() . '"</a></em>
			</span>';
					printAddToFavorites($_zp_current_image, '', gettext('Remove'));
					echo '</li>';
				}
				?>
			</ul>

			<div class="galleryinfo">
				<br />
				<p>
					<?php
					if (!is_null($firstImage)) {
						echo '<em class="count">';
						printf(gettext('images %1$u-%2$u of %3$u'), $firstImage, $lastImage, getNumImages());
						echo "</em>";
					}
					$current = getCurrentPage();
					$total = max(1, getTotalPages());
					$nav = getPageNavList(false, 2, true, $current, $total);
					if (hasPrevPage()) {
						?>
						<a href="<?php echo html_encode(getPrevPageURL()); ?>" accesskey="x">« <?php echo gettext('prev page'); ?></a>
						<?php
					}
					if (hasNextPage()) {
						if (hasPrevPage()) {
							echo '&nbsp;';
						}
						?>
						<a href="<?php echo html_encode(getNextPageURL()); ?>" accesskey="x"><?php echo gettext('next page'); ?> »</a>
					<?php
				}
				?>
				</p>
<?php
if (function_exists('printUserLogin_out')) {
	printUserLogin_out("");
}
?>
			</div>
		</div>

		<p id="path">
			<?php printHomeLink('', ' > '); ?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> &gt;
			<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> &gt; <?php printParentBreadcrumb("", " > ", " > "); ?> <?php printAlbumTitle(); ?>
		</p>

		<div class="main">
			<?php @call_user_func('printSlideShowLink'); ?>
<?php if (function_exists('printGoogleMap')) printGoogleMap(NULL, NULL, NULL, $thisalbum); ?>
<?php
if (function_exists('printCommentForm')) {
	require_once('comment.php');
}
?>
		</div>
		<div id="footer">
			<hr />
			<p>
<?php printZenphotoLink(); ?>
			</p>
		</div>
<?php
zp_apply_filter('theme_body_close');
?>
	</body>
</html>
