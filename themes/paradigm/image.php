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
		<div id="center" class="row" itemscope itemtype="http://schema.org/WebPage">
		
			<section class="col-sm-9" id="main"  itemscope itemtype="http://schema.org/ImageObject">
			
		<!-- pagination -->
					<ul class="pagination pull-right">
						<?php if (hasPrevImage()) { ?>
							<li><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></li>
						<?php } if (hasNextImage()) { ?>
							<li><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></li>
						<?php } ?>
					</ul>

					<h1 itemprop="name"><?php printImageTitle(); ?></h1>

			<br clear="all"/>

			<!-- The Image -->
			<div class="text-center">
							
					<?php
					if (isImagePhoto()) {
						$fullimage = getFullImageURL();
					} else {
						$fullimage = NULL;
					}
					if (!empty($fullimage)) {
						?>
						<?php
						}
						if (isImagePhoto()) {
							echo '<img src="'. getFullImageURL() . '" alt="'. getImageTitle() . '" class="full-image"/>';
						} else {
							printDefaultSizedImage(getImageTitle());
						}
						if (!empty($fullimage)) {
							?>
						<?php
					}
					?>	
							

			</div>

			<div class="row">
				<div class="col-sm-6">
					<?php 
						if (getImageDesc()!='') {
							echo '<h2>' . gettext('Caption') . '</h2>';
							echo '<p itemprop="caption">';
							printImageDesc(); 
							echo '</p>';
						} 
					?>
					
					<?php 
						if ((getImageData('location')!='') || (getImageData('city')!='') || (getImageData('state')!='') || (getImageData('country')!='')) {
								echo '<h2>' . gettext('Location'). '</h2>';
								if (getImageData('location')!='') {
									echo '<p><strong>' . gettext('Location:'). '</strong>&nbsp;';
									echo '<span  itemprop="contentLocation">';
									echo get_language_string(getImageData('location'));
									echo '</span></p>';							
								}
								if (getImageData('city')!='') {
									echo '<p><strong>' . gettext('City:'). '</strong>&nbsp;';
									echo get_language_string(getImageData('city'));
									echo '</p>';
								}
								if (getImageData('state')!='') {
									echo '<p><strong>' . gettext('State:'). '</strong>&nbsp;';
									echo get_language_string(getImageData('state'));
									echo '</p>';
								}								
								if (getImageData('country')!='') {
									echo '<p><strong>' . gettext('Country:'). '</strong>&nbsp;';
									echo get_language_string(getImageData('country'));
									echo '</p>';
								}	
						}
					?>

					<?php
						if (getTags()) {
							echo '<h2>' . gettext('Tags') . '</h2>';
							printTags_zb('links', '', 'taglist', ', ');
						}	
					?>
					
					<?php if (function_exists('getHitCounter') || (getImageData('copyright')!='')) {
						echo '<h2>' . gettext('Other info') . '</h2>';
						if (function_exists('getHitCounter')) {
							echo '<p><strong>' . gettext('Views:') . '</strong>&nbsp;';
							echo gethitcounter();
							echo '</p>';
						}
						if (getImageData('copyright')!='') {
							echo '<p itemprop="copyrightHolder"><strong>' . gettext('Copyright:') . '</strong>&nbsp;';
							echo get_language_string(getImageData('copyright'));
							echo '</p>';
						}
						?>				
						<?php 
						if (function_exists('printAddToFavorites')) {
							printAddToFavorites($_zp_current_image);
						}
					 if (extensionEnabled('rating')) { 
						echo '<div id="rating">';
						echo '<h2>' . gettext('Rating') . '</h2>';
						printRating();
						echo '</div>'; 
						}
					}
					?>
				</div>		
				<div class="col-sm-6">
				<?php
					if (getImageMetaData()) {
						printImageMetadata_zb();
					}
				?>
				</div>	
			</div>
							
		<!-- Codeblock 1 -->
			<?php printCodeBlock(1);?>						

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

			<br style="clear:both" />
			
			<?php @call_user_func('printCommentForm'); ?>

			</section>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>				
	</div>
</div>
<!-- end of content row -->
	

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>