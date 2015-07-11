<?php include ("inc-header.php"); ?>

		<div id="breadcrumbs">
			<a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery Index"); ?>"><?php echo gettext("Gallery Index"); ?></a> &raquo;  <?php echo gettext('User Registration'); ?>
		</div>
		<div id="wrapper">
			<div id="sidebar">
				<div id="sidebar-inner">
					<div id="sidebar-padding">
						<?php include ("inc-copy.php"); ?>
					</div>
				</div>
			</div>
			<div id="page">
				<div class="post">
					<h1><?php echo gettext('User Registration') ?></h1>
					<?php printRegistrationForm(); ?>
				</div>
			</div>	
		</div>

<?php include ("inc-footer.php"); ?>	
