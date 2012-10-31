<?php
if (!defined('WEBPATH') || !function_exists('printContactForm')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo gettext('Contact'); ?></title>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle());?></a></h1>
				</div>
			</div>
		</div>
		<!-- header -->
		<div class="sidebar">
     	<div id="leftsidebar">
      	<?php include("sidebar.php"); ?>
      </div>
     </div>

		<div id="center">
			<div id="squeeze">
				<div class="right-corner">
					<div class="left-corner">
						<!-- begin content -->
						<div class="main section" id="main">
							<h2 id="gallerytitle">
							<?php printHomeLink('',' » '); ?>
							<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a> »
							<?php echo gettext('Contact us'); ?>
							</h2>
							<em><?php  printContactForm(); ?></em>
							<?php footer(); ?>
							<p style="clear: both;"></p>
						</div>
						<!-- end content -->
						<span class="clear"></span> </div>
				</div>
			</div>
		</div>
		<span class="clear"></span>
	</div><!-- /container -->
</div>
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
