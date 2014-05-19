<?php include("inc-header.php"); ?>
<?php include ("inc-sidebar.php"); ?>

<div class="right">
	<h1 id="tagline"><?php echo gettext('Page not found...') ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo getBareGalleryTitle(); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo getBareGalleryTitle(); ?></a></h2>
	<?php } ?>
	<div class="post">
		<br /><h4>
			<?php
			echo gettext("The page you are requesting cannot be found.");
			if (isset($album)) {
				echo '<br />' . sprintf(gettext('Album: %s'), sanitize($album));
			}
			if (isset($image)) {
				echo '<br />' . sprintf(gettext('Image: %s'), sanitize($image));
			}
			if (isset($obj)) {
				echo '<br />' . sprintf(gettext('Page: %s'), substr(basename($obj), 0, -4));
			}
			?>
		</h4><br />
	</div>
</div>

<?php include("inc-footer.php"); ?>