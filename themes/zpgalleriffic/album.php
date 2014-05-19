<?php include ("header.php"); ?>
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<?php if (getNumImages() > 0) { ?>
				<div id="slideshowlink">
					<?php $x=0; while (next_image(true)): 
					if ($x>=1) { $show='style="display:none;"'; } else { $show='';}  ?>
					<?php if (!isImageVideo()) { ?>
					<a class="slideshowlink"<?php echo $show; ?> rel="slideshow" href="<?php echo htmlspecialchars(getFullImageURL());?>" title="<?php echo getBareImageTitle();?>"><?php echo gettext('Play Slideshow'); ?></a>
					<?php $x=$x+1; $imagepresent=true; } else { $x=$x; $videopresent=true;} ?>
					<?php endwhile; ?>
				</div>
				<?php } ?>
				<div>
					<?php printParentBreadcrumb('',' » ',' » ',30,'...'); ?> <?php echo shortenContent(printAlbumTitle(true),30,'...'); ?>
					<small>(
					<?php if ((getNumAlbums()) > 0) { echo getNumAlbums().gettext(' Subalbums, '); } ?>
					<?php echo getTotalImagesIn($_zp_current_album).gettext(' Total Images'); ?>
					)</small>
				</div>
			</div>
		</div>
	</div>
	
	<?php if (isAlbumPage()) { ?>
	<div class="wrapper">
		<div class="centered">	
			<div id="album-wrap" class="subalbums<?php if (!$zpgal_nogal) { ?> withsidebar<?php } ?>">
				<ul>
					<?php if ($zpgal_nogal) {$lastcolnum=3;$listall='true';setOption('albums_per_row','3',false);} else {$lastcolnum=2;$listall='';setOption('albums_per_row','2',false);} ?>
					<?php $x=1; while (next_album($listall)): $lastcol=""; 
					if ($x==$lastcolnum) {$lastcol=" class='lastcol'"; $x=0;} ?>
					<li<?php echo $lastcol; ?>>	
						<a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(),NULL,238,100,238,100); ?></a>
						<h4><a href="<?php echo htmlspecialchars(getAlbumURL());?>" title="<?php echo getBareAlbumTitle().' ('.getAlbumDate().') - '.getAlbumDesc(); ?>"><?php echo shortenContent(getBareAlbumTitle(),30,'...'); ?></a></h4>				
					</li>
					<?php $x++; endwhile; ?>				
				</ul>
			</div>
		</div>
	</div>
	<?php } ?>
	
	<?php if (getNumImages() > 0){ ?>
	
	<?php if ($zpgal_nogal) { ?>
	<?php if ($imagepresent) { ?>
	<div class="wrapper">
		<div class="centered">
			<div class="container">
				<div class="content">
					<div class="slideshow-container">
						
						<div id="loading" class="loader"></div>
						<div id="slideshow" class="slideshow"></div>
					</div>
					<div id="caption" class="caption-container">
						<div id="albumdesc"><?php echo shortenContent(getAlbumDesc(),500,'...'); ?></div>
						<div><?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'hor-list', ', '); ?></div>	
						<div id="controls" class="controls"></div>
						<div class="photo-index"></div>
					</div>
				</div>
			</div>
			<!-- If javascript is disabled in the users browser, the following message will display  -->
			<noscript>
				<?php echo gettext('Sorry, please enable Javascript in your browser to view our gallery.'); ?>
			</noscript>
			<!-- End of noscript display -->
		</div>
	</div>
	<?php $navheight=getOption('thumb_size'); ?>
	<style>
		div.navigation a.pageLink {
			height: <?php echo $navheight; ?>px;
			line-height: <?php echo $navheight; ?>px;
		}
	</style>
	<div class="wrapper" id="thumbstrip">
		<div class="centered">
			<div class="">						
				<div class="navigation-container">
					<div id="thumbs" class="navigation">
						<a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>
					
						<ul class="thumbs noscript">
							<?php while (next_image(true)): ?>
							<li>
								<?php if (!isImageVideo()) { ?>
								<?php if ($zpgal_crop) { ?>
								<a name="<?php echo $_zp_current_image->getFileName(); ?>" class="thumb" href="<?php echo getCustomImageURL(475,null,null,475,475,null,null,true,null); ?>" title="<?php echo getBareImageTitle();?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>		
								<?php } else { ?>
								<a name="<?php echo $_zp_current_image->getFileName(); ?>" class="thumb" href="<?php echo getCustomImageURL(475,null,null,null,null,null,null,true,null); ?>" title="<?php echo getBareImageTitle();?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>		
								<?php } ?>
								<a rel="zoom" href="<?php if ($zpgal_cbtarget) { echo htmlspecialchars(getDefaultSizedImage()); } else { echo htmlspecialchars(getUnprotectedImageURL()); } ?>" title="<?php echo getBareImageTitle();?>"></a>
								<?php } ?>
								<div class="caption">
									<div class="image-title"><?php printImageTitle(false); ?></div>
									<?php if (strlen(getImageDesc()) > 0) { ?>
									<div class="image-desc">
										<?php echo shortenContent(getImageDesc(),300,'...'); ?>		
									</div>
									<?php } ?>
									<div><?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'hor-list', ', ',false); ?></div>
									<div class="download">
										<?php if (isImageVideo()) { $downLoadText=gettext('Video'); } else { $downLoadText=gettext('Image'); } ?>
										<a class="details-link button" href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo gettext('Detail Page: '); ?><?php echo getImageTitle(); ?>"><?php echo $downLoadText.gettext(' Details'); ?></a>
										
										<?php if ($zpgal_download_link) { ?><a class="download-link button" target="_blank" href="<?php echo htmlspecialchars(getUnprotectedImageURL());?>" title="<?php echo gettext('Download: '); ?> <?php echo getImageTitle(); ?>"><?php echo gettext('Download ').$downLoadText; ?></a><?php } ?>
									</div>
								</div>
							</li>
							<?php endwhile; ?>
						</ul>
						<a class="pageLink next" style="visibility: hidden;" href="#" title="Next Page"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	
	<?php if ($videopresent) { ?>
	<div class="wrapper">
		<div class="centered">
			<div class="breadcrumbs">
				<span><?php echo gettext('Videos'); ?></span>
			</div>
			<br />
			<div id="alt-thumbs">
				<ul class="alt-thumbs">
					<?php while (next_image(true)): ?>
					<?php if (isImageVideo()) { ?>
					<li style="width:<?php echo getOption('thumb_size'); ?>px;height:<?php echo getOption('thumb_size'); ?>px;">
						<a href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo getImageTitle(); ?><?php if (getCommentCount() > 0) { echo ' - '.getCommentCount().' '.gettext('Comment(s)'); } ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
					</li>
					<?php } ?>
					<?php endwhile; ?>
				</ul>
			</div>
		</div>
	</div>
	<?php } ?>
	
	<?php } ?>
	
	<?php if (!$zpgal_nogal) { // displays alternate album view WITHOUT galleriffic script, setting in theme options. ?>
	<div class="wrapper" id="album-alt">
		<div class="centered">
			<div id="sidebar">
				<div><?php printAlbumDesc(true); ?></div>
				<div><?php printTags('links', gettext('<strong>Tags:</strong>').' ', 'hor-list', ', '); ?></div>
			</div>
			<div id="alt-thumbs" class="withsidebar">
				<ul class="alt-thumbs">
					<?php while (next_image()): ?>
					<li style="width:<?php echo getOption('thumb_size'); ?>px;height:<?php echo getOption('thumb_size'); ?>px;">
						<a href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo getImageTitle(); ?><?php if (getCommentCount() > 0) { echo ' - '.getCommentCount().' '.gettext('Comment(s)'); } ?>">
							<span class="overlay">
							<?php printImageThumb(getAnnotatedImageTitle()); ?>
							<a rel="zoom" class="zoom-overlay" href="<?php if ($zpgal_cbtarget) { echo htmlspecialchars(getDefaultSizedImage()); } else { echo htmlspecialchars(getUnprotectedImageURL()); } ?>" title="<?php echo getBareImageTitle();?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
							</span>
						</a>
					</li>
					<?php endwhile; ?>
				</ul>
			</div>
			<div class="paging">
				<?php if ( (getPrevPageURL()) || (getNextPageURL()) ) { ?>
				<?php printPageListWithNav( gettext('‹ Previous'),gettext('Next ›'),false,'true','pagelist','',true,'5' ); ?>
				<?php } ?>
				<?php if (function_exists('printAlbumMenu')) { ?>
				<div id="albumjump">
					<?php printAlbumMenu('jump',true); ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>					
	<?php } ?>
			
	<?php } // end of thumb display options... ?>
			
	<div class="wrapper">
		<div class="centered">	
			<?php if (function_exists('printGoogleMap')) { setOption('gmap_width',800,false); printGoogleMap(); } ?>
			<?php if (function_exists('printRating')) { ?>
			<div id="rating-wrap">
				<?php printRating(); ?>
				<noscript>Sorry, you must enable Javascript in your browser in order to vote...</noscript>
			</div>
			<?php } ?>
			<?php if (function_exists('printCommentForm')) { ?><div id="comment-wrap"><?php printCommentForm(); ?></div><?php } ?>
			<?php if ($_zp_current_album->getParent()) { $linklabel=gettext('Subalbum'); } else { $linklabel=gettext('Album'); } ?>
			<div id="navbar-prev">
				<?php $albumnav = getPrevAlbum();
				if (!is_null($albumnav)) { ?>
				<a class="button" href="<?php echo getPrevAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo '&larr; '.$linklabel.': '.shortenContent($albumnav->getTitle(),30,'...'); ?></a>
				<?php } ?>
			</div>
			<div id="navbar-next">
				<?php $albumnav = getNextAlbum();
				if (!is_null($albumnav)) { ?>
				<a class="button" href="<?php echo getNextAlbumURL(); ?>" title="<?php echo html_encode($albumnav->getTitle()); ?>"><?php echo $linklabel.': '.shortenContent($albumnav->getTitle(),30,'...').' &rarr;'; ?></a>
				<?php } ?>
			</div>
		</div>
	</div>			
			
<?php include("footer.php"); ?>