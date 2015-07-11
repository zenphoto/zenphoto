
<div id="sidemenu">
	<?php
	if (function_exists('printAlbumMenuJump')) {
		printAlbumMenuJump('count', gettext('Home'));
	}
	?>
	<?php
	if (($zpmin_menu) && (function_exists('printCustomMenu'))) {
		printCustomMenu($zpmin_menu, 'list', 'nav', 'active', '', 'active', true, true, gettext('Home'));
	} else {
		?>
		<ul id="nav">
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo ('Home'); ?>"><?php echo ('Home'); ?></a></li>
			<li <?php if ($galleryactive) { ?>class="active" <?php } ?>><a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery'); ?>"><?php echo gettext('Gallery'); ?></a></li>
			<li <?php if ($_zp_gallery_page == "archive.php") { ?>class="active" <?php } ?>><a href="<?php echo getCustomPageURL('archive'); ?>" title="<?php echo gettext('Archive View'); ?>"><?php echo gettext('Archive'); ?></a></li>
			<?php if (function_exists('getNewsIndexURL')) { ?>
				<li <?php if ($_zp_gallery_page == "news.php") { ?>class="active" <?php } ?>>
					<a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('News'); ?></a>
					<?php
					if ($_zp_gallery_page == "news.php") {
						printAllNewsCategories('', true, '', 'active', true, '', 'active', 'list', true);
					}
					?>
				</li>
			<?php } ?>
			<?php
			if (function_exists('printPageMenu')) {
				printPageMenu('list', '', 'active', '', 'active', '', true, false);
			}
			?>
			<?php
			if (function_exists('printFavoritesURL')) {
				printFavoritesURL(NULL, '<li>', '</li><li>', '</li>');
			}
			?>
			<?php if (function_exists('printContactForm')) { ?><li <?php if ($_zp_gallery_page == "contact.php") { ?>class="active" <?php } ?>><?php printCustomPageURL(gettext('Contact'), "contact"); ?></li><?php } ?>
			<?php if (!zp_loggedin() && function_exists('printRegistrationForm')) { ?>
				<li <?php if ($_zp_gallery_page == "register.php") { ?>class="active" <?php } ?>><a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a></li>
			<?php } ?>
			<?php
			if (function_exists("printUserLogin_out")) {
				if (zp_loggedin()) {
					?>
					<li><?php printUserLogin_out('', ''); ?></li>
				<?php } else { ?>
					<li <?php if ($_zp_gallery_page == "login.php") { ?>class="active" <?php } ?>><a href="<?php echo getCustomPageURL('login'); ?>" title="<?php echo gettext('Login'); ?>"><?php echo gettext('Login'); ?></a></li>
					<?php } ?>
				<?php } ?>
		</ul>
	<?php } ?>
</div>