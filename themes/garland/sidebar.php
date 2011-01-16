<?php

// force UTF-8 Ø
if(function_exists("printAllNewsCategories")) {
	?>
	<div class="menu">
		<h3><?php echo gettext("News articles"); ?></h3>
		<?php
		printAllNewsCategories(gettext("All news"),TRUE,"menu","menu",true,"menu_sub","menu_sub_active");
		?>
	</div>
	<?php
	}
?>

<?php
if(function_exists("printAlbumMenu")) {
	?>
	<div class="menu">
		<h3><?php echo gettext("Gallery"); ?></h3>
		<?php
		$gallery = '';
		if (getOption('zp_plugin_zenpage')) {
			if ($_zp_gallery_page == 'index.php' || $_zp_gallery_page != 'gallery.php') {
				$gallery = gettext('Album index');
			}
		}
		printAlbumMenu("list","count","menu","menu","menu_sub","menu_sub_active", $gallery);
		?>
	</div>
	<?php
}
?>

<?php
if(function_exists("printPageMenu")) {
	?>
	<div class="menu">
		<h3><?php echo gettext("Pages"); ?></h3>
		<?php
		printPageMenu("list","menu","menu-active","submenu","menu-active"); ?>
	</div>
	<?php
}
?>