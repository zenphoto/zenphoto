<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo pathurlencode(dirname(dirname($zenCSS))); ?>/common.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script>
				$(document).ready(function() {
					$(".fullimage").colorbox({
						maxWidth: "98%",
						maxHeight: "98%",
						photo: true,
						close: '<?php echo gettext("close"); ?>',
						onComplete: function () {
							$(window).resize(resizeColorBoxImage);
						}
					});
				});
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
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
						<?php
						printHomeLink('', ' | ');
						printGalleryIndexURL(' | ', getGalleryTitle());
						printParentBreadcrumb("", " | ", " | ");
						printAlbumBreadcrumb("", " | ");
						?>
					</span>
<?php printImageTitle(); ?>
				</h2>
			</div>
			<!-- The Image -->
			<div id="image">
				<strong>
					<?php
					if ($_zp_current_image->isPhoto()) {
						$fullimage = getFullImageURL();
					} else {
						$fullimage = NULL;
					}
					if (!empty($fullimage)) {
						?>
						<a href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php printBareImageTitle(); ?>" class="fullimage">
							<?php
						}
						if (function_exists('printUserSizeImage') && $_zp_current_image->isPhoto()) {
							printUserSizeImage(getImageTitle());
						} else {
							printDefaultSizedImage(getImageTitle());
						}
						if (!empty($fullimage)) {
							?>
						</a>
						<?php
					}
					?>
				</strong>
			</div>
			<div id="narrow">
				<?php printImageDesc(); ?>
				<hr /><br />
				<?php
				If (function_exists('printAddToFavorites'))
					printAddToFavorites($_zp_current_image);
				callUserFunction('printSlideShowLink');

				if (getImageMetaData()) {
					printImageMetadata();
					?>
					<br class="clearall" />
					<?php
				}
				printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', '');
				?>
				<br class="clearall" />

				<?php
				callUserFunction('openStreetMap::printOpenStreetMap');
				callUserFunction('printGoogleMap');
				callUserFunction('printRating');
				callUserFunction('printCommentForm');
				?>
			</div>
		</div>
<?php include 'inc-footer.php'; ?>
	</body>
</html>
