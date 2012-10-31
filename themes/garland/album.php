<?php
if (!defined('WEBPATH')) die();
$map = function_exists('printGoogleMap');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php
	zp_apply_filter('theme_head');
	$personality = getOption('garland_personality');
	require_once(SERVERPATH.'/'.THEMEFOLDER.'/garland/'.$personality.'/functions.php');
	?>
	<title><?php printGalleryTitle(); ?> | <?php echo html_encode(getAlbumTitle()); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
	<?php $oneImagePage = $personality->theme_head($_zp_themeroot); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
	<?php printRSSHeaderLink('Album',getAlbumTitle()); ?>
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<?php $personality->theme_bodyopen($_zp_themeroot); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title">
						<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo sanitize(getGalleryTitle());?></a>
					</h1>
				</div>
			</div>
		</div><!-- header -->
	<div class="sidebar">
		 <div id="leftsidebar">
		<?php include("sidebar.php"); ?>
		</div>
	</div>
	<div id="center">
		<div id="squeeze">
			<div class="right-corner">
				<div class="left-corner"><!-- begin content -->
					<div class="main section" id="main">
						<h2 id="gallerytitle">
							<?php printHomeLink('',' » '); ?>
							<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle();?></a> » <?php printParentBreadcrumb("", " » ", " » "); ?><?php echo html_encode(getAlbumTitle()); ?>
						</h2>
						<?php printAlbumDesc(); ?>
						<?php printCodeblock(1); ?>
						<div id="albums">
							<?php
							while (next_album()) {
								?>
								<div class="album">
									<a class="albumthumb" href="<?php echo getAlbumLinkURL();?>" title="<?php printf (gettext('View album:  %s'),sanitize(getAlbumTitle())); ?>">
										<?php printCustomAlbumThumbImage(getAlbumTitle(),85,NULL,NULL,85,85); ?>
									</a>
									<div class="albumdesc">
										<h3>
											<a href="<?php echo getAlbumLinkURL();?>" title="<?php printf (gettext('View album:  %s'),sanitize(getAlbumTitle())); ?>">
												<?php printAlbumTitle(); ?>
											</a>
										</h3>
										<br />
										<small><?php printAlbumDate(); ?></small>
									</li>
									</div>
								<p style="clear: both;"></p>
								</div>
								<?php
							}
							?>
							</div>
							<p style="clear: both; "></p>
							<?php $personality->theme_content($map); ?>
							<?php
							if ((getNumAlbums() != 0) || !$oneImagePage){
								printPageListWithNav(gettext("« prev"), gettext("next »"), $oneImagePage);
							}
							if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album);
							@call_user_func('printRating');
							@call_user_func('printCommentForm');
							printCodeblock(2);
							footer();
							?>
					 </div>
					<p style="clear: both;"></p>
					</div>	<!-- end content -->
					<span class="clear"></span>
				</div>
			</div>
		</div>
		<div class="sidebar">
			<div id="rightsidebar">
				<?php
				$nextalbum = getNextAlbum();
				$prevalbum = getPrevAlbum();
				if ($nextalbum || $prevalbum) {
					?>
					<h2><?php echo gettext('Album Navigation'); ?></h2>
					<?php
					if ($nextalbum) {
						?>
						<div id="next" class="slides">
						<a href="<?php echo html_encode(getNextAlbumURL()); ?>" title="<?php echo gettext('Next album'); ?>"><?php echo gettext('Next album »'); ?><br /><img src="<?php echo pathurlencode($nextalbum->getAlbumThumb()); ?>" /></a>
						</div>
						<br />
					<?php
					}
					if ($prevalbum) {
						?>
						<div id="prev" class="slides">
						<a href="<?php echo html_encode(getPrevAlbumURL());?>" title="<?php echo gettext('Prev Album'); ?>"><?php echo gettext('« Prev Album'); ?><br /><img src="<?php echo pathurlencode($prevalbum->getAlbumThumb()); ?>" /></a>
						</div>
						<?php
					}
				}
				?>
				<?php printTags('links', gettext('Tags: '), NULL, ''); ?>
				<?php
				if (!empty($points) && $map) {
					setOption('gmap_display', 'colorbox', false);
					?>
					<div id="map_link">
						<?php
						printGoogleMap(NULL, NULL, NULL, 'album_page', 'gMapOptionsAlbum');
						?>
					</div>
					<br clear="all" />
					<?php
				}
				?>
				<?php
				if (function_exists('printLatestImages')) {
					?>
					<h2><?php printf(gettext('Latest Images for %s'),$_zp_current_album->getTitle()); ?></h2>
					<?php
					printLatestImages(5, $_zp_current_album->name);
				}
				?>
			</div><!-- right sidebar -->
		</div><!-- sidebar -->
	</div><!-- container -->
	<span class="clear"></span>
</div><!-- wrapper -->
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
