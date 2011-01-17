<?php
if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<title><?php printGalleryTitle(); ?> | <?php echo gettext('Search'); ?></title>
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
              	<h3 id="gallerytitle"><a href="<?php echo html_encode(getGalleryIndexURL(false)); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo html_encode(getGalleryTitle()); ?></a> &raquo; Search</h3>

				<?php
				if ($_REQUEST['words']) {
		  		  if (($total = getNumImages() + getNumAlbums()) > 0) {
	  	    	    echo "<p>".sprintf(gettext('Total matches for <em>%s</em>: %u'),getSearchWords(), $total)."</p>";
				?>
				<div id="albums">
				<?php while (next_album()): ?>
				  <div class="album">
					<div class="imagethumb">
						<a href="<?php echo getAlbumLinkURL();?>" title="<?php printf(gettext('View album: %s'),sanitize(getAlbumTitle())); ?>"><?php printCustomAlbumThumbImage(getAlbumTitle(),85,NULL,NULL,77,77); ?></a>
					</div>
					<div class="albumdesc">
						<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php printf(gettext('View album: %s'),sanitize(getAlbumTitle()));?>"><?php printAlbumTitle(); ?></a></h3>
						<p><?php printAlbumDesc(); ?></p>
						<small><?php printAlbumDate(gettext("Date Taken: ")); ?></small>
					</div>
					<p style="clear: both; "></p>
				</div>
			  <?php endwhile; ?>
			  </div>

			  <div id="images">
				  <?php while (next_image()): ?>
				  <div class="image">
					  <div class="imagethumb"><a href="<?php echo html_encode(getImageLinkURL()); ?>" title="<?php echo sanitize(getImageTitle()); ?>"><?php printImageThumb(getImageTitle()); ?></a></div>
				  </div>
				  <?php endwhile; ?>
			 </div>
			<?php
	  	      } else {
	  	        echo "<p>".gettext('Sorry, no matches for your search. Try refining your criteria')."</p>";
		      }
		    }
    	    printPageListWithNav(gettext("&laquo; prev"),gettext("next &raquo;"));
	        footer();
	        ?>
            <div style="clear: both;"></div>
            </div>
            <!-- end content -->
            <span class="clear"></span>
        </div>
      </div>
    </div>
    <div class="sidebar">
      <div id="rightsidebar">
        <h2><?php echo gettext('Album Navigation'); ?></h2>
				<?php printLink(getNextAlbumURL(), gettext("Next Album &raquo;")); ?><br />
       	<?php printLink(getPrevAlbumURL(), gettext("Prev Album &laquo;")); ?>
      </div>
    </div>
    <span class="clear"></span>
	</div>
<?php
printAdminToolbox();
zp_apply_filter('theme_body_close');
?>
</body>
</html>
