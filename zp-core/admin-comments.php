<?php
/**
 * provides the Comments tab of admin
 * @package admin
 */

// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(__FILE__).'/admin-functions.php');
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(COMMENT_RIGHTS, currentRelativeURL(__FILE__));

$gallery = new Gallery();
if (isset($_GET['page'])) {
	$page = sanitize($_GET['page']);
} else {
	$page = '';
}
if (isset($_GET['fulltext']) && $_GET['fulltext']) $fulltext = true; else $fulltext = false;
if (isset($_GET['viewall'])) $viewall = true; else $viewall = false;

/* handle posts */
if (isset($_GET['action'])) {
	switch ($_GET['action']) {

	case "spam":
		XSRFdefender('comment_update');
		$comment = new Comment(sanitize_numeric($_GET['id']));
		zp_apply_filter('comment_disapprove', $comment);
		$comment->setInModeration(1);
		$comment->save();
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-comments.php');
		exit();

	case "notspam":
		XSRFdefender('comment_update');
		$comment = new Comment(sanitize_numeric($_GET['id']));
		zp_apply_filter('comment_approve', $comment);
		$comment->setInModeration(0);
		$comment->save();
		header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin-comments.php');
		exit();

	case 'deletecomments':
		XSRFdefender('deletecomment');
		if (isset($_POST['ids'])) {
			$action = processCommentBulkActions();
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-comments.php?bulk=".$action);
			exit();
		} else {
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-comments.php");
			exit();
		}
 case 'deletecomment':
		XSRFdefender('deletecomment');
		$id = $_GET['id'];
 		$sql = "DELETE FROM ".prefix('comments')." WHERE id =".$id;
 		query($sql);
 		header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-comments.php?ndeleted=1");
		exit();

	case 'savecomment':
		if (!isset($_POST['id'])) {
			header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-comments.php");
			exit();
		}
		XSRFdefender('savecomment');
		$id = sanitize_numeric($_POST['id']);
		$name = sanitize($_POST['name'], 3);
		$email = sanitize($_POST['email'], 3);
		$website = sanitize($_POST['website'], 3);
		$date = sanitize($_POST['date'], 3);
		$comment = sanitize($_POST['comment'], 1);
		$custom = zp_apply_filter('save_comment_custom_data', '');
		if (!empty($custom)) {
			$custom = ", `custom_data`=".db_quote($custom);
		}

		$sql = "UPDATE ".prefix('comments')." SET `name` = ".db_quote($name).", `email` = ".db_quote($email).", `website` = ".db_quote($website).", `comment` = ".db_quote($comment).$custom." WHERE id = $id";
		query($sql);

		header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/admin-comments.php?sedit");
		exit();

	}
}


printAdminHeader('comments');
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
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';

if ($page == "editcomment" && isset($_GET['id']) ) { ?>
<h1><?php echo gettext("edit comment"); ?></h1>
<div class="box" style="padding: 10px">
<?php
	$id = sanitize_numeric($_GET['id']);

	$commentarr = query_single_row("SELECT * FROM ".prefix('comments')." WHERE id = $id LIMIT 1");
	extract($commentarr);
	?>

<form action="?action=savecomment" method="post">
<?php XSRFToken('savecomment');?>
<input	type="hidden" name="id" value="<?php echo $id; ?>" />
<table style="float:left;margin-right:2em;">

	<tr>
		<td width="100"><?php echo gettext("Author:"); ?></td>
		<td><input type="text" size="40" name="name" value="<?php echo html_encode($name); ?>" /></td>
	</tr>
	<tr>
		<td><?php echo gettext("Web Site:"); ?></td>
		<td><input type="text" size="40" name="website" value="<?php echo html_encode($website); ?>" /></td>
	</tr>
	<tr>
		<td><?php echo gettext("E-Mail:"); ?></td>
		<td><input type="text" size="40" name="email" value="<?php echo html_encode($email); ?>" /></td>
	</tr>
	<tr>
		<td><?php echo gettext("Date/Time:"); ?></td>
		<td><input type="text" size="18" name="date" value="<?php echo html_encode($date); ?>" /></td>
	</tr>
	<tr>
		<td><?php echo gettext("IP:"); ?></td>
		<td><input type="text" disabled="disabled" size="18" name="date" value="<?php echo html_encode($IP); ?>" /></td>
	</tr>
	<?php
	echo zp_apply_filter('edit_comment_custom_data', '', $custom_data);
	?>
	<tr>
		<td valign="top"><?php echo gettext("Comment:"); ?></td>
		<td><textarea rows="8" cols="60" name="comment" /><?php echo html_encode($comment); ?></textarea></td>
	</tr>
	<tr>
		<td></td>
		<td>


		</td>
	</tr>
</table>
<div style="width:260px; float:right">
<h2 class="h2_bordered_edit"><?php echo gettext('Comment management'); ?></h2>
<div class="box-edit">
<?php
	if ($inmoderation) {
		$status_moderation = '<span style="color: red">'.gettext('Comment is un-approved').'</span>';
		$link_moderation = gettext('Approve');
		$title_moderation = gettext('Approve this comment');
		$url_moderation = '?action=notspam&amp;id='.$id;
		$linkimage = "images/pass.png";
	} else {
		$status_moderation = '<span style="color: green">'.gettext('Comment is approved').'</span>';
		$link_moderation = gettext('Un-approve');
		$title_moderation = gettext('Un-approve this comment');
		$url_moderation = '?action=spam&amp;id='.$id;
		$linkimage = "images/warn.png";
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
<p><?php echo $status_moderation; ?>. <div class="buttons"><a href="<?php echo $url_moderation; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update')?>" title="<?php echo $title_moderation; ?>" ><img src="<?php echo $linkimage; ?>" alt="" /><?php echo $link_moderation; ?></a></div></p>
<br clear="all" />
<hr />
<p><?php echo $status_private; ?></p>
<p><?php echo $status_anon; ?></p>
<hr />
<p class="buttons">
<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment')?>'; }"
		title="<?php echo gettext('Delete'); ?>" ><img src="images/fail.png" alt="" />
		<?php echo gettext('Delete'); ?></a></p>
		<br style="clear:both" />
<p class="buttons" style="margin-top: 10px">
		<button type="submit" title="<?php echo gettext("Apply"); ?>">
		<img src="images/pass.png" alt="" />
		<strong><?php echo gettext("Apply"); ?></strong>
		</button>
		</p>
		<br style="clear:both;" />
<p class="buttons" style="margin-top: 10px">
		<button type="button" title="<?php echo gettext("Cancel"); ?>" onclick="window.location = 'admin-comments.php';">
		<img src="images/reset.png" alt="" />
		<strong><?php echo gettext("Cancel"); ?></strong>
		</button>
		</p>
		<br style="clear:both" />
</div><!-- div box-edit-unpadded end -->
</div>
</form>
<br clear="all" />
</div> <!-- div box end -->
<?php
// end of $page == "editcomment"
} else {
	// Set up some view option variables.

	if (isset($_GET['fulltext']) && $_GET['fulltext']) {
		define('COMMENTS_PER_PAGE',10);
		$fulltext = true;
		$fulltexturl = '?fulltext=1';
	} else {
		define('COMMENTS_PER_PAGE',20);
		$fulltext = false;
		$fulltexturl = '';
	}
	$allcomments = fetchComments(NULL);

	if (isset($_GET['subpage'])) {
		$pagenum = max(intval($_GET['subpage']),1);
	} else {
		$pagenum = 1;
	}

	$comments = array_slice($allcomments, ($pagenum-1)*COMMENTS_PER_PAGE, COMMENTS_PER_PAGE);
	$allcommentscount = count($allcomments);
	$totalpages = ceil(($allcommentscount / COMMENTS_PER_PAGE));
	?>
<h1><?php echo gettext("Comments"); ?></h1>

<?php /* Display a message if needed. Fade out and hide after 2 seconds. */

if(isset($_GET['bulk'])) {
	$bulkaction = sanitize($_GET['bulk']);
	switch($bulkaction) {
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
if ((isset($_GET['ndeleted']) && $_GET['ndeleted'] > 0) || isset($_GET['sedit'])) { ?>
<div class="messagebox" id="fade-message"><?php if (isset($_GET['ndeleted'])) { ?>
<h2><?php echo $_GET['ndeleted']; ?> <?php echo gettext("Comments deleted successfully."); ?></h2>
<?php } ?> <?php if (isset($_GET['sedit'])) { ?>
<h2><?php echo gettext("Changes applied"); ?></h2>
<?php } ?></div>
<?php } ?>

<p><?php echo gettext("You can edit or delete comments on your photos."); ?></p>

<?php if ($totalpages > 1) {?>
	<div align="center">
	<?php adminPageNav($pagenum,$totalpages,'admin-comments.php',$fulltexturl); ?>
	</div>
	<?php } ?>

<form name="comments" action="?action=deletecomments" method="post"	onsubmit="return confirmAction();">
	<?php XSRFToken('deletecomment');?>
<input type="hidden" name="subpage" value="<?php echo html_encode($pagenum) ?>" />
<p class="buttons"><button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button></p>
<p class="buttons">
<?php if(!$fulltext) { ?>
			<a href="?fulltext=1<?php echo $viewall ? "&amp;viewall":""; ?>"><img src="images/arrow_out.png" alt="" /> <?php echo gettext("View full text"); ?></a><?php
		} else {
			?> <a	href="admin-comments.php?fulltext=0"<?php echo $viewall ? "?viewall":""; ?>"><img src="images/arrow_in.png" alt="" /> <?php echo gettext("View truncated"); ?></a> <?php
		} ?>
</p>
<br clear="all" /><br />
<table class="bordered">
	<tr>
		<th colspan="11"><?php echo gettext("Edit this comment"); ?>
		<?php
	  	$checkarray = array(
			  	gettext('*Bulk actions*') => 'noaction',
			  	gettext('Delete') => 'deleteall',
			  	gettext('Mark as spam') => 'spam',
			  	gettext('Approve') => 'approve',
	  	);
	  	?>
	  	<span style="float:right">
	  	<select name="checkallaction" id="checkallaction" size="1">
	  	<?php generateListFromArray(array('noaction'), $checkarray,false,true); ?>
			</select>
			</span>
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
		$link = gettext('<strong>database error</strong> '); // in case of such
		$image = '';
		$albumtitle = '';

		// ZENPAGE: switch added for zenpage comment support
		switch ($comment['type']) {
			case "albums":
				$image = '';
				$title = '';
				$albmdata = query_full_array("SELECT `title`, `folder` FROM ". prefix('albums') .
										" WHERE `id`=" . $comment['ownerid']);
				if ($albmdata) {
					$albumdata = $albmdata[0];
					$album = $albumdata['folder'];
					$albumtitle = get_language_string($albumdata['title']);
					$link = "<a href=\"".rewrite_path("/$album","/index.php?album=".pathurlencode($album))."\">".$albumtitle.$title."</a>";
					if (empty($albumtitle)) $albumtitle = $album;
				}
				break;
			case "news": // ZENPAGE: if plugin is installed
				if(getOption("zp_plugin_zenpage")) {
					$titlelink = '';
					$title = '';
					$newsdata = query_full_array("SELECT `title`, `titlelink` FROM ". prefix('news') .
										" WHERE `id`=" . $comment['ownerid']);
					if ($newsdata) {
						$newsdata = $newsdata[0];
						$titlelink = $newsdata['titlelink'];
						$title = get_language_string($newsdata['title']);
						$link = "<a href=\"".rewrite_path("/news/".$titlelink,"/index.php?p=news&amp;title=".urlencode($titlelink))."\">".$title."</a><br /> ".gettext("[news]");
					}
				}
				break;
			case "pages": // ZENPAGE: if plugin is installed
				if(getOption("zp_plugin_zenpage")) {
					$image = '';
					$title = '';
					$pagesdata = query_full_array("SELECT `title`, `titlelink` FROM ". prefix('pages') .
										" WHERE `id`=" . $comment['ownerid']);
					if ($pagesdata) {
						$pagesdata = $pagesdata[0];
						$titlelink = $pagesdata['titlelink'];
						$title = get_language_string($pagesdata['title']);
						$link = "<a href=\"".rewrite_path("/pages/".$titlelink,"/index.php?p=pages&amp;title=".urlencode($titlelink))."\">".$title."</a><br /> ".gettext("[page]");
					}
				}
				break;
			default: // all the image types
				$imagedata = query_full_array("SELECT `title`, `filename`, `albumid` FROM ". prefix('images') .
										" WHERE `id`=" . $comment['ownerid']);
				if ($imagedata) {
					$imgdata = $imagedata[0];
					$image = $imgdata['filename'];
					if ($imgdata['title'] == "") $title = $image; else $title = get_language_string($imgdata['title']);
					$title = '/ ' . $title;
					$albmdata = query_full_array("SELECT `folder`, `title` FROM ". prefix('albums') .
											" WHERE `id`=" . $imgdata['albumid']);
					if ($albmdata) {
						$albumdata = $albmdata[0];
						$album = $albumdata['folder'];
						$albumtitle = get_language_string($albumdata['title']);
						$link = "<a href=\"".rewrite_path("/$album/$image","/index.php?album=".pathurlencode($album).	"&amp;image=".urlencode($image))."\">".$albumtitle.$title."</a>";
						if (empty($albumtitle)) $albumtitle = $album;
					}
				}
				break;
		}
		$date  = myts_date('%m/%d/%Y %I:%M %p', $comment['date']);
		$website = $comment['website'];
		$shortcomment = truncate_string($comment['comment'], 123);
		$fullcomment = $comment['comment'];
		$inmoderation = $comment['inmoderation'];
		$private = $comment['private'];
		$anon = $comment['anon'];
		?>
	<tr class="newstr">
		<td><?php echo ($fulltext) ? $fullcomment : $shortcomment; ?></td>
		<td><?php echo $date; ?></td>
		<td>
		<?php
		echo $website ? "<a href=\"$website\">$author</a>" : $author;
		if ($anon) {
			echo ' <a title="'.gettext('Anonymous posting').'"><img src="images/action.png" style="border: 0px;" alt="'. gettext("Anonymous posting").'" /></a>';
		}
		?>
		</td>
		<td><?php echo $link; ?></td>

		<td><?php echo $comment['IP']; ?></td>
		<td class="icons">
			<?php
			if($private) {
				echo '<a title="'.gettext("Private message").'"><img src="images/reset.png" style="border: 0px;" alt="'. gettext("Private message").'" /></a>';
			}
			?>
		</td>
		<td align="center"><?php
		if ($inmoderation) {
			?>
			<a href="?action=notspam&amp;id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update')?>" title="<?php echo gettext('Approve this message (not SPAM)'); ?>">
				<img src="images/warn.png" style="border: 0px;" alt="<?php echo gettext("Approve this message (not SPAM"); ?>" /></a>
			<?php
		} else {
			?>
			<a href="?action=spam&amp;id=<?php  echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('comment_update')?>" title="<?php  echo gettext('Mark this message as SPAM'); ?>">
				<img src="images/pass.png" style="border: 0px;" alt="<?php echo gettext("Mark this message as SPAM"); ?>" /></a>
			<?php
		}
		?></td>
		<td class="icons"><a href="?page=editcomment&amp;id=<?php echo $id; ?>" title="<?php echo gettext('Edit this comment.'); ?>">
			<img src="images/pencil.png" style="border: 0px;" alt="<?php echo gettext('Edit'); ?>" /></a></td>
		<td class="icons">
		<a href="mailto:<?php echo $email; ?>?body=<?php echo commentReply($fullcomment, $author, $image, $albumtitle); ?>" title="<?php echo gettext('Reply:').' '.$email; ?>">
		<img src="images/icon_mail.png" style="border: 0px;" alt="<?php echo gettext('Reply'); ?>" /></a>
		</td>
		<td class="icons">
			<a href="javascript:if(confirm('<?php echo gettext('Are you sure you want to delete this comment?'); ?>')) { window.location='?action=deletecomment&id=<?php echo $id; ?>&amp;XSRFToken=<?php echo getXSRFToken('deletecomment')?>'; }"
			title="<?php echo gettext('Delete this comment.'); ?>" > <img
			src="images/fail.png" style="border: 0px;" alt="<?php echo gettext('Delete'); ?>" /></a></td>
		<td class="icons"><input type="checkbox" name="ids[]" value="<?php echo $id; ?>"
			onclick="triggerAllBox(this.form, 'ids[]', this.form.allbox);" /></td>
	</tr>
	<?php } ?>



</table>
<p class="buttons"><button type="submit" title="<?php echo gettext("Apply"); ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button></p>
		<ul class="iconlegend">
				<li><img src="images/reset.png" alt="" /><?php echo gettext("Private message"); ?></li>
				<li><img src="images/warn.png" alt="Marked as spam" /><img src="images/pass.png" alt="Approved" /><?php echo gettext("Marked as spam/approved"); ?></li>
				<li><img src="images/action.png" alt="Cache the album" /><?php echo gettext("Anonymous posting"); ?></li>
				<li><img src="images/pencil.png" alt="Edit comment" /><?php echo gettext("Edit comment"); ?></li>
				<li><img src="images/icon_mail.png" alt="E-mail comment author" /><?php echo gettext("E-mail comment author"); ?></li>
				<li><img src="images/fail.png" alt="Delete" /><?php echo gettext("Delete"); ?></li>
		</ul>

</form>

<?php
}

echo "\n" . '</div>';  //content
printAdminFooter();
echo "\n" . '</div>';  //main


echo "\n</body>";
echo "\n</html>";
?>



