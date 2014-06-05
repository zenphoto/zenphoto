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
			<div style='display: none;'>
				<?php printPasswordForm('', true); ?>
			</div>
		</div>
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function(){
				$.colorbox({
					inline: true,
					href: "#passwordform",
					innerWidth: "400px",
					close: '<?php echo gettext("close"); ?>',
					open: true
				});
			});
		//]]>
		</script>

	</div>

<?php include('inc_footer.php'); ?>