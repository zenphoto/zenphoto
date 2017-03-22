	<div class="col-sm-3 hidden-xs"	id="sidebar">
		<?php include("_sidebar.php"); ?>
	</div>	
	
	<div class="col-sm-9" id="homemain" itemprop="mainContentOfPage">
		
	<h1 itemprop="name"><?php printGalleryTitle(); ?></h1>

	<div class="row">
		
		<div class="col-sm-6">
			<h2><?php echo gettext("About"); ?></h2>
			<div class="content"><?php printGalleryDesc(); ?></div>	
		</div>	
	
		<div class="col-sm-6">
			<h2><?php echo gettext("Latest news"); ?></h2>
			<?php  // news article loop
			$cnt=0;					
					while (next_news()&& $cnt<2): ;?>
				 <div>
						<h3><?php printNewsURL(); ?></h3>
						<div class="content"><?php printNewsContent(250); echo "<hr />"; ?></div>
				</div>
			<?php
				$cnt++;
				endwhile;
			?>		
		</div>	
	
	</div>	
	
	<h2><?php echo gettext("Albums"); ?></h2>
					
	<?php include("_albumlist.php"); ?>

	<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
	
<!-- Codeblock 1 -->
	<?php printCodeBlock(1);?>	
	
	<?php @call_user_func('printCommentForm'); ?>
		
	</div>