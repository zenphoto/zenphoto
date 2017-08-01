<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<?php if ($zpfocus_social) include ("inc-social.php"); ?>
	<h1 id="tagline"><?php echo $zpfocus_tagline; ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>

	<?php if (($zenpage) && (($zpfocus_spotlight) == 'latest') && (getLatestNews())) { ?>
		<a id="latest-news-link" href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('Latest News'); ?></a>
		<?php printLatestNewsCustom(1, '', true, true, 500, true); ?>
	<?php } ?>
	<?php if ($zpfocus_spotlight == 'manual') { ?><div id="manual-spotlight"><?php echo $zpfocus_spotlight_text; ?></div> <?php } ?>

	<h4 class="blockhead-r"><span><?php echo gettext('Latest Albums'); ?></span></h4>
	<div class="album-wrap">
		<ul>
			<?php
			$x = 1;
			while (next_album()):
				if ($odd = $x % 2) {
					$css = 'goleft';
				} else {
					$css = 'goright';
				}
				?>
				<li class="<?php echo $css; ?>">
					<h4><a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>"><?php echo html_encodeTagged(shortenContent(getAlbumTitle(), 25, '...')); ?></a></h4>
					<a class="thumb" href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">
						<?php printAlbumThumbImage(getBareAlbumTitle()); ?>
					</a>
					<span class="front-date"><?php printAlbumDate(); ?></span>
					<p class="front-desc">
						<?php echo html_encodeTagged(shortenContent(getAlbumDesc(), 175)); ?>
						<a href="<?php echo htmlspecialchars(getAlbumURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php echo html_encode(getBareAlbumTitle()); ?>">&raquo;</a>
					</p>
				</li>
				<?php
				$x = $x + 1;
			endwhile;
			?>
		</ul>
	</div>
	<?php if ((getPrevPageURL()) || (getNextPageURL())) { ?>
		<?php printPageListWithNav('« ' . gettext('Prev'), gettext('Next') . ' »', false, 'true', 'page-nav', '', true, '5'); ?>
	<?php } ?>
	<?php printCodeblock(); ?>
</div>

<?php include("inc-footer.php"); ?>

