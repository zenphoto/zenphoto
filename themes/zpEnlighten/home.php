<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printZDRoundedCornerJS(); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>
<body>
	<?php zp_apply_filter('theme_body_open'); ?>

	<div id="main" class="home">

		<?php include("header.php"); ?>
		<div id="content">

			<div id="breadcrumb">
				<h2><strong><?php echo gettext('Index'); ?></strong></h2>
			</div>

			<div id="content-left">
			<!-- div class="gallerydesc"><?php /* printGalleryDesc(true); */ ?></div -->
				<h3 class="searchheader">Latest sins</h3>
				<div id="albums" style="margin-left: 4px;">
					<?php
					$latestImages = Utils::getLatestImages(4);
					$u = 0;
					foreach ($latestImages as $i) : $u++;
						?>
						<div class="album" <?php
						if ($u % 2 == 0) {
							echo 'style="margin-left: 8px;"';
						}
						?> >
							<div class="thumb">
								<?php
								$thumb = $i->getCustomImage(NULL, 255, 75, 255, 75, NULL, NULL, false, false);
								$link = $i->getLink();
								$date = $i->getDateTime();
								if ($date) {
									$date = strftime("%d %B %Y", strtotime($date));
								}
								echo "<a href='$link'><img src='$thumb' width='255' height='75'/></a>";
								?>
							</div>
							<div class="albumdesc">
								<?php
								$_zp_current_album = $i->getAlbum();
								?>
								<h3><span style="color: #999;">Album:</span> <a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo $_zp_current_album->getTitle(); ?>"><?php echo $_zp_current_album->getTitle(); ?></a></h3>
								<h3 class="date"><?= $date; ?></h3>
							</div>
						</div>
					<?php endforeach ?>
					<?php
					$_zp_current_album = NULL;
					?>
				</div>
				<br style="clear:both;" /><br />
				<h3 class="searchheader" >Latest words</h3>
				<?php
				$ln = getLatestNews(3);

				foreach ($ln as $n) :
					$_zp_current_article = newArticle($n['titlelink']);
					?>


					<div class="newsarticlewrapper"><div class="newsarticle" style="border-width: 0;">
							<h3><?php printNewsURL(); ?></h3>
							<div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate(); ?> | <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span>
								<?php
								if (is_NewsArticle()) {
									echo ' | ';
									printNewsCategories(", ", gettext("Categories: "), "newscategories");
								}
								?>
							</div>
							<?php printNewsContent(); ?>
							<?php printCodeblock(1); ?>
							<br style="clear:both; " />
						</div>
					</div>
				<?php endforeach; ?>

				<br style="clear:both;" />


			</div><!-- content left-->



			<div id="sidebar">
				<?php include("sidebar.php"); ?>
			</div><!-- sidebar -->



			<div id="footer">
				<?php include("footer.php"); ?>
			</div>

		</div><!-- content -->

	</div><!-- main -->
	<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>