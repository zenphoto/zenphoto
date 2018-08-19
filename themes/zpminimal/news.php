<?php include ("inc-header.php"); ?>
			
				<div id="breadcrumbs">
					<h2><a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php printNewsIndexURL("News",""); ?><?php printCurrentNewsCategory(" » Category → "); ?><?php printNewsTitle("  »  "); printCurrentNewsArchive("  »  "); ?></h2>
				</div>
			</div> <!-- close #header -->
			<div id="content">
				<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<?php if (is_NewsArticle()) { ?>  
					<div id="post">
						<h1><?php printNewsTitle(); ?></h1>
						<div class="newsarticlecredit">
							<span><?php printNewsDate();?> &sdot; <?php printNewsCategories(", ",gettext("Categories: "),"taglist"); ?> <?php if (function_exists('printCommentForm')) { ?>&sdot; <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?><?php } ?></span>
						</div>
						<?php printNewsContent(); printCodeblock(1); ?>
					</div>
					<div id="pagination">
						<?php if(getPrevNewsURL()) { ?><div class="prev"><?php printPrevNewsLink('←'); ?></div><?php } ?>
						<?php if(getNextNewsURL()) { ?><div class="next"><?php printNextNewsLink('→'); ?></div><?php } ?>
					</div>	
					<?php if (function_exists('printCommentForm')) { ?><div class="section"><?php printCommentForm(); ?></div><?php } ?>
					<?php } else { ?>
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
						<?php while (next_news()): ;?> 
						<div class="news-truncate"> 
							<h2><?php printNewsURL(); ?></h2>	
							<div class="newsarticlecredit">
								<span><?php printNewsDate();?> &sdot; <?php printNewsCategories(", ",gettext("Categories: "),"taglist"); ?><?php if (function_exists('printCommentForm')) { ?> &sdot; <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> <?php } ?></span>
							</div>	
							<?php printNewsContent(); printCodeblock(1); ?>
						</div>	
						<?php endwhile; ?>
					</div>
					<?php if ((getNextNewsPageURL()) || (getPrevNewsPageURL()))	{ ?>				
					<div id="pagination">
						<?php printNewsPageListWithNav( gettext('Next →'),gettext('← Prev'),true,'' ); ?>
					</div>
					<?php } ?>
					<?php } ?> 
				</div>
				<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<?php if (is_NewsArticle() && getNewsExtraContent()) { ?>
					<div class="sidebar-divide">
						<div class="extra-content"><?php printNewsExtraContent(); ?></div>
					</div>
					<?php } ?>
					<?php include ("inc-sidemenu.php"); ?>
					<?php if (function_exists('printCommentForm')) { ?>
					<div class="latest">
						<?php if (function_exists('printLatestDisqus')) {
						printLatestDisqus(3);
						} else {
						if ($zenpage) printLatestComments(2);
						printLatestComments(2); 
						} ?>
					</div>
					<?php } ?>
				</div>
			</div>

<?php include ("inc-footer.php"); ?>	

