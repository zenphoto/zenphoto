<?php
if (!$_zenpage_enabled)
	die();
include('inc_header.php');
?>

<!-- wrap -->
<!-- container -->
<!-- header -->
<h3><?php printNewsIndexURL(gettext('News')); ?><?php printCurrentNewsCategory(' | ' . gettext('Category') . ' : '); ?><?php printCurrentNewsArchive(' | '); ?></h3>
</div> <!-- /header -->

<?php
if (is_NewsArticle()) {
	// single news article
	?>

		<?php if ((getPrevNewsURL()) || (getNextNewsURL())) { ?>
		<ul class="pager row nav_news">
		<?php if (getPrevNewsURL()) { ?>
				<li class="previous span6">
					<a href="<?php $article_url = getPrevNewsURL();
			echo $article_url['link'];
			?>" title="<?php echo $article_url['title']; ?>"> &larr; <?php echo html_encodeTagged(shortenContent($article_url['title'], 30, '(...)')); ?></a>
				</li>
		<?php } ?>
					 <?php if (getNextNewsURL()) { ?>
				<li class="next span6 pull-right">
					<a href="<?php $article_url = getNextNewsURL();
						 echo $article_url['link'];
						 ?>" title="<?php echo $article_url['title']; ?>"><?php echo html_encodeTagged(shortenContent($article_url['title'], 30, '(...)')); ?> &rarr; </a>
				</li>
		<?php } ?>
		</ul>
			<?php } ?>

	<div class="row">
		<div class="span9">
			<?php include('inc_print_news.php'); ?>
		</div>

		<div class="span3">
				<?php printAllNewsCategories(gettext('All news'), false, 'news-cat-list', 'active'); ?>

			<?php if (getNewsExtraContent()) { ?>
				<div class="extra-content clearfix">
		<?php printNewsExtraContent(); ?>
				</div>
	<?php } ?>
		</div>
	</div>

	<?php if (extensionEnabled('comment_form')) { ?>
		<?php include('inc_print_comment.php'); ?>
	<?php } ?>

<?php
} else {
	// news article loop
	?>

	<div class="pagination">
	<?php printNewsPageListWithNav('»', '«', true, 'pagination top-margin-reset', true, 7); ?>
	</div>

	<div class="row">
		<div class="span9">
			<div class="list-post">
	<?php while (next_news()) { ?>
		<?php include('inc_print_news.php'); ?>
			<?php } ?>
			</div>
		</div>

		<div class="span3">
	<?php printAllNewsCategories(gettext('All news'), false, 'news-cat-list', 'active'); ?>
		</div>
	</div>

	<div class="row">
		<div class="span12">
			<div class="pagination">
	<?php printNewsPageListWithNav('»', '«', true, 'pagination top-margin-reset', true, 7); ?>
			</div>
		</div>
	</div>
<?php } ?>

<?php include('inc_footer.php'); ?>