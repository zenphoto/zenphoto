<?php
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
	<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery',gettext('Gallery RSS')); ?>
</head>
<body class="sidebars">
<?php zp_apply_filter('theme_body_open'); ?>
<div id="navigation"></div>
<div id="wrapper">
	<div id="container">
		<div id="header">
			<div id="logo-floater">
				<div>
					<h1 class="title"><a href="<?php echo getGalleryIndexURL(false);?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle();?></a></h1>
				</div>
			</div>
		</div>
		<!-- header -->
		<div class="sidebar">
     	<div id="leftsidebar">
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
								<a href="<?php echo getGalleryIndexURL(false);?>" title="<?php echo gettext('Gallery Index'); ?>"><?php printGalleryTitle();?></a> »
								<?php echo "<em>".gettext('Password required')."</em>"; ?>
							</h2>
							<h3><?php echo gettext('A password is required to access this page.') ?></h3>
							<?php printPasswordForm($hint, $show, false); ?>
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
zp_apply_filter('theme_body_close');
?>
</body>
</html>
