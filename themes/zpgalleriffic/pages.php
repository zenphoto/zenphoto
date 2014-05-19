<?php include ("header.php"); ?>
	
	<?php if(!isset($ishomepage)) { ?>
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					<span><?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home'); ?></a><?php if(!isset($ishomepage)) { printZenpageItemsBreadcrumb(" » ",""); } ?><?php if(!isset($ishomepage)) { printPageTitle(" » "); } ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="wrapper">
		<div class="centered">
			<div class="extra-content">
				<?php printPageMenu('omit-top','news-cat-list','news-cat-active','news-cat-list','news-cat-active'); ?>
				<?php printPageExtraContent(); ?>
			</div>

			<div id="post">		
				<h2><?php printPageTitle(); ?></h2>
				<?php
				printPageContent(); 
				printCodeblock(1); 
				?>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="centered">
			<?php if (function_exists('printCommentForm')) { ?><div id="comment-wrap"><?php printCommentForm(); ?></div><?php } ?>
		</div>
	</div>
			
<?php include("footer.php"); ?>

