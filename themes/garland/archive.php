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
					<h1 class="title"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle());?></a></h1>
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
								<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle());?></a> » <?php echo gettext('Archive View'); ?>
							</h2>

								<div id="archive">
									<p><?php echo gettext('Images By Date'); ?></p>
									<?php printAllDates(); ?>
									<?php
									if(function_exists("printNewsArchive")) {
										?>
										<p><?php echo(gettext('News archive')); ?></p><?php printNewsArchive("archive");	?>
										<?php
									}
									?>
								</div>

							<?php footer(); ?>
							<p style="clear: both;"></p>
						</div>
						<!-- end content -->
						<span class="clear"></span> </div>
				</div>
			</div>
		</div>
		<div class="sidebar">
			<div id="rightsidebar">
				<h2>Popular Tags</h2>
				<?php printAllTagsAs('cloud', 'tags'); ?>
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
