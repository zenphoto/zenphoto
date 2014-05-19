<?php include ("header.php"); ?>
	
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					<span><?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <?php printNewsIndexURL("News",""); ?><?php printCurrentNewsCategory(" » Category - "); ?><?php printNewsTitle("  »  "); printCurrentNewsArchive("  »  "); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="centered">	
			<?php // single news article
			if (is_NewsArticle()) { ?>  
			<div class="extra-content">
				<?php printNewsExtraContent(); ?>
			</div>
			<div id="post">
				<h2><?php printNewsTitle(); ?></h2>
				<div class="newsarticlecredit">
					<span><?php printNewsDate();?> | <?php printNewsCategories(", ",gettext("Categories: "),"hor-list"); ?> <?php if (function_exists('printCommentForm')) { ?>| <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?><?php } ?></span>
				</div>
			
				<?php 
				printNewsContent(); 
				printCodeblock(1); 
				?>
			</div>
			<?php if (function_exists('printCommentForm')) { ?><div id="comment-wrap"><?php printCommentForm(); ?></div><?php } ?>
			
			<?php if(is_NewsArticle()) { ?>
			<?php if(getPrevNewsURL()) { ?><div id="navbar-prev"><?php printPrevNewsLink('‹'); ?></div><?php } ?>
			<?php if(getNextNewsURL()) { ?><div id="navbar-next"><?php printNextNewsLink('›'); ?></div><?php } ?>
			<?php } ?>
			
			<?php } else { // news article loop ?>
		
			<div id="post">
				<div class="extra-content">
					<?php printAllNewsCategories( gettext('All News'),true,'news-cat-list','news-cat-active' ); ?>
					<h4><?php echo gettext('News archive'); ?></h4>
					<?php printNewsArchive('archive-menu','year','month','active-selected'); ?>
				</div>	
				<?php if (strlen(getNewsCategoryDesc()) > 0) { ?>
				<div><?php echo getNewsCategoryDesc(); ?></div><br />
				<?php } ?>
				<?php while (next_news()): ;?> 
				<div class="news-truncate"> 
					<h3><?php printNewsURL(); ?></h3>	
					<div class="newsarticlecredit">
						<span><?php printNewsDate();?> | <?php printNewsCategories(", ",gettext("Categories: "),"hor-list"); ?><?php if (function_exists('printCommentForm')) { ?> | <?php echo gettext("Comments:"); ?> <?php echo getCommentCount(); ?> <?php } ?></span>
					</div>	
					<?php printNewsContent(); ?>
					<?php printCodeblock(1); ?>
				</div>	
				<?php endwhile; ?>
			</div>	
			<div class="paging">
				<?php printNewsPageListWithNav( gettext('Next ›'),gettext('‹ Previous'),true,'' ); ?>
			</div>
			<?php } ?> 
		</div>
	</div>
	
<?php include("footer.php"); ?>

