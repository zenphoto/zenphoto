<?php
if (getOption('register_user_address_info')) {
	zp_register_filter('register_user_form', 'comment_form_register_user');
	zp_register_filter('register_user_registered', 'comment_form_register_save');
}

define('COMMENTS_PER_PAGE', max(1, getOption('comment_form_comments_per_page')));

$_zp_comment_stored = array();

function comment_form_PaginationJS() {
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.pagination.js"></script>
	<script type="text/javascript">
		function pageselectCallback(page_index, jq) {
			var items_per_page = <?php echo max(1, COMMENTS_PER_PAGE); ?>;
			var max_elem = Math.min((page_index + 1) * items_per_page, $('#comments div.comment').length);
			var newcontent = '';
			for (var i = page_index * items_per_page; i < max_elem; i++) {
				newcontent += '<div class="comment">' + $('#comments div.comment:nth-child(' + (i + 1) + ')').html() + '</div>';
			}
			$('#Commentresult').html(newcontent);
			return false;
		}
		function initPagination() {
			var startPage;
			if (Comm_ID_found) {
				startPage = Math.ceil(current_comment_N /<?php echo max(1, COMMENTS_PER_PAGE); ?>) - 1;
			} else {
				startPage = 0;
			}
			var num_entries = $('#comments div.comment').length;
			if (num_entries) {
				$(".Pagination").pagination(num_entries, {
					prev_text: "<?php echo gettext('prev'); ?>",
					next_text: "<?php echo gettext('next'); ?>",
					callback: pageselectCallback,
					load_first_page: true,
					items_per_page:<?php echo max(1, getOption('comment_form_comments_per_page')); ?>, // Show only one item per page
					current_page: startPage
				});
			}
		}
		$(document).ready(function() {
			current_comment_N = $('.comment h4').index($(addrBar_hash)) + 1;
			initPagination();
			if (Comm_ID_found) {
				$(addrBar_hash).scrollToMe();
			}
		});
		var current_comment_N, addrBar_hash = window.location.hash, Comm_ID_found = !addrBar_hash.search(/#zp_comment_id_/);
		jQuery.fn.extend({
			scrollToMe: function() {
				var x = jQuery(this).offset().top - 10;
				jQuery('html,body').animate({scrollTop: x}, 400);
			}});
	</script>
	<?php
}

function comment_form_visualEditor() {
	zp_apply_filter('texteditor_config', '', 'comments');
}

/**
 * Returns a processed comment custom data item
 * Called when a comment edit is saved
 *
 * @param string $discard always empty
 * @return string
 */
function comment_form_save_comment($discard) {
	global $_comment_form_save_post;
	return $_comment_form_save_post = serialize(getUserInfo(0));
}

/**
 * Admin overview summary
 */
function comment_form_print10Most() {
	?>
	<div class="box overview-utility">
		<h2 class="h2_bordered"><?php echo gettext("10 Most Recent Comments"); ?></h2>
		<ul>
			<?php
			$comments = fetchComments(10);
			foreach ($comments as $comment) {
				$id = $comment['id'];
				$author = $comment['name'];
				$email = $comment['email'];
				$link = gettext('<strong>database error</strong> '); // incase of such
				// ZENPAGE: switch added for zenpage comment support
				switch ($comment['type']) {
					case "albums":
						$image = '';
						$title = '';
						$albmdata = query_full_array("SELECT `title`, `folder` FROM " . prefix('albums') .
										" WHERE `id`=" . $comment['ownerid']);
						if ($albmdata) {
							$albumdata = $albmdata[0];
							$album = $albumdata['folder'];
							$albumtitle = get_language_string($albumdata['title']);
							$link = "<a href=\"" . rewrite_path("/$album", "/index.php?album=" . pathurlencode($album)) . "\">" . $albumtitle . $title . "</a>";
							if (empty($albumtitle))
								$albumtitle = $album;
						}
						break;
					case "news": // ZENPAGE: if plugin is installed
						if (extensionEnabled('zenpage')) {
							$titlelink = '';
							$title = '';
							$newsdata = query_full_array("SELECT `title`, `titlelink` FROM " . prefix('news') .
											" WHERE `id`=" . $comment['ownerid']);
							if ($newsdata) {
								$newsdata = $newsdata[0];
								$titlelink = $newsdata['titlelink'];
								$title = get_language_string($newsdata['title']);
								$link = "<a href=\"" . rewrite_path('/' . _NEWS_ . '/' . $titlelink, "/index.php?p=news&amp;title=" . urlencode($titlelink)) . "\">" . $title . "</a> " . gettext("[news]");
							}
						}
						break;
					case "pages": // ZENPAGE: if plugin is installed
						if (extensionEnabled('zenpage')) {
							$image = '';
							$title = '';
							$pagesdata = query_full_array("SELECT `title`, `titlelink` FROM " . prefix('pages') .
											" WHERE `id`=" . $comment['ownerid']);
							if ($pagesdata) {
								$pagesdata = $pagesdata[0];
								$titlelink = $pagesdata['titlelink'];
								$title = get_language_string($pagesdata['title']);
								$link = "<a href=\"" . rewrite_path('/' . _PAGES_ . '/' . $titlelink, "/index.php?p=pages&amp;title=" . urlencode($titlelink)) . "\">" . $title . "</a> " . gettext("[page]");
							}
						}
						break;
					default: // all of the image types
						$imagedata = query_full_array("SELECT `title`, `filename`, `albumid` FROM " . prefix('images') .
										" WHERE `id`=" . $comment['ownerid']);
						if ($imagedata) {
							$imgdata = $imagedata[0];
							$image = $imgdata['filename'];
							if ($imgdata['title'] == "")
								$title = $image;
							else
								$title = get_language_string($imgdata['title']);
							$title = '/ ' . $title;
							$albmdata = query_full_array("SELECT `folder`, `title` FROM " . prefix('albums') .
											" WHERE `id`=" . $imgdata['albumid']);
							if ($albmdata) {
								$albumdata = $albmdata[0];
								$album = $albumdata['folder'];
								$albumtitle = get_language_string($albumdata['title']);
								$link = "<a href=\"" . rewrite_path("/$album/$image", "/index.php?album=" . pathurlencode($album) . "&amp;image=" . urlencode($image)) . "\">" . $albumtitle . $title . "</a>";
								if (empty($albumtitle))
									$albumtitle = $album;
							}
						}
						break;
				}
				$comment = shortenContent($comment['comment'], 123, '...');
				echo "<li><div class=\"commentmeta\">" . sprintf(gettext('<em>%1$s</em> commented on %2$s:'), $author, $link) . "</div><div class=\"commentbody\">$comment</div></li>";
			}
			?>
		</ul>
	</div>
	<?php
}

/**
 * Returns table row(s) for edit of a comment's custom data
 *
 * @param string $discard always empty
 * @return string
 */
function comment_form_edit_comment($discard, $raw) {
	$address = getSerializedArray($raw);
	if (empty($address)) {
		$address = array('street'	 => '', 'city'		 => '', 'state'		 => '', 'country'	 => '', 'postal'	 => '', 'website'	 => '');
	}
	$required = getOption('register_user_address_info');
	if ($required == 'required') {
		$required = '*';
	} else {
		$required = false;
	}
	$html =
					'<p>
					<label for="comment_form_street">' .
					sprintf(gettext('Street%s'), $required) .
					'</label>
					<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="' . @$address['street'] . '">
				</p>
				<p>
					<label for="comment_form_city">' .
					sprintf(gettext('City%s'), $required) .
					'</label>
					 <input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="' . @$address['city'] . '">
				</p>
				<p>
					<label for="comment_form_state">' .
					sprintf(gettext('State%s'), $required) .
					'</label>
					<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="' . @$address['state'] . '">
				</p>
				<p>
					<label for="comment_form_country">' .
					sprintf(gettext('Country%s'), $required) .
					'</label>
					<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="' . @$address['country'] . '">
				</p>
				<p>
					<label for="comment_form_postal">' .
					sprintf(gettext('Postal code%s'), $required) .
					'</label>
					 <input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="' . @$address['postal'] . '">
				</p>' . "\n";
	return $html;
}

function comment_form_register_user($html) {
	global $_comment_form_save_post;
	$_comment_form_save_post = zp_getCookie('comment_form_register_save');
	return comment_form_edit_comment(false, $_comment_form_save_post);
}

function comment_form_register_save($user) {
	global $_comment_form_save_post;
	$addresses = getOption('register_user_address_info');
	$userinfo = getUserInfo(0);
	$_comment_form_save_post = serialize($userinfo);
	if ($addresses == 'required') {
		if (!isset($userinfo['street']) || empty($userinfo['street'])) {
			$user->transient = true;
			$user->msg .= ' ' . gettext('You must supply the street field.');
		}
		if (!isset($userinfo['city']) || empty($userinfo['city'])) {
			$user->transient = true;
			$user->msg .= ' ' . gettext('You must supply the city field.');
		}
		if (!isset($userinfo['state']) || empty($userinfo['state'])) {
			$user->transient = true;
			$user->msg .= ' ' . gettext('You must supply the state field.');
		}
		if (!isset($userinfo['country']) || empty($userinfo['country'])) {
			$user->transient = true;
			$user->msg .= ' ' . gettext('You must supply the country field.');
		}
		if (!isset($userinfo['postal']) || empty($userinfo['postal'])) {
			$user->transient = true;
			$user->msg .= ' ' . gettext('You must supply the postal code field.');
		}
	}
	zp_setCookie('comment_form_register_save', $_comment_form_save_post);
	$user->setCustomData($_comment_form_save_post);
}

/**
 * Saves admin custom data
 * Called when an admin is saved
 *
 * @param string $updated true if data has changed
 * @param object $userobj admin user object
 * @param string $i prefix for the admin
 * @param bool $alter will be true if critical admin data may be altered
 * @return bool
 */
function comment_form_save_admin($updated, $userobj, $i, $alter) {
	if ($userobj->getValid()) {
		$olddata = $userobj->getCustomData();
		$userobj->setCustomData(serialize(getUserInfo($i)));
		if ($olddata != $userobj->getCustomData()) {
			return true;
		}
	}
	return $updated;
}

/**
 * Processes the post of an address
 *
 * @param int $i sequence number of the comment
 * @return array
 */
function getUserInfo($i) {
	$result = array();
	if (isset($_POST[$i . '-comment_form_website']))
		$result['website'] = sanitize($_POST[$i . '-comment_form_website'], 1);
	if (isset($_POST[$i . '-comment_form_street']))
		$result['street'] = sanitize($_POST[$i . '-comment_form_street'], 1);
	if (isset($_POST[$i . '-comment_form_city']))
		$result['city'] = sanitize($_POST[$i . '-comment_form_city'], 1);
	if (isset($_POST[$i . '-comment_form_state']))
		$result['state'] = sanitize($_POST[$i . '-comment_form_state'], 1);
	if (isset($_POST[$i . '-comment_form_country']))
		$result['country'] = sanitize($_POST[$i . '-comment_form_country'], 1);
	if (isset($_POST[$i . '-comment_form_postal']))
		$result['postal'] = sanitize($_POST[$i . '-comment_form_postal'], 1);
	return $result;
}

/**
 * Processes the address parts of a comment post
 *
 * @param object $commentobj the comment object
 * @param object $receiver the object receiving the comment
 * @return object
 */
function comment_form_comment_post($commentobj, $receiver) {
	if ($addresses = getOption('comment_form_addresses')) {
		$userinfo = getUserInfo(0);
		if ($addresses == 'required') {
			// Note: this error will be incremented by functions-controller
			if (!isset($userinfo['street']) || empty($userinfo['street'])) {
				$commentobj->setInModeration(-11);
				$commentobj->comment_error_text .= ' ' . gettext('You must supply the street field.');
			}
			if (!isset($userinfo['city']) || empty($userinfo['city'])) {
				$commentobj->setInModeration(-12);
				$commentobj->comment_error_text .= ' ' . gettext('You must supply the city field.');
			}
			if (!isset($userinfo['state']) || empty($userinfo['state'])) {
				$commentobj->setInModeration(-13);
				$commentobj->comment_error_text .= ' ' . gettext('You must supply the state field.');
			}
			if (!isset($userinfo['country']) || empty($userinfo['country'])) {
				$commentobj->setInModeration(-14);
				$commentobj->comment_error_text .= ' ' . gettext('You must supply the country field.');
			}
			if (!isset($userinfo['postal']) || empty($userinfo['postal'])) {
				$commentobj->comment_error_text .= ' ' . gettext('You must supply the postal code field.');
				$commentobj->setInModeration(-15);
			}
		}
		$commentobj->setCustomData(serialize($userinfo));
	}
	return $commentobj;
}

/**
 * Supplies comment form options on the options/comments tab
 */
function comment_form_options() {
	$optionHandler = new comment_form();
	customOptions($optionHandler, "");
}

/**
 * Returns table row(s) for edit of an admin user's custom data
 *
 * @param string $html always empty
 * @param $userobj Admin user object
 * @param string $i prefix for the admin
 * @param string $background background color for the admin row
 * @param bool $current true if this admin row is the logged in admin
 * @return string
 */
function comment_form_edit_admin($html, $userobj, $i, $background, $current) {
	if (!$userobj->getValid())
		return $html;
	$needs = array('street'	 => '', 'city'		 => '', 'state'		 => '', 'country'	 => '', 'postal'	 => '', 'website'	 => '');
	$address = getSerializedArray($userobj->getCustomData());
	if (empty($address)) {
		$address = $needs;
	} else {
		foreach ($needs as $needed => $value) {
			if (!isset($address[$needed])) {
				$address[$needed] = '';
			}
		}
	}

	return $html .
					'<tr' . ((!$current) ? ' style="display:none;"' : '') . ' class="userextrainfo">' .
					'<td width="20%"' . ((!empty($background)) ? ' style="' . $background . '"' : '') . ' valign="top">' .
					'<fieldset>
					<legend>' . gettext("Street") . '</legend>
					<input type="text" name="' . $i . '-comment_form_street" value="' . $address['street'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'<fieldset>
					<legend>' . gettext("City") . '</legend>
					<input type="text" name="' . $i . '-comment_form_city" value="' . $address['city'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'<fieldset>
					<legend>' . gettext("State") . '</legend>
					<input type="text" name="' . $i . '-comment_form_state" value="' . $address['state'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'</td>' .
					'<td' . ((!empty($background)) ? ' style="' . $background . '"' : '') . ' valign="top">' .
					'<fieldset>
					<legend>' . gettext("Website") . '</legend>
					<input type="text" name="' . $i . '-comment_form_website" value="' . $address['website'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'<fieldset>
					<legend>' . gettext("Country") . '</legend>
					<input type="text" name="' . $i . '-comment_form_country" value="' . $address['country'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'<fieldset>
					<legend>' . gettext("Postal code") . '</legend>
					<input type="text" name="' . $i . '-comment_form_postal" value="' . $address['postal'] . '" size="' . TEXT_INPUT_SIZE . '" />
				</fieldset>' .
					'</td>' .
					'</tr>';
}

/**
 * Returns an error message if a comment posting was not accepted
 *
 * @return string
 */
function getCommentErrors() {
	global $_zp_comment_error;
	return $_zp_comment_error;
}

/**
 * Tool to output an error message if a comment posting was not accepted
 */
function printCommentErrors() {
	global $_zp_comment_error, $_zp_comment_on_hold;
	if ($_zp_comment_on_hold) {
		$s = trim(str_replace($_zp_comment_on_hold, '', trim($_zp_comment_error)));
		?>
		<p class="notebox"><?php echo $_zp_comment_on_hold; ?></p>
		<?php
	} else {
		$s = trim($_zp_comment_error);
	}
	if ($s) {
		$lines = explode('.', $s);
		foreach ($lines as $key => $line) {
			if (empty($line) || $line == gettext('Mail send failed')) {
				unset($lines[$key]);
			}
		}
		?>
		<div class="errorbox">
			<h2><?php echo ngettext('Error posting comment:', 'Errors posting comment:', count($lines)); ?></h2>
			<ul class="errorlist">
				<?php
				foreach ($lines as $line) {
					echo '<li>' . trim($line) . '</li>';
				}
				?>
			</ul>
		</div>
		<?php
	}
}

define('COMMENT_EMAIL_REQUIRED', 1);
define('COMMENT_NAME_REQUIRED', 2);
define('COMMENT_WEB_REQUIRED', 4);
define('USE_CAPTCHA', 8);
define('COMMENT_BODY_REQUIRED', 16);
define('COMMENT_SEND_EMAIL', 32);

/**
 * Generic comment adding routine. Called by album objects or image objects
 * to add comments.
 *
 * Returns a comment object
 *
 * @param string $name Comment author name
 * @param string $email Comment author email
 * @param string $website Comment author website
 * @param string $comment body of the comment
 * @param string $code CAPTCHA code entered
 * @param string $code_ok CAPTCHA hash expected
 * @param string $type 'albums' if it is an album or 'images' if it is an image comment
 * @param object $receiver the object (image or album) to which to post the comment
 * @param string $ip the IP address of the comment poster
 * @param bool $private set to true if the comment is for the admin only
 * @param bool $anon set to true if the poster wishes to remain anonymous
 * @param bit $check bitmask of which fields must be checked. If set overrides the options
 * @return object
 */
function comment_form_addCcomment($name, $email, $website, $comment, $code, $code_ok, $receiver, $ip, $private, $anon, $check = false) {
	global $_zp_captcha, $_zp_gallery, $_zp_authority, $_zp_comment_on_hold, $_zp_spamFilter;
	if ($check === false) {
		$whattocheck = 0;
		if (getOption('comment_email_required') == 'required')
			$whattocheck = $whattocheck | COMMENT_EMAIL_REQUIRED;
		if (getOption('comment_name_required'))
			$whattocheck = $whattocheck | COMMENT_NAME_REQUIRED;
		if (getOption('comment_web_required') == 'required')
			$whattocheck = $whattocheck | COMMENT_WEB_REQUIRED;
		switch (getOption('Use_Captcha')) {
			case 0:
				break;
			case 2:
				if (zp_loggedin(POST_COMMENT_RIGHTS)) {
					break;
				}
			default:
				$whattocheck = $whattocheck | USE_CAPTCHA;
				break;
		}
		if (getOption('comment_body_requiired'))
			$whattocheck = $whattocheck | COMMENT_BODY_REQUIRED;
		IF (getOption('email_new_comments'))
			$whattocheck = $whattocheck | COMMENT_SEND_EMAIL;
	} else {
		$whattocheck = $check;
	}
	$type = $receiver->table;
	$class = get_class($receiver);
	$receiver->getComments();
	$name = trim($name);
	$email = trim($email);
	$website = trim($website);
	// Let the comment have trailing line breaks and space? Nah...
	// Also (in)validate HTML here, and in $name.
	$comment = trim($comment);
	$receiverid = $receiver->getID();
	$goodMessage = 2;
	if ($private)
		$private = 1;
	else
		$private = 0;
	if ($anon)
		$anon = 1;
	else
		$anon = 0;
	$commentobj = new Comment();
	$commentobj->transient = false; // otherwise we won't be able to save it....
	$commentobj->setOwnerID($receiverid);
	$commentobj->setName($name);
	$commentobj->setEmail($email);
	$commentobj->setWebsite($website);
	$commentobj->setComment($comment);
	$commentobj->setType($type);
	$commentobj->setIP($ip);
	$commentobj->setPrivate($private);
	$commentobj->setAnon($anon);
	$commentobj->setInModeration(0);
	if (($whattocheck & COMMENT_EMAIL_REQUIRED) && (empty($email) || !is_valid_email_zp($email))) {
		$commentobj->setInModeration(-2);
		$commentobj->comment_error_text .= ' ' . gettext("You must supply an e-mail address.");
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_NAME_REQUIRED) && empty($name)) {
		$commentobj->setInModeration(-3);
		$commentobj->comment_error_text .= ' ' . gettext("You must enter your name.");
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_WEB_REQUIRED) && (empty($website) || !isValidURL($website))) {
		$commentobj->setInModeration(-4);
		$commentobj->comment_error_text .= ' ' . gettext("You must supply a WEB page URL.");
		$goodMessage = false;
	}
	if (($whattocheck & USE_CAPTCHA)) {
		if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
			$commentobj->setInModeration(-5);
			$commentobj->comment_error_text .= ' ' . gettext("CAPTCHA verification failed.");
			$goodMessage = false;
		}
	}
	if (($whattocheck & COMMENT_BODY_REQUIRED) && empty($comment)) {
		$commentobj->setInModeration(-6);
		$commentobj->comment_error_text .= ' ' . gettext("You must enter something in the comment text.");
		$goodMessage = false;
	}
	$moderate = 0;
	if ($goodMessage && isset($_zp_spamFilter)) {
		$goodMessage = $_zp_spamFilter->filterMessage($name, $email, $website, $comment, $receiver, $ip);
		switch ($goodMessage) {
			case 0:
				$commentobj->setInModeration(2);
				$commentobj->comment_error_text .= sprintf(gettext('Your comment was rejected by the <em>%s</em> SPAM filter.'), $_zp_spamFilter->name);
				$goodMessage = false;
				break;
			case 1:
				$_zp_comment_on_hold = sprintf(gettext('Your comment has been marked for moderation by the <em>%s</em> SPAM filter.'), $_zp_spamFilter->name);
				$commentobj->comment_error_text .= $_zp_comment_on_hold;
				$commentobj->setInModeration(1);
				$moderate = 1;
				break;
			case 2:
				$commentobj->setInModeration(0);
				break;
		}
	}
	$localerrors = $commentobj->getInModeration();
	zp_apply_filter('comment_post', $commentobj, $receiver);
	if ($check === false) {
		// ignore filter provided errors if caller is supplying the fields to check
		$localerrors = $commentobj->getInModeration();
	}
	if ($goodMessage && $localerrors >= 0) {
		// Update the database entry with the new comment
		$commentobj->save();
		//  add to comments array and notify the admin user
		if (!$moderate) {
			$receiver->comments[] = array('name'				 => $commentobj->getname(),
							'email'				 => $commentobj->getEmail(),
							'website'			 => $commentobj->getWebsite(),
							'comment'			 => $commentobj->getComment(),
							'date'				 => $commentobj->getDateTime(),
							'custom_data'	 => $commentobj->getCustomData());
		}
		$class = strtolower(get_class($receiver));
		switch ($class) {
			case "album":
				$url = "album=" . pathurlencode($receiver->name);
				$ur_album = getUrAlbum($receiver);
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your album "%1$s".'), $receiver->name);
				} else {
					$action = sprintf(gettext('A comment has been posted on your album "%1$s".'), $receiver->name);
				}
				break;
			case "zenpagenews":
				$url = "p=news&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your article "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your article "%1$s".'), $receiver->getTitlelink());
				}
				break;
			case "zenpagepage":
				$url = "p=pages&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your page "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your page "%1$s".'), $receiver->getTitlelink());
				}
				break;
			default: // all image types
				$album = $receiver->getAlbum();
				$url = "album=" . pathurlencode($album->name) . "&image=" . urlencode($receiver->filename);
				$ur_album = getUrAlbum($album);
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $album->name);
				} else {
					$action = sprintf(gettext('A comment has been posted on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $album->name);
				}
				break;
		}
		if (($whattocheck & COMMENT_SEND_EMAIL)) {
			$message = $action . "\n\n" .
							sprintf(gettext('Author: %1$s' . "\n" . 'Email: %2$s' . "\n" . 'Website: %3$s' . "\n" . 'Comment:' . "\n\n" . '%4$s'), $commentobj->getname(), $commentobj->getEmail(), $commentobj->getWebsite(), $commentobj->getComment()) . "\n\n" .
							sprintf(gettext('You can view all comments about this item here:' . "\n" . '%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/index.php?' . $url) . "\n\n" .
							sprintf(gettext('You can edit the comment here:' . "\n" . '%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form/admin-comments.php?page=editcomment&id=' . $commentobj->getID());
			$emails = array();
			$admin_users = $_zp_authority->getAdministrators();
			foreach ($admin_users as $admin) {
				// mail anyone with full rights
				if (!empty($admin['email']) && (($admin['rights'] & ADMIN_RIGHTS) ||
								(($admin['rights'] & (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)) == (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)))) {
					$emails[] = $admin['email'];
					unset($admin_users[$admin['id']]);
				}
			}
			if ($type === "images" OR $type === "albums") {
				// mail to album admins
				$id = $ur_album->getID();
				$sql = 'SELECT `adminid` FROM ' . prefix('admin_to_object') . ' WHERE `objectid`=' . $id . ' AND `type` LIKE "album%"';
				$result = query($sql);
				if ($result) {
					while ($anadmin = db_fetch_assoc($result)) {
						$id = $anadmin['adminid'];
						if (array_key_exists($id, $admin_users)) {
							$admin = $admin_users[$id];
							if (($admin['rights'] & COMMENT_RIGHTS) && !empty($admin['email'])) {
								$emails[] = $admin['email'];
							}
						}
					}
					db_free_result($result);
				}
			}
			$on = gettext('Comment posted');
			$result = zp_mail("[" . $_zp_gallery->getTitle() . "] $on", $message, $emails);
			if ($result) {
				$commentobj->setInModeration(-12);
				$commentobj->comment_error_text = $result;
			}
		}
	}
	return $commentobj;
}

/**
 * Use see if a captcha should be displayed
 * @return boolean
 */
function commentFormUseCaptcha() {
	switch (getOption('Use_Captcha')) {
		case 0:
			return false;
		case 2:
			return !zp_loggedin(POST_COMMENT_RIGHTS);
		default:
			return true;
	}
}

/**
 *
 * checks if comments are allowed and then processes them if so
 * @param string $error
 */
function comment_form_postcomment($error) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	if (( (commentsAllowed('comment_form_albums') && in_context(ZP_ALBUM) && !in_context(ZP_IMAGE) && $_zp_current_album->getCommentsAllowed()) ||
					(commentsAllowed('comment_form_images') && in_context(ZP_IMAGE) && $_zp_current_image->getCommentsAllowed()) ||
					(commentsAllowed('comment_form_articles') && in_context(ZP_ZENPAGE_NEWS_ARTICLE) && $_zp_current_zenpage_news->getCommentsAllowed()) ||
					(commentsAllowed('comment_form_pages') && in_context(ZP_ZENPAGE_PAGE) && $_zp_current_zenpage_page->getCommentsAllowed()))
	) {
		$error = comment_form_handle_comment();
	}
	return $error;
}

/**
 *
 * Handles the POSTing of a comment
 * @return NULL|boolean
 */
function comment_form_handle_comment() {
	global $_zp_current_image, $_zp_current_album, $_zp_comment_stored, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_HTML_cache;
	$comment_error = 0;
	$cookie = zp_getCookie('zenphoto_comment');
	if (isset($_POST['comment']) && (!isset($_POST['username']) || empty($_POST['username']))) { // 'username' is a honey-pot trap
		/*
		 * do not save the post page in the cache
		 * Also the cache should be cleared so that a new page is saved at the first non-comment posting viewing.
		 * But this has to wait until processing is finished to avoid race conditions.
		 */
		$_zp_HTML_cache->disable();
		if (in_context(ZP_IMAGE)) {
			$commentobject = $_zp_current_image;
			$redirectTo = $_zp_current_image->getImageLink();
		} else if (in_context(ZP_ALBUM)) {
			$commentobject = $_zp_current_album;
			$redirectTo = $_zp_current_album->getAlbumLink();
		} else if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
			$commentobject = $_zp_current_zenpage_news;
			$redirectTo = FULLWEBPATH . '/index.php?p=news&title=' . $_zp_current_zenpage_news->getTitlelink();
		} else if (in_context(ZP_ZENPAGE_PAGE)) {
			$commentobject = $_zp_current_zenpage_page;
			$redirectTo = FULLWEBPATH . '/index.php?p=pages&title=' . $_zp_current_zenpage_page->getTitlelink();
		} else {
			$commentobject = NULL;
			$error = gettext('Comment posted on unknown page!');
		}
		if (is_object($commentobject)) {
			if (isset($_POST['name'])) {
				$p_name = sanitize($_POST['name'], 3);
			} else {
				$p_name = NULL;
			}
			if (isset($_POST['email'])) {
				$p_email = sanitize($_POST['email'], 3);
				if (!is_valid_email_zp($p_email)) {
					$p_email = NULL;
				}
			} else {
				$p_email = NULL;
			}
			if (isset($_POST['website'])) {
				$p_website = sanitize($_POST['website'], 3);
				if ($p_website && strpos($p_website, 'http') !== 0) {
					$p_website = 'http://' . $p_website;
				}
				if (!isValidURL($p_website)) {
					$p_website = NULL;
				}
			} else {
				$p_website = NULL;
			}
			if (isset($_POST['comment'])) {
				$p_comment = sanitize($_POST['comment'], 1);
			} else {
				$p_comment = '';
			}
			$p_server = getUserIP();
			if (isset($_POST['code'])) {
				$code1 = sanitize($_POST['code'], 3);
				$code2 = sanitize($_POST['code_h'], 3);
			} else {
				$code1 = '';
				$code2 = '';
			}
			$p_private = isset($_POST['private']);
			$p_anon = isset($_POST['anon']);

			$commentadded = $commentobject->addComment($p_name, $p_email, $p_website, $p_comment, $code1, $code2, $p_server, $p_private, $p_anon);

			$comment_error = $commentadded->getInModeration();
			$_zp_comment_stored = array('name'		 => $commentadded->getName(),
							'email'		 => $commentadded->getEmail(),
							'website'	 => $commentadded->getWebsite(),
							'comment'	 => $commentadded->getComment(),
							'saved'		 => isset($_POST['remember']),
							'private'	 => $commentadded->getPrivate(),
							'anon'		 => $commentadded->getAnon(),
							'custom'	 => $commentadded->getCustomData()
			);

			if ($comment_error) {
				$error = $commentadded->comment_error_text;
				$comment_error++;
			} else {
				$_zp_HTML_cache->clearHtmlCache();
				$error = NULL;
				if (isset($_POST['remember'])) {
					// Should always re-cookie to update info in case it's changed...
					$_zp_comment_stored['comment'] = ''; // clear the comment itself
					zp_setCookie('zenphoto_comment', serialize($_zp_comment_stored));
				} else {
					zp_clearCookie('zenphoto_comment');
				}
				//use $redirectTo to send users back to where they came from instead of booting them back to the gallery index. (default behaviour)
				if (!isset($_SERVER['SERVER_SOFTWARE']) || strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') === false) {
					// but not for Microsoft IIS because that server fails if we redirect!
					header('Location: ' . $redirectTo . '#zp_comment_id_' . $commentadded->getId());
					exitZP();
				}
			}
		}
		return $error;
	} else {
		if (!empty($cookie)) {
			$cookiedata = getSerializedArray($cookie);
			if (count($cookiedata) > 1) {
				$_zp_comment_stored = $cookiedata;
			}
		}
	}
	return false;
}

/**
 * Returns the comment author's name
 *
 * @return string
 */
function getCommentAuthorName() {
	global $_zp_current_comment;
	return $_zp_current_comment['name'];
}

/**
 * Returns the comment author's email
 *
 * @return string
 */
function getCommentAuthorEmail() {
	global $_zp_current_comment;
	return $_zp_current_comment['email'];
}

/**
 * Returns the comment author's website
 *
 * @return string
 */
function getCommentAuthorSite() {
	global $_zp_current_comment;
	return $_zp_current_comment['website'];
}

/**
 * Prints a link to the author
 *
 * @param string $title URL title tag
 * @param string $class optional class tag
 * @param string $id optional id tag
 */
function getCommentAuthorLink($title = NULL, $class = NULL, $id = NULL) {
	global $_zp_current_comment;
	$site = $_zp_current_comment['website'];
	$name = $_zp_current_comment['name'];
	if ($_zp_current_comment['anon']) {
		$name = substr($name, 1, strlen($name) - 2); // strip off the < and >
	}
	$namecoded = html_encode($_zp_current_comment['name']);
	if (empty($site)) {
		echo $namecoded;
	} else {
		if (is_null($title)) {
			$title = "Visit " . $name;
		}
		return getLink($site, $namecoded, $title, $class, $id);
	}
}

/**
 * Prints a link to the author
 *
 * @param string $title URL title tag
 * @param string $class optional class tag
 * @param string $id optional id tag
 */
function printCommentAuthorLink($title = NULL, $class = NULL, $id = NULL) {
	echo getCommentAuthorLink($title, $class, $id);
}

/**
 * Returns a formatted date and time for the comment.
 * Uses the "date_format" option for the formatting unless
 * a format string is passed.
 *
 * @param string $format 'strftime' date/time format
 * @return string
 */
function getCommentDateTime($format = NULL) {
	if (is_null($format)) {
		$format = DATE_FORMAT;
	}
	global $_zp_current_comment;
	return myts_date($format, $_zp_current_comment['date']);
}

/**
 * Returns the body of the current comment
 *
 * @return string
 */
function getCommentBody() {
	global $_zp_current_comment;
	return str_replace("\n", "<br />", stripslashes($_zp_current_comment['comment']));
}

/**
 * Creates a link to the admin comment edit page for the current comment
 *
 * @param string $text Link text
 * @param string $before text to go before the link
 * @param string $after text to go after the link
 * @param string $title title text
 * @param string $class optional css clasee
 * @param string $id optional css id
 */
function printEditCommentLink($text, $before = '', $after = '', $title = NULL, $class = NULL, $id = NULL) {
	global $_zp_current_comment;
	if (zp_loggedin(COMMENT_RIGHTS)) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		printLink(WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/comment_form//admin-comments.php?page=editcomment&id=' . $_zp_current_comment['id'], $text, $title, $class, $id);
		if ($after) {
			echo '<span class="aftertext">' . html_encode($after) . '</span>';
		}
	}
}

/**
 * Gets latest comments for images and albums
 *
 * @param int $number how many comments you want.
 * @param string $type	"all" for all latest comments of all images and albums
 * 											an array of table items e.g. array('images','albums') for all images and albums
 * 											"image" for the lastest comments of one specific image
 * 											"album" for the latest comments of one specific album
 * 											"news" for the latest comments of one specific news article
 * 											"page" for the latest comments of one specific Page
 * @param int $id the record id of element to get the comments for if $type != "all"
 */
function getLatestComments($number, $type = "all", $id = NULL) {
	global $_zp_gallery;
	$albumcomment = $imagecomment = NULL;
	$comments = array();
	$whereclause = '';
	switch ($type) {
		case is_array($type):
			$whereclause = ' AND `type` IN ("' . implode('","', $type) . '")';
		case 'all':
			$sql = 'SELECT * FROM ' . prefix('comments') . ' WHERE `private`=0 AND `inmoderation`=0' . $whereclause . ' ORDER BY `date` DESC';
			$commentsearch = query($sql);
			if ($commentsearch) {
				while ($number > 0 && $commentcheck = db_fetch_assoc($commentsearch)) {
					$item = getItemByID($commentcheck['type'], $commentcheck['ownerid']);
					if ($item && $item->checkAccess()) {
						$number--;
						$commentcheck['albumtitle'] = $commentcheck['titlelink'] = $commentcheck['folder'] = $commentcheck['filename'] = '';
						$commentcheck['title'] = $item->getTitle('all');
						switch ($item->table) {
							case 'albums':
								$commentcheck['folder'] = $item->getFolder();
								$commentcheck['albumtitle'] = $commentcheck['title'];
								break;
							case 'images':
								$commentcheck['filename'] = $item->filename;
								$commentcheck['folder'] = $item->album->name;
								$commentcheck['albumtitle'] = $item->album->getTitle('all');
								break;
							case 'news':
							case 'pages':
								$commentcheck['titlelink'] = $item->getTitlelink();
								break;
						}
						$commentcheck['pubdate'] = $commentcheck['date']; //	for RSS
						$comments[] = $commentcheck;
					}
				}
				db_free_result($commentsearch);
			}
			return $comments;
		case 'album':
			if ($item = getItemByID('albums', $id)) {
				$comments = array_slice($item->getComments(), 0, $number);
				// add the other stuff people want
				foreach ($comments as $key => $comment) {
					$comment['pubdate'] = $comment['date'];
					$alb = getItemByID('albums', $comment['ownerid']);
					$comment['folder'] = $alb->name;
					$comment['albumtitle'] = $item->getTitle('all');
					$comments[$key] = $comment;
				}
				return $comments;
			} else {
				return array();
			}
		case 'image':
			if ($item = getItemByID('images', $id)) {
				$comments = array_slice($item->getComments(), 0, $number);
				// add the other stuff people want
				foreach ($comments as $key => $comment) {
					$comment['pubdate'] = $comment['date'];
					$img = getItemByID('images', $comment['ownerid']);
					$comment['folder'] = $img->album->name;
					$comment['filename'] = $img->filename;
					$comment['title'] = $item->getTitle('all');
					$comment['albumtitle'] = $img->album->getTitle('all');
					$comments[$key] = $comment;
				}
				return $comments;
			} else {
				return array();
			}
		case 'news':
			if ($item = getItemByID('news', $id)) {
				$comments = array_slice($item->getComments(), 0, $number);
				// add the other stuff people want
				foreach ($comments as $key => $comment) {
					$comment['pubdate'] = $comment['date'];
					$comment['titlelink'] = $item->getTitlelink();
					$comment['title'] = $item->getTitle('all');
					$comments[$key] = $comment;
				}
				return $comments;
			} else {
				return array();
			}
		case 'page':
			if ($item = getItemByID('pages', $id)) {
				$comments = array_slice($item->getComments(), 0, $number);
				// add the other stuff people want
				foreach ($comments as $key => $comment) {
					$comment['pubdate'] = $comment['date'];
					$comment['titlelink'] = $item->getTitlelink();
					$comment['title'] = $item->getTitle('all');
					$comments[$key] = $comment;
				}
				return $comments;
			} else {
				return array();
			}
	}
}

/**
 * Prints out latest comments for images and albums
 *
 * @param see getLatestComments
 *
 */
function printLatestComments($number, $shorten = '123', $type = "all", $item = NULL, $ulid = 'showlatestcomments') {
	$comments = getLatestComments($number, $type, $item);
	echo '<ul id="' . $ulid . $item . "\">\n";
	foreach ($comments as $comment) {
		if ($comment['anon'] === "0") {
			$author = " " . gettext("by") . " " . $comment['name'];
		} else {
			$author = "";
		}
		$shortcomment = truncate_string($comment['comment'], $shorten);
		$website = $comment['website'];
		$date = $comment['date'];
		switch ($comment['type']) {
			case 'albums':
			case 'images':
				$album = $comment['folder'];
				if ($comment['type'] == "images") { // check if not comments on albums or Zenpage items
					$imagetag = '&amp;image=' . $comment['filename'];
					$imagetagR = '/' . $comment['filename'] . getOption('mod_rewrite_image_suffix');
				} else {
					$imagetag = $imagetagR = "";
				}
				$albumtitle = get_language_string($comment['albumtitle']);
				$title = '';
				if ($comment['type'] != 'albums') {
					if ($comment['title'] == "")
						$title = '';
					else
						$title = get_language_string($comment['title']);
				}
				if (!empty($title)) {
					$title = ": " . $title;
				}
				echo '<li><a href="' . rewrite_path($album . $imagetagR, '?album=' . $album . $imagetag) . '" class="commentmeta">' . $albumtitle . $title . $author . "</a><br />\n";
				echo '<span class="commentbody">' . $shortcomment . '</span></li>';
				break;
			case 'news':
				$title = get_language_string($comment['title']);
				echo '<li><a href="' . rewrite_path('/' . _NEWS_ . '/' . $comment['titlelink'], '/index.php?p=news&amp;title=' . $comment['titlelink']) . '" class="commentmeta">' . gettext('News') . ':' . $title . $author . "</a><br />\n";
				echo '<span class="commentbody">' . $shortcomment . '</span></li>';
				break;
			case 'pages':
				$title = get_language_string($comment['title']);
				echo '<li><a href="' . rewrite_path('/' . _PAGES_ . '/' . $comment['titlelink'], '/index.php?p=pages&amp;title=' . $comment['titlelink']) . '" class="commentmeta">' . gettext('News') . ':' . $title . $author . "</a><br />\n";
				echo '<span class="commentbody">' . $shortcomment . '</span></li>';
				break;
		}
	}
	echo "</ul>\n";
}

/**
 * Retuns the count of comments on the current image
 *
 * @return int
 */
function getCommentCount() {
	global $_zp_current_image, $_zp_current_album, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	if (in_context(ZP_IMAGE) && in_context(ZP_ALBUM)) {
		if (is_null($_zp_current_image))
			return false;
		return $_zp_current_image->getCommentCount();
	} else if (!in_context(ZP_IMAGE) && in_context(ZP_ALBUM)) {
		if (is_null($_zp_current_album))
			return false;
		return $_zp_current_album->getCommentCount();
	}
	if (function_exists('is_News')) {
		if (is_News()) {
			return $_zp_current_zenpage_news->getCommentCount();
		}
		if (is_Pages()) {
			return $_zp_current_zenpage_page->getCommentCount();
		}
	}
}

/**
 * Returns true if neither the album nor the image have comments closed
 *
 * @return bool
 */
function getCommentsAllowed() {
	global $_zp_current_image, $_zp_current_album;
	if (in_context(ZP_IMAGE)) {
		if (is_null($_zp_current_image))
			return false;
		return $_zp_current_image->getCommentsAllowed();
	} else {
		return $_zp_current_album->getCommentsAllowed();
	}
}

/**
 * Iterate through comments; use the ZP_COMMENT context.
 * Return true if there are more comments
 * @param  bool $desc set true for desecnding order
 *
 * @return bool
 */
function next_comment($desc = false) {
	global $_zp_current_image, $_zp_current_album, $_zp_current_comment, $_zp_comments, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	//ZENPAGE: comments support
	if (is_null($_zp_current_comment)) {
		if (in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
			if (is_null($_zp_current_image))
				return false;
			$_zp_comments = $_zp_current_image->getComments(false, false, $desc);
		} else if (!in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
			$_zp_comments = $_zp_current_album->getComments(false, false, $desc);
		}
		if (function_exists('is_NewsArticle')) {
			if (is_NewsArticle()) {
				$_zp_comments = $_zp_current_zenpage_news->getComments(false, false, $desc);
			}
			if (is_Pages()) {
				$_zp_comments = $_zp_current_zenpage_page->getComments(false, false, $desc);
			}
		}
		if (empty($_zp_comments)) {
			return false;
		}
	} else if (empty($_zp_comments)) {
		$_zp_comments = NULL;
		$_zp_current_comment = NULL;
		rem_context(ZP_COMMENT);
		return false;
	}
	$_zp_current_comment = array_shift($_zp_comments);
	if ($_zp_current_comment['anon']) {
		$_zp_current_comment['email'] = $_zp_current_comment['name'] = '<' . gettext("Anonymous") . '>';
	}
	add_context(ZP_COMMENT);
	return true;
}

/**
 * Returns the data from the last comment posted
 * @param bool $numeric Set to true for old themes to get 0->6 indices rather than descriptive ones
 *
 * @return array
 */
function getCommentStored($numeric = false) {
	global $_zp_comment_stored;
	if ($numeric) {
		return array_merge($_zp_comment_stored);
	}
	return $_zp_comment_stored;
}
?>
