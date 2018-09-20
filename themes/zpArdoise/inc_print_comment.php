	<?php
	switch ($_zp_gallery_page) {
		case 'album.php':
			$comments_open = getOption('comment_form_albums');					// option of comment_form plugin for albums
			$comments_allowed = $_zp_current_album->getCommentsAllowed();		// value for current album
			break;
		case 'image.php':
			$comments_open = getOption('comment_form_images');
			$comments_allowed = $_zp_current_image->getCommentsAllowed();
			break;
		case 'pages.php':
			$comments_open = getOption('comment_form_pages');
			$comments_allowed = $_zp_current_page->getCommentsAllowed();
			break;
		case 'news.php':
			$comments_open = getOption('comment_form_articles');
			$comments_allowed = $_zp_current_article->getCommentsAllowed();
			break;
		default:
			return;
			break;
	}
	?>

	<?php if (($comments_open) && ($comments_allowed || (getCommentCount() > 0 ))){ ?>
		<a class="fadetoggler"><img src="<?php echo $_zp_themeroot; ?>/images/icon-comment.png" alt="icon-comment" id="icon-comment" />
		<?php
		$num = getCommentCount();
		if ($num == 0) {
			echo gettext('No Comments');
		} else {
			echo sprintf(ngettext('%u Comment','%u Comments',$num), $num);
		}
		?>
		</a>
		<div id="comment-wrap" class="fader clearfix">
			<?php printCommentForm(true, NULL, true); ?>
		</div>
	<?php } ?>