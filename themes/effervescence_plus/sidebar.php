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
	if (extensionEnabled('zenpage')) {
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

	<div class="menu">
		<?php
		if (function_exists("printAlbumMenu")) {
			?>
			<h3><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Visit the image gallery'); ?>"><?php echo gettext("Gallery"); ?></a></h3>
			<?php
			printAlbumMenu("list", NULL, "", "menu-active", "submenu", "menu-active", "");
		} else {
			?>
			<h3><?php echo gettext("Gallery"); ?></h3>
			<ul>
				<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Visit the image gallery'); ?>"><?php printGalleryTitle(); ?></a></li>
			</ul>
			<?php
		}
		?>
		<div class="menu_rule"></div>
	</div>
	<?php
	if (extensionEnabled('zenpage')) {
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
			if ($_zp_gallery_page == "archive.php") {
				?>
				<li class='menu-active'>
				<?php echo gettext("Gallery and News"); ?>
				</li>
					<?php
				} else {
					?>
				<li>
				<?php printCustomPageURL(gettext("Gallery and News"), "archive"); ?>
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
		<?php if (class_exists('RSS')) printRSSLink('Gallery', '<li>', gettext('Gallery'), '</li>'); ?>
				<?php
				if (extensionEnabled('zenpage')) {
					?>
					<?php if (class_exists('RSS')) printRSSLink("News", "<li>", gettext("News"), '</li>'); ?>
					<?php if (class_exists('RSS')) printRSSLink("NewsWithImages", "<li>", gettext("News and Gallery"), '</li>'); ?>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
	}
}
?>
