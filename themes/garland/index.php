<?php
if (!defined('WEBPATH')) die();
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_album_statistics.php');
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/print_album_menu.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php zp_apply_filter('theme_head'); ?>
<title><?php printGalleryTitle(); ?></title>
<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/zen.css"
	type="text/css" />
<?php printRSSHeaderLink('Gallery','Gallery RSS'); ?>
</head>
<body class="sidebars">
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
				<h1 class="title"><?php echo getGalleryTitle(); ?></h1>
				</div>
			</div>
		</div>
	<!-- header -->
	<?php sidebarMenu(); ?>
	<div id="center">
		<div id="squeeze">
			<div class="right-corner">
			<div class="left-corner"><!-- begin content -->
				<div class="main section" id="main">
					<h3 id="gallerytitle"><?php echo getGalleryTitle(); ?></h3>
					<?php
					if (getOption('zp_plugin_zenpage')) {
						commonNewsLoop(false);
					} else {
						?>
						<div id="albums">
						<?php
						while (next_album()) {
							?>
							<div class="album">
								<a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>">
									<?php printAlbumThumbImage(getAlbumTitle()); ?>
								</a>
								<div class="albumdesc"><small><?php printAlbumDate("Date Taken: "); ?></small>
									<h3>
										<a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>">
											<?php printAlbumTitle(); ?>
										</a>
									</h3>
									<p><?php printAlbumDesc(); ?></p>
								</div>
							<p style="clear: both;"></p>
							</div>
							<?php
						}
						printPageListWithNav("&laquo; prev", "next &raquo;");
						?>
						</div>
						<?php
					}
					?>
					<?php footer(); ?></div>
					<div style="clear: both;"></div>
				</div>
			<!-- end content --> <span class="clear"></span>
			</div>
		</div>
	</div>
	<div class="sidebar">
		<div id="rightsidebar">
			<h2>Latest Images</h2>
			<?php printLatestImages(7) ?></div>
		</div>
		<span class="clear"></span>
	</div>
</div>
<?php printAdminToolbox(); ?>
</body>
</html>
