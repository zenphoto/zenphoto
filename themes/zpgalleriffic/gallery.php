<?php include ("header.php"); ?>
<?php if ($zpgal_minigal) { ?>
	<?php if (is_numeric($zpgal_minigalheight)) {
		$minigalimageheight = $zpgal_minigalheight;
		$minigalheight = $minigalimageheight + 3;
	} else {
		$minigalimageheight = 250;
		$minigalheight = $minigalimageheight + 3;
	} ?>
	<?php if (is_numeric($zpgal_minigalcount)) {
		$minigalcount = $zpgal_minigalcount;
	} else {
		$minigalcount = 12;
	} ?>
	<style>
		#minigal div.slideshow a.advance-link {line-height:<?php echo $minigalheight; ?>px;}
		#minigal div.slideshow-container {height:<?php echo $minigalheight; ?>px;}
		#minigal div.loader,#minigal div.slideshow a.advance-link,#minigal div.caption-container {height:<?php echo $minigalheight; ?>px;}
		#home-left,#homegal-wrap,#home-right{height:<?php echo $minigalheight; ?>px;}
		#minigal div.download,#minigal div.download a {height:<?php echo $minigalheight; ?>px;}
	</style>
	<div class="wrapper" id="minigal">
		<div class="centered">
			<div id="minigal-title">
				<?php
				switch ($zpgal_minigaloption) {
					case "random":
						echo gettext('Random Images');
						break;
					case "popular":
						echo gettext('Popular Images');
						$minigalstat = true;
						break;
					case "latest":
						echo gettext('Latest Images');
						$minigalstat = true;
						break;
					case "latest-date":
						echo gettext('Latest Images');
						$minigalstat = true;
						break;
					case "latest-mtime":
						echo gettext('Latest Images');
						$minigalstat = true;
						break;
					case "mostrated":
						echo gettext('Most Rated Images');
						$minigalstat = true;
						break;
					case "toprated":
						echo gettext('Top Rated Images');
						$minigalstat = true;
						break;
					case "specific":
						save_context();
						$album = new Album($_zp_gallery, $zpgal_minigalspecified);
						makeAlbumCurrent($album);
						echo getAlbumTitle();
						break;
				}
				?>
			</div>
			<div id="homegal-wrap">
				<div class="container" id="home-right">
					<div class="content">
						<div class="slideshow-container">
							<div id="loading" class="loader"></div>
							<div id="slideshow" class="slideshow"></div>
						</div>
						<div id="caption" class="caption-container">

						</div>
					</div>
				</div>

				<div class="container" id="home-left">
					<div><?php echo shortenContent(printGalleryDesc(), 20, '...'); ?></div>
					<div id="minigal-thumbwrap">
						<div class="navigation-container">
							<div id="thumbshome" class="navigation">
								<a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>
								<ul class="thumbs noscript">
									<?php if ($minigalstat) { ?>
										<?php $images = getImageStatistic($minigalcount, $zpgal_minigaloption);
										foreach ($images as $image) {
											?>
											<li>
												<a class="thumb" href="<?php echo html_encode($image->getCustomImage(null, 412, $minigalimageheight, 412, $minigalimageheight, null, null, true)); ?>" title="<?php echo html_encode($image->getTitle()); ?>">
													<img src="<?php echo html_encode($image->getCustomImage(null, 65, 65, 65, 65, null, null, true)); ?>" alt="<?php echo html_encode($image->getTitle()); ?>" />
												</a>
												<div class="caption">
													<div class="download">
														<a href="<?php echo html_encode($image->getLink()); ?>" title="<?php echo html_encode($image->getTitle()); ?>"></a>
													</div>
												</div>
											</li>
		<?php } ?>
	<?php
	} else if (($zpgal_minigaloption) == 'specific') {

		$images = $album->getImages(0);
		shuffle($images);
		$c = 0;
		foreach ($images as $img) {
			$c++;
			if (($zpgal_minigalspecifiedcount) && ($c > $zpgal_minigalcount)) {
				break;
			}
			$image = newImage($album, $img);
			?>
											<li>
												<a class="thumb" href="<?php echo html_encode($image->getCustomImage(null, 412, $minigalimageheight, 412, $minigalimageheight, null, null, true)); ?>" title="<?php echo html_encode($image->getTitle()); ?>">
													<img src="<?php echo html_encode($image->getCustomImage(null, 65, 65, 65, 65, null, null, true)); ?>" alt="<?php echo html_encode($image->getTitle()); ?>" />
												</a>
												<div class="caption">
													<div class="download">
														<a href="<?php echo html_encode($image->getLink()); ?>" title="<?php echo html_encode($image->getTitle()); ?>"></a>
													</div>
												</div>
											</li>
		<?php }
		restore_context();
		?>

									<?php } else if (($zpgal_minigaloption) == 'random') { ?>
		<?php
		for ($i = 1; $i <= $minigalcount; $i++) {
			$randomImage = getRandomImages();
			if (is_object($randomImage) && $randomImage->exists) {
				$randomImageURL = html_encode(getURL($randomImage));
				?>
												<li>
													<a class="thumb" href="<?php echo html_encode($randomImage->getCustomImage(null, 412, $minigalimageheight, 412, $minigalimageheight, null, null, true)); ?>" title="<?php echo html_encode($randomImage->getTitle()); ?>">
														<img src="<?php echo html_encode($randomImage->getCustomImage(null, 65, 65, 65, 65, null, null, true)); ?>" alt="<?php echo html_encode($randomImage->getTitle()); ?>" />
													</a>
													<div class="caption">
														<div class="download">
															<a href="<?php echo html_encode($randomImage->getLink()); ?>" title="<?php echo html_encode($randomImage->getTitle()); ?>"></a>
														</div>
													</div>
												</li>
						<?php } ?>
				<?php } ?>
			<?php } ?>
								</ul>
								<a class="pageLink next" style="visibility: hidden;" href="#" title="Next Page"></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
					<?php } ?>

<div class="wrapper">
	<div class="centered">
						<?php if (($zpgal_zp_latestnews > 0) && (function_exists('printLatestNews'))) { ?>
			<div id="sidebar">
					<?php if (is_numeric($zpgal_zp_latestnews_trunc)) {
						$latesttrunc = $zpgal_zp_latestnews_trunc;
					} else {
						$latesttrunc = 400;
					} ?>
				<?php printLatestNews($zpgal_zp_latestnews, '', true, true, $latesttrunc, true); ?>
			</div>
				<?php $lastcolnum = 2;
				setOption('albums_per_row', '2', false);
			} else {
				$lastcolnum = 3;
				setOption('albums_per_row', '3', false);
			} ?>
		<div id="album-wrap" <?php if ((function_exists('printLatestNews')) && ($zpgal_zp_latestnews > 0)) { ?>class="withsidebar"<?php } ?>>
			<ul>
<?php $x = 1;
while (next_album()): $lastcol = "";
	if ($x == $lastcolnum) {
		$lastcol = " class='lastcol'";
		$x = 0;
	}
	?>
					<li<?php echo $lastcol; ?>>
	<?php if (strlen(getAlbumDesc()) > 0) { ?><a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 238, 100, 238, 100); ?></a>
	<?php } else { ?>
							<a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle(); ?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(), NULL, 238, 160, 238, 160); ?></a>
	<?php } ?>
						<h4><a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:') . getBareAlbumTitle(); ?>"><?php echo shortenContent(getBareAlbumTitle(), 30, '...'); ?></a></h4>
	<?php if (strlen(getAlbumDesc()) > 0) { ?><div><?php echo shortenContent(getAlbumDesc(), 115, '...'); ?></div><?php } ?>
						<small>
	<?php printAlbumDate(); ?>
	<?php if (getCommentCount() > 0) { ?>&bull;&nbsp;<?php echo getCommentCount() . ' ' . gettext('Comment(s)'); ?><?php } ?>
						</small>
					</li>
	<?php $x++;
endwhile; ?>
			</ul>
		</div>
		<div class="paging">
<?php if ((getPrevPageURL()) || (getNextPageURL())) { ?>
	<?php printPageListWithNav(gettext('‹ Previous'), gettext('Next ›'), false, 'true', 'pagelist', '', true, '5'); ?>
<?php } ?>
<?php if (function_exists('printAlbumMenu')) { ?>
				<div id="albumjump">
	<?php printAlbumMenu('jump', true); ?>
				</div>
<?php } ?>
		</div>
	</div>
</div>

<?php include("footer.php"); ?>