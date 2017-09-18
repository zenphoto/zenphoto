
<div id="menu">
	<?php if ($_zp_gallery_page != '404.php') { ?>
		<div id="social">
			<?php
			if (extensionEnabled('rss')) {
				if ($zpmas_social) {
					?><div class="social"><?php printAddThis('Style1'); ?></div><?php } ?>
				<?php if ((getOption('RSS_album_image')) || (getOption('RSS_articles')) || (getOption('RSS_article_comments')) || (getOption('RSS_comments'))) { ?>
					<a href="javascript:$('#subscribeextrashow').toggle();" class="rss" title="<?php echo gettext('RSS'); ?>"></a>
					<div id="subscribeextrashow">
						<ul>
							<?php if (in_context(ZP_ALBUM)) { ?>
								<?php if (getOption('RSS_album_image')) { ?><li><?php printRSSLink('Collection', '', gettext('Latest Images of this Album'), '', false); ?></li><?php } ?>
								<?php if (getOption('RSS_comments')) { ?><li><?php printRSSLink('Comments-album', '', gettext('Latest Comments of this Album'), '', false); ?></li><?php } ?>
							<?php } ?>
							<?php if (in_context(ZP_IMAGE)) { ?>
									<?php if (getOption('RSS_comments')) { ?><li><?php printRSSLink('Comments-image', '', gettext('Latest Comments of this Image'), '', false); ?></li><?php } ?>
								<?php } ?>
								<?php if (getOption('RSS_album_image')) { ?><li><?php printRSSLink('Gallery', '', gettext('Latest Images'), '', false); ?></li><?php } ?>
							<?php if (getOption('RSS_album_image')) { ?><li><?php printRSSLink('AlbumsRSS', '', gettext('Latest Albums'), '', false); ?></li><?php } ?>
							<?php if (($zenpage) && ($zpmas_usenews)) { ?>
									<?php if (getOption('RSS_articles')) { ?><li><?php printRSSLink('News', '', '', gettext('Latest News'), '', false); ?></li><?php } ?>
								<?php if (getOption('RSS_article_comments')) { ?><li><?php printRSSLink('Comments-all', '', '', gettext('Latest Comments'), '', false); ?></li><?php } ?>
							<?php } else { ?>
									<?php if (getOption('RSS_comments')) { ?><li><?php printRSSLink('Comments', '', gettext('Latest Comments'), '', false); ?></li><?php } ?>
								<?php } ?>
						</ul>
					</div>
					<?php
				}
			}
			?>
			<?php if ((!zp_loggedin()) && (function_exists('printUserLogin_out'))) { ?>
				<?php if (checkAccess($hint, $show)) { ?>
					<a onclick="$('#password-div').toggle();" class="pass" title="<?php echo gettext('Login'); ?>"></a>
					<div id="password-div">
						<?php printUserLogin_out('', '', 1); ?>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>
	<ul id="nav">
		<li <?php if ($galleryactive) { ?>class="active" <?php } ?>><a href="<?php echo $zpmas_homelink; ?>" title="<?php echo gettext("Gallery"); ?>"><?php echo gettext("Gallery"); ?></a></li>
		<?php if ($zenpage) { ?>
				<?php if ($zpmas_usenews) { ?><li <?php if ($_zp_gallery_page == "news.php") { ?>class="active" <?php } ?>><a href="<?php echo getNewsIndexURL(); ?>"><?php echo gettext('News'); ?></a></li><?php } ?>
				<?php printPageMenu('list-top', '', 'active', '', 'active', '', true, false); ?>
			<?php } ?>
		<li <?php if ($_zp_gallery_page == "archive.php") { ?>class="active" <?php } ?>>
			<a href="<?php echo getCustomPageURL('archive'); ?>" title="<?php echo gettext('Archive View'); ?>"><?php echo gettext('Archive'); ?></a>
		</li>
		<?php
		if (function_exists('printContactForm')) {
			?><li <?php if ($_zp_gallery_page == "contact.php") { ?>class="active" <?php } ?>>
				<?php printCustomPageURL(gettext('Contact'), "contact"); ?></li><?php
		}
		?>
		<?php
		if ((!zp_loggedin()) && (function_exists('printRegistrationForm'))) {
			?>
			<li <?php if ($_zp_gallery_page == "register.php") { ?>class="active" <?php } ?>>
				<a href="<?php echo getCustomPageURL('register'); ?>" title="<?php echo gettext('Register'); ?>"><?php echo gettext('Register'); ?></a>
			</li>
			<?php
		}
		?>
		<?php
		if (function_exists('printFavoritesURL') && $_zp_gallery_page != "favorites.php") {
			printFavoritesURL(NULL, '<li>', '</li><li>', '</li>');
		}
		?>
	</ul>
</div>