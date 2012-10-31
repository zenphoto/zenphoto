<?php
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php zp_apply_filter('theme_head'); ?>
<title><?php printGalleryTitle(); if ($_zp_page>1) echo "[$_zp_page]"; ?></title>
<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
<?php printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a></h1>
					<span id="galleryDescription"><?php printGalleryDesc(); ?></span>
				</div>
			</div>
		</div><!-- header -->
	<div class="sidebar">
		<div id="leftsidebar">
			<?php include("sidebar.php"); ?>
		</div>
	</div>
	<div id="center">
		<div id="squeeze">
			<div class="right-corner">
				<div class="left-corner"><!-- begin content -->
					<div class="main section" id="main">
						<h2 id="gallerytitle"><?php printHomeLink('',' » '); ?><?php printGalleryTitle(); ?></h2>
						<?php printCodeblock(1); ?>
						<?php commonNewsLoop(false); ?>
						<p style="clear: both;"></p>
						<?php printCodeblock(2); ?>
						<?php footer(); ?>
					</div><!-- main -->
				<span class="clear"></span>
				</div><!-- left corner -->
			</div><!-- right corner -->
		</div><!-- squeeze -->
	</div><!-- center -->
	<div class="sidebar">
		<div id="rightsidebar">
			<?php
			if (function_exists('printLatestImages')) {
				?>
				<h2><?php  echo gettext('Latest Images'); ?></h2>
				<?php
				printLatestImages(7);
			}
			?>
		</div><!-- right sidebar -->
	</div><!-- sidebar -->
	<span class="clear"></span>
	</div><!-- container -->
</div><!-- wrapper -->
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
