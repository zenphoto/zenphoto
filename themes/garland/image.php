<?php
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo html_encode(getAlbumTitle()); ?> | <?php echo html_encode(getImageTitle()); ?></title>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
	<script type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function(){
			$(".colorbox").colorbox({inline:true, href:"#imagemetadata"});
		});
		// ]]> -->
	</script>
	<?php printRSSHeaderLink('Album',gettext('Gallery RSS')); ?>
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a></h1>
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
							<h3 id="gallerytitle">
									<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo getGalleryTitle();?></a> &raquo; <?php printParentBreadcrumb("", " &raquo; ", " &raquo; "); ?><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php gettext('Album Thumbnails'); ?>"><?php echo html_encode(getAlbumTitle()); ?></a> &raquo; <?php printImageTitle(true); ?>
							</h3>
							<div id="image_container">
							<?php
							$fullimage = getFullImageURL();
							if (!empty($fullimage)) {
								?>
								<a href="<?php echo html_encode($fullimage);?>" title="<?php echo getBareImageTitle();?>">
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
									if (getImageMetaData()) {
										?>
											<span id="exif_link">
												<a href="#" title="<?php echo gettext("Image Info"); ?>" class="colorbox"><?php echo gettext("Image Info"); ?></a>
											</span>
											<span style="display:none">
												<?php echo printImageMetadata('', false); ?>
											</span>
											<br clear="all" />
										<?php
									}
									if (function_exists('hasMapData') && hasMapData()) {
										?>
										<span id="map_link">
											<?php printGoogleMap(NULL,NULL,NULL,NULL,'gMapOptionsImage'); ?>
										</span>
										<?php
									}
									?>
							<p><?php printImageDesc(true); ?></p>
							<?php if (function_exists('printRating')) printRating(); ?>
							<?php
							if (function_exists('printCommentForm')) {
								printCommentForm();
							}
							?>
							<?php footer(); ?>
							<div style="clear: both;"></div>
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
					<div id="next" class="slides">
						<h2><?php echo gettext('Next &raquo;'); ?></h2>
						<a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext('Next photo'); ?>"><img src="<?php echo html_encode(getNextImageThumb()); ?>" /></a>
					</div>
					<?php
				}
				if (hasPrevImage()) {
					?>
					<div id="prev" class="slides">
						<h2><?php echo gettext('&laquo; Previous'); ?></h2>
						<a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext('Previous photo'); ?>"><img src="<?php echo html_encode(getPrevImageThumb()); ?>" /></a>
					</div>
					<?php
				}
				?>
				<?php printTags(true, gettext('Tags: ')); ?>
			</div>
		</div>
		<span class="clear"></span> </div>
	<!-- /container -->
</div>
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
