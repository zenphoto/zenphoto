<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<h1><?php echo gettext('Archive') ?></h1>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<div class="nine columns">
			<h3><?php echo gettext('Gallery'); ?></h3>
			<?php printAllDates('archive', 'year', 'month', 'desc'); ?>

		</div>
		<div class="six columns offset-by-one">
			<?php if (($zenpage) && ($zpskel_usenews)) { ?>
				<h3><?php echo NEWS_LABEL; ?></h3>
				<?php printNewsArchive('archive', 'year', 'month', 'archive-active', false); ?>
			<?php } ?>
			<h3><?php echo gettext('Tag Cloud'); ?></h3>
			<?php printAllTagsAs('cloud', 'month', 'abc', true, true, 2, 10, 1, null, 1); ?>
		</div>
	</div>
</div>
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>