<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
?>
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
		<?php printGalleryDesc(); ?>
		<?php
		if(function_exists('printLatestImages')) {
			?>
			<h2><?php echo gettext('Latest images'); ?></h2>
			<?php $latestimages = getImageStatistic(8,'latest','',false, 0,'desc'); ?>
			<div class="ui-grid-c">
				<?php 
				$count = '';
				foreach($latestimages as $image) { 
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
					<a class="image<?php echo $imgclass; ?>" href="<?php echo html_encode($image->getImageLink());?>" title="<?php echo html_encode($image->getTitle());?>">
					<img src="<?php echo $image->getCustomImage(null, 230, 230, 230, 230, null, null,true,NULL); ?>" alt="<?php echo $image->getTitle();?>">
			</a>
			<?php 
				}
			}
		?>
		</div>
		<br class="clearall" />
		<br />
		<?php
		if(function_exists('next_news')) { ?>
			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider"><h2><?php echo gettext('Latest news'); ?></h2></li>
				 <?php
				 while (next_news()): ?>
					<li>
						<a href="<?php echo html_encode(jqm_getNewsLink()); ?>" title="<?php printBareNewsTitle(); ?>">
						<?php printNewsTitle(); ?> <small>(<?php printNewsDate();?>)</small>
						<?php jqm_printCombiNewsThumb(); ?>
    				</a>
    			</li>
				<?php
  			endwhile;
  			?>
  		</ul>
  	<?php
		}
		?>

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
