<?php
// force UTF-8 Ø

if (!defined('WEBPATH')) die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php printBareGalleryTitle(); ?> | <?php printBareAlbumTitle(); ?> | <?php printBareImageTitle(); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH.'/'.THEMEFOLDER; ?>/default/common.css" type="text/css" />
		<?php if (zp_has_filter('theme_head', 'colorbox::css')) { ?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function(){
					$(".colorbox").colorbox({
						inline:true,
						href:"#imagemetadata",
						close: '<?php echo gettext("close"); ?>'
					});
				});
				// ]]> -->
			</script>
		<?php } ?>
		<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
        	<p id="path">
			<?php printHomeLink('', ' > '); ?>
			<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Albums Index'); ?>">
			<?php printGalleryTitle();?></a> &gt; <?php printParentBreadcrumb("", " > ", " > "); printAlbumBreadcrumb("", " > ");
		    echo getImageTitle(); ?>
		</p>   
		<div id="main">
			<div id="gallerytitle">

			</div>
            
            
            
				<div id="prevnext">
                <?php if (hasPrevImage()) {
					?>
					<?php
				$w = getDefaultWidth();
				$h = getDefaultHeight();
				$img = $_zp_current_image->getPrevImage();
					 	?>
						<div id="prev"><span class="thumb-image"><span>
													<a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext('Previous image'); ?>">
							<h2><?php echo gettext('« Previous'); ?></h2>
                            
                            <div id="imagethumb">
							<img src="<?php echo html_encode(pathurlencode(getPrevImageThumb())); ?>" />
                            </div>
						</a>
                        
                        
							<strong style="width:<?php echo round(($w+40)/2); ?>px; height:<?php echo $h+40; ?>px;"></a></strong></a>
                            


							</em></span></span></div>
                            
                            
					<?php } ?>

					
					
					<?php
					
					 if (hasNextImage()) {
					
				$w = getDefaultWidth();
				$h = getDefaultHeight();
					$img = $_zp_current_image->getNextImage();
?>


						<div id="next"><span class="thumb-image"><span>
												<a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext('Next image'); ?>">
							<h2><?php echo gettext('Next »'); ?></h2>
                            <div id="imagethumb">
							<img src="<?php echo html_encode(pathurlencode(getNextImageThumb())); ?>" />
                            </div>
						</a>
						<strong style="width:<?php echo round(($w+20)/2); ?>px; height:<?php echo $h+20; ?>px;"></a></strong>

						</em></span></span></div> <?php } ?>
</div>
			<!-- The Image -->
			<div id="image">
            		
				<strong>
					<?php
					if (isImagePhoto()) {
						$fullimage = getFullImageURL();
					} else {
						$fullimage = NULL;
					}
					if (!empty($fullimage)) {
						?>
						<a href="<?php echo html_encode($fullimage); ?>" title="<?php printBareImageTitle(); ?>">
							<?php
						}
						if (function_exists('printUserSizeImage') && isImagePhoto()) {
							printUserSizeImage(getImageTitle());
						} else {
							printDefaultSizedImage(getImageTitle());
						}
						if (!empty($fullimage)) {
							?>
						</a>
						<?php
					}
					?>
				</strong>
				<?php
				if (isImagePhoto())
					@call_user_func('printUserSizeSelector');
				?>
                		<div id="credit">
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			<?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
			<?php
			if (function_exists('printFavoritesLink')) {
				printFavoritesLink();
				?> | <?php
			}
			?>
            ZenPapers Template by <a href="http://animepapers.org">Anime Papers |</a>
			<?php printZenphotoLink(); ?>
			<?php @call_user_func('printUserLogin_out'," | "); ?>
		</div>
			</div>
			<div id="narrow">
				<?php printImageDesc(); ?>
				<hr /><br />
				<?php
				If (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_image);
				@call_user_func('printSlideShowLink');

				if (getImageMetaData()) {
					printImageMetadata(NULL, 'colorbox');
					?>
					<br class="clearall" />
					<?php
				}
				printTags('links', gettext('<strong>Tags:</strong>') . ' ', 'taglist', '');
				?>
				<br class="clearall" />
				
				<?php @call_user_func('printGoogleMap'); ?>
				<?php @call_user_func('printRating'); ?>
				<?php @call_user_func('printCommentForm'); ?>
			</div>
		</div>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>
