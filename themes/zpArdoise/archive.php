<?php include ('inc_header.php'); ?>

<div id="post">

	<div id="headline" class="clearfix">
		<h3><?php echo gettext('Archive View'); ?></h3>
	</div>

	<div class="post">
		<table id="archive">
			<tr>
				<td>
					<h4><?php echo gettext('Gallery'); ?></h4>
					<?php printAllDates('archive', 'year', 'month', 'desc'); ?>
				</td>
				<?php if ($_zenpage_enabled && getNumNews(true)) { ?>
					<td id="newsarchive">
						<h4><?php echo NEWS_LABEL; ?></h4>
						<?php printNewsArchive('archive', 'year', 'month', 'archive-active', false, 'desc'); ?>
					</td>
				<?php } ?>
			</tr>
		</table>
	</div>

</div>

<?php include('inc_footer.php'); ?>