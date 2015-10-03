<?php include("inc-header.php"); ?>
<?php include("inc-sidebar.php"); ?>

<div class="right">
	<h1 id="tagline"><?php echo $zpfocus_tagline; ?></h1>
	<?php if ($zpfocus_logotype) { ?>
		<a style="display:block;" href="<?php echo getGalleryIndexURL(); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpfocus_logofile; ?>" alt="<?php echo html_encode(getBareGalleryTitle()); ?>" /></a>
	<?php } else { ?>
		<h2 id="logo"><a href="<?php echo html_encode(getGalleryIndexURL()); ?>"><?php echo html_encode(getBareGalleryTitle()); ?></a></h2>
	<?php } ?>

	<?php if (!zp_loggedin()) { ?>
		<div class="error"><?php echo gettext("Please Login"); ?></div>
		<?php printPasswordForm($hint, $show); ?>
	<?php } else { ?>
		<div class="errorbox">
			<p><?php echo gettext('You are logged in...'); ?></p>
		</div>
	<?php } ?>

	<?php
	if (!zp_loggedin() && function_exists('printRegistrationForm') && $_zp_gallery->isUnprotectedPage('register')) {
		printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');
		echo '<br />';
	}
	?>

</div>

<?php include("inc-footer.php"); ?>

