<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<h5>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery"); ?></a>&nbsp;&raquo;&nbsp;
				<?php printParentBreadcrumb("", " » ", " » "); ?>
			</h5>
			<h1><?php printAlbumTitle(true); ?></h1>
			<p><?php printAlbumDesc(true); ?></p>
			<div class="album-meta">
				<?php
				$singletag = getTags();
				$tagstring = implode(', ', $singletag);
				?>
				<ul class="taglist">
					<li class="meta-date"><?php printAlbumDate('', '', null, true); ?></li>
					<li class="meta-contents">
						<?php
						if ((getNumAlbums() > 0) && (getNumImages() > 0)) {
							$divider = '- ';
						} else {
							$divider = '';
						}
						?>
						<?php if (getNumAlbums() > 0) echo getNumAlbums() . ' ' . gettext("subalbums"); ?>
						<?php echo $divider; ?>
						<?php if (getNumImages() > 0) echo getNumImages() . ' ' . gettext("images"); ?>
					</li>
					<?php if (strlen($tagstring) > 0) { ?><li class="meta-tags"><?php printTags('links', '', 'taglist', ', '); ?></li><?php } ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<?php
		$c = 0;
		while (next_album()):
			?>
			<div class="one-third column album">
				<h4><?php echo html_encode(getBareAlbumTitle()); ?></h4>
				<a href="<?php echo html_encode(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">
					<?php printCustomAlbumThumbImage(getBareAlbumTitle(), null, 420, 200, 420, 200, null, null, 'remove-attributes'); ?>
				</a>
				<div class="album-meta">
					<ul class="taglist">
						<li class="meta-date"><?php printAlbumDate(''); ?></li>
						<li class="meta-contents">
							<?php
							if ((getNumAlbums() > 0) && (getNumImages() > 0)) {
								$divider = '- ';
							} else {
								$divider = '';
							}
							?>
							<?php if (getNumAlbums() > 0) echo getNumAlbums() . ' ' . gettext("subalbums"); ?>
							<?php echo $divider; ?>
							<?php if (getNumImages() > 0) echo getNumImages() . ' ' . gettext("images"); ?>
						</li>
					</ul>
				</div>
				<p class="albumdesc"><?php echo shortenContent(getAlbumDesc(), 80, '...'); ?></p>
				<hr />
			</div>
			<?php
			$c++;
			if ($c == 3) {
				echo '<br class="clear" />';
				$c = 0;
			} endwhile;
		?>
		<!-- Start Images -->
		<?php
		echo '<br class="clear" />';
		$c = 0;
		if ($zpskel_thumbsize == 'small') {
			$colclass = 'two';
			$breakcount = 8;
			$imagesize = 190;
		} else {
			$colclass = 'four';
			$breakcount = 4;
			$imagesize = 220;
		}
		while (next_image()):
			?>
			<div class="<?php echo $colclass; ?> columns image imagegrid">
				<a href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
					<?php
					if ($thumbcrop) {
						printCustomSizedImage(getBareImageTitle(), null, $imagesize, $imagesize, $imagesize, $imagesize, null, null, 'remove-attributes', null, true);
					} else {
						printCustomSizedImage(getBareImageTitle(), $imagesize, null, null, null, null, null, null, 'remove-attributes', null, true);
					}
					?>
				</a>
			</div>
			<?php
			$c++;
			$mobilebreak = $c % 2;
			if ($c == $breakcount) {
				echo '<br class="clear clearforboth" />';
				$c = 0;
			} else if ($mobilebreak == 0) {
				echo '<br class="clear clearformobile" />';
			} endwhile;
		?>
		<div class="sixteen columns">
			<?php if ((hasNextPage()) || (hasPrevPage())) printPageListWithNav("«", "»", false, true, 'pagination', null, true, 5); ?>
			<?php
			if (function_exists('printAddToFavorites')) {
				printAddToFavorites($_zp_current_album);
			}
			?>
			<?php printPPSlideShowLink(gettext('Slideshow')); ?>
			<?php if ($zpskel_social) include ('inc-social.php'); ?>
			<?php if ((function_exists('printGoogleMap'))) { ?>
				<div id="map">
					<?php printGoogleMap(); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="wrapper contrast">
	<div class="container">
		<div class="sixteen columns">
			<?php if (function_exists('printAlbumMenu')) { ?><div class="jump-menu"><?php printAlbumMenu('jump');
				?>
				</div>
				<?php
			}
			if (extensionEnabled('rss')) {
				?>
				<ul class="taglist rss">
					<?php if (getOption('RSS_album_image')) { ?><li><?php printRSSLink('Collection', '', gettext('Latest Images of this Album'), '', false); ?></li>
						<?php
					}
					?>
					<?php
					if ((function_exists('printCommentForm')) && (getOption('RSS_comments'))) {
						?>
						<li><?php printRSSLink('Comments-album', '', gettext('Latest Comments of this Album'), '', false); ?></li>
						<?php
					}
					?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>
<?php
if (function_exists('printRating') || function_exists('printCommentForm')) {
	?>
	<div class="wrapper">
		<div class="container">
			<div class="sixteen columns">
				<?php
				if (function_exists('printRating')) {
					?>
					<div id="rating"><?php printRating(); ?><hr /></div>
						<?php
					}

					if (function_exists('printCommentForm')) {
						printCommentForm();
						echo '<hr />';
					}
					?>
			</div>
		</div>
	</div>
<?php } ?>

<?php include ("inc-footer.php"); ?>