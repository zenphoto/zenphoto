<?php include ('inc_header.php'); ?>

	<div id="post">

		<div id="headline" class="clearfix">
			<h3><?php printHomeLink('', ' » '); ?>
			<?php if (gettext(getOption('zenpage_homepage')) == gettext('none')) { ?>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
			<?php } else { ?>
				<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
			<?php } ?>
			&raquo;&nbsp;<?php echo gettext("Object not found"); ?></h3>
		</div>

		<h4>
			<?php print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj); ?>
		</h4>

	</div>

<?php include('inc_footer.php'); ?>