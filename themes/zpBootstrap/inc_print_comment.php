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
			} ?>

			<?php if (($comments_open) && (($comments_allowed) || (getCommentCount() > 0 ))) { ?>
			<div id="comment_accordion" class="panel-group" role="tablist">
				<div class="panel panel-default">
					<div id="heading" class="panel-heading" role="tab">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#comment_accordion" href="#comment_collapse">
								<span class="glyphicon glyphicon-comment"></span>&nbsp;
								<?php
								$num = getCommentCount();
								if ($num == 0) {
									echo gettext('No Comments');
								} else {
									echo sprintf(ngettext('%u Comment','%u Comments',$num), $num);
								}
								?>
							</a>
						</h4>
					</div>
				</div>
				<div id="comment_collapse" class="collapse" role="tabpanel">
					<?php printCommentForm(true, NULL, true); ?>
				</div>
			</div>
			<?php } ?>