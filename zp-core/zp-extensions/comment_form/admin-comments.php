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
			$comment->setAddressData($_comment_form_save_post = serialize(getCommentAddress(0)));
			$comment->save();
			header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?saved&page=editcomment&id=' . $comment->getID());
			exitZP();
	}
}


printAdminHeader('comments');
zp_apply_filter('texteditor_config', 'admin_comments');
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
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
?>
<div id="main">
	<?php printTabs(); ?>
	<div id="content">
		<?php
		if ($page == "editcomment" && isset($_GET['id'])) {

			zp_apply_filter('admin_note', 'comments', 'edit');
			?>
			<h1><?php echo gettext("edit comment"); ?></h1>

			<div id="container">
				<div class="tabbox">

					<?php
					$id = sanitize_numeric($_GET['id']);
					$commentarr = query_single_row("SELECT * FROM " . prefix('comments') . " WHERE id = $id LIMIT 1");
					if ($commentarr) {
						extract($commentarr);
						$commentarr = array_merge($commentarr, getSerializedArray($commentarr['address_data']));
						?>
						<form class="dirtylistening" onReset="setClean('form_editcomment');" id="form_editcomment" action="?action=savecomment" method="post" autocomplete="off">
							<?php XSRFToken('savecomment'); ?>
							<input	type="hidden" name="id" value="<?php echo $id; ?>" />
							<span class="buttons">
								<p class="buttons" style="margin-top: 10px">
									<button type="submit">
										<?php echo CHECKMARK_GREEN; ?>
										<strong><?php echo gettext("Apply"); ?></strong>
									</button>
								</p>
								<p class="buttons" style="margin-top: 10px">
									<button type="button" title="<?php echo gettext("Cancel"); ?>" onclick="window.location = 'admin-comments.php';">
										<?php echo CROSS_MARK_RED; ?>
										<strong><?php echo gettext("Cancel"); ?></strong>
									</button>
								</p>
							</span>
							<span class="buttons floatright">
								<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment') ?>'; }"
									 title="<?php echo gettext('Delete'); ?>" >
										 <?php echo WASTEBASKET; ?>
										 <?php echo gettext('Delete'); ?>
								</a>
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
									<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="<?php echo $commentarr['street']; ?>">
									<label for="comment_form_city"><?php echo gettext('City:'); ?></label>
									<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="<?php echo $commentarr['city']; ?>">
									<label for="comment_form_state"><?php echo gettext('State:'); ?></label>
									<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="<?php echo $commentarr['state']; ?>">
									<label for="comment_form_country"><?php echo gettext('Country:'); ?></label>
									<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="<?php echo $commentarr['country']; ?>">
									<label for="comment_form_postal"><?php echo gettext('Postal code:'); ?></label>
									<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="<?php echo $commentarr['postal']; ?>">
									<?php
								}
								?>
								<label for = "comment"><?php echo gettext("Comment:");
								?></label>
								<br clear="all">
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
										$linkimage = CHECKMARK_GREEN;
									} else {
										$status_moderation = '<span style="color: green">' . gettext('Comment is approved') . '</span>';
										$link_moderation = gettext('Un-approve');
										$title_moderation = gettext('Un-approve this comment');
										$url_moderation = '?action=spam&amp;id=' . $id;
										$linkimage = NO_ENTRY;
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
									<p><?php echo $status_moderation; ?>. <div class="buttons"><a href="<?php echo $url_moderation; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo $title_moderation; ?>" ><?php echo $linkimage; ?> <?php echo $link_moderation; ?></a></div></p>
									<br class="clearall">
									<hr />
									<p><?php echo $status_private; ?></p>
									<p><?php echo $status_anon; ?></p>
								</div><!-- div box-edit-unpadded end -->
							</div>
						</form>
						<br class="clearall">
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
				unset($allcomments);
				zp_apply_filter('admin_note', '  comments', '  list');
				?>
				<h1><?php echo gettext("Comments"); ?></h1>
				<div class="tabbox">
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

					<form class="dirtylistening" onReset="setClean('form_commentlist');"  name="comments" id="form_commentlist" action="?action=applycomments" method="post" onsubmit="return confirmAction();" autocomplete="off">
						<?php XSRFToken('applycomments'); ?>
						<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum) ?>" />
						<p class="buttons"><button type="submit"><?php echo CHECKMARK_GREEN; ?> <strong><?php echo gettext("Apply"); ?></strong></button></p>
						<p class="buttons">
							<?php
							if ($fulltext) {
								$msg = gettext("View truncated");
								$arrow = SOUTH_EAST_CORNER_ARROW;
							} else {
								$msg = gettext("View full text");
								$arrow = NORTH_WEST_CORNER_ARROW;
							}
							?>
							<a	href="admin-comments.php?fulltext=<?php
							echo (int) ($fulltext + 1) & 1;
							if ($viewall)
								echo '&amp;viewall';
							if ($pagenum > 1)
								echo "&amp;subpage=$pagenum";
							?>">
									 <?php echo $arrow; ?>
									 <?php echo $msg; ?>
							</a>
						</p>
						<br class="clearall"><br />
						<table class="bordered">
							<tr>
								<th colspan="100%"><?php echo gettext("Edit this comment"); ?>
									<?php
									$checkarray = array(
											gettext('*Bulk actions*') => 'noaction',
											gettext('Delete') => 'deleteall',
											gettext('Mark as spam') => 'spam',
											gettext('Approve') => 'approve',
									);
									$checkarray = zp_apply_filter('bulk_comment_actions', $checkarray);
									printBulkActions($checkarray);
									?>
								</th>

							</tr>
							<tr>
								<td colspan="100%" class="subhead">
									<label style="float: right; margin-right:4px;"><?php echo gettext("Check All"); ?>
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
								// ZENPAGE: switch added for zenpage comment support
								switch ($comment['type']) {
									case "albums":
										$obj = getItemByID('albums', $comment['ownerid']);
										if ($obj) {
											$link = '<a href = "' . $obj->getLink() . '#zp_comment_id_' . $id . '">[' . gettext('album') . '] ' . $obj->getTitle() . '</a>';
										}
										break;
									case "news": // ZENPAGE: if plugin is installed
										if (extensionEnabled('zenpage')) {
											$obj = getItemByID('news', $comment['ownerid']);
											if ($obj) {
												$link = '<a href = "' . $obj->getLink() . '#zp_comment_id_' . $id . '">[' . gettext("article") . '] ' . $obj->getTitle() . "</a> ";
											}
										}
										break;
									case "pages": // ZENPAGE: if plugin is installed
										if (extensionEnabled('zenpage')) {
											$obj = getItemByID('pages', $comment['ownerid']);
											if ($obj) {
												$link = "<a href=\"" . $obj->getLink() . '#zp_comment_id_' . $id . '">[' . gettext("page") . '] ' . $obj->getTitle() . "</a>";
											}
										}
										break;
									default : // all the image types
										$obj = getItemByID('images', $comment['ownerid']);
										if ($obj) {
											$link = "<a href=\"" . $obj->getLink() . '#zp_comment_id_' . $id . '">[' . gettext('image') . '] ' . $obj->getTitle() . "</a>";
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
									<td class="page_list_short">

										<?php echo ($fulltext) ? $fullcomment : $shortcomment; ?>

									<td><?php echo date('Y-m-d H:i:s', strtotime($date)); ?></td>
									<td>
										<?php
										echo $website ? "<a href=\"$website\">$author</a>" : $author;
										if ($anon) {
											echo ' <a title="' . gettext('Anonymous posting') . '">' . EXCLAMATION_RED . '</a>';
										}
										?>
									</td>
									<td><?php echo $link; ?></td>
									<td><?php echo $comment['IP']; ?></td>
									<td>
										<div>
											<?php
											if ($private) {
												echo '<div class = "page-list_icon">'
												. '<a title = "' . gettext("Private message") . '">'
												. NO_ENTRY
												. '</a>'
												. '/div>';
											}
											?>
											<div class="page-list_icon">
												<?php
												if ($inmoderation) {
													?>
													<a href="?action=notspam&amp;id= <?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo gettext('Approve this message (not SPAM)'); ?>">
														<?php echo WARNING_SIGN_ORANGE; ?>
													</a>
													<?php
												} else {
													?>
													<a href="?action=spam&amp;id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update') ?>" title="<?php echo gettext('Mark this message as SPAM'); ?>">
														<?php echo CHECKMARK_GREEN; ?> </a>
													<?php
												}
												?>
											</div>
											<div class="page-list_icon">
												<a href="?page=editcomment&amp;id=<?php echo $id; ?>" title="<?php echo gettext('Edit this comment.'); ?>">
													<?php echo PENCIL_ICON; ?>
												</a>
											</div>
											<div class="page-list_icon">
												<?php
												preg_match('/.*?>(.*)</', $link, $matches);
												$str = sprintf(gettext('%1$s commented on %2$s:'), $author, $matches[1]) . '%0D%0A%0D%0A' . implode('%0D%0A', explode('\n', wordwrap(getBare($fullcomment), 75, '\n')));
												?>
												<a href="mailto:<?php echo $email; ?>?body=<?php echo html_encode($str); ?>" title="<?php echo gettext('Reply:') . ' ' . $email; ?>">
													<?php echo ENVELOPE; ?>
												</a>
											</div>
											<div class="page-list_icon">
												<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment') ?>'; }"
													 title="<?php echo gettext('Delete this comment.'); ?>" >
														 <?php echo WASTEBASKET; ?>
												</a>
											</div>
										</div>
									</td>
									<td>
										<div class="flopatright">
											<input type="checkbox" name="ids[]" value="<?php echo $id; ?>"
														 onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" />
										</div>
									</td>
								</tr>
							<?php } ?>



						</table>
						<p class="buttons"><button type="submit"><?php echo CHECKMARK_GREEN; ?> <?php echo gettext("Apply"); ?></strong></button></p>
						<ul class="iconlegend">
							<li>
								<?php echo NO_ENTRY; ?>
								<?php echo gettext("Private message"); ?>
							</li>
							<li>
								<?php echo WARNING_SIGN_ORANGE; ?>
								<?php echo CHECKMARK_GREEN; ?>
								<?php echo gettext("Marked as spam/approved"); ?>
							</li>
							<li>
								<?php echo EXCLAMATION_RED; ?>
								<?php echo gettext("Anonymous posting"); ?>
							</li>
							<li>
								<?php echo PENCIL_ICON; ?>
								<?php echo gettext("Edit comment"); ?>
							</li>
							<li>
								<?php echo ENVELOPE; ?>
								<?php echo gettext("E-mail comment author"); ?>
							</li>
							<li>
								<?php echo WASTEBASKET; ?>
								<?php echo gettext("Delete"); ?>
							</li>
						</ul>

					</form>

					<?php
				}
				?>

			</div>
		</div><!-- content -->
		<?php
		printAdminFooter();
		echo "\n" . '</div>'; //main


		echo "\n</body>";
		echo "\n</html>";
		?>



