<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<h1 id="tagline"><?php echo gettext('Archive') ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>
	<div class="post">
		<div class="archive">
			<h3><?php echo gettext('Gallery Archive'); ?></h3>
			<?php printAllDates('archive-list', 'year', 'month', 'desc'); ?>
		</div>
		<?php if (function_exists('printNewsArchive')) { ?>
			<div class="archive">
				<h3><?php echo gettext('News Archive'); ?></h3>
				<?php printNewsArchive(); ?>
			</div>
		<?php } ?>
		<div id="tag_cloud">
			<h3><?php echo gettext('Popular Tags'); ?></h3>
			<?php printAllTagsAs('cloud', 'tags'); ?>
		</div>
	</div>
</div>

<?php include("inc-footer.php"); ?>