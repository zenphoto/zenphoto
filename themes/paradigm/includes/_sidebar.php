<aside class="col-sm-3 hidden-xs"	id="sidebar">

<!-- print album menu if in albums -->


<?php if(function_exists("printAlbumMenu") && (($_zp_gallery_page == 'image.php')||($_zp_gallery_page == 'album.php')||($_zp_gallery_page == 'index.php')||($_zp_gallery_page == 'gallery.php'))) { ?>
<div class="panel panel-default" id="nav-local">
	<div class="panel-heading"><div class="panel-title"><?php echo gettext("Albums"); ?></div></div>
	<div class="panel-body">
		<?php
			printAlbumMenu("list", false, "nav-local-albums", "open", "submenu", "open", "", 0 , false, false);
		?>
</div>
</div>
<?php } ?>


<!-- Print news menu if in news -->

<?php if(function_exists("printAllNewsCategories") && ($_zp_gallery_page == 'news.php')) {
	if (getNumNews(true)) {
	?>
<div class="panel panel-default" id="nav-local">
	<div class="panel-heading"><span class="panel-title"><?php echo gettext("News"); ?></span></div>
	<div class="panel-body">
			<?php printAllNewsCategories("",false,"nav-local-news","open",true,"submenu","open", "list", true,""); ?>						
	</div>
</div>	
			<?php
		}
	}
	?>

<!-- Print pages list if in a page -->

<?php if(function_exists("printPageMenu") && ($_zp_gallery_page == 'pages.php')) { ?>
<div class="panel panel-default" id="nav-local">
	<div class="panel-heading"><span class="panel-title"><?php echo gettext("Pages"); ?></span></div>
	<div class="panel-body">
			<?php printPageMenu("list","nav-local-pages","open","submenu","open"); ?>
	</div>
</div>	
<?php } ?> 

<?php if ((getAllTagsUnique(NULL, 1, true)) && ($_zp_gallery_page == 'search.php')) { ?>
	<div class="panel panel-default">
		<div class="panel-heading"><span class="panel-title"><?php echo gettext('Popular Tags'); ?></span></div>
	<div id="tag_cloud"  class="panel-body">
		<?php printAllTagsAs_zb('cloud', 'taglist', 'abc', false, true, 2.5, 60, 15, null); ?>
	</div>
	</div>
<?php } ?>

<?php if (getcodeblock(1, $_zp_gallery)!='') { ?>
<hr/>
	<div class="well">
		<?php  
			printcodeblock (1, $_zp_gallery);
		?>
	</div>
<?php		} ?>


<?php if (getcodeblock(2, $_zp_gallery)!='') { ?>
<hr/>
	<div class="well">
		<?php  
			printcodeblock (2, $_zp_gallery);
		?>
	</div>
<?php		} ?>

</aside>



