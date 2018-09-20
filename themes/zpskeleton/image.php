<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<h5>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext("Gallery"); ?></a>&nbsp;&raquo;&nbsp;
				<?php printParentBreadcrumb('', ' » ', ' » ');
				printAlbumBreadcrumb(' ', ' » ');
				?>
				<span> (<?php echo imageNumber() . "/" . getNumImages(); ?>)</span>
			</h5>
			<h1><?php printImageTitle(true); ?></h1>
			<p><?php printImageDesc(true); ?></p>
			<div class="news-meta">
				<?php $singletag = getTags();
				$tagstring = implode(', ', $singletag);
				?>
				<ul class="taglist">
					<li class="meta-date"><?php printImageDate('', '', null, true); ?></li>
<?php if (strlen($tagstring) > 0) { ?><li class="meta-tags"><?php printTags('links', '', 'taglist', ', '); ?></li><?php } ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<?php
		$nextimg = $_zp_current_image->getNextImage();
		$previmg = $_zp_current_image->getPrevImage();
		?>
		<div class="ten columns">
			<div class="image-wrap" id="image">
				<?php
				if ($zpskel_ismobile) {
					$size = 420;
				} else {
					$size = 630;
				}
				printCustomSizedImage(getBareImageTitle(), $size, null, null, null, null, null, null, 'remove-attributes');
				?>
				<?php if ($nextimg) { ?>
					<a class="mobile-nav next" href="<?php echo html_encode(getNextImageURL()); ?>#image" title="<?php echo gettext("Next Image"); ?>">&raquo;</a>
				<?php } ?>
<?php if ($previmg) { ?>
					<a class="mobile-nav prev" href="<?php echo html_encode(getPrevImageURL()); ?>#image" title="<?php echo gettext("Previous Image"); ?>">&laquo;</a>
			<?php } ?>
				<br class="clear" />
			</div>
				<?php if (function_exists('printGoogleMap')) { ?>
				<div id="map">
					<?php printGoogleMap(); ?>
				</div>
				<?php } ?>
			</div>
		<div class="five columns offset-by-one sidebar">
				<?php if ($zpskel_social) include ("inc-social.php"); ?>
				<?php if (($nextimg) || ($previmg)) { ?>
				<div class="img-nav noshow-mobile">
					<?php if ($nextimg) { ?>
						<a class="button" href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext('Next Image') . ' (' . $nextimg->getTitle() . ')'; ?> &raquo;</a>
					<?php } ?>
				<?php if ($previmg) { ?>
						<a class="button" href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">&laquo; <?php echo gettext('Previous Image') . ' (' . $previmg->getTitle() . ')'; ?></a>
				<?php } ?>
				</div>
				<?php } ?>
			<?php if (getImageMetadata()) { ?>
				<div class="img-nav noshow-mobile">
				<?php printImageMetadata('', false); ?>
				</div>
			<?php } ?>
			<?php
			if (function_exists('printAddToFavorites')) {
				printAddToFavorites($_zp_current_album);
			}
			?>
<?php if ($zpskel_download) { ?><a style="margin-bottom:10px;" class="button" href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo gettext('Download Original'); ?>"><?php echo gettext('Download Original') . ' (' . getFullWidth() . ' x ' . getFullHeight() . ')'; ?></a><?php } ?>
<?php printPPSlideShowLink(gettext('Start Slideshow')); ?>
		</div>
	</div>
</div>
<div class="wrapper contrast">
	<div class="container">
		<div class="sixteen columns">
			<?php if (function_exists('printAlbumMenu')) { ?><div class="jump-menu"><?php printAlbumMenu('jump'); ?></div><?php } ?>
			<?php if (extensionEnabled('rss')) { ?>
				<ul class="taglist rss">
					<?php if ((function_exists('printCommentForm')) && (getOption('RSS_comments'))) { ?><li><?php printRSSLink('Comments-image', '', gettext('Latest Comments of this Image'), '', false); ?></li><?php } ?>
					<?php if (getOption('RSS_album_image')) { ?><li><?php printRSSLink('Collection', '', gettext('Latest Images of this Album'), '', false); ?></li><?php } ?>
					<?php if ((function_exists('printCommentForm')) && (getOption('RSS_comments'))) { ?><li><?php printRSSLink('Comments-album', '', gettext('Latest Comments of this Album'), '', false); ?></li><?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>
			<?php if ((function_exists('printRating')) || (function_exists('printCommentForm'))) { ?>
	<div class="wrapper">
		<div class="container">
			<div class="sixteen columns">
					<?php if (function_exists('printRating')) { ?>
					<div id="rating"><?php printRating(); ?><hr /></div>
					<?php } ?>
					<?php
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