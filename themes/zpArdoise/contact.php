<?php 
if (extensionEnabled('contact_form')) {
	include ('inc_header.php');
?>

	<div id="post">

		<div id="headline" class="clearfix">
			<h3><?php echo gettext('Contact'); ?></h3>
		</div>

		<div class="post">
			<?php printContactForm(); ?>
		</div>

	</div>

<?php
	include('inc_footer.php');

} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
} ?>