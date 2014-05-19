
<div class="wrapper" id="footer">
	<div class="centered">
		<div id="foot-left">
			<div id="copyright">
				<p>&copy; <?php echo getBareGalleryTitle(); ?>, <?php echo gettext('all rights reserved'); ?> <?php if (function_exists('printContactForm')) { ?> | <?php printCustomPageURL(gettext("Contact Us"), "contact");
}
?></p>
			</div>
				<?php if ($zpgal_show_credit) { ?>
				<div id="zpcredit">
				<?php printZenphotoLink(); ?>
				</div>
			<?php } ?>
			<?php
			if (function_exists('printLanguageSelector')) {
				printLanguageSelector("langselector");
			}
			?>
		</div>
		<div id="foot-right">
			<div id="rsslinks">
				<span><?php echo gettext('Subscribe: '); ?></span>
				<?php
				if (in_context(ZP_ALBUM)) {
					printRSSLink("Collection", "", gettext('This Album'), "  |  ", false, "rsslink");
				}
				printRSSLink("Gallery", "", (gettext('Gallery Images')), "", false, "rsslink");
				if (extensionEnabled('zenpage')) {
					printRSSLink("News", '', '  |  ', gettext('News'), '', false);
				}
				?>
			</div>
		</div>
	</div>
</div>

<?php zp_apply_filter('theme_body_close'); ?>
</body>
</html>