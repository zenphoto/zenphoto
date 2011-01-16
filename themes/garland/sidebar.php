<?php

// force UTF-8 Ø

if(function_exists('printCustomMenu') && getOption('zenpage_custommenu')) {
	?>
<div class="menu">
<?php printCustomMenu('zenpage','list','',"menu-active","submenu","menu-active",2); ?>
</div>
<?php
} else {
if(function_exists("printAllNewsCategories")) { ?>
<div class="menu">
	<h3><?php echo gettext("News articles"); ?></h3>
	<?php
	printAllNewsCategories(gettext("All news"),TRUE,"","menu-active",true,"submenu","menu-active");
	?>
</div>
<?php } ?>

<?php if(function_exists("printAlbumMenu")) { ?>
<div class="menu">
	<h3><?php echo gettext("Gallery"); ?></h3>
	<?php
	if(!getOption("zenpage_zp_index_news") OR !getOption("zenpage_homepage")) {
		$allalbums = gettext("Gallery index");
	} else {
		$allalbums = "";
	}
	printAlbumMenu("list",NULL,"","menu-active","submenu","menu-active",$allalbums,false,false);
	?>
</div>
<?php } ?>

<?php if(function_exists("printPageMenu")) { ?>
<div class="menu">
	<h3><?php echo gettext("Pages"); ?></h3>
	<?php
	printPageMenu("list","","menu-active","submenu","menu-active"); ?>
</div>
<?php }
} // custom menu check end
?>