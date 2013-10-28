<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php
		zp_apply_filter('theme_head');
		if (getOption('effervescence_daily_album_image_effect') && getOption('custom_index_page') != 'gallery') {
			setOption('image_custom_images', getOption('effervescence_daily_album_image_effect'), false);
		}
		?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', 'Gallery RSS'); ?>
	</head>

	<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

		<!-- Wrap Header -->
		<div id="header">

			<!-- Logo -->
			<div id="gallerytitle">
				<div id="logo">
					<?php
					if (getOption('Allow_search')) {
						$album_list = array('albums' => '1', 'pages'	 => '0', 'news'	 => '0');
						printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search albums'), NULL, NULL, $album_list);
					}
					printLogo();
					?>
				</div>
			</div> <!-- gallerytitle -->

			<!-- Crumb Trail Navigation -->
			<div id="wrapnav">
				<div id="navbar">
					<span><?php printHomeLink('', ' | '); ?>
						<?php
						if (getOption('custom_index_page') === 'gallery') {
							?>
							<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> |
							<?php
						}
						printGalleryTitle();
						?>
					</span>
				</div>
			</div> <!-- wrapnav -->
		</div> <!-- header -->
		<!-- Random Image -->
		<?php
		printHeadingImage(getRandomImages(getThemeOption('effervescence_daily_album_image')));
		?>

		<!-- Wrap Main Body -->
		<div id="content">
			<div id="main">

				<!-- Album List -->
				<ul id="albums">
					<?php
					$firstAlbum = null;
					$lastAlbum = null;
					while (next_album()) {
						if (is_null($firstAlbum)) {
							$lastAlbum = albumNumber();
							$firstAlbum = $lastAlbum;
						} else {
							$lastAlbum++;
						}
						?>
						<li>
									<?php $annotate = annotateAlbum(); ?>
							<div class="imagethumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo $annotate; ?>">
	<?php printCustomAlbumThumbImage($annotate, null, ALBUM_THMB_WIDTH, null, ALBUM_THMB_WIDTH, ALBUM_THUMB_HEIGHT); ?>
								</a>
							</div>
							<h4><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo $annotate; ?>"><?php printAlbumTitle(); ?></a></h4>
						</li>
				<?php } ?>
				</ul>
				<div class="clearage"></div>
<?php printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums()); ?>

			</div> <!-- main -->
			<!-- Page Numbers -->
			<div id="pagenumbers">
<?php printPageListWithNav("« " . gettext('prev'), gettext('next') . " »"); ?>
			</div>
		</div> <!-- content -->

		<br style="clear:all" />

		<?php
		printFooter();
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>