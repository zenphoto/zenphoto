</div> <!-- END WRAP -->
<div class="footerwrap">
	<?php if (getOption('zpfocus_center_site')) echo "<div class=\"center\">"; ?>
	<div class="left">
		<div id="copyright">
			<p>&copy; <?php echo html_encode(getBareGalleryTitle()); ?>, <?php echo gettext('all rights reserved'); ?></p>
		</div>
		<div id="zpcredit">
			<?php
			if ($zpfocus_show_credit) {
				printZenphotoLink();
			}
			?>
		</div>
	</div>
	<div class="right">
		<ul id="login_menu">
			<?php
			if (function_exists('printFavoritesURL')) {
				printFavoritesURL(NULL, '<li>', '</li><li>', '</li>');
			}
			?>
			<?php if (!zp_loggedin() && function_exists('printRegistrationForm')) { ?>
				<li><a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a></li>
			<?php } ?>

			<?php
			if (function_exists("printUserLogin_out")) {
				if (zp_loggedin()) {
					?>
					<li><?php printUserLogin_out("", ""); ?></li>
				<?php } else { ?>
					<li> | <a href="<?php echo getCustomPageURL('password'); ?>"><?php echo gettext('Login'); ?></a></li>
				<?php } ?>
			<?php } ?>
		</ul> <?php
		if (extensionEnabled('rss')) {
			if ((getOption('RSS_items_albums')) || (getOption('RSS_zenpage_items'))) {
				?>
				<div id="rsslinks">
					<span><?php echo gettext('Subscribe: '); ?></span>
					<?php
					if ((in_context(ZP_ALBUM)) && (getOption('RSS_album_image'))) {
						printRSSLink("Collection", "", gettext('This Album'), "  |  ", false, "rsslink");
					}
					if (getOption('RSS_items_albums')) {
						printRSSLink("Gallery", "", (gettext('Gallery Images')), "", false, "rsslink");
					}
					if (function_exists('getBarePageTitle') && getOption('RSS_zenpage_items') && getNumNews(true)) {
						printRSSLink("News", ' | ', gettext('News'), '', false);
					}
					?>
				</div>
				<br />
				<?php
			}
		}
		if (function_exists('printLanguageSelector')) {
			printLanguageSelector();
		}
		?>
	</div>
</div>
<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>