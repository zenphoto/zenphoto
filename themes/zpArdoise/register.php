<?php
if (extensionEnabled('register_user')) {
	include ('inc_header.php');
?>

	<div id="post">

		<div id="headline" class="clearfix">
			<h3><?php echo gettext('User Registration') ?></h3>
		</div>

		<div class="post">
			<div id="registration"><?php printRegistrationForm(); ?></div>
		</div>
	</div>

<?php
	include('inc_footer.php');

} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
} ?>