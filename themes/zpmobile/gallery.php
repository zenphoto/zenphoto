<?php 
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html> 
<html>
<head>
	<meta charset="<?php echo LOCAL_CHARSET; ?>">
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
	<?php jqm_loadScripts(); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div data-role="page" id="mainpage">

		<?php jqm_printMainHeaderNav(); ?>
	
	<div class="ui-content" role="main">	
		<div class="content-primary">
		<h2>Gallery</h2>
		<?php printGalleryDesc(); ?>
		<?php if(hasPrevPage() || hasNextPage()) printPageListWithNav(gettext("prev"), gettext("next"),false,true,'pagelist',NULL,true,7); ?>
		<ul data-role="listview" data-inset="true">
			<?php while (next_album()): ?>
							<li>
								<a href="<?php echo html_encode(getAlbumURL());?>" title="<?php echo gettext('View album:'); ?>">
									<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 79, 79, 79, 79, NULL, null, NULL,NULL); ?>
									<h3><?php printAlbumTitle(); ?><small> (<?php printAlbumDate(''); ?>)</small></h3>
									<div class="albumdesc"><?php echo shortenContent(getAlbumDesc(), 100,'(...)',false); ?></div>
									<small class="ui-li-aside ui-li-count"><?php jqm_printImageAlbumCount()?></small>
								</a>
							</li>
			<?php endwhile; ?>
		</ul>
		<?php if(hasPrevPage() || hasNextPage()) printPageListWithNav(gettext("prev"), gettext("next"),false,true,'pagelist',NULL,true,7); ?>
	
		</div>
		 <div class="content-secondary">
			<?php jqm_printMenusLinks(); ?>
 		</div>
	
	</div><!-- /content -->

	<?php jqm_printBacktoTopLink(); ?>
	<?php jqm_printFooterNav(); ?>
</div><!-- /page -->

<?php zp_apply_filter('theme_body_close'); ?>

</body>
</html>
