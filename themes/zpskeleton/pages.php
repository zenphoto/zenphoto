<?php include ("inc-header.php"); ?>
	<div class="wrapper contrast top">
		<div class="container">	
			<div class="sixteen columns">
				<?php include ("inc-search.php"); ?>
				<h5><?php printZenpageItemsBreadcrumb(null,' » '); ?>
				<h1><?php printPageTitle(); ?></h1>
			</div>
		</div>
	</div>
	
	<div class="wrapper">
		<div class="container">
			<div class="ten columns">
				<?php
				printPageContent();
				printCodeblock();
				$singletag = getTags(); $tagstring = implode(', ', $singletag);
				if (strlen($tagstring) > 0) { ?>
					<ul><li class="meta-tags"><?php printTags('links','','taglist', ', '); ?></li></ul>
				<?php } ?>
			</div>
			<div class="five columns offset-by-one sidebar">
				<?php if ($zpskel_social) include ("inc-social.php"); ?>
				<?php printPageExtraContent(); ?>
				<br class="clear" />
				<?php printPageMenu('list','side-menu','active','side-sub','sub-active',null,true); ?>
			</div>
		</div>
	</div>
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>