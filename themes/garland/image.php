<?php
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo html_encode(getAlbumTitle()); ?> | <?php echo html_encode(getImageTitle()); ?></title>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
	<?php if(zp_has_filter('theme_head','colorbox::css')) { ?>
		<script type="text/javascript">
			// <!-- <![CDATA[
			$(document).ready(function(){
				$(".colorbox").colorbox({
					inline:true,
					href:"#imagemetadata",
					close: '<?php echo gettext("close"); ?>'
				});
				<?php
				$disposal = getOption('protect_full_image');
				if ($disposal == 'Unprotected' || $disposal == 'Protected view') {
					?>
					$("a.thickbox").colorbox({
						maxWidth:"98%",
						maxHeight:"98%",
						photo:true,
						close: '<?php echo gettext("close"); ?>'
					});
					<?php
				}
				?>
			});
			// ]]> -->
		</script>
	<?php } ?>
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
							<h2 id="gallerytitle">
								<?php printHomeLink('',' » '); ?>
								<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle();?></a> »
												<?php printParentBreadcrumb("", " » ", " » "); printAlbumBreadcrumb("  ", " » "); ?>
												<?php printImageTitle(true); ?>
							</h2>
							<?php printCodeblock(1); ?>
							<div id="image_container">
							<?php
							$fullimage = getFullImageURL();
							if (!empty($fullimage)) {
								?>
								<a href="<?php echo html_encode($fullimage);?>" title="<?php printBareImageTitle();?>" class="thickbox">
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
							If (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_image);
							@call_user_func('printRating');
							@call_user_func('printCommentForm');
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
					<div id="next" class="slides">
						<a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext('Next image'); ?>">
							<h2><?php echo gettext('Next »'); ?></h2>
							<img src="<?php echo pathurlencode(getNextImageThumb()); ?>" />
						</a>
					</div>
					<?php
				}
				if (hasPrevImage()) {
					?>
					<div id="prev" class="slides">
						<a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext('Previous image'); ?>">
							<h2><?php echo gettext('« Previous'); ?></h2>
							<img src="<?php echo pathurlencode(getPrevImageThumb()); ?>" />
						</a>
					</div>
					<?php
				}
				?>
				<p><?php printImageDesc(true); ?></p>
				<?php printTags('links', gettext('Tags: '), NULL, ''); ?>
				<?php
				if (getImageMetaData()) {
					echo printImageMetadata(NULL, 'colorbox');
					?>
					<br clear="all" />
					<?php
				}
				if (function_exists('hasMapData') && hasMapData()) {
					setOption('gmap_display', 'colorbox', false);
					?>
					<span id="map_link">
						<?php printGoogleMap(NULL,NULL,NULL,NULL,'gMapOptionsImage'); ?>
					</span>
					<br clear="all" />
					<?php
				}
				?>
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
