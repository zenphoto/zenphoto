<?php
if (!defined('WEBPATH'))
	die();
	?>
	<!DOCTYPE html>
	<html<?php printLangAttribute(); ?>>
		<head>
			<meta charset="<?php echo LOCAL_CHARSET; ?>">
			<?php zp_apply_filter('theme_head'); ?>
			<?php printHeadTitle(); ?>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/zen.css" type="text/css" />
			<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
		</head>
		<body class="sidebars">
			<?php zp_apply_filter('theme_body_open'); ?>
			<div id="navigation"></div>
			<div id="wrapper">
				<div id="container">
					<div id="header">
						<div id="logo-floater">
							<div>
								<h1 class="title">
									<a href="<?php echo html_encode(getSiteHomeURL()); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a>
								</h1>
								<span id="galleryDescription"><?php printGalleryDesc(); ?></span>
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
											<?php printHomeLink('', ' » '); printGalleryIndexURL(' » '); printZenpageItemsBreadcrumb("", " » "); printPageTitle(""); ?>
										</h2>
										<h3><?php printPageTitle(); ?></h3>
										<div id="pagetext">
											<?php printCodeblock(1); ?>
											<?php printPageContent(); ?>
											<?php printCodeblock(2); ?>
										</div>
										<?php
										callUserFunction('printRating');
										callUserFunction('printCommentForm');
										footer();
										?>
										<p style="clear: both;"></p>
									</div>
									<!-- end content -->
									<span class="clear"></span> </div>
							</div>
						</div>
					</div>
					<span class="clear"></span>
					<div class="sidebar">
						<div id="rightsidebar">
							<?php printTags('links', gettext('Tags: '), NULL, ''); ?>
						</div><!-- right sidebar -->
					</div><!-- sidebar -->
				</div><!-- /container -->
			</div>
			<?php
			zp_apply_filter('theme_body_close');
			?>
		</body>
	</html>