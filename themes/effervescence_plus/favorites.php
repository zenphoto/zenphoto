<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();

$map = function_exists('printGoogleMap');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	</head>

	<body onload="blurAnchors()">
<?php zp_apply_filter('theme_body_open'); ?>

		<!-- Wrap Header -->
		<div id="header">
			<div id="gallerytitle">

				<!-- Subalbum Navigation -->
				<div class="albnav">
					<div class="albprevious">
						<?php
						$album = getPrevAlbum();
						if (is_null($album)) {
							echo '<div class="albdisabledlink">«  ' . gettext('prev') . '</div>';
						} else {
							echo '<a href="' . $album->getAlbumLink() .
							'" title="' . html_encode($album->getTitle()) . '">« ' . gettext('prev') . '</a>';
						}
						?>
					</div> <!-- albprevious -->
					<div class="albnext">
						<?php
						$album = getNextAlbum();
						if (is_null($album)) {
							echo '<div class="albdisabledlink">' . gettext('next') . ' »</div>';
						} else {
							echo '<a href="' . $album->getAlbumLink() .
							'" title="' . html_encode($album->getTitle()) . '">' . gettext('next') . ' »</a>';
						}
						?>
					</div><!-- albnext -->
					<?php
					if (getOption('Allow_search')) {
						$album_list = array('albums' => array($_zp_current_album->name), 'pages'	 => '0', 'news'	 => '0');
						printSearchForm(NULL, 'search', $_zp_themeroot . '/images/search.png', gettext('Search within album'), NULL, NULL, $album_list);
					}
					?>
				</div> <!-- header -->

				<!-- Logo -->
				<div id="logo">
					<?php
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
						?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
<?php printParentBreadcrumb(); ?></span>
<?php printAlbumTitle(); ?>
				</div>
			</div> <!-- wrapnav -->

		</div> <!-- header -->

		<!-- Wrap Subalbums -->
		<div id="subcontent">
			<div id="submain">

				<!-- Album Description -->
				<div id="description">
					<?php
					printAlbumDesc();
					?>
				</div>

				<!-- SubAlbum List -->

				<?php
				$firstAlbum = null;
				$lastAlbum = null;
				while (next_album()) {
					if (is_null($firstAlbum)) {
						$lastAlbum = albumNumber();
						$firstAlbum = $lastAlbum;
						?>
						<ul id="albums">
							<?php
						} else {
							$lastAlbum++;
						}
						?>
						<li>
									<?php $annotate = annotateAlbum(); ?>
							<div class="imagethumb">
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo html_encode($annotate) ?>">
	<?php printCustomAlbumThumbImage($annotate, null, ALBUM_THMB_WIDTH, null, ALBUM_THMB_WIDTH, ALBUM_THUMB_HEIGHT); ?></a>
							</div>
							<h4>
								<a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo html_encode($annotate) ?>">
								<?php printAlbumTitle(); ?>
								</a>
						<?php printAddToFavorites($_zp_current_album, '', gettext('Remove')); ?>
							</h4>
						</li>
						<?php
					}
					if (!is_null($firstAlbum)) {
						?>
					</ul>
					<?php
				}
				?>

				<div class="clearage"></div>
				<?php
				printNofM('Album', $firstAlbum, $lastAlbum, getNumAlbums());
				?>
			</div> <!-- submain -->

			<!-- Wrap Main Body -->
			<?php
			if (getNumImages() > 0) { /* Only print if we have images. */
				?>
				<!-- Image page section -->
				<div id="content">
					<div id="main">
						<div id="images">
							<?php
							$points = array();
							$firstImage = null;
							$lastImage = null;
							while (next_image()) {
								if (is_null($firstImage)) {
									$lastImage = imageNumber();
									$firstImage = $lastImage;
								} else {
									$lastImage++;
								}
								?>
								<div class="image">
									<div class="imagethumb">
										<?php
										if ($map) {
											$coord = getGeoCoord($_zp_current_image);
											if ($coord) {
												$points[] = $coord;
											}
										}
										$annotate = annotateImage();
										echo '<a href="' . html_encode(getImageLinkURL()) . '"';
										echo " title=\"" . $annotate . "\">\n";
										printImageThumb($annotate);
										echo "</a>";
										printAddToFavorites($_zp_current_image, '', gettext('Remove'));
										?>
									</div>
								</div>
								<?php
							}
							echo '<div class="clearage"></div>';
							?>
						</div><!-- images -->
					</div> <!-- main -->
					<div class="clearage"></div>
					<span style="text-align:center"><?php @call_user_func('printSlideShowLink'); ?></span>
				<?php if (isset($firstImage)) printNofM('Photo', $firstImage, $lastImage, getNumImages()); ?>
				</div> <!-- content -->
				<?php
			} else { /* no images to display */
				if (getNumAlbums() == 0) {
					?>
					<div id="main3">
						<div id="main2">
							<br />
							<p align="center"><?php echo gettext('Album is empty'); ?></p>
						</div>
					</div> <!-- main3 -->
					<?php
				}
			}
			?>

			<!-- Page Numbers -->
			<div id="pagenumbers">
				<?php
				if ((getNumAlbums() != 0) || !$_oneImagePage) {
					printPageListWithNav("« " . gettext('prev'), gettext('next') . " »", $_oneImagePage);
				}
				?>
			</div> <!-- pagenumbers -->
<?php commonComment(); ?>
		</div> <!-- subcontent -->

		<!-- Footer -->
		<br style="clear:all" />

		<?php
		printFooter();
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>
