<div class="wrapper contrast bottom">
	<div class="container">
		<?php
		// redundancy to only show on mobile if on index.php
		if ($_zp_gallery_page != "index.php") {
			$classfull = ' class="noshow-mobile"';
			$class = ' noshow-mobile';
		} else {
			$classfull = '';
			$class = '';
		}
		if ((!$zpskel_ismobile) || (($zpskel_ismobile) && ($_zp_gallery_page == "index.php"))) {
			?>
			<div class="sixteen columns<?php echo $class; ?>">
				<?php if (function_exists('printAlbumMenu')) { ?><div class="jump-menu"><?php printAlbumMenu('jump'); ?></div><?php } ?>
				<?php
				if ($zpskel_strip == 'random') {
					echo '<h3>' . gettext('Random Images') . '</h3>';
				} else {
					echo '<h3>' . gettext('Latest Images') . '</h3>';
				}
				?>
			</div>
			<?php if ($zpskel_strip == 'latest') { ?>
				<div<?php echo $classfull; ?>>
					<?php printImageStatistic(8, 'latest', '', false, false, false, 40, '', 190, 190, true, false); ?>
				</div>
			<?php } else { ?>
				<div<?php echo $classfull; ?>>
					<?php printRandomImages(8, 'random-image', 'all', '', 190, 190, true); ?>
				</div>
			<?php } ?>
			<?php if (($zenpage) && (function_exists('printCommentForm')) && ($zpskel_usenews)) { ?>
				<div class="sixteen columns<?php echo $class; ?>"><hr /></div>
				<div class="eight columns latest-list<?php echo $class; ?>">
					<h3><?php echo gettext('Latest News'); ?></h3>
					<?php printLatestNews(1, '', true, true, 250, false, 'Read More...'); ?>
				</div>
				<div class="eight columns latest-list<?php echo $class; ?>">
					<h3><?php echo gettext('Latest Comments'); ?></h3>
					<?php printLatestComments(2, '125', 'all'); ?>
				</div>
			<?php } ?>
		<?php } ?>
		<div class="sixteen columns">
			<hr <?php echo $classfull; ?>/>
			<?php if (extensionEnabled('rss')) { ?>
				<ul class="taglist rss">
					<?php if (getOption('RSS_album_image')) { ?>
						<li><?php printRSSLink('Gallery', '', gettext('Images'), '', false); ?></li>
						<li><?php printRSSLink('AlbumsRSS', '', gettext('Albums'), '', false); ?></li>
					<?php } ?>
					<?php if (($zenpage) && ($zpskel_usenews) && (getOption('RSS_articles'))) { ?>
						<li><?php printRSSLink('News', '', gettext('News'), '', false); ?></li>
						<?php
						if ((function_exists('printCommentForm')) && getOption('RSS_article_comments')) {
							?>
							<li>
								<?php printRSSLink('Comments-all', '', gettext('Comments'), '', false); ?>
							</li>
							<?php
						}
						?>
						<?php
					} else {
						?>
						<?php
						if ((function_exists('printCommentForm')) && getOption('RSS_comments')) {
							?>
							<li>
								<?php printRSSLink('Comments', '', gettext('Comments'), '', false); ?>
							</li>
							<?php
						}
						?>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>