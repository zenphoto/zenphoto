<?php include ('inc_header.php'); ?>

	<div id="post">

		<div id="headline" class="clearfix">
			<h3><?php echo gettext("Object not found"); ?></h3>
		</div>

		<h4>
			<?php print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj); ?>
		</h4>

	</div>

<?php include('inc_footer.php'); ?>