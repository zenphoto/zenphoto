<?php include ("inc-header.php"); ?>

<div id="breadcrumbs">
	<?php if (($zpmas_infscroll) && (in_context(ZP_SEARCH_LINKED))) $_zp_current_search->page = '1'; ?>
	<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a> &raquo;
	<?php printParentBreadcrumb('', ' » ', ' » '); ?>
	<?php
	if ($zpmas_infscroll) {
		$link = rewrite_path("/" . pathurlencode($_zp_current_album->name) . "/", "/index.php?album=" . pathurlencode($_zp_current_album->name));
		?>
		<a href="<?php echo $link; ?>" title="<?php echo $_zp_current_album->getTitle(); ?>"><?php echo $_zp_current_album->getTitle(); ?></a>&nbsp;&raquo;&nbsp;
		<?php
	} else {
		printAlbumBreadcrumb('', ' » ');
	}
	?>
	<?php printImageTitle(true); ?>
</div>
<div id="wrapper">
	<div id="sidebar">
		<div id="sidebar-inner">
			<div id="sidebar-padding">
				<div class="image-nav">
					<?php if (hasPrevImage()) { ?><a class="image-prev" href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">&larr; <?php echo gettext("prev"); ?></a><?php } ?>
					<?php if (hasNextImage()) { ?><a class="image-next" href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> &rarr;</a><?php } ?>
					<span title="<?php echo gettext('Image Number/Total images'); ?>"><?php echo imageNumber() . '/' . getNumImages(); ?></span>
				</div>
				<div class="sidebar-divide">
					<h2><?php printImageTitle(true); ?></h2>
					<?php
					$singletag = getTags();
					$tagstring = implode(', ', $singletag);
					?>
					<ul class="image-info">
						<?php if ((strlen(getImageDate()) > 0) || (zp_loggedin())) { ?><li class="date"><?php printImageDate(''); ?></li><?php } ?>
						<?php if ((strlen(getImageDesc()) > 0) || (zp_loggedin())) { ?><li class="desc"><?php printImageDesc(); ?></li><?php } ?>
<?php if ((strlen($tagstring) > 0) || (zp_loggedin())) { ?><li class="tags"><?php printTags('links', ' ', 'taglist', ', '); ?></li><?php } ?>
					</ul>
				</div>
				<?php if ($useGslideshow) { ?>
					<div id="slideshowlink" class="sidebar-divide gslideshowlink"><?php printSlideShowLink(gettext('Start Slideshow')); ?></div>
				<?php } ?>

				<?php if (!$zpmas_disablemeta) { ?>
					<?php if ((getImageMetaData()) || (zp_loggedin())) { ?><div class="sidebar-divide"><?php printImageMetadata('', false, null, 'full-image-meta', true); ?></div><?php } ?>
				<?php } ?>
<?php include ("inc-copy.php"); ?>
			</div>
		</div>
	</div>
	<div id="page">
		<div id="image-wrap" class="box">
			<div id="full-image">
				<?php if (($zpmas_finallink) == 'colorbox') { ?><a class="zpmas-cb" href="<?php
					if ($zpmas_cbtarget) {
						echo htmlspecialchars(getDefaultSizedImage());
					} else {
						echo htmlspecialchars(getUnprotectedImageURL());
					}
					?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printDefaultSizedImage(getAnnotatedImageTitle()); ?></a><?php } ?>
					 <?php
					 if (($zpmas_finallink) == 'nolink') {
						 printDefaultSizedImage(getAnnotatedImageTitle());
					 }
					 ?>
				<?php if (($zpmas_finallink) == 'standard') { ?><a href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printDefaultSizedImage(getAnnotatedImageTitle()); ?></a><?php } ?>
<?php if (($zpmas_finallink) == 'standard-new') { ?><a target="_blank" href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printDefaultSizedImage(getAnnotatedImageTitle()); ?></a><?php } ?>
			</div>
		</div>
		<?php if (function_exists('printThumbNav')) { ?>
			<div class="post"><?php printThumbNav(3, NULL, 108, 108, 108, 108); ?></div>
		<?php } else if (function_exists('printPagedThumbsNav')) { ?>
			<div class="post"><?php printPagedThumbsNav(6, true, ' ', ' ', 108, 108); ?></div>
		<?php } ?>
		<?php printCodeblock(); ?>
		<?php if (function_exists('printGoogleMap')) { ?><div class="post"><?php printGoogleMap(); ?></div><?php } ?>
		<?php
		if (function_exists('printAddToFavorites'))
			printAddToFavorites($_zp_current_image);
		?>
		<?php if (function_exists('printRating')) { ?><div class="post"><?php printRating(); ?></div><?php } ?>
<?php if (function_exists('printCommentForm')) { ?><div class="post"><?php printCommentForm(); ?></div><?php } ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>

