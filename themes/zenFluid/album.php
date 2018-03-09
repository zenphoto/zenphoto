<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
zp_apply_filter('theme_file_top');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php include("inc-head.php");?>
	</head>
	<body>
		<?php 
		include("inc-header.php");
		$doSlideShowLink = false;
		$thumbstageStyle = (getOption('zenfluid_stagethumb')) ? $stageStyle : '';
		if (getOption('zenfluid_titletop')) { 
			?>
			<div class="stage" <?php echo $stageStyle;?>>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<?php 
					if (getOption('zenfluid_titlebreadcrumb')) {
						printParentBreadcrumb("", " : ", " : ");
					} 
					?>
					<strong><?php printAlbumTitle(); ?></strong>
				</div>
			</div>
			<?php
		}
		?>
		<div class="thumbstage" <?php echo $thumbstageStyle;?>>
			<?php
			while (next_album()) {
				?>
				<div class="thumbs border colour">
					<a href="<?php echo html_encode(getAlbumURL());?>" title="<?php echo gettext('View: '); printBareAlbumTitle();?>">
						<div class="thumbimage">
							<?php 
							printAlbumThumbImage(getBareAlbumTitle(),"border"); 
							if (getOption('zenfluid_thumbdesc')) {
								?>
								<div class="thumbtitle">
									<?php 
									$numItems = getNumImages() + getNumAlbums();
									printAlbumTitle(); echo ' (' . $numItems . ')';
									echo "<p>" . shortenContent(strip_tags(getAlbumDesc()), 150, getOption("zenpage_textshorten_indicator")) . "</p>";
									?>
								</div>
								<?php 
							} 
							?>
						</div>
					</a>
				</div>
				<?php 
			}
			if (getOption('zenfluid_transitionnewrow') && getNumAlbums() > 0 && getNumImages() > 0) {
				?>
				<div class="clearing"></div>
				<?php 
			}
			while (next_image()) {
				if (isImagePhoto()) {
					$doSlideShowLink = true;
				}
				?>
				<div class="thumbs border">
					<a href="<?php echo html_encode(getImageURL());?>" title="<?php echo gettext('View: '); printBareImageTitle();?>">
						<div class="thumbimage">
							<?php 
							printImageThumb(getBareImageTitle(),"border");
							if (isImageVideo()) { 
								?>
								<img class="videoplay" src="<?php echo $_zp_themeroot; ?>/images/videoplay.png">
								<?php 
							}
							if (getOption('zenfluid_thumbdesc')) {
								?>
								<div class="thumbtitle">
									<?php 
									printImageTitle();
									echo "<p>" . shortenContent(strip_tags(getImageDesc()), 150, getOption("zenpage_textshorten_indicator")) . "</p>"; 
									?>
								</div>
								<?php 
							} 
							?>
						</div>
					</a>
				</div>
				<?php 
			}
			?>
		</div>
		<div class="clearing"></div>
		<div class="stage" <?php echo $stageStyle;?>>
			<?php 
			if (getOption('zenfluid_buttontitle')) printButtons();
			if (!getOption('zenfluid_titletop')) { 
				?>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<?php 
					if (getOption('zenfluid_titlebreadcrumb')) {
						printParentBreadcrumb("", " : ", " : ");
					} 
					printAlbumTitle(); 
					?>
				</div>
				<?php 
			}
			if (getAlbumDesc()) { 
				?>
				<div class="content border colour">
					<div class="imagedesc" <?php echo $descriptionStyle;?>>
						<div class="description" <?php echo $justifyStyle;?>>
							<?php printAlbumDesc(); ?>
						</div>
					</div>
				</div>
				<?php 
			}
			if (!getOption('zenfluid_buttontitle')) printButtons();
			if(getTags()) {
				?>
				<div class="albumbuttons" <?php echo $buttonStyle;?>>
					<div class="button border colour">
						<?php printTags('links', gettext('Tags: '), 'taglist', ', ');?>
					</div>
				</div>
				<?php 
			} 
			?>
		</div>
		<?php 
		include("inc-footer.php");
		?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end');

function printButtons() {
	global $_zp_current_album, $buttonStyle, $doSlideShowLink;
	if (hasPrevPage() || hasNextPage() || (getNumImages() > 1 && $doSlideShowLink && function_exists('printSlideShowLink'))) { 
		?>
		<div class="albumbuttons" <?php echo $buttonStyle;?>>
			<?php 
			if (hasPrevPage() || hasNextPage()) { 
				?>
				<div class="button border colour">
					<?php printPageListWithNav("Prev ", " Next", false, true, 'taglist', NULL, true); ?>
				</div>
				<?php 
			}
			if (getNumImages() > 1 && $doSlideShowLink && function_exists('printSlideShowLink')) { 
				?>
				<div class="button border colour">
					<div class="slideshowlink">
						<?php printSlideShowLink();?>
					</div>
				</div>
				<?php 
			} 
			?>
		</div>
		<div class="clearing"></div>
		<?php 
	}
}
?>
