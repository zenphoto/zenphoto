<?php
if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
	<?php printRSSHeaderLink("Gallery", gettext('Gallery RSS')); ?>
	<?php printZDRoundedCornerJS(); ?>
</head>

<body>
	<?php zp_apply_filter('theme_body_open'); ?>

	<div id="main">

		<?php include("header.php"); ?>

		<div id="content">

			<div id="breadcrumb">
				<h2>
					<?php if (is_NewsArticle()) { ?>
						<a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext("Index"); ?></a> <?php printNewsIndexURL("News", " » "); ?><strong><?php printCurrentNewsCategory(" » Category - "); ?><?php
							printNewsTitle(" » ");
							printCurrentNewsArchive(" » ");
							?></strong>
					<?php } else { ?>
						<a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext("Index"); ?></a> » <strong><?php echo gettext("News"); ?></strong>
					<?php } ?>
				</h2>
			</div>

			<div id="content-left">

				<?php printNewsPageListWithNav(gettext('next »'), gettext('« prev')); ?>
				<?php
				// single news article
				if (is_NewsArticle()) {
					?>

					<?php if (getPrevNewsURL()) { ?><div class="singlenews_prev"><?php printPrevNewsLink(); ?></div><?php } ?>
					<?php if (getNextNewsURL()) { ?><div class="singlenews_next"><?php printNextNewsLink(); ?></div><?php } ?>
					<?php if (getPrevNewsURL() OR getNextNewsURL()) { ?><br style="clear:both" /><?php } ?>
					<div class="newsarticlewrapper" style="margin-top: 1em; padding-bottom:0.4em;"><div class="newsarticle">
							<h3 style="color: #82996F;"><?php printNewsTitle(); ?></h3>
							<div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate(); ?> | <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> | </span> <?php printNewsCategories(", ", gettext("Categories: "), "newscategories"); ?></div>
							<?php printNewsContent(); ?>
						</div></div>
					<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', '); ?>
					<br style="clear:both;" /><br />
					<?php
					if (function_exists('printRating')) {
						printRating();
					}
					?>
					<?php
					// COMMENTS TEST
					if (function_exists('printCommentForm')) {
						?>
						<div id="comments">
							<?php printCommentForm(); ?>
						</div>
						<?php
					} // comments allowed - end
				} else {
					/* echo "<hr />";	 */
					// news article loop
					echo '<div class="newsarticlewrapper">';
					$u = 0;
					while (next_news()):;
						if ($u > 0)
							echo '<p class="newsseparator"/>';
						$u++;
						?>
						<div class="newsarticle">
							<h3><?php printNewsURL(); ?></h3>
							<div class="newsarticlecredit"><span class="newsarticlecredit-left"><?php printNewsDate(); ?> | <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span>
								<?php
								echo ' | ';
								printNewsCategories(", ", gettext("Categories: "), "newscategories");
								?>
							</div>
							<?php printNewsContent(); ?>
							<?php printCodeblock(1); ?>
							<?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ', '); ?>
							<br style="clear:both;" /><br />
						</div>

						<?php
					endwhile;
					echo "</div><br/><hr/>";
					printNewsPageListWithNav(gettext('next »'), gettext('« prev'));
				}
				?>


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
