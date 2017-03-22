<div id="breadcrumbs" class="row">
	<div class="col-sm-9">
		<ul class="breadcrumb" itemscope itemtype="http://schema.org/Breadcrumb">

<?php if ($_zp_gallery_page == 'index.php') { ?>		
<?php } ?>
<?php if ($_zp_gallery_page == 'gallery.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li class="active"><?php echo gettext("Albums"); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'album.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li><?php printCustomPageURL(gettext('Albums'), 'gallery'); ?></li><?php printParentBreadcrumb_zb(); ?><li class="active"><?php printAlbumTitle(); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'image.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li><?php printCustomPageURL(gettext('Albums'), 'gallery'); ?></li><?php printParentBreadcrumb_zb(); ?><?php printAlbumBreadcrumb_zb();?><li class="active"><?php printImageTitle(); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'archive.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li class="active"><?php echo gettext("Archive View"); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == '404.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li class="active"><?php echo gettext("Not found"); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'contact.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><li class="active"><?php echo gettext('Contact us') ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'favorites.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li><?php printParentBreadcrumb_zb(); ?><li class="active"><?php printAlbumTitle(); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'news.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><i class="glyphicon glyphicon-home">&nbsp;</i></a></li>
			<li><?php printNewsIndexURL('Blog', ' '); ?></li>
				<?php
					printZenpageItemsBreadcrumb_zb();
					printCurrentNewsCategory_zb();
				?>
				<?php if (is_NewsArticle()) {
						echo '<li class="active">';
						printNewsTitle(" ");
						printCurrentNewsArchive(" ");
						echo '</li>';
				}
				?>
<?php } ?>
				
<?php if ($_zp_gallery_page == 'pages.php') { ?>		
		<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><?php echo gettext("Index"); ?></a></li><?php printZenpageItemsBreadcrumb_zb("", ""); ?><li class="active"><?php printPageTitle(); ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'credits.php') { ?>		
		<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><?php echo gettext("Index"); ?></a></li><li class="active"><?php echo gettext('Credits'); ?></li>
<?php } ?>				
<?php if ($_zp_gallery_page == 'password.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><?php echo gettext("Index"); ?></a></li><li><?php echo gettext("error"); ?></li>
<?php } ?>		
<?php if ($_zp_gallery_page == 'register.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><?php echo gettext("Index"); ?></a></li><li><?php echo gettext('User Registration') ?></li>
<?php } ?>
<?php if ($_zp_gallery_page == 'search.php') { ?>		
			<li><a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo gettext('Index'); ?>" itemprop="url"><?php echo gettext("Index"); ?></a></li><li><?php echo html_encode($searchwords); ?></li>
 <?php } ?>		
		</ul>		
	</div>
	<div class="col-sm-3" id="sharing">
<?php if (getOption('sharethis_id')!='') { ?>		
<!-- ShareThis -->
	<span class='st_facebook'></span>
	<span class='st_googleplus'></span>
	<span class='st_twitter'></span>
	<span class='st_pinterest'></span>
	<span class='st_email'></span>
<?php } ?>	

<?php if (getOption('addthis_code')!='') { ?>
<!-- AddThis -->
	<div class="addthis_sharing_toolbox"></div>
<?php } ?>
	</div>
	
</div>
