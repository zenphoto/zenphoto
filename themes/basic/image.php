<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>

		<?php zp_apply_filter('theme_head'); ?>

		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				window.addEventListener('load', function () {
					$(".colorbox").colorbox({
						inline: true,
						href: "#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
					$(".fullimage").colorbox({
						maxWidth: "98%",
						maxHeight: "98%",
						photo: true,
						close: '<?php echo gettext("close"); ?>',
						onComplete: function () {
							$(window).resize(resizeColorBoxImage);
						}
					});
				}, false);
				// ]]> -->
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery')); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="main">
			<div id="gallerytitle">
				<div class="imgnav">
					<?php
					if (hasPrevImage()) {
						?>
						<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></div>
						<?php
					} if (hasNextImage()) {
						?>
						<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></div>
						<?php
					}
					?>
				</div>
				<h2>
					<span>
						<?php printHomeLink('', ' | '); ?>
						<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php printGalleryTitle(); ?></a> |
						<?php
						printParentBreadcrumb("", " | ", " | ");
						printAlbumBreadcrumb("", " | ");
						?>
					</span>
					<?php printImageTitle(); ?>
				</h2>
			</div>
			<!-- The Image -->
			<div id="image">
				<?php
				if (function_exists('printZoomImage') && isImagePhoto()) {
					$size = getOption('image_size') / 5;
					$zoom = floor($size * 3);
					printZoomImage(floor($size * 2), NULL, NULL, 'zoom_window');
					?>
					<span id="zoom_window" style="display:inline-block; height:<?php echo $zoom; ?>px; width:<?php echo $zoom; ?>px;  background-color:lightgray; text-align: center;">
						<p style="padding-top: 45%;"><?php echo gettext('Zoomed image will appear here.'); ?></p>
					</span>
					<?php
				} else {
					?>
					<strong>
						<?php
						if (isImagePhoto()) {
							$fullimage = getFullImageURL();
						} else {
							$fullimage = NULL;
						}

						if (empty($fullimage)) {
							?>
							<a href="<?php echo pathurlencode($fullimage); ?>" title="<?php printBareImageTitle(); ?>" class="fullimage">
								<?php
							}
							if (function_exists('printUserSizeImage') && isImagePhoto()) {
								printUserSizeImage(getImageTitle());
							} else {
								printDefaultSizedImage(getImageTitle());
							}
							if (empty($fullimage)) {
								?>
							</a>
							<?php
						}
						?>
					</strong>
					<?php
					if (isImagePhoto()) {
						@call_user_func('printUserSizeSelector');
					}
				}
				?>
			</div>
			<div id="narrow">
				<?php
				printImageDesc();
				?>
				<hr />
				<br class="clearall" />
				<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ''); ?>
				<br class="clearall" />
				<?php
				If (function_exists('printAddToFavorites')) {
					printAddToFavorites($_zp_current_image);
					?>
					&nbsp;&nbsp;
					<?php
				}
				If (function_exists('printSlideShowLink')) {
					printSlideShowLink(NULL, NULL, '&nbsp;&nbsp;');
				}

				if (function_exists('printGoogleMap')) {
					printGoogleMap();
					?>
					&nbsp;&nbsp;
					<?php
				}
				if (getImageMetaData()) {
					printImageMetadata(NULL, 'colorbox');
				}
				?>
				<br class="clearall" />
				<?php
				@call_user_func('printRating');
				@call_user_func('printCommentForm');
				?>
			</div>
		</div>
		<div id="credit">
			<?php
			if (function_exists('printFavoritesURL')) {
				printFavoritesURL(NULL, '', ' | ', '<br />');
			}
			?>
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> | <?php printSoftwareLink(); ?>
			<?php @call_user_func('printUserLogin_out', " | "); ?>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
