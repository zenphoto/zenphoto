<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<h1 id="tagline"><?php echo gettext('Register') ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>
	<div class="post"><?php printRegistrationForm(); ?></div>
</div>

<?php include("inc-footer.php"); ?>