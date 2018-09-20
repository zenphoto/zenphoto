<?php include ("inc-header.php"); ?>

<div id="breadcrumbs">
	<h2><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php printParentBreadcrumb('', ' » ', ' » '); ?> <?php printAlbumBreadcrumb('', ' » '); ?><?php printImageTitle(true); ?></h2>
</div>
</div> <!-- close #header -->
<div id="content">
	<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<?php
		if (function_exists('printThumbNav')) {
			printThumbNav(2, 77, 77, 77, 77);
		}
		?>
		<div id="image-wrap">
			<div id="full-image">
				<?php if (($zpmin_finallink) == 'colorbox') { ?><a class="thickbox" href="<?php echo html_encode(getUnprotectedImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printCustomSizedImage(getAnnotatedImageTitle(), 550); ?></a><?php } ?>
				<?php
				if (($zpmin_finallink) == 'nolink') {
					printCustomSizedImage(getAnnotatedImageTitle(), 550);
				}
				?>
				<?php if (($zpmin_finallink) == 'standard') { ?><a href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printCustomSizedImage(getAnnotatedImageTitle(), 550); ?></a><?php } ?>
				<?php if (($zpmin_finallink) == 'standard-new') { ?><a target="_blank" href="<?php echo html_encode(getFullImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>"><?php printCustomSizedImage(getAnnotatedImageTitle(), 550); ?></a><?php } ?>
			</div>
		</div>
		<?php
		if (function_exists('printAddToFavorites')) {
			echo '<div class="section">';
			printAddToFavorites($_zp_current_album);
			echo '</div>';
		}
		?>

		<?php if (function_exists('printGoogleMap')) { ?><div class="section"><?php
			printGoogleMap();
			?></div><?php } ?>
		<?php if (function_exists('printRating')) { ?><div class="section"><?php printRating(); ?></div><?php } ?>
		<?php if (function_exists('printCommentForm')) { ?><div class="section"><?php printCommentForm(); ?></div><?php } ?>
	</div>
	<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div class="image-nav">
			<?php if (hasPrevImage()) { ?><a class="image-prev" href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">&laquo; <?php echo gettext("prev"); ?></a><?php } ?>
			<?php if (hasNextImage()) { ?><a class="image-next" href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> &raquo;</a><?php } ?>
			<span title="<?php echo gettext('Image Number/Total images'); ?>"><?php echo imageNumber() . '/' . getNumImages(); ?></span>
		</div>
		<div class="sidebar-divide">
			<div id="full-image-details">
				<h2><?php printImageTitle(true); ?></h2>
				<div class="sidebar-section"><?php printImageDate('', '', null, true); ?></div>
				<?php if ((getImageDesc()) || (zp_loggedin())) { ?><div class="sidebar-section"><?php printImageDesc(true); ?></div><?php } ?>
				<?php if ((getTags()) || (zp_loggedin())) { ?><div class="sidebar-section"><?php printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', ''); ?></div><?php } ?>
				<?php if (!$zpmin_disablemeta) { ?>
					<?php if ((getImageMetaData()) || (zp_loggedin())) { ?><div class="sidebar-section"><?php printImageMetadata('', false, null, 'full-image-meta', true); ?></div><?php } ?>
				<?php } ?>
				<?php if (function_exists('printSlideShowLink')) { ?><div class="sidebar-section"><div class="slideshow-link"><?php printSlideShowLink(gettext('View Slideshow')); ?></div></div><?php } ?>
			</div>
		</div>
		<?php include ("inc-sidemenu.php"); ?>
	</div>
</div>

<?php include ("inc-footer.php"); ?>
