<?php
/**
 * provides the Comments tab of admin
 * @package admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once('../../admin-globals.php');

admin_securityChecks(COMMENT_RIGHTS, currentRelativeURL());

if (isset($_GET['page'])) {
	$page = sanitize($_GET['page']);
} else {
	$page = '';
}

if (isset($_GET['fulltext']) && $_GET['fulltext'])
	$fulltext = true;
else
	$fulltext = false;
if (isset($_GET['viewall']))
	$viewall = true;
else
	$viewall = false;

/* handle posts */
if (isset($_GET['action'])) {
	switch ($_GET['action']) {

		case "spam":
			XSRFdefender('comment_update');
			$comment = new Comment(sanitize_numeric($_GET['id']));
			$comment->setInModeration(1);
			zp_apply_filter('comment_disapprove', $comment);
			$comment->save();
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php');
			exitZP();

		case "notspam":
			XSRFdefender('comment_update');
			$comment = new Comment(sanitize_numeric($_GET['id']));
			$comment->setInModeration(0);
			zp_apply_filter('comment_approve', $comment);
			$comment->save();
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php');
			exitZP();

		case 'applycomments':
			XSRFdefender('applycomments');
			if (isset($_POST['ids'])) {
				$action = processCommentBulkActions();
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?bulk=' . $action);
			} else {
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?saved');
			}
			exitZP();
		case 'deletecomment':
			XSRFdefender('deletecomment');
			$id = sanitize_numeric($_GET['id']);
			$comment = new Comment($id);
			$comment->remove();
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?ndeleted=1');
			exitZP();

		case 'savecomment':
			if (!isset($_POST['id'])) {
				header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php');
				exitZP();
			}
			XSRFdefender('savecomment');
			$id = sanitize_numeric($_POST['id']);
			$comment = new Comment($id);
			if (isset($_POST['name']))
				$comment->setName(sanitize($_POST['name'], 3));
			if (isset($_POST['email']))
				$comment->setEmail(sanitize($_POST['email'], 3));
			if (isset($_POST['website']))
				$comment->setWebsite(sanitize($_POST['website'], 3));
			$comment->setDateTime(sanitize($_POST['date'], 3));
			$comment->setComment(sanitize($_POST['comment'], 1));
			$comment->setCustomData($_comment_form_save_post = serialize(getCommentAddress(0)));
			$comment->save();
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?saved&page=editcomment&id=' . $comment->getID());
			exitZP();
	}
}


printAdminHeader('comments');
zp_apply_filter('texteditor_config', 'comments');
?>
<script type="text/javascript">
	//<!-- <![CDATA[
	function confirmAction() {
		if ($('#checkallaction').val() == 'deleteall') {
			return confirm('<?php echo js_encode(gettext("Are you sure you want to delete the checked items?")); ?>');
		} else {
			return true;
		}
	}
	// ]]> -->
</script>
<?php
zp_apply_filter('texteditor_config', 'zenphoto');
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';

if ($page == "editcomment" && isset($_GET['id'])) {
	zp_apply_filter('admin_note', 'comments', 'edit');
	?>
	<h1><?php echo gettext("edit comment"); ?></h1>
	<div class="box" style="padding: 10px">
		<?php
		$id = sanitize_numeric($_GET['id']);
		$commentarr = query_single_row("SELECT * FROM " . prefix('comments') . " WHERE id = $id LIMIT 1");
		if ($commentarr) {
			extract($commentarr);
			$commentarr = array_merge($commentarr, getSerializedArray($commentarr['custom_data']));
			?>
			<form class="dirty-check" id="form_editcomment" action="?action=savecomment" method="post" autocomplete="off">
				<?php XSRFToken('savecomment'); ?>
				<input	type="hidden" name="id" value="<?php echo $id; ?>" />
				<span class="buttons">
					<p class="buttons">
						<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment') ?>'; }"
							 title="<?php echo gettext('Delete'); ?>" ><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="" />
							<?php echo gettext('Delete'); ?></a>
					</p>
					<p class="buttons" style="margin-top: 10px">
						<button type="submit">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
							<strong><?php echo gettext("Apply"); ?></strong>
						</button>
					</p>
					<p class="buttons" style="margin-top: 10px">
						<button type="button" title="<?php echo gettext("Cancel"); ?>" onclick="window.location = 'admin-comments.php';">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" />
							<strong><?php echo gettext("Cancel"); ?></strong>
						</button>
					</p>
				</span>
				<br style="clear:both" /><br />
				<div class="commentformedit_fields">
					<?php
					if (getOption('comment_name_required')) {
						?>
						<label for="name"><?php echo gettext("Author:"); ?></label>
						<input type="text" size="40" name="name" value="<?php echo html_encode($name); ?>" />
						<?php
					}
					if (getOption('comment_web_required')) {
						?>
						<label for="website"><?php echo gettext("Web Site:"); ?></label>
						<input type="text" size="40" name="website" value="<?php echo html_encode($website); ?>" />
						<?php
					}
					if (getOption('comment_email_required')) {
						?>
						<label for="email"><?php echo gettext("E-Mail:"); ?></label>
						<input type="text" size="40" name="email" value="<?php echo html_encode($email); ?>" />
						<?php
					}
					?>
					<label for="date"><?php echo gettext("Date/Time:"); ?></label>
					<input type="text" size="18" name="date" value="<?php echo date('Y-m-d H:i:s', strtotime($date)); ?>" />
					<label for="date"><?php echo gettext("IP:"); ?></label>
					<input type="text" size="18" name="ip" value="<?php echo html_encode($IP); ?>" />
					<?php
					$_comment_form_save_post = $commentarr;
					if (getOption('comment_form_addresses')) {
						?>
						<label for="comment_form_street"><?php echo gettext('Street:'); ?></label>
						<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="<?php echo @$address['street']; ?>">
						<label for="comment_form_city"><?php echo gettext('City:'); ?></label>
						<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="<?php echo @$address['city']; ?>">
						<label for="comment_form_state"><?php echo gettext('State:'); ?></label>
						<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="<?php echo @$address['state']; ?>">
						<label for="comment_form_country"><?php echo gettext('Country:'); ?></label>
						<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="<?php echo @$address['country']; ?>">
						<label for="comment_form_postal"><?php echo gettext('Postal code:'); ?></label>
						<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="<?php echo @$address['postal']; ?>">
						<?php
					}
					?>
					<label for = "comment"><?php echo gettext("Comment:");
					?></label>
					<textarea rows="8" cols="60" name="comment" class="texteditor_comments"><?php echo html_encode($comment); ?></textarea>
				</div>
				<div class="commentformedit_box">
					<h2 class="h2_bordered_edit"><?php echo gettext('Comment management'); ?></h2>
					<div class="box-edit">
						<?php
						if ($inmoderation) {
							$status_moderation = '<span style="color: red">' . gettext('Comment is un-approved') . '</span>';
							$link_moderation = gettext('Approve');
							$title_moderation = gettext('Approve this comment');
							$url_moderation = '?action=notspam&amp;id=' . $id;
							$linkimage = WEBPATH . '/' . ZENFOLDER . '/images/pass.png';
						} else {
							$status_moderation = '<span style="color: green">' . gettext('Comment is approved') . '</span>';
							$link_moderation = gettext('Un-approve');
							$title_moderation = gettext('Un-approve this comment');
							$url_moderation = '?action=spam&amp;id=' . $id;
							$linkimage = WEBPATH . '/' . ZENFOLDER . '/images/warn.png';
						}

						if ($private) {
							$status_private = gettext('Comment is private');
						} else {
							$status_private = gettext('Comment is public');
						}

						if ($anon) {
							$status_anon = gettext('Comment is anonymous');
						} else {
							$status_anon = gettext('Comment is not anonymous');
						}
						?>
						<p><?php echo $status_moderation; ?>. <div class="buttons"><a href="<?php echo $url_moderation; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo $title_moderation; ?>" ><img src="<?php echo $linkimage; ?>" alt="" /><?php echo $link_moderation; ?></a></div></p>
						<br class="clearall" />
						<hr />
						<p><?php echo $status_private; ?></p>
						<p><?php echo $status_anon; ?></p>
					</div><!-- div box-edit-unpadded end -->
				</div>
			</form>
			<br class="clearall" />
		</div> <!-- div box end -->
		<?php
// end of $page == "editcomment"
	} else {
		?>
		<p class="errorbox"><?php echo gettext('Comment does not exist'); ?></p>
		<?php
	}
} else {
// Set up some view option variables.
	if (isset($_GET['fulltext']) && $_GET['fulltext']) {
		$fulltext = true;
		$fulltexturl = '?fulltext = 1';
	} else {
		$fulltext = false;
		$fulltexturl = '';
	}
	$allcomments = fetchComments(NULL);

	$pagenum = max((int) @$_GET['subpage'], 1);

	$comments = array_slice($allcomments, ($pagenum - 1) * COMMENTS_PER_PAGE, COMMENTS_PER_PAGE);
	$allcommentscount = count($allcomments);
	$totalpages = ceil(($allcommentscount / COMMENTS_PER_PAGE));
	zp_apply_filter('admin_note', '  comments', '  list');
	unset($allcomments);
	?>
	<h1><?php echo gettext("Comments"); ?></h1>

	<?php
	/* Display a message if needed. Fade out and hide after 2 seconds. */

	if (isset($_GET['bulk'])) {
		$bulkaction = sanitize($_GET['bulk']);
		switch ($bulkaction) {
			case 'deleteall':
				$message = gettext('Selected items deleted');
				break;
			case 'spam':
				$message = gettext('Selected items marked as spam');
				break;
			case 'approve':
				$message = gettext('Selected items approved');
				break;
		}
		?>
		<div class="messagebox fade-message"><?php echo $message; ?></div>
		<?php
	}
	if ((isset($_GET['ndeleted']) && $_GET['ndeleted'] > 0) || isset($_GET['saved'])) {
		?>
		<div class="messagebox fade-message">
			<?php
			if (isset($_GET['ndeleted'])) {
				?>
				<h2>
					<?php
					$n = sanitize_numeric($_GET['ndeleted']);
					printf(ngettext("%u Comment deleted successfully.", "%u Comment deleted successfully.", $n), $n);
					?>
				</h2>
				<?php
			}
			if (isset($_GET['saved'])) {
				?>
				<h2>
					<?php echo gettext("Changes applied"); ?>
				</h2>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>

	<p><?php echo gettext("You can edit or delete comments."); ?></p>

	<?php
	if ($totalpages > 1) {
		?>
		<div align="center">
			<?php adminPageNav($pagenum, $totalpages, '  admin-comments.php ', $fulltexturl); ?>
		</div>
		<?php
	}
	?>

	<form class="dirty-check" name="comments" id="form_commentlist" action="?action=applycomments" method="post" onsubmit="return confirmAction();" autocomplete="off">
		<?php XSRFToken('applycomments'); ?>
		<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum) ?>" />
		<p class="buttons"><button type="submit"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button></p>
		<p class="buttons">
			<?php
			if ($fulltext) {
				$msg = gettext("View truncated");
				$img = WEBPATH . '/' . ZENFOLDER . '/images/arrow_in.png';
			} else {
				$msg = gettext("View full text");
				$img = WEBPATH . '/' . ZENFOLDER . '/images/arrow_out.png';
			}
			?>
			<a	href="admin-comments.php?fulltext=<?php
			echo (int) ($fulltext + 1) & 1;
			if ($viewall)
				echo '&amp;viewall';
			if ($pagenum > 1)
				echo "&amp;subpage=$pagenum";
			?>"><img src="<?php echo $img; ?>" alt = "" /> <?php echo $msg; ?></a>
		</p>
		<br class="clearall" /><br />
		<table class="bordered">
			<tr>
				<th colspan="11"><?php echo gettext("Edit this comment"); ?>
					<?php
					$checkarray = array(
									gettext('*Bulk actions*')	 => 'noaction',
									gettext('Delete')					 => 'deleteall',
									gettext('Mark as spam')		 => 'spam',
									gettext('Approve')				 => 'approve',
					);
					printBulkActions($checkarray);
					?>
				</th>

			</tr>
			<tr>
				<td colspan="11" class="subhead">
					<label style="float: right"><?php echo gettext("Check All"); ?>
						<input type="checkbox" name="allbox" id="allbox" onclick="checkAll(this.form, 'ids[]', this.checked);" />

					</label>
				</td>
			</tr>
			<?php
			foreach ($comments as $comment) {
				$id = $comment['id'];
				$author = $comment['name'];
				$email = $comment['email'];
				$link = '<a title="' . gettext('The item upon which this comment was posted no longer exists.') . '">' . gettext('<strong>Missing Object</strong> ') . '</a>'; // in case of such
				$image = '';
				$albumtitle = '';

				// ZENPAGE: switch added for zenpage comment support
				switch ($comment['type']) {
					case "albums":
						$obj = getItemByID('albums', $comment['ownerid']);
						if ($obj) {
							$link = '<a href = "' . $obj->getLink() . '#zp_comment_id_' . $id . '">' . $obj->getTitle() . '</a>';
						}
						break;
					case "news": // ZENPAGE: if plugin is installed
						if (extensionEnabled('zenpage')) {
							$obj = getItemByID('news', $comment['ownerid']);
							if ($obj) {
								$link = '<a href = "' . $obj->getLink() . '#zp_comment_id_' . $id . '">' . gettext("[news]") . ' ' . $obj->getTitle() . "</a> ";
							}
						}
						break;
					case "pages": // ZENPAGE: if plugin is installed
						if (extensionEnabled('zenpage')) {
							$obj = getItemByID('pages', $comment['ownerid']);
							if ($obj) {
								$link = "<a href=\"" . $obj->getLink() . '#zp_comment_id_' . $id . '">' . gettext("[page]") . ' ' . $obj->getTitle() . "</a>";
							}
						}
						break;
					default : // all the image types
						$obj = getItemByID('images', $comment['ownerid']);
						if ($obj) {
							$link = "<a href=\"" . $obj->getLink() . '#zp_comment_id_' . $id . '">' . $obj->getTitle() . "</a>";
						}
						break;
				}
				$date = myts_date('%m/%d/%Y %I:%M %p', $comment['date']);
				$website = $comment['website'];
				$fullcomment = sanitize($comment['comment'], 2);
				$shortcomment = truncate_string(getBare($fullcomment), 123);
				$inmoderation = $comment['inmoderation'];
				$private = $comment['private'];
				$anon = $comment['anon'];

				?>
				<tr class="newstr">
					<td><?php echo ($fulltext) ? $fullcomment : $shortcomment; ?></td>
					<td><?php echo date('Y-m-d H:i:s', strtotime($date)); ?></td>
					<td>
						<?php
						echo $website ? "<a href=\"$website\">$author</a>" : $author;
						if ($anon) {
							echo ' <a title="' . gettext('Anonymous posting') . '"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/action.png" style="border: 0px;" alt="' . gettext("Anonymous posting") . '" /></a>';
						}
						?>
					</td>
					<td><?php echo $link; ?></td>
					<td><?php echo $comment['IP']; ?></td>
					<td class="page-list_icon">
						<?php
						if ($private) {
							echo '<a title="' . gettext("Private message") . '"><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/reset.png" style="border: 0px;" alt="' . gettext("Private message") . '" /></a>';
						}
						?>
					</td>
					<td class="page-list_icon"><?php
						if ($inmoderation) {
							?>
							<a href="?action=notspam&amp;id= <?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo gettext('Approve this message (not SPAM)'); ?>">
								<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/warn.png" style="border: 0px;" alt="<?php echo gettext("Approve this message (not SPAM"); ?>" /></a>
							<?php
						} else {
							?>
							<a href="?action=spam&amp;id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo gettext('Mark this message as SPAM'); ?>">
								<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" style="border: 0px;" alt="<?php echo gettext("Mark this message as SPAM"); ?>" /></a>
							<?php
						}
						?></td>
					<td class="page-list_icon"><a href="?page=editcomment&amp;id=<?php echo $id; ?>" title="<?php echo gettext('Edit this comment.'); ?>">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pencil.png" style="border: 0px;" alt="<?php echo gettext('Edit'); ?>" /></a></td>
					<td class="page-list_icon">
						<a href="mailto:<?php echo $email; ?>?body=<?php echo commentReply($obj, $author, $fullcomment); ?>" title="<?php echo gettext('Reply:') . ' ' . $email; ?>">
							<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_mail.png" style="border: 0px;" alt="<?php echo gettext('Reply'); ?>" /></a>
					</td>
					<td class="page-list_icon">
						<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment') ?>'; }"
							 title="<?php echo gettext('Delete this comment.'); ?>" > <img
								src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" style="border: 0px;" alt="<?php echo gettext('Delete'); ?>" /></a></td>
					<td class="page-list_icon"><input type="checkbox" name="ids[]" value="<?php echo $id; ?>"
																						onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" /></td>
				</tr>
			<?php } ?>



		</table>
		<p class="buttons"><button type="submit"><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button></p>
		<ul class="iconlegend">
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" /><?php echo gettext("Private message"); ?></li>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/warn.png" alt="Marked as spam" /><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="Approved" /><?php echo gettext("Marked as spam/approved"); ?></li>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/action.png" alt="Anonymous posting" /><?php echo gettext("Anonymous posting"); ?></li>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pencil.png" alt="Edit comment" /><?php echo gettext("Edit comment"); ?></li>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/icon_mail.png" alt="E-mail comment author" /><?php echo gettext("E-mail comment author"); ?></li>
			<li><img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/fail.png" alt="Delete" /><?php echo gettext("Delete"); ?></li>
		</ul>

	</form>

	<?php
}

echo "\n" . '</div>'; //content
printAdminFooter();
echo "\n" . '</div>'; //main


echo "\n</body>";
echo "\n</html>";
?>



