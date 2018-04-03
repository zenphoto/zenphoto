<?php include ("inc-header.php"); ?>

<div id="breadcrumbs">
	<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php echo gettext("Archive View"); ?>
</div>
<div id="wrapper">
	<div id="sidebar">
		<div id="sidebar-inner">
			<div id="sidebar-padding">
				<div id="tag_cloud">
					<h3><?php echo gettext('Popular Tags'); ?></h3>
					<?php printAllTagsAs('cloud', 'tags'); ?>
				</div>
				<?php include ("inc-copy.php"); ?>
			</div>
		</div>
	</div>


	<div id="mason">
		<div id="gallery-archive" class="box col17">
			<h3><?php echo gettext('Gallery'); ?></h3>
			<?php printAllDates('archive-list', 'year', 'month', 'desc'); ?>
		</div>
		<?php if (($zenpage) && ($zpmas_usenews)) { ?>
			<div id="news-archive" class="box col16">
				<h3><?php echo NEWS_LABEL; ?></h3>
				<?php printNewsArchive(); ?>
			</div>
		<?php } ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>

