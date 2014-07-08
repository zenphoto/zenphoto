<?php
// force UTF-8 Ø

rem_context(ZP_ALBUM | ZP_IMAGE);

if (function_exists('printCustomMenu') && ($menu = getOption('effervescence_menu'))) {
	?>
	<div class="menu">
		<?php
		printCustomMenu($menu, 'list', '', "menu-active", "submenu", "menu-active", 2);
		?>
	</div>
	<?php
} else { //	"standard zenpage sidebar menus
	if (extensionEnabled('zenpage') && ZP_NEWS_ENABLED) {
		if (getNumNews(true)) {
			?>
			<div class="menu">
				<h3><?php echo gettext("News articles"); ?></h3>
				<?php printAllNewsCategories(gettext("All news"), true, "", "menu-active", true, "submenu", "menu-active"); ?>
				<div class="menu_rule"></div>
			</div>
			<?php
		}
	}
	?>
	<?php
	if (function_exists("printAlbumMenu")) {
		?>
		<div class="menu">
			<?php
			if (extensionEnabled('zenpage')) {
				if ($_zp_gallery_page == 'index.php' || $_zp_gallery_page != 'gallery.php') {
					?>
					<h3>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext("Gallery"); ?></a>
					</h3>
					<?php
				}
			} else {
				?>
				<h3><?php echo gettext("Gallery"); ?></h3>
				<?php
			}
			printAlbumMenu("list", "count", "album_menu", "menu", "menu_sub", "menu_sub_active", '');
			?>
		</div>
		<?php
	} else {
		if (extensionEnabled('zenpage')) {
			?>
			<div class="menu">
				<h3><?php echo gettext("Albums"); ?></h3>
				<ul id="album_menu">
					<li>
						<a href="<?php echo html_encode(getCustomPageURL('gallery')); ?>" title="<?php echo gettext('Album index'); ?>"><?php echo gettext('Album index'); ?></a>
					</li>
				</ul>
			</div>
			<?php
		}
	}
	?>

	<?php
	if (extensionEnabled('zenpage') && ZP_PAGES_ENABLED) {
		if (getNumPages(true)) {
			?>
			<div class="menu">
				<h3><?php echo gettext("Pages"); ?></h3>
				<?php printPageMenu("list", "", "menu-active", "submenu", "menu-active"); ?>
				<div class="menu_rule"></div>
			</div>
			<?php
		}
	}
	?>

	<div class="menu">
		<h3><?php echo gettext("Archive"); ?></h3>
		<ul>
			<?php
    if (extensionEnabled('zenpage') && ZP_NEWS_ENABLED) {
      $archivlinktext = gettext('Gallery and News');
    } else {
      $archivlinktext = gettext('Gallery');
    }
    if ($_zp_gallery_page == "archive.php") {
				?>
				<li class='menu-active'>
					<?php echo $archivlinktext; ?>
				</li>
				<?php
			} else {
				?>
				<li>
					<?php printCustomPageURL($archivlinktext, "archive"); ?>
				</li>
				<?php
			}
			?>
		</ul>
		<div class="menu_rule"></div>
	</div>

	<?php
	if (getOption('RSS_album_image') || getOption('RSS_articles')) {
		?>
		<div class="menu">
			<h3><?php echo gettext("RSS"); ?></h3>
			<ul>
				<?php
				if (class_exists('RSS')) {
      printRSSLink('Gallery', '<li>', gettext('Gallery'), '</li>');
      if (extensionEnabled('zenpage') && ZP_NEWS_ENABLED) {
        printRSSLink("News", "<li>", gettext("News"), '</li>');
      }
    }
    ?>
			</ul>
		</div>
		<?php
	}
}
?>
