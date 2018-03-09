<?php
// force UTF-8 Ø
if (!defined('WEBPATH') || !class_exists("CMS")) die();
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
		$commentCount = function_exists('printCommentForm') ? getCommentCount() : 0;
		echo CommentsJS($commentCount);
		?>
		<div class="stage" <?php echo $stageStyle;?>>
			<?php 
			if (is_NewsArticle()) { // single news article 
				?>
				<div class="title border colour" <?php echo $titleStyle;?>>
					<?php printNewsTitle(); ?>
					<div class="newsdate">
						<?php printNewsDate(); ?>
					</div>
				</div>
				<div class="content border colour">
					<div class="page" <?php echo $commentStyle;?>>
						<div class="pagetext">
							<?php printNewsContent();?>
						</div>
					</div>
				</div>
				<div class="imagebuttons" <?php echo $buttonStyle;?>>
					<?php 
					if (getPrevNewsURL()) { 
						?>
						<div class="button border colour">
							Previous:&nbsp
							<?php printPrevNewsLink(''); ?>
						</div>
						<?php 
					}
					if (getNextNewsURL()) { 
						?>
						<div class="button border colour">
							Next:&nbsp
							<?php printNextNewsLink(''); ?>
						</div>
						<?php 
					}
					if (function_exists('getHitcounter')) { 
						?>
						<div class="button border colour">
							<?php echo "Views: " . getHitcounter();?>
						</div>
						<?php 
					}
					if (function_exists('printCommentForm') && ($_zp_current_article->getCommentsAllowed() || $commentCount)) { 
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
				if (function_exists('printCommentForm') && ($_zp_current_article->getCommentsAllowed() || $commentCount)) { 
					?>
					<a id="readComment"></a>
					<div class="content border colour">
						<div class="commentbox" <?php echo $commentStyle;?>>
							<?php printCommentForm(true, '<a id="addComment"></a>', false); ?>
						</div>
					</div>
					<?php 
				} 
				?>
				<div class="albumbuttons" <?php echo $buttonStyle;?>>
					<?php 
					if(getNewsCategories()) { 
						?>
						<div class="button border colour">
							<?php printNewsCategories(", ", gettext("Categories: "), "taglist"); ?>
						</div>
						<?php 
					}
					if(getTags()) {
						?>
						<div class="button border colour">
							<?php printTags('links', gettext('Tags: '), 'taglist', ', ');?>
						</div>
						<?php 
					} 
					?>
				</div>
				<div class="clearing" ></div>
				<?php 
			} else {
				if (getNextNewsPageURL() || getPrevNewsPageURL()) { 
					?>
					<div class="buttons" <?php echo $buttonStyle;?>>
						<div class="button border colour">
							<?php printNewsPageListWithNav(gettext('Next'), gettext('Prev'), true, 'taglist', true); ?>
						</div>
					</div>
					<div class="clearing" ></div>
					<?php 
				}
				while (next_news()) {  // news article loop 
					?>
					<div class="title border colour" <?php echo $titleStyle;?>>
						<div class="newslink">
							<?php printNewsURL();?>
						</div>
						<div class="newsdate">
							<?php printNewsDate(); ?>
						</div>
						<?php printNewsContent();?>
					</div>
					<div class="albumbuttons" <?php echo $buttonStyle;?>>
						<?php 
						if (function_exists('getHitcounter')) { 
							?>
							<div class="button border colour">
								<?php echo "Views: " . getHitcounter();?>
							</div>
							<?php 
						}
						$commentCount = function_exists('printCommentForm') ? getCommentCount() : 0;
						if (function_exists('printCommentForm') && ($_zp_current_article->getCommentsAllowed() || $commentCount)) { 
							if ($commentCount == 0) {
								$comments = gettext('No Comments');
							} else {
								$comments = sprintf(ngettext('%u Comment', '%u Comments', $commentCount), $commentCount);
							}
							?>
							<div class="button border colour">
								<?php echo $comments; ?></a>
							</div>
							<?php 
						}
						if(getTags()) {
							?>
							<div class="button border colour">
								<?php printTags('links', gettext('Tags: '), 'taglist', ', ');?>
							</div>
							<?php 
						}
						if(getNewsCategories()) { 
							?>
							<div class="button border colour">
								<?php printNewsCategories(", ", gettext("Categories: "), "taglist"); ?>
							</div>
							<?php 
						} 
						?>
					</div>
					<div class="clearing" ></div>
					<?php 
				} 
			} 
			?>
		</div>
		<?php include("inc-footer.php");?>
	</body>
</html>
<?php
zp_apply_filter('theme_file_end')
?>