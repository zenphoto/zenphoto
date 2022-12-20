<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
	<head>
		<meta charset="<?php echo LOCAL_CHARSET; ?>">
		<?php zp_apply_filter('theme_head'); ?>
		<?php printHeadTitle(); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script>
				$(document).ready(function() {
	<?php
	$disposal = getOption('protect_full_image');
	if ($disposal == 'unprotected' || $disposal == 'protected') {
		?>
						$("a.thickbox").colorbox({
							maxWidth: "98%",
							maxHeight: "98%",
							photo: true,
							close: '<?php echo gettext("close"); ?>',
							onComplete: function(){
								$(window).resize(resizeColorBoxImage);
							}
						});
		<?php
	}
	?>
				});
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Album', gettext('Gallery RSS')); ?>
	</head>
	<body class="sidebars">
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="navigation"></div>
		<div id="wrapper">
			<div id="container">
				<div id="header">
					<div id="logo-floater">
						<div>
							<h1 class="title">
								<a href="<?php echo html_encode(getSiteHomeURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
							</h1>
							<span id="galleryDescription"><?php printGalleryDesc(); ?></span>
						</div>
					</div>
				</div>
				<!-- header -->
				<div class="sidebar">
					<div id="leftsidebar">
						<?php include("sidebar.php"); ?>
					</div>
				</div>

				<div id="center">
					<div id="squeeze">
						<div class="right-corner">
							<div class="left-corner">
								<!-- begin content -->
								<div class="main section" id="main">
									<h2 id="gallerytitle">
										<?php printHomeLink('', ' » ');
										printGalleryIndexURL(' » ');
										printParentBreadcrumb("", " » ", " » ");
										printAlbumBreadcrumb("  ", " » ");
										?>
										<?php printImageTitle(); ?>
									</h2>
									<?php printCodeblock(1); ?>
									<div id="image_container">
										<?php
										if ($_zp_current_image->isPhoto()) {
											$fullimage = getFullImageURL();
										} else {
											$fullimage = NULL;
										}
										if (!empty($fullimage)) {
											?>
											<a href="<?php echo html_encode(pathurlencode($fullimage)); ?>" title="<?php printBareImageTitle(); ?>" class="thickbox">
												<?php
											}
											printCustomSizedImage(getImageTitle(), null, 520);
											if (!empty($fullimage)) {
												?>
											</a>
											<?php
										}
										?>
									</div>
									<?php
									callUserFunction('openStreetMap::printOpenStreetMap');
									If (function_exists('printAddToFavorites'))
										printAddToFavorites($_zp_current_image);
									callUserFunction('printRating');
									callUserFunction('printCommentForm');
									printCodeblock(2);
									footer();
									?>
									<p style="clear: both;"></p>
								</div>
								<!-- end content -->
								<span class="clear"></span> </div>
						</div>
					</div>
				</div>
				<div class="sidebar">
					<div id="rightsidebar">
						<?php
						if (hasNextImage()) {
							?>
							<div id="nextalbum" class="slides">
								<a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext('Next image'); ?>">
									<h2><?php echo gettext('Next »'); ?></h2>
									<img src="<?php echo html_encode(pathurlencode(getNextImageThumb())); ?>" loading="lazy" />
								</a>
							</div>
							<?php
						}
						if (hasPrevImage()) {
							?>
							<div id="prevalbum" class="slides">
								<a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext('Previous image'); ?>">
									<h2><?php echo gettext('« Previous'); ?></h2>
									<img src="<?php echo html_encode(pathurlencode(getPrevImageThumb())); ?>" loading="lazy" />
								</a>
							</div>
							<?php
						}
						?>
						<p><?php printImageDesc(); ?></p>
						<?php printTags('links', gettext('Tags: '), NULL, ''); ?>
						<?php
						if (getImageMetaData()) {
							printImageMetadata();
							?>
							<br class="clearall" />
							<?php
						}
						if (function_exists('printGoogleMap')) {
							setOption('gmap_display', 'colorbox', false);
							?>
							<span id="map_link">
								<?php printGoogleMap(NULL, NULL, NULL, NULL, 'gMapOptionsImage'); ?>
							</span>
							<br class="clearall" />
							<?php
						}
						?>
					</div>
				</div>
				<span class="clear"></span> </div>
			<!-- /container -->
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
