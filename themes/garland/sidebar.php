<?php

// force UTF-8 Ø

if ($_zp_gallery_page != 'index.php') {
	?>
	<ul class="menu">
		<li><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>"
			title="<?php echo gettext('Gallery index'); ?>"><?php echo gettext('Gallery index')?></a>
		</li>
	</ul>
	<?php
}

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
		if ($_zp_gallery_page != 'gallery.php' || ($_zp_gallery_page == 'index.php' &&  (getOption('zp_plugin_zenpage') && getOption('custom_index_page')=='gallery'))) {
			$gallery = gettext('Album index');
		} else {
			$gallery = '';
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