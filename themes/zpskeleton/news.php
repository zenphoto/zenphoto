<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<?php if (is_NewsArticle()) { ?>
				<h5><?php
					printNewsIndexURL(gettext('News'));
					echo ' &raquo; ';
					?>(<?php printNewsCategories(', ', '', 'taglist'); ?>)</h5>
				<h1><?php printNewsTitle(); ?></h1>
				<div class="news-meta">
					<?php
					$singletag = getTags();
					$tagstring = implode(', ', $singletag);
					?>
					<ul class="taglist">
						<li class="meta-date"><?php printNewsDate(); ?></li>
						<li class="meta-comments"><?php echo getCommentCount() . ' ' . gettext('Comment(s)'); ?></li>
						<li class="meta-cats"><?php printNewsCategories(', ', '', 'taglist'); ?></li>
						<?php if (strlen($tagstring) > 0) { ?><li class="meta-tags"><?php printTags('links', '', 'taglist', ', '); ?></li><?php } ?>
					</ul>
				</div>
			<?php } else if (in_context(ZP_ZENPAGE_NEWS_CATEGORY)) { ?>
				<h5><?php
					printNewsIndexURL(gettext('News'));
					echo ' » ';
					?></h5>
				<h1><?php printCurrentNewsCategory(); ?></h1>
				<p><?php printNewsCategoryDesc(); ?></p>
			<?php } else if (in_context(ZP_ZENPAGE_NEWS_DATE)) { ?>
				<h5><?php
					printNewsIndexURL(gettext('News'));
					echo ' » ';
					?></h5>
				<h1><?php printCurrentNewsArchive(); ?></h1>
			<?php } else { ?>
				<h1><?php echo gettext('News'); ?></h1>
			<?php } ?>
		</div>
	</div>
</div>

<div class="wrapper">
	<div class="container">
		<div class="ten columns">
			<?php
			// single news article
			if (is_NewsArticle()) {
				?>
				<?php printNewsContent(); ?>
				<?php
			} else {
				// news article loop
				while (next_news()):;
					?>
					<div class="newsarticle clearfix">
						<h3><?php printNewsURL(); ?></h3>
						<div class="news-meta">
							<ul class="taglist">
								<li class="meta-date"><?php printNewsDate(); ?></li>
								<li class="meta-comments"><?php echo getCommentCount() . ' ' . gettext('Comment(s)'); ?></li>
								<?php
								echo '<li class="meta-cats">';
								printNewsCategories(', ', '', 'taglist');
								echo '</li>';
								?>
								<?php
								$singletag = getTags();
								$tagstring = implode(', ', $singletag);
								?>
									<?php if (strlen($tagstring) > 0) { ?><li class="meta-tags"><?php printTags('links', '', 'taglist', ', '); ?></li><?php } ?>
							</ul>
						</div>
						<?php printNewsContent(); ?>
						<?php printCodeblock(); ?>
					</div>
				<?php endwhile; ?>
				<?php if ((getPrevNewsPageURL()) || (getNextNewsPageURL())) { ?>
					<div class="pagination">
						<?php printNewsPageListWithNav('»', '«', true, 'pagination', true, 5); ?>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="five columns offset-by-one sidebar">
			<?php if (is_NewsArticle()) { ?>
				<?php if ($zpskel_social) include ("inc-social.php"); ?>
				<?php printNewsExtraContent(); ?>

				<div class="news-nav">
					<?php
					$next_article_url = getNextNewsURL('date', 'desc');
					if ($next_article_url && array_key_exists('link', $next_article_url) && $next_article_url['link'] != "") {
						echo "<a class=\"button\" href=\"" . html_encode($next_article_url['link']) . "\" title=\"" . html_encode(strip_tags($next_article_url['title'])) . "\">" . $next_article_url['title'] . " &raquo;</a> ";
					}
					$prev_article_url = getPrevNewsURL('date', 'desc');
					if ($prev_article_url && array_key_exists('link', $prev_article_url) && $prev_article_url['link'] != "") {
						echo "<a class=\"button\" href=\"" . html_encode($prev_article_url['link']) . "\" title=\"" . html_encode(strip_tags($prev_article_url['title'])) . "\">&laquo; " . $prev_article_url['title'] . "</a> ";
					}
					?>
				</div>

			<?php } ?>
			<h3><?php echo gettext('News Categories'); ?></h3>
			<?php printAllNewsCategories('', true, 'side-menu', 'active'); ?>
		</div>
	</div>
</div>
<?php if (is_NewsArticle()) { ?>
	<div class="wrapper contrast">
		<div class="container">
			<div class="sixteen columns">
				<?php if (function_exists('printAlbumMenu')) { ?><div class="jump-menu"><?php printAlbumMenu('jump'); ?></div><?php } ?>
				<?php if (extensionEnabled('rss')) { ?>
					<ul class="taglist rss">
						<?php if ((function_exists('printCommentForm')) && (getOption('RSS_article_comments'))) { ?><li><?php printRSSLink('Comments-news', '', '', gettext('Comments of this article'), '', false); ?></li><?php } ?>
					</ul>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php if ((function_exists('printRating')) || (function_exists('printCommentForm'))) { ?>
		<div class="wrapper">
			<div class="container">
				<div class="sixteen columns">
					<?php if (function_exists('printRating')) { ?>
						<div id="rating"><?php printRating(); ?><hr /></div>
						<?php } ?>
						<?php
						if (function_exists('printCommentForm')) {
							printCommentForm();
							echo '<hr />';
						}
						?>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } else { ?>
	<?php include ("inc-bottom.php"); ?>
<?php } ?>

<?php include ("inc-footer.php"); ?>