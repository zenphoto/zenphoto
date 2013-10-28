<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die(); ?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" />
	<?php jqm_loadScripts(); ?>
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>


<div data-role="page" id="mainpage">

  <?php jqm_printMainHeaderNav(); ?>

	<div data-role="content">
		<div class="content-primary">
		<h2 class="breadcrumb"><a href="<?php echo getGalleryIndexURL(); ?>"><?php echo gettext('Gallery'); ?></a> <?php printParentBreadcrumb('','',''); ?> <?php printAlbumTitle();?></h2>
		<?php printAlbumDesc(); ?>
		<?php if(hasPrevPage() || hasNextPage()) printPageListWithNav(gettext("prev"), gettext("next"),false,true,'pagelist',NULL,true,7); ?>
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<?php while (next_album()): ?>
			<li>
			<a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php echo gettext('View album:'); ?>">
			<?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 79, 79, 79, 79, NULL, null, NULL,NULL); ?>
			<?php printAlbumTitle(); ?><small> (<?php printAlbumDate(''); ?>)</small>
			<div class="albumdesc"><?php echo shortenContent(getAlbumDesc(), 100,'(...)',false); ?></div>
			<small class="ui-li-count"><?php jqm_printImageAlbumCount()?></small>
			</a>
			</li>
			<?php endwhile; ?>
		</ul>
		<div class="ui-grid-c">
			<?php 
			$count = '';
			while (next_image()): 
				$count++;
				switch($count) {
					case 1:
						$imgclass = ' ui-block-a';
						break;
					case 2:
						$imgclass = ' ui-block-b';
						break;
					case 3:
						$imgclass = ' ui-block-c';
						break;
					case 4:
						$imgclass = ' ui-block-d';
						$count = ''; // reset to start with a again;
						break;
				}
			?>
				<a class="image<?php echo $imgclass; ?>" href="<?php echo html_encode(getImageLinkURL());?>" title="<?php printBareImageTitle();?>">
					<?php printCustomSizedImage(getAnnotatedImageTitle(), NULL,230, 230, 230, 230, NULL, NULL, NULL, NULL, true, NULL); ?>
				</a>
			<?php endwhile; ?>
		</div>
		<br class="clearall" />
		<?php if(hasPrevPage() || hasNextPage()) printPageListWithNav(gettext("prev"), gettext("next"),false,true,'pagelist',NULL,true,7); ?>
		<?php
					if (function_exists('printAddToFavorites')) {
						echo "<br />";
						printAddToFavorites($_zp_current_album);
					}
	
	  ?>	
			
			<?php
		if (function_exists('printCommentForm')) {
		  printCommentForm();
		}	?>
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