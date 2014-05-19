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
					<?php $x=$x+1; } else { $x=$x; } ?>
					<?php endwhile; ?>
				</div>
				<?php } ?>
				<div>
					<?php echo "<em>".gettext("Search")."</em> &raquo "; ?>
					<?php if (($total = getNumImages() + getNumAlbums()) > 0) {
					if (isset($_REQUEST['date'])){
					$searchwords = getSearchDate();
					} else { $searchwords = getSearchWords(); }
					echo sprintf(gettext('Total matches for <em>%1$s</em>: %2$u'), html_encode($searchwords), $total); 
					} else { echo gettext("Sorry, no matches. Try refining your search."); 
					} ?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="wrapper">
		<div class="centered">
			<div id="sidebar">
				<h4><?php echo gettext('Gallery Archive'); ?></h4>
				<?php printAllDates('archive-menu','year','month','desc'); ?>
			</div>
			<div id="archive-bar">
				<div id="search-top">
					<?php printSearchForm( '','searchform','',gettext('Search'),"$_zp_themeroot/images/drop.gif",null,null,"$_zp_themeroot/images/reset.gif" ); ?>
				</div>
				<?php if (function_exists('printAlbumMenu')) { ?>
				<div id="albumjump">
					<?php printAlbumMenu('jump',true); ?>
				</div>
				<?php } ?>	
			</div>
			<?php if (isAlbumPage()) { ?>
			<div id="album-wrap" class="subalbums withsidebar">
				<ul>
					<?php $lastcolnum=2;$listall='';setOption('albums_per_row','2',false); ?>
					<?php $x=1; while (next_album()): $lastcol=""; 
					if ($x==$lastcolnum) {$lastcol=" class='lastcol'"; $x=0;} ?>
					<li<?php echo $lastcol; ?>>	
						<a class="album-thumb" href="<?php echo htmlspecialchars(getAlbumURL());?>" title="<?php echo gettext('View album:'); ?> <?php echo getBareAlbumTitle();?>"><?php printCustomAlbumThumbImage(getBareAlbumTitle(),NULL,238,100,238,100); ?></a>
						<h4><a href="<?php echo htmlspecialchars(getAlbumURL());?>" title="<?php echo getBareAlbumTitle().' ('.getAlbumDate().') - '.getAlbumDesc(); ?>"><?php echo shortenContent(getBareAlbumTitle(),30,'...'); ?></a></h4>				
					</li>
					<?php $x++; endwhile; ?>					
				</ul>
			</div>
			<?php } ?>
			<?php if (getNumImages() > 0) { ?>
			<div id="alt-thumbs" class="withsidebar">
				<ul class="alt-thumbs">
					<?php while (next_image()): ?>
					<li style="width:<?php echo getOption('thumb_size'); ?>px;height:<?php echo getOption('thumb_size'); ?>px;">
						<a href="<?php echo htmlspecialchars(getImageLinkURL());?>" title="<?php echo getImageTitle(); ?><?php if (getCommentCount() > 0) { echo ' - '.getCommentCount().' '.gettext('Comment(s)'); } ?>"><?php printImageThumb(getAnnotatedImageTitle()); ?></a>
						<a rel="zoom" class="zoom-overlay" href="<?php if ($zpgal_cbtarget) { echo htmlspecialchars(getDefaultSizedImage()); } else { echo htmlspecialchars(getUnprotectedImageURL()); } ?>" title="<?php echo getBareImageTitle();?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
					</li>
					<?php endwhile; ?>
				</ul>
			</div>
			<?php } ?>
			<div class="paging">
				<?php if ( (getPrevPageURL()) || (getNextPageURL()) ) { ?>
				<?php printPageListWithNav( gettext('‹ Previous'),gettext('Next ›'),false,'true','pagelist','',true,'5' ); ?>
				<?php } ?>
			</div>
		</div>
	</div>
	
<?php include("footer.php"); ?>


