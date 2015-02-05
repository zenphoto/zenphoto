<?php 
if (!extensionEnabled('contact_form')) die();
include('inc_header.php');
?>

	<!-- wrap -->
		<!-- container -->
			<!-- header -->
				<h3><?php echo gettext('Contact'); ?></h3>
			</div> <!-- /header -->

			<div class="row">
				<div class="span10 offset1">
					<div class="post">
						<?php printContactForm(); ?>
					</div>
				</div>
			</div>

<?php include('inc_footer.php'); ?>