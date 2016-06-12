<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<?php if (($zpfocus_social) && (is_NewsArticle())) include ("inc-social.php"); ?>
	<h1 id="tagline"><?php printNewsIndexURL("News"); ?><?php printCurrentNewsCategory(" / Category - "); ?><?php
		printNewsTitle(" / ");
		printCurrentNewsArchive(" / ");
		?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>

	<?php
	// single news article
	if (is_NewsArticle()) {
		?>

		<div class="newsarticle">
			<div class="newsarticlecredit">
				<span class="newsarticlecredit-left"><?php printNewsDate(); ?> |
					<?php if ((function_exists('printCommentForm')) && (getCommentsAllowed())) { ?>
						<?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> |
					<?php } ?>
				</span>
				<?php printNewsCategories(", ", gettext("Categories: "), "newscategories"); ?> |
				<?php printTags('links', gettext('Tags:') . ' ', 'taglist', ', '); ?>
			</div>
			<h3><?php printNewsURL(); ?></h3>
			<?php printNewsContent(); ?>
		</div>

		<?php if (function_exists('printRating')) { ?>
			<div id="rating" class="rating-news">
				<?php printRating(); ?>
			</div>
		<?php } ?>

		<?php if (function_exists('printCommentForm')) printCommentForm(); ?>

		<div id="img-topbar" class="clearfix" style="margin-top:15px;">
			<?php if (getNextNewsURL()) { ?>
				<div id="img-next"><?php printNextNewsLink('»'); ?></div>
			<?php } ?>
			<?php if (getPrevNewsURL()) { ?>
				<div id="img-prev"><?php printPrevNewsLink('«'); ?></div>
			<?php } ?>
		</div>

		<?php
	} else {
		// news article loop

		if ($_zp_current_category) {
			?>
			<h4 class="blockhead-r"><span><?php printCurrentNewsCategory(''); ?></span></h4>
			<?php if (strlen(getNewsCategoryDesc()) > 0) { ?>
				<div id="manual-spotlight"><?php echo getNewsCategoryDesc(); ?></div>
				<?php
			}
		}

		while (next_news()):;
			?>
			<div class="newsarticle">
				<div class="newsarticlecredit">
					<span class="newsarticlecredit-left"><?php printNewsDate(); ?> |
						<?php if (function_exists('printCommentForm')) { ?>
							<?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> |
						<?php } ?>
					</span>
					<?php printNewsCategories(", ", gettext("Categories: "), "newscategories"); ?>
					<?php printTags('links', gettext('Tags:') . ' ', 'taglist', ', '); ?>
				</div>
				<h3><?php printNewsURL(); ?></h3>
				<?php printNewsContent(); ?>
				<?php printCodeblock(); ?>
			</div>
		<?php endwhile; ?>
		<div class="page-nav">
			<?php printNewsPageListWithNav('»', '«', 'true', 'page-nav'); ?>
		</div>
	<?php } ?>

</div>

<?php include("inc-footer.php"); ?>

