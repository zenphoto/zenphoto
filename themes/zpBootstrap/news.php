<?php
if (!$_zenpage_enabled) die();
include('inc_header.php');
?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printZenpageItemsBreadcrumb(); ?><?php printCurrentNewsCategory(' | ' . gettext('Category') . ' : '); ?><?php printCurrentNewsArchive(' | '); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

	<?php if (is_NewsArticle()) {
		// single news article
		$news_class = 'post'; ?>

		<?php if ((getPrevNewsURL()) || (getNextNewsURL())) { ?>
		<nav class="row">
			<ul class="pager margin-top-reset margin-bottom-reset">
				<?php if (getPrevNewsURL()) { ?>
				<li class="previous margin-bottom col-sm-6 pull-left">
					<a href="<?php $article_url = getPrevNewsURL(); echo $article_url['link']; ?>" title="<?php echo $article_url['title']; ?>"> &larr; <?php echo shortenContent($article_url['title'], 30, ' (...)'); ?></a>
				</li>
				<?php } ?>
				<?php if (getNextNewsURL()) { ?>
				<li class="next margin-bottom col-sm-6 pull-right">
					<a href="<?php $article_url = getNextNewsURL(); echo $article_url['link']; ?>" title="<?php echo $article_url['title']; ?>"><?php echo shortenContent($article_url['title'], 30, ' (...)'); ?> &rarr; </a>
				</li>
				<?php } ?>
			</ul>
		</nav>
		<?php } ?>

		<div class="row">
			<?php if (getNewsExtraContent()) { ?>
			<div class="col-sm-9">
				<?php include('inc_print_news.php'); ?>
			</div>
			<div class="col-sm-3">
				<div class="post extra-content clearfix">
					<?php printNewsExtraContent(); ?>
				</div>
			</div>
			<?php } else { ?>
			<div class="col-sm-12">
				<?php include('inc_print_news.php'); ?>
			</div>
			<?php } ?>
		</div>

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

	<?php } else {
		// news article loop
		$news_class = 'list-post'; ?>

		<?php if ($_zp_CMS->getAllCategories()) { ?>
		<div class="row margin-bottom">
			<div class="col-sm-offset-1 col-sm-10">
				<?php printAllNewsCategories(gettext('All news'), true, 'news-cat-list', 'active'); ?>
			</div>
		</div>
		<?php } ?>

		<?php printNewsPageListWithNav('»', '«', true, 'pagination pagination-sm', true, 7); ?>

		<div class="list-news">
			<?php while (next_news()) { ?>
				<?php include('inc_print_news.php'); ?>
			<?php } ?>
		</div>

		<?php printNewsPageListWithNav('»', '«', true, 'pagination pagination-sm margin-top-reset', true, 7); ?>

	<?php } ?>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>