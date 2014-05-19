<?php include ("header.php"); ?>
	
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div id="slideshowlink">
					<?php if (hasPrevImage()) { ?>
					<a href="<?php echo getPrevImageURL(); ?>" title="<?php echo gettext('Previous Image'); ?>">&larr; </a>
					<?php } ?>	
					(<?php echo imageNumber()."/".getNumImages(); ?>)
					<?php if (hasNextImage()) { ?>
					<a href="<?php echo getNextImageURL(); ?>" title="<?php echo gettext('Next Image'); ?>"> &rarr;</a>
					<?php } ?>	
				</div>
				<div>
					<?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home');?></a> &raquo; <?php printParentBreadcrumb("", " » ", " » "); printAlbumBreadcrumb("", " » "); ?></span> <?php printImageTitle(true); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="centered">
			<div id="container" itemscope itemtype="http://schema.org/Thing">	
				<div id="img-sidebar">
					<div itemprop="name" class="image-title"><?php printImageTitle(true); ?></div>
					<div class="img-date"><?php printImageDate('','',null,true); ?></div>
					<div itemprop="description" class="img-desc"><?php printImageDesc( true,'',gettext('(Edit Description...)') ); ?></div>
					<div class="img-tags"><?php printTags( 'links',gettext('TAGS:  '),'hor-list',', ',true,'',true  ); ?></div>
					
					<?php if ($zpgal_show_meta) { printImageMetadata('',false,'imagemetadata','',true,'',gettext('None Available')); } ?>
					<?php if (function_exists('printRating')) { ?>
					<div id="rating-wrap">
						<?php printRating(); ?>
						<noscript>Sorry, you must enable Javascript in your browser in order to vote...</noscript>
					</div>
					<?php } ?>
				</div>			
				<div id="img-full">
					<?php if (function_exists('printjCarouselThumbNav')) { 
					printjCarouselThumbNav(5,65,65,65,65,false);
					} else {
					if (function_exists("printPagedThumbsNav")) {
					printPagedThumbsNav(5,true,' ',' ',65,65);
					}
					} ?>
					<div id="img-wrap" itemprop="image">
						<?php if (($zpgal_final_link)=='colorbox') { ?><a rel="zoom" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printCustomSizedImageMaxSpace(getImageTitle(),490,700); ?></a><?php } ?>
						<?php if (($zpgal_final_link)=='nolink') { printCustomSizedImageMaxSpace(getImageTitle(),490,700); } ?>
						<?php if (($zpgal_final_link)=='standard') { ?><a href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printCustomSizedImageMaxSpace(getImageTitle(),490,700); ?></a><?php } ?>
						<?php if (($zpgal_final_link)=='standard-new') { ?><a target="_blank" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php printCustomSizedImageMaxSpace(getImageTitle(),490,700); ?></a><?php } ?>
					</div>
					<?php if (function_exists('printGoogleMap')) { setOption('gmap_width',490,false); printGoogleMap(); } ?>

				</div>
				<?php if (function_exists('printCommentForm')) { ?><div id="comment-wrap"><?php printCommentForm(); ?></div><?php } ?>
			</div>		
		</div>	
	</div>

<?php include("footer.php"); ?>