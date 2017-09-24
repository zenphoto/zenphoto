				<div class="<?php echo $news_class; ?> clearfix">
					<div class="post-date">
						<span class="month"><?php echo strftime('%b', strtotime($_zp_current_article->getDateTime())); ?></span>
						<span class="day"><?php echo strftime('%d', strtotime($_zp_current_article->getDateTime())); ?></span>
						<span class="year"><?php echo strftime('%Y', strtotime($_zp_current_article->getDateTime())); ?></span>
					</div>
					<h4 class="post-title">
						<?php
						if (is_NewsArticle()) {
							printNewsTitle();
						} else {
							printNewsURL();
						}
						?>
					</h4>
					<div class="post-meta clearfix"><?php printNewsCategories('', '', 'nav nav-pills'); ?></div>
					<div class="post-content clearfix">
						<?php
						printNewsContent();
						if (is_NewsArticle()) {
							printCodeblock(1);
						}
						?>
					</div><!--/.post-content -->
				</div><!--/.<?php echo $news_class; ?> -->