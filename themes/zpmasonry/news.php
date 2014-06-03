<?php include ("inc-header.php"); ?>

		<div id="breadcrumbs">
			<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a> &raquo; <?php printNewsIndexURL("News",""); ?><?php printCurrentNewsCategory(" » Category - "); ?><?php printNewsTitle("  »  "); printCurrentNewsArchive("  »  "); ?>
		</div>
		<div id="wrapper">
			<div id="sidebar">
				<div id="sidebar-inner">
					<div id="sidebar-padding">
						<?php if (getNewsExtraContent()) { ?>
						<div class="sidebar-divide">
							<div class="extra-content"><?php printNewsExtraContent(); ?></div>
						</div>
						<?php } ?>
						<div class="side-menu sidebar-divide">
							<h3><?php echo gettext('News Categories'); ?></h3>
							<?php printAllNewsCategories('',true,'','active',true,'','active','list',true); ?>
						</div>
						<?php if (function_exists('printCommentForm')) { ?>
						<div class="latest sidebar-divide">
							<h3><?php echo gettext('Latest Comments'); ?></h3>
							<?php if (function_exists('printLatestDisqus')) {
							printLatestDisqus(3);
							} else {
							printLatestComments(1);
							printLatestComments(1);
							} ?>
						</div>
						<?php } ?>
						<?php include ("inc-copy.php"); ?>
					</div>
				</div>
			</div>
			<?php if (is_NewsArticle()) { ?>
			<div id="page">
				<div class="post">
					<h1><?php printNewsTitle(); ?></h1>
					<div class="newsarticlecredit">
						<span><?php printNewsDate();?></span><span><?php printNewsCategories(", ",gettext("Categories: "),"taglist"); ?></span><?php if (function_exists('printCommentForm')) { ?><span><?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span><?php } ?>
					</div>
					<?php printNewsContent(); printCodeblock(); ?>
				</div>
				<div id="pagination">
					<?php if(getPrevNewsURL()) { ?><div class="prev"><?php printPrevNewsLink('«'); ?></div><?php } ?>
					<?php if(getNextNewsURL()) { ?><div class="next"><?php printNextNewsLink('»'); ?></div><?php } ?>
				</div>
				<?php if (function_exists('printRating')) { ?><div class="post"><?php printRating(); ?></div><?php } ?>
				<?php if (function_exists('printCommentForm')) { ?><div class="post"><?php printCommentForm(); ?></div><?php } ?>
			</div>
			<?php } else { ?>
			<div id="page">
				<div id="post">
					<?php if ($_zp_current_category) { ?>
					<h1><?php printCurrentNewsCategory(''); ?></h1>
					<?php } ?>
					<?php if (getCurrentNewsArchive()) { ?>
					<h1><?php printCurrentNewsArchive(); ?></h1>
					<?php } ?>
					<?php if (strlen(getNewsCategoryDesc()) > 0) { ?>
					<div><?php echo getNewsCategoryDesc(); ?></div><br />
					<?php } ?>

					<div id="mason">
					<?php while (next_news()):;?>
					<div class="news-truncate box">
						<h2><?php printNewsURL(); ?></h2>
						<div class="newsarticlecredit">
							<span><?php printNewsDate();?></span><span><?php printNewsCategories(", ", gettext("Categories: "), "taglist"); ?></span><?php if (function_exists('printCommentForm')) { ?><span><?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?></span><?php } ?>
						</div>
						<?php echo preg_replace("/<img[^>]+\>/i", " [image removed] ", getNewsContent()); printCodeblock(); ?>
					</div>
					<?php endwhile; ?>
					</div>
				</div>

				<?php if ($zpmas_infscroll) { ?>
				<div id="page_nav">
					<?php if (getNextNewsPageURL())  { ?><a href="<?php echo getNextNewsPageURL(); ?>">Next Page</a> <?php } ?>
				</div>
				<?php } else {
				if ((getNextNewsPageURL()) || (getPrevNewsPageURL()))	{ ?>
				<div id="pagination">
					<?php printNewsPageListWithNav( gettext('Next »'),gettext('« Prev'),true,'' ); ?>
				</div>
				<?php } ?>
				<?php } ?>

			</div>
			<?php } ?>
		</div>

<?php include ("inc-footer.php"); ?>