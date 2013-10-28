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
		<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function() {
					$(".colorbox").colorbox({
						inline: true,
						href: "#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
					$("a.thickbox").colorbox({
						maxWidth: "98%",
						maxHeight: "98%",
						photo: true,
						close: '<?php echo gettext("close"); ?>'
					});
				});
				// ]]> -->
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Album', getAlbumTitle()); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>

		<div id="main">
			<div id="header">
				<h1><?php printGalleryTitle(); ?></h1>
				<div class="imgnav">
					<?php if (hasPrevImage()) { ?>
						<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></div>
					<?php } if (hasNextImage()) { ?>
						<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></div>
					<?php } ?>
				</div>
			</div>

			<div id="content">

				<div id="breadcrumb">
					<h2><a href="<?php echo getGalleryIndexURL(false); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a> » <?php printParentBreadcrumb("", " » ", " » ");
					printAlbumBreadcrumb("  ", " » "); ?>
						<strong><?php printImageTitle(); ?></strong> (<?php echo imageNumber() . "/" . getNumImages(); ?>)
					</h2>
				</div>
				<div id="content-left">

					<!-- The Image -->
					<?php
					//
					if (function_exists('printjCarouselThumbNav')) {
						printjCarouselThumbNav(6, 50, 50, 50, 50, FALSE);
					} else {
						@call_user_func('printPagedThumbsNav', 6, FALSE, gettext('« prev thumbs'), gettext('next thumbs »'), 40, 40);
					}
					?>

					<div id="image">
						<?php
						if (getOption("Use_thickbox") && !isImageVideo()) {
							$boxclass = " class=\"thickbox\"";
						} else {
							$boxclass = "";
						}
						if (isImagePhoto()) {
							$tburl = getFullImageURL();
						} else {
							$tburl = NULL;
						}
						if (!empty($tburl)) {
							?>
							<a href="<?php echo html_encode(pathurlencode($tburl)); ?>"<?php echo $boxclass; ?> title="<?php printBareImageTitle(); ?>">
								<?php
							}
							printCustomSizedImageMaxSpace(getBareImageTitle(), 580, 580);
							?>
							<?php
							if (!empty($tburl)) {
								?>
							</a>
							<?php
						}
						?>
					</div>
					<div id="narrow">
						<div id="imagedesc"><?php printImageDesc(); ?></div>
						<?php if (getTags()) {
							echo gettext('<strong>Tags:</strong>');
						} printTags('links', '', 'taglist', ', '); ?>
						<br style="clear:both;" /><br />
						<?php
						if (function_exists('printSlideShowLink')) {
							echo '<span id="slideshowlink">';
							printSlideShowLink();
							echo '</span>';
						}
						?>

						<?php
						if (getImageMetaData()) {
							printImageMetadata(NULL, 'colorbox');
						}
						?>

						<br style="clear:both" />
<?php If (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_image); ?>
					<?php @call_user_func('printRating'); ?>
					<?php @call_user_func('printGoogleMap'); ?>

					</div>
<?php @call_user_func('printCommentForm'); ?>

				</div><!-- content-left -->

				<div id="sidebar">
					<?php include("sidebar.php"); ?>
				</div>

				<div id="footer">
<?php include("footer.php"); ?>
				</div>


			</div><!-- content -->

		</div><!-- main -->
<?php
zp_apply_filter('theme_body_close');
?>
	</body>
</html>
