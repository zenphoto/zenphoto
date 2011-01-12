<?php
if (!defined('WEBPATH')) die();
require_once (ZENFOLDER.'/'.PLUGIN_FOLDER.'/image_album_statistics.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo gettext('Search'); ?></title>
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
              	<h3 id="gallerytitle"><a href="<?php echo getGalleryIndexURL();?>" title="Gallery Index"><?php echo getGalleryTitle();?></a> &raquo; Search</h3>

				<?php
				if ($_REQUEST['words']) {
		  		  if (($total = getNumImages() + getNumAlbums()) > 0) {
	  	    	    echo "<p>Total matches for <em>".getSearchWords()."</em>: $total</p>";
				?>
				<div id="albums">
				<?php while (next_album()): ?>
				  <div class="album">
					<div class="imagethumb">
						<a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>"><?php printAlbumThumbImage(getAlbumTitle()); ?></a>
					</div>
					<div class="albumdesc">
						<h3><a href="<?php echo getAlbumLinkURL();?>" title="View album: <?php echo getAlbumTitle();?>"><?php printAlbumTitle(); ?></a></h3>
						<p><?php printAlbumDesc(); ?></p>
						<small><?php printAlbumDate("Date Taken: "); ?></small>
					</div>
					<p style="clear: both; "></p>
				</div>
			  <?php endwhile; ?>
			  </div>

			  <div id="images">
				  <?php while (next_image(false, $firstPageImages)): ?>
				  <div class="image">
					  <div class="imagethumb"><a href="<?php echo getImageLinkURL();?>" title="<?php echo getImageTitle();?>"><?php printImageThumb(getImageTitle()); ?></a></div>
				  </div>
				  <?php endwhile; ?>
			 </div>
			<?php
	  	      } else {
	  	        echo "<p>Sorry, no matches for your search. Try refining your criteria.</p>";
		      }
		    }
    	    printPageListWithNav("&laquo; prev","next &raquo;");
	        footer();
	        ?>
            <div style="clear: both;"></div>
            </div>
            <!-- end content -->
            <span class="clear"></span> </div>
        </div>
      </div>
    </div>
    <div class="sidebar">
      <div id="rightsidebar">
        <h2>Album Navigation</h2>
		<?php printLink(getNextAlbumURL(), "Next Album &raquo;"); ?><br />
        <?php printLink(getPrevAlbumURL(), "Prev Album &laquo;"); ?>
      </div>
    </div>
    <span class="clear"></span> </div>
  <!-- /container -->
</div>
<?php printAdminToolbox(); ?>
</body>
</html>
