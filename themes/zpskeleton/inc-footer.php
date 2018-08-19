<!-- End Document
================================================== -->
<div class="wrapper footer">
	<div class="container">
		<div class="sixteen columns">
			<div class="footer-right">
				<ul class="taglist">

					<?php
					if (function_exists("printUserLogin_out")) {
						if (zp_loggedin()) {
							?>
							<li><?php printUserLogin_out("", ""); ?></li>
						<?php } else { ?>
							<li><a href="<?php echo getCustomPageURL('login'); ?>" title="<?php echo gettext('Login'); ?>"><?php echo gettext('Login'); ?></a></li>
						<?php } ?>
					<?php } ?>

					<?php if (!zp_loggedin() && function_exists('printRegistrationForm')) { ?>
						<li>|&nbsp;<a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a></li>
					<?php } ?>

				</ul>
			</div>
			<div class="footer-left">
				&copy; <?php printGalleryTitle(); ?>

				<?php printZenphotoLink(); ?>
			</div>
		</div>
		<?php if (function_exists('printLanguageSelector')) { ?>
			<div class="sixteen columns">
				<?php printLanguageSelector(); ?>
			</div>
		<?php } ?>
		<?php
		if ($zpskel_debuguser) {
			echo '<div class="sixteen columns"><hr />';
			if ($zpskel_ismobile) {
				$isMobile = 'Mobile User';
			} else {
				$isMobile = 'Desktop User';
			}
			echo '<strong>' . $isMobile . '</strong><br />';
			echo $browser->__toString();
			echo '</div>';
		}
		?>
	</div>
</div>
<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>