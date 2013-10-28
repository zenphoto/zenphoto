<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();
require_once('normalizer.php');
 ?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
	<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
</head>

<body class="gallery">
	<?php
	zp_apply_filter('theme_body_open');
	$anumber = getNumAlbums();
	$inumber = getNumImages();
	if ($anumber+$inumber == 0) {
		$_zp_current_search->clearSearchWords();
	}
	?>
	<?php printGalleryTitle(); ?>
	<?php if (getOption('Allow_search')) {  printSearchForm(); } ?>

	<div id="content">

		<div class="galleryinfo">
			<h1>
			<?php printSearchBreadcrumb(NULL, NULL, ''); ?>
			</h1>
		</div>
		<?php
		$results = getNumAlbums() + getNumImages();
		?>
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
					<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext('View album: %s'), html_encode(getAnnotatedAlbumTitle()));?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 210, null, 210, 59); ?></a>
					<h3><a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext('View album: %s'), html_encode(getAnnotatedAlbumTitle()));?>"><?php printAlbumTitle(); ?></a></h3>
					<p>
						<?php
						if ($anumber > 0 || $inumber > 0) {
							echo '<p><em>(';
							if ($anumber == 0 && $inumber == 1) {
								printf(gettext('1 image'));
							} else if ($anumber == 0 && $inumber > 1) {
								printf(gettext('%u images'), $inumber);
							} else if ($anumber == 1 && $inumber == 1) {
								printf(gettext('1 album,&nbsp;1 image'));
							} else if ($anumber > 1 && $inumber == 1) {
								printf(gettext('%u albums,&nbsp;1 image'), $anumber);
							} else if ($anumber > 1 && $inumber > 1) {
								printf(gettext('%1$u albums,&nbsp;%2$u images'), $anumber, $inumber);
							} else if ($anumber == 1 && $inumber == 0) {
								printf(gettext('1 album'));
							} else if ($anumber > 1 && $inumber == 0) {
								printf(gettext('%u albums'),$anumber);
							} else if ($anumber == 1 && $inumber > 1) {
								printf(gettext('1 album,&nbsp;%u images'), $inumber);
							}
							echo ')</em><br />';
						}
							$text = getAlbumDesc();
							if(strlen($text) > 50) {
							$text = preg_replace("/[^ ]*$/", '', sanitize(substr($text, 0, 50),1))."...";
						}
						echo $text;
						?>
					</p>
				</li>
			<?php
			}
			if (!$first) { echo "\n</ul>\n</div>\n"; }
			?>

	<ul class="slideset" style="width:<?php echo getOption('images_per_row')*133; ?>px;">
		<?php
		$firstImage = null;
		$lastImage = null;
		if ($myimagepage > 1) {
		?>
		<li class="thumb"><span class="backward"><em style="background-image:url('<?php echo $_zp_themeroot ?>/images/moreslide_prev.gif');"><a href="<?php echo html_encode(getPrevPageURL()); ?>" style="background:#fff;"><?php echo gettext('Next page'); ?></a></em></span></li>
		<?php
		}
		while (next_image()) {
			if (is_null($firstImage)) {
				$lastImage = imageNumber();
				$firstImage = $lastImage;
			} else {
				$lastImage++;
			}
			if (isLandscape()) {
				$iw = 89;
				$ih = NULL;
				$cw = 89;
				$ch = 67;
			} else {
				$iw = NULL;
				$ih = 89;
				$ch = 89;
				$cw = 67;
			}
			echo "\n<li class=\"thumb\"><span><em style=\"background-image:url(" . html_encode($_zp_current_image->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)) . '); "><a href="' .
			html_encode(getImageLinkURL()) . '" title="' . html_encode(getAnnotatedImageTitle()) . '" style="background:#fff;">"'.
			getImageTitle().'"</a></em></span></li>';
		}
			if (!is_null($lastImage)  && $lastImage < getNumImages()) {
				$np = getCurrentPage()+1;
			?>
			<li class="thumb"><span class="forward"><em style="background-image:url('<?php echo $_zp_themeroot ?>/images/moreslide_next.gif');">
			<a href="<?php echo html_encode(getPageURL($np, $np)); ?>" style="background:#fff;"><?php echo gettext('Next page'); ?></a></em></span></li>
		<?php
		}
		?>
	</ul>

	<div class="galleryinfo">
		<?php
		$params = $_zp_current_search->getSearchParams();
		if (!empty($params)) {
			if ($results != "0") {
				if ($firstImage + $lastImage != 0) {
					echo '<em class="count">';
					printf( gettext('Photos %1$u-%2$u of %3$u'), $firstImage, $lastImage, getNumImages());
					echo "</em>";
					@call_user_func('printSlideShowLink');
				}
			}
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
			echo '</p>';
			echo "<em class=\"count\">"  .sprintf(gettext('Total matches for <em>%1$s</em>: %2$u'),html_encode(getSearchWords()), $results);
		} else {
			echo "<p>".gettext('Sorry, no matches found. Try refining your search.')."</p>";
		}
		?>
	</div>
	</div>

	<p id="path">
		<?php printHomeLink('', ' > '); ?>
		<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
		<?php printGalleryTitle();?></a> &gt;
		<?php
		echo "<em>".gettext('Search')."</em>";
		?>
	</p>

	<div id="footer">
		<hr />
		<?php
		if (function_exists('printFavoritesLink')) {
			printFavoritesLink();
		}
		if (function_exists('printUserLogin_out')) { printUserLogin_out(""); }
		?>
		<p>
		<?php printZenphotoLink(); ?>
		</p>
	</div>
	<?php
	zp_apply_filter('theme_body_close');
	?>
</body>
</html>
