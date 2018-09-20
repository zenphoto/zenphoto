<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php if (getOption('full_width')) {echo '-fluid';}?>">
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>
		<div id="center" class="row" itemscope itemtype="http://schema.org/ImageGallery">
			<section class="col-sm-9" id="main" itemprop="mainContentOfPage">
				
				<h1 itemprop="name"><?php printAlbumTitle(); ?></h1>

				<div itemprop="description" class="content"><?php printAlbumDesc(); ?></div>
								
				<?php include("includes/_albumlist.php"); ?>

				<?php include("includes/_imagethumbs.php"); ?>
				
					<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>
				
			<!-- Tags -->
				<?php
					if (getTags()) {
						echo '<h2>' . gettext('Tags') . '</h2>';
						printTags_zb('links', '', 'taglist', ', ');
					}	
				?>

				<?php if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album); ?>

			<!-- Rating -->	
				<?php
					if (extensionEnabled('rating')) { 
						echo '<div id="rating">';
						echo '<h2>' . gettext('Rating') . '</h2>';
						printRating();
						echo '</div>';
					}
				?>	

			<!-- Codeblock 1 -->
				<?php  
					printcodeblock (1, $_zp_current_album);
				?>  
				<?php  
					printcodeblock (2, $_zp_current_album);
				?>	
				
				<?php
					if (function_exists('printGoogleMap')) {
						printGoogleMap("","","show");
					}
				?>

				<?php
					if (function_exists('printOpenStreetMap')) {
						printOpenStreetMap();
					}
				?>		

			<!-- album location -->
				<?php if (getAlbumLocation()!='') {
					echo '<p><strong>' . gettext('Location') . '</strong><br/><span itemprop="contentLocation">';
					printAlbumLocation();
					echo '</span>';
					} 
				?>

				<br style="clear:both;" />

				<?php @call_user_func('printCommentForm'); ?>
				
			</section>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>	
</div>			

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>		