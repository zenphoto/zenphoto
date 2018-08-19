<?php include ("inc-header.php"); ?>
<div class="wrapper contrast top">
	<div class="container">
		<div class="sixteen columns">
			<?php include ("inc-search.php"); ?>
			<h1><?php echo gettext("404 not found"); ?></h1>
		</div>
	</div>
</div>
<div class="wrapper">
	<div class="container">
		<div class="sixteen columns">
			<div class="alert-message block-message error">
				<?php print404status(); ?>
			</div>
		</div>
	</div>
</div>
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>