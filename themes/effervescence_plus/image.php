<?php
// force UTF-8 Ø
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function() {
					$(".colorbox").colorbox({
						inline: true,
						href: "#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
	<?php
	$disposal = getOption('protect_full_image');
	if ($disposal == 'Unprotected' || $disposal == 'Protected view') {
		?>
						$("a.thickbox").colorbox({
							maxWidth: "98%",
							maxHeight: "98%",
							photo: true,
							close: '<?php echo gettext("close"); ?>'
						});
		<?php
	}
	?>
				});
				// ]]> -->
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', 'Gallery RSS'); ?>
	</head>

	<body onload="blurAnchors()">
		<?php zp_apply_filter('theme_body_open'); ?>

		<!-- Wrap Everything -->
		<div id="main4">
			<div id="main2">

				<!-- Wrap Header -->
				<div id="galleryheader">
					<div id="gallerytitle">

						<!-- Image Navigation -->
						<div class="imgnav">
							<div class="imgprevious">
								<?php
								global $_zp_current_image;
								if (hasPrevImage()) {
									$image = $_zp_current_image->getPrevImage();
									echo '<a href="' . html_encode(getPrevImageURL()) . '" title="' . html_encode($image->getTitle()) . '">« ' . gettext('prev') . '</a>';
								} else {
									echo '<div class="imgdisabledlink">« ' . gettext('prev') . '</div>';
								}
								?>
							</div>
							<div class="imgnext">
								<?php
								if (hasNextImage()) {
									$image = $_zp_current_image->getNextImage();
									echo '<a href="' . html_encode(getNextImageURL()) . '" title="' . html_encode($image->getTitle()) . '">' . gettext('next') . ' »</a>';
								} else {
									echo '<div class="imgdisabledlink">' . gettext('next') . ' »</div>';
								}
								?>
							</div>
						</div>

						<!-- Logo -->
						<div id="logo2">
							<?php printLogo(); ?>
						</div>
					</div>

					<!-- Crumb Trail Navigation -->
					<div id="wrapnav">
						<div id="navbar">
							<span>
								<?php printHomeLink('', ' | '); ?>
								<?php
								if (getOption('custom_index_page') === 'gallery') {
									?>
									<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home'); ?></a> |
									<?php
								}
								?>
								<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
								<?php printParentBreadcrumb();
								printAlbumBreadcrumb("", " | "); ?>
							</span>
							<?php printImageTitle(); ?>
						</div>
					</div>
				</div>

				<!-- The Image -->
				<?php
				$s = getDefaultWidth() + 22;
				$wide = " style=\"width:" . $s . "px;";
				$s = getDefaultHeight() + 22;
				$high = " height:" . $s . "px;\"";
				?>
				<div id="image" <?php echo $wide . $high; ?>>
					<?php
					if (isImagePhoto()) {
						$fullimage = getFullImageURL();
						$imgclass = 'photo';
					} else {
						$fullimage = NULL;
						$imgclass = 'video';
					}
					?>
					<div id="image_container" class="<?php echo $imgclass; ?>">
						<?php
						if (isImagePhoto()) {
							$fullimage = getFullImageURL();
						} else {
							$fullimage = NULL;
						}
						if (!empty($fullimage)) {
							?>
							<a href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php printBareImageTitle(); ?>" class="thickbox">
								<?php
							}
							printDefaultSizedImage(getImageTitle());
							if (!empty($fullimage)) {
								?>
							</a>
							<?php
						}
						?>
					</div>
				</div>
				<br class="clearall" />
			</div>

			<!-- Image Description -->

			<div id="description">
				<p><?php printImageDesc(); ?></p>
				<?php
				@call_user_func('printRating');
				If (function_exists('printAddToFavorites'))
					printAddToFavorites($_zp_current_image);
				if (function_exists('printGoogleMap')) {
					?>
					<div id="map_link">
	<?php printGoogleMap(); ?>
					</div>
					<br class="clearall" />
					<?php
				}
				if (getImageMetaData()) {
					printImageMetadata(NULL, 'colorbox');
				}
				?>
			</div>

		</div>

		<!-- Wrap Bottom Content -->
		<?php
		commonComment();
		printFooter();
		zp_apply_filter('theme_body_close');
		?>

	</body>
</html>
