<?php include ('inc_header.php'); ?>

<div id="post">
	<div id="headline" class="clearfix">
		<h3><?php printHomeLink('', ' » '); ?>
			<?php if (gettext(getOption('zenpage_homepage')) == gettext('none')) { ?>
				<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Albums Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
			<?php } else { ?>
				<?php printCustomPageURL(getGalleryTitle(), 'gallery'); ?>
			<?php } ?>
			&raquo;&nbsp;<?php echo gettext('Password required'); ?></h3>
	</div>

	<div class="post">
		<?php printPasswordForm($hint, $show, false); ?>
	</div>
</div>

<?php include('inc_footer.php'); ?>