<?php
if (!defined('WEBPATH')) die();
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_album_statistics.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
<title><?php printGalleryTitle(); ?> | Archive View</title>
<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/zen.css" type="text/css" />
<?php printRSSHeaderLink('Gallery','Gallery RSS'); ?>
</head>
<body class="sidebars">
<div id="navigation"></div>
<div id="wrapper">
  <div id="container">
    <div id="header">
      <div id="logo-floater">
        <div>
          <h1 class="title"><a href="<?php echo getGalleryIndexURL();?>" title="Gallery Index"><?php echo getGalleryTitle();?></a></h1>
        </div>
      </div>
    </div>
    <!-- header -->
    <?php sidebarMenu(); ?>
    <div id="center">
      <div id="squeeze">
        <div class="right-corner">
          <div class="left-corner">
            <!-- begin content -->
            <div class="main section" id="main">
              <h3 id="gallerytitle"><a href="<?php echo getGalleryIndexURL();?>" title="Gallery Index"><?php echo getGalleryTitle();?></a> &raquo; Archive View</h3>
              <div id="image_container">
              	<div id="archive">
									<p><?php echo gettext('Images By Date'); ?></p>
									<?php printAllDates(); ?>
									<?php
									if(function_exists("printNewsArchive")) {
										?>
										<p><?php echo('News archive') ?></p><?php printNewsArchive("archive");	?>
										<?php
									}
									?>
                </div>
              </div>
  			  		<?php footer(); ?>
              <div style="clear: both;"></div>
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
    <span class="clear"></span> </div>
  <!-- /container -->
</div>
<?php printAdminToolbox(); ?>
</body>
</html>
