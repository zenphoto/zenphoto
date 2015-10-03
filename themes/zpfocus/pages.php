<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<?php if ($zpfocus_social) include ("inc-social.php"); ?>
	<h1 id="tagline"><?php printPageTitle(); ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>

	<div class="post">
		<?php
		printPageContent();
		printCodeblock(1);
		?>
		<div class="newsarticlecredit">
		<?php printTags('links', gettext('Tags:') . ' ', 'taglist', ', '); ?>
		</div>
		<?php
		if (function_exists('printRating')) {
			printRating();
		}
		?>
	</div>
<?php printCodeblock(); ?>
<?php if (function_exists('printCommentForm')) printCommentForm(); ?>

</div>

<?php include("inc-footer.php"); ?>

