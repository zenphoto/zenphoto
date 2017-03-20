<?php 
if ($_zenpage_enabled) {
	include ('inc_header.php');
?>

		<div id="headline-news">
			<?php if (is_NewsArticle()) { ?>
			<div class="control-nav-news">
				<div class="nav-img clearfix">
					<ul class="clearfix">
					<?php if (getPrevNewsURL()) { ?>
						<li><a href="<?php $article_url = getPrevNewsURL(); echo $article_url['link']; ?>" title="<?php echo $article_url['title']; ?>">&laquo; <?php echo gettext('newer'); ?></a></li>
					<?php } else { ?>
						<li class="disabledlink"><span>&laquo; <?php echo gettext('newer'); ?></span></li>
					<?php } ?>
					<?php if (getNextNewsURL()) { ?>
						<li><a href="<?php $article_url = getNextNewsURL(); echo $article_url['link']; ?>" title="<?php echo $article_url['title']; ?>"><?php echo gettext('older'); ?> &raquo;</a></li>
					<?php } else { ?>
						<li class="disabledlink"><span><?php echo gettext('older'); ?> &raquo;</span></li>
					<?php } ?>
					</ul>
				</div>
			</div>
			<?php } else { ?>
			<div class="news-cat-list">
				<?php printAllNewsCategories(gettext('All news'), true, 'news-cat-list', 'news-cat-active'); ?>
			</div>
			<?php } ?>
			<h3><?php printZenpageItemsBreadcrumb(); ?><?php printCurrentNewsCategory(' » ' . gettext('Category') . ' : '); ?><?php printCurrentNewsArchive(' » '); ?></h3>
		</div>

	<?php
	// single news article
	if (is_NewsArticle()) { ?>
		<div id="news" class="clearfix">
			<h3><?php printNewsTitle(); ?></h3>
			<div class="newsarticlecredit">
				<?php printNewsDate(); ?><?php printNewsCategories(', ', gettext(' | '), 'hor-list'); ?>
			</div>
			<?php if (getNewsExtraContent()) { ?>
			<div class="extra-content clearfix">
				<?php printNewsExtraContent(); ?>
			</div>
			<?php } ?>
			<div class="clearfix">
				<?php printNewsContent(); ?>
				<?php printCodeblock(1); ?>
			</div>
			<?php if (getOption('show_tag')) { ?>
				<div class="headline-tags"><?php printTags('links', '', 'hor-list'); ?></div>
			<?php } ?>
		</div>

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

	<?php } else {
	// news article loop ?>

		<div class="pagination-news clearfix">
			<?php printNewsPageListWithNav(' » ', ' « ', true, 'clearfix'); ?>
		</div>

		<div id="news" class="clearfix">
			<?php while (next_news()) { ?>
			<div class="news-truncate clearfix">
				<h3><?php printNewsURL(); ?></h3>
				<div class="newsarticlecredit">
					<?php printNewsDate(); ?><?php printNewsCategories(', ', ' | ', 'hor-list'); ?>
				</div>

				<?php printNewsContent(false, '<p class="readmorelink">(...)</p>'); ?>

			</div>
			<?php } ?>
		</div>

		<div class="pagination-news clearfix">
			<?php printNewsPageListWithNav(' » ', ' « ', true, 'clearfix'); ?>
		</div>

	<?php } ?>

<?php
	include('inc_footer.php');

} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
} ?>