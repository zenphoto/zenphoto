<?php include('inc_header.php'); ?>

<!-- wrap -->
<!-- container -->
<!-- header -->
<h3><?php printGalleryTitle(); ?> &raquo; <?php echo gettext("Object not found"); ?></h3>
</div> <!-- / header -->

<h4>
	<?php
	echo gettext('The object you are requesting cannot be found.');
	if (isset($album)) {
		echo '<br />' . sprintf(gettext('Album: %s'), html_encode($album));
	}
	if (isset($image)) {
		echo '<br />' . sprintf(gettext('Image: %s'), html_encode($image));
	}
	if (isset($obj)) {
		echo '<br />' . sprintf(gettext('Page: %s'), html_encode(substr(basename($obj), 0, -4)));
	}
	?>
</h4>

<?php include('inc_footer.php'); ?>