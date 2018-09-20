<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printZDRoundedCornerJS(); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<script type="text/javascript">
		// <!-- <![CDATA[
		window.addEventListener('load', function () {
			$(".colorbox").colorbox({
				inline: true,
				href: "#imagemetadata",
				close: '<?php echo gettext("close"); ?>'
			});
			$("a.thickbox").colorbox({
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

	<?php if (extensionEnabled('rss')) printRSSHeaderLink('Album', getAlbumTitle()); ?>
</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>

	<div style="margin-top: 16px;"><!-- somehow the thickbox.css kills the top margin here that all other pages have... -->
	</div>
	<div id="main">
		<div id="header">
			<h3 style="float:left; padding-left: 32px;">
        <a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/banner.png"/></a>
			</h3>
			<div class="imgnav" style="margin-top: 33px;">
				<?php if (hasPrevImage()) { ?>
					<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></div>
				<?php } else { ?>
					<div class="imgprevious disabled"><a>« <?php echo gettext("prev"); ?></a></div>
				<?php } if (hasNextImage()) { ?>
					<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></div>
				<?php } else { ?>
					<div class="imgnext disabled"><a><?php echo gettext("next"); ?> »</a></div>
				<?php } ?>
			</div>
		</div>

		<div id="content">

			<div id="breadcrumb">
				<h2>
					<?php if (extensionEnabled('zenpage')) { ?>
						<a href="<?php echo getGalleryIndexURL(); ?>" title="<?php gettext('Index'); ?>"><?php echo gettext("Index"); ?></a>»
					<?php } ?>
					<a href="<?php echo html_encode(getCustomPageURl('gallery')); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery") . " » "; ?></a><?php
					printParentBreadcrumb(" » ", " » ", " » ");
					printAlbumBreadcrumb(" ", " » ");
					?>
					<strong><?php /* printImageTitle(true); */ ?><?php echo gettext("Image") . " " . imageNumber() . "/" . getNumImages(); ?></strong>
				</h2>
			</div>
			<div id="content-left">

				<!-- The Image -->
				<?php
				//
				if (function_exists('printThumbNav')) {
					printThumbNav(3, 6, 50, 50, 50, 50, FALSE);
				} else {
					@call_user_func('printPagedThumbsNav', 6, FALSE, gettext('« prev thumbs'), gettext('next thumbs »'), 40, 40);
				}
				?>

				<div id="image">
					<?php
					if (getOption("Use_thickbox")) {
						$boxclass = " class=\"thickbox\"";
						$tburl = getUnprotectedImageURL();
					} else {
						$boxclass = "";
						$tburl = getFullImageURL();
					}
					if (!empty($tburl)) {
						?>
						<a href="<?php echo htmlspecialchars($tburl); ?>"<?php echo $boxclass; ?> title="<?php echo html_encode(getBareImageTitle()); ?>">
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
					<div style="text-align:center; font-weight: bold; color: #999;"><?php printImageTitle(true); ?></div>
					<?php
					$d = getImageDesc();
					if (!empty($d)) {
						?> <div class="imagedesc"><?php printImageDesc(true); ?></div> <?php } ?>
					<?php /* printTags('links', gettext('<strong>Tags:</strong>').' ', 'taglist', ', '); */ ?>
					<br style="clear:both;" /><br />
					<?php
					if (function_exists('printAddToFavorites')) {
						printAddToFavorites($_zp_current_image);
						echo '<br/>';
					}
					?>
					<?php
					if (function_exists('printSlideShowLink')) {
						echo '<span id="slideshowlink">';
						printSlideShowLink(gettext('View Slideshow'));
						echo '</span>';
					}
					?>

					<?php
					if (getImageMetaData()) {
						echo "<div id=\"exif_link\"><a href=\"#\" title=\"" . gettext("Image Info") . "\" class=\"colorbox\">" . gettext("Image Info") . "</a></div>";
						echo "<div style='display:none'>";
						printImageMetadata('', false);
						echo "</div>";
					}
					?>

					<br style="clear:both" />
					<?php
					if (function_exists('printRating')) {
						printRating();
					}
					?>
					<?php if (function_exists('printGoogleMap')) printGoogleMap(); ?>

				</div>
				<?php if (function_exists('printCommentForm')) { ?>
					<div id="comments">
						<?php printCommentForm(); ?>
					</div>
				<?php } ?>

			</div><!-- content-left -->

			<div id="sidebar">
				<?php include("sidebar.php"); ?>
			</div>

			<div id="footer">
				<?php include("footer.php"); ?>
			</div>


		</div><!-- content -->

	</div><!-- main -->
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>