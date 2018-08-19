<?php 
if (!extensionEnabled('register_user')) die();
include('inc_header.php');
?>

	<!-- .container -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php echo gettext('User Registration') ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<div class="row">
			<div class="col-sm-offset-1 col-sm-10">
				<div class="post">
					<?php printRegistrationForm(); ?>
				</div>
			</div>
		</div>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>