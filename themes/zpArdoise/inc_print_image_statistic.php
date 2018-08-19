<div id="image-stat" class="clearfix">
	<h4 id="image-stat-title">
		<?php
		switch (getOption('image_statistic')) {
			case 'random':
				echo gettext('Random images');
				break;
			case 'popular':
				echo gettext('Popular images');
				break;
			case 'latest':
				echo gettext('Latest images');
				break;
			case 'latest-date':
				echo gettext('Latest images');
				break;
			case 'latest-mtime':
				echo gettext('Latest images');
				break;
			case 'mostrated':
				echo gettext('Most rated images');
				break;
			case 'toprated':
				echo gettext('Top rated images');
				break;
		}
		?>
	</h4>
	<?php
	if (getOption('use_galleriffic')) {
		$number = 8;	// displays 8 thumbnails with default size (85*85) with galleriffic script
	} else {
		$number = 5;	// displays 5 thumbnails with default size (150*150)
	}

	if (getOption('image_statistic') == 'random') {
		if (getOption('use_colorbox_album')) {
			zpArdoise_printRandomImages($number, NULL, 'all', '', NULL, NULL, NULL, true, 'colorbox');
		} else {
			zpArdoise_printRandomImages($number, NULL, 'all', '', NULL, NULL, NULL, false);
		}
	} else {
		if (getOption('use_colorbox_album')) {
			zpArdoise_printImageStatistic($number, getOption('image_statistic'), '', false, false, false, '', '', NULL, NULL, NULL, false, true, 0, 'colorbox');
		} else {
			zpArdoise_printImageStatistic($number, getOption('image_statistic'), '', false, false, false, '', '', NULL, NULL, NULL, false, false, 0);
		}
	}
	?>
</div>