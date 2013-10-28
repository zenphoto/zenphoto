<?php
if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	?>
	<!DOCTYPE html>
	<html>
		<head>
	<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
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
								<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a></h1>
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
											<?php printHomeLink('', ' » '); ?>
											<a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a> »
	<?php echo gettext('Contact us'); ?>
										</h2>
										<em><?php printContactForm(); ?></em>
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
	<?php
} else {
	include(dirname(__FILE__) . '/404.php');
}
?>