<?php include ("inc-header.php"); ?>
	<div class="wrapper contrast top">
		<div class="container">	
			<div class="sixteen columns">
				<?php include ("inc-search.php"); ?>
				<h1><?php echo gettext("Login"); ?></h1>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="container">
			<div class="sixteen columns">
				<?php printUserLogin_out("","",true); ?>
			</div>
		</div>
	</div>	
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>