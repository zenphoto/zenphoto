<?php include ("header.php"); ?>
	
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					Archive &raquo;
					<?php switch ($zpgal_archiveoption) {
						case "random":
						echo gettext('Random Images');
					break;
						case "popular":
						echo gettext('Popular Images');
					break;
						case "latest":
						echo gettext('Latest Images');
					break;
						case "latest-date":
						echo gettext('Latest Images');
					break;
						case "latest-mtime":
						echo gettext('Latest Images');
					break;
						case "mostrated":
						echo gettext('Most Rated Images');
					break;
						case "toprated":
						echo gettext('Top Rated Images');
					break;
					} ?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="wrapper" id="album-alt">
		<div class="centered">
			<div id="sidebar">
				<h4><?php echo gettext('Gallery Archive'); ?></h4>
				<?php printAllDates('archive-menu','year','month','desc'); ?>
				<h4><?php echo gettext('Popular Tags'); ?></h4>
				<div id="tag_cloud">
					<?php printAllTagsAs('cloud', 'tags'); ?>
				</div>
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
	
			<div id="alt-thumbs" class="withsidebar">
				<?php if (is_numeric($zpgal_archivecount)) {$archivecount=$zpgal_archivecount;} else {$archivecount=12;} ?>
				<ul class="alt-thumbs">
					<?php if (($zpgal_archiveoption) != 'random') { ?>
					<?php $images = getImageStatistic($archivecount,$zpgal_archiveoption);
					foreach ($images as $image) { ?>
					<li style="width:<?php echo getOption('thumb_size'); ?>px;height:<?php echo getOption('thumb_size'); ?>px;">
						<a href="<?php echo html_encode($image->getLink()); ?>" title="<?php echo html_encode($image->getTitle()); ?>">
							<img src="<?php if (getOption('thumb_crop')) { echo html_encode($image->getCustomImage(null,getOption('thumb_size'),getOption('thumb_size'),getOption('thumb_size'),getOption('thumb_size'),null,null,true)); } else { echo html_encode($image->getCustomImage(getOption('thumb_size'),null,null,null,null,null,null,true)); } ?>" alt="<?php echo html_encode($image->getTitle()); ?>" />
						</a>
						<a rel="zoom" class="zoom-overlay" href="<?php if ($zpgal_cbtarget) { echo html_encode($image->getSizedImage(getOption('image_size'))); } else { echo html_encode($image->getFullImage()); } ?>" title="<?php echo html_encode($image->getTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
					</li>
					<?php } ?>
					<?php } else { ?>
					<?php for ($i=1; $i<=$archivecount; $i++) { 
					$randomImage = getRandomImages(); 
					if (is_object($randomImage) && $randomImage->exists) {
					$randomImageURL = html_encode(getURL($randomImage)); ?>
					<li style="width:<?php echo getOption('thumb_size'); ?>px;height:<?php echo getOption('thumb_size'); ?>px;">
						<a href="<?php echo html_encode($randomImage->getLink()); ?>" title="<?php echo html_encode($randomImage->getTitle()); ?>">
							<img src="<?php if (getOption('thumb_crop')) { echo html_encode($randomImage->getCustomImage(null,getOption('thumb_size'),getOption('thumb_size'),getOption('thumb_size'),getOption('thumb_size'),null,null,true)); } else { echo html_encode($randomImage->getCustomImage(getOption('thumb_size'),null,null,null,null,null,null,true)); } ?>" alt="<?php echo html_encode($randomImage->getTitle()); ?>" />
						</a>
						<a rel="zoom" class="zoom-overlay" href="<?php if ($zpgal_cbtarget) { echo html_encode($randomImage->getSizedImage(getOption('image_size'))); } else { echo html_encode($randomImage->getFullImage()); } ?>" title="<?php echo html_encode($randomImage->getTitle()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/zoom.png" /></a>
					</li>
					<?php } ?>
					<?php } ?>
					<?php } ?>
				</ul>	
			</div>
			
		</div>
	</div>		
	
<?php include("footer.php"); ?>
