<?php
// force UTF-8 Ø
if (!defined('WEBPATH')) die();
zp_apply_filter('theme_file_top')
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
		$commentCount = function_exists('printCommentForm') ? getCommentCount() : 0;
		echo CommentsJS($commentCount);
		$titleMargin = getOption("zenfluid_titlemargin");
		if (getOption('zenfluid_titletop')) {
			$titleMargin = $titleMargin - 20;
			?>
			<div class="title border colour" <?php echo $titleStyle;?>>
				<?php
				if (getOption('zenfluid_titlebreadcrumb')) {
					printParentBreadcrumb("", " : ", " : ");
					printAlbumBreadcrumb("  ", " : ");
				}
				?>
				<strong><?php printImageTitle(); ?></strong>
			</div>
			<?php 
		}
		if (isImagePhoto()) {
			$doSlideShowLink = true;
			echo ImageJS($titleMargin,$stageWidth,getOption('zenfluid_stageimage'));
			if (zp_has_filter('theme_head', 'colorbox::css')) {
				echo colorBoxJS();
			} 
			?>
			<div class="image" <?php echo $imageStyle;?>>
				<?php 
				if (getOption("Use_thickbox")) {
					$boxclass = 'class="thickbox"';
				} else {
					$boxclass = "";
				}
				$tburl = getFullImageURL();
				if (!empty($tburl)) {
					echo '<a href="' . pathurlencode($tburl) . '" ' . $boxclass . ' title="' . getBareImageTitle() . '">' . "\n";
				}
				printCustomSizedImageMaxSpace(getBareImageTitle(),null,null,"imgheight border");
				if (!empty($tburl)) {
					echo "\n</a>\n";
				}
				?>
			</div>
			<?php 
		} else {
			$metadata = getImageMetaData(NULL,false);
			$vidWidth = $metadata['VideoResolution_x'];
			$vidHeight = $metadata['VideoResolution_y'];
			echo vidJS($vidWidth, $vidHeight, $titleMargin, $stageWidth, getOption('zenfluid_stageimage'),$commentCount);
			//jPlayer adds a 40 px controls bar below the video. Others add the bar in the video.
			$playerMarginBottom = (extensionEnabled('jPlayer')) ? 'style="margin-bottom: 44px;"' : ''; 
			?>
			<div class="videocontainer" <?php echo $playerMarginBottom; ?>>
				<div class="video" <?php echo $stageStyle;?>>
					<?php printCustomSizedImageMaxSpace(getBareImageTitle(),null,null); ?>
				</div>
			</div>
			<?php 
		} 
		?>
		<div class="stage" <?php echo $stageStyle;?>>
			<?php 
			if (getOption('zenfluid_buttontitle')) printButtons();
			if (!getOption('zenfluid_titletop')) {
				?>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<?php 
					if (getOption('zenfluid_titlebreadcrumb')) {
						printParentBreadcrumb("", " : ", " : ");
						printAlbumBreadcrumb("  ", " : ");
					} 
					printImageTitle();
					?>
				</div>
				<?php 
			}
			if (getImageDesc()) { 
				?>
				<div class="content border colour">
					<div class="imagedesc" <?php echo $descriptionStyle;?>>
						<div class="description" <?php echo $justifyStyle;?>>
							<?php printImageDesc(); ?>
						</div>
					</div>
				</div>
				<?php 
			}
			if (!getOption('zenfluid_buttontitle')) printButtons(); 
			if (function_exists('printCommentForm') && ($_zp_current_image->getCommentsAllowed() || $commentCount)) { 
				?>
				<a id="readComment"></a>
				<div class="content border colour">
					<div class="commentbox" <?php echo $commentStyle;?>>
						<?php printCommentForm(true, '<a id="addComment"></a>', false); ?>
					</div>
				</div>
				<?php 
			}
			if(getTags()) {
				?>
				<div class="albumbuttons" <?php echo $buttonStyle;?>>
					<div class="button border colour">
						<?php printTags('links', gettext('Tags: '), 'taglist', ', ');?>
					</div>
				</div>
				<div class="clearing" ></div>
				<?php 
			} 
			?>
		</div>
		<?php include("inc-footer.php");?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end')
?>

<?php
function printButtons() {
	global $_zp_current_image, $buttonStyle, $commentCount, $doSlideShowLink;
	?>
	<div class="imagebuttons" <?php echo $buttonStyle;?>>
		<?php 
		if (hasPrevImage()) { 
			?>
			<div class="button border colour">
				<a href="<?php echo html_encode(getPrevImageURL()) ?>" title="<?php echo gettext('Previous Image') ?>"><?php echo gettext('« Prev') ?></a>
			</div>
			<?php 
		} 
		?>
		<div class ="button border colour">
			<?php echo imageNumber() . "/" . getNumImages(); ?>
		</div>
		<?php 
		if (hasNextImage()) { 
			?>
			<div class="button border colour">
				<a href="<?php echo html_encode(getNextImageURL()) ?>" title="<?php echo gettext('Next Image') ?>"><?php echo gettext('Next »') ?></a>
			</div>
			<?php 
		}
		if (getNumImages() > 1 && $doSlideShowLink && function_exists('printSlideShowLink')) { 
			?>
			<div class="button border colour">
				<?php printSlideShowLink();?>
			</div>
			<?php 
		}
		if (getImageMetaData()) { 
			?>
			<div class="button border colour">
				<?php printImageMetadata(NULL, 'colorbox');?>
			</div>
			<?php 
		}
		if (function_exists('getHitcounter')) { 
			?>
			<div class="button border colour">
				<?php echo gettext("Views: ") . getHitcounter() . "\n";?>
			</div>
			<?php 
		}
		if (function_exists('printCommentForm') && ($_zp_current_image->getCommentsAllowed() || $commentCount)) { 
			if ($commentCount == 0) {
				$comments = gettext('No Comments');
			} else {
				$comments = sprintf(ngettext('%u Comment', '%u Comments', $commentCount), $commentCount);
			}
			?>
			<div class="button border colour">
				<a href="#readComment"><?php echo $comments; ?></a>
			</div>
			<div class="button border colour">
				<a href="#addComment">Add Comment</a>
			</div>
			<?php 
		}
		if (function_exists('printLikeButton')) { 
			?>
			<div class="button fb-button border colour">
				<?php printLikeButton(); ?>
			</div>
			<?php 
		} 
		?>
	</div>
	<div class="clearing" ></div>
	<?php 
}
