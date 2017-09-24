<?php include('inc_header.php'); ?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php echo gettext('Search'); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="page-header row">
			<div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-offset-3 col-md-6">
				<?php printSearchForm(); ?>
			</div>
		</div>

		<table id="archives" class="table">
			<thead>
				<th><h4><?php echo gettext('Gallery archive'); ?></h4></th>
				<?php if ($_zenpage_enabled) { ?>
				<th><h4><?php echo gettext('News archive'); ?></h4></th>
				<?php } ?>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php printAllDates('list-unstyled', 'year', 'month nav nav-pills col-xs-offset-1', 'desc'); ?>
					</td>
					<?php if ($_zenpage_enabled) { ?>
					<td id="newsarchives">
						<?php printNewsArchive('list-unstyled', 'year', 'month nav nav-pills col-xs-offset-1', null, false, 'desc'); ?>
					</td>
					<?php } ?>
				</tr>
			</tbody>
		</table>

		<?php if (getOption('zpB_show_tags')) { ?>
		<table id="tags" class="table">
			<thead>
				<th><h4><?php echo gettext('Tags'); ?></h4></th>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php printAllTagsAs('list', 'nav nav-pills', 'abc', true, true); ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } ?>

		<table id="cat" class="table">
			<thead>
				<th><h4><?php echo gettext('News Categories'); ?></h4></th>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php printAllNewsCategories('', true, 'news-cat-list'); ?>
					</td>
				</tr>
			</tbody>
		</table>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>