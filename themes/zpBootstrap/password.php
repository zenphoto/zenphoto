<?php include('inc_header.php'); ?>

	<!-- wrap -->
		<!-- container -->
			<!-- header -->
				<h3><?php echo gettext('Password required'); ?></h3>
			</div> <!-- /header -->

			<div class="row">
				<div class="span12">
					<div class="post">
						<?php printPasswordForm('', true); ?>
						<script type="text/javascript">
							jQuery(document).ready(function($) {
								$('#zpB_passwordform').modal({
									show: true
								});
							});
						</script>
					</div>
				</div>
			</div>

<?php include('inc_footer.php'); ?>