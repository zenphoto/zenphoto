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
		
			<h2><?php echo gettext("Archive view"); ?></h2>

			<div id="archive">
			<h3><?php echo gettext('Gallery archive'); ?></h3>
			<?php printAllDates(); ?>
			<hr />
			<?php if(function_exists("printNewsArchive")) { ?>
			<h3><?php echo gettext('News archive'); ?></h3>
			<?php printNewsArchive("archive"); ?>
			<hr />
			<?php } ?>

					<h3><?php echo gettext('Popular Tags'); ?></h3>
			<div id="tag_cloud">
 			<?php printAllTagsAs('cloud', 'tags'); ?>
				</div>
 			</div>
 		
 		</div>
		 <div class="content-secondary">
			<?php jqm_printMenusLinks(); ?>
 		</div>
</div><!-- /content -->
<?php jqm_printBacktoTopLink(); ?>
<?php jqm_printFooterNav(); ?>
</div><!-- /page -->
<?php zp_apply_filter('theme_body_close');
?>
</body>
</html>
